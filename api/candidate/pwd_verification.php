<?php
/**
 * Enhanced PWD Verification Handler with File Upload Support
 * Location: C:\xampp\htdocs\ThisAble\api\candidate\pwd_verification.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Required files
require_once('../../backend/db.php');
require_once('DOHPwdVerifier.php');
require_once('../../backend/ApiResponse.php');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Function to generate a secure token
function generateVerificationToken($pwdIdNumber) {
    return hash('sha256', $pwdIdNumber . uniqid('pwd', true) . time());
}

// Function to log verification attempts
function logVerificationAttempt($pwdIdNumber, $result, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $logDir = '../../logs/verification';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logMessage = date('Y-m-d H:i:s') . " | IP: $ip | PWD ID: $pwdIdNumber | Result: $result | $details\n";
    file_put_contents("$logDir/verification_log.txt", $logMessage, FILE_APPEND);
}

// Check if this is a verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Determine if this is a multipart request (with file) or JSON request
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
        
        if ($isMultipart) {
            // Handle multipart form data (with file upload)
            $pwdIdNumber = trim($_POST['pwdIdNumber'] ?? '');
            $pwdIdIssuedDate = trim($_POST['pwdIdIssuedDate'] ?? '');
            $pwdIdIssuingLGU = trim($_POST['pwdIdIssuingLGU'] ?? '');
            $action = trim($_POST['action'] ?? '');
            $skipImage = ($_POST['skipImage'] ?? 'true') === 'false'; // If skipImage is false, we have a file
        } else {
            // Handle JSON request (without file upload)
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                ApiResponse::error('Invalid JSON input');
                exit;
            }
            
            $pwdIdNumber = trim($input['pwdIdNumber'] ?? '');
            $pwdIdIssuedDate = trim($input['pwdIdIssuedDate'] ?? '');
            $pwdIdIssuingLGU = trim($input['pwdIdIssuingLGU'] ?? '');
            $action = trim($input['action'] ?? '');
            $skipImage = ($input['skipImage'] ?? 'true') === 'true';
        }
        
        // Validate action
        if ($action !== 'verify') {
            ApiResponse::error('Invalid action');
            exit;
        }
        
        // Validate required fields
        if (empty($pwdIdNumber) || empty($pwdIdIssuedDate) || empty($pwdIdIssuingLGU)) {
            ApiResponse::error('Missing required PWD ID information');
            exit;
        }
        
        // Get seeker_id (from session or token)
        $seekerId = $_SESSION['seeker_id'] ?? null;
        
        // Create DOH Verifier instance
        $dohVerifier = new DOHPwdVerifier();
        
        // Step 1: Try DOH online verification first (if skipImage is true)
        if ($skipImage) {
            $dohVerificationResult = $dohVerifier->verify($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU);
            
            if ($dohVerificationResult['verified']) {
                // DOH verification successful
                $verificationToken = generateVerificationToken($pwdIdNumber);
                
                $_SESSION['pwd_verification'] = [
                    'token' => $verificationToken,
                    'pwdIdNumber' => $pwdIdNumber,
                    'verified' => true,
                    'timestamp' => time()
                ];
                
                // Update database if we have seeker_id
                if ($seekerId) {
                    updatePwdVerificationStatus($conn, $seekerId, $pwdIdNumber, 'verified', true);
                }
                
                logVerificationAttempt($pwdIdNumber, 'SUCCESS', 'DOH online verification successful');
                
                ApiResponse::success([
                    'message' => 'Your PWD ID has been successfully verified through DOH database.',
                    'token' => $verificationToken,
                    'verified' => true,
                    'status' => 'verified'
                ]);
                exit;
            } 
            
            // DOH verification failed - request file upload
            if ($dohVerificationResult['status'] === 'service_unavailable') {
                ApiResponse::success([
                    'message' => 'The DOH verification service is currently unavailable. Please upload your PWD ID image for manual verification.',
                    'verified' => false,
                    'status' => 'service_unavailable',
                    'requireUpload' => true
                ]);
                exit;
            } else {
                ApiResponse::success([
                    'message' => $dohVerificationResult['message'] . ' Please upload your PWD ID for manual verification.',
                    'verified' => false,
                    'status' => 'verification_failed',
                    'requireUpload' => true
                ]);
                exit;
            }
        }
        
        // Step 2: Handle file upload for manual verification
        $uploadPath = null;
        
        if (!$skipImage && isset($_FILES['pwdIdFile'])) {
            // Process file upload
            $uploadedFile = $_FILES['pwdIdFile'];
            
            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                ApiResponse::error('File upload failed');
                exit;
            }
            
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            $fileType = mime_content_type($uploadedFile['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                ApiResponse::error('Invalid file type. Only JPG, PNG, and PDF files are allowed.');
                exit;
            }
            
            // Check file size (5MB limit)
            if ($uploadedFile['size'] > 5 * 1024 * 1024) {
                ApiResponse::error('File size too large. Maximum size is 5MB.');
                exit;
            }
            
            // Create upload directory
            $uploadDir = '../../uploads/pwd_ids/' . date('Y/m/') . '/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
            $fileName = uniqid('pwd_', true) . '.' . $fileExtension;
            $fullPath = $uploadDir . $fileName;
            $uploadPath = 'uploads/pwd_ids/' . date('Y/m/') . '/' . $fileName;
            
            // Move uploaded file
            if (!move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
                ApiResponse::error('Failed to save uploaded file');
                exit;
            }
            
            logVerificationAttempt($pwdIdNumber, 'FILE_UPLOADED', 'File uploaded for manual verification: ' . $fileName);
        }
        
        // Step 3: Set pending status for manual verification
        $verificationToken = generateVerificationToken($pwdIdNumber);
        
        $_SESSION['pwd_verification'] = [
            'token' => $verificationToken,
            'pwdIdNumber' => $pwdIdNumber,
            'verified' => false,
            'pending' => true,
            'timestamp' => time()
        ];
        
        // Update database
        if ($seekerId) {
            updatePwdVerificationStatus($conn, $seekerId, $pwdIdNumber, 'pending', false, $uploadPath);
            
            // Create admin notification
            createAdminNotification($conn, $seekerId, $pwdIdNumber, $uploadPath);
        }
        
        logVerificationAttempt($pwdIdNumber, 'PENDING', 'Manual verification required, file uploaded: ' . ($uploadPath ?? 'none'));
        
        ApiResponse::success([
            'message' => 'Your PWD ID has been submitted for manual verification. You will be notified within 24 hours.',
            'token' => $verificationToken,
            'verified' => false,
            'status' => 'pending_verification',
            'estimated_review_time' => '24 hours'
        ]);
        
    } catch (Exception $e) {
        error_log("PWD Verification Error: " . $e->getMessage());
        ApiResponse::error('An error occurred during verification. Please try again.');
    }
} else {
    ApiResponse::error('Invalid request method');
}

/**
 * Update PWD verification status in database
 */
function updatePwdVerificationStatus($conn, $seekerId, $pwdIdNumber, $status, $isVerified, $imagePath = null) {
    try {
        // Check if record exists
        $stmt = $conn->prepare("SELECT pwd_id FROM pwd_ids WHERE seeker_id = ? AND pwd_id_number = ?");
        $stmt->execute([$seekerId, $pwdIdNumber]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE pwd_ids SET 
                    verification_status = ?, 
                    is_verified = ?,
                    verification_attempts = verification_attempts + 1,
                    updated_at = NOW()";
            $params = [$status, $isVerified ? 1 : 0];
            
            if ($imagePath) {
                $sql .= ", id_image_path = ?";
                $params[] = $imagePath;
            }
            
            $sql .= " WHERE seeker_id = ? AND pwd_id_number = ?";
            $params[] = $seekerId;
            $params[] = $pwdIdNumber;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            // Create new record
            $stmt = $conn->prepare("
                INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, verification_status, is_verified, id_image_path)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $seekerId, 
                $pwdIdNumber, 
                date('Y-m-d'), 
                $status, 
                $isVerified ? 1 : 0, 
                $imagePath
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database update error: " . $e->getMessage());
    }
}

/**
 * Create admin notification for manual verification
 */
function createAdminNotification($conn, $seekerId, $pwdIdNumber, $imagePath) {
    try {
        // First, ensure the admin_notifications table exists
        createAdminNotificationsTable($conn);
        
        // Get user name
        $stmt = $conn->prepare("SELECT first_name, last_name FROM job_seekers WHERE seeker_id = ?");
        $stmt->execute([$seekerId]);
        $user = $stmt->fetch();
        $userName = $user ? ($user['first_name'] . ' ' . $user['last_name']) : 'User ID ' . $seekerId;
        
        // Create notification
        $stmt = $conn->prepare("
            INSERT INTO admin_notifications (type, title, message, data, created_at) 
            VALUES ('pwd_verification', 'PWD ID Verification Required', ?, ?, NOW())
        ");
        
        $message = "User {$userName} requires PWD ID verification";
        $data = json_encode([
            'seeker_id' => $seekerId,
            'pwd_id_number' => $pwdIdNumber,
            'image_path' => $imagePath,
            'submission_time' => date('Y-m-d H:i:s')
        ]);
        
        $stmt->execute([$message, $data]);
    } catch (PDOException $e) {
        error_log("Failed to create admin notification: " . $e->getMessage());
    }
}

/**
 * Create admin_notifications table if it doesn't exist
 */
function createAdminNotificationsTable($conn) {
    try {
        $sql = "CREATE TABLE IF NOT EXISTS admin_notifications (
            notification_id INT PRIMARY KEY AUTO_INCREMENT,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            data JSON,
            status ENUM('unread', 'read', 'archived') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL,
            INDEX idx_type (type),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        )";
        $conn->exec($sql);
    } catch (PDOException $e) {
        error_log("Failed to create admin_notifications table: " . $e->getMessage());
    }
}
?>