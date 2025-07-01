<?php
// backend/employer/get_employer_jobs.php
// API to fetch employer's jobs for filter dropdown

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get only jobs that have applications (for filter purposes)
    $filter_type = $_GET['filter'] ?? 'with_applications'; // 'all' or 'with_applications'
    
    if ($filter_type === 'with_applications') {
        $sql = "SELECT DISTINCT
                    jp.job_id,
                    jp.job_title,
                    jp.employment_type,
                    jp.job_status,
                    jp.created_at,
                    COUNT(ja.application_id) as application_count,
                    COUNT(CASE WHEN ja.application_status = 'submitted' THEN 1 END) as new_applications
                FROM job_posts jp
                INNER JOIN job_applications ja ON jp.job_id = ja.job_id
                WHERE jp.employer_id = :employer_id
                AND jp.job_status IN ('active', 'paused')
                GROUP BY jp.job_id, jp.job_title, jp.employment_type, jp.job_status, jp.created_at
                ORDER BY jp.created_at DESC";
    } else {
        // Get all jobs
        $sql = "SELECT 
                    jp.job_id,
                    jp.job_title,
                    jp.employment_type,
                    jp.job_status,
                    jp.created_at,
                    COALESCE(app_counts.application_count, 0) as application_count,
                    COALESCE(app_counts.new_applications, 0) as new_applications
                FROM job_posts jp
                LEFT JOIN (
                    SELECT 
                        job_id,
                        COUNT(*) as application_count,
                        COUNT(CASE WHEN application_status = 'submitted' THEN 1 END) as new_applications
                    FROM job_applications
                    GROUP BY job_id
                ) app_counts ON jp.job_id = app_counts.job_id
                WHERE jp.employer_id = :employer_id
                AND jp.job_status IN ('active', 'paused', 'draft')
                ORDER BY jp.created_at DESC";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':employer_id', $employer_id);
    $stmt->execute();
    
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    foreach ($jobs as &$job) {
        $job['created_at_formatted'] = date('M j, Y', strtotime($job['created_at']));
        $job['display_name'] = $job['job_title'] . ' (' . $job['application_count'] . ' applicants)';
    }
    
    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'total_jobs' => count($jobs),
        'filter_type' => $filter_type
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}