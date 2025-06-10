<?php
/**
 * Login API Endpoint
 * CloudTech V4 - User Authentication
 */

if (!defined('CLOUDTECH_V4')) {
    define('CLOUDTECH_V4', true);
}
require_once __DIR__ . '/../core/api_handler.php';
require_once __DIR__ . '/../config/app.php';

// Initialize API handler without authentication requirement
$api = new ApiHandler(false);

// Handle CORS
$api->handleCORS();

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $api->sendError('Invalid request method', 405);
}

// Validate required parameters
$api->validateParams(['username', 'password'], $_POST);

// Sanitize input
$username = $api->sanitize($_POST['username']);
$password = $_POST['password']; // Don't sanitize password

// Check against configured admin credentials
if ($username === $config['app']['admin']['username']) {
    if (password_verify($password, $config['app']['admin']['password'])) {
        // Start session and set authentication
        session_start();
        $_SESSION['user_authenticated'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['last_activity'] = time();
        $_SESSION['user_role'] = 'admin';

        // Log successful login
        $api->logActivity('login_success', ['username' => $username]);

        // Return success response with session info
        $api->sendSuccess([
            'username' => $username,
            'role' => 'admin',
            'brand' => $config['app']['brand'],
            'features' => array_map(function($feature) {
                return $feature['enabled'];
            }, $config['app']['features'])
        ], 'Login successful');
    }
}

// If we get here, authentication failed
$api->logActivity('login_failed', ['username' => $username]);
$api->sendError('Invalid credentials', 401);
