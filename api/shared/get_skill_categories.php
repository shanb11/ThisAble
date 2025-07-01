<?php
/**
 * Get Skill Categories API
 * Returns all skill categories from database for mobile app
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
    
    // Fetch all skill categories from database
    $stmt = $conn->prepare("
        SELECT 
            category_id,
            category_name,
            category_icon
        FROM skill_categories
        ORDER BY category_id
    ");
    
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($categories)) {
        ApiResponse::error("No skill categories found", 404);
    }
    
    // Format data for mobile app
    $formattedCategories = [];
    foreach ($categories as $category) {
        $formattedCategories[] = [
            'id' => $category['category_id'],
            'name' => $category['category_name'],
            'icon' => $category['category_icon']
        ];
    }
    
    // Return success response
    ApiResponse::success([
        'skill_categories' => $formattedCategories,
        'total_count' => count($formattedCategories)
    ], "Skill categories retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Database error in get_skill_categories: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Error in get_skill_categories: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while fetching skill categories");
}
?>