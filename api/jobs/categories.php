<?php
/**
 * Job Categories API for ThisAble Landing Page  
 * File: C:\xampp\htdocs\ThisAble\api\jobs\categories.php
 * 
 * FIXED: Database connection issue resolved
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
    // FIXED: Database connection - try different approaches
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
    
    // Test connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Define categories that match your Flutter constants
    $categories = [
        [
            'id' => 'education',
            'name' => 'Education & Training',
            'icon' => 'graduation-cap',
            'departments' => ['Education', 'Training', 'Teaching', 'Academic']
        ],
        [
            'id' => 'office',
            'name' => 'Office Administration',
            'icon' => 'briefcase',
            'departments' => ['Administration', 'Office', 'Administrative', 'Clerical']
        ],
        [
            'id' => 'customer',
            'name' => 'Customer Service',
            'icon' => 'headset',
            'departments' => ['Customer Service', 'Support', 'Call Center', 'Help Desk']
        ],
        [
            'id' => 'business',
            'name' => 'Business Administration',
            'icon' => 'chart-line',
            'departments' => ['Business', 'Management', 'Operations', 'Strategy']
        ],
        [
            'id' => 'healthcare',
            'name' => 'Healthcare & Wellness',
            'icon' => 'heartbeat',
            'departments' => ['Healthcare', 'Medical', 'Wellness', 'Health', 'Nursing']
        ],
        [
            'id' => 'finance',
            'name' => 'Finance & Accounting',
            'icon' => 'dollar-sign',
            'departments' => ['Finance', 'Accounting', 'Banking', 'Financial']
        ],
        [
            'id' => 'engineering',
            'name' => 'Engineering & Technical',
            'icon' => 'cog',
            'departments' => ['Engineering', 'Technical', 'IT', 'Technology', 'Software']
        ],
        [
            'id' => 'design',
            'name' => 'Design & Creative',
            'icon' => 'palette',
            'departments' => ['Design', 'Creative', 'Art', 'Graphic', 'UI/UX']
        ],
        [
            'id' => 'marketing',
            'name' => 'Marketing & Sales',
            'icon' => 'bullhorn',
            'departments' => ['Marketing', 'Sales', 'Digital Marketing', 'Advertising']
        ]
    ];

    // Get real job counts for each category
    $categoriesWithCounts = [];
    
    foreach ($categories as $category) {
        // Build query for this category
        $deptConditions = [];
        $params = [];
        
        foreach ($category['departments'] as $dept) {
            $deptConditions[] = "jp.department LIKE ?";
            $params[] = "%{$dept}%";
        }
        
        $sql = "SELECT COUNT(*) as job_count 
                FROM job_posts jp 
                WHERE jp.job_status = 'active'";
        
        if (!empty($deptConditions)) {
            $sql .= " AND (" . implode(' OR ', $deptConditions) . ")";
        }
        
        $stmt = $pdo->prepare($sql);
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

    // Get total statistics
    $totalSql = "SELECT COUNT(*) as total FROM job_posts WHERE job_status = 'active'";
    $totalStmt = $pdo->prepare($totalSql);
    $totalStmt->execute();
    $totalJobs = (int)$totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get recent jobs count (last 7 days)
    $recentSql = "SELECT COUNT(*) as recent 
                  FROM job_posts 
                  WHERE job_status = 'active' 
                  AND (posted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                       OR (posted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)))";
    $recentStmt = $pdo->prepare($recentSql);
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