<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

try {
    // Get user basic info
    $stmt = $conn->prepare("
        SELECT js.*, ua.email 
        FROM job_seekers js 
        LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id 
        WHERE js.seeker_id = ?
    ");
    $stmt->execute([$seeker_id]);
    $user_info = $stmt->fetch();
    
    // Get all settings from different tables
    $settings = [];
    
    // User settings
    $stmt = $conn->prepare("SELECT * FROM user_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $user_settings = $stmt->fetch();
    if ($user_settings) {
        $settings = array_merge($settings, $user_settings);
    }
    
    // Accessibility settings
    $stmt = $conn->prepare("SELECT * FROM accessibility_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $accessibility_settings = $stmt->fetch();
    if ($accessibility_settings) {
        $settings = array_merge($settings, $accessibility_settings);
    }
    
    // Notification settings
    $stmt = $conn->prepare("SELECT * FROM notification_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $notification_settings = $stmt->fetch();
    if ($notification_settings) {
        $settings = array_merge($settings, $notification_settings);
    }
    
    // Privacy settings
    $stmt = $conn->prepare("SELECT * FROM privacy_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $privacy_settings = $stmt->fetch();
    if ($privacy_settings) {
        $settings = array_merge($settings, $privacy_settings);
    }
    
    // Job alert settings
    $stmt = $conn->prepare("SELECT * FROM job_alert_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $job_alert_settings = $stmt->fetch();
    if ($job_alert_settings) {
        $settings = array_merge($settings, $job_alert_settings);
    }
    
    // Application settings
    $stmt = $conn->prepare("SELECT * FROM application_settings WHERE seeker_id = ?");
    $stmt->execute([$seeker_id]);
    $application_settings = $stmt->fetch();
    if ($application_settings) {
        $settings = array_merge($settings, $application_settings);
    }
    
    $response = [
        'success' => true,
        'data' => [
            'user_info' => $user_info,
            'settings' => $settings
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while fetching user data: ' . $e->getMessage()]);
}
?>