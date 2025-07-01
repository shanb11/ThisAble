<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];
$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$suffix = trim($_POST['suffix'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');

// Validation
if (empty($first_name) || empty($last_name) || empty($email) || empty($contact_number)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Update job_seekers table
    $stmt = $conn->prepare("
        UPDATE job_seekers 
        SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, 
            contact_number = ?, city = ?, province = ?
        WHERE seeker_id = ?
    ");
    $stmt->execute([$first_name, $middle_name, $last_name, $suffix, $contact_number, $city, $province, $seeker_id]);
    
    // Check if email is different and not already taken
    $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $current_email = $stmt->fetchColumn();
    
    if ($current_email !== $email) {
        // Check if new email is already taken
        $stmt = $conn->prepare("SELECT COUNT(*) FROM user_accounts WHERE email = ? AND seeker_id != ?");
        $stmt->execute([$email, $seeker_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Email is already in use by another account');
        }
        
        // Update email
        $stmt = $conn->prepare("UPDATE user_accounts SET email = ? WHERE seeker_id = ?");
        $stmt->execute([$email, $seeker_id]);
    }
    
    $conn->commit();
    
    // Update session data
    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
    
    echo json_encode(['success' => true, 'message' => 'Contact information updated successfully']);
    
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>