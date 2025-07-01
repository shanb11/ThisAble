<?php
/**
 * Save Accommodations API for ThisAble Mobile
 * Saves workplace accommodations to database
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

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
    
    // Extract accommodation data
    $disabilityType = $input['disability_type'] ?? null; // 'apparent' or 'non-apparent'
    $accommodations = $input['accommodations'] ?? [];
    $noAccommodationsNeeded = $input['no_accommodations_needed'] ?? false;
    
    // Validation
    if (!in_array($disabilityType, ['apparent', 'non-apparent'])) {
        ApiResponse::validationError(['disability_type' => 'Invalid disability type']);
    }
    
    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Prepare accommodation list for database
    $accommodationList = json_encode($accommodations);
    
    // Check if accommodations already exist
    $checkStmt = $conn->prepare("SELECT accommodation_id FROM workplace_accommodations WHERE seeker_id = :seeker_id");
    $checkStmt->bindParam(':seeker_id', $seekerId);
    $checkStmt->execute();
    
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
        $updateStmt->bindParam(':no_accommodations_needed', $noAccommodationsNeeded ? 1 : 0);
        $updateStmt->execute();
        
    } else {
        // Insert new accommodations
        $insertStmt = $conn->prepare("INSERT INTO workplace_accommodations 
                                     (seeker_id, disability_type, accommodation_list, no_accommodations_needed) 
                                     VALUES (:seeker_id, :disability_type, :accommodation_list, :no_accommodations_needed)");
        
        $insertStmt->bindParam(':seeker_id', $seekerId);
        $insertStmt->bindParam(':disability_type', $disabilityType);
        $insertStmt->bindParam(':accommodation_list', $accommodationList);
        $insertStmt->bindParam(':no_accommodations_needed', $noAccommodationsNeeded ? 1 : 0);
        $insertStmt->execute();
    }
    
    // Log activity
    ApiResponse::logActivity('accommodations_saved', [
        'user_id' => $seekerId,
        'disability_type' => $disabilityType,
        'accommodation_count' => count($accommodations),
        'no_accommodations_needed' => $noAccommodationsNeeded
    ]);
    
    ApiResponse::success([
        'accommodations_saved' => true,
        'disability_type' => $disabilityType,
        'accommodation_count' => count($accommodations)
    ], "Accommodations saved successfully");
    
} catch(PDOException $e) {
    error_log("Save accommodations database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Save accommodations error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while saving accommodations");
}
?>