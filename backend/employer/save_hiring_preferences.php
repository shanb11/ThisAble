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
$disability_type = $_POST['disability_type'] ?? '';
$disabilities = json_decode($_POST['disabilities'] ?? '[]', true);
$accessibility_options = json_decode($_POST['accessibility'] ?? '[]', true);
$additional_accommodations = trim($_POST['additional_accommodations'] ?? '');

// Validation
$errors = [];
if (empty($disability_type)) {
    $errors[] = 'Please select accommodation type';
}
if (empty($disabilities)) {
    $errors[] = 'Please select at least one type of disability';
}
if (empty($accessibility_options)) {
    $errors[] = 'Please select at least one accessibility option';
}

// Character limit validation for additional accommodations
if (strlen($additional_accommodations) > 400) {
    $errors[] = 'Additional accommodations must be 400 characters or less';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Prepare data for database
    $disability_types_json = json_encode($disabilities);
    $workplace_accommodations_json = json_encode($accessibility_options);
    
    // Check if hiring preferences record exists
    $check_sql = "SELECT preference_id FROM employer_hiring_preferences WHERE employer_id = :employer_id";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $result = $check_stmt->fetch();
    
    if (!$result) {
        // Insert new record
        $insert_sql = "INSERT INTO employer_hiring_preferences 
                      (employer_id, open_to_pwd, disability_types, workplace_accommodations, additional_accommodations, created_at, updated_at) 
                      VALUES (:employer_id, 1, :disability_types, :workplace_accommodations, :additional_accommodations, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':disability_types', $disability_types_json);
        $insert_stmt->bindParam(':workplace_accommodations', $workplace_accommodations_json);
        $insert_stmt->bindParam(':additional_accommodations', $additional_accommodations);
        $insert_stmt->execute();
    } else {
        // Update existing record
        $update_sql = "UPDATE employer_hiring_preferences 
                      SET disability_types = :disability_types, workplace_accommodations = :workplace_accommodations, 
                          additional_accommodations = :additional_accommodations, updated_at = NOW() 
                      WHERE employer_id = :employer_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':disability_types', $disability_types_json);
        $update_stmt->bindParam(':workplace_accommodations', $workplace_accommodations_json);
        $update_stmt->bindParam(':additional_accommodations', $additional_accommodations);
        $update_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $update_stmt->execute();
    }
    
    // Update progress
    updateProgressStep($conn, $employer_id, 'hiring_preferences_complete', 1, 'preferences_complete', 1, 70);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Hiring preferences saved successfully',
        'progress' => 70
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Hiring preferences save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save hiring preferences']);
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