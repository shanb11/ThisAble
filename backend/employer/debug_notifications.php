<?php
// Debug script to identify notification issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🚨 DEBUGGING NOTIFICATIONS SYSTEM</h2>";
echo "<pre>";

echo "1. CHECKING SESSION...\n";
session_start();
if (isset($_SESSION['employer_id'])) {
    echo "✅ Session OK - Employer ID: " . $_SESSION['employer_id'] . "\n";
    $employer_id = $_SESSION['employer_id'];
} else {
    echo "❌ No session found\n";
    echo "Available session data: " . print_r($_SESSION, true) . "\n";
}

echo "\n2. CHECKING DATABASE CONNECTION...\n";
try {
    require_once('../db.php');
    echo "✅ Database connected successfully\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit();
}

echo "\n3. CHECKING REQUIRED FILES...\n";

// Check if session_check.php exists
$sessionCheckFile = __DIR__ . '/session_check.php';
if (file_exists($sessionCheckFile)) {
    echo "✅ session_check.php exists\n";
    
    // Check if session_helper.php exists
    $sessionHelperFile = __DIR__ . '/../shared/session_helper.php';
    if (file_exists($sessionHelperFile)) {
        echo "✅ session_helper.php exists at: $sessionHelperFile\n";
        try {
            require_once('../shared/session_helper.php');
            echo "✅ session_helper.php loaded successfully\n";
        } catch (Exception $e) {
            echo "❌ Error loading session_helper.php: " . $e->getMessage() . "\n";
            echo "This is likely causing the 500 errors!\n";
        }
    } else {
        echo "❌ session_helper.php NOT FOUND at: $sessionHelperFile\n";
        echo "This is causing the 500 errors!\n";
    }
    
    try {
        require_once('session_check.php');
        echo "✅ session_check.php loaded successfully\n";
    } catch (Exception $e) {
        echo "❌ Error loading session_check.php: " . $e->getMessage() . "\n";
        echo "This is the main cause of 500 errors!\n";
    }
} else {
    echo "❌ session_check.php NOT FOUND\n";
    echo "Expected path: $sessionCheckFile\n";
}

// Check if notification_system.php exists and loads
$notificationSystemFile = __DIR__ . '/notification_system.php';
if (file_exists($notificationSystemFile)) {
    echo "✅ notification_system.php exists\n";
    try {
        require_once('notification_system.php');
        echo "✅ notification_system.php loaded successfully\n";
    } catch (Exception $e) {
        echo "❌ Error loading notification_system.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ notification_system.php NOT FOUND\n";
}

echo "\n4. CHECKING NOTIFICATION TYPES...\n";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $stmt->execute();
    $typeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✅ Found $typeCount notification types\n";
} catch (Exception $e) {
    echo "❌ Error checking notification types: " . $e->getMessage() . "\n";
}

echo "\n5. CHECKING NOTIFICATIONS TABLE...\n";
try {
    if (isset($employer_id)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ?");
        $stmt->execute([$employer_id]);
        $notifCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✅ Found $notifCount notifications for employer $employer_id\n";
    } else {
        echo "⚠️ Cannot check notifications - no employer_id\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking notifications: " . $e->getMessage() . "\n";
}

echo "\n6. TESTING SIMPLE API CALL...\n";
try {
    // Test basic notification query
    if (isset($employer_id)) {
        $sql = "SELECT n.notification_id, n.title, n.message 
                FROM notifications n 
                JOIN notification_types nt ON n.type_id = nt.type_id
                WHERE n.recipient_type = 'employer' 
                AND n.recipient_id = ? 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employer_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo "✅ Sample notification query works\n";
            echo "Sample: " . $result['title'] . "\n";
        } else {
            echo "⚠️ No notifications found but query works\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error in API test: " . $e->getMessage() . "\n";
}

echo "\n7. CHECKING FILE PERMISSIONS...\n";
$files = [
    'get_notifications.php',
    'update_notification.php', 
    'mark_all_read.php',
    'notification_system.php'
];

foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        echo "✅ $file exists and is " . (is_readable($filepath) ? 'readable' : 'NOT readable') . "\n";
    } else {
        echo "❌ $file NOT FOUND\n";
    }
}

echo "\n8. SUMMARY:\n";
echo "Current directory: " . __DIR__ . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Error reporting: " . error_reporting() . "\n";

echo "</pre>";
?>