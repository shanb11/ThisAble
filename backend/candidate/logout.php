<?php
    // logout.php
    session_start();

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    // Redirect to login page
    header("Location: ../../frontend/candidate/login.php");
    exit;
?>  