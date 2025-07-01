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

try {
    $employerId = $_SESSION['employer_id'];
    
    // Get setup progress from database
    $stmt = $conn->prepare("SELECT esp.*, e.company_name, e.company_description, e.company_website,
                                   e.company_logo_path, ec.first_name, ec.last_name, ec.position,
                                   ec.contact_number, ec.email
                           FROM employer_setup_progress esp
                           JOIN employers e ON esp.employer_id = e.employer_id
                           JOIN employer_contacts ec ON e.employer_id = ec.employer_id
                           WHERE esp.employer_id = :employer_id AND ec.is_primary = 1");
    $stmt->bindParam(':employer_id', $employerId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $setupData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculate completion percentage if not already set
        $completionPercentage = 0;
        $totalSteps = 5;
        $completedSteps = 0;
        
        if ($setupData['basic_info_complete']) $completedSteps++;
        if ($setupData['company_description_complete']) $completedSteps++;
        if ($setupData['hiring_preferences_complete']) $completedSteps++;
        if ($setupData['social_links_complete']) $completedSteps++;
        if ($setupData['logo_uploaded']) $completedSteps++;
        
        $completionPercentage = ($completedSteps / $totalSteps) * 100;
        
        // Update completion percentage in database if changed
        if ($completionPercentage != $setupData['completion_percentage']) {
            $updateStmt = $conn->prepare("UPDATE employer_setup_progress 
                                         SET completion_percentage = :percentage,
                                             setup_complete = :complete
                                         WHERE employer_id = :employer_id");
            $updateStmt->bindParam(':percentage', $completionPercentage);
            $setupComplete = ($completionPercentage >= 100) ? 1 : 0;
            $updateStmt->bindParam(':complete', $setupComplete);
            $updateStmt->bindParam(':employer_id', $employerId);
            $updateStmt->execute();
            
            $setupData['completion_percentage'] = $completionPercentage;
            $setupData['setup_complete'] = $setupComplete;
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'setup_complete' => (bool)$setupData['setup_complete'],
                'completion_percentage' => $setupData['completion_percentage'],
                'basic_info_complete' => (bool)$setupData['basic_info_complete'],
                'company_description_complete' => (bool)$setupData['company_description_complete'],
                'hiring_preferences_complete' => (bool)$setupData['hiring_preferences_complete'],
                'social_links_complete' => (bool)$setupData['social_links_complete'],
                'logo_uploaded' => (bool)$setupData['logo_uploaded'],
                'company_info' => [
                    'company_name' => $setupData['company_name'],
                    'company_description' => $setupData['company_description'],
                    'company_website' => $setupData['company_website'],
                    'company_logo_path' => $setupData['company_logo_path']
                ],
                'contact_info' => [
                    'first_name' => $setupData['first_name'],
                    'last_name' => $setupData['last_name'],
                    'position' => $setupData['position'],
                    'contact_number' => $setupData['contact_number'],
                    'email' => $setupData['email']
                ]
            ]
        ]);
    } else {
        // No setup progress found, create initial record
        $stmt = $conn->prepare("INSERT INTO employer_setup_progress 
                               (employer_id, basic_info_complete, company_description_complete, 
                                hiring_preferences_complete, social_links_complete, logo_uploaded,
                                setup_complete, completion_percentage) 
                               VALUES (:employer_id, 0, 0, 0, 0, 0, 0, 0)");
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->execute();
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'setup_complete' => false,
                'completion_percentage' => 0,
                'basic_info_complete' => false,
                'company_description_complete' => false,
                'hiring_preferences_complete' => false,
                'social_links_complete' => false,
                'logo_uploaded' => false
            ]
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Check setup status error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check setup status'
    ]);
}
?>