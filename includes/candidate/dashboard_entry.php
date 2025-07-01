<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force setup to be marked as complete if directly accessed
if (isset($_POST['setup_complete']) || isset($_GET['setup']) && $_GET['setup'] === 'complete') {
    $_SESSION['setup_complete'] = true;
}

// Check cookies as well
if (isset($_COOKIE['setup_complete']) && $_COOKIE['setup_complete'] === 'true') {
    $_SESSION['setup_complete'] = true;
}

// If setup is not marked as complete in any way, redirect to account setup
if (
    !isset($_SESSION['setup_complete']) && 
    !isset($_POST['setup_complete']) && 
    !(isset($_GET['setup']) && $_GET['setup'] === 'complete') &&
    !(isset($_COOKIE['setup_complete']) && $_COOKIE['setup_complete'] === 'true')
) {
    // Debugging output - you can remove this in production
    error_log('Dashboard redirecting to account setup - session setup_complete not set');
    
    // Redirect to account setup
    header('Location: accountsetup.php');
    exit;
}
?>