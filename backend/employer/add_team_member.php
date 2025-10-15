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
if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email']) || !isset($input['role'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$first_name = trim($input['first_name']);
$last_name = trim($input['last_name']);
$email = trim($input['email']);
$role = trim($input['role']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Check if email already exists for this employer
    $checkStmt = $conn->prepare("
        SELECT team_member_id 
        FROM hiring_team 
        WHERE employer_id = ? AND email = ?
    ");
    $checkStmt->execute([$employer_id, $email]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A team member with this email already exists']);
        exit;
    }
    
    // Insert new team member
    $stmt = $conn->prepare("
        INSERT INTO hiring_team (employer_id, first_name, last_name, email, role, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$employer_id, $first_name, $last_name, $email, $role]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Team member added successfully',
        'team_member_id' => $conn->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    error_log("Error adding team member: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add team member'
    ]);
}
?>