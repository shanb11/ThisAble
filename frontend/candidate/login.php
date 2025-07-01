<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login-Candidate</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/login.css">
    </head>

    <body>
        <!-- Left -->
        <?php include('../../includes/candidate/login_left.php'); ?>

        <!-- Right -->
        <?php include('../../includes/candidate/login_right.php'); ?>

        <!-- Forgot Password Modal -->
        <?php include('../../modals/candidate/login_forgotpass_modal.php'); ?>
        
        <!-- Selection Modal -->
        <?php include('../../modals/candidate/selection_modal.php'); ?>
        
        <!-- PWD Details Modal for Google Sign-up completion -->
        <?php
        // Check if we need to show the PWD details modal
        $showPwdDetails = isset($_GET['show_pwd_details']) && $_GET['show_pwd_details'] == '1';
        
        // Only include the PWD details modal if we have Google data in session
        if ($showPwdDetails && isset($_SESSION['google_data'])) {
            include('../../modals/candidate/pwd_details_modal.php');
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const pwdDetailsModal = document.getElementById("pwdDetailsModal");
                    if (pwdDetailsModal) {
                        pwdDetailsModal.style.display = "flex";
                    }
                });
            </script>';
        }
        ?>

        <script src="../../scripts/candidate/login.js"></script>
    </body>
</html>