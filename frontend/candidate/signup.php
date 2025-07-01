<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign Up-Candidate</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/signupcandidate.css">
    </head>

    <body>
        <!-- Left -->
        <?php include('../../includes/candidate/login_left.php'); ?>

        <!-- Right -->
        <?php include('../../includes/candidate/signup_right.php'); ?>

        <!-- Google Sign In Modal -->
        <?php include('../../modals/candidate/login_google_modal.php'); ?>

        <script src="../../scripts/candidate/signup.js"></script>
    </body>
</html>