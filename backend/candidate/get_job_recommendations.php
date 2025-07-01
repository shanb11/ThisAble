<?php
// backend/candidate/get_job_recommendations.php
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

try {
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
    $exclude_applied = isset($_GET['exclude_applied']) ? (bool)$_GET['exclude_applied'] : true;
    
    // Get user's skills
    $userSkillsQuery = "
        SELECT s.skill_id, s.skill_name, sc.category_name
        FROM seeker_skills ss
        INNER JOIN skills s ON ss.skill_id = s.skill_id
        INNER JOIN skill_categories sc ON s.category_id = sc.category_id
        WHERE ss.seeker_id = :seeker_id
    ";
    
    $skillsStmt = $conn->prepare($userSkillsQuery);
    $skillsStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $skillsStmt->execute();
    $userSkills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $userSkillIds = array_column($userSkills, 'skill_id');
    $userSkillNames = array_column($userSkills, 'skill_name');
    
    // Get user preferences
    $preferencesQuery = "
        SELECT work_style, job_type, salary_range, availability
        FROM user_preferences
        WHERE seeker_id = :seeker_id
    ";
    
    $prefStmt = $conn->prepare($preferencesQuery);
    $prefStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $prefStmt->execute();
    $preferences = $prefStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get user's location (city, province)
    $locationQuery = "
        SELECT city, province
        FROM job_seekers
        WHERE seeker_id = :seeker_id
    ";
    
    $locStmt = $conn->prepare($locationQuery);
    $locStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $locStmt->execute();
    $userLocation = $locStmt->fetch(PDO::FETCH_ASSOC);
    
    // Build recommendations query
    $recommendationsQuery = "
        SELECT DISTINCT
            jp.job_id,
            jp.job_title,
            jp.job_description,
            jp.job_requirements,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.posted_at,
            jp.views_count,
            jp.applications_count,
            
            -- Company info
            e.company_name,
            e.company_logo_path,
            e.industry,
            
            -- Match scoring
            (
                -- Skill match score (40% weight)
                CASE WHEN EXISTS (
                    SELECT 1 FROM job_requirements jr 
                    WHERE jr.job_id = jp.job_id 
                    AND jr.skill_id IN (" . (empty($userSkillIds) ? '0' : implode(',', array_map('intval', $userSkillIds))) . ")
                ) THEN 40 ELSE 0 END +
                
                -- Location match score (20% weight)
                CASE 
                    WHEN jp.remote_work_available = 1 THEN 20
                    WHEN jp.location LIKE :user_city OR jp.location LIKE :user_province THEN 20
                    WHEN jp.location LIKE '%General Trias%' OR jp.location LIKE '%Cavite%' THEN 15
                    ELSE 0 
                END +
                
                -- Employment type match (15% weight)
                CASE 
                    WHEN :job_type_pref IS NULL OR jp.employment_type LIKE CONCAT('%', :job_type_pref, '%') THEN 15
                    ELSE 0 
                END +
                
                -- Work style match (15% weight)
                CASE 
                    WHEN :work_style_pref = 'remote' AND jp.remote_work_available = 1 THEN 15
                    WHEN :work_style_pref = 'hybrid' AND (jp.remote_work_available = 1 OR jp.flexible_schedule = 1) THEN 15
                    WHEN :work_style_pref = 'onsite' THEN 10
                    ELSE 5 
                END +
                
                -- Recency bonus (10% weight)
                CASE 
                    WHEN jp.posted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 10
                    WHEN jp.posted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 5
                    ELSE 0 
                END
            ) as match_score,
            
            -- Calculate skill matches
            (
                SELECT COUNT(*)
                FROM job_requirements jr
                WHERE jr.job_id = jp.job_id 
                AND jr.skill_id IN (" . (empty($userSkillIds) ? '0' : implode(',', array_map('intval', $userSkillIds))) . ")
            ) as skill_matches,
            
            (
                SELECT COUNT(*)
                FROM job_requirements jr
                WHERE jr.job_id = jp.job_id
            ) as total_requirements
            
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active'
        AND jp.application_deadline >= CURDATE()
    ";
    
    $params = [
        ':user_city' => '%' . ($userLocation['city'] ?? '') . '%',
        ':user_province' => '%' . ($userLocation['province'] ?? '') . '%',
        ':job_type_pref' => $preferences['job_type'] ?? null,
        ':work_style_pref' => $preferences['work_style'] ?? null
    ];
    
    // Exclude jobs already applied to
    if ($exclude_applied) {
        $recommendationsQuery .= " AND jp.job_id NOT IN (
            SELECT job_id FROM job_applications WHERE seeker_id = :seeker_id
        )";
        $params[':seeker_id'] = $seeker_id;
    }
    
    // Order by match score and limit
    $recommendationsQuery .= " 
        ORDER BY match_score DESC, jp.posted_at DESC 
        LIMIT :limit
    ";
    $params[':limit'] = $limit;
    
    $stmt = $conn->prepare($recommendationsQuery);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === ':seeker_id' || $key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format recommendations for frontend
    $formattedRecommendations = [];
    foreach ($recommendations as $job) {
        // Calculate match percentage
        $matchPercentage = min(100, max(20, $job['match_score']));
        
        // Get specific skill matches
        $skillMatchesQuery = "
            SELECT s.skill_name, jr.is_required, jr.experience_level
            FROM job_requirements jr
            INNER JOIN skills s ON jr.skill_id = s.skill_id
            WHERE jr.job_id = :job_id
            AND jr.skill_id IN (" . (empty($userSkillIds) ? '0' : implode(',', array_map('intval', $userSkillIds))) . ")
        ";
        
        $skillMatchStmt = $conn->prepare($skillMatchesQuery);
        $skillMatchStmt->bindValue(':job_id', $job['job_id'], PDO::PARAM_INT);
        $skillMatchStmt->execute();
        $matchingSkills = $skillMatchStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Determine why this job is recommended
        $reasons = [];
        if ($job['skill_matches'] > 0) {
            $reasons[] = $job['skill_matches'] . ' skill match' . ($job['skill_matches'] > 1 ? 'es' : '');
        }
        if ($job['remote_work_available']) {
            $reasons[] = 'Remote work available';
        }
        if (stripos($job['location'], $userLocation['city'] ?? '') !== false) {
            $reasons[] = 'Near your location';
        }
        if ($job['flexible_schedule']) {
            $reasons[] = 'Flexible schedule';
        }
        
        // Generate company logo
        $companyLogo = generateCompanyLogo($job['company_name']);
        
        // Format posted date
        $postedAgo = getTimeAgo($job['posted_at']);
        
        $formattedRecommendations[] = [
            'job_id' => $job['job_id'],
            'job_title' => $job['job_title'],
            'company_name' => $job['company_name'],
            'company_logo' => $companyLogo,
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'],
            'remote_work_available' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'job_description' => substr($job['job_description'], 0, 200) . '...',
            'match_percentage' => $matchPercentage,
            'skill_matches' => (int)$job['skill_matches'],
            'total_requirements' => (int)$job['total_requirements'],
            'matching_skills' => array_column($matchingSkills, 'skill_name'),
            'reasons' => $reasons,
            'posted_at' => $job['posted_at'],
            'posted_ago' => $postedAgo,
            'views_count' => (int)$job['views_count'],
            'applications_count' => (int)$job['applications_count'],
            'industry' => $job['industry']
        ];
    }
    
    // If we don't have enough recommendations with user skills, add some general recommendations
    if (count($formattedRecommendations) < $limit) {
        $remainingLimit = $limit - count($formattedRecommendations);
        $existingJobIds = array_column($formattedRecommendations, 'job_id');
        $excludeIds = empty($existingJobIds) ? '0' : implode(',', $existingJobIds);
        
        $generalQuery = "
            SELECT 
                jp.job_id,
                jp.job_title,
                jp.location,
                jp.employment_type,
                jp.salary_range,
                jp.remote_work_available,
                jp.flexible_schedule,
                jp.posted_at,
                jp.views_count,
                jp.applications_count,
                e.company_name,
                e.industry
            FROM job_posts jp
            INNER JOIN employers e ON jp.employer_id = e.employer_id
            WHERE jp.job_status = 'active'
            AND jp.application_deadline >= CURDATE()
            AND jp.job_id NOT IN ($excludeIds)
        ";
        
        if ($exclude_applied) {
            $generalQuery .= " AND jp.job_id NOT IN (
                SELECT job_id FROM job_applications WHERE seeker_id = :seeker_id
            )";
        }
        
        $generalQuery .= " ORDER BY jp.posted_at DESC LIMIT :remaining_limit";
        
        $generalStmt = $conn->prepare($generalQuery);
        if ($exclude_applied) {
            $generalStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
        }
        $generalStmt->bindValue(':remaining_limit', $remainingLimit, PDO::PARAM_INT);
        $generalStmt->execute();
        $generalJobs = $generalStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($generalJobs as $job) {
            $formattedRecommendations[] = [
                'job_id' => $job['job_id'],
                'job_title' => $job['job_title'],
                'company_name' => $job['company_name'],
                'company_logo' => generateCompanyLogo($job['company_name']),
                'location' => $job['location'],
                'employment_type' => $job['employment_type'],
                'salary_range' => $job['salary_range'],
                'remote_work_available' => (bool)$job['remote_work_available'],
                'flexible_schedule' => (bool)$job['flexible_schedule'],
                'match_percentage' => 50, // Default match for general recommendations
                'skill_matches' => 0,
                'total_requirements' => 0,
                'matching_skills' => [],
                'reasons' => ['New opportunity', 'Recently posted'],
                'posted_at' => $job['posted_at'],
                'posted_ago' => getTimeAgo($job['posted_at']),
                'views_count' => (int)$job['views_count'],
                'applications_count' => (int)$job['applications_count'],
                'industry' => $job['industry']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'recommendations' => $formattedRecommendations,
        'total_count' => count($formattedRecommendations),
        'user_skills' => $userSkillNames,
        'preferences' => $preferences,
        'filters_applied' => [
            'limit' => $limit,
            'exclude_applied' => $exclude_applied
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_job_recommendations.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch job recommendations'
    ]);
}

// Helper functions
function generateCompanyLogo($companyName) {
    $words = explode(' ', $companyName);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($companyName, 0, 2));
    }
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>