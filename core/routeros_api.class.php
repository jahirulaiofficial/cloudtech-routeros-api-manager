<?php
/**
 * RouterOS API Class for CloudTech V4
 * MikroTik RouterOS API implementation for PHP
 */

class RouterosAPI {
    private $debug = false;
    private $socket;
    private $error_no;
    private $error_str;
    private $attempts = 5;
    private $connected = false;
    private $port = 8728;
    private $ssl = false;
    private $timeout = 3;

    public function __construct() {
        $this->error_no = 0;
        $this->error_str = '';
    }

    public function debug($status = true) {
        $this->debug = $status;
        return $this;
    }

    public function connect($ip, $login, $password, $port = null) {
        if ($port !== null) {
            $this->port = $port;
        }
        
        $this->connected = false;
        $ATTEMPT = 0;
        
        $this->socket = @fsockopen(($this->ssl ? 'ssl://' : '') . $ip, $this->port, $this->error_no, $this->error_str, $this->timeout);
        
        if (!$this->socket) {
            throw new Exception('Error connecting to RouterOS: ' . $this->error_str);
        }
        
        // Read the welcome message
        $this->read(false);
        
        $this->write('/login');
        $RESPONSE = $this->read(false);
        
        if (isset($RESPONSE[0]) && $RESPONSE[0] === '!done') {
            if (!isset($RESPONSE[1])) {
                // Login method post-v6.43
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=password=' . $password);
            } else {
                // Login method pre-v6.43
                $MATCHES = array();
                if (preg_match_all('/[^=]+/i', $RESPONSE[1], $MATCHES)) {
                    if ($MATCHES[0][0] == 'ret' && strlen($MATCHES[0][1]) == 32) {
                        $this->write('/login', false);
                        $this->write('=name=' . $login, false);
                        $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $MATCHES[0][1])));
                    }
                }
            }
            
            $RESPONSE = $this->read(false);
            if (isset($RESPONSE[0]) && $RESPONSE[0] === '!done') {
                $this->connected = true;
                return true;
            }
        }
        
        throw new Exception('Failed to connect to RouterOS');
    }

    public function disconnect() {
        if ($this->connected) {
            fclose($this->socket);
            $this->connected = false;
            $this->socket = null;
        }
    }

    public function write($command, $param2 = true) {
        if ($command === '') {
            return;
        }
        
        $data = explode("\n", $command);
        
        foreach ($data as $line) {
            $len = strlen($line);
            if ($len < 0x80) {
                $this->writeByte($len);
            } elseif ($len < 0x4000) {
                $len |= 0x8000;
                $this->writeByte(($len >> 8) & 0xFF);
                $this->writeByte($len & 0xFF);
            } elseif ($len < 0x200000) {
                $len |= 0xC00000;
                $this->writeByte(($len >> 16) & 0xFF);
                $this->writeByte(($len >> 8) & 0xFF);
                $this->writeByte($len & 0xFF);
            } elseif ($len < 0x10000000) {
                $len |= 0xE0000000;
                $this->writeByte(($len >> 24) & 0xFF);
                $this->writeByte(($len >> 16) & 0xFF);
                $this->writeByte(($len >> 8) & 0xFF);
                $this->writeByte($len & 0xFF);
            } else {
                $this->writeByte(0xF0);
                $this->writeByte(($len >> 24) & 0xFF);
                $this->writeByte(($len >> 16) & 0xFF);
                $this->writeByte(($len >> 8) & 0xFF);
                $this->writeByte($len & 0xFF);
            }
            
            fwrite($this->socket, $line);
        }
        
        if ($param2) {
            fwrite($this->socket, chr(0));
        }
        
        return true;
    }

    public function read($parse = true) {
        $RESPONSE = array();
        $receiveddone = false;
        
        while (true) {
            $BYTE = ord(fread($this->socket, 1));
            $LENGTH = 0;
            
            // Get the length of the word
            if ($BYTE & 128) {
                if (($BYTE & 192) == 128) {
                    $LENGTH = (($BYTE & 63) << 8) + ord(fread($this->socket, 1));
                } else {
                    if (($BYTE & 224) == 192) {
                        $LENGTH = (($BYTE & 31) << 8) + ord(fread($this->socket, 1));
                        $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                    } else {
                        if (($BYTE & 240) == 224) {
                            $LENGTH = (($BYTE & 15) << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        } else {
                            $LENGTH = ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                            $LENGTH = ($LENGTH << 8) + ord(fread($this->socket, 1));
                        }
                    }
                }
            } else {
                $LENGTH = $BYTE;
            }
            
            if ($LENGTH > 0) {
                $_ = "";
                $retlen = 0;
                
                while ($retlen < $LENGTH) {
                    $toread = $LENGTH - $retlen;
                    $_ .= fread($this->socket, $toread);
                    $retlen = strlen($_);
                }
                
                $RESPONSE[] = $_;
                
                if ($_ == "!done") {
                    $receiveddone = true;
                }
            }
            
            if ($receiveddone) {
                break;
            }
        }
        
        if ($parse) {
            $PARSED = array();
            $CURRENT = null;
            $singlevalue = null;
            
            foreach ($RESPONSE as $x) {
                if (in_array($x, array('!fatal', '!re', '!trap'))) {
                    if ($x == '!re') {
                        $CURRENT = &$PARSED[];
                    } else {
                        $CURRENT = &$PARSED[$x][];
                    }
                } else if ($x != '!done') {
                    $MATCHES = array();
                    if (preg_match_all('/[^=]+/i', $x, $MATCHES)) {
                        if ($MATCHES[0][0] == 'ret') {
                            $singlevalue = $MATCHES[0][1];
                        }
                        $CURRENT[$MATCHES[0][0]] = (isset($MATCHES[0][1]) ? $MATCHES[0][1] : '');
                    }
                }
            }
            
            if (empty($PARSED) && !is_null($singlevalue)) {
                $PARSED = $singlevalue;
            }
            
            return $PARSED;
        } else {
            return $RESPONSE;
        }
    }

    private function writeByte($byte) {
        fwrite($this->socket, chr($byte));
        return true;
    }

    /**
     * MAC Address List Refresh
     * Refreshes the MAC address list on the router
     */
    public function refreshMacList() {
        if (!$this->connected) {
            throw new Exception('Not connected to RouterOS');
        }

        $this->write('/interface/ethernet/getall');
        return $this->read();
    }

    /**
     * Get System Resource
     * Returns system resource usage
     */
    public function getSystemResource() {
        if (!$this->connected) {
            throw new Exception('Not connected to RouterOS');
        }

        $this->write('/system/resource/print');
        return $this->read();
    }

    /**
     * Get Active Users
     * Returns list of active users/clients
     */
    public function getActiveUsers() {
        if (!$this->connected) {
            throw new Exception('Not connected to RouterOS');
        }

        $this->write('/ip/hotspot/active/print');
        return $this->read();
    }
}
