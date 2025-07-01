<?php
/**
 * Get Dashboard Home Data API for ThisAble Mobile
 * Returns: stats, recent applications, upcoming interviews, suggested jobs
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    error_log("Dashboard Home API: seeker_id=$seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== DASHBOARD STATS =====
    $stats = [];
    
    // Total applications count
    $stmt = $conn->prepare("SELECT COUNT(*) as total_applications FROM job_applications WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $stats['applications_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];
    
    // Saved jobs count
    $stmt = $conn->prepare("SELECT COUNT(*) as saved_jobs FROM saved_jobs WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $stats['saved_jobs_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['saved_jobs'];
    
    // Interviews count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as interviews_count 
        FROM interviews i 
        JOIN job_applications ja ON i.application_id = ja.application_id 
        WHERE ja.seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $stats['interviews_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['interviews_count'];
    
    // Profile views (placeholder - you can implement tracking later)
    $stats['profile_views'] = 47; // Static for now, implement tracking later
    
    // ===== RECENT APPLICATIONS =====
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            jp.job_title,
            e.company_name,
            jp.location,
            jp.employment_type
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = ?
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== UPCOMING INTERVIEWS =====
    $stmt = $conn->prepare("
        SELECT 
            i.interview_id,
            i.scheduled_date,
            i.scheduled_time,
            i.interview_type,
            i.meeting_link,
            i.location_address,
            i.interview_status,
            jp.job_title,
            e.company_name
        FROM interviews i
        JOIN job_applications ja ON i.application_id = ja.application_id
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = ? 
        AND i.scheduled_date >= CURDATE()
        AND i.interview_status IN ('scheduled', 'confirmed')
        ORDER BY i.scheduled_date ASC, i.scheduled_time ASC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $upcomingInterviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== SUGGESTED JOBS =====
    // Get user's skills for job matching
    $stmt = $conn->prepare("
        SELECT GROUP_CONCAT(skill_id) as skill_ids 
        FROM seeker_skills 
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $userSkillsResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $userSkillIds = $userSkillsResult['skill_ids'] ? explode(',', $userSkillsResult['skill_ids']) : [];
    
    // Get suggested jobs (PWD-friendly jobs that match user preferences)
    $stmt = $conn->prepare("
        SELECT DISTINCT
            jp.job_id,
            jp.job_title,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.posted_at,
            e.company_name,
            e.company_logo_path,
            ja_accom.wheelchair_accessible,
            ja_accom.flexible_schedule,
            ja_accom.remote_work_option,
            ja_accom.assistive_technology,
            ja_accom.additional_accommodations
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_accommodations ja_accom ON jp.job_id = ja_accom.job_id
        LEFT JOIN job_applications existing_app ON jp.job_id = existing_app.job_id AND existing_app.seeker_id = ?
        WHERE jp.job_status = 'active'
        AND existing_app.application_id IS NULL
        AND jp.application_deadline >= CURDATE()
        ORDER BY jp.posted_at DESC
        LIMIT 6
    ");
    $stmt->execute([$seekerId]);
    $suggestedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process PWD accommodations for suggested jobs
    foreach ($suggestedJobs as &$job) {
        $accommodations = [];
        if ($job['wheelchair_accessible']) $accommodations[] = 'Wheelchair Accessible';
        if ($job['flexible_schedule']) $accommodations[] = 'Flexible Schedule';
        if ($job['remote_work_option']) $accommodations[] = 'Remote Work';
        if ($job['assistive_technology']) $accommodations[] = 'Assistive Technology';
        if ($job['additional_accommodations']) {
            $additional = json_decode($job['additional_accommodations'], true);
            if (is_array($additional)) {
                $accommodations = array_merge($accommodations, $additional);
            }
        }
        $job['pwd_accommodations'] = $accommodations;
        
        // Clean up individual accommodation fields
        unset($job['wheelchair_accessible'], $job['flexible_schedule'], 
              $job['remote_work_option'], $job['assistive_technology'], 
              $job['additional_accommodations']);
    }
    
    // ===== COMPILE RESPONSE =====
    $dashboardData = [
        'stats' => $stats,
        'recent_applications' => $recentApplications,
        'upcoming_interviews' => $upcomingInterviews,
        'suggested_jobs' => $suggestedJobs,
        'user_name' => $user['first_name'] ?? 'User' // From auth token
    ];
    
    ApiResponse::success($dashboardData, "Dashboard data retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Dashboard home database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Dashboard home error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while retrieving dashboard data");
}
?>