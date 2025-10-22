<?php
/**
 * Mobile Login API for ThisAble
 * ✅ FIXED: PostgreSQL/Supabase compatible (case-insensitive email)
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::validationError(['input' => 'Invalid JSON input']);
    }
    
    // Extract and validate input
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation
    $errors = [];
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    if (!empty($errors)) {
        ApiResponse::validationError($errors, "Validation failed");
    }
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ✅ FIXED: Case-insensitive email lookup for PostgreSQL
    $stmt = $conn->prepare("SELECT ua.account_id, ua.seeker_id, ua.email, ua.password_hash, ua.google_account,
                                  js.first_name, js.last_name, js.setup_complete, js.disability_id,
                                  dt.disability_name,
                                  pwd.is_verified as pwd_verified
                           FROM user_accounts ua 
                           JOIN job_seekers js ON ua.seeker_id = js.seeker_id 
                           LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
                           LEFT JOIN pwd_ids pwd ON js.seeker_id = pwd.seeker_id
                           WHERE LOWER(ua.email) = LOWER(:email)");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password (skip if Google account)
        if ($user['google_account'] == 1 || password_verify($password, $user['password_hash'])) {
            
            // Generate API token for mobile
            $token = ApiDatabase::generateApiToken($user['seeker_id'], 'candidate');
            
            if (!$token) {
                ApiResponse::serverError("Failed to generate authentication token");
            }
            
            // Check setup completion status (using your existing logic)
            $setupComplete = false;
            
            if (isset($user['setup_complete']) && $user['setup_complete'] == 1) {
                $setupComplete = true;
            } else {
                // Check if they have selected skills (your existing logic)
                $skillCheckStmt = $conn->prepare("SELECT COUNT(*) as skill_count FROM seeker_skills WHERE seeker_id = :seeker_id");
                $skillCheckStmt->bindParam(':seeker_id', $user['seeker_id']);
                $skillCheckStmt->execute();
                $skillResult = $skillCheckStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($skillResult['skill_count'] > 0) {
                    $setupComplete = true;
                    
                    // Update the database to reflect completion
                    $updateStmt = $conn->prepare("UPDATE job_seekers SET setup_complete = 1 WHERE seeker_id = :seeker_id");
                    $updateStmt->bindParam(':seeker_id', $user['seeker_id']);
                    $updateStmt->execute();
                }
            }
            
            // Log successful login
            ApiResponse::logActivity('candidate_login', [
                'user_id' => $user['seeker_id'],
                'email' => $email,
                'setup_complete' => $setupComplete
            ]);
            
            // Prepare user data for mobile app
            $userData = [
                'user_id' => $user['seeker_id'],
                'account_id' => $user['account_id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'disability_type' => $user['disability_name'],
                'setup_complete' => $setupComplete,
                'pwd_verified' => (bool)$user['pwd_verified'],
                'google_account' => (bool)$user['google_account'],
                'user_type' => 'candidate'
            ];
            
            // Return success response with token
            ApiResponse::success([
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
                'next_step' => $setupComplete ? 'dashboard' : 'account_setup'
            ], "Login successful");
            
        } else {
            // Log failed login attempt
            ApiResponse::logActivity('candidate_login_failed', [
                'email' => $email,
                'reason' => 'invalid_password'
            ]);
            
            ApiResponse::error("Invalid email or password", 401);
        }
    } else {
        // Log failed login attempt
        ApiResponse::logActivity('candidate_login_failed', [
            'email' => $email,
            'reason' => 'user_not_found'
        ]);
        
        ApiResponse::error("Invalid email or password", 401);
    }
    
} catch(PDOException $e) {
    // Log database error
    error_log("Login API database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    // Log general error
    error_log("Login API error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during login");
}
?>
