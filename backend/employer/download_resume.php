<?php
// backend/employer/download_resume.php
// API to securely download applicant resumes

session_start();
require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    $application_id = $_GET['application_id'] ?? null;
    $resume_id = $_GET['resume_id'] ?? null;
    
    if (!$application_id && !$resume_id) {
        http_response_code(400);
        die('Application ID or Resume ID required');
    }
    
    // Security check: verify the resume belongs to an applicant for this employer's job
    if ($application_id) {
        $sql = "SELECT 
                    r.file_name,
                    r.file_path,
                    r.file_type,
                    r.file_size,
                    CONCAT(js.first_name, ' ', js.last_name) as applicant_name,
                    jp.job_title
                FROM job_applications ja
                JOIN job_posts jp ON ja.job_id = jp.job_id
                JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                JOIN resumes r ON ja.resume_id = r.resume_id
                WHERE ja.application_id = :application_id 
                AND jp.employer_id = :employer_id
                AND r.file_path IS NOT NULL";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':application_id', $application_id);
        $stmt->bindValue(':employer_id', $employer_id);
    } else {
        // Direct resume access (with security check)
        $sql = "SELECT 
                    r.file_name,
                    r.file_path,
                    r.file_type,
                    r.file_size,
                    CONCAT(js.first_name, ' ', js.last_name) as applicant_name,
                    'Resume' as job_title
                FROM resumes r
                JOIN job_seekers js ON r.seeker_id = js.seeker_id
                JOIN job_applications ja ON js.seeker_id = ja.seeker_id
                JOIN job_posts jp ON ja.job_id = jp.job_id
                WHERE r.resume_id = :resume_id 
                AND jp.employer_id = :employer_id
                GROUP BY r.resume_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':resume_id', $resume_id);
        $stmt->bindValue(':employer_id', $employer_id);
    }
    
    $stmt->execute();
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        http_response_code(404);
        die('Resume not found or access denied');
    }
    
    // Build full file path
    $file_path = '../../' . $resume['file_path'];
    
    // Check if file exists
    if (!file_exists($file_path)) {
        http_response_code(404);
        die('Resume file not found on server');
    }
    
    // Log the download activity
    logActivity("RESUME_DOWNLOAD", "Downloaded resume for application {$application_id}");
    
    // Set headers for download
    header('Content-Type: ' . $resume['file_type']);
    header('Content-Disposition: attachment; filename="' . basename($resume['file_name']) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: private');
    header('Pragma: private');
    header('Expires: 0');
    
    // Output the file
    readfile($file_path);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error downloading resume: ' . $e->getMessage());
}