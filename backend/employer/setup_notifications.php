<?php
session_start();
require_once('../db.php');

// Set content type for better display
header('Content-Type: text/plain; charset=utf-8');

echo "🚀 SETTING UP NOTIFICATIONS SYSTEM\n";
echo "==================================\n\n";

try {
    // Step 1: Populate notification types
    echo "📋 Step 1: Creating notification types...\n";
    
    $notification_types = [
        ['new_application', 'New job application received', 'fas fa-user-plus', 'green'],
        ['interview_scheduled', 'Interview scheduled or updated', 'fas fa-calendar-alt', 'blue'],
        ['interview_reminder', 'Upcoming interview reminder', 'fas fa-bell', 'yellow'],
        ['interview_feedback', 'Interview feedback required', 'fas fa-clipboard-check', 'orange'],
        ['application_status', 'Application status changed', 'fas fa-clipboard-check', 'orange'],
        ['job_posted', 'Job successfully posted', 'fas fa-briefcase', 'purple'],
        ['job_expiring', 'Job posting expiring soon', 'fas fa-clock', 'red'],
        ['job_performance', 'Job performance update', 'fas fa-chart-line', 'green'],
        ['system_update', 'System notifications', 'fas fa-cog', 'gray'],
        ['subscription_renewal', 'Subscription renewal reminder', 'fas fa-credit-card', 'blue'],
        ['profile_completion', 'Profile completion reminder', 'fas fa-user-edit', 'orange']
    ];
    
    $created_types = 0;
    $existing_types = 0;
    
    foreach ($notification_types as $type) {
        // Check if type already exists
        $checkSql = "SELECT type_id FROM notification_types WHERE type_name = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$type[0]]);
        
        if (!$checkStmt->fetch()) {
            // Insert new type
            $insertSql = "INSERT INTO notification_types (type_name, type_description, icon_class, color_class) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute($type);
            echo "✅ Created: " . $type[0] . "\n";
            $created_types++;
        } else {
            echo "⚪ Exists: " . $type[0] . "\n";
            $existing_types++;
        }
    }
    
    echo "\n📊 Notification Types Summary:\n";
    echo "   Created: $created_types\n";
    echo "   Already existed: $existing_types\n\n";
    
    // Step 2: Check for existing employers
    echo "👥 Step 2: Checking for employers...\n";
    
    $employerSql = "SELECT employer_id, company_name FROM employers LIMIT 5";
    $employerStmt = $conn->prepare($employerSql);
    $employerStmt->execute();
    $employers = $employerStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($employers)) {
        echo "⚠️  No employers found in database. Please create employer accounts first.\n";
        echo "   Sample notifications will be skipped.\n\n";
    } else {
        echo "Found " . count($employers) . " employer(s):\n";
        foreach ($employers as $emp) {
            echo "   - ID: {$emp['employer_id']}, Company: {$emp['company_name']}\n";
        }
        echo "\n";
        
        // Step 3: Create sample notifications for testing
        echo "🧪 Step 3: Creating sample notifications for testing...\n";
        
        // Get the first employer for sample notifications
        $sample_employer_id = $employers[0]['employer_id'];
        $company_name = $employers[0]['company_name'];
        
        $sample_notifications = [
            ['new_application', 'New Applicant', 'John Doe has applied for the Senior Web Developer position.'],
            ['new_application', 'New Applicant', 'Maria Santos has applied for the UI/UX Designer position.'],
            ['interview_scheduled', 'Interview Scheduled', 'Interview scheduled with Robert Johnson for Senior Web Developer tomorrow at 10:00 AM.'],
            ['job_expiring', 'Job Posting Expiring', 'Your job posting for "Content Writer" will expire in 3 days. Consider extending it to attract more applicants.'],
            ['system_update', 'Welcome to Notifications', 'Your notification system is now set up and ready to use!'],
            ['job_performance', 'Job Performance', 'Your "Senior Web Developer" posting is performing well with 5 applicants so far.'],
            ['profile_completion', 'Complete Your Profile', 'Complete your company profile to increase visibility to potential applicants.']
        ];
        
        $created_notifications = 0;
        
        foreach ($sample_notifications as $notif) {
            // Get type_id
            $typeStmt = $conn->prepare("SELECT type_id FROM notification_types WHERE type_name = ?");
            $typeStmt->execute([$notif[0]]);
            $typeResult = $typeStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($typeResult) {
                // Check if similar notification already exists
                $existingStmt = $conn->prepare("SELECT notification_id FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ? AND title = ?");
                $existingStmt->execute([$sample_employer_id, $notif[1]]);
                
                if (!$existingStmt->fetch()) {
                    $insertNotifSql = "INSERT INTO notifications (recipient_type, recipient_id, type_id, title, message, created_at) VALUES ('employer', ?, ?, ?, ?, NOW())";
                    $insertNotifStmt = $conn->prepare($insertNotifSql);
                    $insertNotifStmt->execute([$sample_employer_id, $typeResult['type_id'], $notif[1], $notif[2]]);
                    echo "✅ Created notification: " . $notif[1] . "\n";
                    $created_notifications++;
                } else {
                    echo "⚪ Notification exists: " . $notif[1] . "\n";
                }
            }
        }
        
        echo "\n📧 Sample Notifications Summary:\n";
        echo "   Created: $created_notifications sample notifications for {$company_name}\n\n";
    }
    
    // Step 4: Verify setup
    echo "🔍 Step 4: Verifying setup...\n";
    
    // Count notification types
    $typeCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $typeCountStmt->execute();
    $typeCount = $typeCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total notifications
    $notifCountStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE recipient_type = 'employer'");
    $notifCountStmt->execute();
    $notifCount = $notifCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "✅ Database verification:\n";
    echo "   Notification types: $typeCount\n";
    echo "   Total employer notifications: $notifCount\n\n";
    
    echo "🎉 SETUP COMPLETE!\n";
    echo "==================\n\n";
    echo "✅ Your notification system is ready!\n";
    echo "✅ You can now test the API endpoints:\n\n";
    echo "   📡 GET  backend/employer/get_notifications.php\n";
    echo "   ✏️  POST backend/employer/update_notification.php\n";
    echo "   📝 POST backend/employer/mark_all_read.php\n\n";
    echo "💡 Next steps:\n";
    echo "   1. Test the get_notifications.php endpoint\n";
    echo "   2. Update your frontend JavaScript to use real API\n";
    echo "   3. Add notification generation hooks to your existing code\n\n";
    
    if (!empty($employers)) {
        echo "🧪 For testing, log in as employer ID: {$employers[0]['employer_id']} ({$employers[0]['company_name']})\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}

echo "🏁 Setup script finished.\n";
?>