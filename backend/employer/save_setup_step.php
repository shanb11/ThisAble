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
$step = $_POST['step'] ?? '';

try {
    $conn->beginTransaction();
    
    switch ($step) {
        case 'logo_complete':
            updateProgressStep($conn, $employer_id, 'logo_upload_complete', 1, 35);
            break;
            
        case 'description_complete':
            updateProgressStep($conn, $employer_id, 'description_complete', 1, 50);
            break;
            
        case 'preferences_complete':
            updateProgressStep($conn, $employer_id, 'preferences_complete', 1, 70);
            break;
            
        case 'social_complete':
            updateProgressStep($conn, $employer_id, 'social_complete', 1, 85);
            break;
            
        default:
            throw new Exception('Invalid step');
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Progress updated successfully']);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Setup step error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update progress']);
}

function updateProgressStep($conn, $employer_id, $step_column, $value, $percentage) {
    // Check if progress record exists
    $check_sql = "SELECT progress_id FROM employer_setup_progress WHERE employer_id = :employer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $result = $check_stmt->fetch();
    
    if (!$result) {
        // Create new progress record
        $insert_sql = "INSERT INTO employer_setup_progress 
                      (employer_id, $step_column, completion_percentage, updated_at) 
                      VALUES (:employer_id, :value, :percentage, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':value', $value, PDO::PARAM_INT);
        $insert_stmt->bindParam(':percentage', $percentage, PDO::PARAM_INT);
        $insert_stmt->execute();
    } else {
        // Update existing progress record
        $update_sql = "UPDATE employer_setup_progress 
                      SET $step_column = :value, completion_percentage = :percentage, updated_at = NOW() 
                      WHERE employer_id = :employer_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':value', $value, PDO::PARAM_INT);
        $update_stmt->bindParam(':percentage', $percentage, PDO::PARAM_INT);
        $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $update_stmt->execute();
    }
}
?>