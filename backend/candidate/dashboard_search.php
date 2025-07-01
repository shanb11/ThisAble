<?php
// backend/candidate/dashboard_search.php
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
$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    echo json_encode([
        'success' => true,
        'results' => [],
        'total_count' => 0,
        'message' => 'Enter a search term'
    ]);
    exit;
}

try {
    $searchResults = [];
    $totalCount = 0;
    
    // Search Jobs
    $jobSearchQuery = "
        SELECT 
            'job' as result_type,
            jp.job_id as id,
            jp.job_title as title,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.remote_work_available,
            jp.posted_at,
            e.company_name as subtitle,
            e.company_logo_path,
            'joblistings.php' as link_base,
            (
                CASE 
                    WHEN jp.job_title LIKE :exact_query THEN 100
                    WHEN jp.job_title LIKE :start_query THEN 90
                    WHEN jp.job_title LIKE :search_query THEN 70
                    WHEN jp.job_description LIKE :search_query THEN 50
                    WHEN jp.job_requirements LIKE :search_query THEN 40
                    ELSE 30
                END +
                CASE 
                    WHEN e.company_name LIKE :search_query THEN 20
                    ELSE 0
                END +
                CASE 
                    WHEN jp.location LIKE :search_query THEN 15
                    ELSE 0
                END
            ) as relevance_score
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active'
        AND (
            jp.job_title LIKE :search_query
            OR jp.job_description LIKE :search_query
            OR jp.job_requirements LIKE :search_query
            OR e.company_name LIKE :search_query
            OR jp.location LIKE :search_query
        )
        AND jp.job_id NOT IN (
            SELECT job_id FROM job_applications WHERE seeker_id = :seeker_id
        )
        ORDER BY relevance_score DESC, jp.posted_at DESC
        LIMIT 8
    ";
    
    $searchTerm = '%' . $query . '%';
    $exactTerm = $query;
    $startTerm = $query . '%';
    
    $jobStmt = $conn->prepare($jobSearchQuery);
    $jobStmt->bindValue(':search_query', $searchTerm);
    $jobStmt->bindValue(':exact_query', $exactTerm);
    $jobStmt->bindValue(':start_query', $startTerm);
    $jobStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $jobStmt->execute();
    $jobResults = $jobStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Search Companies
    $companySearchQuery = "
        SELECT DISTINCT
            'company' as result_type,
            e.employer_id as id,
            e.company_name as title,
            e.industry as subtitle,
            CONCAT(e.company_address, ' • ', COUNT(jp.job_id), ' active jobs') as location,
            e.company_logo_path,
            'joblistings.php' as link_base,
            (
                CASE 
                    WHEN e.company_name LIKE :exact_query THEN 100
                    WHEN e.company_name LIKE :start_query THEN 90
                    WHEN e.company_name LIKE :search_query THEN 70
                    WHEN e.industry LIKE :search_query THEN 50
                    ELSE 30
                END
            ) as relevance_score
        FROM employers e
        LEFT JOIN job_posts jp ON e.employer_id = jp.employer_id AND jp.job_status = 'active'
        WHERE (
            e.company_name LIKE :search_query
            OR e.industry LIKE :search_query
            OR e.company_description LIKE :search_query
        )
        AND e.verification_status = 'verified'
        GROUP BY e.employer_id
        ORDER BY relevance_score DESC
        LIMIT 5
    ";
    
    $companyStmt = $conn->prepare($companySearchQuery);
    $companyStmt->bindValue(':search_query', $searchTerm);
    $companyStmt->bindValue(':exact_query', $exactTerm);
    $companyStmt->bindValue(':start_query', $startTerm);
    $companyStmt->execute();
    $companyResults = $companyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Search Skills (for skill-based job suggestions)
    $skillSearchQuery = "
        SELECT 
            'skill' as result_type,
            s.skill_id as id,
            s.skill_name as title,
            sc.category_name as subtitle,
            CONCAT('Jobs requiring ', s.skill_name) as location,
            'joblistings.php' as link_base,
            (
                CASE 
                    WHEN s.skill_name LIKE :exact_query THEN 100
                    WHEN s.skill_name LIKE :start_query THEN 90
                    WHEN s.skill_name LIKE :search_query THEN 70
                    ELSE 50
                END
            ) as relevance_score
        FROM skills s
        INNER JOIN skill_categories sc ON s.category_id = sc.category_id
        WHERE s.skill_name LIKE :search_query
        ORDER BY relevance_score DESC
        LIMIT 5
    ";
    
    $skillStmt = $conn->prepare($skillSearchQuery);
    $skillStmt->bindValue(':search_query', $searchTerm);
    $skillStmt->bindValue(':exact_query', $exactTerm);
    $skillStmt->bindValue(':start_query', $startTerm);
    $skillStmt->execute();
    $skillResults = $skillStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine and format results
    $allResults = array_merge($jobResults, $companyResults, $skillResults);
    
    // Sort by relevance score
    usort($allResults, function($a, $b) {
        return $b['relevance_score'] - $a['relevance_score'];
    });
    
    // Format results for frontend
    foreach ($allResults as &$result) {
        // Generate company logo if not available
        if (empty($result['company_logo_path'])) {
            $result['logo'] = generateCompanyLogo($result['title']);
        } else {
            $result['logo'] = $result['company_logo_path'];
        }
        
        // Format link
        switch ($result['result_type']) {
            case 'job':
                $result['link'] = $result['link_base'] . '?job=' . $result['id'];
                $result['icon'] = 'fas fa-briefcase';
                $result['action'] = 'View Job';
                $result['meta'] = [
                    'employment_type' => $result['employment_type'] ?? '',
                    'salary_range' => $result['salary_range'] ?? '',
                    'remote_available' => $result['remote_work_available'] ?? false,
                    'posted_ago' => getTimeAgo($result['posted_at'] ?? '')
                ];
                break;
                
            case 'company':
                $result['link'] = $result['link_base'] . '?company=' . $result['id'];
                $result['icon'] = 'fas fa-building';
                $result['action'] = 'View Jobs';
                $result['meta'] = [];
                break;
                
            case 'skill':
                $result['link'] = $result['link_base'] . '?skill=' . urlencode($result['title']);
                $result['icon'] = 'fas fa-cogs';
                $result['action'] = 'Find Jobs';
                $result['meta'] = [];
                break;
        }
        
        // Remove unnecessary fields
        unset($result['company_logo_path'], $result['link_base'], $result['relevance_score']);
        if (isset($result['posted_at'])) unset($result['posted_at']);
        if (isset($result['employment_type'])) unset($result['employment_type']);
        if (isset($result['salary_range'])) unset($result['salary_range']);
        if (isset($result['remote_work_available'])) unset($result['remote_work_available']);
    }
    
    // Get search suggestions based on popular searches
    $suggestions = getSearchSuggestions($conn, $query);
    
    // Get total count for all categories
    $totalJobsQuery = "
        SELECT COUNT(*) as count
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active'
        AND (
            jp.job_title LIKE :search_query
            OR jp.job_description LIKE :search_query
            OR e.company_name LIKE :search_query
            OR jp.location LIKE :search_query
        )
    ";
    
    $totalStmt = $conn->prepare($totalJobsQuery);
    $totalStmt->bindValue(':search_query', $searchTerm);
    $totalStmt->execute();
    $totalJobs = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'results' => array_slice($allResults, 0, 10), // Limit to top 10 results
        'total_count' => count($allResults),
        'total_jobs_available' => (int)$totalJobs,
        'search_query' => $query,
        'suggestions' => $suggestions,
        'categories' => [
            'jobs' => count($jobResults),
            'companies' => count($companyResults),
            'skills' => count($skillResults)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in dashboard_search.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Search failed. Please try again.'
    ]);
}

// Helper function to generate search suggestions
function getSearchSuggestions($conn, $query) {
    // Get popular job titles that match
    $suggestionsQuery = "
        SELECT DISTINCT jp.job_title as suggestion, COUNT(*) as frequency
        FROM job_posts jp
        WHERE jp.job_status = 'active'
        AND jp.job_title LIKE :query
        AND jp.job_title != :exact_query
        GROUP BY jp.job_title
        ORDER BY frequency DESC, jp.job_title ASC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($suggestionsQuery);
    $stmt->bindValue(':query', '%' . $query . '%');
    $stmt->bindValue(':exact_query', $query);
    $stmt->execute();
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add some common search terms if not enough suggestions
    if (count($suggestions) < 3) {
        $commonTerms = [
            'developer', 'designer', 'analyst', 'manager', 'specialist',
            'assistant', 'coordinator', 'engineer', 'technician', 'administrator'
        ];
        
        foreach ($commonTerms as $term) {
            if (stripos($term, $query) !== false && !in_array($term, $suggestions)) {
                $suggestions[] = ucfirst($term);
                if (count($suggestions) >= 5) break;
            }
        }
    }
    
    return $suggestions;
}

// Helper function to generate company logo
function generateCompanyLogo($companyName) {
    $words = explode(' ', $companyName);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($companyName, 0, 2));
    }
}

// Helper function to get time ago
function getTimeAgo($datetime) {
    if (empty($datetime)) return '';
    
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>