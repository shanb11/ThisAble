<?php
/**
 * Delete Document API for Candidates
 * Save as: backend/candidate/delete_document.php
 */

session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['document_id'])) {
        throw new Exception('Document ID is required');
    }

    $document_id = intval($input['document_id']);

    // Start transaction
    $conn->beginTransaction();

    // Get document info with security check
    $stmt = $conn->prepare("
        SELECT file_path, document_name, original_filename 
        FROM candidate_documents 
        WHERE document_id = ? AND seeker_id = ?
    ");
    $stmt->execute([$document_id, $seeker_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        throw new Exception('Document not found or access denied');
    }

    // Delete file from filesystem
    $file_path = __DIR__ . '/../../' . $document['file_path'];
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            error_log("Failed to delete file: " . $file_path);
            // Continue anyway - don't fail just because file deletion failed
        }
    }

    // Delete from database
    $delete_stmt = $conn->prepare("
        DELETE FROM candidate_documents 
        WHERE document_id = ? AND seeker_id = ?
    ");
    $delete_stmt->execute([$document_id, $seeker_id]);

    if ($delete_stmt->rowCount() === 0) {
        throw new Exception('Document not found or already deleted');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Document deleted successfully',
        'data' => [
            'document_id' => $document_id,
            'document_name' => $document['document_name'] ?: $document['original_filename']
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Delete document error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>