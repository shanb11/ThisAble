<?php
/**
 * API Test Endpoint for ThisAble Mobile
 * Save as: C:\xampp\htdocs\ThisAble\api\test.php
 */

// Basic CORS headers for Flutter
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get request information
    $timestamp = date('Y-m-d H:i:s');
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Detect platform from User-Agent
    $platform = 'Unknown';
    if (strpos($user_agent, 'Flutter') !== false) {
        if (strpos($user_agent, 'Android') !== false) {
            $platform = 'Flutter Android';
        } elseif (strpos($user_agent, 'iOS') !== false) {
            $platform = 'Flutter iOS';
        } else {
            $platform = 'Flutter App';
        }
    } elseif (strpos($user_agent, 'Chrome') !== false) {
        $platform = 'Web Browser (Chrome)';
    } elseif (strpos($user_agent, 'Safari') !== false) {
        $platform = 'Web Browser (Safari)';
    } elseif (strpos($user_agent, 'Firefox') !== false) {
        $platform = 'Web Browser (Firefox)';
    }
    
    // Special detection for emulators
    $is_emulator = false;
    if ($client_ip === '10.0.2.2' || strpos($user_agent, 'Android') !== false && strpos($user_agent, 'Chrome') !== false) {
        $is_emulator = 'Likely Android Emulator';
    } elseif ($client_ip === '127.0.0.1' || $client_ip === '::1') {
        $is_emulator = 'Localhost/Simulator';
    }
    
    // Success response with detailed info
    $response = [
        'success' => true,
        'message' => 'ThisAble API connection successful! 🎉',
        'data' => [
            'timestamp' => $timestamp,
            'client_info' => [
                'ip' => $client_ip,
                'platform' => $platform,
                'emulator_detected' => $is_emulator,
                'user_agent' => substr($user_agent, 0, 100) . '...' // Truncate for readability
            ],
            'server_info' => [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'xampp_detected' => strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false
            ],
            'api_info' => [
                'project' => 'ThisAble Mobile API',
                'version' => '1.0.0',
                'endpoint' => $_SERVER['REQUEST_URI'] ?? '/test.php',
                'method' => $_SERVER['REQUEST_METHOD']
            ]
        ]
    ];
    
    // Log successful connection
    error_log("ThisAble API Test - SUCCESS - Platform: $platform - IP: $client_ip - Time: $timestamp");
    
    // Send response
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    // Error response
    $error_response = [
        'success' => false,
        'message' => 'API test failed',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log error
    error_log("ThisAble API Test - ERROR - " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode($error_response, JSON_PRETTY_PRINT);
}
?>