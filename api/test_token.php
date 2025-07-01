<?php
/**
 * TEST SCRIPT - Check if token validation is working
 * Place this in: api/test_token.php
 */

require_once 'config/cors.php';
require_once 'config/response.php';
require_once 'config/database.php';

// Test token extraction
error_log("=== TOKEN TEST SCRIPT ===");

$token = getAuthToken();
error_log("TEST: Extracted token: " . ($token ? substr($token, 0, 20) . "..." : "NULL"));

if (!$token) {
    ApiResponse::error("No token provided", 400);
}

// Test token validation
$user = ApiDatabase::getUserByToken($token);
error_log("TEST: User validation result: " . json_encode($user));

if ($user) {
    ApiResponse::success([
        'token_valid' => true,
        'user' => $user
    ], "Token is valid");
} else {
    ApiResponse::error("Token is invalid", 401);
}
?>