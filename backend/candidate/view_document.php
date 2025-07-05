<?php
/**
 * View Document API for Candidates and Employers
 * Save as: backend/candidate/view_document.php
 */

session_start();
require_once '../db.php';

// Check if user is logged in (candidate or employer)
$is_candidate = isset($_SESSION['seeker_id']);
$is_employer = isset($_SESSION['employer_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if (!$is_candidate && !$is_employer) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in.'
    ]);
    exit();
}

// Get document ID from request
$document_id = $_GET['document_id'] ?? null;
$seeker_id = $_GET['seeker_id'] ?? null; // For employer access

if (!$document_id || !is_numeric($document_id)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Valid document ID is required'
    ]);
    exit();
}

try {
    // Build query based on user type
    if ($is_candidate) {
        // Candidates can only view their own documents
        $sql = "
            SELECT 
                document_id,
                document_type,
                document_name,
                original_filename,
                file_path,
                mime_type,
                file_size
            FROM candidate_documents 
            WHERE document_id = :document_id AND seeker_id = :seeker_id
        ";
        $params = [
            'document_id' => $document_id,
            'seeker_id' => $_SESSION['seeker_id']
        ];
    } else {
        // Employers can view documents of applicants
        if (!$seeker_id) {
            throw new Exception('Seeker ID is required for employer access');
        }
        
        $sql = "
            SELECT DISTINCT
                cd.document_id,
                cd.document_type,
                cd.document_name,
                cd.original_filename,
                cd.file_path,
                cd.mime_type,
                cd.file_size
            FROM candidate_documents cd
            INNER JOIN job_applications ja ON cd.seeker_id = ja.seeker_id
            INNER JOIN job_posts jp ON ja.job_id = jp.job_id
            WHERE cd.document_id = :document_id 
            AND cd.seeker_id = :seeker_id 
            AND jp.employer_id = :employer_id
        ";
        $params = [
            'document_id' => $document_id,
            'seeker_id' => $seeker_id,
            'employer_id' => $_SESSION['employer_id']
        ];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        throw new Exception('Document not found or access denied');
    }

    // Check if file exists
    $file_path = '../../' . $document['file_path'];
    if (!file_exists($file_path)) {
        throw new Exception('Document file not found on server');
    }

    // Determine if this is a download request
    $download = isset($_GET['download']) && $_GET['download'] === '1';

    // Set appropriate headers
    header('Content-Type: ' . $document['mime_type']);
    header('Content-Length: ' . $document['file_size']);
    
    if ($download) {
        header('Content-Disposition: attachment; filename="' . $document['original_filename'] . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $document['original_filename'] . '"');
    }

    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');

    // Output file content
    readfile($file_path);

} catch (Exception $e) {
    error_log("View document error: " . $e->getMessage());
    
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>