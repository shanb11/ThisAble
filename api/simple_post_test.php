<?php
// Create this as: C:\xampp\htdocs\ThisAble\api\simple_post_test.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $body = file_get_contents('php://input');
    
    echo json_encode([
        'success' => true,
        'message' => 'Simple POST test successful',
        'data' => [
            'method' => $method,
            'timestamp' => date('Y-m-d H:i:s'),
            'body_received' => $body,
            'body_length' => strlen($body)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Test failed: ' . $e->getMessage()
    ]);
}
?>