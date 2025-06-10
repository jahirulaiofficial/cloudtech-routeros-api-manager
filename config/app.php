<?php
/**
 * Application Configuration
 * CloudTech V4 - Main Application Settings
 */

// Define application constants
define('CLOUDTECH_V4', true);
define('APP_VERSION', '4.0.0');
define('APP_NAME', 'CloudTech V4');

// Application settings
$config['app'] = [
    // Branding
    'brand' => [
        'name' => 'CloudTech V4',
        'footer' => 'Powered by CloudTech ISP',
        'logo' => '/public/assets/logo.png',
        'favicon' => '/public/assets/favicon.ico',
    ],
    
    // Session configuration
    'session' => [
        'lifetime' => 3600,           // Session lifetime in seconds
        'regenerate' => 1800,         // Session ID regeneration time
        'name' => 'CLOUDTECH_SESSID', // Session name
    ],
    
    // Security settings
    'security' => [
        'hash_algo' => PASSWORD_ARGON2ID,
        'token_length' => 32,
        'max_attempts' => 5,          // Max login attempts
        'lockout_time' => 900,        // Lockout time in seconds (15 minutes)
    ],
    
    // API settings
    'api' => [
        'rate_limit' => 100,          // Requests per minute
        'timeout' => 30,              // API request timeout
        'pagination' => 25,           // Default items per page
    ],
    
    // Future features (placeholders)
    'features' => [
        'whatsapp' => [
            'enabled' => false,
            'api_key' => '',
            'number' => '',
        ],
        'voucher' => [
            'enabled' => true,
            'expiry_alert' => true,
            'alert_threshold' => 24,   // Hours before expiry
        ],
        'statistics' => [
            'enabled' => true,
            'retention' => 30,         // Days to keep statistics
        ],
        'backup' => [
            'enabled' => true,
            'auto_backup' => true,
            'interval' => 'daily',     // backup interval
            'retention' => 7,          // Number of backups to keep
        ],
        'pppoe' => [
            'enabled' => true,
            'dashboard' => true,
        ],
        'mac_binding' => [
            'enabled' => true,
            'auto_bind' => false,
        ],
        'dns_firewall' => [
            'enabled' => true,
            'blacklist' => true,
        ],
    ],
    
    // Development settings
    'development' => [
        'debug' => false,             // Enable debug mode
        'display_errors' => false,     // Display PHP errors
        'log_errors' => true,         // Log errors to file
        'error_log' => 'logs/error.log',
    ],
    
    // Default admin credentials (change in production)
    'admin' => [
        'username' => 'Cloudtech',
        'email' => 'admin@cloudtech.isp',
        // Password: Cloudtech1234 (bcrypt hash)
        'password' => '$2y$10$7Q6v6q6v6q6v6q6v6q6v6u6v6q6v6q6v6q6v6q6v6q6v6q6v6q6v',
    ],
];

// Environment-specific settings
$config['environment'] = 'production'; // Options: development, production

// Timezone settings
date_default_timezone_set('UTC');

// Error reporting
if ($config['app']['development']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security check - prevent direct access
if (!defined('CLOUDTECH_V4')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access forbidden');
}
