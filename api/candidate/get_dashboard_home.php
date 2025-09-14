<?php
/**
 * CORRECTED Dashboard API - Fixed response structure
 * LOCATION: api/candidate/get_dashboard_home.php
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
    
    $seekerId = $user['user_id'] ?? $user['seeker_id'] ?? null;
    
    if (!$seekerId) {
        ApiResponse::serverError("No seeker_id found");
    }

    error_log("Dashboard API: Processing for seeker_id = $seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // GET APPLICATIONS COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_applications WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $apps_count = (int)$result['count'];
    
    // GET SAVED JOBS COUNT  
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $saved_count = (int)$result['count'];
    
    // GET INTERVIEWS COUNT
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM interviews i
        JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE ja.seeker_id = ? 
        AND i.interview_status IN ('scheduled', 'confirmed')
        AND i.scheduled_date >= CURDATE()
    ");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $interviews_count = (int)$result['count'];
    
    // GET NOTIFICATIONS COUNT
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE recipient_type = 'candidate' 
        AND recipient_id = ? 
        AND is_read = 0
    ");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $notifications_count = (int)$result['count'];
    
    // BUILD STATS WITH GUARANTEED INTEGERS
    $stats = [
        'applications_count' => $apps_count,
        'saved_jobs_count' => $saved_count,
        'interview_scheduled_count' => $interviews_count,
        'notifications_count' => $notifications_count
    ];
    
    // GET RECENT APPLICATIONS
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            jp.job_title,
            jp.location,
            jp.employment_type,
            e.company_name
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = ?
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates
    foreach ($recentApplications as &$app) {
        $app['applied_at'] = date('F j, Y', strtotime($app['applied_at']));
    }
    
    // GET UPCOMING INTERVIEWS  
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
    
    // Format interview dates/times
    foreach ($upcomingInterviews as &$interview) {
        $interview['scheduled_date'] = date('F j, Y', strtotime($interview['scheduled_date']));
        if ($interview['scheduled_time']) {
            $interview['scheduled_time'] = date('g:i A', strtotime($interview['scheduled_time']));
        }
    }
    
    // GET SUGGESTED JOBS
    $stmt = $conn->prepare("
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.posted_at,
            e.company_name,
            e.company_logo_path
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active'
        AND jp.job_id NOT IN (
            SELECT job_id FROM job_applications WHERE seeker_id = ?
        )
        ORDER BY jp.posted_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $suggestedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format job dates
    foreach ($suggestedJobs as &$job) {
        $job['posted_at'] = date('F j, Y', strtotime($job['posted_at']));
        $job['company_logo'] = $job['company_logo_path'] ?? substr($job['company_name'], 0, 2);
    }
    
    // CORRECTED: Build response data object (not string)
    $responseData = [
        'stats' => $stats,
        'recent_applications' => $recentApplications,
        'upcoming_interviews' => $upcomingInterviews, 
        'suggested_jobs' => $suggestedJobs
    ];
    
    error_log("Dashboard API Success: " . json_encode($stats));
    
    // FIXED: Correct parameter order - message first, data second
    ApiResponse::success('Dashboard data retrieved successfully', $responseData);
    
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    ApiResponse::error("Failed to fetch dashboard data: " . $e->getMessage());
}
?>