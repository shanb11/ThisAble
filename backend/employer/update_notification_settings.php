<?php
/**
 * Update Notification Settings
 * Handles employer notification preferences
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
    $email_notifications = isset($input['email_notifications']) ? (bool)$input['email_notifications'] : true;
    $sms_notifications = isset($input['sms_notifications']) ? (bool)$input['sms_notifications'] : false;
    $push_notifications = isset($input['push_notifications']) ? (bool)$input['push_notifications'] : true;
    $new_applications = isset($input['new_applications']) ? (bool)$input['new_applications'] : true;
    $application_status = isset($input['application_status']) ? (bool)$input['application_status'] : true;
    $message_notifications = isset($input['message_notifications']) ? (bool)$input['message_notifications'] : true;
    $system_updates = isset($input['system_updates']) ? (bool)$input['system_updates'] : true;
    $marketing_notifications = isset($input['marketing_notifications']) ? (bool)$input['marketing_notifications'] : false;
    $email_frequency = $input['email_frequency'] ?? 'immediate';
    $enable_quiet_hours = isset($input['enable_quiet_hours']) ? (bool)$input['enable_quiet_hours'] : false;
    $quiet_from = $input['quiet_from'] ?? '22:00';
    $quiet_to = $input['quiet_to'] ?? '08:00';
    
    // Validation
    $errors = [];
    
    // Validate email frequency
    $valid_frequencies = ['immediate', 'daily', 'weekly'];
    if (!in_array($email_frequency, $valid_frequencies)) {
        $errors[] = 'Invalid email frequency';
    }
    
    // Validate time formats
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $quiet_from)) {
        $errors[] = 'Invalid quiet hours start time format';
    }
    
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $quiet_to)) {
        $errors[] = 'Invalid quiet hours end time format';
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
        // Insert or update notification settings
        $upsert_sql = "
            INSERT INTO employer_notification_settings 
            (employer_id, email_notifications, sms_notifications, push_notifications, 
             new_applications, application_status, message_notifications, system_updates, 
             marketing_notifications, email_frequency, enable_quiet_hours, quiet_from, quiet_to,
             created_at, updated_at)
            VALUES (:employer_id, :email_notifications, :sms_notifications, :push_notifications,
                    :new_applications, :application_status, :message_notifications, :system_updates,
                    :marketing_notifications, :email_frequency, :enable_quiet_hours, :quiet_from, :quiet_to,
                    NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            email_notifications = :email_notifications,
            sms_notifications = :sms_notifications,
            push_notifications = :push_notifications,
            new_applications = :new_applications,
            application_status = :application_status,
            message_notifications = :message_notifications,
            system_updates = :system_updates,
            marketing_notifications = :marketing_notifications,
            email_frequency = :email_frequency,
            enable_quiet_hours = :enable_quiet_hours,
            quiet_from = :quiet_from,
            quiet_to = :quiet_to,
            updated_at = NOW()
        ";
        
        $upsert_stmt = $conn->prepare($upsert_sql);
        $upsert_result = $upsert_stmt->execute([
            'employer_id' => $employer_id,
            'email_notifications' => $email_notifications ? 1 : 0,
            'sms_notifications' => $sms_notifications ? 1 : 0,
            'push_notifications' => $push_notifications ? 1 : 0,
            'new_applications' => $new_applications ? 1 : 0,
            'application_status' => $application_status ? 1 : 0,
            'message_notifications' => $message_notifications ? 1 : 0,
            'system_updates' => $system_updates ? 1 : 0,
            'marketing_notifications' => $marketing_notifications ? 1 : 0,
            'email_frequency' => $email_frequency,
            'enable_quiet_hours' => $enable_quiet_hours ? 1 : 0,
            'quiet_from' => $quiet_from . ':00',
            'quiet_to' => $quiet_to . ':00'
        ]);
        
        if (!$upsert_result) {
            throw new Exception('Failed to update notification settings');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Notification settings updated successfully',
            'data' => [
                'email_notifications' => $email_notifications,
                'sms_notifications' => $sms_notifications,
                'push_notifications' => $push_notifications,
                'new_applications' => $new_applications,
                'application_status' => $application_status,
                'message_notifications' => $message_notifications,
                'system_updates' => $system_updates,
                'marketing_notifications' => $marketing_notifications,
                'email_frequency' => $email_frequency,
                'enable_quiet_hours' => $enable_quiet_hours,
                'quiet_from' => $quiet_from,
                'quiet_to' => $quiet_to
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_notification_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_notification_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating notification settings. Please try again.'
    ]);
}
?>