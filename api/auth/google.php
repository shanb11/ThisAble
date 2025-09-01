<?php
/**
 * Google OAuth API for ThisAble Mobile - FIXED FOR WEB
 * ENHANCED to handle both ID tokens (mobile) and Access tokens (web)
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
    error_log("=== GOOGLE AUTH DEBUG ===");
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
    
    // Connect to database
    $conn = ApiDatabase::connect();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $email = $userInfo['email'];
    $firstName = $userInfo['given_name'] ?? '';
    $lastName = $userInfo['family_name'] ?? '';
    $profilePicture = $userInfo['picture'] ?? '';
    
    error_log("Processing user: $email");
    
    // Check if user exists in user_accounts
    $stmt = $conn->prepare("SELECT user_id, account_type FROM user_accounts WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        error_log("Existing user found");
        
        if ($existingUser['account_type'] !== 'candidate') {
            ApiResponse::validationError(['account_type' => 'This email is registered as an employer account']);
        }
        
        // Get candidate profile
        $candidateStmt = $conn->prepare("
            SELECT js.*, ua.email, ua.account_type, ua.created_at as account_created
            FROM job_seekers js 
            INNER JOIN user_accounts ua ON js.user_id = ua.user_id 
            WHERE ua.user_id = ?
        ");
        $candidateStmt->execute([$existingUser['user_id']]);
        $candidate = $candidateStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$candidate) {
            ApiResponse::serverError("Candidate profile not found");
        }
        
        // FIXED: Update profile picture if changed
        if (!empty($profilePicture) && $candidate['profile_picture'] !== $profilePicture) {
            $updatePictureStmt = $conn->prepare("UPDATE job_seekers SET profile_picture = ? WHERE user_id = ?");
            $updatePictureStmt->execute([$profilePicture, $existingUser['user_id']]);
            $candidate['profile_picture'] = $profilePicture;
        }
        
        // Generate or refresh token
        $token = ApiDatabase::generateToken($existingUser['user_id']);
        
        ApiResponse::success([
            'message' => 'Login successful',
            'user' => [
                'user_id' => $candidate['user_id'],
                'first_name' => $candidate['first_name'],
                'last_name' => $candidate['last_name'],
                'middle_name' => $candidate['middle_name'],
                'email' => $candidate['email'],
                'phone' => $candidate['phone'],
                'account_type' => $candidate['account_type'],
                'profile_picture' => $candidate['profile_picture'],
                'account_setup_completed' => $candidate['account_setup_completed'],
                'created_at' => $candidate['account_created']
            ],
            'token' => $token
        ]);
        
    } else {
        // New user registration
        error_log("New user registration");
        
        $conn->beginTransaction();
        
        try {
            // Create user account
            $insertUserStmt = $conn->prepare("
                INSERT INTO user_accounts (email, account_type, email_verified, created_at) 
                VALUES (?, 'candidate', 1, NOW())
            ");
            $insertUserStmt->execute([$email]);
            $userId = $conn->lastInsertId();
            
            // Create candidate profile
            $insertCandidateStmt = $conn->prepare("
                INSERT INTO job_seekers (
                    user_id, first_name, last_name, email, profile_picture, 
                    account_setup_completed, created_at
                ) VALUES (?, ?, ?, ?, ?, 0, NOW())
            ");
            $insertCandidateStmt->execute([
                $userId, $firstName, $lastName, $email, $profilePicture
            ]);
            
            $conn->commit();
            
            // Generate token
            $token = ApiDatabase::generateToken($userId);
            
            ApiResponse::success([
                'message' => 'Account created successfully',
                'user' => [
                    'user_id' => $userId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'middle_name' => null,
                    'email' => $email,
                    'phone' => null,
                    'account_type' => 'candidate',
                    'profile_picture' => $profilePicture,
                    'account_setup_completed' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'token' => $token,
                'is_new_user' => true
            ]);
            
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
 * Verify Google ID token
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
 * Verify Google Access Token (Web fallback) - ENHANCED ERROR HANDLING
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