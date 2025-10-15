<?php
session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$employer_id = $_SESSION['employer_id'];

try {
    // Get all team members for this employer
    $stmt = $conn->prepare("
        SELECT 
            team_member_id,
            first_name,
            last_name,
            email,
            role,
            created_at
        FROM hiring_team
        WHERE employer_id = ?
        ORDER BY created_at ASC
    ");
    
    $stmt->execute([$employer_id]);
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $teamMembers
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching hiring team: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load hiring team members'
    ]);
}
?>