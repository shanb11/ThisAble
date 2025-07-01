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
    <title>Select Job Type</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/jobtype.css">
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="Opening Doors Logo" class="logo">

    <div class="header">
        <h1>Select Your Preferred Job Type</h1>
        <p class="tagline">Choose the employment type that best suits your needs and career goals.</p>
    </div>

    <div class="guidance-box">
        <i class="fas fa-lightbulb guidance-icon"></i>
        <p class="guidance-text">Different job types offer unique benefits. Consider your financial needs, flexibility requirements, and long-term career goals when making your selection.</p>
    </div>

    <div class="jobtype-flow">
        <div class="jobtype-card" onclick="selectOption(this, 'freelance')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/freelancework.png" alt="Freelance" class="card-image">
                <div class="card-badge">Independence</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-briefcase card-icon"></i>
                    Freelance
                </h3>
                <p class="card-description">Project-based work with the freedom to set your schedule and work from anywhere.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Complete schedule flexibility</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Diverse project opportunities</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Income potential based on effort</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="jobtype-card" onclick="selectOption(this, 'parttime')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/parttimework.png" alt="Part-time" class="card-image">
                <div class="card-badge">Balance</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-clock card-icon"></i>
                    Part-time
                </h3>
                <p class="card-description">Work fewer hours per week with a flexible schedule tailored to specific tasks.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Predictable income stream</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>More time for other pursuits</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Possible benefits (with some employers)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="jobtype-card" onclick="selectOption(this, 'fulltime')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/fulltimework.png" alt="Full-time" class="card-image">
                <div class="card-badge">Stability</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check card-icon"></i>
                    Full-time
                </h3>
                <p class="card-description">Work on a regular 40-hour schedule with comprehensive duties and potential benefits.</p>
                <div class="card-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Consistent salary and benefits</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Career advancement opportunities</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle feature-icon"></i>
                        <span>Stable work environment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the navigation buttons -->
    <?php
        // Define previous page
        $customPrevPage = "workstyle.php";
        
        // Next page will be determined by JavaScript based on disability type
        $customNextPage = "disabilitytype.php";
        
        // Custom onclick handlers for navigation
        $backOnClick = "goBack()";
        $continueOnClick = "goToNextPage()";
        
        // Include the navigation buttons
        include('../../includes/candidate/standard_navigation.php');
    ?>

    <script src="../../scripts/candidate/jobtype.js"></script>

</body>
</html>