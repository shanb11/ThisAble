<?php
/**
 * Google Sign-In API for ThisAble Mobile & Web
 * ✅ FIXED: Accepts BOTH idToken (mobile) and accessToken (web)
 * ✅ FIXED: PostgreSQL/Supabase compatible (case-insensitive email)
 * ✅ ENHANCED: Better error logging and debugging
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
    
    // Enhanced logging for debugging
    error_log("=== GOOGLE AUTH REQUEST START ===");
    error_log("Request keys: " . json_encode(array_keys($input ?? [])));
    
    // ✅ FIXED: Accept EITHER idToken OR accessToken
    $idToken = $input['idToken'] ?? null;
    $accessToken = $input['accessToken'] ?? null;
    $action = $input['action'] ?? 'login';
    
    error_log("Action: $action");
    error_log("Has idToken: " . (!empty($idToken) ? 'YES' : 'NO'));
    error_log("Has accessToken: " . (!empty($accessToken) ? 'YES' : 'NO'));
    
    // Validate that we have at least one token
    if (empty($idToken) && empty($accessToken)) {
        error_log("ERROR: No tokens provided");
        ApiResponse::validationError([
            'token' => 'Either Google ID token or access token is required'
        ]);
    }
    
    // ============================================
    // STEP 1: Get User Info (Skip verification for complete_profile)
    // ============================================
    $userInfo = null;
    $tokenType = null;
    $email = null;
    $firstName = null;
    $lastName = null;
    $profilePicture = null;

    // For complete_profile, skip token verification (token may have expired)
    if ($action === 'complete_profile') {
        error_log("Complete profile action - skipping token verification");
        
        // Get user info directly from request
        $email = $input['email'] ?? '';
        $firstName = $input['firstName'] ?? '';
        $lastName = $input['lastName'] ?? '';
        
        if (empty($email)) {
            error_log("ERROR: No email provided in complete_profile request");
            ApiResponse::validationError(['email' => 'Email is required']);
        }
        
        error_log("Using email from request: $email");
        
    } else {
        // For login action, verify token with Google
        if (!empty($idToken)) {
            // ✅ Method 1: Verify using ID Token (Mobile/Android)
            error_log("Verifying with ID Token...");
            $tokenType = 'idToken';
            
            $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === FALSE) {
                error_log("ERROR: Failed to verify ID token");
                ApiResponse::error("Failed to verify Google ID token", 401);
            }
            
            $userInfo = json_decode($response, true);
            error_log("ID Token verification response: " . json_encode($userInfo));
            
            // Check for Google API error
            if (isset($userInfo['error'])) {
                error_log("ERROR: Google API error - " . $userInfo['error_description']);
                ApiResponse::error("Invalid Google ID token: " . ($userInfo['error_description'] ?? 'Unknown error'), 401);
            }
            
        } elseif (!empty($accessToken)) {
            // ✅ Method 2: Verify using Access Token (Web)
            error_log("Verifying with Access Token...");
            $tokenType = 'accessToken';
            
            $url = 'https://www.googleapis.com/oauth2/v3/userinfo';
            
            $options = [
                'http' => [
                    'header' => "Authorization: Bearer " . $accessToken . "\r\n" .
                            "Accept: application/json\r\n",
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ];
            
            $context = stream_context_create($options);
            $response = @file_get_contents($url, false, $context);
            
            if ($response === FALSE) {
                error_log("ERROR: Failed to verify access token");
                ApiResponse::error("Failed to verify Google access token", 401);
            }
            
            $userInfo = json_decode($response, true);
            error_log("Access Token verification response: " . json_encode($userInfo));
            
            // Check for Google API error
            if (isset($userInfo['error'])) {
                error_log("ERROR: Google API error - " . $userInfo['error']['message']);
                ApiResponse::error("Invalid Google access token: " . ($userInfo['error']['message'] ?? 'Unknown error'), 401);
            }
        }
        
        // Validate we got valid user info
        if (!isset($userInfo['email'])) {
            error_log("ERROR: No email in user info - " . json_encode($userInfo));
            ApiResponse::error("Invalid Google token - no email returned", 401);
        }
        
        // Extract user information from verified token
        $email = $userInfo['email'];
        $firstName = $userInfo['given_name'] ?? '';
        $lastName = $userInfo['family_name'] ?? '';
        $profilePicture = $userInfo['picture'] ?? '';
        
        error_log("Google user email: $email");
        error_log("Token type used: $tokenType");
    }
    
    // // ============================================
    // // STEP 2: Extract User Information
    // // ============================================
    // $email = $userInfo['email'];
    // $firstName = $userInfo['given_name'] ?? '';
    // $lastName = $userInfo['family_name'] ?? '';
    // $profilePicture = $userInfo['picture'] ?? '';
    
    // error_log("Google user email: $email");
    // error_log("Token type used: $tokenType");
    
    // ============================================
    // STEP 3: Handle Complete Profile Action
    // ============================================
    if ($action === 'complete_profile') {
        error_log("Processing complete_profile action...");
        
        // Get additional data from request
        $phone = $input['phone'] ?? '';
        $disability = intval($input['disability'] ?? 0);
        $pwdIdNumber = $input['pwdIdNumber'] ?? '';
        $pwdIdIssuedDate = $input['pwdIdIssuedDate'] ?? '';
        $pwdIdIssuingLGU = $input['pwdIdIssuingLGU'] ?? '';
        
        // Validate required fields
        if (empty($phone) || $disability <= 0 || empty($pwdIdNumber)) {
            error_log("ERROR: Missing required fields for complete_profile");
            ApiResponse::validationError([
                'phone' => empty($phone) ? 'Phone number is required' : null,
                'disability' => $disability <= 0 ? 'Disability type is required' : null,
                'pwdIdNumber' => empty($pwdIdNumber) ? 'PWD ID number is required' : null,
            ]);
        }
        
        // Get database connection
        $conn = ApiDatabase::getConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            error_log("ERROR: Email already registered");
            ApiResponse::error("Email already registered", 400);
        }
        
        // Check if PWD ID already exists
        $stmt = $conn->prepare("SELECT pwd_id_number FROM pwd_ids WHERE pwd_id_number = ?");
        $stmt->execute([$pwdIdNumber]);
        
        if ($stmt->rowCount() > 0) {
            error_log("ERROR: PWD ID already registered");
            ApiResponse::error("PWD ID already registered", 400);
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        try {
            // Insert into job_seekers
            $stmt = $conn->prepare("
                INSERT INTO job_seekers (first_name, last_name, disability_id, contact_number, setup_complete) 
                VALUES (?, ?, ?, ?, false)
            ");
            $stmt->execute([$firstName, $lastName, $disability, $phone]);
            $seekerId = $conn->lastInsertId();
            
            // Insert into user_accounts
            $stmt = $conn->prepare("
                INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                VALUES (?, ?, '', true)
            ");
            $stmt->execute([$seekerId, $email]);
            $accountId = $conn->lastInsertId();
            
            // Check if PWD file was already uploaded (orphaned record)
            $stmt = $conn->prepare("SELECT pwd_id, id_image_path FROM pwd_ids WHERE pwd_id_number = ? AND seeker_id IS NULL ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$pwdIdNumber]);
            $orphanedPwd = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($orphanedPwd) {
                // Link the orphaned upload to this new user
                $stmt = $conn->prepare("
                    UPDATE pwd_ids 
                    SET seeker_id = ?, 
                        date_issued = ?, 
                        issuing_lgu = ?,
                        updated_at = NOW()
                    WHERE pwd_id = ?
                ");
                $stmt->execute([
                    $seekerId,
                    !empty($pwdIdIssuedDate) ? $pwdIdIssuedDate : null,
                    !empty($pwdIdIssuingLGU) ? $pwdIdIssuingLGU : null,
                    $orphanedPwd['pwd_id']
                ]);
                error_log("Linked orphaned PWD upload (pwd_id: {$orphanedPwd['pwd_id']}) to new user: $seekerId");
            } else {
                // No orphaned upload found - create new record without file
                $stmt = $conn->prepare("
                    INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, is_verified, verification_status) 
                    VALUES (?, ?, ?, false, 'pending')
                ");
                $stmt->execute([
                    $seekerId,
                    $pwdIdNumber,
                    !empty($pwdIdIssuedDate) ? $pwdIdIssuedDate : null
                ]);
                error_log("Created new PWD record (no file uploaded) for user: $seekerId");
            }
            
            // Commit transaction
            $conn->commit();
            
            // Generate API token
            $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
            
            // Log successful signup
            ApiResponse::logActivity('google_signup', [
                'user_id' => $seekerId,
                'email' => $email,
                'pwd_id' => $pwdIdNumber
            ]);
            
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
            
            // Prepare response
            $userData = [
                'user_id' => $seekerId,
                'account_id' => $accountId,
                'email' => $email,
                'first_name' => $user['first_name'],
                'middle_name' => $user['middle_name'],
                'last_name' => $user['last_name'],
                'full_name' => trim($user['first_name'] . ' ' . $user['last_name']),
                'disability_type' => $user['disability_name'],
                'setup_complete' => false,
                'pwd_verified' => false,
                'google_account' => true,
                'user_type' => 'candidate',
                'profile_picture' => $profilePicture
            ];
            
            error_log("Google signup successful for: $email");
            
            ApiResponse::success([
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60,
                'next_step' => 'account_setup'
            ], "Google signup successful");
            
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("ERROR: Transaction failed - " . $e->getMessage());
            throw $e;
        }
    }
    
    // ============================================
    // STEP 4: Handle Login Action (Default)
    // ============================================
    
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
            error_log("ERROR: User data not found for seeker_id: $seekerId");
            ApiResponse::serverError("User data not found");
        }
        
        // Generate API token
        $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
        
        if (!$token) {
            error_log("ERROR: Failed to generate API token");
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
        
        error_log("Google login successful for: $email");
        
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
        error_log("New Google user detected, requires PWD ID registration");
        
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
    error_log("ERROR: Google Auth database error - " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("ERROR: Google Auth error - " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    ApiResponse::serverError("An error occurred during Google authentication");
}

error_log("=== GOOGLE AUTH REQUEST END ===");
?>