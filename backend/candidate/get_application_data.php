<?php
// backend/candidate/get_application_data.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$seeker_id = get_seeker_id();
$job_id = $_GET['job_id'] ?? 0;

if (!$job_id) {
    echo json_encode(['success' => false, 'error' => 'Job ID required']);
    exit;
}

try {
    // Get user's current resume
    $resumeQuery = "
        SELECT 
            resume_id,
            file_name,
            file_path,
            file_type,
            upload_date
        FROM resumes 
        WHERE seeker_id = :seeker_id 
        AND is_current = 1 
        ORDER BY upload_date DESC 
        LIMIT 1
    ";
    
    $resumeStmt = $conn->prepare($resumeQuery);
    $resumeStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $resumeStmt->execute();
    $resume = $resumeStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get job details and requirements
    $jobQuery = "
        SELECT 
            jp.job_title,
            jp.job_description,
            jp.job_requirements as job_req_text,
            e.company_name,
            GROUP_CONCAT(
                DISTINCT CONCAT(s.skill_name, ':', jr.is_required, ':', jr.experience_level)
            ) as job_requirements
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_requirements jr ON jp.job_id = jr.job_id
        LEFT JOIN skills s ON jr.skill_id = s.skill_id
        WHERE jp.job_id = :job_id
        GROUP BY jp.job_id
    ";
    
    $jobStmt = $conn->prepare($jobQuery);
    $jobStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $jobStmt->execute();
    $job = $jobStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }
    
    // Get user's skills
    $skillsQuery = "
        SELECT s.skill_name
        FROM seeker_skills ss
        INNER JOIN skills s ON ss.skill_id = s.skill_id
        WHERE ss.seeker_id = :seeker_id
    ";
    
    $skillsStmt = $conn->prepare($skillsQuery);
    $skillsStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $skillsStmt->execute();
    $userSkills = $skillsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Calculate resume match percentage
    $matchPercentage = calculateResumeMatch($job['job_requirements'], $userSkills);
    
    // Generate personalization tips
    $personalizationTips = generatePersonalizationTips($job, $userSkills);
    
    // Check if already applied
    $appliedQuery = "SELECT application_id FROM job_applications WHERE seeker_id = :seeker_id AND job_id = :job_id";
    $appliedStmt = $conn->prepare($appliedQuery);
    $appliedStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $appliedStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $appliedStmt->execute();
    $alreadyApplied = $appliedStmt->fetch() ? true : false;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'job' => [
                'job_id' => $job_id,
                'job_title' => $job['job_title'],
                'company_name' => $job['company_name']
            ],
            'resume' => $resume ? [
                'resume_id' => $resume['resume_id'],
                'file_name' => $resume['file_name'],
                'file_path' => $resume['file_path'],
                'file_type' => $resume['file_type'],
                'upload_date' => $resume['upload_date'],
                'match_percentage' => $matchPercentage
            ] : null,
            'personalization_tips' => $personalizationTips,
            'already_applied' => $alreadyApplied,
            'user_skills' => $userSkills
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_application_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load application data'
    ]);
}

// Calculate resume match percentage based on skills
function calculateResumeMatch($jobRequirements, $userSkills) {
    if (empty($jobRequirements) || empty($userSkills)) {
        return 50; // Default if no data
    }
    
    // Parse job requirements
    $requiredSkills = [];
    if ($jobRequirements) {
        $requirements = explode(',', $jobRequirements);
        foreach ($requirements as $req) {
            $parts = explode(':', $req);
            if (count($parts) >= 1) {
                $requiredSkills[] = strtolower(trim($parts[0]));
            }
        }
    }
    
    if (empty($requiredSkills)) {
        return 75; // Default if no specific requirements
    }
    
    // Convert user skills to lowercase for comparison
    $userSkillsLower = array_map('strtolower', $userSkills);
    
    // Calculate matches
    $matches = 0;
    foreach ($requiredSkills as $requiredSkill) {
        foreach ($userSkillsLower as $userSkill) {
            if (strpos($userSkill, $requiredSkill) !== false || strpos($requiredSkill, $userSkill) !== false) {
                $matches++;
                break;
            }
        }
    }
    
    // Calculate percentage (minimum 30%, maximum 95%)
    $percentage = ($matches / count($requiredSkills)) * 100;
    return max(30, min(95, round($percentage)));
}

// Generate personalization tips based on job and user skills
function generatePersonalizationTips($job, $userSkills) {
    $tips = [];
    $jobTitle = strtolower($job['job_title']);
    $jobDescription = strtolower($job['job_description'] ?? '');
    
    // Generic tips based on job title
    if (strpos($jobTitle, 'developer') !== false || strpos($jobTitle, 'programmer') !== false) {
        $tips[] = "Highlight your programming projects and technical achievements";
        $tips[] = "Mention experience with version control and collaborative development";
        if (strpos($jobDescription, 'accessibility') !== false) {
            $tips[] = "Emphasize any experience with web accessibility standards (WCAG)";
        }
    } elseif (strpos($jobTitle, 'designer') !== false) {
        $tips[] = "Showcase your design portfolio and creative projects";
        $tips[] = "Highlight experience with design tools and user-centered design";
        $tips[] = "Mention any accessibility-focused design work";
    } elseif (strpos($jobTitle, 'support') !== false || strpos($jobTitle, 'customer') !== false) {
        $tips[] = "Emphasize your communication and problem-solving skills";
        $tips[] = "Highlight experience helping people with diverse needs";
        $tips[] = "Mention any multilingual abilities or cultural sensitivity training";
    } elseif (strpos($jobTitle, 'data') !== false || strpos($jobTitle, 'analyst') !== false) {
        $tips[] = "Showcase your analytical skills and attention to detail";
        $tips[] = "Highlight experience with data visualization and reporting";
        $tips[] = "Mention proficiency with relevant software and tools";
    } else {
        $tips[] = "Tailor your experience to match the specific job requirements";
        $tips[] = "Highlight achievements that demonstrate your capabilities";
        $tips[] = "Mention any relevant certifications or training";
    }
    
    // Add accessibility-specific tip
    $tips[] = "Emphasize how your unique perspective as a PWD can benefit the role";
    
    // Skill-specific tips
    if (!empty($userSkills)) {
        $hasRelevantSkills = false;
        foreach ($userSkills as $skill) {
            if (strpos($jobDescription, strtolower($skill)) !== false) {
                $hasRelevantSkills = true;
                break;
            }
        }
        
        if ($hasRelevantSkills) {
            $tips[] = "Highlight specific skills that directly match the job requirements";
        } else {
            $tips[] = "Connect your transferable skills to the job requirements";
        }
    }
    
    return array_slice($tips, 0, 4); // Return max 4 tips
}
?>