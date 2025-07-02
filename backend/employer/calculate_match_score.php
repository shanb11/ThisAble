<?php
// backend/employer/calculate_match_score.php
// UPDATED VERSION - Enhanced skill extraction for better matching

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';

/**
 * ENHANCED Extract skills from job requirements text
 */
function extractSkillsFromJobText($conn, $job_requirements_text) {
    // Get all skills from database
    $skills_sql = "SELECT skill_id, skill_name FROM skills";
    $skills_stmt = $conn->query($skills_sql);
    $all_skills = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $found_skills = [];
    $job_text_lower = strtolower($job_requirements_text);
    
    // Enhanced skill matching with variations
    foreach ($all_skills as $skill) {
        $skill_name_lower = strtolower($skill['skill_name']);
        
        // Direct exact match
        if (strpos($job_text_lower, $skill_name_lower) !== false) {
            $found_skills[] = [
                'skill_id' => $skill['skill_id'],
                'skill_name' => $skill['skill_name'],
                'match_type' => 'exact'
            ];
            continue;
        }
        
        // Enhanced variations matching
        $variations = getEnhancedSkillVariations($skill['skill_name']);
        foreach ($variations as $variation) {
            if (strpos($job_text_lower, strtolower($variation)) !== false) {
                $found_skills[] = [
                    'skill_id' => $skill['skill_id'],
                    'skill_name' => $skill['skill_name'],
                    'match_type' => 'variation'
                ];
                break; // Avoid duplicates
            }
        }
    }
    
    // Remove duplicates based on skill_id
    $unique_skills = [];
    foreach ($found_skills as $skill) {
        $unique_skills[$skill['skill_id']] = $skill;
    }
    
    return array_values($unique_skills);
}

/**
 * ENHANCED Get comprehensive skill variations
 */
function getEnhancedSkillVariations($skill_name) {
    $variations = [
        'Digital Literacy' => ['digital skills', 'computer literacy', 'digital competency', 'computer skills', 'it skills', 'technology skills'],
        'Data Entry' => ['data input', 'data processing', 'typing', 'data encoding', 'keyboard skills', 'data capture'],
        'Microsoft Office' => ['ms office', 'office suite', 'word', 'excel', 'powerpoint', 'outlook', 'office applications'],
        'Customer Service' => ['customer support', 'client service', 'customer care', 'customer relations', 'client support'],
        'Problem Resolution' => ['problem solving', 'troubleshooting', 'issue resolution', 'analytical thinking'],
        'Basic Coding' => ['programming', 'coding', 'software development', 'web development', 'development'],
        'Web Development' => ['web dev', 'website development', 'web programming', 'frontend', 'backend'],
        'Database Management' => ['database', 'sql', 'mysql', 'data management', 'database admin'],
        'Call Handling' => ['phone skills', 'telephone', 'call center', 'voice support'],
        'Client Communication' => ['communication', 'client relations', 'interpersonal skills']
    ];
    
    // Return variations or split skill name as fallback
    if (isset($variations[$skill_name])) {
        return $variations[$skill_name];
    }
    
    // Fallback: split skill name into words for partial matching
    $words = explode(' ', strtolower($skill_name));
    return count($words) > 1 ? $words : [];
}

/**
 * Calculate comprehensive match score between job and candidate
 */
function calculateJobMatch($conn, $job_id, $seeker_id) {
    try {
        // Get job details and requirements
        $job_data = getJobData($conn, $job_id);
        if (!$job_data) {
            return ['success' => false, 'error' => 'Job not found'];
        }
        
        // Get candidate skills and preferences
        $candidate_data = getCandidateData($conn, $seeker_id);
        if (!$candidate_data) {
            return ['success' => false, 'error' => 'Candidate not found'];
        }
        
        // Extract skills from job requirements text
        $job_skills = extractSkillsFromJobText($conn, $job_data['job_requirements']);
        
        // DEBUG: Log what skills were found
        error_log("Job {$job_id} extracted skills: " . json_encode(array_column($job_skills, 'skill_name')));
        error_log("Candidate {$seeker_id} has skills: " . json_encode(array_column($candidate_data['skills'], 'skill_name')));
        
        // Calculate skills match
        $skills_match = calculateSkillsMatch($job_skills, $candidate_data['skills']);
        
        // Calculate accommodation compatibility
        $accommodation_match = calculateAccommodationMatch($job_data['accommodations'], $candidate_data['accommodations']);
        
        // Calculate work preferences match
        $preferences_match = calculatePreferencesMatch($job_data, $candidate_data);
        
        // Calculate overall match score with weights
        $overall_score = (
            $skills_match['score'] * 0.50 +           // 50% skills
            $accommodation_match['score'] * 0.20 +    // 20% accommodations
            $preferences_match['score'] * 0.20 +      // 20% work preferences
            calculateExperienceMatch($job_data, $candidate_data) * 0.10  // 10% experience
        );
        
        $match_result = [
            'overall_score' => round($overall_score, 2),
            'skills_match' => $skills_match,
            'accommodation_match' => $accommodation_match,
            'preferences_match' => $preferences_match,
            'breakdown' => [
                'skills_weight' => '50%',
                'accommodations_weight' => '20%',
                'preferences_weight' => '20%',
                'experience_weight' => '10%'
            ]
        ];
        
        // Store match result in database
        storeMatchResult($conn, $job_id, $seeker_id, $match_result);
        
        return ['success' => true, 'data' => $match_result];
        
    } catch (Exception $e) {
        error_log("Match calculation error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Match calculation failed'];
    }
}

/**
 * Calculate skills match percentage and details
 */
function calculateSkillsMatch($job_skills, $candidate_skills) {
    if (empty($job_skills)) {
        return [
            'score' => 100, // If no specific skills required, everyone matches
            'matched_skills' => [],
            'missing_skills' => [],
            'total_job_skills' => 0,
            'matched_count' => 0
        ];
    }
    
    $job_skill_names = array_column($job_skills, 'skill_name');
    $candidate_skill_names = array_column($candidate_skills, 'skill_name');
    
    $matched_skills = array_intersect($job_skill_names, $candidate_skill_names);
    $missing_skills = array_diff($job_skill_names, $candidate_skill_names);
    
    $match_percentage = (count($matched_skills) / count($job_skill_names)) * 100;
    
    return [
        'score' => round($match_percentage, 2),
        'matched_skills' => array_values($matched_skills),
        'missing_skills' => array_values($missing_skills),
        'total_job_skills' => count($job_skill_names),
        'matched_count' => count($matched_skills)
    ];
}

/**
 * Calculate accommodation compatibility (PWD-specific feature)
 */
function calculateAccommodationMatch($job_accommodations, $candidate_accommodations) {
    if (empty($candidate_accommodations) || isset($candidate_accommodations['no_accommodations_needed'])) {
        return [
            'score' => 100, // No accommodations needed = perfect match
            'compatible_accommodations' => [],
            'missing_accommodations' => [],
            'notes' => 'No accommodations required'
        ];
    }
    
    $required_accommodations = $candidate_accommodations;
    $available_accommodations = $job_accommodations;
    
    $compatible = [];
    $missing = [];
    
    foreach ($required_accommodations as $accommodation => $needed) {
        if ($needed && $accommodation !== 'no_accommodations_needed') {
            if (isset($available_accommodations[$accommodation]) && $available_accommodations[$accommodation]) {
                $compatible[] = $accommodation;
            } else {
                $missing[] = $accommodation;
            }
        }
    }
    
    $total_needed = count($compatible) + count($missing);
    $compatibility_score = $total_needed > 0 ? (count($compatible) / $total_needed) * 100 : 100;
    
    return [
        'score' => round($compatibility_score, 2),
        'compatible_accommodations' => $compatible,
        'missing_accommodations' => $missing,
        'notes' => $total_needed === 0 ? 'No specific accommodations analyzed' : null
    ];
}

/**
 * Calculate work preferences match
 */
function calculatePreferencesMatch($job_data, $candidate_data) {
    $score = 0;
    $factors = 0;
    
    // Employment type match
    if (isset($candidate_data['preferred_employment_type']) && $candidate_data['preferred_employment_type']) {
        $factors++;
        if ($job_data['employment_type'] === $candidate_data['preferred_employment_type']) {
            $score += 100;
        }
    }
    
    // Remote work preference
    if (isset($candidate_data['remote_work_preference'])) {
        $factors++;
        if ($job_data['remote_work_available'] && $candidate_data['remote_work_preference'] === 'remote') {
            $score += 100;
        } elseif (!$job_data['remote_work_available'] && $candidate_data['remote_work_preference'] === 'on_site') {
            $score += 100;
        } elseif ($candidate_data['remote_work_preference'] === 'hybrid') {
            $score += 80; // Hybrid is flexible
        }
    }
    
    // Location preference (if both specified)
    if (isset($candidate_data['preferred_location']) && $job_data['location']) {
        $factors++;
        if (stripos($job_data['location'], $candidate_data['preferred_location']) !== false) {
            $score += 100;
        }
    }
    
    return [
        'score' => $factors > 0 ? round($score / $factors, 2) : 100,
        'factors_evaluated' => $factors
    ];
}

/**
 * Calculate experience level match (simplified)
 */
function calculateExperienceMatch($job_data, $candidate_data) {
    // This is a simplified version - can be enhanced later
    return 75; // Default neutral score
}

/**
 * Get job data including accommodations
 */
function getJobData($conn, $job_id) {
    $sql = "
        SELECT 
            jp.*,
            ja.wheelchair_accessible,
            ja.assistive_technology,
            ja.remote_work_option,
            ja.screen_reader_compatible,
            ja.sign_language_interpreter,
            ja.modified_workspace,
            ja.transportation_support,
            ja.additional_accommodations
        FROM job_posts jp
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_id = :job_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':job_id', $job_id);
    $stmt->execute();
    
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) return null;
    
    // Format accommodations
    $job['accommodations'] = [
        'wheelchair_accessible' => (bool)$job['wheelchair_accessible'],
        'assistive_technology' => (bool)$job['assistive_technology'],
        'remote_work_option' => (bool)$job['remote_work_option'],
        'screen_reader_compatible' => (bool)$job['screen_reader_compatible'],
        'sign_language_interpreter' => (bool)$job['sign_language_interpreter'],
        'modified_workspace' => (bool)$job['modified_workspace'],
        'transportation_support' => (bool)$job['transportation_support'],
        'additional_accommodations' => $job['additional_accommodations']
    ];
    
    return $job;
}

/**
 * Get candidate data including skills and accommodations
 */
function getCandidateData($conn, $seeker_id) {
    // Get basic candidate info
    $candidate_sql = "SELECT * FROM job_seekers WHERE seeker_id = :seeker_id";
    $candidate_stmt = $conn->prepare($candidate_sql);
    $candidate_stmt->bindParam(':seeker_id', $seeker_id);
    $candidate_stmt->execute();
    
    $candidate = $candidate_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$candidate) return null;
    
    // Get candidate skills
    $skills_sql = "
        SELECT s.skill_id, s.skill_name 
        FROM seeker_skills ss
        JOIN skills s ON ss.skill_id = s.skill_id
        WHERE ss.seeker_id = :seeker_id
    ";
    $skills_stmt = $conn->prepare($skills_sql);
    $skills_stmt->bindParam(':seeker_id', $seeker_id);
    $skills_stmt->execute();
    $candidate['skills'] = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workplace accommodations
    $accommodations_sql = "SELECT * FROM workplace_accommodations WHERE seeker_id = :seeker_id";
    $accommodations_stmt = $conn->prepare($accommodations_sql);
    $accommodations_stmt->bindParam(':seeker_id', $seeker_id);
    $accommodations_stmt->execute();
    $accommodations = $accommodations_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($accommodations) {
        if ($accommodations['no_accommodations_needed']) {
            $candidate['accommodations'] = ['no_accommodations_needed' => true];
        } else {
            $acc_list = $accommodations['accommodation_list'];
            if ($acc_list && strpos($acc_list, '{') === 0) {
                // JSON format
                $candidate['accommodations'] = json_decode($acc_list, true);
            } else {
                // Comma-separated format
                $candidate['accommodations'] = array_filter(array_map('trim', explode(',', $acc_list)));
            }
        }
    } else {
        $candidate['accommodations'] = [];
    }
    
    return $candidate;
}

/**
 * Store match result in database
 */
function storeMatchResult($conn, $job_id, $seeker_id, $match_result) {
    $sql = "
        UPDATE job_applications 
        SET 
            match_score = :match_score,
            skills_matched = :skills_matched,
            skills_missing = :skills_missing,
            accommodation_compatibility = :accommodation_compatibility
        WHERE job_id = :job_id AND seeker_id = :seeker_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'match_score' => $match_result['overall_score'],
        'skills_matched' => json_encode($match_result['skills_match']['matched_skills']),
        'skills_missing' => json_encode($match_result['skills_match']['missing_skills']),
        'accommodation_compatibility' => $match_result['accommodation_match']['score'],
        'job_id' => $job_id,
        'seeker_id' => $seeker_id
    ]);
}

// API endpoint for calculating match scores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['job_id']) && isset($input['seeker_id'])) {
        $result = calculateJobMatch($conn, $input['job_id'], $input['seeker_id']);
        echo json_encode($result);
    } elseif (isset($input['job_id']) && $input['action'] === 'calculate_all') {
        // Calculate for all applicants of a job
        $job_id = $input['job_id'];
        
        $applicants_sql = "SELECT seeker_id FROM job_applications WHERE job_id = :job_id";
        $applicants_stmt = $conn->prepare($applicants_sql);
        $applicants_stmt->bindParam(':job_id', $job_id);
        $applicants_stmt->execute();
        $applicants = $applicants_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $results = [];
        foreach ($applicants as $seeker_id) {
            $result = calculateJobMatch($conn, $job_id, $seeker_id);
            $results[] = [
                'seeker_id' => $seeker_id,
                'result' => $result
            ];
        }
        
        echo json_encode(['success' => true, 'results' => $results]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    }
}
?>