<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set JSON content type header
header('Content-Type: application/json');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }
    
    try {
        // Fetch user from database
        $stmt = $conn->prepare("SELECT ua.account_id, ua.seeker_id, ua.email, ua.password_hash, js.first_name, js.setup_complete
                               FROM user_accounts ua 
                               JOIN job_seekers js ON ua.seeker_id = js.seeker_id 
                               WHERE ua.email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['account_id'];
                $_SESSION['seeker_id'] = $user['seeker_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['logged_in'] = true;
                
                // Check if the user has completed account setup
                $setupComplete = false;
                
                // Check the setup_complete flag from job_seekers table first
                if (isset($user['setup_complete']) && $user['setup_complete'] == 1) {
                    $setupComplete = true;
                } else {
                    // Additional checks to determine if setup is complete
                    
                    // 1. Check if they have selected skills
                    $skillCheckStmt = $conn->prepare("
                        SELECT COUNT(*) as skill_count 
                        FROM seeker_skills 
                        WHERE seeker_id = :seeker_id
                    ");
                    $skillCheckStmt->bindParam(':seeker_id', $user['seeker_id']);
                    $skillCheckStmt->execute();
                    $skillResult = $skillCheckStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // 2. Check for resume uploads (corrected table name)
                    $resumeCheckStmt = $conn->prepare("
                        SELECT COUNT(*) as resume_count 
                        FROM resumes 
                        WHERE seeker_id = :seeker_id
                    ");
                    
                    $resumeUploaded = false;
                    try {
                        $resumeCheckStmt->bindParam(':seeker_id', $user['seeker_id']);
                        $resumeCheckStmt->execute();
                        $resumeResult = $resumeCheckStmt->fetch(PDO::FETCH_ASSOC);
                        $resumeUploaded = ($resumeResult && $resumeResult['resume_count'] > 0);
                    } catch (PDOException $e) {
                        // Table might not exist, which is fine
                        $resumeUploaded = false;
                    }
                    
                    // If skills are selected, consider setup complete
                    if ($skillResult['skill_count'] > 0) {
                        $setupComplete = true;
                        
                        // Update the database to reflect completion
                        $updateStmt = $conn->prepare("UPDATE job_seekers SET setup_complete = 1 WHERE seeker_id = :seeker_id");
                        $updateStmt->bindParam(':seeker_id', $user['seeker_id']);
                        $updateStmt->execute();
                    }
                }
                
                // Store setup status in session
                $_SESSION['account_setup_complete'] = $setupComplete;
                $_SESSION['setup_complete'] = $setupComplete;
                
                if (!$setupComplete) {
                    $_SESSION['setup_redirect_page'] = 'accountsetup.php';
                }
                
                // Return JSON response only
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Login successful',
                    'seeker_id' => $user['seeker_id'],
                    'user_name' => $user['first_name'],
                    'setup_complete' => $setupComplete,
                    'redirect_page' => $setupComplete ? 'dashboard.php' : 'accountsetup.php'
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        }
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred. Please try again.']);
        // Log the actual error for debugging (don't show to user)
        error_log("Login error: " . $e->getMessage());
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
exit;
?>