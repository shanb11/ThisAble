<?php
/**
 * Resume Upload Handler - FIXED VERSION
 * Works on both localhost (XAMPP) and Railway production
 * 
 * FIXES:
 * 1. Uses absolute paths instead of relative paths
 * 2. Better error logging for debugging
 * 3. Proper directory creation with permissions
 * 4. Environment-aware file paths
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once __DIR__ . '/../db.php';

// Enable detailed error logging
ini_set('display_errors', 0); // Don't show errors to user
error_reporting(E_ALL);

// LOG EVERYTHING FOR DEBUGGING
error_log("=== RESUME UPLOAD DEBUG START ===");
error_log("Timestamp: " . date('Y-m-d H:i:s'));
error_log("Environment: " . ($_SERVER['HTTP_HOST'] ?? 'unknown'));
error_log("Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown'));
error_log("Script Path: " . __FILE__);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// Determine seeker ID source (setup flow vs profile page)
$seekerId = null;
$isSetupFlow = false;
$authMethod = '';

// Check if seeker_id is provided (setup flow)
if (isset($_POST['seeker_id']) && !empty($_POST['seeker_id'])) {
    $seekerId = $_POST['seeker_id'];
    $isSetupFlow = true;
    $authMethod = 'POST parameter';
    error_log("Setup flow detected with seeker_id: $seekerId");
} 
// Check session for profile page flow
elseif (isset($_SESSION['seeker_id'])) {
    $seekerId = $_SESSION['seeker_id'];
    $isSetupFlow = false;
    $authMethod = 'SESSION';
    error_log("Profile flow detected with session seeker_id: $seekerId");
} 
// No valid seeker ID found
else {
    error_log("ERROR: No valid seeker ID found");
    error_log("POST seeker_id: " . (isset($_POST['seeker_id']) ? $_POST['seeker_id'] : 'NOT SET'));
    error_log("SESSION seeker_id: " . (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : 'NOT SET'));
    error_log("SESSION logged_in: " . (isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : 'NOT SET'));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Authentication required. Please log in and try again.',
        'debug' => [
            'post_seeker_id' => isset($_POST['seeker_id']) ? $_POST['seeker_id'] : null,
            'session_seeker_id' => isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null,
            'session_logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : null
        ]
    ]);
    exit;
}

// Additional security check for profile flow
if (!$isSetupFlow && !isset($_SESSION['logged_in'])) {
    error_log("ERROR: Profile flow attempted without proper login session");
    echo json_encode(['success' => false, 'message' => 'Please log in to upload resume']);
    exit;
}

error_log("AUTH SUCCESS: seeker_id=$seekerId, method=$authMethod, flow=" . ($isSetupFlow ? 'setup' : 'profile'));

// Check if file was uploaded
if (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['resume_file']) ? $_FILES['resume_file']['error'] : 'No file uploaded';
    error_log("FILE ERROR: $error");
    
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit', 
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $errorMsg = isset($errorMessages[$error]) ? $errorMessages[$error] : "Upload error code: $error";
    error_log("FILE ERROR MESSAGE: $errorMsg");
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Get file details
$fileName = $_FILES['resume_file']['name'];
$fileSize = $_FILES['resume_file']['size'];
$fileTmp = $_FILES['resume_file']['tmp_name'];
$fileType = $_FILES['resume_file']['type'];

error_log("FILE DETAILS: name=$fileName, size=$fileSize, type=$fileType, tmp=$fileTmp");

// Validate file type
$allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if (!in_array($fileType, $allowedTypes)) {
    error_log("VALIDATION ERROR: Invalid file type: $fileType");
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.']);
    exit;
}

// Validate file size (5MB max)
if ($fileSize > 5 * 1024 * 1024) {
    error_log("VALIDATION ERROR: File size too large: $fileSize bytes");
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
    exit;
}

// ===== FIX: Use absolute paths that work in both localhost and Railway =====
// Detect the project root directory
$projectRoot = realpath(__DIR__ . '/../../');
error_log("PROJECT ROOT: $projectRoot");

// Define upload directory using absolute path
$uploadDirRelative = 'uploads/resumes/';
$uploadDirAbsolute = $projectRoot . '/' . $uploadDirRelative;

error_log("UPLOAD DIR ABSOLUTE: $uploadDirAbsolute");
error_log("UPLOAD DIR EXISTS: " . (file_exists($uploadDirAbsolute) ? 'YES' : 'NO'));

// Create upload directory if it doesn't exist
if (!file_exists($uploadDirAbsolute)) {
    error_log("Creating directory: $uploadDirAbsolute");
    if (!mkdir($uploadDirAbsolute, 0755, true)) {
        $lastError = error_get_last();
        error_log("ERROR: Failed to create directory");
        error_log("LAST ERROR: " . print_r($lastError, true));
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create upload directory',
            'debug' => [
                'path' => $uploadDirAbsolute,
                'error' => $lastError
            ]
        ]);
        exit;
    }
    error_log("Directory created successfully");
}

// Check if directory is writable
if (!is_writable($uploadDirAbsolute)) {
    error_log("ERROR: Directory is not writable: $uploadDirAbsolute");
    
    // Try to change permissions
    @chmod($uploadDirAbsolute, 0755);
    
    if (!is_writable($uploadDirAbsolute)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Upload directory is not writable. Please check permissions.',
            'debug' => [
                'path' => $uploadDirAbsolute,
                'writable' => false,
                'exists' => file_exists($uploadDirAbsolute)
            ]
        ]);
        exit;
    }
}

error_log("Directory is writable: YES");

// Generate unique filename
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
$uniqueName = $seekerId . '_' . uniqid() . '.' . $fileExtension;
$filePathAbsolute = $uploadDirAbsolute . $uniqueName;
$filePathRelative = $uploadDirRelative . $uniqueName;

error_log("FILE PATH ABSOLUTE: $filePathAbsolute");
error_log("FILE PATH RELATIVE: $filePathRelative");

// Move uploaded file to destination
if (move_uploaded_file($fileTmp, $filePathAbsolute)) {
    error_log("FILE MOVED SUCCESSFULLY");
    
    // Verify file was created
    if (!file_exists($filePathAbsolute)) {
        error_log("ERROR: File does not exist after move");
        echo json_encode(['success' => false, 'message' => 'File upload verification failed']);
        exit;
    }
    
    error_log("FILE EXISTS AFTER MOVE: YES");
    error_log("FILE SIZE AFTER MOVE: " . filesize($filePathAbsolute));
    
    try {
        // For profile flow, mark old resumes as not current first
        if (!$isSetupFlow) {
            $updateOldStmt = $conn->prepare("UPDATE resumes SET is_current = 0 WHERE seeker_id = ?");
            $result = $updateOldStmt->execute([$seekerId]);
            error_log("OLD RESUMES UPDATE: " . ($result ? 'SUCCESS' : 'FAILED'));
        }
        
        // Insert new resume record into database
        $sql = "INSERT INTO resumes (seeker_id, file_name, file_path, file_size, file_type, is_current) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$seekerId, $fileName, $filePathRelative, $fileSize, $fileType])) {
            error_log("DATABASE INSERT: SUCCESS");
            
            $successMessage = $isSetupFlow ? 'Resume uploaded successfully' : 'Resume updated successfully!';
            
            echo json_encode([
                'success' => true, 
                'message' => $successMessage,
                'debug' => [
                    'seeker_id' => $seekerId,
                    'auth_method' => $authMethod,
                    'flow' => $isSetupFlow ? 'setup' : 'profile',
                    'file_path' => $filePathRelative,
                    'file_exists' => file_exists($filePathAbsolute)
                ]
            ]);
        } else {
            // Database insert failed - clean up uploaded file
            if (file_exists($filePathAbsolute)) {
                unlink($filePathAbsolute);
                error_log("FILE DELETED after database error");
            }
            
            error_log("DATABASE ERROR: " . print_r($stmt->errorInfo(), true));
            echo json_encode([
                'success' => false, 
                'message' => 'Database error: Failed to save resume information',
                'debug' => $stmt->errorInfo()
            ]);
        }
    } catch (Exception $e) {
        // Exception occurred - clean up uploaded file
        if (file_exists($filePathAbsolute)) {
            unlink($filePathAbsolute);
            error_log("FILE DELETED after exception");
        }
        
        error_log("EXCEPTION: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Database exception: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("FILE MOVE FAILED");
    error_log("Source (tmp): $fileTmp");
    error_log("Destination: $filePathAbsolute");
    error_log("Source exists: " . (file_exists($fileTmp) ? 'YES' : 'NO'));
    error_log("Destination dir exists: " . (file_exists($uploadDirAbsolute) ? 'YES' : 'NO'));
    error_log("Destination dir writable: " . (is_writable($uploadDirAbsolute) ? 'YES' : 'NO'));
    
    $lastError = error_get_last();
    error_log("LAST PHP ERROR: " . print_r($lastError, true));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to move uploaded file to server',
        'debug' => [
            'source' => $fileTmp,
            'destination' => $filePathAbsolute,
            'source_exists' => file_exists($fileTmp),
            'dest_dir_exists' => file_exists($uploadDirAbsolute),
            'dest_dir_writable' => is_writable($uploadDirAbsolute),
            'php_error' => $lastError
        ]
    ]);
}

error_log("=== RESUME UPLOAD DEBUG END ===");
?>