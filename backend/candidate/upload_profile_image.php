<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
require_once '../db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$seekerId = $_SESSION['seeker_id'];

// Check if image type is specified
if (!isset($_POST['image_type']) || !in_array($_POST['image_type'], ['profile', 'cover'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image type']);
    exit;
}

$imageType = $_POST['image_type'];
$fileInputName = $imageType . '_image';

// Check if file was uploaded
if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES[$fileInputName]) ? $_FILES[$fileInputName]['error'] : 'No file uploaded';
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $error]);
    exit;
}

// Get file details
$fileName = $_FILES[$fileInputName]['name'];
$fileSize = $_FILES[$fileInputName]['size'];
$fileTmp = $_FILES[$fileInputName]['tmp_name'];
$fileType = $_FILES[$fileInputName]['type'];

// Validate file type (only images)
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.']);
    exit;
}

// Validate file size (5MB max)
if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../../uploads/profile_images/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Generate unique filename
$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$uniqueName = $seekerId . '_' . $imageType . '_' . uniqid() . '.' . $extension;
$filePath = $uploadDir . $uniqueName;

// Move uploaded file to destination
if (move_uploaded_file($fileTmp, $filePath)) {
    // Store file information in database
    $relativePath = 'uploads/profile_images/' . $uniqueName;
    
    try {
        // Check if profile_details record exists
        $checkStmt = $conn->prepare("SELECT profile_id FROM profile_details WHERE seeker_id = ?");
        $checkStmt->execute([$seekerId]);
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing record
            $columnName = $imageType === 'profile' ? 'profile_photo_path' : 'cover_photo_path';
            $updateSql = "UPDATE profile_details SET {$columnName} = ?, updated_at = CURRENT_TIMESTAMP WHERE seeker_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            if ($updateStmt->execute([$relativePath, $seekerId])) {
                echo json_encode([
                    'success' => true, 
                    'message' => ucfirst($imageType) . ' photo updated successfully',
                    'image_url' => '../../' . $relativePath
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed']);
            }
        } else {
            // Insert new record
            $insertSql = "INSERT INTO profile_details (seeker_id, " . 
                        ($imageType === 'profile' ? 'profile_photo_path' : 'cover_photo_path') . 
                        ", created_at, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $insertStmt = $conn->prepare($insertSql);
            
            if ($insertStmt->execute([$seekerId, $relativePath])) {
                echo json_encode([
                    'success' => true, 
                    'message' => ucfirst($imageType) . ' photo uploaded successfully',
                    'image_url' => '../../' . $relativePath
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database insert failed']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>