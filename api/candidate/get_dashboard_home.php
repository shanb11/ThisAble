<?php
/**
 * BULLETPROOF Get Dashboard Home Data API for ThisAble Mobile
 * Simple queries that match your exact database structure
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
    error_log("BULLETPROOF Dashboard API: seeker_id=$seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: USER-SPECIFIC DASHBOARD STATS =====
    $stats = [];
    
    // Total applications count FOR THIS USER ONLY
    $stmt = $conn->prepare("SELECT COUNT(*) as total_applications FROM job_applications WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $stats['applications_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_applications'];
    
    // Saved jobs count FOR THIS USER ONLY
    $stmt = $conn->prepare("SELECT COUNT(*) as saved_jobs FROM saved_jobs WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $stats['saved_jobs_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['saved_jobs'];
    
    // FIXED: Get status breakdown FOR THIS USER ONLY
    $stmt = $conn->prepare("
        SELECT 
            application_status,
            COUNT(*) as count
        FROM job_applications 
        WHERE seeker_id = ? 
        GROUP BY application_status
    ");
    $stmt->execute([$seekerId]);
    $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize all status counts to 0
    $stats['submitted_count'] = 0;
    $stats['under_review_count'] = 0;
    $stats['interview_scheduled_count'] = 0;
    $stats['interviewed_count'] = 0;
    $stats['hired_count'] = 0;
    $stats['rejected_count'] = 0;
    
    // Populate actual counts from database
    foreach ($statusBreakdown as $status) {
        switch ($status['application_status']) {
            case 'submitted':
                $stats['submitted_count'] = $status['count'];
                break;
            case 'under_review':
                $stats['under_review_count'] = $status['count'];
                break;
            case 'interview_scheduled':
                $stats['interview_scheduled_count'] = $status['count'];
                break;
            case 'interviewed':
                $stats['interviewed_count'] = $status['count'];
                break;
            case 'hired':
                $stats['hired_count'] = $status['count'];
                break;
            case 'rejected':
                $stats['rejected_count'] = $status['count'];
                break;
        }
    }
    
    // FIXED: interviews_count should be user-specific
    $stats['interviews_count'] = $stats['interview_scheduled_count'] + $stats['interviewed_count'];
    
    // Profile views (placeholder)
    $stats['profile_views'] = 47;
    
    error_log("USER-SPECIFIC STATS for seeker_id: $seekerId");
    error_log("Total: {$stats['applications_count']}, Interview_scheduled: {$stats['interview_scheduled_count']}, Interviews_count: {$stats['interviews_count']}");
    
    // ===== STEP 2: RECENT APPLICATIONS (SIMPLE QUERY) =====
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at
        FROM job_applications ja
        WHERE ja.seeker_id = ?
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $recentApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get job details for each application separately
    foreach ($recentApplications as &$app) {
        // Get job info
        $jobStmt = $conn->prepare("SELECT job_title, location, employment_type FROM job_posts WHERE job_id = ?");
        $jobStmt->execute([$app['job_id']]);
        $jobInfo = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($jobInfo) {
            $app['job_title'] = $jobInfo['job_title'];
            $app['location'] = $jobInfo['location'];
            $app['employment_type'] = $jobInfo['employment_type'];
        } else {
            $app['job_title'] = 'Unknown Job';
            $app['location'] = 'Unknown';
            $app['employment_type'] = 'Unknown';
        }
        
        // Get company name
        $companyStmt = $conn->prepare("
            SELECT e.company_name 
            FROM employers e 
            JOIN job_posts jp ON e.employer_id = jp.employer_id 
            WHERE jp.job_id = ?
        ");
        $companyStmt->execute([$app['job_id']]);
        $companyInfo = $companyStmt->fetch(PDO::FETCH_ASSOC);
        $app['company_name'] = $companyInfo['company_name'] ?? 'Unknown Company';
        
        // Format date
        $app['applied_at'] = date('F j, Y', strtotime($app['applied_at']));
    }
    
    error_log("BULLETPROOF: Recent applications found: " . count($recentApplications));
    
    // ===== STEP 3: UPCOMING INTERVIEWS (SIMPLE QUERY) =====
    $stmt = $conn->prepare("
        SELECT 
            i.interview_id,
            i.scheduled_date,
            i.scheduled_time,
            i.interview_type,
            i.meeting_link,
            i.location_address,
            i.interview_status,
            ja.job_id
        FROM interviews i
        JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE ja.seeker_id = ? 
        AND i.scheduled_date >= CURDATE()
        AND i.interview_status IN ('scheduled', 'confirmed')
        ORDER BY i.scheduled_date ASC, i.scheduled_time ASC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $upcomingInterviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get job and company details for each interview separately
    foreach ($upcomingInterviews as &$interview) {
        // Get job info
        $jobStmt = $conn->prepare("SELECT job_title FROM job_posts WHERE job_id = ?");
        $jobStmt->execute([$interview['job_id']]);
        $jobInfo = $jobStmt->fetch(PDO::FETCH_ASSOC);
        $interview['job_title'] = $jobInfo['job_title'] ?? 'Unknown Job';
        
        // Get company name
        $companyStmt = $conn->prepare("
            SELECT e.company_name 
            FROM employers e 
            JOIN job_posts jp ON e.employer_id = jp.employer_id 
            WHERE jp.job_id = ?
        ");
        $companyStmt->execute([$interview['job_id']]);
        $companyInfo = $companyStmt->fetch(PDO::FETCH_ASSOC);
        $interview['company_name'] = $companyInfo['company_name'] ?? 'Unknown Company';
        
        // Format dates/times
        $interview['scheduled_date'] = date('F j, Y', strtotime($interview['scheduled_date']));
        if ($interview['scheduled_time']) {
            $interview['scheduled_time'] = date('g:i A', strtotime($interview['scheduled_time']));
        }
    }
    
    error_log("BULLETPROOF: Upcoming interviews found: " . count($upcomingInterviews));
    
    // ===== STEP 4: SUGGESTED JOBS (SIMPLE QUERY) =====
    $stmt = $conn->prepare("
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.posted_at,
            jp.employer_id
        FROM job_posts jp
        WHERE jp.job_status = 'active'
        AND jp.job_id NOT IN (
            SELECT job_id FROM job_applications WHERE seeker_id = ?
        )
        ORDER BY jp.posted_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $suggestedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get company info for suggested jobs
    foreach ($suggestedJobs as &$job) {
        $companyStmt = $conn->prepare("SELECT company_name, company_logo_path FROM employers WHERE employer_id = ?");
        $companyStmt->execute([$job['employer_id']]);
        $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
        
        $job['company_name'] = $company['company_name'] ?? 'Unknown Company';
        $job['company_logo_path'] = $company['company_logo_path'] ?? null;
        $job['company_logo'] = $job['company_logo_path'] ?? substr($job['company_name'], 0, 2);
        
        // Format date
        $job['posted_at'] = date('F j, Y', strtotime($job['posted_at']));
        
        // Get basic accommodations
        $accomStmt = $conn->prepare("SELECT wheelchair_accessible, flexible_schedule, remote_work_option FROM job_accommodations WHERE job_id = ?");
        $accomStmt->execute([$job['job_id']]);
        $accommodations = $accomStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($accommodations) {
            $job['wheelchair_accessible'] = (bool)$accommodations['wheelchair_accessible'];
            $job['flexible_schedule'] = (bool)$accommodations['flexible_schedule'];
            $job['remote_work_option'] = (bool)$accommodations['remote_work_option'];
        } else {
            $job['wheelchair_accessible'] = false;
            $job['flexible_schedule'] = false;
            $job['remote_work_option'] = false;
        }
    }
    
    error_log("BULLETPROOF: Suggested jobs found: " . count($suggestedJobs));
    
    // ===== STEP 5: GET USER INFO AND PROFILE COMPLETION =====

    // Get user's first name
    $stmt = $conn->prepare("SELECT first_name, last_name FROM job_seekers WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    $userInfo = [
        'first_name' => $userData['first_name'] ?? '',
        'last_name' => $userData['last_name'] ?? '',
    ];

    // Calculate profile completion (matching web logic)
    require_once __DIR__ . '/../../includes/candidate/profile_completion_handler.php';
    $completionData = calculateProfileCompletion($conn, $seekerId);
    $profileCompletion = [
        'percentage' => $completionData['percentage'],
        'sections' => $completionData['sections']
    ];

    error_log("User info: " . json_encode($userInfo));
    error_log("Profile completion: " . $completionData['percentage'] . "%");

    // ===== STEP 6: COMPILE RESPONSE =====
    $responseData = [
        'user_info' => $userInfo,  // 🆕 ADDED
        'profile_completion' => $profileCompletion,  // 🆕 ADDED
        'stats' => $stats,
        'recent_applications' => $recentApplications,
        'upcoming_interviews' => $upcomingInterviews,
        'suggested_jobs' => $suggestedJobs,
        'debug_info' => [
            'seeker_id' => $seekerId,
            'stats_collected' => !empty($stats),
            'applications_found' => count($recentApplications),
            'interviews_found' => count($upcomingInterviews),
            'suggested_jobs_found' => count($suggestedJobs),
            'sql_working' => true
        ]
    ];
    
    error_log("BULLETPROOF: Dashboard data compiled successfully");
    
    ApiResponse::success($responseData, "Dashboard data retrieved successfully");
    
} catch(PDOException $e) {
    error_log("BULLETPROOF Dashboard database error: " . $e->getMessage());
    error_log("BULLETPROOF SQL Error Info: " . json_encode($e->errorInfo ?? []));
    ApiResponse::serverError("Database query failed: " . $e->getMessage());
    
} catch(Exception $e) {
    error_log("BULLETPROOF Dashboard general error: " . $e->getMessage());
    ApiResponse::serverError("API error: " . $e->getMessage());
}
?>