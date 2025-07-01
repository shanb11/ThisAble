<?php
/**
 * Session Debug Script
 * Create this file as backend/debug_session.php
 * Access it at: http://localhost/ThisAble/backend/debug_session.php
 */

session_start();

header('Content-Type: application/json');

echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'session_status' => session_status(),
    'php_session_started' => (session_status() === PHP_SESSION_ACTIVE),
    'cookies' => $_COOKIE,
    'current_time' => date('Y-m-d H:i:s'),
    'session_file_path' => session_save_path(),
    'session_name' => session_name()
], JSON_PRETTY_PRINT);
?>