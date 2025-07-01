<?php
/**
 * Standard navigation buttons for candidate journey
 * 
 * This file manages the navigation flow between pages in the candidate journey.
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
// This allows for conditional routing (like apparent vs non-apparent)
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

// Configure button visibility
$showBackButton = isset($hideBackButton) ? !$hideBackButton : true;
$showContinueButton = isset($hideContinueButton) ? !$hideContinueButton : true;

// Handle custom button text
$backText = isset($customBackText) ? $customBackText : "Back";
$continueText = isset($customContinueText) ? $customContinueText : "Continue";

// Handle custom button IDs and classes for JavaScript events
$backBtnId = isset($backBtnId) ? $backBtnId : "backBtn";
$continueBtnId = isset($continueBtnId) ? $continueBtnId : "continueBtn";

// Handle custom onclick functions if provided
$backOnClick = isset($backOnClick) ? "onclick=\"{$backOnClick}\"" : "";
$continueOnClick = isset($continueOnClick) ? "onclick=\"{$continueOnClick}\"" : "";
?>

<div class="buttons">
    <?php if ($showBackButton): ?>
    <a href="<?php echo $prevPage; ?>" class="btn back" id="<?php echo $backBtnId; ?>" <?php echo $backOnClick; ?>>
        <i class="fas fa-arrow-left"></i> <?php echo $backText; ?>
    </a>
    <?php endif; ?>
    
    <?php if ($showContinueButton): ?>
    <a href="<?php echo $nextPage; ?>" class="btn continue" id="<?php echo $continueBtnId; ?>" <?php echo $continueOnClick; ?>>
        <?php echo $continueText; ?> <i class="fas fa-arrow-right"></i>
    </a>
    <?php endif; ?>
</div>