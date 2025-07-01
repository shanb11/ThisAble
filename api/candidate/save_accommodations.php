<?php
/**
 * Save Accommodations API for ThisAble Mobile
 * Uses EXACT same authentication as save_skills.php (PROVEN WORKING)
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// DEBUG: Log all received data (EXACT COPY from save_skills.php)
error_log("=== SAVE ACCOMMODATIONS DEBUG ===");
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
    
    // Extract accommodation data
    $disabilityType = $input['disability_type'] ?? null; // 'apparent' or 'non-apparent'
    $accommodations = $input['accommodations'] ?? [];
    $noAccommodationsNeeded = $input['no_accommodations_needed'] ?? false;
    
    // Validation
    $validDisabilityTypes = ['apparent', 'non-apparent'];
    if (!$disabilityType || !in_array($disabilityType, $validDisabilityTypes)) {
        ApiResponse::validationError(['disability_type' => 'Invalid disability type selection']);
    }
    
    // Prepare accommodation list for database
    $accommodationList = json_encode($accommodations);
    
    // Get database connection (EXACT COPY from save_skills.php)
    $conn = ApiDatabase::getConnection();
    
    // Begin transaction (EXACT COPY from save_skills.php)
    $conn->beginTransaction();
    
    try {
        // Check if accommodations already exist
        $checkStmt = $conn->prepare("SELECT accommodation_id FROM workplace_accommodations WHERE seeker_id = :seeker_id");
        $checkStmt->bindParam(':seeker_id', $seekerId);
        $checkStmt->execute();
        
        // FIXED: Store boolean value in variable for bindParam
        $noAccommodationsValue = $noAccommodationsNeeded ? 1 : 0;
        
        if ($checkStmt->rowCount() > 0) {
            // Update existing accommodations
            $updateStmt = $conn->prepare("UPDATE workplace_accommodations SET 
                                         disability_type = :disability_type,
                                         accommodation_list = :accommodation_list,
                                         no_accommodations_needed = :no_accommodations_needed,
                                         updated_at = NOW()
                                         WHERE seeker_id = :seeker_id");
            
            $updateStmt->bindParam(':seeker_id', $seekerId);
            $updateStmt->bindParam(':disability_type', $disabilityType);
            $updateStmt->bindParam(':accommodation_list', $accommodationList);
            $updateStmt->bindParam(':no_accommodations_needed', $noAccommodationsValue); // FIXED
            $updateStmt->execute();
            
        } else {
            // Insert new accommodations
            $insertStmt = $conn->prepare("INSERT INTO workplace_accommodations 
                                         (seeker_id, disability_type, accommodation_list, no_accommodations_needed) 
                                         VALUES (:seeker_id, :disability_type, :accommodation_list, :no_accommodations_needed)");
            
            $insertStmt->bindParam(':seeker_id', $seekerId);
            $insertStmt->bindParam(':disability_type', $disabilityType);
            $insertStmt->bindParam(':accommodation_list', $accommodationList);
            $insertStmt->bindParam(':no_accommodations_needed', $noAccommodationsValue); // FIXED
            $insertStmt->execute();
        }
        
        // Commit transaction (EXACT COPY from save_skills.php)
        $conn->commit();
        
        // Log activity (EXACT COPY from save_skills.php)
        ApiResponse::logActivity('accommodations_saved', [
            'user_id' => $seekerId,
            'disability_type' => $disabilityType,
            'accommodations_count' => count($accommodations),
            'no_accommodations_needed' => $noAccommodationsNeeded
        ]);
        
        ApiResponse::success([
            'accommodations_saved' => true,
            'disability_type' => $disabilityType,
            'accommodations' => $accommodations,
            'no_accommodations_needed' => $noAccommodationsNeeded
        ], "Workplace accommodations saved successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    error_log("Save accommodations database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Save accommodations error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while saving accommodations");
}
?>