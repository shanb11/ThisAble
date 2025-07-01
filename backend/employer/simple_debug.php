<?php
// Simple debug that works with your existing session structure
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "🚨 SIMPLE DEBUG FOR NOTIFICATIONS\n";
echo "=================================\n\n";

// Test 1: Session
echo "1️⃣ TESTING SESSION:\n";
session_start();
if (isset($_SESSION['employer_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo "✅ Session OK - Employer ID: " . $_SESSION['employer_id'] . "\n";
    echo "✅ Company: " . ($_SESSION['company_name'] ?? 'Unknown') . "\n";
    $employer_id = $_SESSION['employer_id'];
} else {
    echo "❌ Session problem\n";
    echo "Session data: " . print_r($_SESSION, true) . "\n";
    exit();
}

// Test 2: Database
echo "\n2️⃣ TESTING DATABASE:\n";
try {
    require_once('../db.php');
    echo "✅ Database connected\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit();
}

// Test 3: Session files
echo "\n3️⃣ TESTING SESSION FILES:\n";
try {
    require_once('../shared/session_helper.php');
    echo "✅ session_helper.php loaded\n";
} catch (Exception $e) {
    echo "❌ session_helper.php error: " . $e->getMessage() . "\n";
}

try {
    require_once('session_check.php');
    echo "✅ session_check.php loaded\n";
} catch (Exception $e) {
    echo "❌ session_check.php error: " . $e->getMessage() . "\n";
}

// Test 4: Notification tables
echo "\n4️⃣ TESTING NOTIFICATION TABLES:\n";
try {
    $stmt = $conn->prepare("SHOW TABLES LIKE 'notification_types'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ notification_types table exists\n";
        
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
        $countStmt->execute();
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✅ Found $count notification types\n";
        
    } else {
        echo "❌ notification_types table missing\n";
    }
} catch (Exception $e) {
    echo "❌ Table check error: " . $e->getMessage() . "\n";
}

try {
    $stmt = $conn->prepare("SHOW TABLES LIKE 'notifications'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "✅ notifications table exists\n";
        
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ?");
        $countStmt->execute([$employer_id]);
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "✅ Found $count notifications for your employer\n";
        
    } else {
        echo "❌ notifications table missing\n";
    }
} catch (Exception $e) {
    echo "❌ Notifications check error: " . $e->getMessage() . "\n";
}

// Test 5: Simple API test
echo "\n5️⃣ TESTING SIMPLE API QUERY:\n";
try {
    $testSql = "SELECT 1 as test";
    $testStmt = $conn->prepare($testSql);
    $testStmt->execute();
    $result = $testStmt->fetch();
    echo "✅ Basic query works: " . $result['test'] . "\n";
    
    // Test notification query
    $notifSql = "SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer'";
    $notifStmt = $conn->prepare($notifSql);
    $notifStmt->execute();
    $notifCount = $notifStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✅ Total employer notifications in DB: $notifCount\n";
    
} catch (Exception $e) {
    echo "❌ API query error: " . $e->getMessage() . "\n";
}

echo "\n✅ DEBUG COMPLETE!\n";
echo "If all tests pass, the issue might be in the complex API files.\n";
echo "Try the working_notifications.php file next.\n";
?>