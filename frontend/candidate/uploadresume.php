<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Check if user is logged in
$isLoggedIn = isset($_SESSION['seeker_id']);
$seekerId = $isLoggedIn ? $_SESSION['seeker_id'] : null;

// Skip login check during setup
// require_login(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Resume - Opening Doors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/uploadresume.css">
     <script>
        // Pass session data to JavaScript
        const userLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false' ?>;
        const serverSeekerId = <?php echo $seekerId ? $seekerId : 'null' ?>;
    </script>
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

    <div class="header">
        <h1>Upload Your Resume</h1>
        <p class="tagline">Share your professional experience to help employers understand your qualifications and find the perfect match for your skills.</p>
        
        
    </div>

    <div class="guidance-box">
        <i class="fas fa-lightbulb guidance-icon"></i>
        <p class="guidance-text">Your resume helps employers understand your background and skills. Having an up-to-date resume increases your chances of finding suitable employment opportunities.</p>
    </div>

    <div class="infographic-container">
        <div class="resume-tips">
            <div class="tip-card">
                <div class="tip-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="tip-title">Clear Formatting</h3>
                <p class="tip-description">Use clean, consistent formatting with readable fonts and proper spacing for better readability.</p>
            </div>
            
            <div class="tip-card">
                <div class="tip-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="tip-title">Highlight Skills</h3>
                <p class="tip-description">Emphasize your relevant skills and competencies that match potential job requirements.</p>
            </div>
            
            <div class="tip-card">
                <div class="tip-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="tip-title">Updated Content</h3>
                <p class="tip-description">Ensure your resume reflects your most recent experience, education, and accomplishments.</p>
            </div>
        </div>

        <div class="upload-section">
            <div class="upload-container" id="dropZone">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">Drag & Drop your resume here</div>
                <div class="upload-subtext">or</div>
                <!-- FIXED: Removed the label that was causing double click issue -->
                <button type="button" class="choose-file" onclick="document.getElementById('fileInput').click()">Choose File</button>
                <div class="file-types">Accepted file types: PDF, DOC, DOCX (Max size: 5MB)</div>
            </div>

            <div class="file-info" id="fileInfo">
                <div class="file-preview">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="file-details">
                    <div class="file-name" id="fileName">document.pdf</div>
                    <div class="file-size" id="fileSize">1.2 MB</div>
                    <div class="file-status">
                        <i class="fas fa-check-circle"></i>
                        <span>Ready to upload</span>
                    </div>
                    <a class="remove-file" onclick="removeFile()">
                        <i class="fas fa-trash-alt"></i>
                        <span>Remove file</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the navigation buttons -->
    <?php
        // Determine previous page based on disability type
        // We'll set this with JavaScript based on the stored disability type
        $customPrevPage = "";  // Will be set by JavaScript
        
        // The next page is the dashboard
        $customNextPage = "dashboard.php";
        
        // Custom onclick handlers for navigation
        $backOnClick = "goBack()";
        $continueOnClick = "goToNextPage()";
        
        // Include the navigation buttons
        include('../../includes/candidate/standard_navigation.php');
    ?>

    <input type="file" id="fileInput" accept=".pdf,.doc,.docx" hidden>

    <script src="../../scripts/candidate/uploadresume.js"></script>

</body>
</html>