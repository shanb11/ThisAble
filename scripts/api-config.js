/**
 * ThisAble API Configuration - FIXED VERSION
 * Provides dynamic API paths that work on both localhost and Railway
 * Auto-detects environment and uses correct base path
 */

// ===== FIX: Detect environment and set correct BASE_URL =====
function getBaseUrl() {
    const hostname = window.location.hostname;
    
    // Check if we're on Railway production
    if (hostname.includes('railway.app') || hostname.includes('up.railway.app')) {
        return '/'; // Railway serves from root
    }
    
    // Check if we're on localhost
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
        return '/ThisAble/'; // XAMPP uses /ThisAble/ directory
    }
    
    // Default fallback
    return '/';
}

const BASE_URL = window.APP_BASE_URL || getBaseUrl();

/**
 * Build full API path
 * @param {string} endpoint - The endpoint path (e.g., 'backend/candidate/login.php')
 * @returns {string} - Full URL
 */
function apiPath(endpoint) {
    // Remove leading slash if present
    endpoint = endpoint.replace(/^\/+/, '');
    
    // Build full URL
    const fullUrl = window.location.origin + BASE_URL + endpoint;
    
    return fullUrl;
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
    hostname: window.location.hostname,
    baseUrl: BASE_URL,
    origin: window.location.origin,
    sampleApiPath: apiPath('backend/candidate/login.php')
});