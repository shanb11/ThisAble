<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Get form data
$work_style = $_POST['work_style'] ?? '';
$job_type = $_POST['job_type'] ?? '';
$salary_range = $_POST['salary_range'] ?? '';
$availability = $_POST['availability'] ?? '';

try {
    // Check if user preferences exist
    $check_query = "SELECT preference_id FROM user_preferences WHERE seeker_id = :seeker_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing preferences
        $query = "UPDATE user_preferences SET 
                  work_style = :work_style,
                  job_type = :job_type,
                  salary_range = :salary_range,
                  availability = :availability
                  WHERE seeker_id = :seeker_id";
    } else {
        // Insert new preferences
        $query = "INSERT INTO user_preferences (seeker_id, work_style, job_type, salary_range, availability) 
                  VALUES (:seeker_id, :work_style, :job_type, :salary_range, :availability)";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindParam(':work_style', $work_style);
    $stmt->bindParam(':job_type', $job_type);
    $stmt->bindParam(':salary_range', $salary_range);
    $stmt->bindParam(':availability', $availability);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Work preferences updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update preferences']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>