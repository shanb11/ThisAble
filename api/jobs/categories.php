<?php
/**
 * Job Categories API - HTACCESS HANDLES CORS
 * File: api/jobs/categories.php
 * 
 * IMPORTANT: CORS headers removed from PHP - .htaccess handles them
 * This prevents duplicate headers that confuse browsers
 */

// NO CORS HEADERS HERE - .htaccess handles them!
// This prevents duplicate header conflicts

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
                if (!empty($dept)) {
                    $deptConditions[] = "jp.department LIKE ?";
                    $params[] = "%{$dept}%";
                }
            }
            
            // Build SQL query
            $sql = "SELECT COUNT(*) as job_count 
                    FROM job_posts jp 
                    WHERE jp.job_status = 'active'";
            
            // Only add AND clause if we have conditions
            if (!empty($deptConditions)) {
                $sql .= " AND (" . implode(' OR ', $deptConditions) . ")";
            }
        }
        
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ✅ CRITICAL FIX: Explicitly cast to integer
            $count = (int)$result['job_count'];
            
        } catch (PDOException $e) {
            error_log("Category query error for {$category['id']}: " . $e->getMessage());
            $count = 0; // Already an integer
        }
        
        // Format count display
        $countDisplay = $count > 0 ? $count . ' job' . ($count > 1 ? 's' : '') : 'No jobs';
        
        // ✅ CRITICAL: Ensure all values are proper types for Flutter
        $categoriesWithCounts[] = [
            'id' => (string)$category['id'],           // String
            'name' => (string)$category['name'],       // String
            'icon' => (string)$category['icon'],       // String
            'job_count' => $count,                     // Integer (not string!)
            'count_display' => (string)$countDisplay   // String
        ];
    }

    // Set content type header (still needed for JSON)
    header('Content-Type: application/json; charset=UTF-8');
    
    // ✅ Set proper JSON encoding options
    echo json_encode([
        'success' => true,
        'data' => $categoriesWithCounts,
        'total_categories' => count($categoriesWithCounts),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_NUMERIC_CHECK); // This ensures numbers stay as numbers, not strings

} catch (PDOException $e) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage(),
        'error' => 'Could not connect to database',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load job categories',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}