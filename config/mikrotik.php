<?php
/**
 * MikroTik Router Configuration
 * CloudTech V4 - Router Connection Settings
 */

$config['mikrotik'] = [
    // Router connection details
    'host' => '192.168.1.1',  // Default IP, change according to your router
    'username' => 'admin',     // Default username, change for security
    'password' => '',         // Set your router password
    'port' => 8728,          // Default API port (8729 for SSL)
    
    // Connection settings
    'timeout' => 3,          // Connection timeout in seconds
    'ssl' => false,          // Use SSL connection
    'debug' => false,        // Enable debug mode
    
    // API settings
    'attempts' => 5,         // Connection attempts before failure
    'delay' => 1,           // Delay between attempts (seconds)
    
    // Default interfaces
    'default_interface' => 'ether1',
    
    // Hotspot settings
    'hotspot_interface' => 'wlan1',
    'hotspot_profile' => 'default',
    
    // MAC address settings
    'mac_timeout' => 300,    // MAC address cache timeout (seconds)
];

// Security check - prevent direct access
if (!defined('CLOUDTECH_V4')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access forbidden');
}
