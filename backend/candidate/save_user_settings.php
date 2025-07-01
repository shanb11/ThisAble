<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];
$setting_type = $_POST['setting_type'] ?? '';

try {
    switch ($setting_type) {
        case 'accessibility':
            $high_contrast = isset($_POST['high_contrast']) ? 1 : 0;
            $text_size = $_POST['text_size'] ?? 'medium';
            $screen_reader = isset($_POST['screen_reader']) ? 1 : 0;
            $keyboard_nav = isset($_POST['keyboard_nav']) ? 1 : 0;
            $motion_reduction = isset($_POST['motion_reduction']) ? 1 : 0;
            $assistive_tools = isset($_POST['assistive_tools']) ? json_encode($_POST['assistive_tools']) : null;
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM accessibility_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("
                    UPDATE accessibility_settings 
                    SET high_contrast = ?, text_size = ?, screen_reader_support = ?, 
                        keyboard_navigation = ?, motion_reduction = ?, assistive_tools = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$high_contrast, $text_size, $screen_reader, $keyboard_nav, $motion_reduction, $assistive_tools, $seeker_id]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO accessibility_settings (seeker_id, high_contrast, text_size, screen_reader_support, keyboard_navigation, motion_reduction, assistive_tools)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seeker_id, $high_contrast, $text_size, $screen_reader, $keyboard_nav, $motion_reduction, $assistive_tools]);
            }
            break;
            
        case 'display':
            $theme = $_POST['theme'] ?? 'light';
            $font_size = $_POST['font_size'] ?? 'medium';
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM user_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("UPDATE user_settings SET theme = ?, font_size = ? WHERE seeker_id = ?");
                $stmt->execute([$theme, $font_size, $seeker_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO user_settings (seeker_id, theme, font_size) VALUES (?, ?, ?)");
                $stmt->execute([$seeker_id, $theme, $font_size]);
            }
            break;
            
        case 'notifications':
            $email_notif = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_notif = isset($_POST['sms_notifications']) ? 1 : 0;
            $push_notif = isset($_POST['push_notifications']) ? 1 : 0;
            $job_alerts = isset($_POST['job_alerts']) ? 1 : 0;
            $app_updates = isset($_POST['application_updates']) ? 1 : 0;
            $msg_notif = isset($_POST['message_notifications']) ? 1 : 0;
            $marketing_notif = isset($_POST['marketing_notifications']) ? 1 : 0;
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM notification_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("
                    UPDATE notification_settings 
                    SET email_notifications = ?, sms_notifications = ?, push_notifications = ?,
                        job_alerts = ?, application_updates = ?, message_notifications = ?, marketing_notifications = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$email_notif, $sms_notif, $push_notif, $job_alerts, $app_updates, $msg_notif, $marketing_notif, $seeker_id]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO notification_settings (seeker_id, email_notifications, sms_notifications, push_notifications, job_alerts, application_updates, message_notifications, marketing_notifications)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seeker_id, $email_notif, $sms_notif, $push_notif, $job_alerts, $app_updates, $msg_notif, $marketing_notif]);
            }
            break;
            
        case 'privacy':
            $profile_visibility = $_POST['profile_visibility'] ?? 'all';
            $peer_visibility = isset($_POST['peer_visibility']) ? 1 : 0;
            $search_listing = isset($_POST['search_listing']) ? 1 : 0;
            $data_collection = isset($_POST['data_collection']) ? 1 : 0;
            $third_party_sharing = isset($_POST['third_party_sharing']) ? 1 : 0;
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM privacy_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("
                    UPDATE privacy_settings 
                    SET profile_visibility = ?, peer_visibility = ?, search_listing = ?, data_collection = ?, third_party_sharing = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$profile_visibility, $peer_visibility, $search_listing, $data_collection, $third_party_sharing, $seeker_id]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO privacy_settings (seeker_id, profile_visibility, peer_visibility, search_listing, data_collection, third_party_sharing)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seeker_id, $profile_visibility, $peer_visibility, $search_listing, $data_collection, $third_party_sharing]);
            }
            break;
            
        case 'job_alerts':
            $alert_frequency = $_POST['alert_frequency'] ?? 'daily';
            $email_alerts = isset($_POST['email_alerts']) ? 1 : 0;
            $sms_alerts = isset($_POST['sms_alerts']) ? 1 : 0;
            $app_alerts = isset($_POST['app_alerts']) ? 1 : 0;
            $job_categories = isset($_POST['job_categories']) ? json_encode($_POST['job_categories']) : null;
            $job_keywords = $_POST['job_keywords'] ?? null;
            $job_location = $_POST['job_location'] ?? null;
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM job_alert_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("
                    UPDATE job_alert_settings 
                    SET alert_frequency = ?, email_alerts = ?, sms_alerts = ?, app_alerts = ?, 
                        job_categories = ?, job_keywords = ?, job_location = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$alert_frequency, $email_alerts, $sms_alerts, $app_alerts, $job_categories, $job_keywords, $job_location, $seeker_id]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO job_alert_settings (seeker_id, alert_frequency, email_alerts, sms_alerts, app_alerts, job_categories, job_keywords, job_location)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seeker_id, $alert_frequency, $email_alerts, $sms_alerts, $app_alerts, $job_categories, $job_keywords, $job_location]);
            }
            break;
            
        case 'application_prefs':
            $auto_fill = isset($_POST['auto_fill']) ? 1 : 0;
            $include_cover_letter = isset($_POST['include_cover_letter']) ? 1 : 0;
            $follow_companies = isset($_POST['follow_companies']) ? 1 : 0;
            $default_cover_letter = $_POST['default_cover_letter'] ?? null;
            $save_history = isset($_POST['save_application_history']) ? 1 : 0;
            $receive_feedback = isset($_POST['receive_application_feedback']) ? 1 : 0;
            
            // Check if record exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM application_settings WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $stmt = $conn->prepare("
                    UPDATE application_settings 
                    SET auto_fill = ?, include_cover_letter = ?, follow_companies = ?, 
                        default_cover_letter = ?, save_application_history = ?, receive_application_feedback = ?
                    WHERE seeker_id = ?
                ");
                $stmt->execute([$auto_fill, $include_cover_letter, $follow_companies, $default_cover_letter, $save_history, $receive_feedback, $seeker_id]);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO application_settings (seeker_id, auto_fill, include_cover_letter, follow_companies, default_cover_letter, save_application_history, receive_application_feedback)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$seeker_id, $auto_fill, $include_cover_letter, $follow_companies, $default_cover_letter, $save_history, $receive_feedback]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid setting type']);
            exit;
    }
    
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while saving settings: ' . $e->getMessage()]);
}
?>