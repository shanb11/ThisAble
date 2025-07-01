<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');

// Check if employer is logged in
if (!isset($_SESSION['employer_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employerId = $_SESSION['employer_id'];
    $step = $_POST['step'] ?? '';
    $completed = isset($_POST['completed']) ? (bool)$_POST['completed'] : false;
    
    try {
        // Determine which field to update based on step
        $updateField = '';
        switch ($step) {
            case 'basic_info':
                $updateField = 'basic_info_complete';
                break;
            case 'company_description':
                $updateField = 'company_description_complete';
                break;
            case 'hiring_preferences':
                $updateField = 'hiring_preferences_complete';
                break;
            case 'social_links':
                $updateField = 'social_links_complete';
                break;
            case 'logo':
                $updateField = 'logo_uploaded';
                break;
            default:
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid step'
                ]);
                exit;
        }
        
        // Update the specific step
        $stmt = $conn->prepare("UPDATE employer_setup_progress 
                               SET $updateField = :completed, 
                                   updated_at = CURRENT_TIMESTAMP 
                               WHERE employer_id = :employer_id");
        $stmt->bindParam(':completed', $completed);
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->execute();
        
        // Get current progress to calculate completion percentage
        $stmt = $conn->prepare("SELECT basic_info_complete, company_description_complete, 
                                      hiring_preferences_complete, social_links_complete, logo_uploaded
                               FROM employer_setup_progress 
                               WHERE employer_id = :employer_id");
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->execute();
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate completion percentage
        $totalSteps = 5;
        $completedSteps = 0;
        
        if ($progress['basic_info_complete']) $completedSteps++;
        if ($progress['company_description_complete']) $completedSteps++;
        if ($progress['hiring_preferences_complete']) $completedSteps++;
        if ($progress['social_links_complete']) $completedSteps++;
        if ($progress['logo_uploaded']) $completedSteps++;
        
        $completionPercentage = ($completedSteps / $totalSteps) * 100;
        $setupComplete = ($completionPercentage >= 100) ? 1 : 0;
        
        // Update completion percentage and setup_complete status
        $stmt = $conn->prepare("UPDATE employer_setup_progress 
                               SET completion_percentage = :percentage,
                                   setup_complete = :complete,
                                   updated_at = CURRENT_TIMESTAMP
                               WHERE employer_id = :employer_id");
        $stmt->bindParam(':percentage', $completionPercentage);
        $stmt->bindParam(':complete', $setupComplete);
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->execute();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Setup progress updated successfully',
            'data' => [
                'completion_percentage' => $completionPercentage,
                'setup_complete' => (bool)$setupComplete,
                'step_completed' => $completed
            ]
        ]);
        
    } catch(PDOException $e) {
        error_log("Setup progress update error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update setup progress'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>