<?php
/**
 * Job Recommendation System
 * Automatically suggests relevant jobs to candidates and creates notifications
 */

require_once 'notification_system.php';

class JobRecommendationSystem {
    private $conn;
    private $notificationSystem;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->notificationSystem = new NotificationSystem($db_connection);
    }
    
    /**
     * Find and notify about job recommendations for a specific candidate
     */
    public function findRecommendationsForCandidate($seeker_id) {
        try {
            // Get candidate profile and skills
            $candidate = $this->getCandidateProfile($seeker_id);
            if (!$candidate) return false;
            
            // Find matching jobs
            $recommendedJobs = $this->findMatchingJobs($candidate);
            
            $notified_count = 0;
            foreach ($recommendedJobs as $job) {
                // Check if already notified about this job
                if (!$this->hasBeenNotified($seeker_id, $job['job_id'])) {
                    $match_reason = $this->generateMatchReason($candidate, $job);
                    
                    $result = $this->notificationSystem->notifyJobRecommendation(
                        $seeker_id,
                        $job['job_id'],
                        $match_reason
                    );
                    
                    if ($result) {
                        $notified_count++;
                        // Record that we've notified about this job
                        $this->recordNotification($seeker_id, $job['job_id']);
                    }
                }
            }
            
            return $notified_count;
            
        } catch (Exception $e) {
            error_log("Error finding recommendations for candidate {$seeker_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find recommendations for all active candidates
     */
    public function findRecommendationsForAllCandidates() {
        try {
            $stmt = $this->conn->prepare("
                SELECT seeker_id FROM job_seekers 
                WHERE setup_complete = 1
            ");
            $stmt->execute();
            $candidates = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $total_notified = 0;
            foreach ($candidates as $seeker_id) {
                $count = $this->findRecommendationsForCandidate($seeker_id);
                if ($count) {
                    $total_notified += $count;
                }
                
                // Small delay to avoid overwhelming the system
                usleep(100000); // 0.1 second delay
            }
            
            return $total_notified;
            
        } catch (Exception $e) {
            error_log("Error finding recommendations for all candidates: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process new job posting and notify relevant candidates
     */
    public function processNewJobPosting($job_id) {
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
            
            // Find candidates who might be interested
            $relevant_candidates = $this->findRelevantCandidates($job);
            
            $notified_count = 0;
            foreach ($relevant_candidates as $candidate) {
                $match_reason = $this->generateMatchReason($candidate, $job);
                
                $result = $this->notificationSystem->notifyJobRecommendation(
                    $candidate['seeker_id'],
                    $job_id,
                    $match_reason
                );
                
                if ($result) {
                    $notified_count++;
                    $this->recordNotification($candidate['seeker_id'], $job_id);
                }
            }
            
            return $notified_count;
            
        } catch (Exception $e) {
            error_log("Error processing new job posting {$job_id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get candidate profile with skills and preferences
     */
    private function getCandidateProfile($seeker_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    js.*,
                    up.work_style,
                    up.job_type,
                    up.salary_range,
                    GROUP_CONCAT(s.skill_name) as skills
                FROM job_seekers js
                LEFT JOIN user_preferences up ON js.seeker_id = up.seeker_id
                LEFT JOIN seeker_skills ss ON js.seeker_id = ss.seeker_id
                LEFT JOIN skills s ON ss.skill_id = s.skill_id
                WHERE js.seeker_id = ?
                GROUP BY js.seeker_id
            ");
            $stmt->execute([$seeker_id]);
            $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($candidate && $candidate['skills']) {
                $candidate['skills_array'] = explode(',', $candidate['skills']);
            } else {
                $candidate['skills_array'] = [];
            }
            
            return $candidate;
            
        } catch (Exception $e) {
            error_log("Error getting candidate profile: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find jobs matching candidate profile
     */
    private function findMatchingJobs($candidate) {
        try {
            $location_condition = "";
            $params = [];
            
            // Location matching
            if (!empty($candidate['city'])) {
                $location_condition = " AND (jp.location LIKE ? OR jp.remote_work_available = 1)";
                $params[] = '%' . $candidate['city'] . '%';
            }
            
            // Work style matching
            $work_style_condition = "";
            if ($candidate['work_style']) {
                switch ($candidate['work_style']) {
                    case 'remote':
                        $work_style_condition = " AND jp.remote_work_available = 1";
                        break;
                    case 'onsite':
                        $work_style_condition = " AND jp.remote_work_available = 0";
                        break;
                    // hybrid can match both
                }
            }
            
            // Employment type matching
            $employment_type_condition = "";
            if ($candidate['job_type']) {
                $type_mapping = [
                    'fulltime' => 'Full-time',
                    'parttime' => 'Part-time',
                    'freelance' => 'Freelance'
                ];
                
                if (isset($type_mapping[$candidate['job_type']])) {
                    $employment_type_condition = " AND jp.employment_type = ?";
                    $params[] = $type_mapping[$candidate['job_type']];
                }
            }
            
            $query = "
                SELECT DISTINCT jp.*, e.company_name,
                    COUNT(jr.skill_id) as matching_skills,
                    COUNT(jr.requirement_id) as total_requirements
                FROM job_posts jp
                JOIN employers e ON jp.employer_id = e.employer_id
                LEFT JOIN job_requirements jr ON jp.job_id = jr.job_id
                LEFT JOIN skills s ON jr.skill_id = s.skill_id
                WHERE jp.job_status = 'active'
                AND (jp.application_deadline IS NULL OR jp.application_deadline > NOW())
                {$location_condition}
                {$work_style_condition}
                {$employment_type_condition}
                GROUP BY jp.job_id
                HAVING matching_skills > 0 OR total_requirements = 0
                ORDER BY matching_skills DESC, jp.created_at DESC
                LIMIT 10
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error finding matching jobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Find candidates relevant to a job posting
     */
    private function findRelevantCandidates($job) {
        try {
            $query = "
                SELECT DISTINCT js.*, up.work_style, up.job_type,
                    COUNT(ss.skill_id) as matching_skills,
                    GROUP_CONCAT(s.skill_name) as skills
                FROM job_seekers js
                LEFT JOIN user_preferences up ON js.seeker_id = up.seeker_id
                LEFT JOIN seeker_skills ss ON js.seeker_id = ss.seeker_id
                LEFT JOIN skills s ON ss.skill_id = s.skill_id
                LEFT JOIN job_requirements jr ON s.skill_id = jr.skill_id AND jr.job_id = ?
                WHERE js.setup_complete = 1
                AND (js.city IS NULL OR js.city = '' OR ? LIKE CONCAT('%', js.city, '%') OR ? = 1)
                GROUP BY js.seeker_id
                HAVING matching_skills > 0
                ORDER BY matching_skills DESC
                LIMIT 20
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $job['job_id'],
                $job['location'],
                $job['remote_work_available']
            ]);
            $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process skills array for each candidate
            foreach ($candidates as &$candidate) {
                if ($candidate['skills']) {
                    $candidate['skills_array'] = explode(',', $candidate['skills']);
                } else {
                    $candidate['skills_array'] = [];
                }
            }
            
            return $candidates;
            
        } catch (Exception $e) {
            error_log("Error finding relevant candidates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate match reason explanation
     */
    private function generateMatchReason($candidate, $job) {
        $reasons = [];
        
        // Skill matching
        if (isset($job['matching_skills']) && $job['matching_skills'] > 0) {
            $reasons[] = "Matches {$job['matching_skills']} of your skills";
        } elseif (isset($candidate['matching_skills']) && $candidate['matching_skills'] > 0) {
            $reasons[] = "Matches {$candidate['matching_skills']} of your skills";
        }
        
        // Location matching
        if (!empty($candidate['city']) && stripos($job['location'], $candidate['city']) !== false) {
            $reasons[] = "Located in your preferred area";
        } elseif ($job['remote_work_available']) {
            $reasons[] = "Offers remote work option";
        }
        
        // Work style matching
        if ($candidate['work_style'] === 'remote' && $job['remote_work_available']) {
            $reasons[] = "Supports remote work";
        } elseif ($candidate['work_style'] === 'hybrid' && $job['flexible_schedule']) {
            $reasons[] = "Offers flexible work arrangements";
        }
        
        if (empty($reasons)) {
            $reasons[] = "Based on your profile and preferences";
        }
        
        return implode(', ', $reasons) . '.';
    }
    
    /**
     * Check if candidate has been notified about this job
     */
    private function hasBeenNotified($seeker_id, $job_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM notifications 
                WHERE recipient_type = 'candidate' 
                AND recipient_id = ? 
                AND related_job_id = ?
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$seeker_id, $job_id]);
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Record that we've notified about this job
     */
    private function recordNotification($seeker_id, $job_id) {
        // This is handled by the notification creation itself
        // Could add additional tracking table if needed
        return true;
    }
}

// Global instance
function getJobRecommendationSystem() {
    global $conn;
    static $recommendation_system = null;
    
    if ($recommendation_system === null) {
        $recommendation_system = new JobRecommendationSystem($conn);
    }
    
    return $recommendation_system;
}
?>