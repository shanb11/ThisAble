<?php
// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include session helper
require_once('../../backend/shared/session_helper.php');

// Check if employer is logged in
requireEmployerLogin();

// Update last activity
updateLastActivity();

// Check for session expiration
if (isSessionExpired()) {
    logoutEmployer();
    header('Location: emplogin.php?error=session_expired');
    exit;
}

// Log dashboard access
logActivity('dashboard_access', 'Viewed employer dashboard');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ThisAble - Employer Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/employer/empdashboard.css">
        <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
    </head>

    <body>
        <!-- Sidebar -->
        <?php include('../../includes/employer/empsidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Header - Modified to remove profile dropdown -->
            <?php include('../../includes/employer/empdashboard_header.php'); ?>

            <!-- Welcome Section -->
            <?php include('../../includes/employer/empdashboard_welcome.php'); ?>

            <!-- Stats Grid -->
            <?php include('../../includes/employer/empdashboard_stats.php'); ?>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Left Column -->
                <?php include('../../includes/employer/empdashboard_left.php'); ?>

                <!-- Right Column -->
                <?php include('../../includes/employer/empdashboard_right.php'); ?>
            </div>

        </div>

        <!-- Post Job Modal -->
        <?php include('../../modals/employer/postjob_modal.php'); ?>

        <!-- View Job Details Modal -->
        <?php include('../../modals/employer/viewjob_modal.php'); ?>

        <!-- Edit Job Modal -->
        <?php include('../../modals/employer/editjob_modal.php'); ?>

        <!-- View Applicant Profile Modal -->
        <?php include('../../modals/employer/view_applicant_modal.php'); ?>

        <!-- Schedule Interview Modal -->
        <?php include('../../modals/employer/schedule_interview_modal.php'); ?>

        <!-- View Interview Details Modal -->
        <?php include('../../modals/employer/view_interview_modal.php'); ?>

        <!-- Reschedule Interview Modal -->
        <?php include('../../modals/employer/reschedule_interview_modal.php'); ?>

        <!-- Notifications Panel -->
        <?php include('../../modals/employer/notification_modal.php'); ?>

        <!-- Confirmation Dialog -->
        <?php include('../../modals/employer/confirmation_modal.php'); ?>

        <!-- Session Data & JavaScript -->
        <?php echoEmployerSessionScript(); ?>
        
        <!-- Main Dashboard Script -->
        <script src="../../scripts/employer/empdashboard.js"></script>

        <!-- Debug Information -->
        <script>
            console.log('Dashboard loaded');
            console.log('Session data:', window.employerSession);
            console.log('Employer ID:', window.getCurrentEmployerId());
            console.log('Company Name:', window.getCurrentCompanyName());
        </script>
    </body>
</html>