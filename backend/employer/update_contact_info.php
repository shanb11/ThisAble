<?php
/**
 * Update Contact Information
 * Handles contact person details and password updates
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
    // Validate session and get employer ID using existing function
    $employer_id = validateEmployerSession();
    
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
    
    // Extract and validate input data
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $position = trim($input['position'] ?? '');
    $contact_number = trim($input['contact_number'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation array
    $errors = [];
    
    // Validate required fields
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    } elseif (strlen($first_name) > 100) {
        $errors[] = 'First name must be less than 100 characters';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    } elseif (strlen($last_name) > 100) {
        $errors[] = 'Last name must be less than 100 characters';
    }
    
    if (empty($position)) {
        $errors[] = 'Position is required';
    } elseif (strlen($position) > 100) {
        $errors[] = 'Position must be less than 100 characters';
    }
    
    if (empty($contact_number)) {
        $errors[] = 'Contact number is required';
    } elseif (strlen($contact_number) > 20) {
        $errors[] = 'Contact number must be less than 20 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email must be less than 255 characters';
    }
    
    // Password validation (only if provided and not placeholder)
    $update_password = false;
    if (!empty($password) && $password !== '••••••••') {
        $update_password = true;
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
    }
    
    // Return validation errors
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $errors[0],
            'errors' => $errors
        ]);
        exit;
    }
    
    // Check if email is already used by another employer (excluding current one)
    $email_check_sql = "
        SELECT ea.employer_id 
        FROM employer_accounts ea
        WHERE ea.email = :email AND ea.employer_id != :employer_id
        LIMIT 1
    ";
    
    $email_check_stmt = $conn->prepare($email_check_sql);
    $email_check_stmt->execute([
        'email' => $email,
        'employer_id' => $employer_id
    ]);
    
    if ($email_check_stmt->fetch()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'This email address is already in use by another account'
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update employer contact
        $update_contact_sql = "
            UPDATE employer_contacts 
            SET first_name = :first_name,
                last_name = :last_name,
                position = :position,
                contact_number = :contact_number,
                email = :email,
                updated_at = NOW()
            WHERE employer_id = :employer_id AND is_primary = 1
        ";
        
        $update_contact_stmt = $conn->prepare($update_contact_sql);
        $contact_result = $update_contact_stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'position' => $position,
            'contact_number' => $contact_number,
            'email' => $email,
            'employer_id' => $employer_id
        ]);
        
        if (!$contact_result) {
            throw new Exception('Failed to update contact information');
        }
        
        $password_updated = false;
        
        // Update employer account email and password if needed
        if ($update_password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $update_account_sql = "
                UPDATE employer_accounts 
                SET email = :email,
                    password_hash = :password_hash,
                    updated_at = NOW()
                WHERE employer_id = :employer_id
            ";
            
            $update_account_stmt = $conn->prepare($update_account_sql);
            $account_result = $update_account_stmt->execute([
                'email' => $email,
                'password_hash' => $password_hash,
                'employer_id' => $employer_id
            ]);
            
            if (!$account_result) {
                throw new Exception('Failed to update account password');
            }
            
            $password_updated = true;
        } else {
            // Update only email
            $update_account_sql = "
                UPDATE employer_accounts 
                SET email = :email,
                    updated_at = NOW()
                WHERE employer_id = :employer_id
            ";
            
            $update_account_stmt = $conn->prepare($update_account_sql);
            $account_result = $update_account_stmt->execute([
                'email' => $email,
                'employer_id' => $employer_id
            ]);
            
            if (!$account_result) {
                throw new Exception('Failed to update account email');
            }
        }
        
        // Update session data
        $_SESSION['employer_name'] = $first_name . ' ' . $last_name;
        $_SESSION['employer_email'] = $email;
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Contact information updated successfully',
            'data' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'full_name' => $first_name . ' ' . $last_name,
                'position' => $position,
                'contact_number' => $contact_number,
                'email' => $email,
                'password_updated' => $password_updated
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_contact_info.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_contact_info.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating contact information. Please try again.'
    ]);
}
?>