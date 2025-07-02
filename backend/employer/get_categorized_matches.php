<?php
// backend/employer/get_categorized_matches.php
// FULLY CORRECTED VERSION - Only using columns that exist in your database

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
    
    // Fetch categorized applicants - ONLY using columns that exist
    $sql = "
        SELECT 
            ja.application_id,
            ja.seeker_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            ja.match_score,
            ja.cover_letter,
            ja.employer_notes,
            ja.last_activity,
            ja.resume_id,
            
            -- Job information
            jp.job_title,
            jp.job_description,
            jp.employment_type,
            jp.location as job_location,
            jp.salary_range,
            
            -- Applicant information
            js.first_name,
            js.last_name,
            CONCAT(js.first_name, ' ', js.last_name) as full_name,
            js.contact_number,
            js.city,
            js.province,
            js.disability_id,
            
            -- User account information
            ua.email,
            
            -- Resume information
            r.file_name as resume_file,
            r.file_path as resume_path,
            r.file_type as resume_type,
            
            -- Profile details
            pd.headline,
            pd.bio,
            pd.profile_photo_path as profile_picture
            
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        LEFT JOIN resumes r ON ja.resume_id = r.resume_id AND r.is_current = 1
        LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
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
    
    if (empty($applicants)) {
        echo json_encode([
            'success' => true,
            'category' => $category,
            'category_range' => $range,
            'total_applicants' => 0,
            'total_jobs' => 0,
            'job_groups' => [],
            'message' => 'No applicants found in this category',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Group applicants by job
    $jobGroups = [];
    foreach ($applicants as $applicant) {
        $jobId = $applicant['job_id'];
        
        if (!isset($jobGroups[$jobId])) {
            $jobGroups[$jobId] = [
                'job_info' => [
                    'job_id' => $jobId,
                    'job_title' => $applicant['job_title'],
                    'job_description' => $applicant['job_description'],
                    'employment_type' => $applicant['employment_type'],
                    'job_location' => $applicant['job_location'],
                    'salary_range' => $applicant['salary_range']
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
        
        // Get applicant skills for analysis
        $skillsQuery = "
            SELECT s.skill_name 
            FROM seeker_skills ss 
            JOIN skills s ON ss.skill_id = s.skill_id 
            WHERE ss.seeker_id = :seeker_id
        ";
        $skillsStmt = $conn->prepare($skillsQuery);
        $skillsStmt->execute(['seeker_id' => $applicant['seeker_id']]);
        $skills = $skillsStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get job requirements for skills matching
        $reqQuery = "
            SELECT s.skill_name 
            FROM job_requirements jr 
            JOIN skills s ON jr.skill_id = s.skill_id 
            WHERE jr.job_id = :job_id
        ";
        $reqStmt = $conn->prepare($reqQuery);
        $reqStmt->execute(['job_id' => $jobId]);
        $requiredSkills = $reqStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Build location string from available fields
        $location_parts = array_filter([$applicant['city'], $applicant['province']]);
        $location = !empty($location_parts) ? implode(', ', $location_parts) : 'Location not specified';
        
        // Process applicant data
        $processedApplicant = [
            'application_id' => $applicant['application_id'],
            'seeker_id' => $applicant['seeker_id'],
            'full_name' => $applicant['full_name'],
            'email' => $applicant['email'] ?: 'No email available',
            'contact_number' => $applicant['contact_number'] ?: 'No contact number',
            'headline' => $applicant['headline'] ?: 'Job Seeker',
            'profile_picture' => $applicant['profile_picture'],
            'match_score' => floatval($applicant['match_score']),
            'application_status' => $applicant['application_status'],
            'applied_at' => $applicant['applied_at'],
            'last_activity' => $applicant['last_activity'],
            'location' => $location,
            'resume_file' => $applicant['resume_file'],
            'resume_path' => $applicant['resume_path'],
            'resume_type' => $applicant['resume_type'],
            'cover_letter' => $applicant['cover_letter'],
            'employer_notes' => $applicant['employer_notes'],
            'bio' => $applicant['bio'],
            'skills_analysis' => analyzeSkillsMatch($requiredSkills, $skills),
            'resume_content' => getResumeContent($applicant['resume_path'])
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
        if ($group['stats']['lowest_score'] === 100) {
            $group['stats']['lowest_score'] = $group['stats']['highest_score'];
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
        'debug_info' => [
            'query_executed' => true,
            'applicants_found' => count($applicants),
            'jobs_found' => count($jobGroups)
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Get categorized matches error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'category' => $category ?? 'unknown',
            'employer_id' => $employer_id ?? 'unknown',
            'sql_state' => $e->getCode()
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function analyzeSkillsMatch($requiredSkills, $applicantSkills) {
    if (empty($requiredSkills)) {
        return [
            'matched_skills' => [],
            'missing_skills' => [],
            'bonus_skills' => $applicantSkills,
            'match_percentage' => 100
        ];
    }
    
    $matched = array_intersect($requiredSkills, $applicantSkills);
    $missing = array_diff($requiredSkills, $applicantSkills);
    $bonus = array_diff($applicantSkills, $requiredSkills);
    
    return [
        'matched_skills' => array_values($matched),
        'missing_skills' => array_values($missing),
        'bonus_skills' => array_values($bonus),
        'match_percentage' => round((count($matched) / count($requiredSkills)) * 100, 1)
    ];
}

function getResumeContent($resumePath) {
    if (empty($resumePath)) {
        return "No resume content available";
    }
    
    $fullPath = "../../" . $resumePath;
    if (file_exists($fullPath)) {
        $fileInfo = pathinfo($fullPath);
        $extension = strtolower($fileInfo['extension']);
        
        if ($extension === 'txt') {
            $content = file_get_contents($fullPath);
            return substr($content, 0, 300) . "...";
        } else {
            return "Resume file available - " . $fileInfo['filename'] . "." . $extension;
        }
    }
    
    return "Resume file not found";
}
?>