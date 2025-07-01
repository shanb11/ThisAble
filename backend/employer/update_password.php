<?php
/**
 * Update Password & Security Settings
 * Handles password changes and security preferences (with Google account support)
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Check if this is a Google account first
    $account_check_sql = "SELECT google_account, password_hash FROM employer_accounts WHERE employer_id = :employer_id";
    $account_check_stmt = $conn->prepare($account_check_sql);
    $account_check_stmt->execute(['employer_id' => $employer_id]);
    $account_info = $account_check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$account_info) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Account not found'
        ]);
        exit;
    }
    
    // If Google account, reject password change
    if ($account_info['google_account']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password cannot be changed for Google accounts. Please manage your password through your Google account settings.',
            'is_google_account' => true
        ]);
        exit;
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Extract input data
    $current_password = $input['current_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';
    $two_factor = isset($input['two_factor']) ? (bool)$input['two_factor'] : false;
    $remember_login = isset($input['remember_login']) ? (bool)$input['remember_login'] : false;
    
    // Validation
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = 'Current password is required';
    }
    
    if (empty($new_password)) {
        $errors[] = 'New password is required';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters long';
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $errors[0],
            'errors' => $errors
        ]);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $account_info['password_hash'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_sql = "
            UPDATE employer_accounts 
            SET password_hash = :password_hash,
                updated_at = NOW()
            WHERE employer_id = :employer_id
        ";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_result = $update_stmt->execute([
            'password_hash' => $new_password_hash,
            'employer_id' => $employer_id
        ]);
        
        if (!$update_result) {
            throw new Exception('Failed to update password');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully',
            'data' => [
                'two_factor' => $two_factor,
                'remember_login' => $remember_login,
                'password_changed' => true
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_password.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_password.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating password. Please try again.'
    ]);
}
?>