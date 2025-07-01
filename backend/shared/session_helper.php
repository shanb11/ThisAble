<?php
/**
 * Session Helper for Employer Dashboard
 * Provides session management and employer data access
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if employer is logged in
 */
function isEmployerLoggedIn() {
    return isset($_SESSION['employer_id']) && 
           isset($_SESSION['logged_in']) && 
           $_SESSION['logged_in'] === true;
}

/**
 * Get current employer ID
 */
function getCurrentEmployerId() {
    if (isEmployerLoggedIn()) {
        return $_SESSION['employer_id'];
    }
    return null;
}

/**
 * Get current employer name
 */
function getCurrentEmployerName() {
    if (isEmployerLoggedIn()) {
        return $_SESSION['employer_name'] ?? 'Employer';
    }
    return null;
}

/**
 * Get current company name
 */
function getCurrentCompanyName() {
    if (isEmployerLoggedIn()) {
        return $_SESSION['company_name'] ?? 'Company';
    }
    return null;
}

/**
 * Get employer session data as JSON for JavaScript
 */
function getEmployerSessionData() {
    if (!isEmployerLoggedIn()) {
        return json_encode(['logged_in' => false]);
    }
    
    return json_encode([
        'logged_in' => true,
        'employer_id' => $_SESSION['employer_id'],
        'employer_name' => $_SESSION['employer_name'] ?? '',
        'company_name' => $_SESSION['company_name'] ?? '',
        'account_id' => $_SESSION['account_id'] ?? null,
        'contact_id' => $_SESSION['contact_id'] ?? null
    ]);
}

/**
 * Redirect to login if not authenticated
 */
function requireEmployerLogin($redirectTo = 'emplogin.php') {
    if (!isEmployerLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

/**
 * Output session data as JavaScript variable
 */
function echoEmployerSessionScript() {
    echo "<script>\n";
    echo "window.employerSession = " . getEmployerSessionData() . ";\n";
    echo "window.getCurrentEmployerId = function() { return window.employerSession.employer_id || null; };\n";
    echo "window.getCurrentCompanyName = function() { return window.employerSession.company_name || ''; };\n";
    echo "window.isEmployerLoggedIn = function() { return window.employerSession.logged_in || false; };\n";
    echo "</script>\n";
}

/**
 * Update last activity timestamp
 */
function updateLastActivity() {
    $_SESSION['last_activity'] = time();
}

/**
 * Check if session has expired (30 minutes)
 */
function isSessionExpired($timeout = 1800) {
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    return (time() - $_SESSION['last_activity']) > $timeout;
}

/**
 * Clear employer session
 */
function logoutEmployer() {
    // Clear employer-specific session data
    unset($_SESSION['employer_id']);
    unset($_SESSION['account_id']);
    unset($_SESSION['contact_id']);
    unset($_SESSION['employer_email']);
    unset($_SESSION['employer_name']);
    unset($_SESSION['company_name']);
    unset($_SESSION['logged_in']);
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Log activity for security/audit purposes
 */
function logActivity($action, $details = '') {
    if (isEmployerLoggedIn()) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'employer_id' => getCurrentEmployerId(),
            'company_name' => getCurrentCompanyName(),
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Log to PHP error log
        error_log("Employer Activity: " . json_encode($logEntry));
    }
}
/**
 * Add this function to your existing backend/shared/session_helper.php file
 * Add it anywhere in the file (preferably near the other functions)
 */

/**
 * Get employer data by employer ID
 * @param int $employer_id
 * @return array|false Employer data or false if not found
 */
function getEmployerData($employer_id) {
    global $conn;
    
    try {
        $sql = "
            SELECT 
                e.employer_id,
                e.company_name,
                e.industry,
                e.company_address,
                e.company_size,
                e.company_website,
                e.company_description,
                e.verification_status,
                ec.first_name,
                ec.last_name,
                ec.position,
                ec.contact_number,
                ec.email
            FROM employers e
            INNER JOIN employer_contacts ec ON e.employer_id = ec.employer_id
            WHERE e.employer_id = :employer_id AND ec.is_primary = 1
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['employer_id' => $employer_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error getting employer data: " . $e->getMessage());
        return false;
    }
}

?>