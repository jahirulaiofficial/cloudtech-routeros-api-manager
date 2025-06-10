<?php
/**
 * MAC Refresh API Endpoint
 * CloudTech V4 - MAC Address List Refresh
 */

if (!defined('CLOUDTECH_V4')) {
    define('CLOUDTECH_V4', true);
}
require_once __DIR__ . '/../core/api_handler.php';
require_once __DIR__ . '/../config/app.php';

// Initialize API handler with authentication requirement
$api = new ApiHandler(true);

// Handle CORS
$api->handleCORS();

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $api->sendError('Invalid request method', 405);
}

try {
    // Get RouterOS API instance
    $routeros = $api->getRouterOS();
    
    // Get current MAC addresses
    $routeros->write('/interface/ethernet/print');
    $interfaces = $routeros->read();
    
    // Get DHCP leases
    $routeros->write('/ip/dhcp-server/lease/print');
    $dhcpLeases = $routeros->read();
    
    // Get hotspot active users
    $routeros->write('/ip/hotspot/active/print');
    $hotspotUsers = $routeros->read();
    
    // Format MAC address data
    $macData = [
        'ethernet' => array_map(function($interface) {
            return [
                'name' => $interface['name'],
                'mac_address' => $interface['mac-address'] ?? 'N/A',
                'type' => 'ethernet',
                'status' => $interface['running'] === 'true' ? 'active' : 'inactive'
            ];
        }, $interfaces),
        
        'dhcp' => array_map(function($lease) {
            return [
                'mac_address' => $lease['mac-address'],
                'ip_address' => $lease['address'],
                'hostname' => $lease['host-name'] ?? 'unknown',
                'type' => 'dhcp',
                'status' => $lease['status'],
                'last_seen' => $lease['last-seen'] ?? 'never'
            ];
        }, $dhcpLeases),
        
        'hotspot' => array_map(function($user) {
            return [
                'mac_address' => $user['mac-address'],
                'ip_address' => $user['address'],
                'username' => $user['user'] ?? 'anonymous',
                'type' => 'hotspot',
                'uptime' => $user['uptime'],
                'bytes_in' => $user['bytes-in'],
                'bytes_out' => $user['bytes-out']
            ];
        }, $hotspotUsers)
    ];
    
    // Optional: Update MAC binding if feature is enabled
    if ($config['app']['features']['mac_binding']['enabled'] && 
        $config['app']['features']['mac_binding']['auto_bind']) {
        foreach ($macData['dhcp'] as $device) {
            $routeros->write('/ip/dhcp-server/lease/make-static', false);
            $routeros->write('=mac-address=' . $device['mac_address']);
        }
    }
    
    // Log MAC refresh activity
    $api->logActivity('mac_refresh', [
        'total_ethernet' => count($macData['ethernet']),
        'total_dhcp' => count($macData['dhcp']),
        'total_hotspot' => count($macData['hotspot'])
    ]);
    
    // Cache MAC data if configured
    if (isset($config['mikrotik']['mac_timeout']) && $config['mikrotik']['mac_timeout'] > 0) {
        // Implementation for caching mechanism would go here
        // This could use Redis, Memcached, or file-based caching
    }
    
    // Disconnect from RouterOS
    $routeros->disconnect();
    
    // Send success response
    $api->sendSuccess([
        'mac_addresses' => $macData,
        'timestamp' => time(),
        'cache_timeout' => $config['mikrotik']['mac_timeout'] ?? 0
    ], 'MAC address list refreshed successfully');
    
} catch (Exception $e) {
    $api->sendError('Error refreshing MAC addresses: ' . $e->getMessage());
}
