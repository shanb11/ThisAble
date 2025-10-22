<?php
/**
 * Google Sign-In API for ThisAble Mobile
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
    
    if (!$input || empty($input['idToken'])) {
        ApiResponse::validationError(['idToken' => 'Google ID token is required']);
    }
    
    $idToken = $input['idToken'];
    
    // Verify Google ID token
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $idToken;
    $response = file_get_contents($url);
    
    if ($response === FALSE) {
        ApiResponse::error("Failed to verify Google token", 401);
    }
    
    $userInfo = json_decode($response, true);
    
    if (!isset($userInfo['email'])) {
        ApiResponse::error("Invalid Google token", 401);
    }
    
    // Extract user information
    $email = $userInfo['email'];
    $firstName = $userInfo['given_name'] ?? '';
    $lastName = $userInfo['family_name'] ?? '';
    $profilePicture = $userInfo['picture'] ?? '';
    
    error_log("Processing Google user: $email");
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ✅ FIXED: Case-insensitive email lookup
    $stmt = $conn->prepare("SELECT account_id, seeker_id FROM user_accounts WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        // ✅ EXISTING USER LOGIN
        error_log("Existing Google user found, logging in");
        
        $seekerId = $existingUser['seeker_id'];
        
        // Get complete user data
        $userStmt = $conn->prepare("
            SELECT 
                js.seeker_id, js.first_name, js.middle_name, js.last_name, 
                js.suffix, js.disability_id, js.contact_number, js.setup_complete,
                js.city, js.province,
                dt.disability_name,
                ua.email, ua.google_account
            FROM job_seekers js 
            LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
            LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
            WHERE js.seeker_id = ?
        ");
        $userStmt->execute([$seekerId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            error_log("User data not found for seeker_id: $seekerId");
            ApiResponse::serverError("User data not found");
        }
        
        // Generate API token
        $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
        
        if (!$token) {
            error_log("Failed to generate API token");
            ApiResponse::serverError("Failed to generate authentication token");
        }
        
        // Log successful login
        ApiResponse::logActivity('google_login', [
            'user_id' => $seekerId,
            'email' => $email,
            'setup_complete' => (bool)$user['setup_complete']
        ]);
        
        // Prepare user data
        $userData = [
            'user_id' => $seekerId,
            'account_id' => $existingUser['account_id'],
            'email' => $email,
            'first_name' => $user['first_name'],
            'middle_name' => $user['middle_name'],
            'last_name' => $user['last_name'],
            'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'disability_type' => $user['disability_name'],
            'setup_complete' => (bool)$user['setup_complete'],
            'pwd_verified' => false, // Will check separately if needed
            'google_account' => true,
            'user_type' => 'candidate',
            'profile_picture' => $profilePicture
        ];
        
        // Return success response
        ApiResponse::success([
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
            'next_step' => $user['setup_complete'] ? 'dashboard' : 'account_setup'
        ], "Google login successful");
        
    } else {
        // ✅ NEW USER - Return user info for PWD registration
        error_log("New Google user, requires PWD ID registration");
        
        // Return Google user data for registration flow
        ApiResponse::success([
            'is_new_user' => true,
            'google_data' => [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'profile_picture' => $profilePicture
            ],
            'next_step' => 'pwd_registration',
            'message' => 'Please complete your registration with PWD ID details'
        ], "New Google user detected");
    }
    
} catch(PDOException $e) {
    error_log("Google Auth database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Google Auth error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during Google authentication");
}
?>
