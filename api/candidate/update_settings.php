<?php
/**
 * Update Settings API for ThisAble Mobile
 * Handles: all user settings updates
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $category = $input['category'] ?? '';
    
    if (empty($category)) {
        ApiResponse::validationError(['category' => 'Settings category is required']);
    }
    
    error_log("Update Settings API: seeker_id=$seekerId, category=$category");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    switch ($category) {
        case 'contact_info':
            $firstName = trim($input['first_name'] ?? '');
            $lastName = trim($input['last_name'] ?? '');
            $email = trim($input['email'] ?? '');
            $contactNumber = trim($input['contact_number'] ?? '');
            $address = trim($input['address'] ?? '');
            
            if (empty($firstName) || empty($lastName) || empty($email)) {
                ApiResponse::validationError(['required' => 'First name, last name, and email are required']);
            }
            
            $conn->beginTransaction();
            
            try {
                // Update job_seekers table
                $stmt = $conn->prepare("
                    UPDATE job_seekers 
                    SET first_name = ?, last_name = ?, contact_number = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$firstName, $lastName, $contactNumber, $seekerId]);
                
                // Update user_accounts table
                $stmt = $conn->prepare("
                    UPDATE user_accounts 
                    SET email = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$email, $seekerId]);
                
                // Update profile_details table
                $stmt = $conn->prepare("
                    INSERT INTO profile_details (seeker_id, location, updated_at)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE location = VALUES(location), updated_at = NOW()
                ");
                $stmt->execute([$seekerId, $address]);
                
                $conn->commit();
                ApiResponse::success(['updated' => true], "Contact information updated successfully");
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'notifications':
            $emailNotifications = (bool)($input['email_notifications'] ?? true);
            $smsNotifications = (bool)($input['sms_notifications'] ?? false);
            $pushNotifications = (bool)($input['push_notifications'] ?? true);
            $jobAlerts = (bool)($input['job_alerts'] ?? true);
            $applicationUpdates = (bool)($input['application_updates'] ?? true);
            $messageNotifications = (bool)($input['message_notifications'] ?? true);
            $marketingNotifications = (bool)($input['marketing_notifications'] ?? false);
            
            // Update or insert notification_settings
            $stmt = $conn->prepare("
                INSERT INTO notification_settings 
                (seeker_id, email_notifications, sms_notifications, push_notifications, 
                 job_alerts, application_updates, message_notifications, marketing_notifications, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                email_notifications = VALUES(email_notifications),
                sms_notifications = VALUES(sms_notifications),
                push_notifications = VALUES(push_notifications),
                job_alerts = VALUES(job_alerts),
                application_updates = VALUES(application_updates),
                message_notifications = VALUES(message_notifications),
                marketing_notifications = VALUES(marketing_notifications),
                updated_at = NOW()
            ");
            $stmt->execute([
                $seekerId, $emailNotifications, $smsNotifications, $pushNotifications,
                $jobAlerts, $applicationUpdates, $messageNotifications, $marketingNotifications
            ]);
            
            ApiResponse::success(['updated' => true], "Notification settings updated successfully");
            break;
            
        case 'accessibility':
            $highContrast = (bool)($input['high_contrast'] ?? false);
            $textSize = $input['text_size'] ?? 'medium';
            $screenReaderSupport = (bool)($input['screen_reader_support'] ?? true);
            $keyboardNavigation = (bool)($input['keyboard_navigation'] ?? true);
            $motionReduction = (bool)($input['motion_reduction'] ?? false);
            $assistiveTools = $input['assistive_tools'] ?? null;
            
            if (!in_array($textSize, ['small', 'medium', 'large'])) {
                ApiResponse::validationError(['text_size' => 'Invalid text size']);
            }
            
            // Update or insert accessibility_settings
            $stmt = $conn->prepare("
                INSERT INTO accessibility_settings 
                (seeker_id, high_contrast, text_size, screen_reader_support, 
                 keyboard_navigation, motion_reduction, assistive_tools, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                high_contrast = VALUES(high_contrast),
                text_size = VALUES(text_size),
                screen_reader_support = VALUES(screen_reader_support),
                keyboard_navigation = VALUES(keyboard_navigation),
                motion_reduction = VALUES(motion_reduction),
                assistive_tools = VALUES(assistive_tools),
                updated_at = NOW()
            ");
            $stmt->execute([
                $seekerId, $highContrast, $textSize, $screenReaderSupport,
                $keyboardNavigation, $motionReduction, json_encode($assistiveTools)
            ]);
            
            ApiResponse::success(['updated' => true], "Accessibility settings updated successfully");
            break;
            
        case 'privacy':
            $profileVisibility = $input['profile_visibility'] ?? 'all';
            $peerVisibility = (bool)($input['peer_visibility'] ?? true);
            $searchListing = (bool)($input['search_listing'] ?? true);
            $dataCollection = (bool)($input['data_collection'] ?? true);
            $thirdPartySharing = (bool)($input['third_party_sharing'] ?? false);
            
            if (!in_array($profileVisibility, ['all', 'verified', 'none'])) {
                ApiResponse::validationError(['profile_visibility' => 'Invalid profile visibility setting']);
            }
            
            // Update or insert privacy_settings
            $stmt = $conn->prepare("
                INSERT INTO privacy_settings 
                (seeker_id, profile_visibility, peer_visibility, search_listing, 
                 data_collection, third_party_sharing, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                profile_visibility = VALUES(profile_visibility),
                peer_visibility = VALUES(peer_visibility),
                search_listing = VALUES(search_listing),
                data_collection = VALUES(data_collection),
                third_party_sharing = VALUES(third_party_sharing),
                updated_at = NOW()
            ");
            $stmt->execute([
                $seekerId, $profileVisibility, $peerVisibility, $searchListing,
                $dataCollection, $thirdPartySharing
            ]);
            
            ApiResponse::success(['updated' => true], "Privacy settings updated successfully");
            break;
            
        case 'display':
            $theme = $input['theme'] ?? 'light';
            $fontSize = $input['font_size'] ?? 'medium';
            
            if (!in_array($theme, ['light', 'dark', 'system'])) {
                ApiResponse::validationError(['theme' => 'Invalid theme']);
            }
            
            if (!in_array($fontSize, ['small', 'medium', 'large'])) {
                ApiResponse::validationError(['font_size' => 'Invalid font size']);
            }
            
            // Update or insert user_settings
            $stmt = $conn->prepare("
                INSERT INTO user_settings (seeker_id, theme, font_size, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                theme = VALUES(theme), font_size = VALUES(font_size), updated_at = NOW()
            ");
            $stmt->execute([$seekerId, $theme, $fontSize]);
            
            ApiResponse::success(['updated' => true], "Display settings updated successfully");
            break;
            
        case 'job_alerts':
            $alertFrequency = $input['alert_frequency'] ?? 'daily';
            $emailAlerts = (bool)($input['email_alerts'] ?? true);
            $smsAlerts = (bool)($input['sms_alerts'] ?? false);
            $appAlerts = (bool)($input['app_alerts'] ?? true);
            $jobCategories = $input['job_categories'] ?? [];
            $jobKeywords = $input['job_keywords'] ?? null;
            $jobLocation = $input['job_location'] ?? null;
            
            if (!in_array($alertFrequency, ['daily', 'weekly', 'off'])) {
                ApiResponse::validationError(['alert_frequency' => 'Invalid alert frequency']);
            }
            
            // Update or insert job_alert_settings
            $stmt = $conn->prepare("
                INSERT INTO job_alert_settings 
                (seeker_id, alert_frequency, email_alerts, sms_alerts, app_alerts, 
                 job_categories, job_keywords, job_location, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                alert_frequency = VALUES(alert_frequency),
                email_alerts = VALUES(email_alerts),
                sms_alerts = VALUES(sms_alerts),
                app_alerts = VALUES(app_alerts),
                job_categories = VALUES(job_categories),
                job_keywords = VALUES(job_keywords),
                job_location = VALUES(job_location),
                updated_at = NOW()
            ");
            $stmt->execute([
                $seekerId, $alertFrequency, $emailAlerts, $smsAlerts, $appAlerts,
                json_encode($jobCategories), $jobKeywords, $jobLocation
            ]);
            
            ApiResponse::success(['updated' => true], "Job alert settings updated successfully");
            break;
            
        case 'applications':
            $autoFill = (bool)($input['auto_fill'] ?? true);
            $includeCoverLetter = (bool)($input['include_cover_letter'] ?? true);
            $followCompanies = (bool)($input['follow_companies'] ?? true);
            $defaultCoverLetter = $input['default_cover_letter'] ?? null;
            $saveApplicationHistory = (bool)($input['save_application_history'] ?? true);
            $receiveApplicationFeedback = (bool)($input['receive_application_feedback'] ?? true);
            
            // Update or insert application_settings
            $stmt = $conn->prepare("
                INSERT INTO application_settings 
                (seeker_id, auto_fill, include_cover_letter, follow_companies, 
                 default_cover_letter, save_application_history, receive_application_feedback, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                auto_fill = VALUES(auto_fill),
                include_cover_letter = VALUES(include_cover_letter),
                follow_companies = VALUES(follow_companies),
                default_cover_letter = VALUES(default_cover_letter),
                save_application_history = VALUES(save_application_history),
                receive_application_feedback = VALUES(receive_application_feedback),
                updated_at = NOW()
            ");
            $stmt->execute([
                $seekerId, $autoFill, $includeCoverLetter, $followCompanies,
                $defaultCoverLetter, $saveApplicationHistory, $receiveApplicationFeedback
            ]);
            
            ApiResponse::success(['updated' => true], "Application settings updated successfully");
            break;
            
        case 'password':
            $currentPassword = $input['current_password'] ?? '';
            $newPassword = $input['new_password'] ?? '';
            $confirmPassword = $input['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                ApiResponse::validationError(['required' => 'All password fields are required']);
            }
            
            if ($newPassword !== $confirmPassword) {
                ApiResponse::validationError(['password_match' => 'New passwords do not match']);
            }
            
            if (strlen($newPassword) < 8) {
                ApiResponse::validationError(['password_length' => 'Password must be at least 8 characters']);
            }
            
            // Verify current password
            $stmt = $conn->prepare("SELECT password_hash FROM user_accounts WHERE seeker_id = ?");
            $stmt->execute([$seekerId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                ApiResponse::validationError(['current_password' => 'Current password is incorrect']);
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user_accounts SET password_hash = ? WHERE seeker_id = ?");
            $stmt->execute([$newPasswordHash, $seekerId]);
            
            ApiResponse::success(['updated' => true], "Password updated successfully");
            break;
            
        default:
            ApiResponse::validationError(['category' => 'Invalid settings category']);
    }
    
} catch(PDOException $e) {
    error_log("Update settings database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Update settings error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while updating settings");
}
?>