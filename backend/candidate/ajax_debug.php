<?php
// File: backend/candidate/ajax_debug.php
session_start();
header('Content-Type: application/json');

// Log the request info
$log_file = __DIR__ . '/ajax_debug.txt';
$log_data = [
    'time' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'post' => $_POST,
    'get' => $_GET,
    'session' => $_SESSION
];

file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Return a success response
echo json_encode([
    'success' => true,
    'message' => 'Debug data logged successfully',
    'received_data' => [
        'post' => $_POST,
        'session' => $_SESSION
    ]
]);