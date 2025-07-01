<?php
// backend/candidate/application_actions.php
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
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'withdraw_application':
            handleWithdrawApplication($conn, $seeker_id);
            break;
            
        case 'confirm_interview':
            handleConfirmInterview($conn, $seeker_id);
            break;
            
        case 'reschedule_interview':
            handleRescheduleInterview($conn, $seeker_id);
            break;
            
        case 'download_resume':
            handleDownloadResume($conn, $seeker_id);
            break;
            
        case 'add_note':
            handleAddNote($conn, $seeker_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in application_actions.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Operation failed']);
}

// Handle withdraw application
function handleWithdrawApplication($conn, $seeker_id) {
    $application_id = $_POST['application_id'] ?? 0;
    $reason = $_POST['reason'] ?? 'Candidate withdrew application';
    
    if (!$application_id) {
        echo json_encode(['success' => false, 'error' => 'Application ID required']);
        return;
    }
    
    // Verify ownership and check if withdrawal is allowed
    $checkQuery = "
        SELECT application_status 
        FROM job_applications 
        WHERE application_id = :application_id 
        AND seeker_id = :seeker_id
    ";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $application = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode(['success' => false, 'error' => 'Application not found']);
        return;
    }
    
    // Check if withdrawal is allowed
    $allowedStatuses = ['submitted', 'under_review', 'shortlisted'];
    if (!in_array($application['application_status'], $allowedStatuses)) {
        echo json_encode(['success' => false, 'error' => 'Application cannot be withdrawn at this stage']);
        return;
    }
    
    $conn->beginTransaction();
    
    try {
        // Update application status
        $updateQuery = "
            UPDATE job_applications 
            SET application_status = 'withdrawn', 
                status_updated_at = CURRENT_TIMESTAMP,
                candidate_notes = CONCAT(COALESCE(candidate_notes, ''), '\n\nWithdrawal reason: ', :reason)
            WHERE application_id = :application_id
        ";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $updateStmt->bindValue(':reason', $reason);
        $updateStmt->execute();
        
        // Add to status history
        $historyQuery = "
            INSERT INTO application_status_history 
            (application_id, previous_status, new_status, changed_by_employer, notes) 
            VALUES (:application_id, :previous_status, 'withdrawn', 0, :notes)
        ";
        
        $historyStmt = $conn->prepare($historyQuery);
        $historyStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $historyStmt->bindValue(':previous_status', $application['application_status']);
        $historyStmt->bindValue(':notes', "Application withdrawn by candidate. Reason: $reason");
        $historyStmt->execute();
        
        // Cancel any scheduled interviews
        $cancelInterviewQuery = "
            UPDATE interviews 
            SET interview_status = 'cancelled',
                updated_at = CURRENT_TIMESTAMP
            WHERE application_id = :application_id 
            AND interview_status IN ('scheduled', 'confirmed')
        ";
        
        $cancelStmt = $conn->prepare($cancelInterviewQuery);
        $cancelStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $cancelStmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Application withdrawn successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

// Handle confirm interview
function handleConfirmInterview($conn, $seeker_id) {
    $interview_id = $_POST['interview_id'] ?? 0;
    $notes = $_POST['notes'] ?? '';
    
    if (!$interview_id) {
        echo json_encode(['success' => false, 'error' => 'Interview ID required']);
        return;
    }
    
    // Verify ownership
    $checkQuery = "
        SELECT i.interview_status 
        FROM interviews i
        INNER JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE i.interview_id = :interview_id 
        AND ja.seeker_id = :seeker_id
    ";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':interview_id', $interview_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $interview = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$interview) {
        echo json_encode(['success' => false, 'error' => 'Interview not found']);
        return;
    }
    
    if ($interview['interview_status'] !== 'scheduled') {
        echo json_encode(['success' => false, 'error' => 'Interview cannot be confirmed']);
        return;
    }
    
    // Update interview status
    $updateQuery = "
        UPDATE interviews 
        SET interview_status = 'confirmed',
            candidate_notes = :notes,
            updated_at = CURRENT_TIMESTAMP
        WHERE interview_id = :interview_id
    ";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindValue(':interview_id', $interview_id, PDO::PARAM_INT);
    $updateStmt->bindValue(':notes', $notes);
    $updateStmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Interview confirmed successfully'
    ]);
}

// Handle reschedule interview request
function handleRescheduleInterview($conn, $seeker_id) {
    $interview_id = $_POST['interview_id'] ?? 0;
    $reason = $_POST['reason'] ?? '';
    $preferred_dates = $_POST['preferred_dates'] ?? '';
    
    if (!$interview_id) {
        echo json_encode(['success' => false, 'error' => 'Interview ID required']);
        return;
    }
    
    // Verify ownership
    $checkQuery = "
        SELECT i.interview_status 
        FROM interviews i
        INNER JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE i.interview_id = :interview_id 
        AND ja.seeker_id = :seeker_id
    ";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':interview_id', $interview_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $interview = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$interview) {
        echo json_encode(['success' => false, 'error' => 'Interview not found']);
        return;
    }
    
    // Add reschedule request note
    $updateQuery = "
        UPDATE interviews 
        SET candidate_notes = CONCAT(
            COALESCE(candidate_notes, ''), 
            '\n\nReschedule requested: ', :reason,
            '\nPreferred dates: ', :preferred_dates
        ),
        updated_at = CURRENT_TIMESTAMP
        WHERE interview_id = :interview_id
    ";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindValue(':interview_id', $interview_id, PDO::PARAM_INT);
    $updateStmt->bindValue(':reason', $reason);
    $updateStmt->bindValue(':preferred_dates', $preferred_dates);
    $updateStmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Reschedule request submitted successfully'
    ]);
}

// Handle download resume
function handleDownloadResume($conn, $seeker_id) {
    $application_id = $_POST['application_id'] ?? 0;
    
    if (!$application_id) {
        echo json_encode(['success' => false, 'error' => 'Application ID required']);
        return;
    }
    
    // Get resume information
    $resumeQuery = "
        SELECT r.file_path, r.file_name, r.file_type
        FROM job_applications ja
        INNER JOIN resumes r ON ja.resume_id = r.resume_id
        WHERE ja.application_id = :application_id 
        AND ja.seeker_id = :seeker_id
    ";
    
    $resumeStmt = $conn->prepare($resumeQuery);
    $resumeStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $resumeStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $resumeStmt->execute();
    $resume = $resumeStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        echo json_encode(['success' => false, 'error' => 'Resume not found']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'resume' => [
            'file_path' => $resume['file_path'],
            'file_name' => $resume['file_name'],
            'file_type' => $resume['file_type']
        ]
    ]);
}

// Handle add note to application
function handleAddNote($conn, $seeker_id) {
    $application_id = $_POST['application_id'] ?? 0;
    $note = $_POST['note'] ?? '';
    
    if (!$application_id || !$note) {
        echo json_encode(['success' => false, 'error' => 'Application ID and note required']);
        return;
    }
    
    // Verify ownership
    $checkQuery = "
        SELECT application_id 
        FROM job_applications 
        WHERE application_id = :application_id 
        AND seeker_id = :seeker_id
    ";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $checkStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Application not found']);
        return;
    }
    
    // Add note
    $updateQuery = "
        UPDATE job_applications 
        SET candidate_notes = CONCAT(
            COALESCE(candidate_notes, ''), 
            '\n\n[', NOW(), '] ', :note
        )
        WHERE application_id = :application_id
    ";
    
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $updateStmt->bindValue(':note', $note);
    $updateStmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Note added successfully'
    ]);
}
?>