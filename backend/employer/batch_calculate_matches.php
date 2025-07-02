<?php
// backend/employer/batch_calculate_matches.php
// FIXED VERSION - Absolutely clean JSON output, no PHP errors

// SUPPRESS ALL PHP ERRORS AND WARNINGS
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// BUFFER OUTPUT TO PREVENT ANY ACCIDENTAL HTML
ob_start();

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON headers immediately
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Clear any buffered output
ob_clean();

try {
    // Check if files exist before including
    $required_files = [
        '../db.php',
        'session_check.php', 
        'calculate_match_score.php'
    ];
    
    foreach ($required_files as $file) {
        if (!file_exists($file)) {
            throw new Exception("Required file missing: $file");
        }
    }
    
    require_once '../db.php';
    require_once 'session_check.php';
    require_once 'calculate_match_score.php';
    
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get JSON input properly
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    if (!$input || !isset($input['job_id'])) {
        throw new Exception('Job ID is required');
    }
    
    $job_id = (int)$input['job_id'];
    
    // Verify job belongs to this employer
    $job_check_sql = "SELECT job_id FROM job_posts WHERE job_id = ? AND employer_id = ?";
    $job_check_stmt = $conn->prepare($job_check_sql);
    $job_check_stmt->execute([$job_id, $employer_id]);
    
    if (!$job_check_stmt->fetch()) {
        throw new Exception('Job not found or access denied');
    }
    
    // Get all applicants for this job
    $applicants_sql = "
        SELECT DISTINCT ja.seeker_id, ja.application_id, ja.match_score
        FROM job_applications ja 
        WHERE ja.job_id = ?
    ";
    $applicants_stmt = $conn->prepare($applicants_sql);
    $applicants_stmt->execute([$job_id]);
    $applicants = $applicants_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($applicants)) {
        // Clean output and send response
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'No applicants found for this job',
            'summary' => [
                'total_applicants' => 0,
                'processed' => 0,
                'errors' => 0,
                'skipped' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'excellent_matches' => 0,
                'good_matches' => 0,
                'fair_matches' => 0,
                'poor_matches' => 0
            ],
            'results' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    $results = [];
    $processed = 0;
    $errors = 0;
    
    foreach ($applicants as $applicant) {
        try {
            // Skip if match already calculated and force_recalculate is not set
            if (!isset($input['force_recalculate']) && (float)$applicant['match_score'] > 0) {
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'status' => 'skipped',
                    'reason' => 'already_calculated',
                    'score' => (float)$applicant['match_score']
                ];
                continue;
            }
            
            // Calculate match score
            $match_result = calculateJobMatch($conn, $job_id, $applicant['seeker_id']);
            
            if ($match_result && isset($match_result['success']) && $match_result['success']) {
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'application_id' => $applicant['application_id'],
                    'status' => 'success',
                    'score' => (float)$match_result['data']['overall_score'],
                    'skills_matched' => count($match_result['data']['skills_match']['matched_skills']),
                    'skills_missing' => count($match_result['data']['skills_match']['missing_skills']),
                    'accommodation_compatibility' => (float)$match_result['data']['accommodation_match']['score']
                ];
                $processed++;
            } else {
                $error_msg = isset($match_result['error']) ? $match_result['error'] : 'Unknown error';
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'status' => 'error',
                    'error' => $error_msg
                ];
                $errors++;
            }
            
        } catch (Exception $e) {
            $results[] = [
                'seeker_id' => $applicant['seeker_id'],
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            $errors++;
        }
    }
    
    // Calculate summary statistics
    $successful_results = array_filter($results, function($r) { return $r['status'] === 'success'; });
    $scores = array_column($successful_results, 'score');
    
    $summary = [
        'total_applicants' => count($applicants),
        'processed' => $processed,
        'errors' => $errors,
        'skipped' => count($applicants) - $processed - $errors,
        'average_score' => empty($scores) ? 0 : round(array_sum($scores) / count($scores), 2),
        'highest_score' => empty($scores) ? 0 : max($scores),
        'lowest_score' => empty($scores) ? 0 : min($scores),
        'excellent_matches' => count(array_filter($scores, function($s) { return $s >= 90; })),
        'good_matches' => count(array_filter($scores, function($s) { return $s >= 75 && $s < 90; })),
        'fair_matches' => count(array_filter($scores, function($s) { return $s >= 60 && $s < 75; })),
        'poor_matches' => count(array_filter($scores, function($s) { return $s < 60; }))
    ];
    
    // Clean output buffer and send JSON
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => "Match calculation completed for {$processed} applicants",
        'summary' => $summary,
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Batch match calculation error: " . $e->getMessage());
    
    // Clean output buffer and send error JSON
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

// Flush and end output buffering
ob_end_flush();
?>