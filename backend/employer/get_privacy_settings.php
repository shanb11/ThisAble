<?php
/**
 * Get Privacy Settings
 * Fetches current employer privacy preferences
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get privacy settings
    $sql = "
        SELECT 
            profile_visibility,
            share_company_info,
            share_contact_info,
            job_visibility,
            allow_data_collection,
            allow_marketing,
            allow_third_party
        FROM employer_privacy_settings
        WHERE employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Return default settings if none exist
        $settings = [
            'profile_visibility' => true,
            'share_company_info' => true,
            'share_contact_info' => false,
            'job_visibility' => 'public',
            'allow_data_collection' => true,
            'allow_marketing' => false,
            'allow_third_party' => false
        ];
    }
    
    // Convert boolean values
    foreach (['profile_visibility', 'share_company_info', 'share_contact_info',
              'allow_data_collection', 'allow_marketing', 'allow_third_party'] as $field) {
        $settings[$field] = (bool)($settings[$field] ?? false);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_privacy_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_privacy_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>