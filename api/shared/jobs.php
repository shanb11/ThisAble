<?php
/**
 * Jobs API for ThisAble Landing Page - FIXED FOR SUPABASE
 * File: api/shared/jobs.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // ✅ FIXED: Use proper Supabase database connection
    require_once __DIR__ . '/../config/database.php';
    
    // ✅ FIXED: Use $conn from database.php (not $pdo)
    if (!isset($conn) || $conn === null) {
        throw new Exception("Database connection failed");
    }

    // Get query parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $job_type = isset($_GET['job_type']) ? trim($_GET['job_type']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // PostgreSQL compatible query
    $sql = "SELECT 
                jp.job_id,
                jp.job_title,
                jp.job_description,
                jp.job_requirements,
                jp.department,
                jp.location,
                jp.employment_type,
                jp.salary_range,
                jp.remote_work_available,
                jp.flexible_schedule,
                jp.application_deadline,
                jp.posted_at,
                jp.created_at,
                jp.views_count,
                jp.applications_count,
                e.company_name,
                e.company_logo_path,
                e.industry,
                e.industry_id,
                i.industry_name,
                EXTRACT(DAY FROM (NOW() - COALESCE(jp.posted_at, jp.created_at))) as days_ago
            FROM job_posts jp
            JOIN employers e ON jp.employer_id = e.employer_id
            LEFT JOIN industries i ON e.industry_id = i.industry_id
            WHERE jp.job_status = 'active'";

    $params = [];

    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (jp.job_title ILIKE ? OR jp.job_description ILIKE ? OR jp.job_requirements ILIKE ? OR e.company_name ILIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Add location filter
    if (!empty($location)) {
        $sql .= " AND jp.location ILIKE ?";
        $params[] = "%{$location}%";
    }

    // Add category filter (map to departments)
    if (!empty($category)) {
        $categoryMap = [
            'education' => ['Education', 'Training', 'Teaching'],
            'office' => ['Administration', 'Office', 'Administrative'],
            'customer' => ['Customer Service', 'Support', 'Call Center'],
            'business' => ['Business', 'Management', 'Operations'],
            'healthcare' => ['Healthcare', 'Medical', 'Wellness', 'Health'],
            'finance' => ['Finance', 'Accounting', 'Banking'],
            'engineering' => ['Engineering', 'Technical', 'IT'],
            'design' => ['Design', 'Creative', 'Art'],
            'marketing' => ['Marketing', 'Sales', 'Advertising']
        ];

        if (isset($categoryMap[$category])) {
            $categoryConditions = [];
            foreach ($categoryMap[$category] as $dept) {
                $categoryConditions[] = "jp.department ILIKE ?";
                $params[] = "%{$dept}%";
            }
            $sql .= " AND (" . implode(' OR ', $categoryConditions) . ")";
        }
    }

    // Add job type filter
    if (!empty($job_type)) {
        $sql .= " AND jp.employment_type = ?";
        $params[] = $job_type;
    }

    // Add ordering and pagination
    $sql .= " ORDER BY jp.posted_at DESC, jp.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format job data
    $formattedJobs = [];
    foreach ($jobs as $job) {
        $formattedJobs[] = [
            'job_id' => (int)$job['job_id'],
            'job_title' => $job['job_title'],
            'company_name' => $job['company_name'],
            'company_logo' => $job['company_logo_path'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'],
            'department' => $job['department'],
            'industry' => $job['industry_name'] ?? $job['industry'],
            'remote_work' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'posted_date' => $job['posted_at'] ?? $job['created_at'],
            'days_ago' => (int)$job['days_ago'],
            'views_count' => (int)$job['views_count'],
            'applications_count' => (int)$job['applications_count'],
            'job_description' => $job['job_description'],
            'job_requirements' => $job['job_requirements'],
            'application_deadline' => $job['application_deadline']
        ];
    }

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM job_posts jp 
                 JOIN employers e ON jp.employer_id = e.employer_id 
                 WHERE jp.job_status = 'active'";
    $countParams = [];

    if (!empty($search)) {
        $countSql .= " AND (jp.job_title ILIKE ? OR jp.job_description ILIKE ? OR jp.job_requirements ILIKE ? OR e.company_name ILIKE ?)";
        $searchTerm = "%{$search}%";
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }

    if (!empty($location)) {
        $countSql .= " AND jp.location ILIKE ?";
        $countParams[] = "%{$location}%";
    }

    if (!empty($category) && isset($categoryMap[$category])) {
        $categoryConditions = [];
        foreach ($categoryMap[$category] as $dept) {
            $categoryConditions[] = "jp.department ILIKE ?";
            $countParams[] = "%{$dept}%";
        }
        $countSql .= " AND (" . implode(' OR ', $categoryConditions) . ")";
    }

    if (!empty($job_type)) {
        $countSql .= " AND jp.employment_type = ?";
        $countParams[] = $job_type;
    }

    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($countParams);
    $totalCount = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'jobs' => $formattedJobs,
            'pagination' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $totalCount
            ],
            'filters' => [
                'search' => $search,
                'location' => $location,
                'category' => $category,
                'job_type' => $job_type
            ]
        ],
        'message' => count($formattedJobs) . ' jobs found successfully'
    ]);

} catch (PDOException $e) {
    error_log("Database Error in jobs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage(),
        'error' => 'Could not connect to database'
    ]);
} catch (Exception $e) {
    error_log("General Error in jobs.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>