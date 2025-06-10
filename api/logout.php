<?php
/**
 * Logout API Endpoint
 * CloudTech V4 - Session Termination
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

// Log the logout activity
$api->logActivity('logout', [
    'username' => $_SESSION['username'] ?? 'unknown'
]);

// Destroy the session
session_start();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Send success response
$api->sendSuccess(null, 'Successfully logged out');
