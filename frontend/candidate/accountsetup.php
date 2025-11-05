<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();

require_once '../../config/config.php';

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Setup</title>
    <link rel="stylesheet" href="../../styles/candidate/accountsetup.css"> 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@400;600&display=swap">
</head>
<body>

    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">
    
    <div class="header">
        <img src="../../images/setupimg.png" alt="Career Opportunities" class="center-image">
        <h1>Welcome to ThisAble!</h1>
        <p class="tagline">Ready to unlock your career potential? We're here to help you connect with inclusive employers who value your unique talents.</p>
        
        <div class="progress-bar">
            <div class="progress-indicator"></div>
        </div>
    </div>
    
    <div class="infographic">
        <div class="step">
            <div class="step-icon">1</div>
            <div class="step-content">
                <h3 class="step-title">Create Your Profile</h3>
                <p class="step-desc">Build a professional profile that highlights your experience, education, and unique skills that make you stand out.</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-icon">2</div>
            <div class="step-content">
                <h3 class="step-title">Showcase Your Skills</h3>
                <p class="step-desc">Select from our comprehensive skill library or add custom skills that represent your professional capabilities.</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-icon">3</div>
            <div class="step-content">
                <h3 class="step-title">Connect With Employers</h3>
                <p class="step-desc">Get matched with inclusive companies looking for talented professionals with your specific skill set.</p>
            </div>
        </div>
    </div>
    
    <button class="get-started-btn" onclick="goToSkillSelection()">Get Started Now</button>

    <script>
        function goToSkillSelection() {
            window.location.href = "skillselection.php";
        }
    </script>
</body>
</html>