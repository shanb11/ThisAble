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
    <title>Workplace Needs - Apparent Disabilities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/apparent-workplaceneeds.css">
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

    <div class="header">
        <h1>Workplace Needs - Apparent Disabilities</h1>
        <p class="tagline">Select the accommodations that would help you perform at your best in the workplace.</p>
        
       
    </div>

    <div class="guidance-box">
        <i class="fas fa-info-circle guidance-icon"></i>
        <p class="guidance-text">You can select multiple options that apply to your situation. This information helps employers prepare appropriate accommodations for your interviews and potential employment.</p>
    </div>

    <div class="infographic-container">
        <!-- Physical Access Category -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-building category-icon"></i>
                Physical Access
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div class="need-title">Accessible Entrances</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Wheelchair ramps, automatic doors, and other accessibility features for entering and navigating the building.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-restroom"></i>
                        </div>
                        <div class="need-title">Accessible Restrooms</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">ADA-compliant restroom facilities with grab bars, adequate space for mobility aids, and accessible fixtures.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-parking"></i>
                        </div>
                        <div class="need-title">Accessible Parking</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Designated accessible parking spaces located close to building entrances with proper signage and access paths.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobility & Navigation Category -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-wheelchair category-icon"></i>
                Mobility & Navigation
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="need-title">Clear Pathways</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Unobstructed hallways and work areas with sufficient space for mobility devices like wheelchairs or walkers.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-arrows-alt-v"></i>
                        </div>
                        <div class="need-title">Elevators/Ramps</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Accessible elevators with proper dimensions and controls, or ramps as alternatives to stairs for multi-level workplaces.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-hand-paper"></i>
                        </div>
                        <div class="need-title">Reachable Controls</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Light switches, thermostats, and other controls positioned at accessible heights for individuals with limited reach or mobility.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual & Auditory Accommodations -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-eye category-icon"></i>
                Visual & Auditory Accommodations
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div class="need-title">Screen Readers</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Software that reads text aloud for individuals with visual impairments, allowing them to navigate digital content.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-search-plus"></i>
                        </div>
                        <div class="need-title">Magnification Tools</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Digital or physical magnifiers that enlarge text and images for people with low vision or visual impairments.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-volume-up"></i>
                        </div>
                        <div class="need-title">Enhanced Audio</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Amplification devices, assistive listening systems, or visual alerts for individuals with hearing impairments.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workplace Setup -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-chair category-icon"></i>
                Workplace Setup
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <div class="need-title">Ergonomic Workstation</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Adjustable height desks, specialized chairs, and ergonomic equipment configured for physical comfort and accessibility.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-keyboard"></i>
                        </div>
                        <div class="need-title">Adaptive Equipment</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Specialized input devices, modified keyboards, switches, or other hardware adapted for individuals with limited dexterity or mobility.</p>
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
                        <div class="need-title">Lighting Adjustments</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Task lighting, anti-glare screens, or modified lighting conditions to accommodate visual sensitivities or impairments.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communication Support -->
        <div class="need-category">
            <h3 class="category-title">
                <i class="fas fa-comments category-icon"></i>
                Communication Support
            </h3>
            <div class="needs-grid">
                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-sign-language"></i>
                        </div>
                        <div class="need-title">Sign Language Interpreter</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Professional interpreters who facilitate communication between deaf or hard of hearing employees and others in meetings and group settings.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-closed-captioning"></i>
                        </div>
                        <div class="need-title">Captioning Services</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Real-time captioning for meetings, presentations, and video content to support individuals with hearing impairments.</p>
                    </div>
                </div>

                <div class="need-card" onclick="toggleSelection(this)">
                    <div class="select-indicator">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="need-header">
                        <div class="need-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="need-title">Alternative Formats</div>
                    </div>
                    <div class="need-content">
                        <p class="need-description">Materials provided in braille, large print, digital accessible formats, or other alternative formats based on individual needs.</p>
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

    <script src="../../scripts/candidate/apparent-workplaceneeds.js"></script>

</body>
</html>