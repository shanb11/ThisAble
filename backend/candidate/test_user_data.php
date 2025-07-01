<?php
session_start();
require_once '../db.php';

echo "Session seeker_id: " . (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : 'NOT SET') . "<br>";
echo "Database connection: " . ($pdo ? 'OK' : 'FAILED') . "<br>";

if (isset($_SESSION['seeker_id'])) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM job_seekers WHERE seeker_id = ?");
    $stmt->execute([$_SESSION['seeker_id']]);
    $user = $stmt->fetch();
    echo "User found: " . ($user ? $user['first_name'] . ' ' . $user['last_name'] : 'NOT FOUND') . "<br>";
}

// Check if settings tables exist
$tables = ['user_settings', 'accessibility_settings', 'notification_settings', 'privacy_settings', 'job_alert_settings', 'application_settings'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        echo "Table $table: EXISTS<br>";
    } catch (Exception $e) {
        echo "Table $table: MISSING<br>";
    }
}
?>