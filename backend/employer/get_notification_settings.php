<?php
/**
 * Get Notification Settings
 * Fetches current employer notification preferences
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get notification settings
    $sql = "
        SELECT 
            email_notifications,
            sms_notifications,
            push_notifications,
            new_applications,
            application_status,
            message_notifications,
            system_updates,
            marketing_notifications,
            email_frequency,
            enable_quiet_hours,
            quiet_from,
            quiet_to
        FROM employer_notification_settings
        WHERE employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Return default settings if none exist
        $settings = [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'new_applications' => true,
            'application_status' => true,
            'message_notifications' => true,
            'system_updates' => true,
            'marketing_notifications' => false,
            'email_frequency' => 'immediate',
            'enable_quiet_hours' => false,
            'quiet_from' => '22:00:00',
            'quiet_to' => '08:00:00'
        ];
    }
    
    // Convert boolean values
    foreach (['email_notifications', 'sms_notifications', 'push_notifications', 
              'new_applications', 'application_status', 'message_notifications',
              'system_updates', 'marketing_notifications', 'enable_quiet_hours'] as $field) {
        $settings[$field] = (bool)($settings[$field] ?? false);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_notification_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_notification_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>