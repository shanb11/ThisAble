<?php
/**
 * Enhanced Job Listings API for ThisAble Mobile - CORRECTED COLUMN NAMES
 * Uses your EXACT job_accommodations table structure
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_enhanced_job_listings.php
 */

require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

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
    
    // Get query parameters with validation
    $search_query = $_GET['search'] ?? '';
    $location = $_GET['location'] ?? '';
    $job_type = $_GET['job_type'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1)); // Ensure minimum 1
    $limit = min(50, max(1, intval($_GET['limit'] ?? 20))); // Between 1-50
    $offset = ($page - 1) * $limit;

    error_log("Enhanced Jobs API - seekerId: $seekerId, page: $page, limit: $limit, offset: $offset");

    $conn = ApiDatabase::getConnection();
    
    // Build WHERE conditions
    $whereConditions = ["jp.job_status = 'active'"];
    $params = [];
    
    if (!empty($search_query)) {
        $whereConditions[] = "(jp.job_title LIKE ? OR jp.job_description LIKE ? OR jp.job_requirements LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($location)) {
        $whereConditions[] = "jp.location LIKE ?";
        $params[] = "%{$location}%";
    }
    
    if (!empty($job_type)) {
        $whereConditions[] = "jp.employment_type = ?";
        $params[] = $job_type;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // CORRECTED: Using your EXACT job_accommodations column names
    $sql = "
        SELECT 
            -- Job details
            jp.job_id,
            jp.employer_id,
            jp.job_title,
            jp.job_description,
            jp.job_requirements,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.posted_at,
            jp.application_deadline,
            jp.views_count,
            jp.applications_count,
            jp.department,
            
            -- Company details
            e.company_name,
            e.company_logo_path,
            e.company_description,
            e.industry,
            
            -- PWD Accommodations (CORRECTED: Using your EXACT column names)
            COALESCE(ja.wheelchair_accessible, 0) as wheelchair_accessible,
            COALESCE(ja.flexible_schedule, 0) as accommodation_flexible_schedule,
            COALESCE(ja.assistive_technology, 0) as assistive_technology,
            COALESCE(ja.remote_work_option, 0) as remote_work_option,
            COALESCE(ja.screen_reader_compatible, 0) as screen_reader_compatible,
            COALESCE(ja.sign_language_interpreter, 0) as sign_language_interpreter,
            COALESCE(ja.modified_workspace, 0) as modified_workspace,
            COALESCE(ja.transportation_support, 0) as transportation_support,
            ja.additional_accommodations,
            
            -- User interaction status
            CASE WHEN app.application_id IS NOT NULL THEN 1 ELSE 0 END as user_applied,
            CASE WHEN sj.saved_id IS NOT NULL THEN 1 ELSE 0 END as user_saved,
            app.application_status,
            COALESCE(app.match_score, 0) as match_score
            
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        LEFT JOIN job_applications app ON jp.job_id = app.job_id AND app.seeker_id = ?
        LEFT JOIN saved_jobs sj ON jp.job_id = sj.job_id AND sj.seeker_id = ?
        WHERE {$whereClause}
        ORDER BY jp.posted_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    // Add seeker_id params at the beginning
    array_unshift($params, $seekerId, $seekerId);
    
    error_log("SQL Query: " . substr($sql, 0, 200) . "...");
    error_log("Params: " . json_encode($params));
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Jobs found: " . count($jobs));
    
    // Format jobs for Flutter
    $formattedJobs = [];
    foreach ($jobs as $job) {
        // Calculate posted time
        $postedDate = new DateTime($job['posted_at']);
        $now = new DateTime();
        $daysAgo = $now->diff($postedDate)->days;
        
        if ($daysAgo == 0) {
            $postedTime = 'Today';
        } elseif ($daysAgo == 1) {
            $postedTime = '1 day ago';
        } elseif ($daysAgo <= 7) {
            $postedTime = $daysAgo . ' days ago';
        } elseif ($daysAgo <= 30) {
            $weeks = floor($daysAgo / 7);
            $postedTime = $weeks . ($weeks == 1 ? ' week ago' : ' weeks ago');
        } else {
            $months = floor($daysAgo / 30);
            $postedTime = $months . ($months == 1 ? ' month ago' : ' months ago');
        }
        
        // Build PWD accommodations array (CORRECTED: Using your exact column names)
        $accommodations = [];
        if ($job['wheelchair_accessible']) $accommodations[] = 'Wheelchair Accessible';
        if ($job['accommodation_flexible_schedule']) $accommodations[] = 'Flexible Schedule';
        if ($job['assistive_technology']) $accommodations[] = 'Assistive Technology';
        if ($job['screen_reader_compatible']) $accommodations[] = 'Screen Reader Compatible';
        if ($job['sign_language_interpreter']) $accommodations[] = 'Sign Language Interpreter';
        if ($job['modified_workspace']) $accommodations[] = 'Modified Workspace';
        if ($job['transportation_support']) $accommodations[] = 'Transportation Support';
        
        // Additional accommodations from text field
        if (!empty($job['additional_accommodations'])) {
            $accommodations[] = 'Additional Accommodations Available';
        }
        
        // Additional features from job_posts table
        $features = [];
        if ($job['remote_work_available']) $features[] = 'Remote Work Available';
        if ($job['remote_work_option']) $features[] = 'Remote Work Option';
        
        $formattedJobs[] = [
            'job_id' => (int)$job['job_id'],
            'title' => $job['job_title'],
            'company' => $job['company_name'],
            'company_logo' => $job['company_logo_path'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'] ?: 'Competitive',
            'department' => $job['department'],
            'description' => $job['job_description'],
            'requirements' => $job['job_requirements'],
            'posted_time' => $postedTime,
            'posted_date' => $job['posted_at'],
            'deadline' => $job['application_deadline'],
            'views' => (int)$job['views_count'],
            'applications' => (int)$job['applications_count'],
            
            // PWD Features - KEY FOR YOUR APP!
            'accommodations' => $accommodations,
            'features' => $features,
            'has_pwd_support' => count($accommodations) > 0,
            
            // User status
            'user_applied' => (bool)$job['user_applied'],
            'user_saved' => (bool)$job['user_saved'],
            'application_status' => $job['application_status'],
            'match_score' => (float)$job['match_score'],
            
            // Company info
            'company_description' => $job['company_description'],
            'industry' => $job['industry'],
            
            // Additional accommodations details
            'additional_accommodations' => $job['additional_accommodations']
        ];
    }
    
    // Get total count for pagination (same fix)
    $countSql = "
        SELECT COUNT(*) as total 
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE {$whereClause}
    ";
    
    // Remove seeker_id params for count query
    $countParams = array_slice($params, 2);
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($countParams);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get REAL statistics from your database (CORRECTED: Using exact column names)
    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT jp.job_id) as total_jobs,
            COUNT(DISTINCT CASE 
                WHEN (ja.wheelchair_accessible = 1 OR ja.flexible_schedule = 1 OR ja.assistive_technology = 1 
                      OR ja.remote_work_option = 1 OR ja.screen_reader_compatible = 1 
                      OR ja.sign_language_interpreter = 1 OR ja.modified_workspace = 1 
                      OR ja.transportation_support = 1 OR ja.additional_accommodations IS NOT NULL) 
                THEN jp.job_id END) as pwd_friendly_jobs,
            COUNT(DISTINCT CASE WHEN jp.remote_work_available = 1 THEN jp.job_id END) as remote_jobs
        FROM job_posts jp
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_status = 'active'
    ");
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Statistics: " . json_encode($stats));
    
    ApiResponse::success([
        'jobs' => $formattedJobs,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total
        ],
        'statistics' => [
            'total_jobs' => (int)$stats['total_jobs'],
            'pwd_friendly' => (int)$stats['pwd_friendly_jobs'],
            'remote_jobs' => (int)$stats['remote_jobs']
        ],
        'filters' => [
            'search' => $search_query,
            'location' => $location,
            'job_type' => $job_type
        ]
    ], "Jobs loaded successfully");
    
} catch (Exception $e) {
    error_log("Enhanced Job Listings Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    ApiResponse::serverError("Failed to load jobs: " . $e->getMessage());
}
?>