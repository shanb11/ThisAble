<?php
// Create this file as: C:\xampp\htdocs\ThisAble\api\diagnostic.php
// This will help us see if the backend is hanging

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, Origin');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $log = [];
    $log[] = "=== BACKEND DIAGNOSTIC START ===";
    $log[] = "Timestamp: " . date('Y-m-d H:i:s');
    $log[] = "Request Method: " . $_SERVER['REQUEST_METHOD'];
    $log[] = "Request URI: " . $_SERVER['REQUEST_URI'];
    $log[] = "Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set');
    
    // Test 1: Basic PHP functionality
    $log[] = "PHP Version: " . phpversion();
    $log[] = "Memory Limit: " . ini_get('memory_limit');
    $log[] = "Max Execution Time: " . ini_get('max_execution_time');
    
    // Test 2: Read request body
    $rawBody = file_get_contents('php://input');
    $log[] = "Raw Body Length: " . strlen($rawBody);
    $log[] = "Raw Body Preview: " . substr($rawBody, 0, 100);
    
    // Test 3: Parse JSON
    if (!empty($rawBody)) {
        $jsonData = json_decode($rawBody, true);
        $log[] = "JSON Parse Result: " . (json_last_error() === JSON_ERROR_NONE ? 'Success' : 'Failed');
        $log[] = "JSON Error: " . json_last_error_msg();
        if ($jsonData) {
            $log[] = "JSON Keys: " . implode(', ', array_keys($jsonData));
        }
    }
    
    // Test 4: Database connection (if possible)
    try {
        if (file_exists('../config/database.php')) {
            $log[] = "Database config exists: Yes";
            // Don't actually include it to avoid errors, just check existence
        } else {
            $log[] = "Database config exists: No";
        }
    } catch (Exception $e) {
        $log[] = "Database test error: " . $e->getMessage();
    }
    
    // Test 5: Simulate Google token processing
    if (!empty($rawBody)) {
        $jsonData = json_decode($rawBody, true);
        if ($jsonData && isset($jsonData['accessToken'])) {
            $log[] = "Access Token provided: Yes";
            $log[] = "Access Token length: " . strlen($jsonData['accessToken']);
            
            // Test Google API call (without actually calling)
            $log[] = "Would call Google API with access token...";
            
            // Simulate some processing time
            usleep(100000); // 0.1 seconds
            $log[] = "Simulated processing complete";
        }
    }
    
    $log[] = "=== BACKEND DIAGNOSTIC END ===";
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Backend diagnostic completed successfully',
        'data' => [
            'diagnostic_log' => $log,
            'timestamp' => date('Y-m-d H:i:s'),
            'server_info' => [
                'php_version' => phpversion(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
            ]
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Backend diagnostic failed: ' . $e->getMessage(),
        'data' => [
            'error_details' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ]
    ]);
}
?>