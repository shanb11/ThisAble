<?php
/**
 * Session Test API - Same format as get_job_listings.php
 * Create as: backend/employer/test_session.php
 * Test at: http://localhost/ThisAble/backend/employer/test_session.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once('../db.php');
require_once('../shared/session_helper.php');

try {
    $debug_info = [
        'session_id' => session_id(),
        'session_data' => $_SESSION,
        'session_status' => session_status(),
        'cookies' => $_COOKIE,
        'current_time' => date('Y-m-d H:i:s')
    ];

    // Test 1: Check session exists
    if (empty($_SESSION)) {
        throw new Exception('No session data found');
    }

    // Test 2: Check employer_id
    if (!isset($_SESSION['employer_id'])) {
        throw new Exception('No employer_id in session');
    }

    // Test 3: Check account_type
    if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'employer') {
        throw new Exception('Invalid account type: ' . ($_SESSION['account_type'] ?? 'none'));
    }

    // Test 4: Try isEmployerLoggedIn function
    $is_logged_in = false;
    if (function_exists('isEmployerLoggedIn')) {
        $is_logged_in = isEmployerLoggedIn();
    } else {
        throw new Exception('isEmployerLoggedIn function not found');
    }

    if (!$is_logged_in) {
        throw new Exception('isEmployerLoggedIn() returned false');
    }

    // Test 5: Try getEmployerData function
    $employer_data = null;
    if (function_exists('getEmployerData')) {
        $employer_data = getEmployerData($_SESSION['employer_id']);
    } else {
        throw new Exception('getEmployerData function not found');
    }

    if (!$employer_data) {
        throw new Exception('getEmployerData returned no data');
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Session validation successful',
        'data' => [
            'employer_id' => $_SESSION['employer_id'],
            'account_type' => $_SESSION['account_type'],
            'company_name' => $employer_data['company_name'],
            'is_logged_in' => $is_logged_in
        ],
        'debug' => $debug_info,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Session validation failed: ' . $e->getMessage(),
        'debug' => $debug_info ?? [],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>