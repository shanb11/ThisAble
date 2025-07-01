<?php
/**
 * Get User Settings API for ThisAble Mobile
 * Returns: all user preference settings
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    error_log("User Settings API: seeker_id=$seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== CONTACT INFORMATION =====
    $stmt = $conn->prepare("
        SELECT 
            js.first_name,
            js.last_name,
            js.contact_number,
            js.city,
            js.province,
            ua.email,
            pd.location as address
        FROM job_seekers js
        LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
        WHERE js.seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $contactInfo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // ===== NOTIFICATION SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            email_notifications,
            sms_notifications,
            push_notifications,
            job_alerts,
            application_updates,
            message_notifications,
            marketing_notifications
        FROM notification_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $notificationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$notificationSettings) {
        $notificationSettings = [
            'email_notifications' => true,
            'sms_notifications' => false,
            'push_notifications' => true,
            'job_alerts' => true,
            'application_updates' => true,
            'message_notifications' => true,
            'marketing_notifications' => false
        ];
    } else {
        // Convert to boolean
        foreach ($notificationSettings as &$setting) {
            $setting = (bool)$setting;
        }
    }
    
    // ===== ACCESSIBILITY SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            high_contrast,
            text_size,
            screen_reader_support,
            keyboard_navigation,
            motion_reduction,
            assistive_tools
        FROM accessibility_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $accessibilitySettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$accessibilitySettings) {
        $accessibilitySettings = [
            'high_contrast' => false,
            'text_size' => 'medium',
            'screen_reader_support' => true,
            'keyboard_navigation' => true,
            'motion_reduction' => false,
            'assistive_tools' => null
        ];
    } else {
        // Convert boolean fields
        $accessibilitySettings['high_contrast'] = (bool)$accessibilitySettings['high_contrast'];
        $accessibilitySettings['screen_reader_support'] = (bool)$accessibilitySettings['screen_reader_support'];
        $accessibilitySettings['keyboard_navigation'] = (bool)$accessibilitySettings['keyboard_navigation'];
        $accessibilitySettings['motion_reduction'] = (bool)$accessibilitySettings['motion_reduction'];
        
        // Parse assistive tools JSON
        if ($accessibilitySettings['assistive_tools']) {
            $accessibilitySettings['assistive_tools'] = json_decode($accessibilitySettings['assistive_tools'], true);
        }
    }
    
    // ===== PRIVACY SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            profile_visibility,
            peer_visibility,
            search_listing,
            data_collection,
            third_party_sharing
        FROM privacy_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $privacySettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$privacySettings) {
        $privacySettings = [
            'profile_visibility' => 'all',
            'peer_visibility' => true,
            'search_listing' => true,
            'data_collection' => true,
            'third_party_sharing' => false
        ];
    } else {
        // Convert boolean fields
        $privacySettings['peer_visibility'] = (bool)$privacySettings['peer_visibility'];
        $privacySettings['search_listing'] = (bool)$privacySettings['search_listing'];
        $privacySettings['data_collection'] = (bool)$privacySettings['data_collection'];
        $privacySettings['third_party_sharing'] = (bool)$privacySettings['third_party_sharing'];
    }
    
    // ===== DISPLAY SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            theme,
            font_size
        FROM user_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $displaySettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$displaySettings) {
        $displaySettings = [
            'theme' => 'light',
            'font_size' => 'medium'
        ];
    }
    
    // ===== JOB ALERT SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            alert_frequency,
            email_alerts,
            sms_alerts,
            app_alerts,
            job_categories,
            job_keywords,
            job_location
        FROM job_alert_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $jobAlertSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$jobAlertSettings) {
        $jobAlertSettings = [
            'alert_frequency' => 'daily',
            'email_alerts' => true,
            'sms_alerts' => false,
            'app_alerts' => true,
            'job_categories' => [],
            'job_keywords' => null,
            'job_location' => null
        ];
    } else {
        // Convert boolean fields
        $jobAlertSettings['email_alerts'] = (bool)$jobAlertSettings['email_alerts'];
        $jobAlertSettings['sms_alerts'] = (bool)$jobAlertSettings['sms_alerts'];
        $jobAlertSettings['app_alerts'] = (bool)$jobAlertSettings['app_alerts'];
        
        // Parse job categories JSON
        if ($jobAlertSettings['job_categories']) {
            $jobAlertSettings['job_categories'] = json_decode($jobAlertSettings['job_categories'], true);
        } else {
            $jobAlertSettings['job_categories'] = [];
        }
    }
    
    // ===== APPLICATION SETTINGS =====
    $stmt = $conn->prepare("
        SELECT 
            auto_fill,
            include_cover_letter,
            follow_companies,
            default_cover_letter,
            save_application_history,
            receive_application_feedback
        FROM application_settings
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $applicationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no settings found
    if (!$applicationSettings) {
        $applicationSettings = [
            'auto_fill' => true,
            'include_cover_letter' => true,
            'follow_companies' => true,
            'default_cover_letter' => null,
            'save_application_history' => true,
            'receive_application_feedback' => true
        ];
    } else {
        // Convert boolean fields
        $applicationSettings['auto_fill'] = (bool)$applicationSettings['auto_fill'];
        $applicationSettings['include_cover_letter'] = (bool)$applicationSettings['include_cover_letter'];
        $applicationSettings['follow_companies'] = (bool)$applicationSettings['follow_companies'];
        $applicationSettings['save_application_history'] = (bool)$applicationSettings['save_application_history'];
        $applicationSettings['receive_application_feedback'] = (bool)$applicationSettings['receive_application_feedback'];
    }
    
    // ===== COMPILE RESPONSE =====
    $settingsData = [
        'contact_info' => $contactInfo,
        'notifications' => $notificationSettings,
        'accessibility' => $accessibilitySettings,
        'privacy' => $privacySettings,
        'display' => $displaySettings,
        'job_alerts' => $jobAlertSettings,
        'applications' => $applicationSettings
    ];
    
    ApiResponse::success($settingsData, "User settings retrieved successfully");
    
} catch(PDOException $e) {
    error_log("User settings database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("User settings error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while retrieving user settings");
}
?>