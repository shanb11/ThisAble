<?php
/**
 * BULLETPROOF Get Applications List API for ThisAble Mobile
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
    error_log("BULLETPROOF Applications API: seeker_id=$seekerId");

    // Get query parameters
    $status_filter = $_GET['status'] ?? 'all';
    $search_query = $_GET['search'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: BUILD SIMPLE QUERY =====
    $whereConditions = ["ja.seeker_id = ?"];
    $params = [$seekerId];
    
    // Status filter
    if ($status_filter !== 'all') {
        $whereConditions[] = "ja.application_status = ?";
        $params[] = $status_filter;
    }
    
    // Search filter
    if (!empty($search_query)) {
        $whereConditions[] = "(jp.job_title LIKE ? OR e.company_name LIKE ? OR jp.location LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // ===== STEP 2: GET APPLICATIONS (SIMPLE QUERY) =====
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.last_activity,
            ja.resume_id
        FROM job_applications ja
        WHERE {$whereClause}
        ORDER BY ja.applied_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("BULLETPROOF: Found " . count($applications) . " applications");
    
    // ===== STEP 3: GET JOB AND COMPANY INFO FOR EACH APPLICATION =====
    foreach ($applications as &$app) {
        // Get job info
        $jobStmt = $conn->prepare("SELECT job_title, location, employment_type, employer_id FROM job_posts WHERE job_id = ?");
        $jobStmt->execute([$app['job_id']]);
        $jobInfo = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($jobInfo) {
            $app['job_title'] = $jobInfo['job_title'];
            $app['job_location'] = $jobInfo['location'];
            $app['employment_type'] = $jobInfo['employment_type'];
            
            // Get company name
            $companyStmt = $conn->prepare("SELECT company_name FROM employers WHERE employer_id = ?");
            $companyStmt->execute([$jobInfo['employer_id']]);
            $companyInfo = $companyStmt->fetch(PDO::FETCH_ASSOC);
            $app['company_name'] = $companyInfo['company_name'] ?? 'Unknown Company';
        } else {
            $app['job_title'] = 'Unknown Job';
            $app['job_location'] = 'Unknown';
            $app['employment_type'] = 'Unknown';
            $app['company_name'] = 'Unknown Company';
        }
        
        // Add progress percentage based on status
        switch ($app['application_status']) {
            case 'submitted':
                $app['progress_percentage'] = 20;
                break;
            case 'under_review':
                $app['progress_percentage'] = 40;
                break;
            case 'shortlisted':
                $app['progress_percentage'] = 60;
                break;
            case 'interview_scheduled':
                $app['progress_percentage'] = 60;
                break;
            case 'interviewed':
                $app['progress_percentage'] = 80;
                break;
            case 'hired':
                $app['progress_percentage'] = 100;
                break;
            default:
                $app['progress_percentage'] = 20;
        }
        
        // Check if can withdraw
        $app['can_withdraw'] = in_array($app['application_status'], ['submitted', 'under_review']) ? 1 : 0;
        
        // Format dates
        $app['applied_at'] = date('F j, Y', strtotime($app['applied_at']));
        $app['last_activity'] = date('F j, Y', strtotime($app['last_activity']));
        
        // Get application timeline/history
        $timelineStmt = $conn->prepare("
            SELECT 
                changed_at as date,
                new_status as status,
                notes,
                CASE 
                    WHEN new_status = 'submitted' THEN 'Application Submitted'
                    WHEN new_status = 'under_review' THEN 'Application Reviewed'
                    WHEN new_status = 'shortlisted' THEN 'Shortlisted'
                    WHEN new_status = 'interview_scheduled' THEN 'Interview Scheduled'
                    WHEN new_status = 'interviewed' THEN 'Interview Completed'
                    WHEN new_status = 'hired' THEN 'Job Offer Received'
                    WHEN new_status = 'rejected' THEN 'Application Rejected'
                    ELSE 'Status Updated'
                END as title
            FROM application_status_history
            WHERE application_id = ?
            ORDER BY changed_at ASC
        ");
        $timelineStmt->execute([$app['application_id']]);
        $app['timeline'] = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format timeline dates
        foreach ($app['timeline'] as &$timeline_item) {
            $timeline_item['date'] = date('M j, Y', strtotime($timeline_item['date']));
        }
    }
    
    // ===== STEP 4: GET TOTAL COUNT =====
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total_count
        FROM job_applications ja
        LEFT JOIN job_posts jp ON ja.job_id = jp.job_id
        LEFT JOIN employers e ON jp.employer_id = e.employer_id
        WHERE {$whereClause}
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // ===== STEP 5: GET STATUS STATS =====
    $statsStmt = $conn->prepare("
        SELECT 
            application_status,
            COUNT(*) as count
        FROM job_applications 
        WHERE seeker_id = ?
        GROUP BY application_status
    ");
    $statsStmt->execute([$seekerId]);
    $statusStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array
    $stats = ['all' => $totalCount];
    foreach ($statusStats as $stat) {
        $stats[$stat['application_status']] = $stat['count'];
    }
    
    // ===== STEP 6: COMPILE RESPONSE =====
    $responseData = [
        'applications' => $applications,
        'pagination' => [
            'current_page' => $page,
            'total_count' => $totalCount,
            'per_page' => $limit,
            'total_pages' => ceil($totalCount / $limit)
        ],
        'filter_stats' => $stats,
        'debug_info' => [
            'seeker_id' => $seekerId,
            'applications_found' => count($applications),
            'where_clause' => $whereClause,
            'sql_working' => true
        ]
    ];
    
    error_log("BULLETPROOF: Applications data compiled successfully");
    
    ApiResponse::success($responseData, "Applications retrieved successfully");
    
} catch(PDOException $e) {
    error_log("BULLETPROOF Applications database error: " . $e->getMessage());
    error_log("BULLETPROOF SQL Error Info: " . json_encode($e->errorInfo ?? []));
    ApiResponse::serverError("Database query failed: " . $e->getMessage());
    
} catch(Exception $e) {
    error_log("BULLETPROOF Applications general error: " . $e->getMessage());
    ApiResponse::serverError("API error: " . $e->getMessage());
}
?>