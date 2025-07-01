<?php
/**
 * Delete Resume Handler
 * Backend file: backend/candidate/delete_resume.php
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
$resume_id = $input['resume_id'] ?? null;

if (!$resume_id) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Fetch resume information with security check
    $stmt = $conn->prepare("
        SELECT * FROM resumes 
        WHERE resume_id = ? AND seeker_id = ?
    ");
    $stmt->execute([$resume_id, $seeker_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Resume not found or access denied']);
        exit;
    }
    
    // Delete file from filesystem
    $file_path = __DIR__ . '/../../' . $resume['file_path'];
    $real_path = realpath($file_path);
    
    if ($real_path && file_exists($real_path)) {
        // Security: Validate file path to prevent directory traversal
        $upload_dir = realpath(__DIR__ . '/../../uploads/resumes/');
        
        if (strpos($real_path, $upload_dir) === 0) {
            if (!unlink($real_path)) {
                error_log("Failed to delete resume file: " . $real_path);
                // Continue with database deletion even if file deletion fails
            }
        }
    }
    
    // Delete from database
    $delete_stmt = $conn->prepare("DELETE FROM resumes WHERE resume_id = ? AND seeker_id = ?");
    $delete_stmt->execute([$resume_id, $seeker_id]);
    
    if ($delete_stmt->rowCount() === 0) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete resume from database']);
        exit;
    }
    
    // Commit transaction
    $conn->commit();
    
    // Log the deletion
    error_log("Resume deleted: ID {$resume_id} by seeker {$seeker_id}");
    
    echo json_encode([
        'success' => true, 
        'message' => 'Resume deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log('Delete resume error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>