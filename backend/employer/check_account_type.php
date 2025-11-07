<?php
/**
 * Check Account Type Endpoint - Employer
 * Returns whether the logged-in employer is a Google account or regular account
 * Path: /backend/employer/check_account_type.php
 */

session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['employer_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated'
    ]);
    exit;
}

$employer_id = $_SESSION['employer_id'];

try {
    $stmt = $conn->prepare("
        SELECT google_account 
        FROM employer_accounts 
        WHERE employer_id = ?
    ");
    $stmt->execute([$employer_id]);
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