<?php
/**
 * Get Skills API
 * Fetches all skills organized by categories for skill selection page
 * Works on both localhost and Railway production
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to catch any unexpected output
ob_start();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to browser
ini_set('log_errors', 1);

// Set JSON content type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // CRITICAL FIX: Use the existing db.php which handles both local and production
    require_once(__DIR__ . '/../db.php');
    
    // Check if connection exists and is valid (for PDO connections)
    if (!isset($conn)) {
        throw new Exception("Database connection not available");
    }
    
    // Query to get all skills with their categories
    $query = "
        SELECT s.skill_id, s.skill_name, s.skill_icon, s.skill_tooltip, c.category_name
        FROM skills s
        JOIN skill_categories c ON s.category_id = c.category_id
        ORDER BY c.category_name, s.skill_name
    ";
    
    // Execute query (supports both MySQLi and PDO)
    if ($conn instanceof PDO) {
        // PDO connection
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result === false) {
            throw new Exception("Database query failed");
        }
        
        $skills = [];
        foreach ($result as $row) {
            $skills[] = [
                'id' => $row['skill_id'],
                'name' => $row['skill_name'],
                'category' => strtolower(str_replace(' ', '_', $row['category_name'])),
                'icon' => $row['skill_icon'],
                'tooltip' => $row['skill_tooltip']
            ];
        }
    } else {
        // MySQLi connection (for backwards compatibility)
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            throw new Exception("Database query failed: " . mysqli_error($conn));
        }
        
        $skills = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $skills[] = [
                'id' => $row['skill_id'],
                'name' => $row['skill_name'],
                'category' => strtolower(str_replace(' ', '_', $row['category_name'])),
                'icon' => $row['skill_icon'],
                'tooltip' => $row['skill_tooltip']
            ];
        }
    }
    
    // Discard any unexpected output
    ob_end_clean();
    
    // Log success for debugging
    error_log("Skills API: Successfully returned " . count($skills) . " skills");
    
    // Return successful JSON response
    echo json_encode($skills, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Discard any unexpected output
    ob_end_clean();
    
    // Log the detailed error
    error_log("Skills API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return error as JSON
    http_response_code(500);
    echo json_encode([
        'error' => "Server error occurred: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>