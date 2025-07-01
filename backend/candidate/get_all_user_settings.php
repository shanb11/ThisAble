<?php
// backend/candidate/get_all_user_settings.php
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

$seeker_id = get_seeker_id();

try {
    // Get user basic info
    $userQuery = "
        SELECT 
            js.*,
            ua.email,
            dt.disability_name,
            dc.category_name as disability_category
        FROM job_seekers js
        LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
        LEFT JOIN disability_categories dc ON dt.category_id = dc.category_id
        WHERE js.seeker_id = :seeker_id
    ";
    
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $userStmt->execute();
    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userInfo) {
        throw new Exception('User not found');
    }
    
    // Get all settings tables data
    $settingsData = [];
    
    // User settings
    $userSettingsQuery = "SELECT * FROM user_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($userSettingsQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['user_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Accessibility settings
    $accessibilityQuery = "SELECT * FROM accessibility_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($accessibilityQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['accessibility_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Notification settings
    $notificationQuery = "SELECT * FROM notification_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($notificationQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['notification_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Privacy settings
    $privacyQuery = "SELECT * FROM privacy_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($privacyQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['privacy_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Job alert settings
    $jobAlertQuery = "SELECT * FROM job_alert_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($jobAlertQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['job_alert_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Application settings
    $appSettingsQuery = "SELECT * FROM application_settings WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($appSettingsQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['application_settings'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // User preferences
    $preferencesQuery = "SELECT * FROM user_preferences WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($preferencesQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['user_preferences'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Workplace accommodations
    $accommodationsQuery = "SELECT * FROM workplace_accommodations WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($accommodationsQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['workplace_accommodations'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // PWD ID information
    $pwdQuery = "SELECT * FROM pwd_ids WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($pwdQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $settingsData['pwd_info'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Get user's current resume count
    $resumeCountQuery = "SELECT COUNT(*) as count FROM resumes WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($resumeCountQuery);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    $resumeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get activity statistics
    $activityQuery = "
        SELECT 
            (SELECT COUNT(*) FROM job_applications WHERE seeker_id = :seeker_id) as total_applications,
            (SELECT COUNT(*) FROM saved_jobs WHERE seeker_id = :seeker_id) as saved_jobs,
            (SELECT COUNT(*) FROM job_views WHERE seeker_id = :seeker_id) as job_views,
            (SELECT COUNT(*) FROM notifications WHERE recipient_type = 'candidate' AND recipient_id = :seeker_id) as total_notifications
    ";
    
    $activityStmt = $conn->prepare($activityQuery);
    $activityStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $activityStmt->execute();
    $activityStats = $activityStmt->fetch(PDO::FETCH_ASSOC);
    
    // Parse JSON fields
    if (isset($settingsData['accessibility_settings']['assistive_tools'])) {
        $settingsData['accessibility_settings']['assistive_tools'] = 
            json_decode($settingsData['accessibility_settings']['assistive_tools'], true) ?: [];
    }
    
    if (isset($settingsData['job_alert_settings']['job_categories'])) {
        $settingsData['job_alert_settings']['job_categories'] = 
            json_decode($settingsData['job_alert_settings']['job_categories'], true) ?: [];
    }
    
    if (isset($settingsData['workplace_accommodations']['accommodation_list'])) {
        $settingsData['workplace_accommodations']['accommodation_list'] = 
            json_decode($settingsData['workplace_accommodations']['accommodation_list'], true) ?: [];
    }
    
    // Format response
    $response = [
        'success' => true,
        'user_info' => [
            'seeker_id' => $userInfo['seeker_id'],
            'first_name' => $userInfo['first_name'],
            'middle_name' => $userInfo['middle_name'],
            'last_name' => $userInfo['last_name'],
            'suffix' => $userInfo['suffix'],
            'email' => $userInfo['email'],
            'contact_number' => $userInfo['contact_number'],
            'city' => $userInfo['city'],
            'province' => $userInfo['province'],
            'disability_name' => $userInfo['disability_name'],
            'disability_category' => $userInfo['disability_category'],
            'setup_complete' => (bool)$userInfo['setup_complete'],
            'created_at' => $userInfo['created_at']
        ],
        'settings' => $settingsData,
        'activity_stats' => [
            'total_applications' => (int)$activityStats['total_applications'],
            'saved_jobs' => (int)$activityStats['saved_jobs'],
            'job_views' => (int)$activityStats['job_views'],
            'total_notifications' => (int)$activityStats['total_notifications'],
            'resume_count' => (int)$resumeCount
        ],
        'account_info' => [
            'member_since' => date('F Y', strtotime($userInfo['created_at'])),
            'profile_completion' => calculateProfileCompletionSimple($conn, $seeker_id),
            'last_login' => date('M j, Y'),
            'account_status' => 'Active'
        ]
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_all_user_settings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch user settings'
    ]);
}

// Simple profile completion calculation
function calculateProfileCompletionSimple($conn, $seeker_id) {
    $completionChecks = [];
    
    // Check profile details
    $profileStmt = $conn->prepare("SELECT COUNT(*) as count FROM profile_details WHERE seeker_id = ? AND bio IS NOT NULL AND bio != ''");
    $profileStmt->execute([$seeker_id]);
    $completionChecks['profile'] = $profileStmt->fetch()['count'] > 0;
    
    // Check resume
    $resumeStmt = $conn->prepare("SELECT COUNT(*) as count FROM resumes WHERE seeker_id = ?");
    $resumeStmt->execute([$seeker_id]);
    $completionChecks['resume'] = $resumeStmt->fetch()['count'] > 0;
    
    // Check skills
    $skillsStmt = $conn->prepare("SELECT COUNT(*) as count FROM seeker_skills WHERE seeker_id = ?");
    $skillsStmt->execute([$seeker_id]);
    $completionChecks['skills'] = $skillsStmt->fetch()['count'] > 0;
    
    // Check experience
    $expStmt = $conn->prepare("SELECT COUNT(*) as count FROM experience WHERE seeker_id = ?");
    $expStmt->execute([$seeker_id]);
    $completionChecks['experience'] = $expStmt->fetch()['count'] > 0;
    
    // Check education
    $eduStmt = $conn->prepare("SELECT COUNT(*) as count FROM education WHERE seeker_id = ?");
    $eduStmt->execute([$seeker_id]);
    $completionChecks['education'] = $eduStmt->fetch()['count'] > 0;
    
    $completed = array_sum($completionChecks);
    $total = count($completionChecks);
    
    return round(($completed / $total) * 100);
}
?>