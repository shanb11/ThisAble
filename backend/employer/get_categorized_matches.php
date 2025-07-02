<?php
// backend/employer/get_categorized_matches.php
// New API endpoint for categorized match results

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get category filter
    $category = $_GET['category'] ?? '';
    
    // Define score ranges for categories
    $scoreRanges = [
        'excellent' => ['min' => 90, 'max' => 100],
        'good' => ['min' => 75, 'max' => 89],
        'fair' => ['min' => 60, 'max' => 74],
        'needs-review' => ['min' => 0, 'max' => 59]
    ];
    
    if (!isset($scoreRanges[$category])) {
        throw new Exception('Invalid category specified');
    }
    
    $range = $scoreRanges[$category];
    
    // Fetch categorized applicants with job information
    $sql = "
        SELECT 
            ja.application_id,
            ja.seeker_id,
            ja.job_id,
            ja.status,
            ja.applied_at,
            ja.match_score,
            ja.interview_scheduled,
            
            -- Job information
            jp.job_title,
            jp.required_skills,
            jp.job_type,
            jp.work_arrangement,
            
            -- Applicant information
            js.first_name,
            js.last_name,
            CONCAT(js.first_name, ' ', js.last_name) as full_name,
            js.email,
            js.contact_number,
            js.headline,
            js.profile_picture,
            js.selected_skills,
            js.disability_type,
            js.accommodation_needs,
            js.resume_file,
            js.resume_content,
            
            -- Location information
            js.country,
            js.city,
            js.state_province
            
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        WHERE jp.employer_id = :employer_id
        AND ja.match_score >= :min_score 
        AND ja.match_score <= :max_score
        ORDER BY jp.job_title, ja.match_score DESC, ja.applied_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'employer_id' => $employer_id,
        'min_score' => $range['min'],
        'max_score' => $range['max']
    ]);
    
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group applicants by job
    $jobGroups = [];
    foreach ($applicants as $applicant) {
        $jobId = $applicant['job_id'];
        
        if (!isset($jobGroups[$jobId])) {
            $jobGroups[$jobId] = [
                'job_info' => [
                    'job_id' => $jobId,
                    'job_title' => $applicant['job_title'],
                    'required_skills' => $applicant['required_skills'],
                    'job_type' => $applicant['job_type'],
                    'work_arrangement' => $applicant['work_arrangement']
                ],
                'applicants' => [],
                'stats' => [
                    'count' => 0,
                    'average_score' => 0,
                    'highest_score' => 0,
                    'lowest_score' => 100
                ]
            ];
        }
        
        // Process applicant data
        $processedApplicant = [
            'application_id' => $applicant['application_id'],
            'seeker_id' => $applicant['seeker_id'],
            'full_name' => $applicant['full_name'],
            'email' => $applicant['email'],
            'contact_number' => $applicant['contact_number'],
            'headline' => $applicant['headline'],
            'profile_picture' => $applicant['profile_picture'],
            'match_score' => floatval($applicant['match_score']),
            'status' => $applicant['status'],
            'applied_at' => $applicant['applied_at'],
            'interview_scheduled' => $applicant['interview_scheduled'],
            'location' => trim($applicant['city'] . ', ' . $applicant['country'], ', '),
            'disability_type' => $applicant['disability_type'],
            'accommodation_needs' => $applicant['accommodation_needs'],
            'resume_file' => $applicant['resume_file'],
            'resume_content' => $applicant['resume_content'],
            'skills_analysis' => analyzeSkillsMatch(
                $applicant['required_skills'], 
                $applicant['selected_skills']
            )
        ];
        
        $jobGroups[$jobId]['applicants'][] = $processedApplicant;
        
        // Update job stats
        $jobGroups[$jobId]['stats']['count']++;
        $score = floatval($applicant['match_score']);
        $jobGroups[$jobId]['stats']['highest_score'] = max($jobGroups[$jobId]['stats']['highest_score'], $score);
        $jobGroups[$jobId]['stats']['lowest_score'] = min($jobGroups[$jobId]['stats']['lowest_score'], $score);
    }
    
    // Calculate average scores for each job
    foreach ($jobGroups as &$group) {
        if ($group['stats']['count'] > 0) {
            $totalScore = array_sum(array_column($group['applicants'], 'match_score'));
            $group['stats']['average_score'] = round($totalScore / $group['stats']['count'], 1);
        }
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'category' => $category,
        'category_range' => $range,
        'total_applicants' => count($applicants),
        'total_jobs' => count($jobGroups),
        'job_groups' => array_values($jobGroups),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Get categorized matches error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function analyzeSkillsMatch($requiredSkills, $applicantSkills) {
    $required = array_filter(array_map('trim', explode(',', $requiredSkills ?? '')));
    $applicant = array_filter(array_map('trim', explode(',', $applicantSkills ?? '')));
    
    $matched = array_intersect($required, $applicant);
    $missing = array_diff($required, $applicant);
    $bonus = array_diff($applicant, $required);
    
    return [
        'matched_skills' => array_values($matched),
        'missing_skills' => array_values($missing),
        'bonus_skills' => array_values($bonus),
        'match_percentage' => count($required) > 0 ? round((count($matched) / count($required)) * 100, 1) : 0
    ];
}
?>