<?php
// backend/employer/calculate_match_score.php
// UPDATED VERSION - Enhanced skill extraction for better matching

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn)) {
    if (file_exists('../db.php')) {
        require_once '../db.php';
    } elseif (file_exists('backend/db.php')) {
        require_once 'backend/db.php';
    } else {
        require_once __DIR__ . '/../db.php';
    }
}
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
        
        // ENHANCED: Get job skills with structured data priority
        $job_skills = getJobSkills($conn, $job_id, $job_data['job_requirements']);
        
        // DEBUG: Log what skills were found
        error_log("Job {$job_id} enhanced skills: " . json_encode([
            'total_skills' => count($job_skills),
            'structured' => count(array_filter($job_skills, function($s) { return $s['match_type'] === 'structured'; })),
            'extracted' => count(array_filter($job_skills, function($s) { return $s['match_type'] !== 'structured'; })),
            'skill_names' => array_column($job_skills, 'skill_name')
        ]));
        error_log("Candidate {$seeker_id} has skills: " . json_encode(array_column($candidate_data['skills'], 'skill_name')));

        // Get candidate documents count
        $doc_query = "SELECT COUNT(*) as doc_count FROM candidate_documents WHERE seeker_id = ?";
        $doc_stmt = $conn->prepare($doc_query);
        $doc_stmt->execute([$seeker_id]);
        $doc_result = $doc_stmt->fetch();
        $candidate_documents = $doc_result['doc_count'] ?? 0;
        
        // Check if job requires credentials
        $requires_creds = $job_data['requires_credentials'] ?? false;
        
        $credential_match = calculateCredentialMatch(
            ['requires_credentials' => $requires_creds], 
            $candidate_documents
        );
        
        // ENHANCED: Calculate skills match with weighting
        $skills_match = calculateEnhancedSkillsMatch($job_skills, $candidate_data['skills']);
        
        // Calculate accommodation compatibility
        $accommodation_match = calculateAccommodationMatch($job_data['accommodations'], $candidate_data['accommodations']);
        
        // Calculate work preferences match
        $preferences_match = calculatePreferencesMatch($job_data, $candidate_data);
        
        // Enhanced overall scoring with critical skills penalty
        if ($requires_creds) {
            $base_score = (
                $skills_match['score'] * 0.30 +           // Reduced
                $credential_match['score'] * 0.20 +       // NEW!
                $accommodation_match['score'] * 0.25 +
                $preferences_match['score'] * 0.20 +
                $experience_match * 0.05
            );
        } else {
            $base_score = (
                $skills_match['score'] * 0.35 +
                $accommodation_match['score'] * 0.25 +
                $preferences_match['score'] * 0.25 +
                $experience_match * 0.15
            );
        }
        
        // Apply critical skills penalty
        $critical_penalty = 0;
        if (!empty($skills_match['critical_missing'])) {
            $critical_penalty = count($skills_match['critical_missing']) * 15; // 15% penalty per critical skill
        }
        
        $overall_score = max(0, $base_score - $critical_penalty);
        
        $match_result = [
            'overall_score' => round($overall_score, 2),
            'skills_match' => $skills_match,
            'accommodation_match' => $accommodation_match,
            'preferences_match' => $preferences_match,
            'critical_penalty' => $critical_penalty,
            'breakdown' => [
                'skills_weight' => '50%',
                'accommodations_weight' => '20%',
                'preferences_weight' => '20%',
                'experience_weight' => '10%',
                'critical_penalty' => $critical_penalty . '%'
            ]
        ];
        
        // Store match result in database
        storeMatchResult($conn, $job_id, $seeker_id, $match_result);
        
        return ['success' => true, 'data' => $match_result];
        
    } catch (Exception $e) {
        error_log("Enhanced match calculation error: " . $e->getMessage());
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
/**
 * Calculate experience level match - SIMPLE VERSION
 * Replace this function in your calculate_match_score.php
 */
function calculateExperienceMatch($job_data, $candidate_data) {
    global $conn;
    
    try {
        // Get candidate's total years of experience
        $total_years = getTotalExperienceYears($conn, $candidate_data['seeker_id']);
        
        // Extract required years from job posting
        $required_years = extractRequiredYears($job_data);
        
        // Calculate score based on years comparison
        $score = calculateYearsScore($total_years, $required_years);
        
        // Return score with simple analysis
        return $score;
        
    } catch (Exception $e) {
        // Fallback to neutral score if error
        error_log("Experience calculation error: " . $e->getMessage());
        return [
            'score' => 60,
            'candidate_years' => 0,
            'required_years' => 0,
            'analysis' => 'Experience data unavailable'
        ];
    }
}

function calculateCredentialMatch($job_data, $candidate_documents) {
    // If job doesn't require credentials
    if (!$job_data['requires_credentials']) {
        return ['score' => 100, 'type' => 'not_required'];
    }
    
    // Simple check: does candidate have ANY documents?
    $candidate_doc_count = count($candidate_documents);
    
    if ($candidate_doc_count > 0) {
        return ['score' => 100, 'type' => 'has_documents'];
    } else {
        return ['score' => 0, 'type' => 'missing_documents'];
    }
}

/**
 * Get total years of experience from database
 */
function getTotalExperienceYears($conn, $seeker_id) {
    $sql = "SELECT 
                start_date,
                end_date,
                is_current
            FROM experience 
            WHERE seeker_id = :seeker_id 
            ORDER BY start_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':seeker_id', $seeker_id);
    $stmt->execute();
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($experiences)) {
        return 0; // No experience found
    }
    
    $total_months = 0;
    
    foreach ($experiences as $exp) {
        // Calculate duration for each job
        $start_date = new DateTime($exp['start_date']);
        
        // Use current date if job is ongoing, otherwise use end_date
        if ($exp['is_current']) {
            $end_date = new DateTime(); // Current date
        } else {
            $end_date = new DateTime($exp['end_date']);
        }
        
        // Calculate months between start and end
        $interval = $start_date->diff($end_date);
        $months = ($interval->y * 12) + $interval->m;
        
        $total_months += $months;
    }
    
    // Convert to years (rounded to 1 decimal place)
    return round($total_months / 12, 1);
}

/**
 * Extract required years from job posting text
 */
function extractRequiredYears($job_data) {
    // Combine job requirements and description for analysis
    $text = strtolower($job_data['job_requirements'] . ' ' . $job_data['job_description']);
    
    // Common patterns for experience requirements
    $patterns = [
        // "2+ years", "3+ years experience", "5+ years of experience"
        '/(\d+)\+?\s*years?\s*(of\s*)?(experience|exp)/i',
        
        // "minimum 2 years", "at least 3 years"
        '/(?:minimum|at least)\s*(\d+)\s*years?/i',
        
        // "2-5 years", "3 to 5 years"
        '/(\d+)\s*(?:to|-)\s*(\d+)\s*years?/i',
        
        // "entry level" = 0 years, "senior" = 5+ years
        '/entry\s*level|fresh\s*graduate/i' => 0,
        '/senior|lead/i' => 5
    ];
    
    foreach ($patterns as $pattern => $default) {
        if (is_string($pattern)) {
            if (preg_match($pattern, $text, $matches)) {
                if (isset($matches[2]) && is_numeric($matches[2])) {
                    // For range patterns like "2-5 years", use the minimum
                    return (int)$matches[1];
                } else {
                    return (int)$matches[1];
                }
            }
        } else {
            // For keyword patterns
            if (preg_match($pattern, $text)) {
                return $default;
            }
        }
    }
    
    return 0; // No specific requirement found
}

/**
 * Calculate score based on years comparison
 */
function calculateYearsScore($candidate_years, $required_years) {
    // If no requirement specified, score based on general experience value
    if ($required_years == 0) {
        if ($candidate_years >= 5) return 90;      // Excellent experience
        if ($candidate_years >= 3) return 85;      // Good experience
        if ($candidate_years >= 2) return 80;      // Decent experience
        if ($candidate_years >= 1) return 75;      // Some experience
        if ($candidate_years >= 0.5) return 65;    // Limited experience
        return 50;                                  // No experience
    }
    
    // Calculate based on requirement comparison
    if ($candidate_years >= $required_years) {
        // Meets or exceeds requirement
        $excess = $candidate_years - $required_years;
        
        if ($excess >= 3) {
            return 100; // Significantly exceeds requirement
        } elseif ($excess >= 1) {
            return 95;  // Exceeds requirement
        } else {
            return 90;  // Meets requirement exactly
        }
    } else {
        // Below requirement
        $deficit = $required_years - $candidate_years;
        
        if ($deficit <= 0.5) {
            return 85;  // Very close to requirement
        } elseif ($deficit <= 1) {
            return 75;  // Somewhat below requirement
        } elseif ($deficit <= 2) {
            return 65;  // Below requirement
        } else {
            return 45;  // Significantly below requirement
        }
    }
}

/**
 * Generate simple human-readable analysis
 */
function generateSimpleAnalysis($candidate_years, $required_years, $score) {
    if ($required_years == 0) {
        // No specific requirement
        if ($candidate_years >= 5) {
            return "Excellent: {$candidate_years} years of experience";
        } elseif ($candidate_years >= 2) {
            return "Good: {$candidate_years} years of experience";
        } elseif ($candidate_years >= 1) {
            return "Moderate: {$candidate_years} years of experience";
        } else {
            return "Limited: {$candidate_years} years of experience";
        }
    } else {
        // Specific requirement exists
        if ($candidate_years >= $required_years) {
            $excess = $candidate_years - $required_years;
            if ($excess >= 1) {
                return "Exceeds requirement: {$candidate_years} years (Required: {$required_years})";
            } else {
                return "Meets requirement: {$candidate_years} years (Required: {$required_years})";
            }
        } else {
            return "Below requirement: {$candidate_years} years (Required: {$required_years})";
        }
    }
}

// OPTIONAL: Helper function for debugging/testing
function testExperienceCalculation($conn, $seeker_id, $job_data) {
    $result = calculateExperienceMatch($job_data, ['seeker_id' => $seeker_id]);
    
    echo "=== Experience Match Test ===\n";
    echo "Candidate Years: {$result['candidate_years']}\n";
    echo "Required Years: {$result['required_years']}\n";
    echo "Score: {$result['score']}%\n";
    echo "Analysis: {$result['analysis']}\n";
    echo "============================\n";
    
    return $result;
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

// ADD THIS TO YOUR calculate_match_score.php file
// Enhanced calculateJobMatch function that prioritizes structured skills

/**
 * Enhanced Get job skills with structured data priority
 */
function getJobSkills($conn, $job_id, $job_requirements_text) {
    $job_skills = [];
    
    // PRIORITY 1: Get structured skills from job_requirements table
    $structured_skills_sql = "
        SELECT 
            jr.skill_id,
            jr.is_required,
            jr.priority,
            jr.weight,
            s.skill_name,
            sc.category_name
        FROM job_requirements jr
        JOIN skills s ON jr.skill_id = s.skill_id
        JOIN skill_categories sc ON s.category_id = sc.category_id
        WHERE jr.job_id = :job_id
        ORDER BY 
            CASE jr.priority 
                WHEN 'critical' THEN 1 
                WHEN 'important' THEN 2 
                WHEN 'preferred' THEN 3 
                ELSE 4 
            END,
            sc.category_name, 
            s.skill_name
    ";
    
    $structured_stmt = $conn->prepare($structured_skills_sql);
    $structured_stmt->execute(['job_id' => $job_id]);
    $structured_skills = $structured_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert structured skills to standard format
    foreach ($structured_skills as $skill) {
        $job_skills[] = [
            'skill_id' => $skill['skill_id'],
            'skill_name' => $skill['skill_name'],
            'category_name' => $skill['category_name'],
            'is_required' => (bool)$skill['is_required'],
            'priority' => $skill['priority'],
            'weight' => (float)($skill['weight'] ?? 1.0),
            'match_type' => 'structured'
        ];
    }
    
    // PRIORITY 2: Extract additional skills from job_requirements text (fallback)
    if (!empty($job_requirements_text)) {
        $extracted_skills = extractSkillsFromJobText($conn, $job_requirements_text);
        $structured_skill_ids = array_column($job_skills, 'skill_id');
        
        // Add extracted skills that aren't already in structured skills
        foreach ($extracted_skills as $extracted_skill) {
            if (!in_array($extracted_skill['skill_id'], $structured_skill_ids)) {
                $job_skills[] = [
                    'skill_id' => $extracted_skill['skill_id'],
                    'skill_name' => $extracted_skill['skill_name'],
                    'category_name' => null,
                    'is_required' => true, // Default for text-extracted skills
                    'priority' => 'important',
                    'weight' => 0.8, // Lower weight for text-extracted skills
                    'match_type' => $extracted_skill['match_type']
                ];
            }
        }
    }
    
    return $job_skills;
}

/**
 * Enhanced Calculate skills match with weighted scoring
 */
/**
 * Enhanced Calculate skills match with weighted scoring + CONFIDENCE FACTOR
 */
function calculateEnhancedSkillsMatch($job_skills, $candidate_skills) {
    if (empty($job_skills)) {
        return [
            'score' => 100, 
            'matched_skills' => [],
            'missing_skills' => [],
            'critical_missing' => [],
            'total_job_skills' => 0,
            'matched_count' => 0,
            'structured_skills_count' => 0,
            'text_extracted_count' => 0,
            'weighted_score' => 100,
            'confidence_factor' => 1.0,  // NEW
            'raw_score' => 100           // NEW
        ];
    }
    
    $candidate_skill_names = array_column($candidate_skills, 'skill_name');
    
    $matched_skills = [];
    $missing_skills = [];
    $critical_missing = [];
    $total_weight = 0;
    $matched_weight = 0;
    $structured_count = 0;
    $extracted_count = 0;
    
    foreach ($job_skills as $job_skill) {
        $skill_name = $job_skill['skill_name'];
        $weight = $job_skill['weight'] ?? 1.0;
        $priority = $job_skill['priority'] ?? 'important';
        $is_structured = $job_skill['match_type'] === 'structured';
        
        if ($is_structured) {
            $structured_count++;
        } else {
            $extracted_count++;
        }
        
        $total_weight += $weight;
        
        if (in_array($skill_name, $candidate_skill_names)) {
            $matched_skills[] = [
                'skill_name' => $skill_name,
                'priority' => $priority,
                'weight' => $weight,
                'match_type' => $job_skill['match_type']
            ];
            $matched_weight += $weight;
        } else {
            $missing_skill = [
                'skill_name' => $skill_name,
                'priority' => $priority,
                'weight' => $weight,
                'match_type' => $job_skill['match_type']
            ];
            
            $missing_skills[] = $missing_skill;
            
            // Track critical missing skills separately
            if ($priority === 'critical') {
                $critical_missing[] = $missing_skill;
            }
        }
    }
    
    // Calculate weighted score
    $weighted_score = $total_weight > 0 ? ($matched_weight / $total_weight) * 100 : 100;
    
    // Calculate simple percentage
    $simple_percentage = (count($matched_skills) / count($job_skills)) * 100;
    
    // Use weighted score if we have structured skills, otherwise use simple percentage
    $raw_score = $structured_count > 0 ? $weighted_score : $simple_percentage;
    
    // NEW: Calculate confidence factor based on candidate's total skill count
    $candidate_skill_count = count($candidate_skills);
    
    if ($candidate_skill_count > 20) {
        $confidence_factor = 0.80; // 20% penalty for claiming too many skills
    } elseif ($candidate_skill_count > 15) {
        $confidence_factor = 0.85; // 15% penalty
    } elseif ($candidate_skill_count > 12) {
        $confidence_factor = 0.90; // 10% penalty  
    } elseif ($candidate_skill_count > 8) {
        $confidence_factor = 0.95; // 5% penalty
    } else {
        $confidence_factor = 1.0;   // No penalty for reasonable skill count
    }
    
    // Apply confidence factor to final score
    $final_score = $raw_score * $confidence_factor;
    
    return [
        'score' => round($final_score, 2),                           // ADJUSTED score
        'raw_score' => round($raw_score, 2),                        // NEW: Original score
        'confidence_factor' => $confidence_factor,                  // NEW: Confidence factor applied
        'candidate_skill_count' => $candidate_skill_count,          // NEW: For debugging
        'matched_skills' => array_column($matched_skills, 'skill_name'),
        'missing_skills' => array_column($missing_skills, 'skill_name'),
        'critical_missing' => array_column($critical_missing, 'skill_name'),
        'total_job_skills' => count($job_skills),
        'matched_count' => count($matched_skills),
        'structured_skills_count' => $structured_count,
        'text_extracted_count' => $extracted_count,
        'weighted_score' => round($weighted_score, 2),
        'detailed_matches' => $matched_skills,
        'detailed_missing' => $missing_skills
    ];
}

?>