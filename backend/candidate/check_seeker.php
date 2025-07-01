<?php
// Add this at the top of your backend PHP files
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include database connection
    require_once('../db.php');
    
    // Get seeker ID from multiple sources
    $seekerId = isset($_POST['seeker_id']) ? $_POST['seeker_id'] : 
               (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null);
    
    if (!$seekerId) {
        throw new Exception("No seeker ID found");
    }
    
    // Debug log
    error_log("check_seeker.php called with seeker_id: $seekerId");
    
    // Query the database
    $query = "SELECT js.* FROM job_seekers js WHERE js.seeker_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $seekerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // User exists in database
        $user = mysqli_fetch_assoc($result);
        
        // Log the found user
        error_log("Found user: " . print_r($user, true));
        
        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['seeker_id'] = $seekerId;
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['setup_complete'] = true;
        
        // Set cookies for redundancy
        setcookie('setup_complete', 'true', time() + (86400 * 30), '/');
        
        echo json_encode([
            'success' => true,
            'exists' => true,
            'setup_complete' => true,
            'user' => [
                'id' => $seekerId,
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ]
        ]);
    } else {
        error_log("No user found with seeker_id: $seekerId");
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => 'User not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in check_seeker.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>