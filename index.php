<?php
// Load dynamic configuration FIRST
require_once 'config/config.php';

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_log("Index.php - User ID: " . (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : 'not set'));
error_log("Index.php - Setup complete: " . (isset($_SESSION['setup_complete']) ? ($_SESSION['setup_complete'] ? 'true' : 'false') : 'not set'));

// Check if setup is complete - redirect to dashboard
if (isset($_SESSION['setup_complete']) && $_SESSION['setup_complete']) {
    header('Location: frontend/candidate/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    
    <?php output_js_config(); ?>
    
    <link rel="stylesheet" href="styles/landing/landingpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php include('includes/landing/landing_navbar.php'); ?>

    <!-- Hero Section -->
    <?php include('includes/landing/landing_hero.php'); ?>

    <!-- Job Categories Section -->
    <?php include('includes/landing/landing_job_categories_section.php'); ?>

    <!-- Inclusive Workplace Section -->
    <?php include('includes/landing/landing_inclusive_workplace.php'); ?>

    <!-- Footer -->
    <?php include('includes/landing/landing_footer.php'); ?>

    <!-- Modal for job listings -->
    <?php include('modals/landing/landing_job_listings_modal.php'); ?>

    <!-- Modal for post job -->
    <?php include('modals/landing/landing_post_job_modal.php'); ?>
</body>

<script src="scripts/api-config.js"></script>
<script src="scripts/landing/landingpage.js"></script>

</html>