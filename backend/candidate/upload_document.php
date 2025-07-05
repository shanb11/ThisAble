<?php
/**
 * Upload Document API for Candidates
 * Save as: backend/candidate/upload_document.php
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
    // Validate required fields
    if (!isset($_POST['document_type']) || !isset($_FILES['document_file'])) {
        throw new Exception('Document type and file are required');
    }

    $document_type = trim($_POST['document_type']);
    $document_name = trim($_POST['document_name'] ?? '');
    $file = $_FILES['document_file'];

    // Validate document type
    $allowed_types = ['diploma', 'certificate', 'license', 'other'];
    if (!in_array($document_type, $allowed_types)) {
        throw new Exception('Invalid document type');
    }

    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed: ' . $file['error']);
    }

    // Validate file type (PDF only)
    $allowed_mime_types = ['application/pdf'];
    $file_mime_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_mime_type, $allowed_mime_types)) {
        throw new Exception('Only PDF files are allowed');
    }

    // Validate file size (max 10MB)
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        throw new Exception('File size exceeds 10MB limit');
    }

    // Create upload directory
    $base_upload_dir = '../../uploads/documents/';
    $type_dir = $document_type . 's/'; // diplomas, certificates, etc.
    $upload_dir = $base_upload_dir . $type_dir;

    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = $seeker_id . '_' . $document_type . '_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $unique_filename;
    $relative_path = 'uploads/documents/' . $type_dir . $unique_filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to save uploaded file');
    }

    // Start database transaction
    $conn->beginTransaction();

    // For diplomas, replace existing one
    if ($document_type === 'diploma') {
        $check_sql = "SELECT document_id, file_path FROM candidate_documents 
                      WHERE seeker_id = :seeker_id AND document_type = 'diploma'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute(['seeker_id' => $seeker_id]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Delete old file
            $old_file_path = '../../' . $existing['file_path'];
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
            
            // Delete database record
            $delete_sql = "DELETE FROM candidate_documents WHERE document_id = :document_id";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->execute(['document_id' => $existing['document_id']]);
        }
    }

    // Insert document record
    $insert_sql = "
        INSERT INTO candidate_documents (
            seeker_id, 
            document_type, 
            document_name, 
            original_filename, 
            file_path, 
            file_size, 
            mime_type, 
            upload_date
        ) VALUES (
            :seeker_id, 
            :document_type, 
            :document_name, 
            :original_filename, 
            :file_path, 
            :file_size, 
            :mime_type, 
            NOW()
        )
    ";

    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->execute([
        'seeker_id' => $seeker_id,
        'document_type' => $document_type,
        'document_name' => $document_name ?: $file['name'],
        'original_filename' => $file['name'],
        'file_path' => $relative_path,
        'file_size' => $file['size'],
        'mime_type' => $file_mime_type
    ]);

    $document_id = $conn->lastInsertId();

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => ucfirst($document_type) . ' uploaded successfully',
        'data' => [
            'document_id' => $document_id,
            'document_type' => $document_type,
            'document_name' => $document_name ?: $file['name'],
            'file_size' => $file['size'],
            'upload_date' => date('Y-m-d H:i:s'),
            'file_path' => $relative_path
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }

    // Delete uploaded file if exists
    if (isset($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }

    error_log("Document upload error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>