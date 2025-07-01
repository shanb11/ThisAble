<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in and is an employer
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$employer_id = $_SESSION['employer_id'];

try {
    // Get current progress
    $progress_sql = "SELECT 
                        esp.*,
                        e.company_name,
                        e.industry,
                        e.company_address,
                        e.company_description,
                        e.mission_vision,
                        e.why_join_us,
                        e.company_logo_path
                     FROM employer_setup_progress esp
                     LEFT JOIN employers e ON esp.employer_id = e.employer_id
                     WHERE esp.employer_id = :employer_id";
    $progress_stmt = $conn->prepare($progress_sql);
    $progress_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $progress_stmt->execute();
    $progress_data = $progress_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$progress_data) {
        // Create initial progress record if not exists
        $create_sql = "INSERT INTO employer_setup_progress 
                      (employer_id, basic_info_complete, completion_percentage, created_at, updated_at) 
                      VALUES (:employer_id, 1, 20, NOW(), NOW())";
        $create_stmt = $conn->prepare($create_sql);
        $create_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
        $create_stmt->execute();
        
        // Get the created record
        $progress_stmt->execute();
        $progress_data = $progress_stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Calculate actual completion percentage based on completed steps
    $completed_steps = 0;
    $total_steps = 5; // basic_info, logo, description, preferences, social
    
    if ($progress_data['basic_info_complete']) $completed_steps++;
    if ($progress_data['logo_upload_complete']) $completed_steps++;
    if ($progress_data['description_complete']) $completed_steps++;
    if ($progress_data['preferences_complete']) $completed_steps++;
    if ($progress_data['social_complete']) $completed_steps++;
    
    $actual_percentage = ($completed_steps / $total_steps) * 100;
    
    // Determine next step
    $next_step = 'empaccsetup.php';
    if (!$progress_data['logo_upload_complete']) {
        $next_step = 'empuploadlogo.php';
    } elseif (!$progress_data['description_complete']) {
        $next_step = 'empdescription.php';
    } elseif (!$progress_data['preferences_complete']) {
        $next_step = 'empreferences.php';
    } elseif (!$progress_data['social_complete']) {
        $next_step = 'empsocmedlinks.php';
    } elseif ($actual_percentage === 100) {
        $next_step = 'empdashboard.php';
    }
    
    // Get hiring preferences if exists
    $preferences_sql = "SELECT * FROM employer_hiring_preferences WHERE employer_id = :employer_id";
    $preferences_stmt = $conn->prepare($preferences_sql);
    $preferences_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $preferences_stmt->execute();
    $preferences_data = $preferences_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get social links if exists
    $social_sql = "SELECT * FROM employer_social_links WHERE employer_id = :employer_id";
    $social_stmt = $conn->prepare($social_sql);
    $social_stmt->bindParam(':employer_id', $employer_id, PDO::PARAM_INT);
    $social_stmt->execute();
    $social_data = $social_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'progress' => [
            'completion_percentage' => $actual_percentage,
            'setup_complete' => $progress_data['setup_complete'],
            'basic_info_complete' => $progress_data['basic_info_complete'],
            'logo_upload_complete' => $progress_data['logo_upload_complete'],
            'description_complete' => $progress_data['description_complete'],
            'preferences_complete' => $progress_data['preferences_complete'],
            'social_complete' => $progress_data['social_complete'],
            'next_step' => $next_step
        ],
        'company_data' => [
            'company_name' => $progress_data['company_name'],
            'industry' => $progress_data['industry'],
            'company_address' => $progress_data['company_address'],
            'company_description' => $progress_data['company_description'],
            'mission_vision' => $progress_data['mission_vision'],
            'why_join_us' => $progress_data['why_join_us'],
            'company_logo_path' => $progress_data['company_logo_path']
        ],
        'preferences_data' => $preferences_data,
        'social_data' => $social_data
    ]);
    
} catch (Exception $e) {
    error_log("Get setup progress error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to get setup progress']);
}
?>