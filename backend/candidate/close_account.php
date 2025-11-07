<?php
/**
 * Close Account Endpoint - Candidate (Updated for Google Accounts)
 * Implements soft delete with 30-day grace period
 * Handles both regular and Google OAuth accounts
 * Path: /backend/candidate/close_account.php
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
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated'
    ]);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];
$password = $_POST['password'] ?? '';
$confirm_closure = $_POST['confirm_closure'] ?? '';

try {
    // Get account details including Google account status
    $stmt = $conn->prepare("
        SELECT ua.password_hash, ua.google_account, js.first_name, js.last_name
        FROM user_accounts ua
        INNER JOIN job_seekers js ON ua.seeker_id = js.seeker_id
        WHERE ua.seeker_id = ?
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
        // 1. Mark account as closed in job_seekers
        $stmt = $conn->prepare("
            UPDATE job_seekers 
            SET account_status = 'closed', 
                closed_at = NOW() 
            WHERE seeker_id = ?
        ");
        $stmt->execute([$seeker_id]);
        
        // 2. Withdraw all pending job applications
        $stmt = $conn->prepare("
            UPDATE job_applications 
            SET application_status = 'withdrawn',
                updated_at = NOW()
            WHERE seeker_id = ? 
            AND application_status IN ('pending', 'under_review', 'shortlisted')
        ");
        $stmt->execute([$seeker_id]);
        
        // 3. Invalidate all API tokens
        $stmt = $conn->prepare("
            UPDATE api_tokens 
            SET is_active = 0 
            WHERE user_id = ? 
            AND user_type = 'candidate'
        ");
        $stmt->execute([$seeker_id]);
        
        // Commit transaction
        $conn->commit();
        
        // Clear session
        session_destroy();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Your account has been closed. You have 30 days to reactivate by logging in again.'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Close account error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while closing your account. Please try again.'
    ]);
} catch (Exception $e) {
    error_log("Close account error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>