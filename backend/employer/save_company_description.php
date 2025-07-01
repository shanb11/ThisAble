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
$about_us = trim($_POST['about_us'] ?? '');
$mission_vision = trim($_POST['mission_vision'] ?? '');
$why_join = trim($_POST['why_join'] ?? '');

// Validation
$errors = [];
if (empty($about_us)) {
    $errors[] = 'About Us field is required';
}
if (empty($mission_vision)) {
    $errors[] = 'Mission & Vision field is required';
}
if (empty($why_join)) {
    $errors[] = 'Why Join Us field is required';
}

// Character limits validation
if (strlen($about_us) > 500) {
    $errors[] = 'About Us must be 500 characters or less';
}
if (strlen($mission_vision) > 300) {
    $errors[] = 'Mission & Vision must be 300 characters or less';
}
if (strlen($why_join) > 400) {
    $errors[] = 'Why Join Us must be 400 characters or less';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Update employer description fields
    $update_sql = "UPDATE employers 
                   SET company_description = :about_us, mission_vision = :mission_vision, why_join_us = :why_join, updated_at = NOW() 
                   WHERE employer_id = :employer_id";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':about_us', $about_us);
    $update_stmt->bindParam(':mission_vision', $mission_vision);
    $update_stmt->bindParam(':why_join', $why_join);
    $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $update_stmt->execute();
    
    if ($update_stmt->rowCount() === 0) {
        throw new Exception('No employer record found to update');
    }
    
    // Update progress
    updateProgressStep($conn, $employer_id, 'company_description_complete', 1, 'description_complete', 1, 50);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Company description saved successfully',
        'progress' => 50
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Company description save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save company description']);
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