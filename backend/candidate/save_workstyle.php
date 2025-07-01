<?php
// Include database connection
include_once '../db.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log incoming request for debugging
error_log("save_workstyle.php called. POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Get seeker ID from POST, localStorage via POST, or session
$seekerId = isset($_POST['seeker_id']) ? $_POST['seeker_id'] : (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null);
$workStyle = isset($_POST['work_style']) ? $_POST['work_style'] : null;

// Validate inputs
if (!$seekerId) {
    error_log("ERROR: No seeker ID found in save_workstyle.php");
    echo json_encode(['success' => false, 'message' => 'No user ID found. Please log in again.']);
    exit;
}

if (!$workStyle || !in_array($workStyle, ['remote', 'hybrid', 'onsite'])) {
    error_log("ERROR: Invalid work style: " . $workStyle);
    echo json_encode(['success' => false, 'message' => 'Invalid work style selection.']);
    exit;
}

try {
    // Check if record exists
    $checkQuery = "SELECT preference_id FROM user_preferences WHERE seeker_id = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "i", $seekerId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Update existing record
        $updateQuery = "UPDATE user_preferences SET work_style = ? WHERE seeker_id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "si", $workStyle, $seekerId);
    } else {
        // Insert new record
        $insertQuery = "INSERT INTO user_preferences (seeker_id, work_style) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "is", $seekerId, $workStyle);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        error_log("Work style saved successfully for seeker ID: " . $seekerId);
        echo json_encode(['success' => true]);
    } else {
        throw new Exception(mysqli_error($conn));
    }
    
} catch (Exception $e) {
    error_log("ERROR in save_workstyle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>