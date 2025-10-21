<?php
/**
 * Get Jobs Statistics API for ThisAble Mobile
 * Returns job statistics for the jobs screen
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_jobs_stats.php
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
    
    $conn = ApiDatabase::getConnection();
    
    // Get total active jobs
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM job_posts WHERE job_status = 'active'");
    $stmt->execute();
    $totalJobs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get PWD friendly jobs (jobs with accommodations)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT jp.job_id) as pwd_friendly
        FROM job_posts jp
        INNER JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_status = 'active'
    ");
    $stmt->execute();
    $pwdFriendlyJobs = $stmt->fetch(PDO::FETCH_ASSOC)['pwd_friendly'];
    
    // Get remote jobs
    $stmt = $conn->prepare("
        SELECT COUNT(*) as remote 
        FROM job_posts 
        WHERE job_status = 'active' 
        AND (remote_work_available = 1 OR location LIKE '%remote%' OR employment_type LIKE '%remote%')
    ");
    $stmt->execute();
    $remoteJobs = $stmt->fetch(PDO::FETCH_ASSOC)['remote'];
    
    // Get today's new jobs
    $stmt = $conn->prepare("
        SELECT COUNT(*) as today_new 
        FROM job_posts 
        WHERE job_status = 'active' 
        AND DATE(posted_at) = CURDATE()
    ");
    $stmt->execute();
    $todayNewJobs = $stmt->fetch(PDO::FETCH_ASSOC)['today_new'];
    
    // Get this week's new jobs
    $stmt = $conn->prepare("
        SELECT COUNT(*) as week_new 
        FROM job_posts 
        WHERE job_status = 'active' 
        AND posted_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->execute();
    $weekNewJobs = $stmt->fetch(PDO::FETCH_ASSOC)['week_new'];
    
    $statsData = [
        'total' => (int)$totalJobs,
        'pwd_friendly' => (int)$pwdFriendlyJobs,
        'remote' => (int)$remoteJobs,
        'today_new' => (int)$todayNewJobs,
        'week_new' => (int)$weekNewJobs,
        'last_updated' => date('Y-m-d H:i:s')
    ];
    
    ApiResponse::success($statsData, "Job statistics retrieved successfully");
    
} catch (Exception $e) {
    error_log("Get Jobs Stats Error: " . $e->getMessage());
    ApiResponse::serverError("Failed to load job statistics: " . $e->getMessage());
}
?>