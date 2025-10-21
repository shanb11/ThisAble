<?php
/**
 * Toggle Save Job API for ThisAble Mobile
 * Save/unsave jobs for candidates
 * File: C:\xampp\htdocs\ThisAble\api\candidate\toggle_save_job.php
 */

require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['job_id'])) {
        ApiResponse::validationError(['job_id' => 'Job ID is required']);
    }
    
    $jobId = (int)$input['job_id'];
    
    $conn = ApiDatabase::getConnection();
    
    // Check if job exists and is active
    $jobStmt = $conn->prepare("SELECT job_id FROM job_posts WHERE job_id = ? AND job_status = 'active'");
    $jobStmt->execute([$jobId]);
    
    if (!$jobStmt->fetch()) {
        ApiResponse::error("Job not found or inactive", 404);
    }
    
    // Check if job is already saved
    $checkStmt = $conn->prepare("SELECT saved_id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
    $checkStmt->execute([$seekerId, $jobId]);
    $existingSave = $checkStmt->fetch();
    
    if ($existingSave) {
        // Remove from saved jobs
        $deleteStmt = $conn->prepare("DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
        $deleteStmt->execute([$seekerId, $jobId]);
        
        ApiResponse::success([
            'saved' => false,
            'message' => 'Job removed from saved jobs'
        ]);
    } else {
        // Add to saved jobs
        $insertStmt = $conn->prepare("INSERT INTO saved_jobs (seeker_id, job_id, saved_at) VALUES (?, ?, NOW())");
        $insertStmt->execute([$seekerId, $jobId]);
        
        ApiResponse::success([
            'saved' => true,
            'message' => 'Job saved successfully'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Toggle Save Job Error: " . $e->getMessage());
    ApiResponse::serverError("Failed to save/unsave job: " . $e->getMessage());
}
?>