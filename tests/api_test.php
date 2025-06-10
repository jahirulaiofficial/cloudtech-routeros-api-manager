<?php
/**
 * API Test Script for CloudTech V4
 * Tests all API endpoints and basic functionality
 */

class CloudTechApiTest {
    private $baseUrl;
    private $sessionCookie;
    
    public function __construct($baseUrl = 'http://localhost:8000') {
        $this->baseUrl = $baseUrl;
    }
    
    public function runTests() {
        echo "Starting CloudTech V4 API Tests\n";
        echo "================================\n\n";
        
        try {
            $this->testLogin();
            $this->testStatus();
            $this->testMacRefresh();
            $this->testLogout();
            
            echo "\nAll tests completed successfully! ✅\n";
        } catch (Exception $e) {
            echo "\nTest failed: " . $e->getMessage() . " ❌\n";
        }
    }
    
    private function testLogin() {
        echo "Testing Login API...\n";
        
        $response = $this->makeRequest('/api/login.php', 'POST', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        
        if (!$response['success']) {
            throw new Exception("Login failed: " . $response['message']);
        }
        
        echo "Login test passed ✅\n";
    }
    
    private function testStatus() {
        echo "Testing Status API...\n";
        
        $response = $this->makeRequest('/api/status.php', 'GET');
        
        if (!$response['success']) {
            throw new Exception("Status check failed: " . $response['message']);
        }
        
        // Verify required fields
        $required = ['system', 'network', 'features'];
        foreach ($required as $field) {
            if (!isset($response['data'][$field])) {
                throw new Exception("Missing required field in status response: {$field}");
            }
        }
        
        echo "Status test passed ✅\n";
    }
    
    private function testMacRefresh() {
        echo "Testing MAC Refresh API...\n";
        
        $response = $this->makeRequest('/api/mac_refresh.php', 'POST');
        
        if (!$response['success']) {
            throw new Exception("MAC refresh failed: " . $response['message']);
        }
        
        // Verify MAC address data structure
        if (!isset($response['data']['mac_addresses'])) {
            throw new Exception("Missing MAC addresses in response");
        }
        
        echo "MAC refresh test passed ✅\n";
    }
    
    private function testLogout() {
        echo "Testing Logout API...\n";
        
        $response = $this->makeRequest('/api/logout.php', 'POST');
        
        if (!$response['success']) {
            throw new Exception("Logout failed: " . $response['message']);
        }
        
        echo "Logout test passed ✅\n";
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-Requested-With: XMLHttpRequest'
            ]
        ];
        
        if ($this->sessionCookie) {
            $options[CURLOPT_COOKIE] = $this->sessionCookie;
        }
        
        if ($data && $method === 'POST') {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception("CURL Error: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Store session cookie if present
        $cookieHeader = curl_getinfo($ch, CURLINFO_COOKIELIST);
        if ($cookieHeader && !$this->sessionCookie) {
            $this->sessionCookie = $cookieHeader[0];
        }
        
        $decoded = json_decode($response, true);
        if (!$decoded) {
            throw new Exception("Invalid JSON response");
        }
        
        return $decoded;
    }
}

// Run the tests
$tester = new CloudTechApiTest();
$tester->runTests();
