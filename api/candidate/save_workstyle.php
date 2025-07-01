<?php
/**
 * Save Workstyle API for ThisAble Mobile
 * Uses EXACT same authentication as save_skills.php (PROVEN WORKING)
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data (EXACT COPY from save_skills.php)
error_log("=== SAVE WORKSTYLE DEBUG ===");
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
    
    // Get JSON input (EXACT COPY from save_skills.php)
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::validationError(['input' => 'Invalid JSON input']);
    }
    
    // Extract workstyle data
    $workStyle = $input['work_style'] ?? null;
    
    // Validation
    $validWorkStyles = ['remote', 'hybrid', 'onsite'];
    if (!$workStyle || !in_array($workStyle, $validWorkStyles)) {
        ApiResponse::validationError(['work_style' => 'Invalid work style selection']);
    }
    
    // Get database connection (EXACT COPY from save_skills.php)
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction (EXACT COPY from save_skills.php)
    $conn->beginTransaction();
    
    try {
        // Check if preferences already exist
        $checkStmt = $conn->prepare("SELECT preference_id FROM user_preferences WHERE seeker_id = :seeker_id");
        $checkStmt->bindParam(':seeker_id', $seekerId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing preferences
            $updateStmt = $conn->prepare("UPDATE user_preferences SET 
                                         work_style = :work_style,
                                         updated_at = NOW()
                                         WHERE seeker_id = :seeker_id");
            $updateStmt->bindParam(':seeker_id', $seekerId);
            $updateStmt->bindParam(':work_style', $workStyle);
            $updateStmt->execute();
        } else {
            // Insert new preferences
            $insertStmt = $conn->prepare("INSERT INTO user_preferences 
                                         (seeker_id, work_style) 
                                         VALUES (:seeker_id, :work_style)");
            $insertStmt->bindParam(':seeker_id', $seekerId);
            $insertStmt->bindParam(':work_style', $workStyle);
            $insertStmt->execute();
        }
        
        // Commit transaction (EXACT COPY from save_skills.php)
        $conn->commit();
        
        // Log activity (EXACT COPY from save_skills.php)
        ApiResponse::logActivity('workstyle_saved', [
            'user_id' => $seekerId,
            'work_style' => $workStyle
        ]);
        
        ApiResponse::success([
            'workstyle_saved' => true,
            'work_style' => $workStyle
        ], "Work style saved successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Save workstyle database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Save workstyle error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while saving work style");
}
?>