<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Notifications - Thisable</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/employer/empnotifications.css">
    <link rel="stylesheet" href="../../styles/employer/empsidebar.css">


</head>
<body>
    <!-- Sidebar -->
    <?php include('../../includes/employer/empsidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Notifications</h1>
            <div class="notification-actions">
                <button class="action-btn" id="mark-all-read">
                    <i class="fas fa-check-double"></i>
                    Mark All as Read
                </button>
            </div>
        </div>

        <div class="notifications-container">
            <div class="notification-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="applicant">Applicants</button>
                <button class="filter-btn" data-filter="job">Jobs</button>
                <button class="filter-btn" data-filter="interview">Interviews</button>
                <button class="filter-btn" data-filter="system">System</button>
                <div class="notification-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="notification-search" placeholder="Search notifications...">
                </div>
            </div>

            <div class="notification-list" id="notification-list">
                <!-- Notifications will be filled by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toast-container">
        <!-- Toasts will be added dynamically -->
    </div>

        <script src="../../scripts/employer/empnotifications.js"></script>
       
    </script>
</body>
</html>