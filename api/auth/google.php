<?php
/**
 * Google OAuth API for ThisAble Mobile
 * Handles Google Sign-In for mobile app users
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
    
    // Extract Google token data from mobile app
    $googleIdToken = trim($input['idToken'] ?? '');
    $googleAccessToken = trim($input['accessToken'] ?? '');
    $action = trim($input['action'] ?? 'login');
    
    // Determine which verification method to use
    $googleUserInfo = null;
    
    // Try ID token first (preferred for security - mobile platforms)
    if (!empty($googleIdToken)) {
        $googleUserInfo = verifyGoogleIdToken($googleIdToken);
        if ($googleUserInfo) {
            error_log("Verified using ID token");
        }
    }
    
    // If no ID token or verification failed, try access token (web fallback)
    if (!$googleUserInfo && !empty($googleAccessToken)) {
        $googleUserInfo = verifyGoogleAccessToken($googleAccessToken);
        if ($googleUserInfo) {
            error_log("Verified using access token (web fallback)");
        }
    }
    
    // If still no user info, authentication failed
    if (!$googleUserInfo) {
        ApiResponse::error("Invalid Google authentication", 401);
    }



// Add this new function
function verifyGoogleAccessToken($accessToken) {
    try {
        // Use Google's userinfo endpoint with the access token
        $url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $accessToken;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ThisAble Mobile App'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        // Verify we got valid user data
        if (isset($data['email']) && isset($data['id'])) {
            // Format to match ID token structure
            return [
                'email' => $data['email'],
                'given_name' => $data['given_name'] ?? '',
                'family_name' => $data['family_name'] ?? '',
                'picture' => $data['picture'] ?? '',
                'sub' => $data['id']
            ];
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Google access token verification error: " . $e->getMessage());
        return null;
    }
}
    
    // Extract user information from Google
    $email = $googleUserInfo['email'];
    $firstName = $googleUserInfo['given_name'] ?? '';
    $lastName = $googleUserInfo['family_name'] ?? '';
    $profilePicture = $googleUserInfo['picture'] ?? '';
    $googleId = $googleUserInfo['sub'];
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Check if user exists in database
    $stmt = $conn->prepare("SELECT ua.account_id, ua.seeker_id, js.first_name, js.last_name, js.setup_complete
                           FROM user_accounts ua 
                           JOIN job_seekers js ON ua.seeker_id = js.seeker_id 
                           WHERE ua.email = :email AND ua.google_account = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Existing Google user - Log them in
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Generate API token for mobile
        $token = ApiDatabase::generateApiToken($user['seeker_id'], 'candidate');
        
        if (!$token) {
            ApiResponse::serverError("Failed to generate authentication token");
        }
        
        // Check setup completion status (simplified version)
        $setupComplete = false;
        
        if (isset($user['setup_complete']) && $user['setup_complete'] == 1) {
            $setupComplete = true;
        }
        
        // DO NOT auto-update setup_complete based on skills
        // Setup should only be marked complete through proper completion flow
        
        // Log successful login
        ApiResponse::logActivity('google_login', [
            'user_id' => $user['seeker_id'],
            'email' => $email,
            'setup_complete' => $setupComplete
        ]);
        
        // Prepare user data
        $userData = [
            'user_id' => $user['seeker_id'],
            'account_id' => $user['account_id'],
            'email' => $email,
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'setup_complete' => $setupComplete,
            'google_account' => true,
            'user_type' => 'candidate',
            'profile_picture' => $profilePicture
        ];
        
        // Return success response
        ApiResponse::success([
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 days
            'next_step' => $setupComplete ? 'dashboard' : 'account_setup',
            'is_new_user' => false
        ], "Google login successful");
    } else {
        // New Google user - Need to complete profile
        
        if ($action === 'complete_profile') {
            // Complete Google profile with additional info
            $phone = trim($input['phone'] ?? '');
            $disability = intval($input['disability'] ?? 0);
            $pwdIdNumber = trim($input['pwdIdNumber'] ?? '');
            $pwdIdIssuedDate = trim($input['pwdIdIssuedDate'] ?? '');
            $pwdIdIssuingLGU = trim($input['pwdIdIssuingLGU'] ?? '');
            
            // Validation for profile completion
            $errors = [];
            if (empty($phone)) {
                $errors['phone'] = 'Phone number is required';
            }
            if ($disability <= 0) {
                $errors['disability'] = 'Disability type is required';
            }
            if (empty($pwdIdNumber)) {
                $errors['pwdIdNumber'] = 'PWD ID number is required';
            }
            
            if (!empty($errors)) {
                ApiResponse::validationError($errors, "Profile completion validation failed");
            }
            
            // Begin transaction
            $conn->beginTransaction();
            
            try {
                // Insert into job_seekers table
                $stmt = $conn->prepare("INSERT INTO job_seekers (first_name, last_name, disability_id, contact_number) 
                                       VALUES (:first_name, :last_name, :disability_id, :contact_number)");
                $stmt->bindParam(':first_name', $firstName);
                $stmt->bindParam(':last_name', $lastName);
                $stmt->bindParam(':disability_id', $disability);
                $stmt->bindParam(':contact_number', $phone);
                $stmt->execute();
                
                $seekerId = $conn->lastInsertId();
                
                // Insert PWD ID
                $stmt = $conn->prepare("INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, is_verified, verification_status) 
                                       VALUES (:seeker_id, :pwd_id_number, :issued_at, 0, 'pending')");
                $stmt->bindParam(':seeker_id', $seekerId);
                $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
                $issuedDate = !empty($pwdIdIssuedDate) ? $pwdIdIssuedDate : date('Y-m-d');
                $stmt->bindParam(':issued_at', $issuedDate);
                $stmt->execute();
                
                // Generate random password (not used for Google accounts)
                $randomPassword = bin2hex(random_bytes(8));
                $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
                
                // Insert into user_accounts table
                $stmt = $conn->prepare("INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                                       VALUES (:seeker_id, :email, :password_hash, 1)");
                $stmt->bindParam(':seeker_id', $seekerId);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password_hash', $passwordHash);
                $stmt->execute();
                
                $accountId = $conn->lastInsertId();
                
                // Commit transaction
                $conn->commit();
                
                // Generate API token
                $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
                
                // Log successful registration
                ApiResponse::logActivity('google_signup', [
                    'user_id' => $seekerId,
                    'email' => $email,
                    'pwd_id' => $pwdIdNumber
                ]);
                
                // Get disability type name
                $stmt = $conn->prepare("SELECT disability_name FROM disability_types WHERE disability_id = :disability_id");
                $stmt->bindParam(':disability_id', $disability);
                $stmt->execute();
                $disabilityResult = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Prepare user data
                $userData = [
                    'user_id' => $seekerId,
                    'account_id' => $accountId,
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => trim($firstName . ' ' . $lastName),
                    'disability_type' => $disabilityResult['disability_name'] ?? null,
                    'setup_complete' => false,
                    'pwd_verified' => false,
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
                    'next_step' => 'pwd_verification',
                    'is_new_user' => true
                ], "Google account created successfully");
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            
        } else {
            // First time Google sign-in - need additional info
            ApiResponse::success([
                'requires_profile_completion' => true,
                'google_user_info' => [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'profile_picture' => $profilePicture
                ],
                'next_step' => 'complete_google_profile'
            ], "Profile completion required for new Google user");
        }
    }
    
} catch(PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Google OAuth API database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Google OAuth API error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during Google authentication");
}

/**
 * Verify Google ID token
 * @param string $idToken Google ID token from mobile app
 * @return array|null User info if valid, null if invalid
 */
function verifyGoogleIdToken($idToken) {
    try {
        // Google's public key endpoint
        $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . $idToken;
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ThisAble Mobile App'
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        // Verify the token is for your app (use your Google Client ID)
        $expectedClientId = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
        
        if (isset($data['aud']) && $data['aud'] === $expectedClientId && isset($data['email'])) {
            return $data;
        }
        
        return null;
        
    } catch (Exception $e) {
        error_log("Google token verification error: " . $e->getMessage());
        return null;
    }
}

/**
 * Verify Google Access Token (Web fallback)
 * Add this AFTER the existing verifyGoogleIdToken function
 */
function verifyGoogleAccessToken($accessToken) {
    try {
        // Use Google's tokeninfo endpoint
        $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=" . urlencode($accessToken);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'ThisAble Mobile App'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            error_log("Access token verification failed: Invalid response");
            return null;
        }
        
        $data = json_decode($response, true);
        
        // Verify the token is for your app
        $expectedClientId = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
        
        if (!isset($data['aud']) || $data['aud'] !== $expectedClientId) {
            error_log("Access token verification failed: Wrong client ID");
            return null;
        }
        
        // Get user info using the access token
        $userInfoUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $accessToken . "\r\n",
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
?>