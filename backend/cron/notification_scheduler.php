<?php
/**
 * Automated Notification Scheduler
 * Run this file via cron job every 30 minutes to send automated notifications
 * 
 * Cron command example: 
 * 0,30 * * * * /usr/bin/php /path/to/thisable/backend/cron/notification_scheduler.php
 * 
 * Alternative formats:
 * - Every 30 minutes: 0,30 * * * *
 * - Every 30 minutes: star-slash-30 * * * * (replace star-slash with */30)
 */

// Set execution time limit for cron job
set_time_limit(300); // 5 minutes max

// Include required files
require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/shared/notification_system.php';
require_once dirname(__DIR__) . '/shared/job_recommendations.php';

// Log file for cron execution
$log_file = dirname(__DIR__) . '/../logs/notification_cron.log';

function logMessage($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$message}" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry; // Also output for manual testing
}

logMessage("=== Notification Scheduler Started ===");

try {
    $notificationSystem = getNotificationSystem();
    $jobRecommendationSystem = getJobRecommendationSystem();
    
    // Task 1: Check and send profile completion reminders
    logMessage("Checking profile completion reminders...");
    $profile_result = $notificationSystem->checkProfileCompletionReminders();
    logMessage("Profile completion reminders: " . ($profile_result ? "Success" : "Failed"));
    
    // Task 2: Send application deadline reminders
    logMessage("Checking application deadline reminders...");
    $deadline_count = checkApplicationDeadlines($notificationSystem);
    logMessage("Application deadline reminders sent: {$deadline_count}");
    
    // Task 3: Process job recommendations (every 2 hours)
    $current_hour = date('H');
    if ($current_hour % 2 == 0) { // Every 2 hours
        logMessage("Processing job recommendations...");
        $recommendation_count = $jobRecommendationSystem->findRecommendationsForAllCandidates();
        logMessage("Job recommendations sent: {$recommendation_count}");
    }
    
    // Task 4: Send interview reminders (24 hours and 2 hours before)
    logMessage("Checking interview reminders...");
    $interview_reminders = sendInterviewReminders($notificationSystem);
    logMessage("Interview reminders sent: {$interview_reminders}");
    
    // Task 5: Clean up old notifications (daily at midnight)
    if (date('H:i') === '00:00') {
        logMessage("Cleaning up old notifications...");
        $cleanup_count = cleanupOldNotifications();
        logMessage("Old notifications cleaned: {$cleanup_count}");
    }
    
    // Task 6: Check for newly posted jobs and notify relevant candidates
    logMessage("Checking for new job postings...");
    $new_job_notifications = processNewJobPostings($jobRecommendationSystem);
    logMessage("New job notifications sent: {$new_job_notifications}");
    
    logMessage("=== Notification Scheduler Completed Successfully ===");
    
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

/**
 * Check application deadlines and send reminders
 */
function checkApplicationDeadlines($notificationSystem) {
    global $conn;
    
    try {
        // Find jobs with deadlines in 3 days that have saved candidates who haven't applied
        $stmt = $conn->prepare("
            SELECT DISTINCT jp.job_id, jp.job_title, jp.application_deadline, e.company_name
            FROM job_posts jp
            JOIN employers e ON jp.employer_id = e.employer_id
            WHERE jp.job_status = 'active'
            AND jp.application_deadline IS NOT NULL
            AND jp.application_deadline > NOW()
            AND jp.application_deadline <= DATE_ADD(NOW(), INTERVAL 3 DAY)
            AND jp.application_deadline >= DATE_ADD(NOW(), INTERVAL 2 DAY)
        ");
        $stmt->execute();
        $jobs_with_deadlines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total_sent = 0;
        foreach ($jobs_with_deadlines as $job) {
            $days_remaining = ceil((strtotime($job['application_deadline']) - time()) / (24 * 60 * 60));
            $sent_count = $notificationSystem->notifyApplicationDeadline($job['job_id'], $days_remaining);
            $total_sent += $sent_count;
        }
        
        return $total_sent;
        
    } catch (Exception $e) {
        logMessage("Error checking application deadlines: " . $e->getMessage());
        return 0;
    }
}

/**
 * Send interview reminders
 */
function sendInterviewReminders($notificationSystem) {
    global $conn;
    
    try {
        // Find interviews in the next 24 hours or 2 hours
        $stmt = $conn->prepare("
            SELECT i.*, ja.seeker_id, jp.job_title, e.company_name,
                   TIMESTAMPDIFF(HOUR, NOW(), CONCAT(i.scheduled_date, ' ', i.scheduled_time)) as hours_until
            FROM interviews i
            JOIN job_applications ja ON i.application_id = ja.application_id
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN employers e ON jp.employer_id = e.employer_id
            WHERE i.interview_status = 'scheduled'
            AND CONCAT(i.scheduled_date, ' ', i.scheduled_time) > NOW()
            AND CONCAT(i.scheduled_date, ' ', i.scheduled_time) <= DATE_ADD(NOW(), INTERVAL 24 HOUR)
            AND i.reminder_sent = 0
        ");
        $stmt->execute();
        $upcoming_interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $sent_count = 0;
        foreach ($upcoming_interviews as $interview) {
            $hours_until = $interview['hours_until'];
            
            // Send reminder for interviews in 24 hours or 2 hours
            if ($hours_until <= 24 && $hours_until >= 23) {
                // 24-hour reminder
                $type_id = getNotificationTypeId('interview_reminder');
                $title = "Interview Reminder - 24 Hours";
                $date = date('M j, Y', strtotime($interview['scheduled_date']));
                $time = date('g:i A', strtotime($interview['scheduled_time']));
                $message = "Reminder: You have an interview for {$interview['job_title']} at {$interview['company_name']} tomorrow at {$time}.";
                
                $result = $notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $interview['seeker_id'],
                    'type_id' => $type_id,
                    'title' => $title,
                    'message' => $message,
                    'related_application_id' => $interview['application_id'],
                    'related_interview_id' => $interview['interview_id']
                ]);
                
                if ($result) {
                    $sent_count++;
                    // Mark reminder as sent
                    $stmt = $conn->prepare("UPDATE interviews SET reminder_sent = 1 WHERE interview_id = ?");
                    $stmt->execute([$interview['interview_id']]);
                }
                
            } elseif ($hours_until <= 2 && $hours_until >= 1) {
                // 2-hour reminder
                $type_id = getNotificationTypeId('interview_reminder');
                $title = "Interview Starting Soon!";
                $time = date('g:i A', strtotime($interview['scheduled_time']));
                $message = "Your interview for {$interview['job_title']} at {$interview['company_name']} starts in about 2 hours at {$time}. Good luck!";
                
                $result = $notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $interview['seeker_id'],
                    'type_id' => $type_id,
                    'title' => $title,
                    'message' => $message,
                    'related_application_id' => $interview['application_id'],
                    'related_interview_id' => $interview['interview_id']
                ]);
                
                if ($result) $sent_count++;
            }
        }
        
        return $sent_count;
        
    } catch (Exception $e) {
        logMessage("Error sending interview reminders: " . $e->getMessage());
        return 0;
    }
}

/**
 * Process newly posted jobs and notify relevant candidates
 */
function processNewJobPostings($jobRecommendationSystem) {
    global $conn;
    
    try {
        // Find jobs posted in the last 30 minutes that haven't been processed
        $stmt = $conn->prepare("
            SELECT job_id FROM job_posts 
            WHERE job_status = 'active' 
            AND posted_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            AND posted_at IS NOT NULL
        ");
        $stmt->execute();
        $new_jobs = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $total_notifications = 0;
        foreach ($new_jobs as $job_id) {
            $notification_count = $jobRecommendationSystem->processNewJobPosting($job_id);
            if ($notification_count) {
                $total_notifications += $notification_count;
            }
        }
        
        return $total_notifications;
        
    } catch (Exception $e) {
        logMessage("Error processing new job postings: " . $e->getMessage());
        return 0;
    }
}

/**
 * Clean up old notifications
 */
function cleanupOldNotifications() {
    global $conn;
    
    try {
        // Delete read notifications older than 60 days
        $stmt = $conn->prepare("
            DELETE FROM notifications 
            WHERE is_read = 1 
            AND created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)
        ");
        $stmt->execute();
        $deleted_count = $stmt->rowCount();
        
        return $deleted_count;
        
    } catch (Exception $e) {
        logMessage("Error cleaning up old notifications: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get notification type ID by name
 */
function getNotificationTypeId($type_name) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT type_id FROM notification_types WHERE type_name = ?");
        $stmt->execute([$type_name]);
        $result = $stmt->fetchColumn();
        return $result ?: 1; // Default to first type if not found
    } catch (Exception $e) {
        return 1; // Default fallback
    }
}

logMessage("=== Notification Scheduler Ended ===");
?>