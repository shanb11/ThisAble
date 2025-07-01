<?php
/**
 * Update Privacy Settings
 * Handles employer privacy and data sharing preferences
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Extract and validate input data
    $profile_visibility = isset($input['profile_visibility']) ? (bool)$input['profile_visibility'] : true;
    $share_company_info = isset($input['share_company_info']) ? (bool)$input['share_company_info'] : true;
    $share_contact_info = isset($input['share_contact_info']) ? (bool)$input['share_contact_info'] : false;
    $job_visibility = $input['job_visibility'] ?? 'public';
    $allow_data_collection = isset($input['allow_data_collection']) ? (bool)$input['allow_data_collection'] : true;
    $allow_marketing = isset($input['allow_marketing']) ? (bool)$input['allow_marketing'] : false;
    $allow_third_party = isset($input['allow_third_party']) ? (bool)$input['allow_third_party'] : false;
    
    // Validation
    $errors = [];
    
    // Validate job visibility
    $valid_visibility = ['public', 'limited', 'private'];
    if (!in_array($job_visibility, $valid_visibility)) {
        $errors[] = 'Invalid job visibility setting';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $errors[0],
            'errors' => $errors
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Insert or update privacy settings
        $upsert_sql = "
            INSERT INTO employer_privacy_settings 
            (employer_id, profile_visibility, share_company_info, share_contact_info,
             job_visibility, allow_data_collection, allow_marketing, allow_third_party,
             created_at, updated_at)
            VALUES (:employer_id, :profile_visibility, :share_company_info, :share_contact_info,
                    :job_visibility, :allow_data_collection, :allow_marketing, :allow_third_party,
                    NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            profile_visibility = :profile_visibility,
            share_company_info = :share_company_info,
            share_contact_info = :share_contact_info,
            job_visibility = :job_visibility,
            allow_data_collection = :allow_data_collection,
            allow_marketing = :allow_marketing,
            allow_third_party = :allow_third_party,
            updated_at = NOW()
        ";
        
        $upsert_stmt = $conn->prepare($upsert_sql);
        $upsert_result = $upsert_stmt->execute([
            'employer_id' => $employer_id,
            'profile_visibility' => $profile_visibility ? 1 : 0,
            'share_company_info' => $share_company_info ? 1 : 0,
            'share_contact_info' => $share_contact_info ? 1 : 0,
            'job_visibility' => $job_visibility,
            'allow_data_collection' => $allow_data_collection ? 1 : 0,
            'allow_marketing' => $allow_marketing ? 1 : 0,
            'allow_third_party' => $allow_third_party ? 1 : 0
        ]);
        
        if (!$upsert_result) {
            throw new Exception('Failed to update privacy settings');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Privacy settings updated successfully',
            'data' => [
                'profile_visibility' => $profile_visibility,
                'share_company_info' => $share_company_info,
                'share_contact_info' => $share_contact_info,
                'job_visibility' => $job_visibility,
                'allow_data_collection' => $allow_data_collection,
                'allow_marketing' => $allow_marketing,
                'allow_third_party' => $allow_third_party
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_privacy_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_privacy_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating privacy settings. Please try again.'
    ]);
}
?>