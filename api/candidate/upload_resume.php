<?php
/**
 * Upload Resume API for ThisAble Mobile
 * Based on existing web upload logic but adapted for mobile authentication
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data
error_log("=== MOBILE RESUME UPLOAD DEBUG ===");
error_log("REQUEST METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("REQUEST URI: " . $_SERVER["REQUEST_URI"]);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication (same as save_skills.php)
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    error_log("AUTH SUCCESS: seeker_id=$seekerId");

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
        ApiResponse::error($errorMsg, 400);
    }

    // Get file details
    $fileName = $_FILES['resume_file']['name'];
    $fileSize = $_FILES['resume_file']['size'];
    $fileTmp = $_FILES['resume_file']['tmp_name'];
    $fileType = $_FILES['resume_file']['type'];

    error_log("FILE DETAILS: name=$fileName, size=$fileSize, type=$fileType");

    // Validate file type (PDF only as requested)
    // FIXED - Add octet-stream for mobile compatibility:
    $allowedTypes = ['application/pdf', 'application/octet-stream'];
    if (!in_array($fileType, $allowedTypes)) {
        error_log("VALIDATION ERROR: Invalid file type: $fileType");
        ApiResponse::validationError(['file_type' => 'Only PDF files are allowed']);
    }

    // ADDITIONAL SECURITY: Validate file extension for octet-stream files
    if ($fileType === 'application/octet-stream') {
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExtension !== 'pdf') {
            error_log("VALIDATION ERROR: Invalid file extension for octet-stream: $fileExtension");
            ApiResponse::validationError(['file_type' => 'Only PDF files are allowed']);
        }
        error_log("SECURITY CHECK: octet-stream file has .pdf extension - allowing");
    }

    // Validate file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        error_log("VALIDATION ERROR: File size too large: $fileSize bytes");
        ApiResponse::validationError(['file_size' => 'File size exceeds 5MB limit']);
    }

    // Create upload directory if it doesn't exist (same path as web)
    $uploadDir = '../../uploads/resumes/';
    if (!file_exists($uploadDir)) {
        error_log("Creating directory: $uploadDir");
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("ERROR: Failed to create directory: $uploadDir");
            ApiResponse::serverError('Failed to create upload directory');
        }
    }

    // Generate unique filename (same pattern as web)
    $uniqueName = $seekerId . '_' . uniqid() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
    $filePath = $uploadDir . $uniqueName;
    error_log("FILE PATH: $filePath");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Move uploaded file to destination
        if (move_uploaded_file($fileTmp, $filePath)) {
            error_log("FILE MOVED SUCCESSFULLY");
            
            // Store file information in database (same structure as web)
            $relativePath = 'uploads/resumes/' . $uniqueName;
            
            // Mark old resumes as not current first (for setup flow, this ensures clean state)
            $updateOldStmt = $conn->prepare("UPDATE resumes SET is_current = 0 WHERE seeker_id = ?");
            $updateOldStmt->execute([$seekerId]);
            error_log("OLD RESUMES MARKED AS NOT CURRENT");
            
            // Insert new resume
            $sql = "INSERT INTO resumes (seeker_id, file_name, file_path, file_size, file_type, is_current) 
                    VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute([$seekerId, $fileName, $relativePath, $fileSize, $fileType])) {
                error_log("DATABASE INSERT: SUCCESS");
                
                // Commit transaction
                $conn->commit();
                
                // Log activity
                ApiResponse::logActivity('resume_uploaded', [
                    'user_id' => $seekerId,
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                    'file_path' => $relativePath
                ]);
                
                ApiResponse::success([
                    'resume_uploaded' => true,
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                    'file_path' => $relativePath
                ], "Resume uploaded successfully");
                
            } else {
                // Rollback transaction
                $conn->rollBack();
                error_log("DATABASE ERROR: " . print_r($stmt->errorInfo(), true));
                // Delete uploaded file since database failed
                unlink($filePath);
                ApiResponse::serverError('Database error occurred');
            }
        } else {
            // Rollback transaction
            $conn->rollBack();
            error_log("FILE MOVE ERROR: Failed to move from $fileTmp to $filePath");
            ApiResponse::serverError('Failed to save uploaded file');
        }
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Resume upload database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Resume upload error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while uploading resume");
}

error_log("=== MOBILE RESUME UPLOAD DEBUG END ===");
?>