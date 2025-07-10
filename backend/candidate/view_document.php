<?php
/**
 * Document File Handler for Candidates
 * Save as: backend/candidate/view_document.php
 */

session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    http_response_code(401);
    die('Unauthorized access. Please log in.');
}

$seeker_id = $_SESSION['seeker_id'];

// Get the requested action and document ID
$action = $_GET['action'] ?? 'view';
$document_id = $_GET['document_id'] ?? null;

if (!$document_id) {
    http_response_code(400);
    die('Document ID required');
}

try {
    // Fetch document information with security check
    $stmt = $conn->prepare("
        SELECT * FROM candidate_documents 
        WHERE document_id = ? AND seeker_id = ?
    ");
    $stmt->execute([$document_id, $seeker_id]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$document) {
        http_response_code(404);
        die('Document not found or access denied');
    }
    
    // Construct file path
    $file_path = __DIR__ . '/../../' . $document['file_path'];
    
    // Security: Validate file path to prevent directory traversal
    $real_path = realpath($file_path);
    $upload_dir = realpath(__DIR__ . '/../../uploads/documents/');
    
    if (!$real_path || strpos($real_path, $upload_dir) !== 0) {
        http_response_code(403);
        die('Invalid file path');
    }
    
    // Check if file exists
    if (!file_exists($real_path)) {
        http_response_code(404);
        die('File not found on server');
    }
    
    // Get file info
    $file_size = filesize($real_path);
    $file_name = $document['original_filename'];
    $mime_type = $document['mime_type'];
    
    // Handle different actions
    if ($action === 'download') {
        // EXPLICIT DOWNLOAD - Show save dialog
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($real_path);
        exit;
        
    } else {
        // VIEW/PREVIEW in browser
        if ($mime_type === 'application/pdf') {
            // PDF - display directly in browser
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $file_name . '"');
            header('Content-Length: ' . $file_size);
            
            readfile($real_path);
            exit;
        } else {
            // Non-PDF - force download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Content-Length: ' . $file_size);
            
            readfile($real_path);
            exit;
        }
    }
    
} catch (Exception $e) {
    error_log("Document view error: " . $e->getMessage());
    http_response_code(500);
    die('Error accessing document: ' . $e->getMessage());
}
?>