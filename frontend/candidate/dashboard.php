<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Idagdag ito sa simula ng dashboard.php pagkatapos ng session_start()
error_log("Dashboard.php - User ID: " . (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : 'not set'));
error_log("Dashboard.php - Setup complete: " . (isset($_SESSION['setup_complete']) ? ($_SESSION['setup_complete'] ? 'true' : 'false') : 'not set'));

// Process POST data for setup completion
if (isset($_POST['setup_complete']) && $_POST['setup_complete'] === 'true') {
    $_SESSION['setup_complete'] = true;
}

// First check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Not logged in, redirect to login
    header("Location: login.php");
    exit;
}

// Check setup complete in session OR in database
$setupComplete = false;
if (isset($_SESSION['setup_complete']) && ($_SESSION['setup_complete'] === true || $_SESSION['setup_complete'] === 1 || $_SESSION['setup_complete'] === '1' || $_SESSION['setup_complete'] === 'true')) {    $setupComplete = true;
} else if (isset($_SESSION['seeker_id'])) {
    // Check database
    require_once '../../backend/db.php';
    $seekerId = $_SESSION['seeker_id'];
    
    // PDO version of the query
    $stmt = $conn->prepare("SELECT setup_complete FROM job_seekers WHERE seeker_id = ?");
    $stmt->bindParam(1, $seekerId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $setupComplete = (bool)$result['setup_complete'];
        // Update session if database says it's complete
        if ($setupComplete) {
            $_SESSION['setup_complete'] = true;
        }
    }
}

// Then check if setup is complete
if (!$setupComplete) {
    // Logged in but setup not complete
    header("Location: accountsetup.php");
    exit;
}

// User is both logged in AND has completed setup, continue to dashboard
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/dashboard.css">
    </head>
    <body>
        <!-- Sidebar -->
        <?php include('../../includes/candidate/sidebar.php'); ?>
        
        <?php include('../../includes/candidate/dashboard_entry.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Search Bar -->
            <?php include('../../includes/candidate/dashboard_searchbar.php'); ?>
            
            <!-- Welcome Section -->
            <?php include('../../includes/candidate/dashboard_welcome.php'); ?>

            <!-- Stats Overview -->
            <?php include('../../includes/candidate/dashboard_stats.php'); ?>

            <!-- New -->
            <!--?php include('../../includes/candidate/dashboard_resume_status.php'); ?-->
            
            <!-- Recent Applications -->
            <?php include('../../includes/candidate/dashboard_recentapps.php'); ?>

            <!-- Upcoming Interviews -->
            <?php include('../../includes/candidate/dashboard_upcoming.php'); ?>

            <!-- Suggested Jobs -->
            <?php include('../../includes/candidate/dashboard_suggested.php'); ?>

            <!-- Accessibility Elements -->
            <?php include('../../includes/candidate/accessibility_panel.php'); ?>

            <!-- Notifications Section -->
            <?php include('../../includes/candidate/dashboard_notification.php'); ?>
        </div>

        <!-- Walkthrough Elements -->
        <?php include('../../includes/candidate/dashboard_walkthrough.php'); ?>
        
        <script src="../../scripts/candidate/dashboard.js"></script>

        <?php
            // Add this right before the closing body tag
            if (function_exists('echo_session_sync_script')) {
                echo_session_sync_script();
            }
        ?>
    </body>
</html>