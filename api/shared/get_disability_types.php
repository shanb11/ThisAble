<?php
/**
 * Get Disability Types API
 * Returns all disability types from database for mobile app
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
    
    // Fetch all disability types from database
    $stmt = $conn->prepare("
        SELECT 
            dt.disability_id,
            dt.disability_name,
            dc.category_name,
            dc.category_id
        FROM disability_types dt
        JOIN disability_categories dc ON dt.category_id = dc.category_id
        ORDER BY dc.category_id, dt.disability_id
    ");
    
    $stmt->execute();
    $disabilityTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($disabilityTypes)) {
        ApiResponse::error("No disability types found", 404);
    }
    
    // Format data for mobile app
    $formattedTypes = [];
    foreach ($disabilityTypes as $type) {
        $formattedTypes[] = [
            'id' => $type['disability_id'],
            'name' => $type['disability_name'],
            'category' => strtolower($type['category_name']), // 'apparent' or 'non-apparent'
            'category_id' => $type['category_id']
        ];
    }
    
    // Return success response
    ApiResponse::success([
        'disability_types' => $formattedTypes,
        'total_count' => count($formattedTypes)
    ], "Disability types retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Database error in get_disability_types: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Error in get_disability_types: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while fetching disability types");
}
?>