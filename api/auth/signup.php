<?php
/**
 * Mobile Signup API for ThisAble
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
    
    // Extract input data
    $firstName = trim($input['first_name'] ?? '');
    $middleName = trim($input['middle_name'] ?? '');
    $lastName = trim($input['last_name'] ?? '');
    $suffix = trim($input['suffix'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['contact_number'] ?? '');
    $password = $input['password'] ?? '';
    $disability = $input['disability_id'] ?? null;
    $pwdIdNumber = trim($input['pwd_id_number'] ?? '');
    $pwdIdIssuedDate = trim($input['pwd_id_issued_date'] ?? '');
    $pwdIdIssuingLGU = trim($input['pwd_id_issuing_lgu'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors['first_name'] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors['last_name'] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($phone)) {
        $errors['contact_number'] = 'Contact number is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    if (empty($disability)) {
        $errors['disability_id'] = 'Disability type is required';
    }
    
    if (empty($pwdIdNumber)) {
        $errors['pwd_id_number'] = 'PWD ID number is required';
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
    
    // ✅ FIXED: Case-insensitive email check
    $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE LOWER(email) = LOWER(:email)");
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
