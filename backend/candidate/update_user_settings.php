<?php
// backend/candidate/update_user_settings.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

$seeker_id = get_seeker_id();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

try {
    $conn->beginTransaction();
    
    $updatedSections = [];
    $section = $input['section'] ?? '';
    $data = $input['data'] ?? [];
    
    switch ($section) {
        case 'personal_info':
            updatePersonalInfo($conn, $seeker_id, $data);
            $updatedSections[] = 'Personal Information';
            break;
            
        case 'contact_info':
            updateContactInfo($conn, $seeker_id, $data);
            $updatedSections[] = 'Contact Information';
            break;
            
        case 'display_settings':
            updateDisplaySettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Display Settings';
            break;
            
        case 'accessibility_settings':
            updateAccessibilitySettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Accessibility Settings';
            break;
            
        case 'notification_settings':
            updateNotificationSettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Notification Settings';
            break;
            
        case 'privacy_settings':
            updatePrivacySettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Privacy Settings';
            break;
            
        case 'job_alert_settings':
            updateJobAlertSettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Job Alert Settings';
            break;
            
        case 'application_settings':
            updateApplicationSettings($conn, $seeker_id, $data);
            $updatedSections[] = 'Application Settings';
            break;
            
        case 'work_preferences':
            updateWorkPreferences($conn, $seeker_id, $data);
            $updatedSections[] = 'Work Preferences';
            break;
            
        case 'workplace_accommodations':
            updateWorkplaceAccommodations($conn, $seeker_id, $data);
            $updatedSections[] = 'Workplace Accommodations';
            break;
            
        default:
            throw new Exception('Invalid settings section: ' . $section);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully',
        'updated_sections' => $updatedSections,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error in update_user_settings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update settings: ' . $e->getMessage()
    ]);
}

// Individual update functions
function updatePersonalInfo($conn, $seeker_id, $data) {
    $query = "
        UPDATE job_seekers 
        SET first_name = :first_name, 
            middle_name = :middle_name, 
            last_name = :last_name, 
            suffix = :suffix,
            city = :city,
            province = :province
        WHERE seeker_id = :seeker_id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':first_name', $data['first_name'] ?? '');
    $stmt->bindValue(':middle_name', $data['middle_name'] ?? '');
    $stmt->bindValue(':last_name', $data['last_name'] ?? '');
    $stmt->bindValue(':suffix', $data['suffix'] ?? '');
    $stmt->bindValue(':city', $data['city'] ?? '');
    $stmt->bindValue(':province', $data['province'] ?? '');
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
}

function updateContactInfo($conn, $seeker_id, $data) {
    // Update job_seekers table
    $seekerQuery = "UPDATE job_seekers SET contact_number = :contact_number WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($seekerQuery);
    $stmt->bindValue(':contact_number', $data['contact_number'] ?? '');
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Update user_accounts table
    if (isset($data['email'])) {
        $emailQuery = "UPDATE user_accounts SET email = :email WHERE seeker_id = :seeker_id";
        $stmt = $conn->prepare($emailQuery);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

function updateDisplaySettings($conn, $seeker_id, $data) {
    // Insert or update user_settings
    $query = "
        INSERT INTO user_settings (seeker_id, theme, font_size, updated_at)
        VALUES (:seeker_id, :theme, :font_size, NOW())
        ON DUPLICATE KEY UPDATE 
        theme = VALUES(theme),
        font_size = VALUES(font_size),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':theme', $data['theme'] ?? 'light');
    $stmt->bindValue(':font_size', $data['font_size'] ?? 'medium');
    $stmt->execute();
}

function updateAccessibilitySettings($conn, $seeker_id, $data) {
    $assistive_tools = isset($data['assistive_tools']) ? json_encode($data['assistive_tools']) : '[]';
    
    $query = "
        INSERT INTO accessibility_settings (
            seeker_id, high_contrast, text_size, screen_reader_support, 
            keyboard_navigation, motion_reduction, assistive_tools, updated_at
        ) VALUES (
            :seeker_id, :high_contrast, :text_size, :screen_reader_support,
            :keyboard_navigation, :motion_reduction, :assistive_tools, NOW()
        ) ON DUPLICATE KEY UPDATE 
        high_contrast = VALUES(high_contrast),
        text_size = VALUES(text_size),
        screen_reader_support = VALUES(screen_reader_support),
        keyboard_navigation = VALUES(keyboard_navigation),
        motion_reduction = VALUES(motion_reduction),
        assistive_tools = VALUES(assistive_tools),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':high_contrast', (bool)($data['high_contrast'] ?? false), PDO::PARAM_BOOL);
    $stmt->bindValue(':text_size', $data['text_size'] ?? 'medium');
    $stmt->bindValue(':screen_reader_support', (bool)($data['screen_reader_support'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':keyboard_navigation', (bool)($data['keyboard_navigation'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':motion_reduction', (bool)($data['motion_reduction'] ?? false), PDO::PARAM_BOOL);
    $stmt->bindValue(':assistive_tools', $assistive_tools);
    $stmt->execute();
}

function updateNotificationSettings($conn, $seeker_id, $data) {
    $query = "
        INSERT INTO notification_settings (
            seeker_id, email_notifications, sms_notifications, push_notifications,
            job_alerts, application_updates, message_notifications, 
            marketing_notifications, updated_at
        ) VALUES (
            :seeker_id, :email_notifications, :sms_notifications, :push_notifications,
            :job_alerts, :application_updates, :message_notifications,
            :marketing_notifications, NOW()
        ) ON DUPLICATE KEY UPDATE 
        email_notifications = VALUES(email_notifications),
        sms_notifications = VALUES(sms_notifications),
        push_notifications = VALUES(push_notifications),
        job_alerts = VALUES(job_alerts),
        application_updates = VALUES(application_updates),
        message_notifications = VALUES(message_notifications),
        marketing_notifications = VALUES(marketing_notifications),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':email_notifications', (bool)($data['email_notifications'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':sms_notifications', (bool)($data['sms_notifications'] ?? false), PDO::PARAM_BOOL);
    $stmt->bindValue(':push_notifications', (bool)($data['push_notifications'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':job_alerts', (bool)($data['job_alerts'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':application_updates', (bool)($data['application_updates'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':message_notifications', (bool)($data['message_notifications'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':marketing_notifications', (bool)($data['marketing_notifications'] ?? false), PDO::PARAM_BOOL);
    $stmt->execute();
}

function updatePrivacySettings($conn, $seeker_id, $data) {
    $query = "
        INSERT INTO privacy_settings (
            seeker_id, profile_visibility, peer_visibility, search_listing,
            data_collection, third_party_sharing, updated_at
        ) VALUES (
            :seeker_id, :profile_visibility, :peer_visibility, :search_listing,
            :data_collection, :third_party_sharing, NOW()
        ) ON DUPLICATE KEY UPDATE 
        profile_visibility = VALUES(profile_visibility),
        peer_visibility = VALUES(peer_visibility),
        search_listing = VALUES(search_listing),
        data_collection = VALUES(data_collection),
        third_party_sharing = VALUES(third_party_sharing),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':profile_visibility', $data['profile_visibility'] ?? 'all');
    $stmt->bindValue(':peer_visibility', (bool)($data['peer_visibility'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':search_listing', (bool)($data['search_listing'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':data_collection', (bool)($data['data_collection'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':third_party_sharing', (bool)($data['third_party_sharing'] ?? false), PDO::PARAM_BOOL);
    $stmt->execute();
}

function updateJobAlertSettings($conn, $seeker_id, $data) {
    $job_categories = isset($data['job_categories']) ? json_encode($data['job_categories']) : '[]';
    
    $query = "
        INSERT INTO job_alert_settings (
            seeker_id, alert_frequency, email_alerts, sms_alerts, app_alerts,
            job_categories, job_keywords, job_location, updated_at
        ) VALUES (
            :seeker_id, :alert_frequency, :email_alerts, :sms_alerts, :app_alerts,
            :job_categories, :job_keywords, :job_location, NOW()
        ) ON DUPLICATE KEY UPDATE 
        alert_frequency = VALUES(alert_frequency),
        email_alerts = VALUES(email_alerts),
        sms_alerts = VALUES(sms_alerts),
        app_alerts = VALUES(app_alerts),
        job_categories = VALUES(job_categories),
        job_keywords = VALUES(job_keywords),
        job_location = VALUES(job_location),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':alert_frequency', $data['alert_frequency'] ?? 'daily');
    $stmt->bindValue(':email_alerts', (bool)($data['email_alerts'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':sms_alerts', (bool)($data['sms_alerts'] ?? false), PDO::PARAM_BOOL);
    $stmt->bindValue(':app_alerts', (bool)($data['app_alerts'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':job_categories', $job_categories);
    $stmt->bindValue(':job_keywords', $data['job_keywords'] ?? '');
    $stmt->bindValue(':job_location', $data['job_location'] ?? '');
    $stmt->execute();
}

function updateApplicationSettings($conn, $seeker_id, $data) {
    $query = "
        INSERT INTO application_settings (
            seeker_id, auto_fill, include_cover_letter, follow_companies,
            default_cover_letter, save_application_history, receive_application_feedback, updated_at
        ) VALUES (
            :seeker_id, :auto_fill, :include_cover_letter, :follow_companies,
            :default_cover_letter, :save_application_history, :receive_application_feedback, NOW()
        ) ON DUPLICATE KEY UPDATE 
        auto_fill = VALUES(auto_fill),
        include_cover_letter = VALUES(include_cover_letter),
        follow_companies = VALUES(follow_companies),
        default_cover_letter = VALUES(default_cover_letter),
        save_application_history = VALUES(save_application_history),
        receive_application_feedback = VALUES(receive_application_feedback),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':auto_fill', (bool)($data['auto_fill'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':include_cover_letter', (bool)($data['include_cover_letter'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':follow_companies', (bool)($data['follow_companies'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':default_cover_letter', $data['default_cover_letter'] ?? '');
    $stmt->bindValue(':save_application_history', (bool)($data['save_application_history'] ?? true), PDO::PARAM_BOOL);
    $stmt->bindValue(':receive_application_feedback', (bool)($data['receive_application_feedback'] ?? true), PDO::PARAM_BOOL);
    $stmt->execute();
}

function updateWorkPreferences($conn, $seeker_id, $data) {
    $query = "
        INSERT INTO user_preferences (
            seeker_id, work_style, job_type, salary_range, availability, updated_at
        ) VALUES (
            :seeker_id, :work_style, :job_type, :salary_range, :availability, NOW()
        ) ON DUPLICATE KEY UPDATE 
        work_style = VALUES(work_style),
        job_type = VALUES(job_type),
        salary_range = VALUES(salary_range),
        availability = VALUES(availability),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':work_style', $data['work_style'] ?? null);
    $stmt->bindValue(':job_type', $data['job_type'] ?? null);
    $stmt->bindValue(':salary_range', $data['salary_range'] ?? '');
    $stmt->bindValue(':availability', $data['availability'] ?? '');
    $stmt->execute();
}

function updateWorkplaceAccommodations($conn, $seeker_id, $data) {
    $accommodation_list = isset($data['accommodation_list']) ? json_encode($data['accommodation_list']) : '[]';
    
    $query = "
        INSERT INTO workplace_accommodations (
            seeker_id, disability_type, accommodation_list, no_accommodations_needed, updated_at
        ) VALUES (
            :seeker_id, :disability_type, :accommodation_list, :no_accommodations_needed, NOW()
        ) ON DUPLICATE KEY UPDATE 
        disability_type = VALUES(disability_type),
        accommodation_list = VALUES(accommodation_list),
        no_accommodations_needed = VALUES(no_accommodations_needed),
        updated_at = NOW()
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindValue(':disability_type', $data['disability_type'] ?? 'apparent');
    $stmt->bindValue(':accommodation_list', $accommodation_list);
    $stmt->bindValue(':no_accommodations_needed', (bool)($data['no_accommodations_needed'] ?? false), PDO::PARAM_BOOL);
    $stmt->execute();
}
?>