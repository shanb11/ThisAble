<?php
// backend/candidate/get_job_listings.php - Updated with applied status
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';

try {
    // Get current user ID
    $current_seeker_id = $_SESSION['seeker_id'] ?? null;
    
    // Get parameters
    $search = $_GET['search'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    // Base query for jobs with applied status
    $baseQuery = "
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.created_at,
            jp.posted_at,
            jp.views_count,
            jp.applications_count,
            jp.remote_work_available,
            jp.flexible_schedule,
            e.employer_id,
            e.company_name,
            e.industry,
            e.verification_status,
            SUBSTRING(jp.job_description, 1, 200) as job_description,
            jp.job_requirements";
    
    // Add applied status check if user is logged in
    if ($current_seeker_id) {
        $baseQuery .= ",
            CASE 
                WHEN ja.application_id IS NOT NULL THEN 1 
                ELSE 0 
            END as has_applied,
            ja.application_status,
            ja.applied_at";
    } else {
        $baseQuery .= ",
            0 as has_applied,
            NULL as application_status,
            NULL as applied_at";
    }
    
    $baseQuery .= "
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id";
    
    // Left join with applications if user is logged in
    if ($current_seeker_id) {
        $baseQuery .= "
        LEFT JOIN job_applications ja ON jp.job_id = ja.job_id AND ja.seeker_id = :current_seeker_id";
    }
    
    $baseQuery .= "
        WHERE jp.job_status = 'active' 
        AND e.verification_status = 'verified'";
    
    $params = [];
    
    // Add current seeker ID parameter if logged in
    if ($current_seeker_id) {
        $params[':current_seeker_id'] = $current_seeker_id;
    }
    
    // Add search filter
    if (!empty($search)) {
        $baseQuery .= " AND (
            jp.job_title LIKE :search 
            OR e.company_name LIKE :search 
            OR jp.job_description LIKE :search
            OR jp.location LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Add ordering and limits
    $baseQuery .= " ORDER BY jp.posted_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    // Execute main query
    $stmt = $conn->prepare($baseQuery);
    
    // Bind parameters with proper types
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset' || $key === ':current_seeker_id') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
    }
    
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM job_posts jp
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active' 
        AND e.verification_status = 'verified'";
    
    $countParams = [];
    
    if (!empty($search)) {
        $countQuery .= " AND (
            jp.job_title LIKE :search 
            OR e.company_name LIKE :search 
            OR jp.job_description LIKE :search
            OR jp.location LIKE :search
        )";
        $countParams[':search'] = '%' . $search . '%';
    }
    
    $countStmt = $conn->prepare($countQuery);
    foreach ($countParams as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Process each job to add accommodations and work mode
    foreach ($jobs as &$job) {
        // Get accommodations for this job
        $accommodationsQuery = "
            SELECT 
                wheelchair_accessible,
                flexible_schedule,
                assistive_technology,
                remote_work_option,
                screen_reader_compatible,
                sign_language_interpreter,
                modified_workspace,
                transportation_support,
                additional_accommodations
            FROM job_accommodations 
            WHERE job_id = :job_id
        ";
        
        $accStmt = $conn->prepare($accommodationsQuery);
        $accStmt->bindValue(':job_id', $job['job_id'], PDO::PARAM_INT);
        $accStmt->execute();
        $accommodationsData = $accStmt->fetch(PDO::FETCH_ASSOC);
        
        $accommodations = [];
        
        if ($accommodationsData) {
            $accommodationMap = [
                'wheelchair_accessible' => ['name' => 'Wheelchair Accessible', 'icon' => 'fas fa-wheelchair'],
                'flexible_schedule' => ['name' => 'Flexible Schedule', 'icon' => 'fas fa-clock'],
                'assistive_technology' => ['name' => 'Assistive Technology', 'icon' => 'fas fa-tools'],
                'remote_work_option' => ['name' => 'Remote Work', 'icon' => 'fas fa-home'],
                'screen_reader_compatible' => ['name' => 'Screen Reader Compatible', 'icon' => 'fas fa-eye'],
                'sign_language_interpreter' => ['name' => 'Sign Language Support', 'icon' => 'fas fa-hands'],
                'modified_workspace' => ['name' => 'Modified Workspace', 'icon' => 'fas fa-cog'],
                'transportation_support' => ['name' => 'Transportation Support', 'icon' => 'fas fa-bus']
            ];
            
            foreach ($accommodationMap as $field => $details) {
                if ($accommodationsData[$field] == 1) {
                    $accommodations[] = $details;
                }
            }
            
            // Add additional accommodations
            if (!empty($accommodationsData['additional_accommodations'])) {
                $additional = explode(',', $accommodationsData['additional_accommodations']);
                foreach ($additional as $acc) {
                    $acc = trim($acc);
                    if (!empty($acc)) {
                        $accommodations[] = [
                            'name' => $acc,
                            'icon' => 'fas fa-check'
                        ];
                    }
                }
            }
        }
        
        // Default accommodations if none specified
        if (empty($accommodations)) {
            $accommodations = [
                ['name' => 'PWD-Friendly Workplace', 'icon' => 'fas fa-universal-access'],
                ['name' => 'Inclusive Environment', 'icon' => 'fas fa-users'],
                ['name' => 'Equal Opportunities', 'icon' => 'fas fa-balance-scale']
            ];
        }
        
        $job['accommodations'] = $accommodations;
        
        // Determine work mode
        $workMode = 'On-site';
        if ($job['remote_work_available'] == 1) {
            if ($job['flexible_schedule'] == 1) {
                $workMode = 'Hybrid';
            } else {
                $workMode = 'Remote';
            }
        }
        $job['work_mode'] = $workMode;
        
        // Format dates
        $job['posted_date'] = timeAgo($job['posted_at'] ?: $job['created_at']);
        
        // Clean up description if truncated
        if (strlen($job['job_description']) >= 200) {
            $job['job_description'] = substr($job['job_description'], 0, 200) . '...';
        }
        
        // Ensure numeric values
        $job['views_count'] = (int)$job['views_count'];
        $job['applications_count'] = (int)$job['applications_count'];
        $job['has_applied'] = (int)$job['has_applied'];
        
        // Format applied date if exists
        if ($job['applied_at']) {
            $job['applied_date'] = timeAgo($job['applied_at']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'jobs' => $jobs,
        'total' => (int)$totalCount,
        'count' => count($jobs),
        'search' => $search,
        'current_user' => $current_seeker_id
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_job_listings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load job listings',
        'jobs' => [],
        'total' => 0
    ]);
}

// Helper function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}
?>