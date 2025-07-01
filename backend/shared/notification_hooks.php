<?php
/**
 * Notification System Integration Hooks
 * These functions integrate the notification system with existing ThisAble features
 */

require_once 'notification_system.php';
require_once 'job_recommendations.php';

class NotificationHooks {
    private $notificationSystem;
    private $jobRecommendationSystem;
    
    public function __construct() {
        $this->notificationSystem = getNotificationSystem();
        $this->jobRecommendationSystem = getJobRecommendationSystem();
    }
    
    /**
     * Hook: When user completes profile setup
     */
    public function onProfileSetupComplete($seeker_id) {
        try {
            // Send welcome notification
            $this->notificationSystem->createNotification([
                'recipient_type' => 'candidate',
                'recipient_id' => $seeker_id,
                'type_id' => $this->getNotificationTypeId('system_update'),
                'title' => 'Welcome to ThisAble!',
                'message' => 'Your profile setup is complete! You can now browse jobs and apply to positions that match your skills and preferences.'
            ]);
            
            // Trigger job recommendations
            $this->jobRecommendationSystem->findRecommendationsForCandidate($seeker_id);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in profile setup complete hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When user submits job application
     */
    public function onJobApplicationSubmitted($application_id, $job_id, $seeker_id) {
        try {
            // Get job details
            global $conn;
            $stmt = $conn->prepare("
                SELECT jp.job_title, e.company_name 
                FROM job_posts jp 
                JOIN employers e ON jp.employer_id = e.employer_id 
                WHERE jp.job_id = ?
            ");
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($job) {
                // Notify candidate about successful submission
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $seeker_id,
                    'type_id' => $this->getNotificationTypeId('application_status'),
                    'title' => 'Application Submitted Successfully',
                    'message' => "Your application for {$job['job_title']} at {$job['company_name']} has been submitted successfully. You'll receive updates on your application status.",
                    'related_job_id' => $job_id,
                    'related_application_id' => $application_id
                ]);
                
                // Notify employer about new application
                $stmt = $conn->prepare("SELECT employer_id FROM job_posts WHERE job_id = ?");
                $stmt->execute([$job_id]);
                $employer_id = $stmt->fetchColumn();
                
                if ($employer_id) {
                    $this->notificationSystem->createNotification([
                        'recipient_type' => 'employer',
                        'recipient_id' => $employer_id,
                        'type_id' => $this->getNotificationTypeId('new_application'),
                        'title' => 'New Job Application Received',
                        'message' => "A new application has been submitted for your {$job['job_title']} position.",
                        'related_job_id' => $job_id,
                        'related_application_id' => $application_id
                    ]);
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in job application submitted hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When employer posts new job
     */
    public function onJobPosted($job_id, $employer_id) {
        try {
            // Get job details
            global $conn;
            $stmt = $conn->prepare("
                SELECT jp.*, e.company_name 
                FROM job_posts jp 
                JOIN employers e ON jp.employer_id = e.employer_id 
                WHERE jp.job_id = ?
            ");
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($job) {
                // Notify employer about successful posting
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'employer',
                    'recipient_id' => $employer_id,
                    'type_id' => $this->getNotificationTypeId('job_posted'),
                    'title' => 'Job Posted Successfully',
                    'message' => "Your job posting for {$job['job_title']} is now live and visible to candidates.",
                    'related_job_id' => $job_id
                ]);
                
                // Process job recommendations for candidates
                $this->jobRecommendationSystem->processNewJobPosting($job_id);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in job posted hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When interview is scheduled
     */
    public function onInterviewScheduled($interview_id, $application_id) {
        try {
            // Use existing notification system method
            $this->notificationSystem->notifyInterviewScheduled($interview_id);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in interview scheduled hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When user saves a job
     */
    public function onJobSaved($seeker_id, $job_id) {
        try {
            // Check if this is their first saved job
            global $conn;
            $stmt = $conn->prepare("SELECT COUNT(*) FROM saved_jobs WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $saved_count = $stmt->fetchColumn();
            
            if ($saved_count == 1) { // First saved job
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $seeker_id,
                    'type_id' => $this->getNotificationTypeId('system_update'),
                    'title' => 'Job Saved Successfully',
                    'message' => 'Great! You\'ve saved your first job. Saved jobs appear in your dashboard and you\'ll receive notifications about application deadlines.'
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in job saved hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When user uploads resume
     */
    public function onResumeUploaded($seeker_id, $resume_id) {
        try {
            // Check if this is their first resume
            global $conn;
            $stmt = $conn->prepare("SELECT COUNT(*) FROM resumes WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $resume_count = $stmt->fetchColumn();
            
            if ($resume_count == 1) { // First resume
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $seeker_id,
                    'type_id' => $this->getNotificationTypeId('profile_completion'),
                    'title' => 'Resume Uploaded Successfully',
                    'message' => 'Excellent! Your resume has been uploaded. You can now apply to jobs with just one click using your uploaded resume.'
                ]);
                
                // Trigger new job recommendations since profile is more complete
                $this->jobRecommendationSystem->findRecommendationsForCandidate($seeker_id);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in resume uploaded hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When user adds skills to profile
     */
    public function onSkillsUpdated($seeker_id, $skills_added = []) {
        try {
            if (!empty($skills_added)) {
                $skills_text = implode(', ', array_slice($skills_added, 0, 3));
                if (count($skills_added) > 3) {
                    $skills_text .= ' and ' . (count($skills_added) - 3) . ' more';
                }
                
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $seeker_id,
                    'type_id' => $this->getNotificationTypeId('profile_completion'),
                    'title' => 'Skills Updated',
                    'message' => "Great! You've added new skills: {$skills_text}. This will help us recommend more relevant job opportunities."
                ]);
                
                // Trigger new job recommendations with updated skills
                $this->jobRecommendationSystem->findRecommendationsForCandidate($seeker_id);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in skills updated hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When job application deadline approaches
     */
    public function onApplicationDeadlineApproaching($job_id, $days_remaining) {
        try {
            $this->notificationSystem->notifyApplicationDeadline($job_id, $days_remaining);
            return true;
            
        } catch (Exception $e) {
            error_log("Error in application deadline hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: When employer views candidate profile
     */
    public function onProfileViewed($seeker_id, $employer_id) {
        try {
            // Get employer company name
            global $conn;
            $stmt = $conn->prepare("SELECT company_name FROM employers WHERE employer_id = ?");
            $stmt->execute([$employer_id]);
            $company_name = $stmt->fetchColumn();
            
            if ($company_name) {
                $this->notificationSystem->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $seeker_id,
                    'type_id' => $this->getNotificationTypeId('system_update'),
                    'title' => 'Profile Viewed by Employer',
                    'message' => "An employer from {$company_name} has viewed your profile. Keep your profile updated to make a great impression!"
                ]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error in profile viewed hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Hook: System maintenance or updates
     */
    public function onSystemUpdate($title, $message, $target_audience = 'all') {
        try {
            $this->notificationSystem->notifySystemAnnouncement($title, $message, $target_audience);
            return true;
            
        } catch (Exception $e) {
            error_log("Error in system update hook: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification type ID
     */
    private function getNotificationTypeId($type_name) {
        try {
            global $conn;
            $stmt = $conn->prepare("SELECT type_id FROM notification_types WHERE type_name = ?");
            $stmt->execute([$type_name]);
            $result = $stmt->fetchColumn();
            return $result ?: 1; // Default to first type if not found
        } catch (Exception $e) {
            return 1; // Default fallback
        }
    }
}

// Global instance
function getNotificationHooks() {
    static $notification_hooks = null;
    
    if ($notification_hooks === null) {
        $notification_hooks = new NotificationHooks();
    }
    
    return $notification_hooks;
}

// Helper functions for easy integration

/**
 * Easy integration functions for existing code
 */
function triggerNotificationHook($hook_name, ...$args) {
    try {
        $hooks = getNotificationHooks();
        
        switch ($hook_name) {
            case 'profile_setup_complete':
                return $hooks->onProfileSetupComplete(...$args);
            case 'job_application_submitted':
                return $hooks->onJobApplicationSubmitted(...$args);
            case 'job_posted':
                return $hooks->onJobPosted(...$args);
            case 'interview_scheduled':
                return $hooks->onInterviewScheduled(...$args);
            case 'job_saved':
                return $hooks->onJobSaved(...$args);
            case 'resume_uploaded':
                return $hooks->onResumeUploaded(...$args);
            case 'skills_updated':
                return $hooks->onSkillsUpdated(...$args);
            case 'profile_viewed':
                return $hooks->onProfileViewed(...$args);
            case 'system_update':
                return $hooks->onSystemUpdate(...$args);
            default:
                error_log("Unknown notification hook: {$hook_name}");
                return false;
        }
        
    } catch (Exception $e) {
        error_log("Error triggering notification hook {$hook_name}: " . $e->getMessage());
        return false;
    }
}
?>