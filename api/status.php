<?php
/**
 * Status API Endpoint
 * CloudTech V4 - System and Router Status
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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $api->sendError('Invalid request method', 405);
}

try {
    // Get RouterOS API instance
    $routeros = $api->getRouterOS();
    
    // Get system resources
    $resources = $routeros->getSystemResource();
    
    // Get active users/clients
    $activeUsers = $routeros->getActiveUsers();
    
    // Get system health
    $routeros->write('/system/health/print');
    $health = $routeros->read();
    
    // Get interface statistics
    $routeros->write('/interface/print');
    $interfaces = $routeros->read();
    
    // Format response data
    $status = [
        'system' => [
            'version' => APP_VERSION,
            'uptime' => $resources[0]['uptime'] ?? 'Unknown',
            'cpu_load' => $resources[0]['cpu-load'] ?? 0,
            'free_memory' => $resources[0]['free-memory'] ?? 0,
            'total_memory' => $resources[0]['total-memory'] ?? 0,
            'free_hdd_space' => $resources[0]['free-hdd-space'] ?? 0,
            'temperature' => $health[0]['temperature'] ?? 'N/A',
            'voltage' => $health[0]['voltage'] ?? 'N/A',
        ],
        'network' => [
            'active_users' => count($activeUsers),
            'interfaces' => array_map(function($interface) {
                return [
                    'name' => $interface['name'],
                    'type' => $interface['type'],
                    'running' => $interface['running'] === 'true',
                    'disabled' => $interface['disabled'] === 'true',
                    'rx_byte' => $interface['rx-byte'] ?? 0,
                    'tx_byte' => $interface['tx-byte'] ?? 0,
                ];
            }, $interfaces),
        ],
        'features' => [
            'whatsapp' => $config['app']['features']['whatsapp']['enabled'],
            'voucher' => $config['app']['features']['voucher']['enabled'],
            'statistics' => $config['app']['features']['statistics']['enabled'],
            'backup' => $config['app']['features']['backup']['enabled'],
            'pppoe' => $config['app']['features']['pppoe']['enabled'],
            'mac_binding' => $config['app']['features']['mac_binding']['enabled'],
            'dns_firewall' => $config['app']['features']['dns_firewall']['enabled'],
        ],
        'timestamp' => time(),
    ];
    
    // Log status check
    $api->logActivity('status_check', [
        'cpu_load' => $status['system']['cpu_load'],
        'active_users' => $status['network']['active_users']
    ]);
    
    // Disconnect from RouterOS
    $routeros->disconnect();
    
    // Send success response
    $api->sendSuccess($status, 'Status retrieved successfully');
    
} catch (Exception $e) {
    $api->sendError('Error retrieving status: ' . $e->getMessage());
}
