<?php
/**
 * PWD Verification Handler
 * This file handles verification of PWD IDs through the DOH database
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Required files
require_once('../../backend/db.php');
require_once('DOHPwdVerifier.php'); // Added DOH Verifier class

// Set headers
header('Content-Type: application/json');

// Function to generate a secure token
function generateVerificationToken($pwdIdNumber) {
    // Create a unique token based on PWD ID and a random string
    return hash('sha256', $pwdIdNumber . uniqid('pwd', true) . time());
}

// Function to log verification attempts
function logVerificationAttempt($pwdIdNumber, $result, $details = '') {
    // Get client IP
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Create log directory if it doesn't exist
    $logDir = '../../logs/verification';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Create log message
    $logMessage = date('Y-m-d H:i:s') . " | IP: $ip | PWD ID: $pwdIdNumber | Result: $result | $details\n";
    
    // Write to log file
    file_put_contents("$logDir/verification_log.txt", $logMessage, FILE_APPEND);
}

// Check if this is a verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data
    $pwdIdNumber = trim($_POST['pwdIdNumber'] ?? '');
    $pwdIdIssuedDate = trim($_POST['pwdIdIssuedDate'] ?? '');
    $pwdIdIssuingLGU = trim($_POST['pwdIdIssuingLGU'] ?? '');
    $action = trim($_POST['action'] ?? '');
    // New parameter - check if we should skip image verification initially
    $skipImage = (isset($_POST['skipImage']) && $_POST['skipImage'] === 'true');
    
    // Check action type
    if ($action !== 'verify') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action'
        ]);
        exit;
    }
    
    // Validate input
    if (empty($pwdIdNumber) || empty($pwdIdIssuedDate) || empty($pwdIdIssuingLGU)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing required PWD ID information'
        ]);
        exit;
    }
    
    // Create DOH Verifier instance
    $dohVerifier = new DOHPwdVerifier();
    
    // First, try to verify with DOH online system without image
    if ($skipImage) {
        $dohVerificationResult = $dohVerifier->verify($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU);
        
        // If verification was successful, we don't need the image
        if ($dohVerificationResult['verified']) {
            // Generate verification token
            $verificationToken = generateVerificationToken($pwdIdNumber);
            
            // Store verification token in session for security
            $_SESSION['pwd_verification'] = [
                'token' => $verificationToken,
                'pwdIdNumber' => $pwdIdNumber,
                'verified' => true,
                'timestamp' => time()
            ];
            
            // Log successful verification
            logVerificationAttempt($pwdIdNumber, 'SUCCESS', 'DOH online verification successful');
            
            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Your PWD ID has been successfully verified through DOH database.',
                'token' => $verificationToken,
                'verified' => true
            ]);
            exit;
        } 
        
        // If DOH service is down, ask for image upload
        if ($dohVerificationResult['status'] === 'service_unavailable') {
            echo json_encode([
                'status' => 'warning',
                'message' => 'The DOH verification service is currently unavailable. Please upload your PWD ID image for manual verification.',
                'requireUpload' => true
            ]);
            exit;
        }
        
        // If verification failed, ask for image upload
        echo json_encode([
            'status' => 'error',
            'message' => $dohVerificationResult['message'] . ' Please upload your PWD ID for manual verification.',
            'requireUpload' => true
        ]);
        exit;
    }
    
    // If we get here, image verification is needed
    
    // Process file upload conditionally - only if not skipping image
    $uploadPath = null;
    if (!$skipImage) {
        // Check if file was uploaded
        if (!isset($_FILES['pwdIdFile']) || $_FILES['pwdIdFile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'status' => 'error',
                'message' => 'PWD ID image upload failed or not provided'
            ]);
            exit;
        }
        
        // Process file upload
        $uploadDir = '../../uploads/pwd_ids/';
        $fileExtension = pathinfo($_FILES['pwdIdFile']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['pwdIdFile']['tmp_name'], $uploadPath)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save PWD ID image'
            ]);
            exit;
        }
    }
    
    // Now try DOH verification again, with the image path
    $dohVerificationResult = $dohVerifier->verify($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU);
    
    // If DOH online verification failed, fall back to traditional verification
    if (!$dohVerificationResult['verified']) {
        // Connect to DOH API - Replace with your actual DOH verification code
        $dohVerificationResult = verifyWithDOHDatabase($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU, $uploadPath);
    }
    
    // Process verification result
    if ($dohVerificationResult['verified']) {
        // Generate verification token
        $verificationToken = generateVerificationToken($pwdIdNumber);
        
        // Store verification token in session for security
        $_SESSION['pwd_verification'] = [
            'token' => $verificationToken,
            'pwdIdNumber' => $pwdIdNumber,
            'verified' => true,
            'timestamp' => time()
        ];
        
        // Log successful verification
        logVerificationAttempt($pwdIdNumber, 'SUCCESS', 'DOH verification successful');
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Your PWD ID has been successfully verified through the DOH database.',
            'token' => $verificationToken,
            'verified' => true
        ]);
    } 
    else if ($dohVerificationResult['status'] === 'service_unavailable') {
        // DOH service is down, provide fallback with pending status
        $verificationToken = generateVerificationToken($pwdIdNumber);
        
        // Store in session with pending status
        $_SESSION['pwd_verification'] = [
            'token' => $verificationToken,
            'pwdIdNumber' => $pwdIdNumber,
            'verified' => false,
            'pending' => true,
            'timestamp' => time()
        ];
        
        // Log service unavailable
        logVerificationAttempt($pwdIdNumber, 'WARNING', 'DOH service unavailable, manual verification needed');
        
        // Return warning response
        echo json_encode([
            'status' => 'warning',
            'message' => 'The DOH verification service is currently unavailable. Your registration can proceed, but verification will be completed manually.',
            'token' => $verificationToken,
            'verified' => false
        ]);
    }
    else {
        // Verification failed
        // Log failed verification
        logVerificationAttempt($pwdIdNumber, 'FAILED', $dohVerificationResult['message'] ?? 'Verification failed');
        
        // Return error response
        echo json_encode([
            'status' => 'error',
            'message' => $dohVerificationResult['message'] ?? 'PWD ID verification failed. Please check your information and try again.'
        ]);
    }
} 
else {
    // Invalid request method
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}

/**
 * Function to verify PWD ID with DOH Database
 * This is a placeholder for the actual DOH verification logic
 * Replace this with your actual DOH verification code
 */
function verifyWithDOHDatabase($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU, $idImagePath = null) {
    // This is where you would integrate your existing DOH verification code
    
    // Placeholder for verification logic
    // In a real implementation, this would connect to the DOH API
    
    // For testing purposes, we'll simulate different verification scenarios
    
    // Sample implementation (replace with your actual DOH verification logic)
    
    // Simulate API call to DOH database
    $apiUrl = 'https://doh.gov.ph/api/pwd/verify'; // Replace with actual DOH API endpoint
    
    // Simulate API call success/failure scenarios for testing
    // In production, replace this with actual API call
    
    // Testing: Use specific PWD ID numbers to simulate different scenarios
    if ($pwdIdNumber === '123456') {
        // Simulate successful verification
        return [
            'verified' => true,
            'message' => 'PWD ID verified successfully',
            'data' => [
                'pwd_id' => $pwdIdNumber,
                'issued_date' => $pwdIdIssuedDate,
                'issuing_lgu' => $pwdIdIssuingLGU,
                'verification_date' => date('Y-m-d H:i:s')
            ]
        ];
    } 
    else if ($pwdIdNumber === '000000') {
        // Simulate service unavailable
        return [
            'verified' => false,
            'status' => 'service_unavailable',
            'message' => 'DOH verification service is currently unavailable'
        ];
    }
    else if ($pwdIdNumber === '999999') {
        // Simulate invalid PWD ID
        return [
            'verified' => false,
            'message' => 'Invalid PWD ID number. This ID does not exist in the DOH database.'
        ];
    }
    // New test case - require image for verification
    else if ($pwdIdNumber === '555555') {
        // Simulate case where image is required for verification
        if ($idImagePath === null) {
            return [
                'verified' => false,
                'status' => 'image_required',
                'message' => 'Image upload required for this PWD ID'
            ];
        } else {
            return [
                'verified' => true,
                'message' => 'PWD ID verified successfully with image',
                'data' => [
                    'pwd_id' => $pwdIdNumber,
                    'issued_date' => $pwdIdIssuedDate,
                    'issuing_lgu' => $pwdIdIssuingLGU,
                    'verification_date' => date('Y-m-d H:i:s')
                ]
            ];
        }
    }
    
    // Adjust success rate based on whether image is provided
    $successThreshold = ($idImagePath === null) ? 70 : 90;
    $random = mt_rand(1, 100);
    
    if ($random <= $successThreshold) {
        return [
            'verified' => true,
            'message' => 'PWD ID verified successfully' . ($idImagePath ? ' with image' : ''),
            'data' => [
                'pwd_id' => $pwdIdNumber,
                'issued_date' => $pwdIdIssuedDate,
                'issuing_lgu' => $pwdIdIssuingLGU,
                'verification_date' => date('Y-m-d H:i:s')
            ]
        ];
    } else {
        return [
            'verified' => false,
            'message' => 'Could not verify PWD ID. Please check your information and try again.'
        ];
    }
}