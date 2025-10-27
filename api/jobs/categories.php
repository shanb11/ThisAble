<?php
/**
 * Job Categories API - FIXED FOR SUPABASE
 * File: api/jobs/categories.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

    // Define job categories
    $categories = [
        ['id' => 'education', 'name' => 'Education', 'icon' => 'school', 'departments' => ['Education', 'Training', 'Teaching']],
        ['id' => 'office', 'name' => 'Office & Admin', 'icon' => 'business', 'departments' => ['Administration', 'Office', 'Administrative']],
        ['id' => 'customer', 'name' => 'Customer Service', 'icon' => 'support_agent', 'departments' => ['Customer Service', 'Support', 'Call Center']],
        ['id' => 'business', 'name' => 'Business', 'icon' => 'work', 'departments' => ['Business', 'Management', 'Operations']],
        ['id' => 'healthcare', 'name' => 'Healthcare', 'icon' => 'local_hospital', 'departments' => ['Healthcare', 'Medical', 'Wellness', 'Health']],
        ['id' => 'finance', 'name' => 'Finance', 'icon' => 'account_balance', 'departments' => ['Finance', 'Accounting', 'Banking']],
        ['id' => 'engineering', 'name' => 'Engineering', 'icon' => 'engineering', 'departments' => ['Engineering', 'Technical', 'IT']],
        ['id' => 'design', 'name' => 'Design', 'icon' => 'brush', 'departments' => ['Design', 'Creative', 'Art']],
        ['id' => 'marketing', 'name' => 'Marketing', 'icon' => 'campaign', 'departments' => ['Marketing', 'Sales', 'Advertising']]
    ];

    $categoriesWithCounts = [];

    foreach ($categories as $category) {
        // Build department conditions for PostgreSQL (case-insensitive)
        $deptConditions = [];
        $params = [];
        
        foreach ($category['departments'] as $dept) {
            $deptConditions[] = "jp.department ILIKE ?";
            $params[] = "%{$dept}%";
        }
        
        $sql = "SELECT COUNT(*) as job_count 
                FROM job_posts jp 
                WHERE jp.job_status = 'active'";
        
        if (!empty($deptConditions)) {
            $sql .= " AND (" . implode(' OR ', $deptConditions) . ")";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int)$result['job_count'];
        
        // Format count display
        $countDisplay = $count > 0 ? $count . '+' : '0';
        
        $categoriesWithCounts[] = [
            'id' => $category['id'],
            'name' => $category['name'],
            'icon' => $category['icon'],
            'count' => $countDisplay,
            'job_count' => $count
        ];
    }

    // Get total statistics using PostgreSQL
    $totalSql = "SELECT COUNT(*) as total FROM job_posts WHERE job_status = 'active'";
    $totalStmt = $conn->prepare($totalSql);
    $totalStmt->execute();
    $totalJobs = (int)$totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get recent jobs count (last 7 days) - PostgreSQL compatible
    $recentSql = "SELECT COUNT(*) as recent 
                  FROM job_posts 
                  WHERE job_status = 'active' 
                  AND (posted_at >= NOW() - INTERVAL '7 days' 
                       OR (posted_at IS NULL AND created_at >= NOW() - INTERVAL '7 days'))";
    $recentStmt = $conn->prepare($recentSql);
    $recentStmt->execute();
    $recentJobs = (int)$recentStmt->fetch(PDO::FETCH_ASSOC)['recent'];

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => [
            'categories' => $categoriesWithCounts,
            'stats' => [
                'total_jobs' => $totalJobs,
                'recent_jobs' => $recentJobs,
                'active_employers' => 0
            ]
        ],
        'message' => 'Categories retrieved successfully'
    ]);

} catch (PDOException $e) {
    error_log("Database Error in categories.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage(),
        'error' => 'Could not connect to database'
    ]);
} catch (Exception $e) {
    error_log("General Error in categories.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}
?>