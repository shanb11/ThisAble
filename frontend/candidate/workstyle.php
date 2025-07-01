<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Check if user is logged in
$isLoggedIn = isset($_SESSION['seeker_id']);
$seekerId = $isLoggedIn ? $_SESSION['seeker_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Work Style</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/workstyle.css">
    <script>
        // Pass session data to JavaScript
        const userLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false' ?>;
        const serverSeekerId = <?php echo $seekerId ? $seekerId : 'null' ?>;
    </script>
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

    <div class="header">
        <h1>How Would You Like to Work?</h1>
        <p class="tagline">Choose the work arrangement that best matches your lifestyle and preferences.</p>
        
        
    </div>

    <div class="guidance-box">
        <i class="fas fa-lightbulb guidance-icon"></i>
        <p class="guidance-text">Different work styles offer unique benefits. Consider your productivity habits, commute preferences, and social needs when making your selection.</p>
    </div>

    <div class="workstyle-flow">
        <div class="workstyle-card" onclick="selectOption(this, 'remote')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/remotework.png" alt="Remote Work" class="card-image">
                <div class="card-badge">Flexibility</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-home card-icon"></i>
                    Remote Work
                </h3>
                <p class="card-description">Work from anywhere with flexible scheduling and complete autonomy over your workspace.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>No commute time or costs</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Personalized work environment</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Flexible scheduling options</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="workstyle-card" onclick="selectOption(this, 'hybrid')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/hybridwork.png" alt="Hybrid Work" class="card-image">
                <div class="card-badge">Balance</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-sync-alt card-icon"></i>
                    Hybrid Work
                </h3>
                <p class="card-description">Enjoy the best of both worlds with a mix of remote work and in-office collaboration.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Balanced social interaction</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Partial commute reduction</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Structured yet flexible schedule</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="workstyle-card" onclick="selectOption(this, 'onsite')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/onsitework.png" alt="Onsite Work" class="card-image">
                <div class="card-badge">Collaboration</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-building card-icon"></i>
                    Onsite Work
                </h3>
                <p class="card-description">Traditional office environment with face-to-face collaboration and team dynamics.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>In-person team collaboration</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Clear work/home separation</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Access to office resources</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the navigation buttons -->
    <?php
        // Define previous page 
        $customPrevPage = "skillselection.php";
        
        // Next page will be job type
        $customNextPage = "jobtype.php";
        
        // Custom onclick handlers for navigation
        $backOnClick = "goBack()";
        $continueOnClick = "goToNextPage()";
        
        // Include the navigation buttons
        include('../../includes/candidate/standard_navigation.php');
    ?>

    <script src="../../scripts/candidate/workstyle.js"></script>
</body>
</html>