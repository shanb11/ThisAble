<?php
// backend/employer/view_document.php - NEW FILE for Phase 4
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';

try {
    // Check if employer is logged in
    if (!isset($_SESSION['employer_id'])) {
        http_response_code(401);
        exit('Unauthorized access');
    }

    $employer_id = $_SESSION['employer_id'];
    $document_path = $_GET['path'] ?? null;

    if (!$document_path) {
        http_response_code(400);
        exit('Document path is required');
    }

    // Security: Verify the document belongs to an applicant for this employer's jobs
    $securityQuery = "
        SELECT cd.*, js.seeker_id 
        FROM candidate_documents cd
        JOIN job_seekers js ON cd.seeker_id = js.seeker_id
        JOIN job_applications ja ON js.seeker_id = ja.seeker_id
        JOIN job_posts jp ON ja.job_id = jp.job_id
        WHERE cd.file_path = :file_path 
        AND jp.employer_id = :employer_id
        LIMIT 1
    ";

    $securityStmt = $conn->prepare($securityQuery);
    $securityStmt->bindParam(':file_path', $document_path);
    $securityStmt->bindParam(':employer_id', $employer_id);
    $securityStmt->execute();

    $document = $securityStmt->fetch(PDO::FETCH_ASSOC);

    if (!$document) {
        http_response_code(403);
        exit('Access denied to this document');
    }

    // Build full file path
    $full_path = '../../' . $document_path;
    
    if (!file_exists($full_path)) {
        http_response_code(404);
        exit('Document not found');
    }

    // Set appropriate headers based on file type
    $mime_type = $document['mime_type'] ?: mime_content_type($full_path);
    
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($full_path));
    header('Content-Disposition: inline; filename="' . basename($document['document_name']) . '"');

    // Output the file
    readfile($full_path);

} catch (Exception $e) {
    http_response_code(500);
    exit('Error loading document: ' . $e->getMessage());
}
?>