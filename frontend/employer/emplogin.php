<?php
// Load configuration first
require_once '../../config/config.php';

// frontend/employer/login.php - Using same structure as candidate login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Employer</title>
        
        <?php output_js_config(); ?>
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!-- Use the same CSS as candidate login -->
        <link rel="stylesheet" href="../../styles/candidate/login.css">
    </head>

    <body>
        <!-- Left - Same as candidate -->
        <?php include('../../includes/candidate/login_left.php'); ?>

        <!-- Right - Employer version -->
        <?php include('../../includes/employer/emplogin_right.php'); ?>

        <!-- Forgot Password Modal - Employer version -->
        <?php include('../../modals/employer/emplogin_forgotpass_modal.php'); ?>
        
        <!-- Selection Modal - Same as candidate (for switching between candidate/employer) -->
        <?php include('../../modals/candidate/selection_modal.php'); ?>

        <!-- Use employer-specific JavaScript -->
        <script src="../../scripts/api-config.js?v=2"></script>
        <script src="../../scripts/employer/emplogin.js?v=2"></script>
    </body>
</html>