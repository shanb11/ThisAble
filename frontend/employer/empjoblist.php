<?php
// Start session and check authentication
session_start();

// Include required files FIRST
require_once('../../backend/db.php');
require_once('../../backend/shared/session_helper.php');

// Check if employer is logged in using YOUR existing function
if (!isEmployerLoggedIn()) {
    header('Location: emplogin.php');
    exit();
}

// Get employer information using YOUR existing functions
$employer_id = getCurrentEmployerId();
$company_name = getCurrentCompanyName();

// Validate that we have the required data
if (!$employer_id || !$company_name) {
    // If we can't get employer data, clear session and redirect
    session_destroy();
    header('Location: emplogin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - <?php echo htmlspecialchars($company_name); ?> | ThisAble</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/employer/empjoblist.css">
    <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include('../../includes/employer/empsidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Search Bar -->
        <div class="search-bar">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for job titles..." id="search-input">
            </div>
            <div class="notification-icons">
                <i class="far fa-bell" onclick="window.location.href='empnotifications.php'"></i>
            </div>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Job Listings</h1>
            <button class="post-job-btn" id="open-job-form">
                <i class="fas fa-plus"></i>
                Post New Job
            </button>
        </div>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <div class="filter-group">
                <span class="filter-label">Status:</span>
                <select class="filter-select" id="status-filter">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="closed">Closed</option>
                    <option value="draft">Draft</option>
                    <option value="paused">Paused</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Sort by:</span>
                <select class="filter-select" id="sort-filter">
                    <option value="recent">Most Recent</option>
                    <option value="applicants">Most Applicants</option>
                    <option value="views">Most Views</option>
                </select>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading-state" id="loading-state" style="display: none;">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <p>Loading job listings...</p>
        </div>

        <!-- Job Listings Container (Will be populated by JavaScript) -->
        <div class="job-listings" id="job-listings">
            <!-- Job cards will be dynamically loaded here by JavaScript -->
        </div>

        <!-- Success Message -->
        <div class="success-message" id="success-message">
            <i class="fas fa-check-circle"></i>
            <span id="success-message-text">Action completed successfully!</span>
        </div>

        <!-- Error Message -->
        <div class="error-message" id="error-message" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="error-message-text">An error occurred. Please try again.</span>
        </div>

        <!-- Empty State (will show when no jobs are found) -->
        <div class="empty-state" id="empty-state" style="display: none;">
            <div class="empty-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <h3 class="empty-title">No Job Listings Found</h3>
            <p class="empty-text">You haven't posted any job listings yet.</p>
            <button class="post-job-btn" id="empty-state-post-job">
                <i class="fas fa-plus"></i>
                Post Your First Job
            </button>
        </div>
    </div>

    <!-- Hidden data for JavaScript -->
    <script>
        // Pass PHP data to JavaScript
        window.employerData = {
            employer_id: <?php echo json_encode($employer_id); ?>,
            company_name: <?php echo json_encode($company_name); ?>,
            session_status: <?php echo json_encode(session_status()); ?>,
            debug_info: {
                session_id: '<?php echo session_id(); ?>',
                timestamp: '<?php echo date('Y-m-d H:i:s'); ?>'
            }
        };
        
        // Debug log
        console.log('Employer Data:', window.employerData);
    </script>

    <!-- JavaScript -->
    <script src="../../scripts/employer/empjoblist.js"></script>
</body>
</html>