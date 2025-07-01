<?php
// Clean up dummy/sample notifications and keep only real ones
session_start();
require_once('../db.php');
require_once('../shared/session_helper.php');

header('Content-Type: text/plain; charset=utf-8');

if (!isEmployerLoggedIn()) {
    echo "Please log in as employer first.\n";
    exit();
}

$employer_id = getCurrentEmployerId();

echo "🧹 CLEANING UP DUMMY NOTIFICATIONS\n";
echo "==================================\n\n";

try {
    // Delete dummy notifications (ones with generic names like "John Doe", "Robert Johnson", etc.)
    $dummyPatterns = [
        '%John Doe%',
        '%Robert Johnson%', 
        '%Maria Santos%',
        '%Anna Lee%',
        '%James Wilson%',
        'Welcome to Notifications',
        'Your notification system is now set up'
    ];
    
    $deletedCount = 0;
    
    foreach ($dummyPatterns as $pattern) {
        $deleteSql = "DELETE FROM notifications 
                     WHERE recipient_type = 'employer' 
                     AND recipient_id = ? 
                     AND message LIKE ?";
        
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->execute([$employer_id, $pattern]);
        
        $deleted = $deleteStmt->rowCount();
        if ($deleted > 0) {
            echo "🗑️  Deleted $deleted notifications matching: $pattern\n";
            $deletedCount += $deleted;
        }
    }
    
    // Also delete by title patterns
    $titlePatterns = [
        'New Applicant',
        'Interview Scheduled', 
        'Welcome to Notifications',
        'Complete Your Profile'
    ];
    
    foreach ($titlePatterns as $pattern) {
        $deleteSql = "DELETE FROM notifications 
                     WHERE recipient_type = 'employer' 
                     AND recipient_id = ? 
                     AND title = ? 
                     AND message LIKE '%John Doe%' OR message LIKE '%Robert Johnson%' OR message LIKE '%system is now set up%'";
        
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->execute([$employer_id, $pattern]);
        
        $deleted = $deleteStmt->rowCount();
        if ($deleted > 0) {
            echo "🗑️  Deleted $deleted dummy notifications with title: $pattern\n";
            $deletedCount += $deleted;
        }
    }
    
    // Show remaining notifications
    $remainingStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ?");
    $remainingStmt->execute([$employer_id]);
    $remaining = $remainingStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "\n📊 CLEANUP SUMMARY:\n";
    echo "   Deleted: $deletedCount dummy notifications\n";
    echo "   Remaining: $remaining notifications\n\n";
    
    if ($remaining > 0) {
        echo "📋 REMAINING NOTIFICATIONS:\n";
        $listStmt = $conn->prepare("SELECT title, message, created_at FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ? ORDER BY created_at DESC");
        $listStmt->execute([$employer_id]);
        $notifications = $listStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($notifications as $notif) {
            $time = date('M j, Y H:i', strtotime($notif['created_at']));
            echo "   - [{$time}] {$notif['title']}: " . substr($notif['message'], 0, 50) . "...\n";
        }
    }
    
    echo "\n✅ CLEANUP COMPLETE!\n";
    echo "Now only real notifications from your actual actions will appear.\n";
    echo "Go test some actions (update applicant status, schedule interview, etc.) to see real notifications!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>