<?php
/**
 * Update Analytics Settings
 * Handles employer analytics and reporting preferences
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
    $enable_analytics = isset($input['enable_analytics']) ? (bool)$input['enable_analytics'] : true;
    $track_time_to_hire = isset($input['track_time_to_hire']) ? (bool)$input['track_time_to_hire'] : true;
    $track_cost_per_hire = isset($input['track_cost_per_hire']) ? (bool)$input['track_cost_per_hire'] : true;
    $track_application_completion = isset($input['track_application_completion']) ? (bool)$input['track_application_completion'] : true;
    $track_diversity = isset($input['track_diversity']) ? (bool)$input['track_diversity'] : true;
    $track_source_effectiveness = isset($input['track_source_effectiveness']) ? (bool)$input['track_source_effectiveness'] : true;
    $weekly_report = isset($input['weekly_report']) ? (bool)$input['weekly_report'] : true;
    $monthly_report = isset($input['monthly_report']) ? (bool)$input['monthly_report'] : true;
    $quarterly_report = isset($input['quarterly_report']) ? (bool)$input['quarterly_report'] : false;
    $data_retention = $input['data_retention'] ?? '1-year';
    $integrate_google_analytics = isset($input['integrate_google_analytics']) ? (bool)$input['integrate_google_analytics'] : false;
    $integrate_hr_system = isset($input['integrate_hr_system']) ? (bool)$input['integrate_hr_system'] : false;
    
    // Validation
    $valid_retention_periods = ['3-months', '6-months', '1-year', '2-years', 'indefinite'];
    if (!in_array($data_retention, $valid_retention_periods)) {
        $data_retention = '1-year';
    }
    
    // Create JSON for settings
    $settings_json = json_encode([
        'enable_analytics' => $enable_analytics,
        'kpis' => [
            'track_time_to_hire' => $track_time_to_hire,
            'track_cost_per_hire' => $track_cost_per_hire,
            'track_application_completion' => $track_application_completion,
            'track_diversity' => $track_diversity,
            'track_source_effectiveness' => $track_source_effectiveness
        ],
        'reports' => [
            'weekly_report' => $weekly_report,
            'monthly_report' => $monthly_report,
            'quarterly_report' => $quarterly_report
        ],
        'data_retention' => $data_retention,
        'integrations' => [
            'google_analytics' => $integrate_google_analytics,
            'hr_system' => $integrate_hr_system
        ]
    ]);
    
    // Update employer record with analytics settings
    $update_sql = "
        UPDATE employers 
        SET analytics_settings = :analytics_settings,
            updated_at = NOW()
        WHERE employer_id = :employer_id
    ";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_result = $update_stmt->execute([
        'analytics_settings' => $settings_json,
        'employer_id' => $employer_id
    ]);
    
    if (!$update_result) {
        throw new Exception('Failed to update analytics settings');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Analytics settings updated successfully',
        'data' => [
            'enable_analytics' => $enable_analytics,
            'weekly_report' => $weekly_report,
            'monthly_report' => $monthly_report,
            'quarterly_report' => $quarterly_report,
            'data_retention' => $data_retention
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in update_analytics_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_analytics_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating analytics settings. Please try again.'
    ]);
}
?>