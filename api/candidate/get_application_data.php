<?php
/**
 * Get Application Data API
 * Returns data needed to apply for a job (resume list, match score, personalization tips)
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_application_data.php
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
    $jobId = intval($_GET['job_id'] ?? 0);
    
    if ($jobId <= 0) {
        ApiResponse::error("Job ID is required", 400);
    }
    
    error_log("Application Data API: seeker_id=$seekerId, job_id=$jobId");

    $conn = ApiDatabase::getConnection();
    
    // === STEP 1: CHECK IF JOB EXISTS AND IS ACTIVE ===
    $stmt = $conn->prepare("
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.job_description,
            jp.job_requirements,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.application_deadline,
            e.company_name,
            e.company_logo_path
        FROM job_posts jp
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_id = ? AND jp.job_status = 'active'
    ");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        ApiResponse::error("Job not found or inactive", 404);
    }
    
    // === STEP 2: CHECK IF USER ALREADY APPLIED ===
    $stmt = $conn->prepare("SELECT application_id FROM job_applications WHERE seeker_id = ? AND job_id = ?");
    $stmt->execute([$seekerId, $jobId]);
    $alreadyApplied = $stmt->fetch();
    
    if ($alreadyApplied) {
        ApiResponse::success([
            'already_applied' => true,
            'job' => $job
        ], "Already applied to this job");
    }
    
    // === STEP 3: GET USER'S RESUMES ===
    $stmt = $conn->prepare("
        SELECT 
            resume_id,
            file_name,
            file_path,
            file_size,
            file_type,
            upload_date,
            is_current
        FROM resumes
        WHERE seeker_id = ?
        ORDER BY is_current DESC, upload_date DESC
    ");
    $stmt->execute([$seekerId]);
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format resumes
    foreach ($resumes as &$resume) {
        $resume['upload_date'] = date('F j, Y', strtotime($resume['upload_date']));
        $resume['file_size_formatted'] = number_format($resume['file_size'] / 1024, 2) . ' KB';
        $resume['is_current'] = (bool)$resume['is_current'];
    }
    
    // Get current/recommended resume
    $currentResume = null;
    foreach ($resumes as $resume) {
        if ($resume['is_current']) {
            $currentResume = $resume;
            break;
        }
    }
    
    // If no current resume, use most recent
    if (!$currentResume && count($resumes) > 0) {
        $currentResume = $resumes[0];
    }
    
    // === STEP 4: CALCULATE MATCH SCORE (Simple version for now) ===
    $matchScore = 75; // Default match score
    
    if ($currentResume) {
        // You can implement more sophisticated matching later
        // For now, basic match based on resume existence
        $matchScore = 80;
    }
    
    // === STEP 5: GENERATE PERSONALIZATION TIPS ===
    $personalizationTips = [];
    
    if ($currentResume) {
        $personalizationTips[] = "Your resume matches {$matchScore}% of the job requirements";
        $personalizationTips[] = "Highlight your relevant experience in the cover letter";
        $personalizationTips[] = "Mention specific skills related to " . $job['employment_type'] . " work";
    } else {
        $personalizationTips[] = "Upload a resume to improve your application";
        $personalizationTips[] = "Create a profile highlighting your skills and experience";
    }
    
    // Check application deadline
    if ($job['application_deadline']) {
        $deadline = new DateTime($job['application_deadline']);
        $now = new DateTime();
        $daysLeft = $now->diff($deadline)->days;
        
        if ($daysLeft <= 3) {
            $personalizationTips[] = "Application deadline is in {$daysLeft} days - apply soon!";
        }
    }
    
    // === STEP 6: BUILD RESPONSE ===
    $responseData = [
        'already_applied' => false,
        'job' => $job,
        'resume' => $currentResume,
        'all_resumes' => $resumes,
        'match_percentage' => $matchScore,
        'personalization_tips' => $personalizationTips,
        'has_resume' => $currentResume !== null
    ];
    
    ApiResponse::success($responseData, "Application data retrieved successfully");
    
} catch (Exception $e) {
    error_log("Application Data Error: " . $e->getMessage());
    ApiResponse::serverError("Failed to load application data");
}
?>