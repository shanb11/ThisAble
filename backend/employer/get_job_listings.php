<?php
/**
 * SAFE Get Job Listings API - Minimal version to prevent crashes
 * Replace your get_job_listings.php with this SAFE version
 */

header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

try {
    // Simple session check - no function calls to prevent loops
    if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized access. Please log in.',
            'message' => 'Unauthorized access. Please log in.',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }

    // Include database connection
    require_once('../db.php');
    
    $employer_id = $_SESSION['employer_id'];
    
    // Get request parameters with defaults
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

    // Build simple query
    $where_conditions = ['jp.employer_id = :employer_id'];
    $params = ['employer_id' => $employer_id];

    // Add search if provided
    if (!empty($search)) {
        $where_conditions[] = 'jp.job_title LIKE :search';
        $params['search'] = '%' . $search . '%';
    }

    // Add status filter if not 'all'
    if ($status_filter !== 'all') {
        $where_conditions[] = 'jp.job_status = :status';
        $params['status'] = $status_filter;
    }

    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    
    // Order clause
    $order_clause = 'ORDER BY jp.created_at DESC';
    if ($sort_by === 'applicants') {
        $order_clause = 'ORDER BY jp.applications_count DESC';
    }

    // Simple query - no JOINs to prevent complexity
    $sql = "
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.department,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.job_description,
            jp.job_requirements,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.job_status,
            jp.posted_at,
            jp.created_at,
            jp.applications_count,
            jp.views_count
        FROM job_posts jp
        $where_clause
        $order_clause
        LIMIT 20
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format jobs simply
    $formatted_jobs = [];
    foreach ($jobs as $job) {
        $posted_at = $job['posted_at'] ? date('Y-m-d', strtotime($job['posted_at'])) : date('Y-m-d', strtotime($job['created_at']));
        
        $formatted_jobs[] = [
            'job_id' => $job['job_id'],
            'job_title' => $job['job_title'],
            'department' => $job['department'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'],
            'job_description' => $job['job_description'],
            'job_requirements' => $job['job_requirements'],
            'remote_work_available' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'job_status' => $job['job_status'],
            'posted_at' => $posted_at,
            'applications_count' => (int)$job['applications_count'],
            'views_count' => (int)$job['views_count'],
            'accommodations' => [], // Empty for now
            'company_name' => $_SESSION['company_name'] ?? 'Company'
        ];
    }

    // Simple statistics
    $stats_sql = "SELECT COUNT(*) as total_jobs FROM job_posts WHERE employer_id = :employer_id";
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->execute(['employer_id' => $employer_id]);
    $total_jobs = $stats_stmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    // Success response
    echo json_encode([
        'success' => true,
        'data' => [
            'jobs' => $formatted_jobs,
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 1,
                'total_jobs' => (int)$total_jobs,
                'per_page' => 20,
                'has_next' => false,
                'has_prev' => false
            ],
            'statistics' => [
                'total_jobs' => (int)$total_jobs,
                'active_jobs' => 0,
                'draft_jobs' => 0,
                'closed_jobs' => 0,
                'paused_jobs' => 0,
                'total_applications' => 0,
                'total_views' => 0
            ],
            'filters' => [
                'search' => $search,
                'status' => $status_filter,
                'sort_by' => $sort_by
            ]
        ],
        'message' => 'Job listings retrieved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Safe API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => 'Unable to fetch job listings',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>