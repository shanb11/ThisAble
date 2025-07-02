<?php
// backend/employer/get_skills.php
// API to fetch all available skills organized by categories

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';

try {
    // Get all skills with categories from your existing database structure
    $sql = "
        SELECT 
            s.skill_id,
            s.skill_name,
            sc.category_id,
            sc.category_name,
            sc.category_icon
        FROM skills s 
        JOIN skill_categories sc ON s.category_id = sc.category_id 
        ORDER BY sc.category_name, s.skill_name
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $skills_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize skills by category for easy frontend consumption
    $organized_skills = [];
    
    foreach ($skills_data as $skill) {
        $category_name = $skill['category_name'];
        
        if (!isset($organized_skills[$category_name])) {
            $organized_skills[$category_name] = [
                'category_id' => $skill['category_id'],
                'category_name' => $category_name,
                'category_icon' => $skill['category_icon'],
                'skills' => []
            ];
        }
        
        $organized_skills[$category_name]['skills'][] = [
            'skill_id' => $skill['skill_id'],
            'skill_name' => $skill['skill_name']
        ];
    }
    
    // Convert to indexed array and add stats
    $categories = array_values($organized_skills);
    $total_skills = count($skills_data);
    $total_categories = count($categories);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'categories' => $categories,
            'total_skills' => $total_skills,
            'total_categories' => $total_categories
        ],
        'message' => "Retrieved {$total_skills} skills across {$total_categories} categories",
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    error_log("Get skills error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to retrieve skills',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>