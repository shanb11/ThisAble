<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in and is an employer
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$employer_id = $_SESSION['employer_id'];

try {
    $conn->beginTransaction();
    
    // Check if all required steps are complete
    $check_sql = "SELECT 
                    esp.basic_info_complete,
                    esp.description_complete,
                    esp.preferences_complete,
                    esp.social_complete,
                    e.company_name,
                    e.industry,
                    e.company_address
                  FROM employer_setup_progress esp
                  LEFT JOIN employers e ON esp.employer_id = e.employer_id
                  WHERE esp.employer_id = :employer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $progress_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$progress_data) {
        throw new Exception('Setup progress not found');
    }
    
    // Verify required basic info exists
    if (empty($progress_data['company_name']) || empty($progress_data['industry']) || empty($progress_data['company_address'])) {
        echo json_encode(['success' => false, 'message' => 'Basic company information is incomplete']);
        exit;
    }
    
    // Check if required steps are complete
    $required_steps = ['description_complete', 'preferences_complete', 'social_complete'];
    $missing_steps = [];
    
    foreach ($required_steps as $step) {
        if (!$progress_data[$step]) {
            $missing_steps[] = str_replace('_complete', '', $step);
        }
    }
    
    if (!empty($missing_steps)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please complete the following steps: ' . implode(', ', $missing_steps)
        ]);
        exit;
    }
    
    // Mark setup as complete
    $complete_sql = "UPDATE employer_setup_progress 
                    SET setup_complete = 1, completion_percentage = 100, updated_at = NOW() 
                    WHERE employer_id = :employer_id";
    $complete_stmt = $conn->prepare($complete_sql);
    $complete_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $complete_stmt->execute();
    
    if ($complete_stmt->rowCount() === 0) {
        throw new Exception('Failed to update setup completion status');
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Setup completed successfully! Welcome to ThisAble.',
        'progress' => 100,
        'redirect' => 'empdashboard.php'
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Setup completion error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to complete setup']);
}
?>