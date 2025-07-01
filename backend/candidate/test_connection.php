<?php
// Disable error reporting in output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper JSON header
header('Content-Type: application/json');

// Simple response
echo json_encode(['success' => true, 'message' => 'Connection test successful']);
?>