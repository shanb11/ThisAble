<?php
/**
 * Get Jobs List API for ThisAble Mobile
 * Returns: job listings with PWD accommodations and filters
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
    error_log("Jobs List API: seeker_id=$seekerId");

    // Get query parameters
    $search_query = $_GET['search'] ?? '';
    $job_types = $_GET['job_types'] ?? ''; // comma-separated
    $work_modes = $_GET['work_modes'] ?? ''; // comma-separated  
    $accessibility_features = $_GET['accessibility'] ?? ''; // comma-separated
    $experience_levels = $_GET['experience'] ?? ''; // comma-separated
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== BUILD QUERY =====
    $whereConditions = [
        "jp.job_status = 'active'",
        "jp.application_deadline >= CURDATE()"
    ];
    $params = [];
    
    // Search filter
    if (!empty($search_query)) {
        $whereConditions[] = "(jp.job_title LIKE ? OR jp.job_description LIKE ? OR e.company_name LIKE ? OR jp.location LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Job type filter
    if (!empty($job_types)) {
        $jobTypesArray = explode(',', $job_types);
        $jobTypePlaceholders = str_repeat('?,', count($jobTypesArray) - 1) . '?';
        $whereConditions[] = "jp.employment_type IN ($jobTypePlaceholders)";
        $params = array_merge($params, $jobTypesArray);
    }
    
    // Work mode filter (based on remote_work_available and flexible_schedule)
    if (!empty($work_modes)) {
        $workModesArray = explode(',', $work_modes);
        $workModeConditions = [];
        
        foreach ($workModesArray as $mode) {
            switch (strtolower($mode)) {
                case 'remote':
                    $workModeConditions[] = "jp.remote_work_available = 1";
                    break;
                case 'hybrid':
                    $workModeConditions[] = "(jp.remote_work_available = 1 AND jp.flexible_schedule = 1)";
                    break;
                case 'onsite':
                case 'on-site':
                    $workModeConditions[] = "jp.remote_work_available = 0";
                    break;
            }
        }
        
        if (!empty($workModeConditions)) {
            $whereConditions[] = "(" . implode(' OR ', $workModeConditions) . ")";
        }
    }
    
    // Accessibility features filter
    if (!empty($accessibility_features)) {
        $accessibilityArray = explode(',', $accessibility_features);
        $accessibilityConditions = [];
        
        foreach ($accessibilityArray as $feature) {
            switch (strtolower(str_replace('-', '_', $feature))) {
                case 'flexible_schedule':
                    $accessibilityConditions[] = "ja.flexible_schedule = 1";
                    break;
                case 'assistive_tech':
                case 'assistive_technology':
                    $accessibilityConditions[] = "ja.assistive_technology = 1";
                    break;
                case 'accessible_office':
                    $accessibilityConditions[] = "ja.wheelchair_accessible = 1";
                    break;
                case 'transportation':
                    $accessibilityConditions[] = "ja.transportation_support = 1";
                    break;
                case 'remote_work':
                    $accessibilityConditions[] = "ja.remote_work_option = 1";
                    break;
            }
        }
        
        if (!empty($accessibilityConditions)) {
            $whereConditions[] = "(" . implode(' OR ', $accessibilityConditions) . ")";
        }
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // ===== GET JOBS =====
    $stmt = $conn->prepare("
        SELECT 
            jp.job_id,
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
            e.company_name,
            e.company_logo_path,
            e.company_description,
            ja.wheelchair_accessible,
            ja.flexible_schedule as accommodation_flexible_schedule,
            ja.assistive_technology,
            ja.remote_work_option,
            ja.screen_reader_compatible,
            ja.sign_language_interpreter,
            ja.modified_workspace,
            ja.transportation_support,
            ja.additional_accommodations,
            CASE WHEN sj.saved_id IS NOT NULL THEN 1 ELSE 0 END as is_saved,
            CASE WHEN existing_app.application_id IS NOT NULL THEN 1 ELSE 0 END as has_applied
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        LEFT JOIN saved_jobs sj ON jp.job_id = sj.job_id AND sj.seeker_id = ?
        LEFT JOIN job_applications existing_app ON jp.job_id = existing_app.job_id AND existing_app.seeker_id = ?
        WHERE {$whereClause}
        ORDER BY jp.posted_at DESC
        LIMIT ? OFFSET ?
    ");
    
    // Add seeker_id for saved jobs and applications check
    array_unshift($params, $seekerId, $seekerId);
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== PROCESS PWD ACCOMMODATIONS =====
    foreach ($jobs as &$job) {
        $accommodations = [];
        
        if ($job['wheelchair_accessible']) $accommodations[] = 'Wheelchair Accessible';
        if ($job['accommodation_flexible_schedule']) $accommodations[] = 'Flexible Schedule';
        if ($job['assistive_technology']) $accommodations[] = 'Assistive Technology';
        if ($job['remote_work_option']) $accommodations[] = 'Remote Work Option';
        if ($job['screen_reader_compatible']) $accommodations[] = 'Screen Reader Compatible';
        if ($job['sign_language_interpreter']) $accommodations[] = 'Sign Language Interpreter';
        if ($job['modified_workspace']) $accommodations[] = 'Modified Workspace';
        if ($job['transportation_support']) $accommodations[] = 'Transportation Support';
        
        // Additional accommodations from JSON
        if ($job['additional_accommodations']) {
            $additional = json_decode($job['additional_accommodations'], true);
            if (is_array($additional)) {
                $accommodations = array_merge($accommodations, $additional);
            }
        }
        
        $job['pwd_accommodations'] = $accommodations;
        
        // Clean up individual accommodation fields
        unset($job['wheelchair_accessible'], $job['accommodation_flexible_schedule'], 
              $job['assistive_technology'], $job['remote_work_option'], 
              $job['screen_reader_compatible'], $job['sign_language_interpreter'],
              $job['modified_workspace'], $job['transportation_support'], 
              $job['additional_accommodations']);
        
        // Format dates
        $job['posted_at'] = date('F j, Y', strtotime($job['posted_at']));
        $job['application_deadline'] = $job['application_deadline'] ? date('F j, Y', strtotime($job['application_deadline'])) : null;
        
        // Add company logo placeholder
        $job['company_logo'] = $job['company_logo_path'] ?? substr($job['company_name'], 0, 2);
        
        // Determine work type based on remote and flexible options
        if ($job['remote_work_available'] && $job['flexible_schedule']) {
            $job['work_type'] = 'Hybrid';
        } elseif ($job['remote_work_available']) {
            $job['work_type'] = 'Remote';
        } else {
            $job['work_type'] = 'On-site';
        }
        
        // Convert boolean values
        $job['is_saved'] = (bool)$job['is_saved'];
        $job['has_applied'] = (bool)$job['has_applied'];
        $job['is_remote'] = (bool)$job['remote_work_available'];
        
        // Experience level (placeholder - can be enhanced with job requirements analysis)
        $job['experience_level'] = 'Mid'; // Default, can be determined from job_requirements
    }
    
    // ===== GET TOTAL COUNT =====
    $countParams = array_slice($params, 2, -2); // Remove seeker_id (first 2) and limit/offset (last 2)
    $countStmt = $conn->prepare("
        SELECT COUNT(DISTINCT jp.job_id) as total_count
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE {$whereClause}
    ");
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // ===== GET FILTER STATS =====
    $filterStatsStmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT jp.job_id) as total_jobs,
            SUM(CASE WHEN jp.remote_work_available = 1 THEN 1 ELSE 0 END) as remote_jobs,
            SUM(CASE WHEN EXISTS(SELECT 1 FROM saved_jobs WHERE job_id = jp.job_id AND seeker_id = ?) THEN 1 ELSE 0 END) as saved_jobs
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_status = 'active' AND jp.application_deadline >= CURDATE()
    ");
    $filterStatsStmt->execute([$seekerId]);
    $filterStats = $filterStatsStmt->fetch(PDO::FETCH_ASSOC);
    
    // ===== COMPILE RESPONSE =====
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
            'saved' => intval($filterStats['saved_jobs'])
        ]
    ];
    
    ApiResponse::success($responseData, "Jobs retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Jobs list database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Jobs list error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while retrieving jobs");
}
?>