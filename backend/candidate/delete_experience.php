<?php
/**
 * Experience Delete Handler
 * Backend file: backend/candidate/delete_experience.php
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
$experience_id = $input['experience_id'] ?? null;

if (!$experience_id) {
    echo json_encode(['success' => false, 'message' => 'Experience ID required']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Verify ownership before deletion
    $check_stmt = $conn->prepare("SELECT seeker_id FROM experience WHERE experience_id = ?");
    $check_stmt->execute([$experience_id]);
    $experience = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$experience || $experience['seeker_id'] != $seeker_id) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Experience record not found or access denied']);
        exit;
    }
    
    // Delete experience record
    $delete_stmt = $conn->prepare("DELETE FROM experience WHERE experience_id = ? AND seeker_id = ?");
    $result = $delete_stmt->execute([$experience_id, $seeker_id]);
    
    if ($result && $delete_stmt->rowCount() > 0) {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Experience deleted successfully'
        ]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete experience']);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log('Delete experience error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>