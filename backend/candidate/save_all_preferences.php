<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Include database connection
    require_once('../db.php');
    
    // Get seeker ID
    $seekerId = isset($_POST['seeker_id']) ? $_POST['seeker_id'] : 
               (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : null);
    
    if (!$seekerId) {
        throw new Exception("No seeker ID found. Please log in again.");
    }
    
    // Log the start of the process
    error_log("Saving all preferences for seeker_id: $seekerId");
    
    // 1. SAVE SKILLS
    // ---------------
    $skills = isset($_POST['skills']) ? json_decode($_POST['skills'], true) : [];
    
    if (!empty($skills)) {
        // First, remove existing skills for this user
        $deleteQuery = "DELETE FROM seeker_skills WHERE seeker_id = $seekerId";
        if (!mysqli_query($conn, $deleteQuery)) {
            throw new Exception("Error deleting existing skills: " . mysqli_error($conn));
        }
        
        // Then insert new skills
        foreach ($skills as $skillName) {
            // Get skill ID from name
            $skillQuery = "SELECT skill_id FROM skills WHERE skill_name = '" . mysqli_real_escape_string($conn, $skillName) . "'";
            $skillResult = mysqli_query($conn, $skillQuery);
            
            if ($skillResult && $row = mysqli_fetch_assoc($skillResult)) {
                $skillId = $row['skill_id'];
                
                // Insert skill association
                $insertQuery = "INSERT INTO seeker_skills (seeker_id, skill_id) VALUES ($seekerId, $skillId)";
                if (!mysqli_query($conn, $insertQuery)) {
                    throw new Exception("Error inserting skill: " . mysqli_error($conn));
                }
                
                error_log("Saved skill: $skillName (ID: $skillId) for seeker: $seekerId");
            }
        }
    }
    
    // 2. SAVE WORK STYLE
    // ------------------
    $workStyle = isset($_POST['work_style']) ? $_POST['work_style'] : null;
    
    if ($workStyle) {
        // Check if preference exists
        $checkQuery = "SELECT preference_id FROM user_preferences WHERE seeker_id = $seekerId";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            // Update existing preference
            $updateQuery = "UPDATE user_preferences SET work_style = '" . mysqli_real_escape_string($conn, $workStyle) . "' WHERE seeker_id = $seekerId";
            if (!mysqli_query($conn, $updateQuery)) {
                throw new Exception("Error updating work style: " . mysqli_error($conn));
            }
        } else {
            // Insert new preference
            $insertQuery = "INSERT INTO user_preferences (seeker_id, work_style) VALUES ($seekerId, '" . mysqli_real_escape_string($conn, $workStyle) . "')";
            if (!mysqli_query($conn, $insertQuery)) {
                throw new Exception("Error inserting work style: " . mysqli_error($conn));
            }
        }
        
        error_log("Saved work style: $workStyle for seeker: $seekerId");
    }
    
    // 3. SAVE JOB TYPE
    // ----------------
    $jobType = isset($_POST['job_type']) ? $_POST['job_type'] : null;
    
    if ($jobType) {
        // Check if preference exists
        $checkQuery = "SELECT preference_id FROM user_preferences WHERE seeker_id = $seekerId";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            // Update existing preference
            $updateQuery = "UPDATE user_preferences SET job_type = '" . mysqli_real_escape_string($conn, $jobType) . "' WHERE seeker_id = $seekerId";
            if (!mysqli_query($conn, $updateQuery)) {
                throw new Exception("Error updating job type: " . mysqli_error($conn));
            }
        } else {
            // Insert new preference
            $insertQuery = "INSERT INTO user_preferences (seeker_id, job_type) VALUES ($seekerId, '" . mysqli_real_escape_string($conn, $jobType) . "')";
            if (!mysqli_query($conn, $insertQuery)) {
                throw new Exception("Error inserting job type: " . mysqli_error($conn));
            }
        }
        
        error_log("Saved job type: $jobType for seeker: $seekerId");
    }
    
    // 4. SAVE WORKPLACE ACCOMMODATIONS
    // -------------------------------
    $disabilityType = isset($_POST['disability_type']) ? $_POST['disability_type'] : null;
    $accommodationList = isset($_POST['accommodation_list']) ? $_POST['accommodation_list'] : '[]';
    $noAccommodationsNeeded = isset($_POST['no_accommodations_needed']) ? (int)$_POST['no_accommodations_needed'] : 0;
    
    if ($disabilityType) {
        // Check if accommodation exists
        $checkQuery = "SELECT accommodation_id FROM workplace_accommodations WHERE seeker_id = $seekerId";
        $checkResult = mysqli_query($conn, $checkQuery);
        
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            // Update existing accommodation
            $updateQuery = "UPDATE workplace_accommodations 
                           SET disability_type = '" . mysqli_real_escape_string($conn, $disabilityType) . "',
                               accommodation_list = '" . mysqli_real_escape_string($conn, $accommodationList) . "',
                               no_accommodations_needed = $noAccommodationsNeeded
                           WHERE seeker_id = $seekerId";
            if (!mysqli_query($conn, $updateQuery)) {
                throw new Exception("Error updating accommodations: " . mysqli_error($conn));
            }
        } else {
            // Insert new accommodation
            $insertQuery = "INSERT INTO workplace_accommodations 
                           (seeker_id, disability_type, accommodation_list, no_accommodations_needed)
                           VALUES ($seekerId, '" . mysqli_real_escape_string($conn, $disabilityType) . "',
                                  '" . mysqli_real_escape_string($conn, $accommodationList) . "',
                                  $noAccommodationsNeeded)";
            if (!mysqli_query($conn, $insertQuery)) {
                throw new Exception("Error inserting accommodations: " . mysqli_error($conn));
            }
        }
        
        error_log("Saved accommodations for disability type: $disabilityType for seeker: $seekerId");
    }
    
    // 5. Mark setup as complete
    $_SESSION['setup_complete'] = true;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'All preferences saved successfully',
        'redirect' => 'dashboard.php'
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error in save_all_preferences.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>