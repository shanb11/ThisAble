<?php
/**
 * Check Account Type Endpoint
 * Returns whether the logged-in user is a Google account or regular account
 * Path: /backend/candidate/check_account_type.php
 */

session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated'
    ]);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

try {
    $stmt = $conn->prepare("
        SELECT google_account 
        FROM user_accounts 
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seeker_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        echo json_encode([
            'success' => false, 
            'message' => 'Account not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'is_google_account' => (bool)$account['google_account']
    ]);
    
} catch (PDOException $e) {
    error_log("Check account type error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred'
    ]);
}
?>