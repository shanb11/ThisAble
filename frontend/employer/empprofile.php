<?php
require_once '../../backend/shared/session_helper.php';
requireEmployerLogin();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Company Profile - ThisAble</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
        <link rel="stylesheet" href="../../styles/employer/empprofile.css">
        <?php echoEmployerSessionScript(); ?>
    </head>
    
    <body>
        <!-- Sidebar -->
        <?php include('../../includes/employer/empsidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Company Profile</h1>
                <div class="notification-icons">
                    <a href="empnotifications.php">
                        <i class="far fa-bell"></i>
                    </a>
                </div>
            </div>
            
            <!-- Profile Completion Section -->
            <?php include('../../includes/employer/empprofile_completion.php'); ?>

            <!-- Company Profile Form -->
            <?php include('../../includes/employer/empprofile_form.php'); ?>
            
            <!-- Saving Indicator -->
            <div class="saving-indicator" id="saving-indicator">
                <i class="fas fa-spinner saving-spinner"></i>
                <span>Changes saved successfully!</span>
            </div>
        </div>

        <!-- Identity Modal -->
        <?php include('../../modals/employer/empprofile_identity_modal.php'); ?>

        <!-- Contact Modal -->
        <?php include('../../modals/employer/empprofile_contact_modal.php'); ?>

        <!-- Logo Description Modal -->
        <?php include('../../modals/employer/empprofile_logo_modal.php'); ?>

        <!-- Preferences Modal -->
        <?php include('../../modals/employer/empprofile_preferences_modal.php'); ?>

        <!-- Social Media Modal -->
        <?php include('../../modals/employer/empprofile_social_modal.php'); ?>

        <script src="../../scripts/employer/empprofile.js"></script>
    </body>
</html>