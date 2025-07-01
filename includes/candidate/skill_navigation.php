<?php
/**
 * Special navigation buttons for skill selection page
 * 
 * This file provides the special 3-button layout for the skill selection page.
 * Place this file in your includes/candidate directory.
 */

// Define the page sequence for the candidate journey
$pageSequence = [
    'accountsetup.php',       // Step 1 
    'skillselection.php',     // Step 2
    'workstyle.php',          // Step 3
    'jobtype.php',            // Step 4
    'disabilitytype.php',     // Step 5
    'apparent-workplaceneeds.php',    // Step 6a (for apparent disabilities)
    'non-apparent-workplaceneeds.php',// Step 6b (for non-apparent disabilities)
    'uploadresume.php',       // Step 7
    'dashboard.php'           // Final destination
];

// Get current page name
$currentPage = basename($_SERVER['PHP_SELF']);

// Find current position in sequence
$currentPosition = array_search($currentPage, $pageSequence);

// Check if custom previous or next pages have been defined
$prevPage = isset($customPrevPage) ? $customPrevPage : '';
$nextPage = isset($customNextPage) ? $customNextPage : '';

// If no custom pages set, get previous and next pages from sequence
if (empty($prevPage) && $currentPosition > 0) {
    $prevPage = $pageSequence[$currentPosition - 1];
}

if (empty($nextPage) && $currentPosition < count($pageSequence) - 1) {
    $nextPage = $pageSequence[$currentPosition + 1];
}

// Add relevant directory path to the URLs
$prevPage = !empty($prevPage) ? "../../frontend/candidate/" . $prevPage : "#";
$nextPage = !empty($nextPage) ? "../../frontend/candidate/" . $nextPage : "#";

// Handle custom onClick functions
$backOnClick = isset($backOnClick) ? $backOnClick : "goToAccountSetup()";
$clearOnClick = isset($clearOnClick) ? $clearOnClick : "clearSelection()";
$continueOnClick = isset($continueOnClick) ? $continueOnClick : "goToNextPage()";
?>

<div class="buttons">
    <button class="btn back" onclick="<?php echo $backOnClick; ?>">
        <i class="fas fa-arrow-left"></i> Back
    </button>
    <div class="right-buttons">
        <button class="btn clear" onclick="<?php echo $clearOnClick; ?>">
            <i class="fas fa-trash-alt"></i> Clear
        </button>
        <button class="btn continue" onclick="<?php echo $continueOnClick; ?>">
            Continue <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</div>