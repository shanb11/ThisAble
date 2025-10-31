<?php
/**
 * ThisAble - Dynamic Configuration
 * Auto-detects localhost vs Railway and sets paths accordingly
 */

// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Check if running on Railway
if (strpos($hostname, 'railway.app') !== false || 
    strpos($hostname, 'up.railway.app') !== false) {
    // Production (Railway)
    define('BASE_URL', '/');
    define('ENVIRONMENT', 'production');
} else {
    // Development (localhost/XAMPP)
    define('BASE_URL', '/thisable/');
    define('ENVIRONMENT', 'development');
}

/**
 * Helper function for URLs
 */
function url($path) {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Output JavaScript configuration
 * Call this in HTML <head> to make BASE_URL available in JavaScript
 */
function output_js_config() {
    echo '<script>window.APP_BASE_URL = "' . BASE_URL . '";</script>' . "\n";
}
?>