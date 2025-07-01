<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in and is an employer
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$employer_id = $_SESSION['employer_id'];
$file = $_FILES['logo'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/svg+xml'];
$file_type = $file['type'];

if (!in_array($file_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and SVG are allowed']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
    exit;
}

try {
    // Create upload directory if it doesn't exist
    $upload_dir = '../../uploads/company_logos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $employer_id . '_logo_' . uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Update database
    $conn->beginTransaction();
    
    // Remove old logo file if exists
    $get_old_logo_sql = "SELECT company_logo_path FROM employers WHERE employer_id = :employer_id";
    $get_stmt = $conn->prepare($get_old_logo_sql);
    $get_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $get_stmt->execute();
    $result = $get_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && $result['company_logo_path'] && file_exists('../../' . $result['company_logo_path'])) {
        unlink('../../' . $result['company_logo_path']);
    }
    
    // Update employer with new logo path
    $relative_path = 'uploads/company_logos/' . $filename;
    $update_sql = "UPDATE employers SET company_logo_path = :logo_path, updated_at = NOW() WHERE employer_id = :employer_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':logo_path', $relative_path);
    $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $update_stmt->execute();
    
    // Update progress
    updateProgressStep($conn, $employer_id, 'logo_uploaded', 1, 'logo_upload_complete', 1, 35);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Logo uploaded successfully',
        'file_path' => $relative_path,
        'progress' => 35
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    
    // Remove uploaded file if database update failed
    if (isset($file_path) && file_exists($file_path)) {
        unlink($file_path);
    }
    
    error_log("Logo upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to upload logo']);
}

function updateProgressStep($conn, $employer_id, $step1_column, $step1_value, $step2_column, $step2_value, $percentage) {
    // Check if progress record exists
    $check_sql = "SELECT progress_id FROM employer_setup_progress WHERE employer_id = :employer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $result = $check_stmt->fetch();
    
    if (!$result) {
        // Create new progress record
        $insert_sql = "INSERT INTO employer_setup_progress 
                      (employer_id, $step1_column, $step2_column, completion_percentage, updated_at) 
                      VALUES (:employer_id, :step1_value, :step2_value, :percentage, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':step1_value', $step1_value, PDO::PARAM_INT);
        $insert_stmt->bindParam(':step2_value', $step2_value, PDO::PARAM_INT);
        $insert_stmt->bindParam(':percentage', $percentage, PDO::PARAM_INT);
        $insert_stmt->execute();
    } else {
        // Update existing progress record
        $update_sql = "UPDATE employer_setup_progress 
                      SET $step1_column = :step1_value, $step2_column = :step2_value, completion_percentage = :percentage, updated_at = NOW() 
                      WHERE employer_id = :employer_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':step1_value', $step1_value, PDO::PARAM_INT);
        $update_stmt->bindParam(':step2_value', $step2_value, PDO::PARAM_INT);
        $update_stmt->bindParam(':percentage', $percentage, PDO::PARAM_INT);
        $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $update_stmt->execute();
    }
}
?>