<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Start output buffering to catch any unexpected output
ob_start();

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display to browser
ini_set('log_errors', 1);
ini_set('error_log', '../error_log.txt'); // Save errors to a log file

// Set JSON content type
header('Content-Type: application/json');

try {
    // Database credentials - MODIFY THESE WITH YOUR ACTUAL VALUES
    $db_host = "localhost";
    $db_user = "root";  // Your database username
    $db_pass = "";      // Your database password
    $db_name = "jobportal_db";
    
    // Create connection directly (instead of including db.php)
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    // Query to get all skills with their categories
    $query = "
        SELECT s.skill_id, s.skill_name, s.skill_icon, s.skill_tooltip, c.category_name
        FROM skills s
        JOIN skill_categories c ON s.category_id = c.category_id
        ORDER BY c.category_name, s.skill_name
    ";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    // Count records
    $count = mysqli_num_rows($result);
    
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
    
    // Discard any unexpected output
    ob_end_clean();
    
    // Return successful JSON response
    echo json_encode($skills);
    
} catch (Exception $e) {
    // Discard any unexpected output
    ob_end_clean();
    
    // Log the error
    error_log("Skills API Error: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'error' => "Server error occurred: " . $e->getMessage()
    ]);
}

// Close connection if it exists
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>