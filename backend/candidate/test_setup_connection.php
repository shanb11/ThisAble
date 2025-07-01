<?php
// Create this file as: ../../backend/candidate/test_setup_connection.php
// This will help us debug the connection issues

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Test database connection
    require_once('../db.php');
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test query
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_seekers");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Connection test successful',
        'timestamp' => date('Y-m-d H:i:s'),
        'job_seekers_count' => $result['count'],
        'database_connected' => true,
        'php_version' => PHP_VERSION,
        'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection test failed: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>