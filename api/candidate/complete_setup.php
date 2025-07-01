<?php
/**
 * Complete Setup API for ThisAble Mobile
 * Marks account setup as complete
 * Uses EXACT same authentication as save_skills.php (PROVEN WORKING)
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data (EXACT COPY from save_skills.php)
error_log("=== COMPLETE SETUP DEBUG ===");
error_log("REQUEST METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("REQUEST URI: " . $_SERVER["REQUEST_URI"]);
error_log("RAW POST DATA: " . file_get_contents('php://input'));

// Test token extraction (EXACT COPY from save_skills.php)
$testToken = getAuthToken();
error_log("EXTRACTED TOKEN: " . ($testToken ? substr($testToken, 0, 20) . "..." : "NULL"));

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication (EXACT COPY from save_skills.php)
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    // Get database connection (EXACT COPY from save_skills.php)
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction (EXACT COPY from save_skills.php)
    $conn->beginTransaction();
    
    try {
        // Mark setup as complete in job_seekers table
        $updateSetupStmt = $conn->prepare("UPDATE job_seekers SET setup_complete = 1 WHERE seeker_id = :seeker_id");
        $updateSetupStmt->bindParam(':seeker_id', $seekerId);
        
        if (!$updateSetupStmt->execute()) {
            throw new Exception("Failed to update setup completion status");
        }
        
        // Verify the update was successful
        $verifyStmt = $conn->prepare("SELECT setup_complete FROM job_seekers WHERE seeker_id = :seeker_id");
        $verifyStmt->bindParam(':seeker_id', $seekerId);
        $verifyStmt->execute();
        $result = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || $result['setup_complete'] != 1) {
            throw new Exception("Setup completion verification failed");
        }
        
        // Commit transaction (EXACT COPY from save_skills.php)
        $conn->commit();
        
        // Log activity (EXACT COPY from save_skills.php)
        ApiResponse::logActivity('setup_completed', [
            'user_id' => $seekerId,
            'completion_time' => date('Y-m-d H:i:s')
        ]);
        
        ApiResponse::success([
            'setup_complete' => true,
            'redirect_to' => 'dashboard'
        ], "Account setup completed successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Complete setup database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Complete setup error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while completing setup");
}
?>