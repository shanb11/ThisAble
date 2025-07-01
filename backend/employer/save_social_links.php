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

$employer_id = $_SESSION['employer_id'];
$company_url = trim($_POST['company_url'] ?? '');
$facebook_url = trim($_POST['facebook_url'] ?? '');
$linkedin_url = trim($_POST['linkedin_url'] ?? '');

// Validation
$errors = [];

// Company URL is required
if (empty($company_url)) {
    $errors[] = 'Company website URL is required';
} else {
    // Validate URL format
    if (!filter_var($company_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL (include https://)';
    }
}

// Social media URLs are optional, but validate if provided
if (!empty($facebook_url)) {
    // Validate Facebook username (alphanumeric, dots, hyphens, underscores)
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $facebook_url)) {
        $errors[] = 'Please enter a valid Facebook username';
    } else {
        // Prepend full URL
        $facebook_url = 'https://facebook.com/' . $facebook_url;
    }
}

if (!empty($linkedin_url)) {
    // Validate LinkedIn company name (alphanumeric, dots, hyphens, underscores)
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $linkedin_url)) {
        $errors[] = 'Please enter a valid LinkedIn company name';
    } else {
        // Prepend full URL
        $linkedin_url = 'https://linkedin.com/company/' . $linkedin_url;
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Check if social links record exists
    $check_sql = "SELECT social_id FROM employer_social_links WHERE employer_id = :employer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $result = $check_stmt->fetch();
    
    if (!$result) {
        // Insert new record
        $insert_sql = "INSERT INTO employer_social_links 
                      (employer_id, website_url, facebook_url, linkedin_url, created_at, updated_at) 
                      VALUES (:employer_id, :website_url, :facebook_url, :linkedin_url, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':website_url', $company_url);
        $insert_stmt->bindParam(':facebook_url', $facebook_url);
        $insert_stmt->bindParam(':linkedin_url', $linkedin_url);
        $insert_stmt->execute();
    } else {
        // Update existing record
        $update_sql = "UPDATE employer_social_links 
                      SET website_url = :website_url, facebook_url = :facebook_url, linkedin_url = :linkedin_url, updated_at = NOW() 
                      WHERE employer_id = :employer_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':website_url', $company_url);
        $update_stmt->bindParam(':facebook_url', $facebook_url);
        $update_stmt->bindParam(':linkedin_url', $linkedin_url);
        $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $update_stmt->execute();
    }
    
    // Update progress
    updateProgressStep($conn, $employer_id, 'social_links_complete', 1, 'social_complete', 1, 85);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Social links saved successfully',
        'progress' => 85
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Social links save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save social links']);
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