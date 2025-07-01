<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/candidate/session_check.php';
require_login(); // Add this line to enforce login check
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notifications</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/notifications.css">
        <link rel="stylesheet" href="../../styles/candidate/notifications-phase3.css">
    </head>
    <body>
        <!-- Sidebar -->
        <?php include('../../includes/candidate/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="notifications-header">
                <h1>Notifications</h1>
                <div class="notification-icons">
                    </a>
                    <a href="notifications.php">
                        <i class="far fa-bell"></i>
                    </a>
                </div>
            </div>

            <!-- Notifications Container -->
            <?php include('../../includes/candidate/notifications_container.php'); ?>

            <!-- Accessibility Elements -->
            <?php include('../../includes/candidate/accessibility_panel.php'); ?>
        </div>
        
        <script src="../../scripts/candidate/notifications.js"></script>
               
        <?php
            // Add this right before the closing body tag
            if (function_exists('echo_session_sync_script')) {
                echo_session_sync_script();
            }
        ?>
    </body>
</html>