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
        <title>Skill Selection</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/skillselection.css">
        <script>
            // Pass session data to JavaScript
            const userLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false' ?>;
            const serverSeekerId = <?php echo $seekerId ? $seekerId : 'null' ?>;
        </script>
    </head>
    <body>
        <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

        <div class="header">
            <h1>Showcase Your Skills</h1>
            <p>Select skills that highlight your expertise. These skills will help us connect you with inclusive employers looking for your unique talents.</p>
            
        </div>

        <div class="search-container">
            <input type="text" class="search-box" id="searchSkill" placeholder="Search for skills...">
            <i class="fas fa-search search-icon"></i>
        </div>

        <div class="category-tabs" id="categoryTabs">
            <div class="category-tab active" data-category="all">
                <i class="fas fa-th-large"></i> All Skills
            </div>
            <div class="category-tab" data-category="digital_and_technical_skills">
                <i class="fas fa-laptop-code"></i> Technical
            </div>
            <div class="category-tab" data-category="customer_service_skills">
                <i class="fas fa-headset"></i> Customer Service
            </div>
            <div class="category-tab" data-category="administrative_and_clerical_skills">
                <i class="fas fa-tasks"></i> Administrative
            </div>
            <div class="category-tab" data-category="accounting_and_financial_skills">
                <i class="fas fa-calculator"></i> Accounting
            </div>
            <div class="category-tab" data-category="bpo_specific_skills">
                <i class="fas fa-building"></i> BPO
            </div>
            <div class="category-tab" data-category="manufacturing_skills">
                <i class="fas fa-industry"></i> Manufacturing
            </div>
            <div class="category-tab" data-category="disability_specific_strengths">
                <i class="fas fa-star"></i> Special Strengths
            </div>
        </div>

        <div class="category-content" id="skillsContainer">
            <!-- Skills will be populated here by JavaScript -->
            <div class="loading-indicator">
                <i class="fas fa-spinner fa-spin"></i> Loading skills...
            </div>
        </div>

        <div class="selected-skills-container">
            <h3 class="selected-skills-title">Your Selected Skills <span id="skillCount">(0)</span></h3>
            <div class="selected-skills" id="selectedSkills">
                <!-- Selected skills will appear here -->
            </div>
        </div>

        <?php include('../../includes/candidate/skill_navigation.php'); ?>

        <script src="../../scripts/candidate/skillselection.js"></script>
    </body>
</html>