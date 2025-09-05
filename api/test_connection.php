<?php
header('Content-Type: application/json');

try {
    echo json_encode(['step' => 'Starting test']);
    
    // Test 1: Basic file includes
    require_once 'config/cors.php';
    echo json_encode(['step' => 'CORS loaded']);
    
    require_once 'config/response.php';
    echo json_encode(['step' => 'Response loaded']);
    
    require_once 'config/database.php';
    echo json_encode(['step' => 'Database config loaded']);
    
    // Test 2: Database connection
    $conn = ApiDatabase::getConnection();
    echo json_encode(['step' => 'Database connection successful']);
    
    // Test 3: API tokens table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM api_tokens");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    echo json_encode(['step' => "API tokens table has $count records"]);
    
    // Test 4: Token generation
    $testToken = ApiDatabase::generateApiToken(4, 'candidate');  // Use existing user ID 4
    echo json_encode(['step' => 'Token generation', 'success' => $testToken !== false]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ]);
}
?>