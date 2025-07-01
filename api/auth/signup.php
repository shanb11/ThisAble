<?php
/**
 * Mobile Signup API for ThisAble
 * Wraps existing backend/candidate/signup_process.php with mobile-friendly response
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
    
    // Extract form data (matching your existing signup form)
    $firstName = trim($input['firstName'] ?? '');
    $middleName = trim($input['middleName'] ?? '');
    $lastName = trim($input['lastName'] ?? '');
    $suffix = trim($input['suffix'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $disability = intval($input['disability'] ?? 0);
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    $pwdIdNumber = trim($input['pwdIdNumber'] ?? '');
    $pwdIdIssuedDate = trim($input['pwdIdIssuedDate'] ?? '');
    $pwdIdIssuingLGU = trim($input['pwdIdIssuingLGU'] ?? '');
    
    // Validation (matching your web validation)
    $errors = [];
    
    if (empty($firstName)) {
        $errors['firstName'] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors['lastName'] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^09\d{9}$/', $phone)) {
        $errors['phone'] = 'Invalid Philippine phone number format (09XXXXXXXXX)';
    }
    
    if ($disability <= 0) {
        $errors['disability'] = 'Disability type is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match';
    }
    
    if (empty($pwdIdNumber)) {
        $errors['pwdIdNumber'] = 'PWD ID number is required';
    }
    
    if (empty($pwdIdIssuedDate)) {
        $errors['pwdIdIssuedDate'] = 'PWD ID issued date is required';
    }
    
    if (empty($pwdIdIssuingLGU)) {
        $errors['pwdIdIssuingLGU'] = 'Issuing LGU is required';
    }
    
    if (!empty($errors)) {
        ApiResponse::validationError($errors, "Validation failed");
    }
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        ApiResponse::error('Email already registered', 409);
    }
    
    // Check if PWD ID already exists
    $stmt = $conn->prepare("SELECT pwd_id_number FROM pwd_ids WHERE pwd_id_number = :pwd_id_number");
    $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        ApiResponse::error('PWD ID already registered', 409);
    }
    
    // Begin transaction (using your existing approach)
    $conn->beginTransaction();
    
    try {
        // Insert into job_seekers table (matching your existing structure)
        $stmt = $conn->prepare("INSERT INTO job_seekers (first_name, middle_name, last_name, suffix, disability_id, contact_number) 
                               VALUES (:first_name, :middle_name, :last_name, :suffix, :disability_id, :contact_number)");
        
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':middle_name', $middleName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':suffix', $suffix);
        $stmt->bindParam(':disability_id', $disability);
        $stmt->bindParam(':contact_number', $phone);
        $stmt->execute();
        
        // Get the seeker_id
        $seekerId = $conn->lastInsertId();
        
        // Insert PWD ID (matching your existing logic)
        $stmt = $conn->prepare("INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, is_verified, verification_status) 
                               VALUES (:seeker_id, :pwd_id_number, :issued_at, 0, 'pending')");
        
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
        
        $issuedDate = !empty($pwdIdIssuedDate) ? $pwdIdIssuedDate : date('Y-m-d');
        $stmt->bindParam(':issued_at', $issuedDate);
        $stmt->execute();
        
        // Insert into user_accounts table
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                               VALUES (:seeker_id, :email, :password_hash, 0)");
        
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->execute();
        
        $accountId = $conn->lastInsertId();
        
        // Commit the transaction
        $conn->commit();
        
        // Generate API token for immediate login
        $token = ApiDatabase::generateApiToken($seekerId, 'candidate');
        
        // Log successful registration
        ApiResponse::logActivity('candidate_signup', [
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
            'pwd_verified' => false, // Will be verified separately
            'google_account' => false,
            'user_type' => 'candidate'
        ];
        
        // Return success response
        ApiResponse::success([
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
            'next_step' => 'pwd_verification',
            'message' => 'Account created successfully. Please verify your PWD ID.'
        ], "Registration successful");
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    // Log database error
    error_log("Signup API database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred during registration");
    
} catch(Exception $e) {
    // Log general error
    error_log("Signup API error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during registration");
}
?>