<?php
// session_check.php - Include at the top of pages that require login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Add this line to include the database connection (adjust path if needed)
require_once __DIR__ . '/../../backend/db.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Use this on pages that need authentication
function require_login() {
    if (!is_logged_in()) {
        // Redirect to login page
        header("Location: ../candidate/login.php");
        exit;
    }
    
    // Add this line to sync the setup status
    sync_setup_status();
}

// Get logged in user's name
function get_user_name() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

// Get logged in user's seeker ID
function get_seeker_id() {
    return isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null;
}

// Function to synchronize seekerId in localStorage with PHP session 
// Add this script to pages after login
function echo_session_sync_script() {
    if (is_logged_in()) {
        echo '
        <script>
            // Sync session data with localStorage
            localStorage.setItem("seekerId", "' . get_seeker_id() . '");
            localStorage.setItem("userName", "' . get_user_name() . '");
            localStorage.setItem("loggedIn", "true");
        </script>
        ';
    }
}

// Add this new function to sync setup status between database and session
function sync_setup_status() {
    if (isset($_SESSION['seeker_id'])) {
        global $conn;
        try {
            // Check database for setup status
            $query = "SELECT setup_complete FROM job_seekers WHERE seeker_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$_SESSION['seeker_id']]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['setup_complete'] = (bool)$row['setup_complete'];
                error_log("Synced setup status for user " . $_SESSION['seeker_id'] . ": " . 
                    ($_SESSION['setup_complete'] ? 'true' : 'false'));
            }
        } catch (PDOException $e) {
            error_log("Error syncing setup status: " . $e->getMessage());
        }
    }
}
?>