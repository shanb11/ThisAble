<?php
// backend/employer/session_check.php
// Centralized session validation for employer APIs

require_once '../shared/session_helper.php';

// Function to validate employer session for APIs
function validateEmployerSession() {
    // Check if employer is logged in using your session helper
    if (!isEmployerLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access. Please log in.',
            'redirect' => 'emplogin.php'
        ]);
        exit;
    }
    
    // Check if session has expired
    if (isSessionExpired()) {
        logoutEmployer();
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please log in again.',
            'redirect' => 'emplogin.php'
        ]);
        exit;
    }
    
    // Update last activity
    updateLastActivity();
    
    // Log API access
    $endpoint = basename($_SERVER['PHP_SELF']);
    logActivity("API_ACCESS", "Accessed endpoint: {$endpoint}");
    
    return getCurrentEmployerId();
}

// Function to get employer data with validation
function getValidatedEmployerData() {
    $employer_id = validateEmployerSession();
    
    global $conn;
    $employer_data = getEmployerData($employer_id);
    
    if (!$employer_data) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Employer data not found.'
        ]);
        exit;
    }
    
    return [
        'employer_id' => $employer_id,
        'company_name' => getCurrentCompanyName(),
        'employer_name' => getCurrentEmployerName(),
        'data' => $employer_data
    ];
}
?>