<?php
/**
 * Notification System Helper Functions
 * Used to create notifications when events occur in the system
 */

require_once('../db.php');

/**
 * Create a new notification
 * 
 * @param string $recipient_type 'employer' or 'candidate'
 * @param int $recipient_id The ID of the recipient
 * @param string $type_name The notification type (from notification_types table)
 * @param string $title The notification title
 * @param string $message The notification message
 * @param int|null $related_job_id Optional related job ID
 * @param int|null $related_application_id Optional related application ID
 * @param int|null $related_interview_id Optional related interview ID
 * @return bool Success status
 */
function create_notification($recipient_type, $recipient_id, $type_name, $title, $message, $related_job_id = null, $related_application_id = null, $related_interview_id = null) {
    global $conn;
    
    try {
        // Get the type_id for the notification type
        $typeSql = "SELECT type_id FROM notification_types WHERE type_name = ?";
        $typeStmt = $conn->prepare($typeSql);
        $typeStmt->execute([$type_name]);
        $typeResult = $typeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$typeResult) {
            error_log("Notification type not found: $type_name");
            return false;
        }
        
        $type_id = $typeResult['type_id'];
        
        // Insert the notification
        $sql = "INSERT INTO notifications 
                (recipient_type, recipient_id, type_id, title, message, related_job_id, related_application_id, related_interview_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $recipient_type,
            $recipient_id,
            $type_id,
            $title,
            $message,
            $related_job_id,
            $related_application_id,
            $related_interview_id
        ]);
        
        if ($result) {
            error_log("Notification created: $type_name for $recipient_type $recipient_id");
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Create notification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for new job application
 */
function notify_new_application($employer_id, $job_id, $applicant_name, $job_title, $application_id) {
    $title = "New Application";
    $message = "$applicant_name has applied for the $job_title position.";
    
    return create_notification(
        'employer',
        $employer_id,
        'new_application',
        $title,
        $message,
        $job_id,
        $application_id
    );
}

/**
 * Create notification for application status change
 */
function notify_application_status_change($employer_id, $applicant_name, $new_status, $job_title, $job_id, $application_id) {
    $title = "Application Status Updated";
    $statusMessages = [
        'under_review' => 'is now under review',
        'shortlisted' => 'has been shortlisted',
        'interview_scheduled' => 'has an interview scheduled',
        'interviewed' => 'has been interviewed',
        'hired' => 'has been hired',
        'rejected' => 'application has been rejected'
    ];
    
    $statusText = isset($statusMessages[$new_status]) ? $statusMessages[$new_status] : "status has been updated to $new_status";
    $message = "$applicant_name's application for $job_title $statusText.";
    
    return create_notification(
        'employer',
        $employer_id,
        'application_status',
        $title,
        $message,
        $job_id,
        $application_id
    );
}

/**
 * Create notification for interview scheduled
 */
function notify_interview_scheduled($employer_id, $applicant_name, $job_title, $interview_date, $interview_time, $job_id, $application_id, $interview_id) {
    $title = "Interview Scheduled";
    $formatted_date = date('F j, Y', strtotime($interview_date));
    $formatted_time = date('g:i A', strtotime($interview_time));
    $message = "Interview scheduled with $applicant_name for $job_title on $formatted_date at $formatted_time.";
    
    return create_notification(
        'employer',
        $employer_id,
        'interview_scheduled',
        $title,
        $message,
        $job_id,
        $application_id,
        $interview_id
    );
}

/**
 * Create notification for interview reminder
 */
function notify_interview_reminder($employer_id, $applicant_name, $job_title, $interview_date, $interview_time, $job_id, $application_id, $interview_id) {
    $title = "Interview Reminder";
    $interview_datetime = strtotime("$interview_date $interview_time");
    
    if ($interview_datetime - time() <= 86400) { // 24 hours
        $when = "tomorrow";
    } else {
        $when = "on " . date('F j', $interview_datetime);
    }
    
    $formatted_time = date('g:i A', strtotime($interview_time));
    $message = "You have an interview scheduled with $applicant_name for $job_title $when at $formatted_time.";
    
    return create_notification(
        'employer',
        $employer_id,
        'interview_reminder',
        $title,
        $message,
        $job_id,
        $application_id,
        $interview_id
    );
}

/**
 * Create notification for job posted successfully
 */
function notify_job_posted($employer_id, $job_title, $job_id) {
    $title = "Job Posted Successfully";
    $message = "Your job posting for \"$job_title\" has been published and is now live.";
    
    return create_notification(
        'employer',
        $employer_id,
        'job_posted',
        $title,
        $message,
        $job_id
    );
}

/**
 * Create notification for job expiring soon
 */
function notify_job_expiring($employer_id, $job_title, $days_remaining, $job_id) {
    $title = "Job Posting Expiring";
    $message = "Your job posting for \"$job_title\" will expire in $days_remaining day" . ($days_remaining != 1 ? 's' : '') . ". Consider extending it to attract more applicants.";
    
    return create_notification(
        'employer',
        $employer_id,
        'job_expiring',
        $title,
        $message,
        $job_id
    );
}

/**
 * Create notification for job performance update
 */
function notify_job_performance($employer_id, $job_title, $applications_count, $job_id) {
    $title = "Job Performance Update";
    $message = "Your \"$job_title\" posting is performing well with $applications_count applicant" . ($applications_count != 1 ? 's' : '') . " so far. Consider boosting this post for even more visibility.";
    
    return create_notification(
        'employer',
        $employer_id,
        'job_performance',
        $title,
        $message,
        $job_id
    );
}

/**
 * Create system notification
 */
function notify_system_update($employer_id, $title, $message) {
    return create_notification(
        'employer',
        $employer_id,
        'system_update',
        $title,
        $message
    );
}

/**
 * Create notification for profile completion reminder
 */
function notify_profile_completion($employer_id, $missing_sections) {
    $title = "Complete Your Company Profile";
    $message = "Complete your company profile to increase visibility to potential applicants. Missing: " . implode(', ', $missing_sections) . ".";
    
    return create_notification(
        'employer',
        $employer_id,
        'profile_completion',
        $title,
        $message
    );
}

/**
 * Create notification for subscription renewal
 */
function notify_subscription_renewal($employer_id, $renewal_date, $plan_name) {
    $title = "Subscription Renewal";
    $formatted_date = date('F j, Y', strtotime($renewal_date));
    $message = "Your $plan_name subscription will automatically renew on $formatted_date. Review your payment details to ensure uninterrupted service.";
    
    return create_notification(
        'employer',
        $employer_id,
        'subscription_renewal',
        $title,
        $message
    );
}

/**
 * Populate notification types if they don't exist
 */
function populate_notification_types() {
    global $conn;
    
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
    
    try {
        $created = 0;
        $existing = 0;
        
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
                error_log("Created notification type: " . $type[0]);
                $created++;
            } else {
                $existing++;
            }
        }
        
        error_log("Notification types setup: $created created, $existing existing");
        return true;
        
    } catch (Exception $e) {
        error_log("Error populating notification types: " . $e->getMessage());
        return false;
    }
}

// Auto-populate notification types when this file is included
if (isset($conn)) {
    populate_notification_types();
}
?>