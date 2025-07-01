<?php
// Place this file in backend/candidate/ directory
// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to prevent caching and ensure proper content type
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Start the output buffer to catch any unexpected output
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create a log function that outputs to a file we can check
function debug_log($message) {
    file_put_contents(
        '../../logs/skills_debug.log', 
        date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 
        FILE_APPEND
    );
}

debug_log("debug_save_skills.php called");
debug_log("POST data: " . print_r($_POST, true));
debug_log("SESSION data: " . print_r($_SESSION, true));

try {
    // Step 1: Check for seeker_id
    $seekerId = isset($_POST['seeker_id']) ? $_POST['seeker_id'] : null;
    debug_log("Seeker ID from POST: " . ($seekerId ? $seekerId : "Not set"));

    if (!$seekerId) {
        throw new Exception("No seeker ID provided");
    }

    // Step 2: Check for skills data
    $skills = isset($_POST['skills']) ? $_POST['skills'] : null;
    debug_log("Skills data received: " . ($skills ? $skills : "None"));

    if (!$skills) {
        throw new Exception("No skills data provided");
    }

    // Step 3: Decode JSON
    $decodedSkills = json_decode($skills, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }

    debug_log("Decoded skills: " . print_r($decodedSkills, true));

    // Step 4: Connect to database directly
    $db_host = "localhost";
    $db_user = "root";  // Update these with your actual credentials
    $db_pass = "";      // Update these with your actual credentials
    $db_name = "jobportal_db";

    debug_log("Connecting to database...");
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    debug_log("Database connected successfully");

    // Step 5: Test a simple query first
    $testResult = mysqli_query($conn, "SELECT 1");
    if (!$testResult) {
        throw new Exception("Test query failed: " . mysqli_error($conn));
    }

    debug_log("Test query successful");

    // Step 6: Delete existing skills
    $deleteQuery = "DELETE FROM seeker_skills WHERE seeker_id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    
    if (!$stmt) {
        throw new Exception("Delete prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $seekerId);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Delete execute failed: " . mysqli_stmt_error($stmt));
    }

    debug_log("Deleted existing skills for seeker_id: " . $seekerId);
    mysqli_stmt_close($stmt);

    // Step 7: For each skill, find ID and insert - using direct ID values for simplicity
    $insertCount = 0;
    foreach ($decodedSkills as $skillName) {
        // Get skill ID
        $findQuery = "SELECT skill_id FROM skills WHERE skill_name = ?";
        $findStmt = mysqli_prepare($conn, $findQuery);
        
        if (!$findStmt) {
            debug_log("Warning: Find prepare failed for skill '" . $skillName . "': " . mysqli_error($conn));
            continue;
        }

        mysqli_stmt_bind_param($findStmt, "s", $skillName);
        
        if (!mysqli_stmt_execute($findStmt)) {
            debug_log("Warning: Find execute failed for skill '" . $skillName . "': " . mysqli_stmt_error($findStmt));
            mysqli_stmt_close($findStmt);
            continue;
        }

        $result = mysqli_stmt_get_result($findStmt);
        
        if (!$row = mysqli_fetch_assoc($result)) {
            debug_log("Warning: Skill not found: " . $skillName);
            mysqli_stmt_close($findStmt);
            continue;
        }

        $skillId = $row['skill_id'];
        mysqli_stmt_close($findStmt);

        // Insert the skill
        $insertQuery = "INSERT INTO seeker_skills (seeker_id, skill_id) VALUES (?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertQuery);
        
        if (!$insertStmt) {
            debug_log("Warning: Insert prepare failed for skill_id " . $skillId . ": " . mysqli_error($conn));
            continue;
        }

        mysqli_stmt_bind_param($insertStmt, "ii", $seekerId, $skillId);
        
        if (!mysqli_stmt_execute($insertStmt)) {
            debug_log("Warning: Insert execute failed for skill_id " . $skillId . ": " . mysqli_stmt_error($insertStmt));
            mysqli_stmt_close($insertStmt);
            continue;
        }

        debug_log("Inserted skill: " . $skillName . " (ID: " . $skillId . ")");
        mysqli_stmt_close($insertStmt);
        $insertCount++;
    }

    // Step 8: Summary and cleanup
    debug_log("Inserted " . $insertCount . " skills out of " . count($decodedSkills));
    mysqli_close($conn);
    
    // Clear output buffer of any unexpected output
    ob_end_clean();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Successfully saved ' . $insertCount . ' skills'
    ]);

} catch (Exception $e) {
    debug_log("ERROR: " . $e->getMessage());
    
    // Clear output buffer of any unexpected output
    ob_end_clean();
    
    // Error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>