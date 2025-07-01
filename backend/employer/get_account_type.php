<?php
/**
 * Get Account Type Information
 * Checks if account is Google-based and other account details
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get account information
    $sql = "
        SELECT 
            ea.google_account,
            ea.email,
            ea.email_verified,
            ea.last_login,
            ec.first_name,
            ec.last_name
        FROM employer_accounts ea
        LEFT JOIN employer_contacts ec ON ea.employer_id = ec.employer_id AND ec.is_primary = 1
        WHERE ea.employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account) {
        throw new Exception('Account not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'is_google_account' => (bool)($account['google_account'] ?? false),
            'email' => $account['email'] ?? '',
            'email_verified' => (bool)($account['email_verified'] ?? false),
            'can_change_password' => !(bool)($account['google_account'] ?? false),
            'account_name' => trim(($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? '')),
            'last_login' => $account['last_login']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_account_type.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_account_type.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>