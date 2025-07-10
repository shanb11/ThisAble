<?php
/**
 * Get Documents API for Candidates - COMPLETE VERSION
 * Save as: backend/candidate/get_documents.php
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

$seeker_id = $_SESSION['seeker_id'];

/**
 * Format file size to human readable format
 */
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

try {
    // Get all documents for the user
    $sql = "
        SELECT 
            document_id,
            document_type,
            document_name,
            original_filename,
            file_path,
            file_size,
            mime_type,
            upload_date,
            is_verified,
            verification_notes
        FROM candidate_documents 
        WHERE seeker_id = :seeker_id 
        ORDER BY document_type, upload_date DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['seeker_id' => $seeker_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format documents by type
    $formatted_documents = [
        'diploma' => [],
        'certificate' => [],
        'license' => [],
        'other' => []
    ];

    foreach ($documents as $doc) {
        // Format file size
        $doc['formatted_size'] = formatFileSize($doc['file_size']);
        
        // Format upload date
        $doc['formatted_date'] = date('M j, Y', strtotime($doc['upload_date']));
        
        // Add view URL
        $doc['view_url'] = '../../backend/candidate/view_document.php?document_id=' . $doc['document_id'];
        
        // Check if file actually exists
        $doc['file_exists'] = file_exists('../../' . $doc['file_path']);
        
        $formatted_documents[$doc['document_type']][] = $doc;
    }

    // Get document counts
    $counts = [
        'total' => count($documents),
        'diploma' => count($formatted_documents['diploma']),
        'certificate' => count($formatted_documents['certificate']),
        'license' => count($formatted_documents['license']),
        'other' => count($formatted_documents['other']),
        'verified' => count(array_filter($documents, function($doc) {
            return $doc['is_verified'] == 1;
        }))
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'documents' => $formatted_documents,
            'counts' => $counts,
            'seeker_id' => $seeker_id
        ],
        'message' => 'Documents retrieved successfully'
    ]);

} catch (Exception $e) {
    error_log("Get documents error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving documents: ' . $e->getMessage()
    ]);
}
?>