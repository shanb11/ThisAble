<?php
/**
 * PWD Verification API for ThisAble Mobile
 * Wraps existing backend/candidate/pwd_verification.php and DOHPwdVerifier
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';
require_once '../../backend/candidate/DOHPwdVerifier.php'; // Your existing PWD verifier

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
    
    // Extract verification data
    $pwdIdNumber = trim($input['pwdIdNumber'] ?? '');
    $pwdIdIssuedDate = trim($input['pwdIdIssuedDate'] ?? '');
    $pwdIdIssuingLGU = trim($input['pwdIdIssuingLGU'] ?? '');
    $action = trim($input['action'] ?? 'verify');
    
    // Validation
    $errors = [];
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
        ApiResponse::validationError($errors, "Missing required PWD ID information");
    }
    
    // Get authenticated user (for updating verification status)
    $token = getAuthToken();
    $user = null;
    if ($token) {
        $user = ApiDatabase::getUserByToken($token);
    }
    
    // Create DOH Verifier instance (using your existing class)
    $dohVerifier = new DOHPwdVerifier();
    
    // Try verification with DOH online system
    $verificationResult = $dohVerifier->verify($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU);
    
    // Log verification attempt
    ApiResponse::logActivity('pwd_verification', [
        'pwd_id' => $pwdIdNumber,
        'result' => $verificationResult['verified'] ? 'success' : 'failed',
        'user_id' => $user['user_id'] ?? null
    ]);
    
    // Update verification status in database if user is authenticated
    if ($user) {
        try {
            $conn = ApiDatabase::getConnection();
            
            $verificationStatus = 'pending';
            $isVerified = 0;
            
            if ($verificationResult['verified']) {
                $verificationStatus = 'verified';
                $isVerified = 1;
            } elseif (isset($verificationResult['status']) && $verificationResult['status'] === 'service_unavailable') {
                $verificationStatus = 'pending';
                $isVerified = 0;
            } else {
                $verificationStatus = 'rejected';
                $isVerified = 0;
            }
            
            // Update PWD verification status
            $stmt = $conn->prepare("UPDATE pwd_ids 
                                   SET is_verified = :is_verified, 
                                       verification_status = :verification_status,
                                       verification_date = NOW(),
                                       verification_attempts = verification_attempts + 1
                                   WHERE seeker_id = :seeker_id AND pwd_id_number = :pwd_id_number");
            
            $stmt->bindParam(':is_verified', $isVerified);
            $stmt->bindParam(':verification_status', $verificationStatus);
            $stmt->bindParam(':seeker_id', $user['user_id']);
            $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
            $stmt->execute();
            
        } catch (Exception $e) {
            error_log("PWD verification database update error: " . $e->getMessage());
            // Continue with response even if database update fails
        }
    }
    
    // Prepare response based on verification result
    if ($verificationResult['verified']) {
        // Successful verification
        ApiResponse::success([
            'verified' => true,
            'status' => 'verified',
            'message' => $verificationResult['message'],
            'verification_method' => 'doh_online',
            'verification_date' => date('Y-m-d H:i:s')
        ], "PWD ID verified successfully");
        
    } elseif (isset($verificationResult['status']) && $verificationResult['status'] === 'service_unavailable') {
        // DOH service unavailable - manual verification needed
        ApiResponse::success([
            'verified' => false,
            'status' => 'service_unavailable',
            'message' => $verificationResult['message'],
            'requires_manual_verification' => true,
            'next_step' => 'upload_pwd_image'
        ], "Automatic verification unavailable");
        
    } else {
        // Verification failed
        ApiResponse::success([
            'verified' => false,
            'status' => 'failed',
            'message' => $verificationResult['message'] ?? 'PWD ID verification failed',
            'requires_manual_verification' => true,
            'next_step' => 'upload_pwd_image'
        ], "Verification failed - manual verification required");
    }
    
} catch(Exception $e) {
    // Log error
    error_log("PWD Verification API error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred during PWD verification");
}

/**
 * Generate verification token for security
 */
function generateVerificationToken($pwdIdNumber) {
    return hash('sha256', $pwdIdNumber . uniqid('pwd', true) . time());
}
?>