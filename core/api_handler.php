<?php
/**
 * API Handler Class for CloudTech V4
 * Manages API requests, responses, and session handling
 */

class ApiHandler {
    private $response = [
        'success' => false,
        'message' => '',
        'data' => null
    ];

    /**
     * Initialize the API handler
     * @param bool $requireAuth Whether the endpoint requires authentication
     */
    public function __construct($requireAuth = true) {
        session_start();
        
        // Set JSON response headers
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');
        
        // Check authentication if required
        if ($requireAuth && !$this->isAuthenticated()) {
            $this->sendError('Unauthorized access', 401);
        }
    }

    /**
     * Check if user is authenticated
     * @return bool
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
    }

    /**
     * Set success response
     * @param mixed $data Response data
     * @param string $message Success message
     */
    public function sendSuccess($data = null, $message = 'Success') {
        $this->response['success'] = true;
        $this->response['message'] = $message;
        $this->response['data'] = $data;
        
        echo json_encode($this->response);
        exit;
    }

    /**
     * Set error response
     * @param string $message Error message
     * @param int $code HTTP response code
     */
    public function sendError($message = 'An error occurred', $code = 400) {
        http_response_code($code);
        
        $this->response['success'] = false;
        $this->response['message'] = $message;
        
        echo json_encode($this->response);
        exit;
    }

    /**
     * Validate required parameters
     * @param array $required Required parameters
     * @param array $source Source array ($_POST, $_GET, etc)
     */
    public function validateParams($required, $source) {
        foreach ($required as $param) {
            if (!isset($source[$param]) || empty($source[$param])) {
                $this->sendError("Missing required parameter: {$param}");
            }
        }
    }

    /**
     * Sanitize input
     * @param mixed $input Input to sanitize
     * @return mixed Sanitized input
     */
    public function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                $input[$key] = $this->sanitize($value);
            }
        } else {
            $input = trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        }
        return $input;
    }

    /**
     * Log API activity
     * @param string $action Action being performed
     * @param array $data Additional data to log
     */
    public function logActivity($action, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user' => $_SESSION['username'] ?? 'anonymous',
            'data' => $data
        ];

        // In production, implement proper logging mechanism
        error_log(json_encode($logEntry));
    }

    /**
     * Get RouterOS API instance
     * @return RouterosAPI
     */
    public function getRouterOS() {
        require_once __DIR__ . '/routeros_api.class.php';
        require_once __DIR__ . '/../config/mikrotik.php';

        $api = new RouterosAPI();
        
        try {
            $api->connect($config['mikrotik']['host'], 
                         $config['mikrotik']['username'], 
                         $config['mikrotik']['password'],
                         $config['mikrotik']['port'] ?? null);
            return $api;
        } catch (Exception $e) {
            $this->sendError('RouterOS connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle CORS preflight request
     */
    public function handleCORS() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            exit;
        }
    }
}
