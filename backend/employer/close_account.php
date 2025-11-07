<?php
/**
 * Close Account Endpoint - Employer (Updated for Google Accounts)
 * Implements soft delete with 30-day grace period
 * Handles both regular and Google OAuth accounts
 * Path: /backend/employer/close_account.php
 */

session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check authentication
if (!isset($_SESSION['employer_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated'
    ]);
    exit;
}

$employer_id = $_SESSION['employer_id'];
$password = $_POST['password'] ?? '';
$confirm_closure = $_POST['confirm_closure'] ?? '';

try {
    // Get account details including Google account status
    $stmt = $conn->prepare("
        SELECT ea.password_hash, ea.google_account, e.company_name
        FROM employer_accounts ea
        INNER JOIN employers e ON ea.employer_id = e.employer_id
        WHERE ea.employer_id = ?
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
    
    // HANDLE GOOGLE ACCOUNTS vs REGULAR ACCOUNTS
    if ($account['google_account'] == 1) {
        // Google account - require confirmation instead of password
        if ($confirm_closure !== 'yes') {
            echo json_encode([
                'success' => false, 
                'message' => 'Please confirm account closure'
            ]);
            exit;
        }
        // No password verification needed for Google accounts
        
    } else {
        // Regular account - require password verification
        if (empty($password)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Password is required to close your account'
            ]);
            exit;
        }
        
        // Verify password
        if (!password_verify($password, $account['password_hash'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Incorrect password. Please try again.'
            ]);
            exit;
        }
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // 1. Mark account as closed in employers
        $stmt = $conn->prepare("
            UPDATE employers 
            SET account_status = 'closed', 
                closed_at = NOW() 
            WHERE employer_id = ?
        ");
        $stmt->execute([$employer_id]);
        
        // 2. Mark all active job posts as closed
        $stmt = $conn->prepare("
            UPDATE job_posts 
            SET job_status = 'closed',
                updated_at = NOW()
            WHERE employer_id = ? 
            AND job_status = 'active'
        ");
        $stmt->execute([$employer_id]);
        
        // 3. Invalidate all API tokens
        $stmt = $conn->prepare("
            UPDATE api_tokens 
            SET is_active = 0 
            WHERE user_id = ? 
            AND user_type = 'employer'
        ");
        $stmt->execute([$employer_id]);
        
        // Commit transaction
        $conn->commit();
        
        // Clear session
        session_destroy();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Your company account has been closed. You have 30 days to reactivate by logging in again.'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Close employer account error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while closing your account. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Close employer account error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>