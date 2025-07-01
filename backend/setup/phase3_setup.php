<?php
/**
 * Phase 3 Notification System Setup Script
 * Run this once to set up all Phase 3 features
 */

require_once '../db.php';

echo "🚀 ThisAble Notification System - Phase 3 Setup\n";
echo "================================================\n\n";

$steps_completed = 0;
$total_steps = 8;

try {
    // Step 1: Create logs directory
    echo "Step 1/{$total_steps}: Creating logs directory...\n";
    $logs_dir = dirname(__DIR__, 2) . '/logs';
    if (!file_exists($logs_dir)) {
        mkdir($logs_dir, 0755, true);
        echo "✅ Logs directory created: {$logs_dir}\n";
    } else {
        echo "✅ Logs directory already exists\n";
    }
    $steps_completed++;
    
    // Step 2: Create notification cron log file
    echo "\nStep 2/{$total_steps}: Setting up cron log file...\n";
    $cron_log = $logs_dir . '/notification_cron.log';
    if (!file_exists($cron_log)) {
        file_put_contents($cron_log, "# ThisAble Notification Cron Log\n# Started: " . date('Y-m-d H:i:s') . "\n\n");
        echo "✅ Cron log file created: {$cron_log}\n";
    } else {
        echo "✅ Cron log file already exists\n";
    }
    $steps_completed++;
    
    // Step 3: Insert sample notification data
    echo "\nStep 3/{$total_steps}: Inserting sample notifications...\n";
    insertSampleNotifications();
    echo "✅ Sample notifications inserted\n";
    $steps_completed++;
    
    // Step 4: Update database schema for Phase 3
    echo "\nStep 4/{$total_steps}: Updating database schema...\n";
    updateDatabaseSchema();
    echo "✅ Database schema updated\n";
    $steps_completed++;
    
    // Step 5: Create notification preferences for existing users
    echo "\nStep 5/{$total_steps}: Creating notification preferences...\n";
    createNotificationPreferences();
    echo "✅ Notification preferences created\n";
    $steps_completed++;
    
    // Step 6: Set up default notification types
    echo "\nStep 6/{$total_steps}: Verifying notification types...\n";
    verifyNotificationTypes();
    echo "✅ Notification types verified\n";
    $steps_completed++;
    
    // Step 7: Test notification system
    echo "\nStep 7/{$total_steps}: Testing notification system...\n";
    testNotificationSystem();
    echo "✅ Notification system tested\n";
    $steps_completed++;
    
    // Step 8: Display integration instructions
    echo "\nStep 8/{$total_steps}: Setup complete!\n";
    displayIntegrationInstructions();
    $steps_completed++;
    
    echo "\n🎉 Phase 3 Setup Complete! ({$steps_completed}/{$total_steps} steps)\n";
    echo "================================================\n";
    
} catch (Exception $e) {
    echo "\n❌ Setup failed at step " . ($steps_completed + 1) . "/{$total_steps}\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please check the error and run the setup again.\n";
    exit(1);
}

/**
 * Insert sample notifications for testing
 */
function insertSampleNotifications() {
    global $conn;
    
    // Get first active job seeker
    $stmt = $conn->prepare("SELECT seeker_id FROM job_seekers WHERE setup_complete = 1 LIMIT 1");
    $stmt->execute();
    $seeker_id = $stmt->fetchColumn();
    
    if (!$seeker_id) {
        echo "⚠️  No active job seekers found. Sample notifications skipped.\n";
        return;
    }
    
    $sample_notifications = [
        [
            'type_id' => 5, // system_update
            'title' => 'Welcome to Enhanced Notifications!',
            'message' => 'Phase 3 of the notification system is now active. You\'ll receive more relevant and timely updates about your job search.'
        ],
        [
            'type_id' => 12, // profile_completion
            'title' => 'Complete Your Profile',
            'message' => 'Your profile is looking good! Add more details to increase your visibility to employers.'
        ]
    ];
    
    $count = 0;
    foreach ($sample_notifications as $notification) {
        $stmt = $conn->prepare("
            INSERT INTO notifications (recipient_type, recipient_id, type_id, title, message, is_read, created_at)
            VALUES ('candidate', ?, ?, ?, ?, 0, NOW())
        ");
        
        if ($stmt->execute([$seeker_id, $notification['type_id'], $notification['title'], $notification['message']])) {
            $count++;
        }
    }
    
    echo "   → {$count} sample notifications created for seeker ID {$seeker_id}\n";
}

/**
 * Update database schema for Phase 3 features
 */
function updateDatabaseSchema() {
    global $conn;
    
    $updates = [
        // Add reminder_sent field to interviews table if not exists
        "ALTER TABLE interviews ADD COLUMN IF NOT EXISTS reminder_sent TINYINT(1) DEFAULT 0",
        
        // Add indexes for better performance
        "CREATE INDEX IF NOT EXISTS idx_notifications_recipient_read ON notifications(recipient_type, recipient_id, is_read)",
        "CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_notifications_type_id ON notifications(type_id)",
        
        // Add indexes on job_posts for deadline checking
        "CREATE INDEX IF NOT EXISTS idx_job_posts_deadline ON job_posts(application_deadline, job_status)",
        "CREATE INDEX IF NOT EXISTS idx_job_posts_posted_at ON job_posts(posted_at, job_status)"
    ];
    
    $updated_count = 0;
    foreach ($updates as $sql) {
        try {
            $conn->exec($sql);
            $updated_count++;
        } catch (PDOException $e) {
            // Some updates might fail if already exists - that's okay
            if (!strpos($e->getMessage(), 'already exists') && !strpos($e->getMessage(), 'Duplicate')) {
                throw $e;
            }
        }
    }
    
    echo "   → {$updated_count} database updates applied\n";
}

/**
 * Create notification preferences for existing users
 */
function createNotificationPreferences() {
    global $conn;
    
    // Get all seekers without notification settings
    $stmt = $conn->prepare("
        SELECT js.seeker_id 
        FROM job_seekers js
        LEFT JOIN notification_settings ns ON js.seeker_id = ns.seeker_id
        WHERE ns.seeker_id IS NULL
    ");
    $stmt->execute();
    $seekers_without_settings = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $created_count = 0;
    foreach ($seekers_without_settings as $seeker_id) {
        $stmt = $conn->prepare("
            INSERT INTO notification_settings 
            (seeker_id, email_notifications, push_notifications, job_alerts, 
             application_updates, message_notifications, marketing_notifications)
            VALUES (?, 1, 1, 1, 1, 1, 0)
        ");
        
        if ($stmt->execute([$seeker_id])) {
            $created_count++;
        }
    }
    
    echo "   → {$created_count} notification preference records created\n";
}

/**
 * Verify all notification types exist
 */
function verifyNotificationTypes() {
    global $conn;
    
    $required_types = [
        'new_application' => 'New job application received',
        'application_status' => 'Application status changed', 
        'interview_scheduled' => 'Interview scheduled or updated',
        'interview_reminder' => 'Upcoming interview reminder',
        'interview_feedback' => 'Interview feedback required',
        'job_posted' => 'Job successfully posted',
        'deadline_reminder' => 'Application deadline reminder',
        'job_expiring' => 'Job posting expiring soon',
        'job_performance' => 'Job performance update',
        'system_update' => 'System notifications',
        'profile_completion' => 'Profile completion reminder',
        'subscription_renewal' => 'Subscription renewal reminder'
    ];
    
    $created_count = 0;
    foreach ($required_types as $type_name => $description) {
        // Check if exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM notification_types WHERE type_name = ?");
        $stmt->execute([$type_name]);
        
        if ($stmt->fetchColumn() == 0) {
            // Create missing type
            $icon = getIconForType($type_name);
            $color = getColorForType($type_name);
            
            $stmt = $conn->prepare("
                INSERT INTO notification_types (type_name, type_description, icon_class, color_class)
                VALUES (?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$type_name, $description, $icon, $color])) {
                $created_count++;
            }
        }
    }
    
    echo "   → {$created_count} notification types created\n";
}

/**
 * Test the notification system
 */
function testNotificationSystem() {
    global $conn;
    
    // Test 1: Check if NotificationSystem class can be loaded
    require_once '../shared/notification_system.php';
    $notificationSystem = getNotificationSystem();
    
    if (!$notificationSystem) {
        throw new Exception("Failed to load NotificationSystem");
    }
    
    // Test 2: Check if JobRecommendationSystem can be loaded
    require_once '../shared/job_recommendations.php';
    $jobRecommendationSystem = getJobRecommendationSystem();
    
    if (!$jobRecommendationSystem) {
        throw new Exception("Failed to load JobRecommendationSystem");
    }
    
    // Test 3: Check database connectivity
    $stmt = $conn->prepare("SELECT COUNT(*) FROM notifications");
    $stmt->execute();
    $notification_count = $stmt->fetchColumn();
    
    echo "   → Database connection: ✅\n";
    echo "   → NotificationSystem: ✅\n";
    echo "   → JobRecommendationSystem: ✅\n";
    echo "   → Total notifications in database: {$notification_count}\n";
}

/**
 * Display integration instructions
 */
function displayIntegrationInstructions() {
    echo "\n📋 INTEGRATION INSTRUCTIONS\n";
    echo "===========================\n\n";
    
    echo "1. 🔄 SET UP CRON JOB:\n";
    echo "   Add this line to your crontab (run every 30 minutes):\n";
    echo "   */30 * * * * /usr/bin/php " . dirname(__DIR__) . "/cron/notification_scheduler.php\n\n";
    
    echo "2. 🔗 INTEGRATE NOTIFICATION HOOKS:\n";
    echo "   Add these lines to your existing code:\n\n";
    
    echo "   // When user completes setup:\n";
    echo "   require_once 'backend/shared/notification_hooks.php';\n";
    echo "   triggerNotificationHook('profile_setup_complete', \$seeker_id);\n\n";
    
    echo "   // When user submits application:\n";
    echo "   triggerNotificationHook('job_application_submitted', \$application_id, \$job_id, \$seeker_id);\n\n";
    
    echo "   // When employer posts job:\n";
    echo "   triggerNotificationHook('job_posted', \$job_id, \$employer_id);\n\n";
    
    echo "3. 📱 ADD REAL-TIME UPDATES:\n";
    echo "   Include this JavaScript in your pages:\n";
    echo "   // Auto-refresh notification badge every 2 minutes\n";
    echo "   setInterval(updateNotificationBadge, 120000);\n\n";
    
    echo "4. 🎨 ADD PHASE 3 CSS:\n";
    echo "   Include the phase3 CSS file in your notifications page:\n";
    echo "   <link rel=\"stylesheet\" href=\"styles/candidate/notifications-phase3.css\">\n\n";
    
    echo "5. ✅ VERIFICATION:\n";
    echo "   - Check notification badge appears in sidebar\n";
    echo "   - Test mark as read functionality\n";
    echo "   - Verify bulk actions work\n";
    echo "   - Check notification preferences modal\n";
    echo "   - Test cron job manually: php backend/cron/notification_scheduler.php\n\n";
}

/**
 * Helper functions
 */
function getIconForType($type_name) {
    $icons = [
        'new_application' => 'fas fa-user-plus',
        'application_status' => 'fas fa-clipboard-check',
        'interview_scheduled' => 'fas fa-calendar-alt',
        'interview_reminder' => 'fas fa-bell',
        'interview_feedback' => 'fas fa-clipboard-check',
        'job_posted' => 'fas fa-briefcase',
        'deadline_reminder' => 'fas fa-clock',
        'job_expiring' => 'fas fa-clock',
        'job_performance' => 'fas fa-chart-line',
        'system_update' => 'fas fa-cog',
        'profile_completion' => 'fas fa-user-edit',
        'subscription_renewal' => 'fas fa-credit-card'
    ];
    
    return $icons[$type_name] ?? 'fas fa-bell';
}

function getColorForType($type_name) {
    $colors = [
        'new_application' => 'green',
        'application_status' => 'orange',
        'interview_scheduled' => 'blue',
        'interview_reminder' => 'yellow',
        'interview_feedback' => 'orange',
        'job_posted' => 'purple',
        'deadline_reminder' => 'red',
        'job_expiring' => 'red',
        'job_performance' => 'green',
        'system_update' => 'gray',
        'profile_completion' => 'orange',
        'subscription_renewal' => 'blue'
    ];
    
    return $colors[$type_name] ?? 'blue';
}

echo "\n🎯 Ready to test your enhanced notification system!\n";
?>