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
    <title>Disability Type</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/candidate/disabilitytype.css">
</head>
<body>
    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">

    <div class="header">
        <h1>Your Disability Type</h1>
        <p class="tagline">Help us understand your needs better to match you with appropriate opportunities.</p>
        
       
    </div>

    <div class="guidance-box">
        <i class="fas fa-info-circle guidance-icon"></i>
        <p class="guidance-text">Your selection helps us provide relevant job matches and accommodations. All information is kept confidential and used only to enhance your job search experience.</p>
    </div>

    <div class="disability-flow">
        <div class="disability-card" onclick="selectOption(this, 'apparent')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/apparent.png" alt="Apparent Disability" class="card-image">
                <div class="card-badge">Visible</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-eye card-icon"></i>
                    Apparent Disability
                </h3>
                <p class="card-description">A disability that is visible or immediately evident to others through physical characteristics, mobility devices, or other observable features. These disabilities are generally noticeable in everyday interactions.</p>
                
                <div class="card-examples">
                    <div class="examples-heading">Examples include:</div>
                    <div class="examples-list">
                        <span class="example-tag">Mobility impairments</span>
                        <span class="example-tag">Visual impairments</span>
                        <span class="example-tag">Limb differences</span>
                        <span class="example-tag">Facial differences</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="disability-card" onclick="selectOption(this, 'non-apparent')">
            <div class="select-indicator">
                <i class="fas fa-check"></i>
            </div>
            <div class="card-header">
                <img src="../../images/nonapparent.png" alt="Non-apparent Disability" class="card-image">
                <div class="card-badge">Hidden</div>
            </div>
            <div class="card-content">
                <h3 class="card-title">
                    <i class="fas fa-low-vision card-icon"></i>
                    Non-apparent Disability
                </h3>
                <p class="card-description">A disability that is not immediately obvious or visible to others. These disabilities might include cognitive, neurological, psychological, or other conditions that affect daily functioning but aren't externally evident.</p>
                
                <div class="card-examples">
                    <div class="examples-heading">Examples include:</div>
                    <div class="examples-list">
                        <span class="example-tag">Learning disabilities</span>
                        <span class="example-tag">Chronic illness</span>
                        <span class="example-tag">Autism spectrum</span>
                        <span class="example-tag">Mental health conditions</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="help-text">Not sure? Select the option that most closely represents your situation. You can always update this information later.</p>

    <!-- Include the navigation buttons -->
    <?php
        // Set the previous page to jobtype
        $customPrevPage = "jobtype.php";
        
        // Next page will be determined by JavaScript based on selection
        // We'll start with an empty nextPage, which will be updated by JS
        $customNextPage = "";
        
        // Custom onclick handlers for navigation
        $backOnClick = "goBack()";
        $continueOnClick = "goToNextPage()";
        
        // Include the navigation buttons
        include('../../includes/candidate/standard_navigation.php');
    ?>

    <script>
        // Track selected disability type
        let selectedDisabilityType = '';
        
        function selectOption(selectedCard, disabilityType) {
            // Remove selected class from all cards
            document.querySelectorAll('.disability-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            selectedCard.classList.add('selected');
            
            // Store selected disability type
            selectedDisabilityType = disabilityType;
            
            // Store selection in local storage for persistence
            localStorage.setItem('disabilityType', disabilityType);
        }
        
        // Define goToNextPage function
        function goToNextPage() {

            event.preventDefault();

            if (selectedDisabilityType === 'apparent') {
                window.location.href = 'apparent-workplaceneeds.php';
            } else if (selectedDisabilityType === 'non-apparent') {
                window.location.href = 'non-apparent-workplaceneeds.php';
            } else {
                // If nothing is selected, show an alert
                alert('Please select a disability type to continue.');
            }
        }   
            
        // On page load, check if there's a previously selected option
        document.addEventListener('DOMContentLoaded', () => {
            const savedDisabilityType = localStorage.getItem('disabilityType');
            if (savedDisabilityType) {
                const cards = document.querySelectorAll('.disability-card');
                if (savedDisabilityType === 'apparent') {
                    cards[0].classList.add('selected');
                } else if (savedDisabilityType === 'non-apparent') {
                    cards[1].classList.add('selected');
                }
                selectedDisabilityType = savedDisabilityType;
            }
        });
        
        function goBack() {
            window.location.href = "jobtype.php";
        }
    </script>
</body>
</html>