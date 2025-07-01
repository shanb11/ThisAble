<?php
/**
 * Save Skills API for ThisAble Mobile
 * Saves selected skills to seeker_skills table
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data
error_log("=== SAVE SKILLS DEBUG ===");
error_log("REQUEST METHOD: " . $_SERVER["REQUEST_METHOD"]);
error_log("REQUEST URI: " . $_SERVER["REQUEST_URI"]);
error_log("RAW POST DATA: " . file_get_contents('php://input'));

// Test token extraction
$testToken = getAuthToken();
error_log("EXTRACTED TOKEN: " . ($testToken ? substr($testToken, 0, 20) . "..." : "NULL"));

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::validationError(['input' => 'Invalid JSON input']);
    }
    
    // Extract skill IDs
    $skillIds = $input['skill_ids'] ?? [];
    
    // Validation
    if (empty($skillIds) || !is_array($skillIds)) {
        ApiResponse::validationError(['skill_ids' => 'At least one skill must be selected']);
    }
    
    // Validate skill IDs exist
    foreach ($skillIds as $skillId) {
        if (!is_numeric($skillId)) {
            ApiResponse::validationError(['skill_ids' => 'Invalid skill ID format']);
        }
    }
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // First, remove existing skills for this user
        $deleteStmt = $conn->prepare("DELETE FROM seeker_skills WHERE seeker_id = :seeker_id");
        $deleteStmt->bindParam(':seeker_id', $seekerId);
        $deleteStmt->execute();
        
        // Insert new skills
        $insertStmt = $conn->prepare("INSERT INTO seeker_skills (seeker_id, skill_id) VALUES (:seeker_id, :skill_id)");
        
        foreach ($skillIds as $skillId) {
            $insertStmt->bindParam(':seeker_id', $seekerId);
            $insertStmt->bindParam(':skill_id', $skillId);
            $insertStmt->execute();
        }
        
        // Update setup completion progress
        //$updateStmt = $conn->prepare("UPDATE job_seekers SET setup_complete = 1 WHERE seeker_id = :seeker_id");
        //$updateStmt->bindParam(':seeker_id', $seekerId);
        //$updateStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Log activity
        ApiResponse::logActivity('skills_saved', [
            'user_id' => $seekerId,
            'skill_count' => count($skillIds),
            'skill_ids' => $skillIds
        ]);
        
        ApiResponse::success([
            'skills_saved' => count($skillIds),
            'setup_complete' => true
        ], "Skills saved successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Save skills database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Save skills error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while saving skills");
}
?>