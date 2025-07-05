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

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

try {
    // Get document ID from request
    $input = json_decode(file_get_contents('php://input'), true);
    $document_id = $input['document_id'] ?? $_POST['document_id'] ?? null;

    if (!$document_id || !is_numeric($document_id)) {
        throw new Exception('Valid document ID is required');
    }

    // Start transaction
    $conn->beginTransaction();

    // Get document details and verify ownership
    $select_sql = "
        SELECT 
            document_id,
            document_type,
            document_name,
            file_path,
            original_filename
        FROM candidate_documents 
        WHERE document_id = :document_id AND seeker_id = :seeker_id
    ";

    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->execute([
        'document_id' => $document_id,
        'seeker_id' => $seeker_id
    ]);

    $document = $select_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        throw new Exception('Document not found or you do not have permission to delete it');
    }

    // Delete file from filesystem
    $file_path = '../../' . $document['file_path'];
    $file_deleted = false;

    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            $file_deleted = true;
        } else {
            error_log("Warning: Could not delete file: " . $file_path);
        }
    } else {
        $file_deleted = true; // File doesn't exist
    }

    // Delete database record
    $delete_sql = "DELETE FROM candidate_documents WHERE document_id = :document_id AND seeker_id = :seeker_id";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->execute([
        'document_id' => $document_id,
        'seeker_id' => $seeker_id
    ]);

    if ($delete_stmt->rowCount() === 0) {
        throw new Exception('Failed to delete document from database');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => ucfirst($document['document_type']) . ' deleted successfully',
        'data' => [
            'deleted_document' => [
                'document_id' => $document['document_id'],
                'document_type' => $document['document_type'],
                'document_name' => $document['document_name']
            ],
            'file_deleted' => $file_deleted
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
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