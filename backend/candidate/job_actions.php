<?php
// backend/candidate/job_actions.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$seeker_id = get_seeker_id();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'save_job':
            handleSaveJob($conn, $seeker_id);
            break;
            
        case 'unsave_job':
            handleUnsaveJob($conn, $seeker_id);
            break;
            
        case 'get_saved_jobs':
            handleGetSavedJobs($conn, $seeker_id);
            break;
            
        case 'track_view':
            handleTrackView($conn, $seeker_id);
            break;
            
        case 'apply_job':
            handleJobApplication($conn, $seeker_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in job_actions.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Operation failed']);
}

// Handle save job
function handleSaveJob($conn, $seeker_id) {
    $job_id = $_POST['job_id'] ?? 0;
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'error' => 'Job ID required']);
        return;
    }
    
    // Check if job exists and is active
    $checkQuery = "SELECT job_id FROM job_posts WHERE job_id = :job_id AND job_status = 'active'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        return;
    }
    
    // Check if already saved
    $existQuery = "SELECT saved_id FROM saved_jobs WHERE seeker_id = :seeker_id AND job_id = :job_id";
    $existStmt = $conn->prepare($existQuery);
    $existStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $existStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $existStmt->execute();
    
    if ($existStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Job already saved']);
        return;
    }
    
    // Save the job
    $saveQuery = "INSERT INTO saved_jobs (seeker_id, job_id) VALUES (:seeker_id, :job_id)";
    $saveStmt = $conn->prepare($saveQuery);
    $saveStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $saveStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $saveStmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Job saved successfully']);
}

// Handle unsave job
function handleUnsaveJob($conn, $seeker_id) {
    $job_id = $_POST['job_id'] ?? 0;
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'error' => 'Job ID required']);
        return;
    }
    
    $deleteQuery = "DELETE FROM saved_jobs WHERE seeker_id = :seeker_id AND job_id = :job_id";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $deleteStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $deleteStmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Job removed from saved']);
}

// Get saved jobs for current user
function handleGetSavedJobs($conn, $seeker_id) {
    $query = "SELECT job_id FROM saved_jobs WHERE seeker_id = :seeker_id";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $savedJobs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'saved_jobs' => $savedJobs]);
}

// Track job view
function handleTrackView($conn, $seeker_id) {
    $job_id = $_POST['job_id'] ?? 0;
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'error' => 'Job ID required']);
        return;
    }
    
    // Insert job view record
    $viewQuery = "INSERT INTO job_views (job_id, seeker_id, ip_address, user_agent) 
                  VALUES (:job_id, :seeker_id, :ip_address, :user_agent)";
    $viewStmt = $conn->prepare($viewQuery);
    $viewStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $viewStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $viewStmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR']);
    $viewStmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $viewStmt->execute();
    
    // Update view count in job_posts
    $updateQuery = "UPDATE job_posts SET views_count = views_count + 1 WHERE job_id = :job_id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $updateStmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'View tracked']);
}

// Handle job application
function handleJobApplication($conn, $seeker_id) {
    $job_id = $_POST['job_id'] ?? 0;
    $cover_letter = $_POST['cover_letter'] ?? '';
    $accessibility_needs = $_POST['accessibility_needs'] ?? '';
    $resume_id = $_POST['resume_id'] ?? null;
    
    // Additional materials
    $include_cover_letter = isset($_POST['include_cover_letter']) ? 1 : 0;
    $include_portfolio = isset($_POST['include_portfolio']) ? 1 : 0;
    $include_references = isset($_POST['include_references']) ? 1 : 0;
    
    if (!$job_id) {
        echo json_encode(['success' => false, 'error' => 'Job ID required']);
        return;
    }
    
    // Check if job exists and is active
    $checkQuery = "SELECT job_id FROM job_posts WHERE job_id = :job_id AND job_status = 'active'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Job not found or not active']);
        return;
    }
    
    // Check if already applied
    $existQuery = "SELECT application_id FROM job_applications WHERE seeker_id = :seeker_id AND job_id = :job_id";
    $existStmt = $conn->prepare($existQuery);
    $existStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $existStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
    $existStmt->execute();
    
    if ($existStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'You have already applied for this job']);
        return;
    }
    
    // Get current resume if not specified
    if (!$resume_id) {
        $resumeQuery = "SELECT resume_id FROM resumes WHERE seeker_id = :seeker_id AND is_current = 1 ORDER BY upload_date DESC LIMIT 1";
        $resumeStmt = $conn->prepare($resumeQuery);
        $resumeStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $resumeStmt->execute();
        $resume = $resumeStmt->fetch();
        $resume_id = $resume ? $resume['resume_id'] : null;
    }
    
    // Create application
    $conn->beginTransaction();
    
    try {
        // Prepare additional materials data
        $additionalMaterials = json_encode([
            'include_cover_letter' => $include_cover_letter,
            'include_portfolio' => $include_portfolio,
            'include_references' => $include_references
        ]);
        
        // Insert application with additional materials
        $appQuery = "INSERT INTO job_applications (
            job_id, seeker_id, resume_id, cover_letter, candidate_notes
        ) VALUES (
            :job_id, :seeker_id, :resume_id, :additional_materials, :accessibility_needs
        )";
        
        $appStmt = $conn->prepare($appQuery);
        $appStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
        $appStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $appStmt->bindValue(':resume_id', $resume_id, PDO::PARAM_INT);
        $appStmt->bindValue(':additional_materials', $additionalMaterials);
        $appStmt->bindValue(':accessibility_needs', $accessibility_needs);
        $appStmt->execute();
        
        $application_id = $conn->lastInsertId();
        
        // Update applications count
        $countQuery = "UPDATE job_posts SET applications_count = applications_count + 1 WHERE job_id = :job_id";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bindValue(':job_id', $job_id, PDO::PARAM_INT);
        $countStmt->execute();
        
        // Create status history record
        $historyQuery = "INSERT INTO application_status_history (application_id, new_status, changed_by_employer, notes) 
                         VALUES (:application_id, 'submitted', 0, 'Application submitted by candidate')";
        $historyStmt = $conn->prepare($historyQuery);
        $historyStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $historyStmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Application submitted successfully',
            'application_id' => $application_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}
?>