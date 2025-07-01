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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All password fields are required']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit;
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
    exit;
}

try {
    // Get current password hash
    $stmt = $conn->prepare("SELECT password_hash FROM user_accounts WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $current_hash = $stmt->fetchColumn();
    
    if (!$current_hash) {
        echo json_encode(['success' => false, 'message' => 'User account not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $current_hash)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE user_accounts SET password_hash = ? WHERE seeker_id = ?");
    $stmt->execute([$new_hash, $seeker_id]);
    
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating password']);
}
?><?php
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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All password fields are required']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit;
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
    exit;
}

try {
    // Get current password hash
    $stmt = $conn->prepare("SELECT password_hash FROM user_accounts WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $current_hash = $stmt->fetchColumn();
    
    if (!$current_hash) {
        echo json_encode(['success' => false, 'message' => 'User account not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $current_hash)) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE user_accounts SET password_hash = ? WHERE seeker_id = ?");
    $stmt->execute([$new_hash, $seeker_id]);
    
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating password']);
}
?>