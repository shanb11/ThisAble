<?php
// backend/candidate/get_saved_jobs_count.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$seeker_id = get_seeker_id();

try {
    // Get saved jobs count
    $query = "SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = :seeker_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => (int)$result['count']
    ]);

} catch (Exception $e) {
    error_log("Error in get_saved_jobs_count.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch saved jobs count'
    ]);
}
?>