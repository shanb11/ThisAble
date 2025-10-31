<?php
/**
 * ThisAble - Dynamic Base URL Configuration
 * 
 * Automatically detects whether running on:
 * - Railway Production: https://thisable-production.up.railway.app/
 * - Localhost XAMPP: http://localhost/thisable/
 * 
 * Usage in any PHP file:
 * require_once 'config/config.php';
 * echo BASE_URL; // Will be '/' on Railway, '/thisable/' on localhost
 */

// Auto-detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? '';

// Check if running on Railway production
if (strpos($hostname, 'railway.app') !== false || 
    strpos($hostname, 'up.railway.app') !== false) {
    // Production environment (Railway)
    define('BASE_URL', '/');
    define('ENVIRONMENT', 'production');
} else {
    // Local development environment (XAMPP)
    define('BASE_URL', '/thisable/');
    define('ENVIRONMENT', 'development');
}

// Optional: Define API base path for JavaScript
// This will be output in your HTML head section
define('API_BASE_PATH', BASE_URL);

/**
 * Helper function to generate full URL
 * Usage: url('frontend/candidate/login.php')
 * Returns: '/thisable/frontend/candidate/login.php' (localhost)
 *       or '/frontend/candidate/login.php' (Railway)
 */
function url($path) {
    $path = ltrim($path, '/'); // Remove leading slash if present
    return BASE_URL . $path;
}

/**
 * Output JavaScript configuration
 * Include this in your HTML <head> section
 */
function output_js_config() {
    echo '<script>const APP_BASE_URL = "' . BASE_URL . '";</script>';
}
?>