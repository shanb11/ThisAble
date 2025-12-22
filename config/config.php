<?php
/**
 * ThisAble Configuration File - FIXED FOR INFINITYFREE
 * Replaced heredoc syntax with simple echo for compatibility
 */

// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Determine base URL
if (strpos($hostname, 'localhost') !== false || strpos($hostname, '127.0.0.1') !== false) {
    // LOCALHOST (XAMPP)
    define('BASE_URL', '/ThisAble/');
    define('ENVIRONMENT', 'development');
} elseif (strpos($hostname, 'infinityfree.me') !== false || strpos($hostname, 'infinityfree.com') !== false) {
    // INFINITYFREE PRODUCTION
    define('BASE_URL', '/');
    define('ENVIRONMENT', 'production');
} elseif (strpos($hostname, 'railway.app') !== false || strpos($hostname, 'up.railway.app') !== false) {
    // RAILWAY PRODUCTION
    define('BASE_URL', '/');
    define('ENVIRONMENT', 'production');
} else {
    // DEFAULT (assume root)
    define('BASE_URL', '/');
    define('ENVIRONMENT', 'production');
}

// API Base URL
define('API_BASE_URL', BASE_URL . 'api/');

// Asset paths
define('IMAGES_URL', BASE_URL . 'images/');
define('CSS_URL', BASE_URL . 'styles/');
define('JS_URL', BASE_URL . 'scripts/');

/**
 * Output JavaScript configuration
 * FIXED: Using simple echo instead of heredoc for InfinityFree compatibility
 */
function output_js_config() {
    $baseUrl = BASE_URL;
    $apiBaseUrl = API_BASE_URL;
    $environment = ENVIRONMENT;
    
    // Use simple echo statements instead of heredoc
    echo '<script>';
    echo 'window.APP_BASE_URL = "' . $baseUrl . '";';
    echo 'window.API_BASE_URL = "' . $apiBaseUrl . '";';
    echo 'window.ENVIRONMENT = "' . $environment . '";';
    echo 'window.url = function(path) { path = path.replace(/^\/+/, ""); return window.APP_BASE_URL + path; };';
    echo 'window.apiUrl = function(endpoint) { endpoint = endpoint.replace(/^\/+/, ""); return window.API_BASE_URL + endpoint; };';
    
    // Log config in development mode
    if ($environment === 'development') {
        echo 'console.log("ThisAble Config:", { baseUrl: window.APP_BASE_URL, apiBaseUrl: window.API_BASE_URL, environment: window.ENVIRONMENT });';
    }
    
    echo '</script>';
}

/**
 * Generate URL helper
 * Usage: url('frontend/candidate/login.php')
 */
function url($path) {
    $path = ltrim($path, '/');
    return BASE_URL . $path;
}

/**
 * Generate API URL helper
 * Usage: api_url('candidate/login.php')
 */
function api_url($endpoint) {
    $endpoint = ltrim($endpoint, '/');
    return API_BASE_URL . $endpoint;
}

/**
 * Generate asset URL helper
 * Usage: asset('images/logo.png')
 */
function asset($path) {
    $path = ltrim($path, '/');
    return BASE_URL . $path;
}

// Log environment (for debugging)
if (ENVIRONMENT === 'development') {
    error_log("ThisAble Config Loaded - BASE_URL: " . BASE_URL . " | Environment: " . ENVIRONMENT);
}
?>