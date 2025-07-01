<?php
/**
 * Resume Upload Handler - Profile Page Specific
 * Backend file: backend/candidate/upload_resume_profile.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
require_once '../db.php';

// Enable error logging for debugging
error_log("=== PROFILE RESUME UPLOAD START ===");
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));
error_log("SESSION: seeker_id=" . ($_SESSION['seeker_id'] ?? 'NOT SET'));

// Check if user is logged in (Profile page flow)
if (!isset($_SESSION['seeker_id']) || !isset($_SESSION['logged_in'])) {
    error_log("AUTHENTICATION FAILED");
    echo json_encode([
        'success' => false, 
        'message' => 'Please log in to upload resume',
        'debug' => [
            'session_seeker_id' => $_SESSION['seeker_id'] ?? null,
            'session_logged_in' => $_SESSION['logged_in'] ?? null
        ]
    ]);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];
error_log("AUTHENTICATED: seeker_id=$seeker_id");

// Check if file was uploaded
if (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['resume_file']['error'] ?? 'No file uploaded';
    error_log("FILE ERROR: $error");
    
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit (check php.ini)',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit', 
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $errorMsg = $errorMessages[$error] ?? "Upload error code: $error";
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

// Get file details
$fileName = $_FILES['resume_file']['name'];
$fileSize = $_FILES['resume_file']['size'];
$fileTmp = $_FILES['resume_file']['tmp_name'];
$fileType = $_FILES['resume_file']['type'];

error_log("FILE DETAILS: name=$fileName, size=$fileSize, type=$fileType");

// Validate file type more thoroughly
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'doc', 'docx'];
$allowedMimeTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileType, $allowedMimeTypes)) {
    error_log("VALIDATION ERROR: Invalid file type - ext: $fileExtension, mime: $fileType");
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.',
        'debug' => [
            'extension' => $fileExtension,
            'mime_type' => $fileType,
            'allowed_extensions' => $allowedExtensions,
            'allowed_mime_types' => $allowedMimeTypes
        ]
    ]);
    exit;
}

// Validate file size (5MB max)
if ($fileSize > 5 * 1024 * 1024) {
    error_log("VALIDATION ERROR: File size too large: $fileSize bytes");
    echo json_encode([
        'success' => false, 
        'message' => 'File size exceeds 5MB limit. Your file is ' . round($fileSize / 1024 / 1024, 2) . 'MB.'
    ]);
    exit;
}

// Validate actual file content (additional security)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedType = finfo_file($finfo, $fileTmp);
finfo_close($finfo);

if (!in_array($detectedType, $allowedMimeTypes)) {
    error_log("SECURITY ERROR: File content doesn't match extension. Detected: $detectedType");
    echo json_encode([
        'success' => false, 
        'message' => 'File content validation failed. Please upload a valid document.'
    ]);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../../uploads/resumes/';
$fullUploadPath = __DIR__ . '/' . $uploadDir;

error_log("UPLOAD DIR: $fullUploadPath");

if (!file_exists($fullUploadPath)) {
    error_log("Creating directory: $fullUploadPath");
    if (!mkdir($fullUploadPath, 0755, true)) {
        error_log("ERROR: Failed to create directory");
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$uniqueName = $seeker_id . '_' . uniqid() . '.' . $fileExtension;
$fullFilePath = $fullUploadPath . $uniqueName;
$relativePath = 'uploads/resumes/' . $uniqueName;

error_log("FILE PATHS: full=$fullFilePath, relative=$relativePath");

// Move uploaded file
if (move_uploaded_file($fileTmp, $fullFilePath)) {
    error_log("FILE MOVED SUCCESSFULLY");
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Mark old resumes as not current
        $updateOldStmt = $conn->prepare("UPDATE resumes SET is_current = 0 WHERE seeker_id = ?");
        $updateResult = $updateOldStmt->execute([$seeker_id]);
        error_log("OLD RESUMES MARKED AS NOT CURRENT: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
        
        // Insert new resume
        $insertStmt = $conn->prepare("
            INSERT INTO resumes (seeker_id, file_name, file_path, file_size, file_type, is_current, upload_date) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $insertResult = $insertStmt->execute([$seeker_id, $fileName, $relativePath, $fileSize, $fileType]);
        
        if ($insertResult) {
            // Commit transaction
            $conn->commit();
            error_log("DATABASE INSERT: SUCCESS");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Resume uploaded successfully!',
                'debug' => [
                    'seeker_id' => $seeker_id,
                    'file_name' => $fileName,
                    'file_path' => $relativePath,
                    'file_size' => $fileSize,
                    'file_type' => $fileType
                ]
            ]);
        } else {
            // Rollback transaction
            $conn->rollback();
            
            // Delete uploaded file
            if (file_exists($fullFilePath)) {
                unlink($fullFilePath);
            }
            
            $errorInfo = $insertStmt->errorInfo();
            error_log("DATABASE INSERT ERROR: " . print_r($errorInfo, true));
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to save resume information to database',
                'debug' => $errorInfo
            ]);
        }
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Delete uploaded file
        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }
        
        error_log("DATABASE EXCEPTION: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("FILE MOVE FAILED");
    $lastError = error_get_last();
    error_log("LAST PHP ERROR: " . print_r($lastError, true));
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to save file to server',
        'debug' => [
            'source' => $fileTmp,
            'destination' => $fullFilePath,
            'directory_exists' => file_exists($fullUploadPath),
            'directory_writable' => is_writable($fullUploadPath),
            'php_error' => $lastError
        ]
    ]);
}

error_log("=== PROFILE RESUME UPLOAD END ===");
?>