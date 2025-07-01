<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Display all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once('../db.php');

// Get seeker ID
$seekerId = isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null;

// Allow override via query parameter
if (!$seekerId && isset($_GET['seeker_id'])) {
    $seekerId = $_GET['seeker_id'];
}

if (!$seekerId) {
    echo "Error: No seeker ID found in session or GET parameters.";
    exit;
}

try {
    // Update database directly using PDO
    $stmt = $conn->prepare("UPDATE job_seekers SET setup_complete = TRUE WHERE seeker_id = ?");
    $stmt->bindParam(1, $seekerId, PDO::PARAM_INT);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to update setup_complete: " . implode(", ", $stmt->errorInfo()));
    }
    
    // Set session explicitly
    $_SESSION['setup_complete'] = true;
    $_SESSION['logged_in'] = true;
    $_SESSION['seeker_id'] = $seekerId;
    
    // Success message
    echo "Setup marked as complete for seeker ID: $seekerId<br>";
    echo "Session variables set:<br>";
    echo "- setup_complete: " . ($_SESSION['setup_complete'] ? "true" : "false") . "<br>";
    echo "- logged_in: " . ($_SESSION['logged_in'] ? "true" : "false") . "<br>";
    echo "- seeker_id: " . $_SESSION['seeker_id'] . "<br>";
    
    echo "<p><a href='../../frontend/candidate/dashboard.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>