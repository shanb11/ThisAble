<?php
/**
 * PWD ID File Upload Handler
 * Location: C:\xampp\htdocs\ThisAble\api\candidate\upload_pwd_id.php
 */

// Start session and set headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once('../config/response.php');
require_once('../config/database.php');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include required files
//require_once('../../backend/db.php');
//require_once('../config/ApiResponse.php');  // Instead of ../../backend/

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $action = $_POST['action'] ?? '';
    $pwdIdNumber = trim($_POST['pwdIdNumber'] ?? '');
    $pwdIdIssuedDate = trim($_POST['pwdIdIssuedDate'] ?? '');
    $pwdIdIssuingLGU = trim($_POST['pwdIdIssuingLGU'] ?? '');

    // Validate required fields
    if ($action !== 'upload') {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    if (empty($pwdIdNumber) || empty($pwdIdIssuedDate) || empty($pwdIdIssuingLGU)) {
        echo json_encode(['success' => false, 'message' => 'Missing required PWD ID information']);
        exit;
    }

    // Check if file was uploaded
    if (!isset($_FILES['pwdIdFile']) || $_FILES['pwdIdFile']['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
            UPLOAD_ERR_PARTIAL => 'File upload was interrupted',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error',
            UPLOAD_ERR_CANT_WRITE => 'Failed to save file',
            UPLOAD_ERR_EXTENSION => 'File type blocked by server'
        ];
        
        $error_code = $_FILES['pwdIdFile']['error'] ?? UPLOAD_ERR_NO_FILE;
        $error_message = $error_messages[$error_code] ?? 'Unknown upload error';
        
        echo json_encode(['success' => false, 'message' => 'File upload failed: ' . $error_message]);
        exit;
    }

    $uploadedFile = $_FILES['pwdIdFile'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $fileType = mime_content_type($uploadedFile['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed.']);
        exit;
    }

    // Validate file size (5MB limit)
    if ($uploadedFile['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
        exit;
    }

    // Make authentication optional - allow uploads during signup
    $seekerId = null;

    // Check if this is coming from an authenticated user
    try {
        $user = requireAuth();
        if ($user && $user['user_type'] === 'candidate') {
            $seekerId = $user['user_id'];
            error_log("PWD Upload: Authenticated user - seeker_id: $seekerId");
        }
    } catch (Exception $e) {
        // Not authenticated - this is OK during signup
        error_log("PWD Upload: No authentication - signup mode");
    }

    $seekerId = $user['user_id'];

    // Create upload directory structure
    $uploadBaseDir = '../../uploads/pwd_ids/';
    $uploadYearMonth = date('Y/m/');
    $uploadDir = $uploadBaseDir . $uploadYearMonth;
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
            exit;
        }
    }

    // Generate unique filename
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    $uniqueId = uniqid('pwd_', true);
    $fileName = $uniqueId . '.' . $fileExtension;
    $fullPath = $uploadDir . $fileName;
    $relativePath = 'uploads/pwd_ids/' . $uploadYearMonth . $fileName;

    // Move uploaded file
    if (!move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        exit;
    }

    // Log the upload attempt
    logUploadAttempt($pwdIdNumber, 'SUCCESS', 'File uploaded: ' . $fileName);

    // If we have a seeker_id, update the database
    if ($seekerId) {
        try {
            // Check if PWD record exists
            $stmt = $conn->prepare("SELECT pwd_id FROM pwd_ids WHERE seeker_id = ? AND pwd_id_number = ?");
            $stmt->execute([$seekerId, $pwdIdNumber]);
            $existingRecord = $stmt->fetch();

            if ($existingRecord) {
                // Update existing record
                $stmt = $conn->prepare("
                    UPDATE pwd_ids 
                    SET id_image_path = ?, 
                        verification_status = 'pending',
                        verification_attempts = verification_attempts + 1,
                        updated_at = NOW()
                    WHERE seeker_id = ? AND pwd_id_number = ?
                ");
                $stmt->execute([$relativePath, $seekerId, $pwdIdNumber]);
            } else {
                // Create new record
                $stmt = $conn->prepare("
                    INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, id_image_path, verification_status, is_verified)
                    VALUES (?, ?, ?, ?, 'pending', 0)
                ");
                $stmt->execute([$seekerId, $pwdIdNumber, $pwdIdIssuedDate, $relativePath]);
            }
            
            // Create admin notification
            createAdminNotification($seekerId, $pwdIdNumber, $fileName);
            
        } catch (PDOException $e) {
            error_log("Database error in PWD upload: " . $e->getMessage());
            // Continue without database update - file is still uploaded
        }
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'PWD ID uploaded successfully for manual verification',
        'data' => [
            'filename' => $fileName,
            'upload_time' => date('Y-m-d H:i:s'),
            'status' => 'pending_verification',
            'next_steps' => 'Our team will review your PWD ID within 24 hours and notify you of the result.'
        ]
    ]);
    exit;

} catch (Exception $e) {
    error_log("PWD Upload Error: " . $e->getMessage());
    ApiResponse::error('An error occurred while uploading your PWD ID. Please try again.');
}

/**
 * Log upload attempts for debugging and monitoring
 */
function logUploadAttempt($pwdIdNumber, $result, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logDir = '../../logs/uploads';
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logMessage = date('Y-m-d H:i:s') . " | IP: $ip | PWD ID: $pwdIdNumber | Result: $result | $details\n";
    file_put_contents("$logDir/pwd_upload_log.txt", $logMessage, FILE_APPEND);
}

/**
 * Create admin notification for new upload
 */
function createAdminNotification($seekerId, $pwdIdNumber, $fileName) {
    global $conn;
    
    try {
        // Get user name for notification
        $stmt = $conn->prepare("SELECT first_name, last_name FROM job_seekers WHERE seeker_id = ?");
        $stmt->execute([$seekerId]);
        $user = $stmt->fetch();
        
        $userName = $user ? ($user['first_name'] . ' ' . $user['last_name']) : 'User ID ' . $seekerId;
        
        $stmt = $conn->prepare("
            INSERT INTO admin_notifications (type, title, message, data, created_at) 
            VALUES ('pwd_upload', 'New PWD ID Upload', ?, ?, NOW())
        ");
        
        $message = "User {$userName} has uploaded a PWD ID for verification";
        $data = json_encode([
            'seeker_id' => $seekerId,
            'pwd_id_number' => $pwdIdNumber,
            'filename' => $fileName,
            'upload_time' => date('Y-m-d H:i:s')
        ]);
        
        $stmt->execute([$message, $data]);
        
    } catch (PDOException $e) {
        error_log("Failed to create admin notification: " . $e->getMessage());
    }
}

/**
 * Send email notification to admin team (optional)
 */
function notifyAdminTeam($seekerId, $pwdIdNumber) {
    // TODO: Implement email notification to admin team
    // This could send an email to your verification team
    
    $adminEmail = 'admin@thisable.com'; // Replace with your admin email
    $subject = 'New PWD ID Verification Required';
    $message = "
    A new PWD ID has been uploaded for verification.
    
    User ID: {$seekerId}
    PWD ID Number: {$pwdIdNumber}
    Upload Time: " . date('Y-m-d H:i:s') . "
    
    Please log into the admin dashboard to review and verify this submission.
    ";
    
    // Uncomment when ready to send emails
    // mail($adminEmail, $subject, $message);
}
?>