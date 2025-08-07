<?php
/**
 * Jobs API for ThisAble Landing Page
 * File: C:\xampp\htdocs\ThisAble\api\shared\jobs.php
 * 
 * CORRECTED: Fixed database column names to match your schema
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
    // Database connection
    $pdo = null;
    
    // Method 1: Include your existing database file
    if (file_exists(__DIR__ . '/../config/database.php')) {
        include_once __DIR__ . '/../config/database.php';
    }
    
    // Method 2: If $pdo still not available, create direct connection
    if (!isset($pdo) || $pdo === null) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "jobportal_db";
        
        $pdo = new PDO(
            "mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
            $username, 
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Get query parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $location = isset($_GET['location']) ? trim($_GET['location']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $job_type = isset($_GET['job_type']) ? trim($_GET['job_type']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // CORRECTED SQL query with proper column names from your database
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
                DATEDIFF(NOW(), COALESCE(jp.posted_at, jp.created_at)) as days_ago
            FROM job_posts jp
            JOIN employers e ON jp.employer_id = e.employer_id
            LEFT JOIN industries i ON e.industry_id = i.industry_id
            WHERE jp.job_status = 'active'";

    $params = [];

    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (jp.job_title LIKE ? OR jp.job_description LIKE ? OR jp.job_requirements LIKE ? OR e.company_name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Add location filter
    if (!empty($location)) {
        $sql .= " AND jp.location LIKE ?";
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
                $categoryConditions[] = "jp.department LIKE ?";
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

    // Order by newest first
    $sql .= " ORDER BY COALESCE(jp.posted_at, jp.created_at) DESC";

    // Add limit and offset
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format jobs for Flutter
    $formattedJobs = [];
    foreach ($jobs as $job) {
        // Format posted time
        $postedTime = 'Recently posted';
        $days = (int)$job['days_ago'];
        
        if ($days == 0) {
            $postedTime = 'Today';
        } elseif ($days == 1) {
            $postedTime = '1 day ago';
        } elseif ($days <= 7) {
            $postedTime = $days . ' days ago';
        } elseif ($days <= 30) {
            $weeks = floor($days / 7);
            $postedTime = $weeks . ($weeks == 1 ? ' week ago' : ' weeks ago');
        } else {
            $months = floor($days / 30);
            $postedTime = $months . ($months == 1 ? ' month ago' : ' months ago');
        }

        // Format salary
        $salary = !empty($job['salary_range']) ? $job['salary_range'] : 'Competitive';

        $formattedJobs[] = [
            'id' => (int)$job['job_id'],
            'title' => $job['job_title'],
            'company' => $job['company_name'],
            'location' => $job['location'],
            'type' => $job['employment_type'],
            'department' => $job['department'],
            'salary' => $salary,
            'description' => $job['job_description'],
            'requirements' => $job['job_requirements'],
            'posted' => $postedTime,
            'posted_date' => $job['posted_at'],
            'deadline' => $job['application_deadline'],
            'remote_available' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'company_logo' => $job['company_logo_path'], // CORRECTED column name
            'industry' => $job['industry_name'] ?: $job['industry'], // Fallback to industry column if no industry_name
            'views' => (int)$job['views_count'],
            'applications' => (int)$job['applications_count']
        ];
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total 
                 FROM job_posts jp
                 JOIN employers e ON jp.employer_id = e.employer_id
                 WHERE jp.job_status = 'active'";

    $countParams = [];

    // Add same filters for count
    if (!empty($search)) {
        $countSql .= " AND (jp.job_title LIKE ? OR jp.job_description LIKE ? OR jp.job_requirements LIKE ? OR e.company_name LIKE ?)";
        $searchTerm = "%{$search}%";
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }

    if (!empty($location)) {
        $countSql .= " AND jp.location LIKE ?";
        $countParams[] = "%{$location}%";
    }

    if (!empty($category) && isset($categoryMap[$category])) {
        $categoryConditions = [];
        foreach ($categoryMap[$category] as $dept) {
            $categoryConditions[] = "jp.department LIKE ?";
            $countParams[] = "%{$dept}%";
        }
        $countSql .= " AND (" . implode(' OR ', $categoryConditions) . ")";
    }

    if (!empty($job_type)) {
        $countSql .= " AND jp.employment_type = ?";
        $countParams[] = $job_type;
    }

    $countStmt = $pdo->prepare($countSql);
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