<?php
/**
 * ThisAble Notification System
 * Handles automatic notification creation and management
 */

class NotificationSystem {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }
    
    /**
     * Create a new notification
     */
    public function createNotification($params) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO notifications (
                    recipient_type, recipient_id, type_id, title, message, 
                    related_job_id, related_application_id, related_interview_id, 
                    is_read, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())
            ");
            
            $result = $stmt->execute([
                $params['recipient_type'],
                $params['recipient_id'],
                $params['type_id'],
                $params['title'],
                $params['message'],
                $params['related_job_id'] ?? null,
                $params['related_application_id'] ?? null,
                $params['related_interview_id'] ?? null
            ]);
            
            if ($result) {
                error_log("Notification created: " . $params['title'] . " for " . $params['recipient_type'] . " " . $params['recipient_id']);
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Application Status Change Notifications
     */
    public function notifyApplicationStatusChange($application_id, $new_status, $old_status = null) {
        try {
            // Get application details
            $stmt = $this->conn->prepare("
                SELECT ja.*, jp.job_title, e.company_name, js.seeker_id
                FROM job_applications ja
                JOIN job_posts jp ON ja.job_id = jp.job_id
                JOIN employers e ON jp.employer_id = e.employer_id
                JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                WHERE ja.application_id = ?
            ");
            $stmt->execute([$application_id]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$app) return false;
            
            // Get notification type ID for application status
            $type_id = $this->getNotificationTypeId('application_status');
            
            // Create status-specific messages
            $messages = [
                'under_review' => "Your application for {$app['job_title']} at {$app['company_name']} is now under review.",
                'shortlisted' => "Great news! You've been shortlisted for {$app['job_title']} at {$app['company_name']}.",
                'interview_scheduled' => "An interview has been scheduled for your application to {$app['job_title']} at {$app['company_name']}.",
                'interviewed' => "Thank you for interviewing for {$app['job_title']} at {$app['company_name']}. We'll update you soon.",
                'hired' => "Congratulations! You've been selected for {$app['job_title']} at {$app['company_name']}!",
                'rejected' => "Thank you for your interest in {$app['job_title']} at {$app['company_name']}. Unfortunately, we won't be moving forward at this time."
            ];
            
            $titles = [
                'under_review' => 'Application Under Review',
                'shortlisted' => 'Application Shortlisted!', 
                'interview_scheduled' => 'Interview Scheduled',
                'interviewed' => 'Interview Completed',
                'hired' => 'Congratulations - You\'re Hired!',
                'rejected' => 'Application Update'
            ];
            
            $title = $titles[$new_status] ?? 'Application Status Update';
            $message = $messages[$new_status] ?? "Your application status has been updated to: " . ucfirst(str_replace('_', ' ', $new_status));
            
            return $this->createNotification([
                'recipient_type' => 'candidate',
                'recipient_id' => $app['seeker_id'],
                'type_id' => $type_id,
                'title' => $title,
                'message' => $message,
                'related_job_id' => $app['job_id'],
                'related_application_id' => $application_id
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating application status notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Interview Scheduled Notification
     */
    public function notifyInterviewScheduled($interview_id) {
        try {
            // Get interview details
            $stmt = $this->conn->prepare("
                SELECT i.*, ja.seeker_id, jp.job_title, e.company_name
                FROM interviews i
                JOIN job_applications ja ON i.application_id = ja.application_id
                JOIN job_posts jp ON ja.job_id = jp.job_id
                JOIN employers e ON jp.employer_id = e.employer_id
                WHERE i.interview_id = ?
            ");
            $stmt->execute([$interview_id]);
            $interview = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$interview) return false;
            
            $type_id = $this->getNotificationTypeId('interview_scheduled');
            
            $date = date('M j, Y', strtotime($interview['scheduled_date']));
            $time = date('g:i A', strtotime($interview['scheduled_time']));
            
            $title = "Interview Scheduled";
            $message = "Your interview for {$interview['job_title']} at {$interview['company_name']} has been scheduled for {$date} at {$time}.";
            
            return $this->createNotification([
                'recipient_type' => 'candidate',
                'recipient_id' => $interview['seeker_id'],
                'type_id' => $type_id,
                'title' => $title,
                'message' => $message,
                'related_application_id' => $interview['application_id'],
                'related_interview_id' => $interview_id
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating interview notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Job Recommendation Notification
     */
    public function notifyJobRecommendation($seeker_id, $job_id, $match_reason = '') {
        try {
            // Get job details
            $stmt = $this->conn->prepare("
                SELECT jp.*, e.company_name
                FROM job_posts jp
                JOIN employers e ON jp.employer_id = e.employer_id
                WHERE jp.job_id = ? AND jp.job_status = 'active'
            ");
            $stmt->execute([$job_id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$job) return false;
            
            $type_id = $this->getNotificationTypeId('job_posted');
            
            $title = "New Job Match Found!";
            $message = "We found a job that matches your profile: {$job['job_title']} at {$job['company_name']}.";
            
            if ($match_reason) {
                $message .= " " . $match_reason;
            }
            
            return $this->createNotification([
                'recipient_type' => 'candidate',
                'recipient_id' => $seeker_id,
                'type_id' => $type_id,
                'title' => $title,
                'message' => $message,
                'related_job_id' => $job_id
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating job recommendation notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Profile Completion Reminder
     */
    public function notifyProfileCompletion($seeker_id, $missing_sections = []) {
        try {
            $type_id = $this->getNotificationTypeId('profile_completion');
            
            $title = "Complete Your Profile";
            
            if (empty($missing_sections)) {
                $message = "Your profile is incomplete. Complete it to increase your visibility to employers.";
            } else {
                $sections = implode(', ', $missing_sections);
                $message = "Complete these profile sections to boost your job prospects: {$sections}.";
            }
            
            return $this->createNotification([
                'recipient_type' => 'candidate',
                'recipient_id' => $seeker_id,
                'type_id' => $type_id,
                'title' => $title,
                'message' => $message
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating profile completion notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Application Deadline Reminder
     */
    public function notifyApplicationDeadline($job_id, $days_remaining = 3) {
        try {
            // Get job details and interested candidates
            $stmt = $this->conn->prepare("
                SELECT DISTINCT sv.seeker_id, jp.job_title, e.company_name, jp.application_deadline
                FROM saved_jobs sv
                JOIN job_posts jp ON sv.job_id = jp.job_id
                JOIN employers e ON jp.employer_id = e.employer_id
                LEFT JOIN job_applications ja ON sv.seeker_id = ja.seeker_id AND sv.job_id = ja.job_id
                WHERE sv.job_id = ? 
                AND jp.application_deadline IS NOT NULL
                AND jp.application_deadline > NOW()
                AND ja.application_id IS NULL
            ");
            $stmt->execute([$job_id]);
            $interested_candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($interested_candidates)) return true;
            
            $type_id = $this->getNotificationTypeId('deadline_reminder');
            
            $job_info = $interested_candidates[0]; // All have same job info
            $deadline_date = date('M j, Y', strtotime($job_info['application_deadline']));
            
            $title = "Application Deadline Reminder";
            $message = "Don't miss out! The application deadline for {$job_info['job_title']} at {$job_info['company_name']} is {$deadline_date} ({$days_remaining} days remaining).";
            
            $created_count = 0;
            foreach ($interested_candidates as $candidate) {
                $result = $this->createNotification([
                    'recipient_type' => 'candidate',
                    'recipient_id' => $candidate['seeker_id'],
                    'type_id' => $type_id,
                    'title' => $title,
                    'message' => $message,
                    'related_job_id' => $job_id
                ]);
                
                if ($result) $created_count++;
            }
            
            return $created_count;
            
        } catch (Exception $e) {
            error_log("Error creating deadline reminder notifications: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * System Announcement
     */
    public function notifySystemAnnouncement($title, $message, $target_audience = 'all') {
        try {
            $type_id = $this->getNotificationTypeId('system_update');
            
            // Get target users
            if ($target_audience === 'candidates') {
                $stmt = $this->conn->prepare("SELECT seeker_id FROM job_seekers");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $recipient_type = 'candidate';
            } elseif ($target_audience === 'employers') {
                $stmt = $this->conn->prepare("SELECT employer_id FROM employers");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $recipient_type = 'employer';
            } else {
                // Send to both
                $this->notifySystemAnnouncement($title, $message, 'candidates');
                $this->notifySystemAnnouncement($title, $message, 'employers');
                return true;
            }
            
            $created_count = 0;
            foreach ($users as $user_id) {
                $result = $this->createNotification([
                    'recipient_type' => $recipient_type,
                    'recipient_id' => $user_id,
                    'type_id' => $type_id,
                    'title' => $title,
                    'message' => $message
                ]);
                
                if ($result) $created_count++;
            }
            
            return $created_count;
            
        } catch (Exception $e) {
            error_log("Error creating system announcement: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notification type ID by name
     */
    private function getNotificationTypeId($type_name) {
        try {
            $stmt = $this->conn->prepare("SELECT type_id FROM notification_types WHERE type_name = ?");
            $stmt->execute([$type_name]);
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            return $result ?: 1; // Default to first type if not found
        } catch (Exception $e) {
            return 1; // Default fallback
        }
    }
    
    /**
     * Check profile completion and send reminders
     */
    public function checkProfileCompletionReminders() {
        try {
            $stmt = $this->conn->prepare("
                SELECT js.seeker_id, js.first_name,
                    CASE WHEN pd.profile_id IS NULL THEN 1 ELSE 0 END as missing_profile,
                    CASE WHEN COUNT(e.education_id) = 0 THEN 1 ELSE 0 END as missing_education,
                    CASE WHEN COUNT(ex.experience_id) = 0 THEN 1 ELSE 0 END as missing_experience,
                    CASE WHEN COUNT(ss.skill_id) = 0 THEN 1 ELSE 0 END as missing_skills,
                    CASE WHEN COUNT(r.resume_id) = 0 THEN 1 ELSE 0 END as missing_resume
                FROM job_seekers js
                LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
                LEFT JOIN education e ON js.seeker_id = e.seeker_id
                LEFT JOIN experience ex ON js.seeker_id = ex.seeker_id
                LEFT JOIN seeker_skills ss ON js.seeker_id = ss.seeker_id
                LEFT JOIN resumes r ON js.seeker_id = r.seeker_id AND r.is_current = 1
                WHERE js.setup_complete = 1
                GROUP BY js.seeker_id
                HAVING (missing_profile + missing_education + missing_experience + missing_skills + missing_resume) > 0
            ");
            $stmt->execute();
            $incomplete_profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($incomplete_profiles as $profile) {
                $missing_sections = [];
                if ($profile['missing_profile']) $missing_sections[] = 'About';
                if ($profile['missing_education']) $missing_sections[] = 'Education';
                if ($profile['missing_experience']) $missing_sections[] = 'Experience';
                if ($profile['missing_skills']) $missing_sections[] = 'Skills';
                if ($profile['missing_resume']) $missing_sections[] = 'Resume';
                
                // Only send reminder if they haven't received one in the last 7 days
                $stmt = $this->conn->prepare("
                    SELECT COUNT(*) FROM notifications n
                    JOIN notification_types nt ON n.type_id = nt.type_id
                    WHERE n.recipient_type = 'candidate' 
                    AND n.recipient_id = ? 
                    AND nt.type_name = 'profile_completion'
                    AND n.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                ");
                $stmt->execute([$profile['seeker_id']]);
                $recent_reminders = $stmt->fetchColumn();
                
                if ($recent_reminders == 0) {
                    $this->notifyProfileCompletion($profile['seeker_id'], $missing_sections);
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error checking profile completion reminders: " . $e->getMessage());
            return false;
        }
    }
}

// Global notification instance
function getNotificationSystem() {
    global $conn;
    static $notification_system = null;
    
    if ($notification_system === null) {
        $notification_system = new NotificationSystem($conn);
    }
    
    return $notification_system;
}
?>