<?php
/**
 * BULLETPROOF Get Jobs List API for ThisAble Mobile
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
    error_log("BULLETPROOF Jobs List API: seeker_id=$seekerId");

    // Get query parameters
    $search_query = $_GET['search'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: GET BASIC JOBS (SIMPLE QUERY) =====
    $whereConditions = ["jp.job_status = 'active'"];
    $params = [];
    
    // Simple search filter
    if (!empty($search_query)) {
        $whereConditions[] = "(jp.job_title LIKE ? OR jp.job_description LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Simple query for jobs
    $stmt = $conn->prepare("
        SELECT 
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
            jp.job_status
        FROM job_posts jp
        WHERE {$whereClause}
        ORDER BY jp.posted_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("BULLETPROOF: Found " . count($jobs) . " jobs");
    
    // ===== STEP 2: GET COMPANY INFO FOR EACH JOB (SEPARATE QUERIES) =====
    foreach ($jobs as &$job) {
        // Get company info
        $companyStmt = $conn->prepare("SELECT company_name, company_logo_path, company_description FROM employers WHERE employer_id = ?");
        $companyStmt->execute([$job['employer_id']]);
        $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($company) {
            $job['company_name'] = $company['company_name'];
            $job['company_logo_path'] = $company['company_logo_path'];
            $job['company_description'] = $company['company_description'];
        } else {
            $job['company_name'] = 'Unknown Company';
            $job['company_logo_path'] = null;
            $job['company_description'] = '';
        }
        
        // Check if user saved this job
        $savedStmt = $conn->prepare("SELECT saved_id FROM saved_jobs WHERE job_id = ? AND seeker_id = ?");
        $savedStmt->execute([$job['job_id'], $seekerId]);
        $job['is_saved'] = $savedStmt->fetch() ? true : false;
        
        // Check if user applied to this job
        $appliedStmt = $conn->prepare("SELECT application_id FROM job_applications WHERE job_id = ? AND seeker_id = ?");
        $appliedStmt->execute([$job['job_id'], $seekerId]);
        $job['has_applied'] = $appliedStmt->fetch() ? true : false;
        
        // Get job accommodations
        $accomStmt = $conn->prepare("SELECT * FROM job_accommodations WHERE job_id = ?");
        $accomStmt->execute([$job['job_id']]);
        $accommodations_data = $accomStmt->fetch(PDO::FETCH_ASSOC);
        
        $pwd_accommodations = [];
        if ($accommodations_data) {
            if ($accommodations_data['wheelchair_accessible']) $pwd_accommodations[] = 'Wheelchair Accessible';
            if ($accommodations_data['flexible_schedule']) $pwd_accommodations[] = 'Flexible Schedule';
            if ($accommodations_data['assistive_technology']) $pwd_accommodations[] = 'Assistive Technology';
            if ($accommodations_data['remote_work_option']) $pwd_accommodations[] = 'Remote Work Option';
            if ($accommodations_data['screen_reader_compatible']) $pwd_accommodations[] = 'Screen Reader Compatible';
            if ($accommodations_data['sign_language_interpreter']) $pwd_accommodations[] = 'Sign Language Interpreter';
            if ($accommodations_data['modified_workspace']) $pwd_accommodations[] = 'Modified Workspace';
            if ($accommodations_data['transportation_support']) $pwd_accommodations[] = 'Transportation Support';
        }
        $job['pwd_accommodations'] = $pwd_accommodations;
        
        // Format dates
        $job['posted_at'] = date('F j, Y', strtotime($job['posted_at']));
        $job['application_deadline'] = $job['application_deadline'] ? date('F j, Y', strtotime($job['application_deadline'])) : null;
        
        // Add company logo placeholder
        $job['company_logo'] = $job['company_logo_path'] ?? substr($job['company_name'], 0, 2);
        
        // Determine work type
        if ($job['remote_work_available'] && $job['flexible_schedule']) {
            $job['work_type'] = 'Hybrid';
        } elseif ($job['remote_work_available']) {
            $job['work_type'] = 'Remote';
        } else {
            $job['work_type'] = 'On-site';
        }
        
        // Convert boolean values
        $job['is_remote'] = (bool)$job['remote_work_available'];
        $job['experience_level'] = 'Mid'; // Default
        
        // Check deadline status
        if ($job['application_deadline']) {
            $deadlineDate = strtotime($job['application_deadline']);
            $today = strtotime(date('Y-m-d'));
            $job['deadline_passed'] = $deadlineDate < $today;
        } else {
            $job['deadline_passed'] = false;
        }
    }
    
    // ===== STEP 3: GET TOTAL COUNT =====
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total_count
        FROM job_posts jp
        WHERE {$whereClause}
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // ===== STEP 4: GET SIMPLE FILTER STATS =====
    $statsStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN remote_work_available = 1 THEN 1 ELSE 0 END) as remote_jobs
        FROM job_posts
        WHERE job_status = 'active'
    ");
    $statsStmt->execute();
    $filterStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get saved jobs count
    $savedStmt = $conn->prepare("SELECT COUNT(*) as saved_count FROM saved_jobs WHERE seeker_id = ?");
    $savedStmt->execute([$seekerId]);
    $savedCount = $savedStmt->fetch(PDO::FETCH_ASSOC)['saved_count'];
    
    // ===== STEP 5: COMPILE RESPONSE =====
    $responseData = [
        'jobs' => $jobs,
        'pagination' => [
            'current_page' => $page,
            'total_count' => $totalCount,
            'per_page' => $limit,
            'total_pages' => ceil($totalCount / $limit)
        ],
        'filter_stats' => [
            'total' => intval($filterStats['total_jobs']),
            'remote' => intval($filterStats['remote_jobs']),
            'saved' => intval($savedCount)
        ],
        'debug_info' => [
            'seeker_id' => $seekerId,
            'jobs_found' => count($jobs),
            'where_clause' => $whereClause,
            'sql_working' => true
        ]
    ];
    
    error_log("BULLETPROOF: Response compiled successfully");
    
    ApiResponse::success($responseData, "Jobs retrieved successfully");
    
} catch(PDOException $e) {
    error_log("BULLETPROOF Jobs database error: " . $e->getMessage());
    error_log("BULLETPROOF SQL Error Info: " . json_encode($e->errorInfo ?? []));
    ApiResponse::serverError("Database query failed: " . $e->getMessage());
    
} catch(Exception $e) {
    error_log("BULLETPROOF Jobs general error: " . $e->getMessage());
    ApiResponse::serverError("API error: " . $e->getMessage());
}
?>