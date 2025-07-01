<?php
// backend/employer/batch_calculate_matches.php
// Endpoint for calculating matches for all applicants of a job

session_start();
require_once '../db.php';
require_once 'session_check.php';
require_once 'calculate_match_score.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['job_id'])) {
        throw new Exception('Job ID is required');
    }
    
    $job_id = $input['job_id'];
    
    // Verify job belongs to this employer
    $job_check_sql = "SELECT job_id FROM job_posts WHERE job_id = :job_id AND employer_id = :employer_id";
    $job_check_stmt = $conn->prepare($job_check_sql);
    $job_check_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    
    if (!$job_check_stmt->fetch()) {
        throw new Exception('Job not found or access denied');
    }
    
    // Get all applicants for this job
    $applicants_sql = "
        SELECT DISTINCT ja.seeker_id, ja.application_id, ja.match_score
        FROM job_applications ja 
        WHERE ja.job_id = :job_id
    ";
    $applicants_stmt = $conn->prepare($applicants_sql);
    $applicants_stmt->execute(['job_id' => $job_id]);
    $applicants = $applicants_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($applicants)) {
        echo json_encode([
            'success' => true,
            'message' => 'No applicants found for this job',
            'processed' => 0
        ]);
        exit;
    }
    
    $results = [];
    $processed = 0;
    $errors = 0;
    
    foreach ($applicants as $applicant) {
        try {
            // Skip if match already calculated and force_recalculate is not set
            if (!isset($input['force_recalculate']) && $applicant['match_score'] > 0) {
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'status' => 'skipped',
                    'reason' => 'already_calculated',
                    'score' => $applicant['match_score']
                ];
                continue;
            }
            
            // Calculate match score
            $match_result = calculateJobMatch($conn, $job_id, $applicant['seeker_id']);
            
            if ($match_result['success']) {
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'application_id' => $applicant['application_id'],
                    'status' => 'success',
                    'score' => $match_result['data']['overall_score'],
                    'skills_matched' => count($match_result['data']['skills_match']['matched_skills']),
                    'skills_missing' => count($match_result['data']['skills_match']['missing_skills']),
                    'accommodation_compatibility' => $match_result['data']['accommodation_match']['score']
                ];
                $processed++;
            } else {
                $results[] = [
                    'seeker_id' => $applicant['seeker_id'],
                    'status' => 'error',
                    'error' => $match_result['error']
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
    
    echo json_encode([
        'success' => true,
        'message' => "Match calculation completed for {$processed} applicants",
        'summary' => $summary,
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Batch match calculation error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>