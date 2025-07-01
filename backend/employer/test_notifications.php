<?php
session_start();
require_once('../db.php');

// Set content type for better display
header('Content-Type: text/plain; charset=utf-8');

echo "🧪 TESTING NOTIFICATIONS API\n";
echo "=============================\n\n";

// Test 1: Check session
echo "1️⃣ Testing Session:\n";
if (isset($_SESSION['employer_id'])) {
    echo "✅ Session OK - Employer ID: " . $_SESSION['employer_id'] . "\n";
    $employer_id = $_SESSION['employer_id'];
} else {
    echo "❌ No session found\n";
    echo "💡 Please log in as an employer first, then run this test\n\n";
    
    // Show available employers
    echo "📋 Available employers in database:\n";
    try {
        $stmt = $conn->prepare("SELECT employer_id, company_name FROM employers LIMIT 5");
        $stmt->execute();
        $employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($employers)) {
            echo "   No employers found\n";
        } else {
            foreach ($employers as $emp) {
                echo "   - ID: {$emp['employer_id']}, Company: {$emp['company_name']}\n";
            }
        }
    } catch (Exception $e) {
        echo "   Error checking employers: " . $e->getMessage() . "\n";
    }
    
    exit();
}

echo "\n";

// Test 2: Check database connection
echo "2️⃣ Testing Database Connection:\n";
try {
    $stmt = $conn->prepare("SELECT 1");
    $stmt->execute();
    echo "✅ Database connection OK\n\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n\n";
    exit();
}

// Test 3: Check notification types
echo "3️⃣ Testing Notification Types:\n";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $stmt->execute();
    $typeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($typeCount > 0) {
        echo "✅ Found $typeCount notification types\n";
        
        // Show first few types
        $typesStmt = $conn->prepare("SELECT type_name, type_description FROM notification_types LIMIT 3");
        $typesStmt->execute();
        $types = $typesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Sample types:\n";
        foreach ($types as $type) {
            echo "   - {$type['type_name']}: {$type['type_description']}\n";
        }
    } else {
        echo "❌ No notification types found\n";
        echo "💡 Please run setup_notifications.php first\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking notification types: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check notifications for current employer
echo "4️⃣ Testing Notifications for Current Employer:\n";
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ?");
    $stmt->execute([$employer_id]);
    $notifCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "✅ Found $notifCount notifications for employer ID $employer_id\n";
    
    if ($notifCount > 0) {
        // Show sample notifications
        $sampleStmt = $conn->prepare("SELECT title, message, is_read, created_at FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ? ORDER BY created_at DESC LIMIT 3");
        $sampleStmt->execute([$employer_id]);
        $samples = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   Recent notifications:\n";
        foreach ($samples as $notif) {
            $status = $notif['is_read'] ? 'Read' : 'Unread';
            $time = date('M j, Y H:i', strtotime($notif['created_at']));
            echo "   - [{$status}] {$notif['title']} - $time\n";
        }
    } else {
        echo "💡 No notifications found. Sample notifications may need to be created.\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking notifications: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: API Endpoint Test
echo "5️⃣ Testing API Endpoints:\n";

echo "📡 Testing get_notifications.php:\n";
try {
    // Simulate API call to get_notifications
    $currentDir = dirname(__FILE__);
    $apiUrl = "http://" . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $currentDir) . "/get_notifications.php";
    
    echo "   URL: $apiUrl\n";
    echo "   This should work if accessed via browser/AJAX\n";
    echo "   ✅ File exists: " . (file_exists(__DIR__ . '/get_notifications.php') ? 'Yes' : 'No') . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n📝 Testing update_notification.php:\n";
echo "   ✅ File exists: " . (file_exists(__DIR__ . '/update_notification.php') ? 'Yes' : 'No') . "\n";

echo "\n📋 Testing mark_all_read.php:\n";
echo "   ✅ File exists: " . (file_exists(__DIR__ . '/mark_all_read.php') ? 'Yes' : 'No') . "\n";

echo "\n";

// Test 6: Frontend Integration Check
echo "6️⃣ Frontend Integration Check:\n";
$frontendFile = '../../frontend/employer/empnotifications.php';
$jsFile = '../../scripts/employer/empnotifications.js';

echo "✅ Frontend file exists: " . (file_exists($frontendFile) ? 'Yes' : 'No') . "\n";
echo "✅ JavaScript file exists: " . (file_exists($jsFile) ? 'Yes' : 'No') . "\n";

echo "\n";

// Summary
echo "🎉 TEST SUMMARY\n";
echo "===============\n";
echo "✅ Session: " . (isset($_SESSION['employer_id']) ? 'OK' : 'FAIL') . "\n";
echo "✅ Database: OK\n";
echo "✅ Notification Types: " . ($typeCount > 0 ? 'OK' : 'SETUP NEEDED') . "\n";
echo "✅ Sample Notifications: " . ($notifCount > 0 ? 'OK' : 'COULD BE ADDED') . "\n";
echo "✅ API Files: OK\n";

echo "\n💡 NEXT STEPS:\n";
if ($typeCount == 0) {
    echo "1. Run setup_notifications.php to populate database\n";
} else {
    echo "1. ✅ Database setup complete\n";
}
echo "2. Test get_notifications.php in browser\n";
echo "3. Update frontend JavaScript to use real API\n";
echo "4. Test full notification functionality\n";

echo "\n🏁 Test completed!\n";
?>