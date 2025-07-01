<?php
/**
 * Job Actions API for ThisAble Mobile
 * Handles: save/unsave jobs, apply to jobs
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow POST requests
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
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $jobId = intval($input['job_id'] ?? 0);
    
    if (empty($action) || $jobId <= 0) {
        ApiResponse::validationError(['action' => 'Action and job_id are required']);
    }
    
    error_log("Job Actions API: seeker_id=$seekerId, action=$action, job_id=$jobId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    switch ($action) {
        case 'save_job':
            // Check if job exists and is active
            $stmt = $conn->prepare("SELECT job_id FROM job_posts WHERE job_id = ? AND job_status = 'active'");
            $stmt->execute([$jobId]);
            if (!$stmt->fetch()) {
                ApiResponse::error("Job not found or inactive", 404);
            }
            
            // Check if already saved
            $stmt = $conn->prepare("SELECT saved_id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
            $stmt->execute([$seekerId, $jobId]);
            if ($stmt->fetch()) {
                ApiResponse::error("Job already saved", 400);
            }
            
            // Save the job
            $stmt = $conn->prepare("INSERT INTO saved_jobs (seeker_id, job_id, saved_at) VALUES (?, ?, NOW())");
            if ($stmt->execute([$seekerId, $jobId])) {
                ApiResponse::success(['saved' => true], "Job saved successfully");
            } else {
                ApiResponse::serverError("Failed to save job");
            }
            break;
            
        case 'unsave_job':
            // Remove from saved jobs
            $stmt = $conn->prepare("DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
            $affectedRows = $stmt->execute([$seekerId, $jobId]);
            
            if ($stmt->rowCount() > 0) {
                ApiResponse::success(['saved' => false], "Job removed from saved");
            } else {
                ApiResponse::error("Job was not saved", 400);
            }
            break;
            
        case 'apply_job':
            // Get additional application data
            $coverLetter = $input['cover_letter'] ?? '';
            $resumeId = intval($input['resume_id'] ?? 0);
            $accessibilityNeeds = $input['accessibility_needs'] ?? '';
            
            // Check if job exists and is active
            $stmt = $conn->prepare("
                SELECT job_id, application_deadline 
                FROM job_posts 
                WHERE job_id = ? AND job_status = 'active'
            ");
            $stmt->execute([$jobId]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$job) {
                ApiResponse::error("Job not found or inactive", 404);
            }
            
            // Check if deadline has passed
            if ($job['application_deadline'] && $job['application_deadline'] < date('Y-m-d')) {
                ApiResponse::error("Application deadline has passed", 400);
            }
            
            // Check if already applied
            $stmt = $conn->prepare("SELECT application_id FROM job_applications WHERE seeker_id = ? AND job_id = ?");
            $stmt->execute([$seekerId, $jobId]);
            if ($stmt->fetch()) {
                ApiResponse::error("Already applied to this job", 400);
            }
            
            // Get current resume if not specified
            if ($resumeId <= 0) {
                $stmt = $conn->prepare("SELECT resume_id FROM resumes WHERE seeker_id = ? AND is_current = 1 LIMIT 1");
                $stmt->execute([$seekerId]);
                $resume = $stmt->fetch(PDO::FETCH_ASSOC);
                $resumeId = $resume['resume_id'] ?? null;
            }
            
            $conn->beginTransaction();
            
            try {
                // Insert application
                $stmt = $conn->prepare("
                    INSERT INTO job_applications 
                    (job_id, seeker_id, resume_id, cover_letter, application_status, applied_at, candidate_notes) 
                    VALUES (?, ?, ?, ?, 'submitted', NOW(), ?)
                ");
                $stmt->execute([$jobId, $seekerId, $resumeId, $coverLetter, $accessibilityNeeds]);
                $applicationId = $conn->lastInsertId();
                
                // Insert into application status history
                $stmt = $conn->prepare("
                    INSERT INTO application_status_history 
                    (application_id, new_status, notes, changed_at) 
                    VALUES (?, 'submitted', 'Application submitted successfully', NOW())
                ");
                $stmt->execute([$applicationId]);
                
                // Update job applications count
                $stmt = $conn->prepare("
                    UPDATE job_posts 
                    SET applications_count = applications_count + 1 
                    WHERE job_id = ?
                ");
                $stmt->execute([$jobId]);
                
                $conn->commit();
                
                ApiResponse::success([
                    'application_id' => $applicationId,
                    'applied' => true
                ], "Application submitted successfully");
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'share_job':
            // Log job share activity (could be used for analytics)
            $platform = $input['platform'] ?? 'unknown';
            
            // For now, just return success - you could log this for analytics
            ApiResponse::success(['shared' => true], "Job share link generated");
            break;
            
        default:
            ApiResponse::validationError(['action' => 'Invalid action specified']);
    }
    
} catch(PDOException $e) {
    error_log("Job actions database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Job actions error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while processing job action");
}
?>