<?php
session_start();
require_once '../../backend/db.php';

// Add this temporarily sa profile.php para ma-debug
echo "<!-- DEBUG: Session seeker_id = " . ($_SESSION['seeker_id'] ?? 'NOT SET') . " -->";
echo "<!-- DEBUG: Session logged_in = " . ($_SESSION['logged_in'] ?? 'NOT SET') . " -->";

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    header("Location: login.php");
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Fetch basic user information
$query = "SELECT js.*, dt.disability_name, ua.email 
          FROM job_seekers js 
          JOIN disability_types dt ON js.disability_id = dt.disability_id
          JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
          WHERE js.seeker_id = :seeker_id";
          
$stmt = $conn->prepare($query);
$stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Handle error - user not found
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="seeker_id" content="<?php echo htmlspecialchars($seeker_id); ?>">
        <title>Profile | ThisAble</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/profile.css">
    </head>

    <body>
        <!-- Sidebar -->
        <?php include('../../includes/candidate/sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">

            <div class="top-bar">
                <div class="notification-icons">
                    <a href="notifications.php">
                        <i class="far fa-bell"></i>
                    </a>
                </div>
            </div>

            <!-- Profile Header -->
            <?php include('../../includes/candidate/profile_header.php'); ?>

            <!-- Profile Completion Status -->
            <?php include('../../includes/candidate/profile_completion.php'); ?>

            <!-- Main Profile Sections -->
            <div class="profile-sections">

                <!-- Personal Information Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-user"></i> Personal Information</h2>
                        <button class="edit-section-btn" data-section="personal">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_info.php'); ?>

                    <!-- Personal Info Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_info_edit.php'); ?>

                </div>

                <!-- Skills Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-tools"></i> Skills</h2>
                        <button class="edit-section-btn" data-section="skills">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_skills.php'); ?>

                    <!-- Skills Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_skills_edit.php'); ?>

                </div>

                <!-- Work Preferences Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-briefcase"></i> Work Preferences</h2>

                        <button class="edit-section-btn" data-section="preferences">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_work_preferences.php'); ?>

                    <!-- Work Preferences Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_work_preferences_edit.php'); ?>

                </div>

                <!-- Accessibility Needs Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-universal-access"></i> Accessibility Needs</h2>
                        <button class="edit-section-btn" data-section="accessibility">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_accessibility.php'); ?>
            
                    <!-- Accessibility Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_accessibility_edit.php'); ?>

                </div>

                <!-- Resume Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-file-alt"></i> Resume</h2>

                        <button class="edit-section-btn" data-section="resume">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_resume.php'); ?>

                    <!-- Resume Upload Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_resume_edit.php'); ?>

                </div>

                <!-- Education Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-graduation-cap"></i> Education</h2>
                        <button class="add-item-btn" id="add-education-btn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_education.php'); ?>
                    
                    <!-- Education Add/Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_education_edit.php'); ?>

                </div>

                <!-- Experience Section -->
                <div class="profile-section">

                    <div class="section-header">
                        <h2><i class="fas fa-briefcase"></i> Work Experience</h2>
                        <button class="add-item-btn" id="add-experience-btn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>

                    <?php include('../../includes/candidate/profile_experience.php'); ?>
                    
                    <!-- Experience Add/Edit Form (hidden by default) -->
                    <?php include('../../includes/candidate/profile_experience_edit.php'); ?>

                </div>
            </div>           
        </div>

        <!-- Accessibility Elements -->
        <?php include('../../includes/candidate/accessibility_panel.php'); ?>

        <script src="../../scripts/candidate/profile.js"></script>
        <script src="../../scripts/candidate/profile-ajax.js"></script>
       
    </body>
</html>