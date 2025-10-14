<?php
/**
 * Get Saved Jobs API for ThisAble Mobile
 * Returns list of saved jobs for candidate
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_saved_jobs.php
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
    
    $conn = ApiDatabase::getConnection();
    
    // Get saved jobs with job details
    $stmt = $conn->prepare("
        SELECT 
            sj.saved_id,
            sj.job_id,
            sj.saved_at,
            jp.job_title,
            jp.job_description,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.posted_at,
            jp.application_deadline,
            jp.remote_work_available,
            jp.flexible_schedule,
            e.company_name,
            e.company_logo_path,
            e.company_description
        FROM saved_jobs sj
        INNER JOIN job_posts jp ON sj.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE sj.seeker_id = ?
        AND jp.job_status = 'active'
        ORDER BY sj.saved_at DESC
    ");
    
    $stmt->execute([$seekerId]);
    $savedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formattedJobs = [];
    foreach ($savedJobs as $job) {
        $formattedJobs[] = [
            'saved_id' => $job['saved_id'],
            'job_id' => (int)$job['job_id'],
            'job_title' => $job['job_title'],
            'job_description' => $job['job_description'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'],
            'posted_at' => $job['posted_at'],
            'application_deadline' => $job['application_deadline'],
            'remote_work_available' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'company_name' => $job['company_name'],
            'company_logo_path' => $job['company_logo_path'],
            'company_description' => $job['company_description'],
            'saved_at' => $job['saved_at']
        ];
    }
    
    ApiResponse::success($formattedJobs, "Saved jobs retrieved successfully");
    
} catch (Exception $e) {
    error_log("Get Saved Jobs Error: " . $e->getMessage());
    ApiResponse::serverError("Failed to load saved jobs: " . $e->getMessage());
}
?>