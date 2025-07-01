<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');

try {
    // Fetch all industries from database
    $stmt = $conn->prepare("SELECT industry_id, industry_name, industry_icon 
                           FROM industries 
                           ORDER BY industry_name ASC");
    $stmt->execute();
    
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($industries) {
        echo json_encode([
            'status' => 'success',
            'data' => $industries
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => [],
            'message' => 'No industries found'
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Get industries error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load industries'
    ]);
}
?>