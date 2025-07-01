<?php
// Prevent any PHP errors from being displayed in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set the content type header first thing
header('Content-Type: application/json');

// Start output buffering
ob_start();

try {
    // Log basic information
    error_log("save_skills.php called - simplified test version");
    
    // Return a simple success response without doing any database operations
    $response = ['success' => true, 'message' => 'Test response - no actual save performed'];
    
    // Clear buffer and output JSON
    ob_end_clean();
    echo json_encode($response);
    exit;
} catch (Exception $e) {
    // Clear buffer
    ob_end_clean();
    
    // Log error
    error_log("ERROR in test save_skills.php: " . $e->getMessage());
    
    // Return error JSON
    echo json_encode(['success' => false, 'message' => 'Test error: ' . $e->getMessage()]);
    exit;
}
?>