/**
 * ThisAble API Configuration
 * Provides dynamic API paths that work on both localhost and Railway
 * 
 * Usage in your JavaScript files:
 * Replace: window.location.origin + '/ThisAble/backend/...'
 * With: apiPath('/backend/...')
 */

// Get base URL from PHP config (set in HTML head)
const BASE_URL = window.APP_BASE_URL || '/thisable/';

/**
 * Build full API path
 * @param {string} endpoint - The endpoint path (e.g., '/backend/candidate/login.php')
 * @returns {string} - Full URL
 */
function apiPath(endpoint) {
    // Remove leading slash if present
    endpoint = endpoint.replace(/^\/+/, '');
    return window.location.origin + BASE_URL + endpoint;
}

/**
 * Alternative: Build relative path from BASE_URL
 * @param {string} path - The path (e.g., 'frontend/candidate/login.php')
 * @returns {string} - Full path with base URL
 */
function url(path) {
    path = path.replace(/^\/+/, '');
    return BASE_URL + path;
}

// Log for debugging
console.log('API Config loaded:', {
    baseUrl: BASE_URL,
    origin: window.location.origin,
    fullApiPath: apiPath('backend/')
});