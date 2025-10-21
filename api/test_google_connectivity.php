<?php
// Create this as: C:\xampp\htdocs\ThisAble\api\test_google_connectivity.php
// This will test if your server can reach Google's APIs

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $log = [];
    $log[] = "=== GOOGLE API CONNECTIVITY TEST ===";
    $log[] = "Timestamp: " . date('Y-m-d H:i:s');
    
    // Test 1: Basic Google API endpoint
    $log[] = "Testing basic Google API connectivity...";
    
    $testUrl = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=invalid_token_test";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'ThisAble Test',
            'ignore_errors' => true // Don't treat HTTP errors as failures
        ]
    ]);
    
    $start_time = microtime(true);
    $response = @file_get_contents($testUrl, false, $context);
    $end_time = microtime(true);
    
    $duration = ($end_time - $start_time) * 1000;
    
    if ($response !== FALSE) {
        $log[] = "✅ Google API reachable in {$duration}ms";
        $log[] = "Response: " . substr($response, 0, 200) . "...";
        
        // Even with invalid token, we should get a JSON error response
        $json = json_decode($response, true);
        if ($json && isset($json['error'])) {
            $log[] = "✅ JSON response received (expected error for invalid token)";
        }
    } else {
        $log[] = "❌ Google API unreachable after {$duration}ms";
        $log[] = "This explains why your Google Sign-In hangs!";
    }
    
    // Test 2: Alternative test with curl if available
    if (function_exists('curl_init')) {
        $log[] = "Testing with cURL...";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ThisAble Test cURL');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
        
        $curl_start = microtime(true);
        $curl_response = curl_exec($ch);
        $curl_end = microtime(true);
        $curl_duration = ($curl_end - $curl_start) * 1000;
        
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_response !== FALSE && empty($curl_error)) {
            $log[] = "✅ cURL test successful in {$curl_duration}ms";
        } else {
            $log[] = "❌ cURL test failed in {$curl_duration}ms";
            $log[] = "cURL error: " . $curl_error;
        }
    } else {
        $log[] = "cURL not available for testing";
    }
    
    // Test 3: Check PHP settings that might affect outbound requests
    $log[] = "PHP Configuration:";
    $log[] = "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Yes' : 'No');
    $log[] = "user_agent: " . ini_get('user_agent');
    $log[] = "default_socket_timeout: " . ini_get('default_socket_timeout');
    
    $log[] = "=== TEST COMPLETE ===";
    
    echo json_encode([
        'success' => true,
        'message' => 'Google API connectivity test completed',
        'data' => [
            'test_log' => $log,
            'google_api_reachable' => $response !== FALSE,
            'response_time_ms' => $duration ?? 0
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage(),
        'data' => ['error' => $e->getMessage()]
    ]);
}
?>