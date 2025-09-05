<?php
/**
 * FIXED Google OAuth API for ThisAble Mobile
 * ✅ CORRECTED to match your EXACT database schema
 * ✅ Handles both ID tokens (mobile) and Access tokens (web)
 * ✅ Uses your working authentication patterns
 */

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
    
    // FIXED: Enhanced token validation
    $idToken = $input['idToken'] ?? null;
    $accessToken = $input['accessToken'] ?? null;
    $action = $input['action'] ?? 'login';
    
    // Debug logging
    error_log("=== FIXED GOOGLE AUTH DEBUG ===");
    error_log("Has idToken: " . (!empty($idToken) ? 'YES' : 'NO'));
    error_log("Has accessToken: " . (!empty($accessToken) ? 'YES' : 'NO'));
    error_log("Action: " . $action);
    
    $userInfo = null;
    
    // FIXED: Try ID token first (mobile), then access token (web)
    if (!empty($idToken)) {
        error_log("Attempting ID token verification...");
        $userInfo = verifyGoogleIdToken($idToken);
        if ($userInfo) {
            error_log("ID token verification successful");
        }
    }
    
    // If no ID token or ID token failed, try access token
    if (!$userInfo && !empty($accessToken)) {
        error_log("Attempting access token verification...");
        $userInfo = verifyGoogleAccessToken($accessToken);
        if ($userInfo) {
            error_log("Access token verification successful");
        }
    }
    
    // FIXED: Better error handling
    if (!$userInfo) {
        error_log("All token verification methods failed");
        if (empty($idToken) && empty($accessToken)) {
            ApiResponse::validationError(['tokens' => 'No authentication tokens provided']);
        } else {
            ApiResponse::unauthorized("Token verification failed");
        }
    }
    
    if (empty($userInfo['email'])) {
        ApiResponse::validationError(['email' => 'Email not found in Google response']);
    }
    
    // ✅ FIXED: Use correct database connection method
    $conn = ApiDatabase::getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $email = $userInfo['email'];
    $firstName = $userInfo['given_name'] ?? '';
    $lastName = $userInfo['family_name'] ?? '';
    $profilePicture = $userInfo['picture'] ?? '';
    
    error_log("Processing user: $email");
    
    // ✅ FIXED: Check if user exists using CORRECT column names
    $stmt = $conn->prepare("SELECT account_id, seeker_id FROM user_accounts WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        // ✅ EXISTING USER LOGIN - Use correct table structure
        error_log("Existing user found, logging in");
        
        $seekerId = $existingUser['seeker_id'];
        
        // Get complete user data using WORKING query pattern from your other APIs
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
        
        // Generate API token using your working method
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
        
        // ✅ FIXED: Prepare user data matching your working APIs
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
            'pwd_verified' => false, // Will be checked separately
            'google_account' => true,
            'user_type' => 'candidate'
        ];
        
        // Return success response with token
        ApiResponse::success([
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
            'next_step' => $user['setup_complete'] ? 'dashboard' : 'account_setup'
        ], "Login successful");
        
    } else {
        // ✅ NEW USER REGISTRATION - Fixed to match your schema
        error_log("New user registration");
        
        $conn->beginTransaction();
        
        try {
            // ✅ FIXED: Insert into job_seekers FIRST (correct order)
            // Using default values for required fields that Google doesn't provide
            $insertJobSeekerStmt = $conn->prepare("
                INSERT INTO job_seekers (
                    first_name, middle_name, last_name, suffix, 
                    disability_id, contact_number, setup_complete
                ) VALUES (?, '', ?, '', 1, '09000000000', 0)
            ");
            $insertJobSeekerStmt->execute([$firstName, $lastName]);
            $seekerId = $conn->lastInsertId();
            
            error_log("Created job_seeker with ID: $seekerId");
            
            // ✅ FIXED: Insert into user_accounts with correct columns
            $insertUserStmt = $conn->prepare("
                INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                VALUES (?, ?, '', 1)
            ");
            $insertUserStmt->execute([$seekerId, $email]);
            $accountId = $conn->lastInsertId();
            
            error_log("Created user_account with ID: $accountId");
            
            $conn->commit();
            
            // Generate API token using your working method
            $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
            
            if (!$token) {
                error_log("Failed to generate API token for new user");
                ApiResponse::serverError("Failed to generate authentication token");
            }
            
            // Log successful registration
            ApiResponse::logActivity('google_registration', [
                'user_id' => $seekerId,
                'email' => $email
            ]);
            
            // ✅ FIXED: Return new user data matching your working APIs
            $userData = [
                'user_id' => $seekerId,
                'account_id' => $accountId,
                'email' => $email,
                'first_name' => $firstName,
                'middle_name' => '',
                'last_name' => $lastName,
                'full_name' => trim($firstName . ' ' . $lastName),
                'disability_type' => null, // Will be set during account setup
                'setup_complete' => false,
                'pwd_verified' => false,
                'google_account' => true,
                'user_type' => 'candidate'
            ];
            
            ApiResponse::success([
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
                'next_step' => 'account_setup',
                'is_new_user' => true
            ], "Account created successfully");
            
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            ApiResponse::serverError("Registration failed");
        }
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Google OAuth API error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during Google authentication");
}

/**
 * Verify Google ID token - WORKING VERSION
 */
function verifyGoogleIdToken($idToken) {
    try {
        $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . urlencode($idToken);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ThisAble Mobile App'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            error_log("ID token verification failed: Invalid response");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ID token verification failed: Invalid JSON");
            return null;
        }
        
        // Verify the token is for your app
        $expectedClientId = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
        
        if (isset($data['aud']) && $data['aud'] === $expectedClientId && isset($data['email'])) {
            return $data;
        }
        
        error_log("ID token verification failed: Wrong client ID or missing email");
        return null;
        
    } catch (Exception $e) {
        error_log("ID token verification error: " . $e->getMessage());
        return null;
    }
}

/**
 * Verify Google Access Token (Web fallback) - WORKING VERSION
 */
function verifyGoogleAccessToken($accessToken) {
    try {
        // First verify the token is valid
        $tokenInfoUrl = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=" . urlencode($accessToken);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ThisAble Mobile App'
            ]
        ]);
        
        $tokenResponse = @file_get_contents($tokenInfoUrl, false, $context);
        
        if ($tokenResponse === FALSE) {
            error_log("Access token verification failed: Invalid response from tokeninfo");
            return null;
        }
        
        $tokenData = json_decode($tokenResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Access token verification failed: Invalid JSON from tokeninfo");
            return null;
        }
        
        // Verify the token is for your app
        $expectedClientId = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
        
        if (!isset($tokenData['aud']) || $tokenData['aud'] !== $expectedClientId) {
            error_log("Access token verification failed: Wrong client ID");
            return null;
        }
        
        // Get user info using the access token
        $userInfoUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $accessToken . "\r\n" .
                           "User-Agent: ThisAble Mobile App\r\n",
                'timeout' => 10
            ]
        ];
        
        $userContext = stream_context_create($opts);
        $userResponse = @file_get_contents($userInfoUrl, false, $userContext);
        
        if ($userResponse === FALSE) {
            error_log("Failed to get user info with access token");
            return null;
        }
        
        $userInfo = json_decode($userResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to parse user info JSON");
            return null;
        }
        
        // Format to match ID token structure
        return [
            'email' => $userInfo['email'] ?? null,
            'given_name' => $userInfo['given_name'] ?? '',
            'family_name' => $userInfo['family_name'] ?? '',
            'picture' => $userInfo['picture'] ?? '',
            'sub' => $userInfo['id'] ?? null,
            'email_verified' => $userInfo['verified_email'] ?? false
        ];
        
    } catch (Exception $e) {
        error_log("Access token verification error: " . $e->getMessage());
        return null;
    }
}
?>