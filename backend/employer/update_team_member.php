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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['team_member_id']) || !isset($input['first_name']) || !isset($input['last_name']) || !isset($input['role'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$team_member_id = intval($input['team_member_id']);
$first_name = trim($input['first_name']);
$last_name = trim($input['last_name']);
$role = trim($input['role']);

try {
    // Verify that this team member belongs to the current employer
    $checkStmt = $conn->prepare("
        SELECT team_member_id 
        FROM hiring_team 
        WHERE team_member_id = ? AND employer_id = ?
    ");
    $checkStmt->execute([$team_member_id, $employer_id]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Team member not found']);
        exit;
    }
    
    // Update team member
    $stmt = $conn->prepare("
        UPDATE hiring_team 
        SET first_name = ?, last_name = ?, role = ?, updated_at = NOW()
        WHERE team_member_id = ? AND employer_id = ?
    ");
    
    $stmt->execute([$first_name, $last_name, $role, $team_member_id, $employer_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Team member updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error updating team member: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update team member'
    ]);
}
?>