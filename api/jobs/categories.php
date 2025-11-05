<?php
/**
 * Job Categories API - IMPROVED FIX FOR MYSQL
 * File: api/jobs/categories.php
 * 
 * CHANGES: 
 * - Replaced ILIKE with LIKE for MySQL
 * - Added better error handling for empty conditions
 * - Added validation and debugging
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
    // Use proper database connection
    require_once __DIR__ . '/../config/database.php';
    
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
        // Validate category has departments
        if (!isset($category['departments']) || !is_array($category['departments']) || empty($category['departments'])) {
            // If no departments defined, count all active jobs
            $sql = "SELECT COUNT(*) as job_count 
                    FROM job_posts jp 
                    WHERE jp.job_status = 'active'";
            $params = [];
        } else {
            // Build department conditions for MySQL
            $deptConditions = [];
            $params = [];
            
            foreach ($category['departments'] as $dept) {
                if (!empty($dept)) {  // ✅ Validate department is not empty
                    $deptConditions[] = "jp.department LIKE ?";
                    $params[] = "%{$dept}%";
                }
            }
            
            // Build SQL query
            $sql = "SELECT COUNT(*) as job_count 
                    FROM job_posts jp 
                    WHERE jp.job_status = 'active'";
            
            // ✅ FIXED: Only add AND clause if we have conditions
            if (!empty($deptConditions)) {
                $sql .= " AND (" . implode(' OR ', $deptConditions) . ")";
            }
        }
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)$result['job_count'];
        } catch (PDOException $e) {
            // If query fails for this category, set count to 0 and continue
            error_log("Category query error for {$category['id']}: " . $e->getMessage());
            $count = 0;
        }
        
        // Format count display
        $countDisplay = $count > 0 ? $count . ' job' . ($count > 1 ? 's' : '') : 'No jobs';
        
        $categoriesWithCounts[] = [
            'id' => $category['id'],
            'name' => $category['name'],
            'icon' => $category['icon'],
            'job_count' => $count,
            'count_display' => $countDisplay
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $categoriesWithCounts,
        'total_categories' => count($categoriesWithCounts),
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage(),
        'error' => 'Could not connect to database',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load job categories',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}