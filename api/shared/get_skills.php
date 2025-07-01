<?php
/**
 * Get Skills API
 * Returns all skills from database for mobile app
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Fetch all skills from database with categories
    $stmt = $conn->prepare("
        SELECT 
            s.skill_id,
            s.skill_name,
            s.skill_icon,
            s.skill_tooltip,
            s.category_id,
            sc.category_name
        FROM skills s
        JOIN skill_categories sc ON s.category_id = sc.category_id
        ORDER BY sc.category_id, s.skill_id
    ");
    
    $stmt->execute();
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($skills)) {
        ApiResponse::error("No skills found", 404);
    }
    
    // Format data for mobile app
    $formattedSkills = [];
    foreach ($skills as $skill) {
        $formattedSkills[] = [
            'id' => $skill['skill_id'],
            'name' => $skill['skill_name'],
            'category' => strtolower(str_replace([' ', '&'], ['_', 'and'], $skill['category_name'])),
            'icon' => $skill['skill_icon'],
            'description' => $skill['skill_tooltip'],
            'category_id' => $skill['category_id']
        ];
    }
    
    // Return success response
    ApiResponse::success([
        'skills' => $formattedSkills,
        'total_count' => count($formattedSkills)
    ], "Skills retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Database error in get_skills: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Error in get_skills: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while fetching skills");
}
?>