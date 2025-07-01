<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 day
        'cookie_path' => '/',
    ]);
}

// FIXED: Clean output buffer to prevent any extra output before JSON
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Include database connection
require_once('../../backend/db.php');

// Set content type to JSON
header('Content-Type: application/json');

// FIXED: Disable error display for production to prevent breaking JSON output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Debug logging function
function debug_log($message) {
    $logDir = '../../logs/setup';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents("$logDir/setup_debug.log", date('Y-m-d H:i:s') . " | " . $message . "\n", FILE_APPEND);
}

debug_log("save_setup_data.php received data: " . print_r($_POST, true));

// Response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST allowed.');
    }

    // Check if seeker_id is provided
    if (!isset($_POST['seeker_id']) || empty($_POST['seeker_id'])) {
        throw new Exception('Seeker ID is required');
    }

    $seekerId = $_POST['seeker_id'];
    debug_log("Processing setup for seeker_id: $seekerId");

    // FIXED: Check if database connection exists
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Start transaction
    $conn->beginTransaction();
    debug_log("Transaction started");
    
    // 1. Save skills if provided
    if (isset($_POST['skills']) && !empty($_POST['skills'])) {
        try {
            $skills = json_decode($_POST['skills'], true);
            debug_log("Processing skills: " . print_r($skills, true));
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid skills JSON: " . json_last_error_msg());
            }
            
            // First, clear existing skills for this user
            $stmt = $conn->prepare("DELETE FROM seeker_skills WHERE seeker_id = ?");
            $stmt->execute([$seekerId]);
            debug_log("Cleared existing skills");
            
            // Then insert new skills
            if (!empty($skills) && is_array($skills)) {
                // Get skill IDs from names
                $getSkill = $conn->prepare("SELECT skill_id FROM skills WHERE skill_name = ?");
                $insertSkill = $conn->prepare("INSERT INTO seeker_skills (seeker_id, skill_id) VALUES (?, ?)");
                
                $skillsAdded = 0;
                foreach ($skills as $skillName) {
                    if (!empty($skillName)) {
                        $getSkill->execute([$skillName]);
                        if ($row = $getSkill->fetch(PDO::FETCH_ASSOC)) {
                            $skillId = $row['skill_id'];
                            $insertSkill->execute([$seekerId, $skillId]);
                            $skillsAdded++;
                        } else {
                            debug_log("Skill not found: $skillName");
                        }
                    }
                }
                debug_log("Added $skillsAdded skills");
            }
        } catch (Exception $e) {
            debug_log("Error saving skills: " . $e->getMessage());
            throw new Exception("Failed to save skills: " . $e->getMessage());
        }
    }
    
    // 2. Save work style and job type if provided
    if ((isset($_POST['work_style']) && !empty($_POST['work_style'])) || 
        (isset($_POST['job_type']) && !empty($_POST['job_type']))) {
        
        try {
            debug_log("Saving preferences - work_style: " . ($_POST['work_style'] ?? 'null') . ", job_type: " . ($_POST['job_type'] ?? 'null'));
            
            // Check if preference record exists
            $checkStmt = $conn->prepare("SELECT preference_id FROM user_preferences WHERE seeker_id = ?");
            $checkStmt->execute([$seekerId]);
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing record
                $updateStmt = $conn->prepare("UPDATE user_preferences SET 
                    work_style = COALESCE(?, work_style), 
                    job_type = COALESCE(?, job_type) 
                    WHERE seeker_id = ?");
                $updateStmt->execute([
                    $_POST['work_style'] ?? null, 
                    $_POST['job_type'] ?? null, 
                    $seekerId
                ]);
                debug_log("Updated existing preferences");
            } else {
                // Insert new record
                $insertStmt = $conn->prepare("INSERT INTO user_preferences 
                    (seeker_id, work_style, job_type) 
                    VALUES (?, ?, ?)");
                $insertStmt->execute([
                    $seekerId, 
                    $_POST['work_style'] ?? null, 
                    $_POST['job_type'] ?? null
                ]);
                debug_log("Inserted new preferences");
            }
        } catch (Exception $e) {
            debug_log("Error saving preferences: " . $e->getMessage());
            throw new Exception("Failed to save preferences: " . $e->getMessage());
        }
    }
    
    // 3. Save workplace accommodations if provided
    if (isset($_POST['disability_type']) && !empty($_POST['disability_type'])) {
        try {
            $disabilityType = $_POST['disability_type'];
            $accommodationList = isset($_POST['accommodation_list']) ? $_POST['accommodation_list'] : '[]';
            $noAccommodationsNeeded = isset($_POST['no_accommodations_needed']) ? (int)$_POST['no_accommodations_needed'] : 0;
            
            debug_log("Saving accommodations - type: $disabilityType, list: $accommodationList, no_needs: $noAccommodationsNeeded");
            
            // Validate disability type
            $validTypes = ['apparent', 'non-apparent'];
            if (!in_array($disabilityType, $validTypes)) {
                throw new Exception("Invalid disability type: $disabilityType");
            }
            
            // Check if accommodation record exists
            $checkStmt = $conn->prepare("SELECT accommodation_id FROM workplace_accommodations WHERE seeker_id = ?");
            $checkStmt->execute([$seekerId]);
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing record
                $updateStmt = $conn->prepare("UPDATE workplace_accommodations SET 
                    disability_type = ?, 
                    accommodation_list = ?, 
                    no_accommodations_needed = ? 
                    WHERE seeker_id = ?");
                $updateStmt->execute([
                    $disabilityType, 
                    $accommodationList, 
                    $noAccommodationsNeeded, 
                    $seekerId
                ]);
                debug_log("Updated existing accommodations");
            } else {
                // Insert new record
                $insertStmt = $conn->prepare("INSERT INTO workplace_accommodations 
                    (seeker_id, disability_type, accommodation_list, no_accommodations_needed) 
                    VALUES (?, ?, ?, ?)");
                $insertStmt->execute([
                    $seekerId, 
                    $disabilityType, 
                    $accommodationList, 
                    $noAccommodationsNeeded
                ]);
                debug_log("Inserted new accommodations");
            }
        } catch (Exception $e) {
            debug_log("Error saving accommodations: " . $e->getMessage());
            throw new Exception("Failed to save accommodations: " . $e->getMessage());
        }
    }
    
    // Mark setup as complete in database
    try {
        $updateSetupStmt = $conn->prepare("UPDATE job_seekers SET setup_complete = TRUE WHERE seeker_id = ?");
        if (!$updateSetupStmt->execute([$seekerId])) {
            throw new Exception("Failed to update setup completion status");
        }
        debug_log("Setup marked as complete for seeker_id: $seekerId");
    } catch (Exception $e) {
        debug_log("Error updating setup complete: " . $e->getMessage());
        throw new Exception("Failed to update setup status: " . $e->getMessage());
    }

    // Commit transaction
    $conn->commit();
    debug_log("Transaction committed successfully");
    
    // Only after successful commit, update the session
    $_SESSION['setup_complete'] = true;
    
    $response['success'] = true;
    $response['message'] = 'Setup data saved successfully';
    debug_log("Setup process completed successfully");
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
        debug_log("Transaction rolled back");
    }
    
    // Ensure session is NOT marked as complete on error
    $_SESSION['setup_complete'] = false;
    
    $response['message'] = 'Error: ' . $e->getMessage();
    debug_log('Save setup data error: ' . $e->getMessage());
}

// FIXED: Clean output buffer and ensure only JSON is returned
ob_clean();
echo json_encode($response);
exit;
?>