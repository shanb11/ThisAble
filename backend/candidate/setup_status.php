<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include database connection
    require_once('../db.php');
    
    // Get seeker ID
    $seekerId = isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null;
    
    if (!$seekerId) {
        throw new Exception("No seeker ID in session");
    }
    
    // Check status in each table
    $status = array(
        'session' => array(
            'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : false,
            'setup_complete' => isset($_SESSION['setup_complete']) ? $_SESSION['setup_complete'] : false,
            'seeker_id' => $seekerId
        ),
        'database' => array(
            'skills_saved' => false,
            'preferences_saved' => false,
            'accommodations_saved' => false
        )
    );
    
    // Check if skills are saved
    $skillQuery = "SELECT COUNT(*) as count FROM seeker_skills WHERE seeker_id = $seekerId";
    $skillResult = mysqli_query($conn, $skillQuery);
    if ($skillResult && $row = mysqli_fetch_assoc($skillResult)) {
        $status['database']['skills_saved'] = ($row['count'] > 0);
        $status['database']['skill_count'] = $row['count'];
    }
    
    // Check if preferences are saved
    $prefQuery = "SELECT * FROM user_preferences WHERE seeker_id = $seekerId";
    $prefResult = mysqli_query($conn, $prefQuery);
    if ($prefResult && $row = mysqli_fetch_assoc($prefResult)) {
        $status['database']['preferences_saved'] = true;
        $status['database']['work_style'] = $row['work_style'];
        $status['database']['job_type'] = $row['job_type'];
    }
    
    // Check if accommodations are saved
    $accomQuery = "SELECT * FROM workplace_accommodations WHERE seeker_id = $seekerId";
    $accomResult = mysqli_query($conn, $accomQuery);
    if ($accomResult && $row = mysqli_fetch_assoc($accomResult)) {
        $status['database']['accommodations_saved'] = true;
        $status['database']['disability_type'] = $row['disability_type'];
    }
    
    // Return status
    echo json_encode($status);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>