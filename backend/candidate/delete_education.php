<?php
/**
 * Education Delete Handler
 * Backend file: backend/candidate/delete_education.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set content type
header('Content-Type: application/json');

// Security: Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$education_id = $input['education_id'] ?? null;

if (!$education_id) {
    echo json_encode(['success' => false, 'message' => 'Education ID required']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Verify ownership before deletion
    $check_stmt = $conn->prepare("SELECT seeker_id FROM education WHERE education_id = ?");
    $check_stmt->execute([$education_id]);
    $education = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$education || $education['seeker_id'] != $seeker_id) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Education record not found or access denied']);
        exit;
    }
    
    // Delete education record
    $delete_stmt = $conn->prepare("DELETE FROM education WHERE education_id = ? AND seeker_id = ?");
    $result = $delete_stmt->execute([$education_id, $seeker_id]);
    
    if ($result && $delete_stmt->rowCount() > 0) {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Education deleted successfully'
        ]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete education']);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log('Delete education error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>