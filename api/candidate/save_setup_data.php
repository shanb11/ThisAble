<?php
/**
 * Save Setup Data API for ThisAble Mobile
 * EXACT COPY of save_skills.php authentication logic
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data (EXACT COPY from save_skills.php)
error_log("=== SAVE SETUP DATA DEBUG ===");
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
    
    // Extract setup data instead of skill IDs
    $workStyle = $input['work_style'] ?? null;
    $jobType = $input['job_type'] ?? null;
    $salaryRange = $input['salary_range'] ?? null;
    $availability = $input['availability'] ?? null;
    
    // Validation
    $validWorkStyles = ['remote', 'hybrid', 'onsite'];
    $validJobTypes = ['freelance', 'parttime', 'fulltime'];
    
    if ($workStyle && !in_array($workStyle, $validWorkStyles)) {
        ApiResponse::validationError(['work_style' => 'Invalid work style']);
    }
    
    if ($jobType && !in_array($jobType, $validJobTypes)) {
        ApiResponse::validationError(['job_type' => 'Invalid job type']);
    }
    
    // Get database connection (EXACT COPY from save_skills.php)
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction (EXACT COPY from save_skills.php)
    $conn->beginTransaction();
    
    try {
        // Check if preferences exist
        $checkStmt = $conn->prepare("SELECT preference_id FROM user_preferences WHERE seeker_id = :seeker_id");
        $checkStmt->bindParam(':seeker_id', $seekerId);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing preferences
            $updateStmt = $conn->prepare("UPDATE user_preferences SET 
                                         work_style = :work_style,
                                         job_type = :job_type,
                                         salary_range = :salary_range,
                                         availability = :availability,
                                         updated_at = NOW()
                                         WHERE seeker_id = :seeker_id");
            
            $updateStmt->bindParam(':seeker_id', $seekerId);
            $updateStmt->bindParam(':work_style', $workStyle);
            $updateStmt->bindParam(':job_type', $jobType);
            $updateStmt->bindParam(':salary_range', $salaryRange);
            $updateStmt->bindParam(':availability', $availability);
            $updateStmt->execute();
            
        } else {
            // Insert new preferences
            $insertStmt = $conn->prepare("INSERT INTO user_preferences 
                                         (seeker_id, work_style, job_type, salary_range, availability) 
                                         VALUES (:seeker_id, :work_style, :job_type, :salary_range, :availability)");
            
            $insertStmt->bindParam(':seeker_id', $seekerId);
            $insertStmt->bindParam(':work_style', $workStyle);
            $insertStmt->bindParam(':job_type', $jobType);
            $insertStmt->bindParam(':salary_range', $salaryRange);
            $insertStmt->bindParam(':availability', $availability);
            $insertStmt->execute();
        }
        
        // Commit transaction (EXACT COPY from save_skills.php)
        $conn->commit();
        
        // Log activity (EXACT COPY from save_skills.php)
        ApiResponse::logActivity('setup_data_saved', [
            'user_id' => $seekerId,
            'work_style' => $workStyle,
            'job_type' => $jobType
        ]);
        
        ApiResponse::success([
            'preferences_saved' => true,
            'work_style' => $workStyle,
            'job_type' => $jobType
        ], "Setup data saved successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Save setup data database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Save setup data error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while saving setup data");
}
?>