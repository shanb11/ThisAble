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
    <title>Workplace Needs - Non-Apparent Disabilities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/non-apparent-workplaceneeds.css">
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

    <div class="header">
        <h1>Workplace Needs - Non-Apparent Disabilities</h1>
        <p class="tagline">Select the accommodations that would help you perform at your best in the workplace.</p>
        
    </div>

    <div class="guidance-box">
        <i class="fas fa-info-circle guidance-icon"></i>
        <p class="guidance-text">You can select multiple options that apply to your situation. This information helps employers prepare appropriate accommodations for your interviews and potential employment.
        <span class="privacy-note">Your privacy is important. You control what information is shared with potential employers.</span>
        </p>
    </div>

    <div class="infographic-container">
        <!-- Cognitive & Learning Accommodations -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-brain category-icon"></i>
                Cognitive & Learning Accommodations
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="need-title">Written Instructions</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Providing instructions, tasks, and feedback in written format alongside verbal communication.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="need-title">Task Organization Tools</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Access to digital or physical tools that help with organizing tasks, setting reminders, and tracking progress.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div class="need-title">Reading Assistance</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Text-to-speech software, reading guides, or format modifications for written materials to support reading comprehension.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sensory Environment -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-sliders-h category-icon"></i>
                Sensory Environment Adjustments
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-volume-mute"></i>
                        </div>
                        <div class="need-title">Noise Reduction</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Quieter work environment, noise-canceling headphones, or permission to use white noise machines to minimize auditory distractions.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="need-title">Lighting Accommodations</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Alternative lighting options such as natural light, desk lamps, or reduced fluorescent lighting based on sensory needs.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-door-closed"></i>
                        </div>
                        <div class="need-title">Reduced Stimulation Space</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Access to a quieter workspace, partitioned area, or private office to minimize sensory overload and distractions.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Schedule & Structure -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-calendar-alt category-icon"></i>
                Work Schedule & Structure
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="need-title">Flexible Hours</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Adjustable start/end times or modified work schedules to accommodate energy levels, medical appointments, or treatment schedules.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="need-title">Remote Work Options</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Ability to work from home part-time or full-time to manage symptoms, energy levels, or environmental sensitivities.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-mug-hot"></i>
                        </div>
                        <div class="need-title">Additional Breaks</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Short, more frequent breaks throughout the workday to manage fatigue, medication timing, or prevent symptom flare-ups.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communication & Social Support -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-comments category-icon"></i>
                Communication & Social Support
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-comment-alt"></i>
                        </div>
                        <div class="need-title">Communication Preferences</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Accommodations for preferred communication methods such as email instead of phone calls, or advance notice before meetings.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="need-title">Meeting Accommodations</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Advance meeting agendas, options to participate remotely, or alternative participation methods for group discussions.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="need-title">Mentor or Support Person</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Access to a workplace mentor, job coach, or designated person for questions and support with workplace processes.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Management -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-heartbeat category-icon"></i>
                Health Management
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="need-title">Medication Management</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Allowances for taking medication during work hours or adjustments to accommodate medication effects and timing.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <div class="need-title">Medical Appointment Flexibility</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Flexible scheduling to accommodate regular medical appointments, therapy sessions, or treatments.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-couch"></i>
                        </div>
                        <div class="need-title">Rest Area Access</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Access to a private space for short rest periods, symptom management, or stress reduction during the workday.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="no-needs-option" onclick="toggleNoNeeds(this)">
        <div class="no-needs-text">I don't require any specific accommodations at this time</div>
        <div class="no-needs-checkbox">
            <i class="fas fa-check"></i>
        </div>
    </div>

     <!-- Include the navigation buttons -->
     <?php
        // Set the previous page to disability type
        $customPrevPage = "disabilitytype.php";
        
        // Set the next page to upload resume
        $customNextPage = "uploadresume.php";
        
        // Custom onclick handlers for navigation
        $backOnClick = "goBack()";
        $continueOnClick = "goToNextPage()";
        
        // Include the navigation buttons
        include('../../includes/candidate/standard_navigation.php');
    ?>

    <script src="../../scripts/candidate/non-apparent-workplaceneeds.js"></script>
</body>
</html>