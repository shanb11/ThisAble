<?php
/**
 * API Test Endpoint for ThisAble Mobile API
 * Tests database connection and API functionality
 */

require_once 'config/cors.php';
require_once 'config/response.php';
require_once 'config/database.php';

try {
    // Test database connection
    $conn = ApiDatabase::getConnection();
    
    // Test query
    $stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM job_seekers");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test response
    ApiResponse::success([
        'message' => 'ThisAble Mobile API is working!',
        'database_status' => 'Connected',
        'total_candidates' => $result['user_count'],
        'server_time' => date('Y-m-d H:i:s'),
        'api_version' => '1.0.0'
    ], "API test successful");
    
} catch (Exception $e) {
    ApiResponse::serverError("API test failed: " . $e->getMessage());
}
?>