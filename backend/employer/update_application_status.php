<?php
// backend/employer/update_application_status.php
// API to update application status with history tracking + NOTIFICATIONS

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';

// TRY to load notification system - if it fails, continue without notifications
$notification_system_available = false;
try {
    require_once '../shared/notification_system.php';
    $notification_system_available = true;
} catch (Exception $e) {
    error_log("Notification system not available: " . $e->getMessage());
}

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    $application_id = $input['application_id'] ?? null;
    $new_status = $input['status'] ?? null;
    $notes = $input['notes'] ?? '';

    if (!$application_id || !$new_status) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Application ID and status are required'
        ]);
        exit;
    }

    // Map frontend status to database status
    $status_map = [
        'new' => 'submitted',
        'reviewed' => 'under_review',
        'interview' => 'interview_scheduled',
        'hired' => 'hired',
        'rejected' => 'rejected'
    ];
    
    $db_status = $status_map[$new_status] ?? $new_status;

    // Validate status values
    $valid_statuses = ['submitted', 'under_review', 'shortlisted', 'interview_scheduled', 'interviewed', 'hired', 'rejected', 'withdrawn'];
    if (!in_array($db_status, $valid_statuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status value'
        ]);
        exit;
    }

    $conn->beginTransaction();
    
    // First, verify the application belongs to this employer
    $verify_sql = "SELECT 
                    ja.application_status as current_status,
                    ja.seeker_id,
                    ja.job_id,
                    jp.job_title,
                    js.first_name,
                    js.last_name
                  FROM job_applications ja
                  JOIN job_posts jp ON ja.job_id = jp.job_id
                  JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                  WHERE ja.application_id = :application_id 
                  AND jp.employer_id = :employer_id";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bindValue(':application_id', $application_id);
    $verify_stmt->bindValue(':employer_id', $employer_id);
    $verify_stmt->execute();
    
    $application_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application_data) {
        throw new Exception('Application not found or access denied');
    }
    
    $current_status = $application_data['current_status'];
    $seeker_id = $application_data['seeker_id'];
    $job_id = $application_data['job_id'];
    $job_title = $application_data['job_title'];
    $applicant_name = $application_data['first_name'] . ' ' . $application_data['last_name'];
    
    // Don't update if status is the same
    if ($current_status === $db_status) {
        echo json_encode([
            'success' => true,
            'message' => 'Status is already ' . $db_status,
            'no_change' => true
        ]);
        exit;
    }
    
    // Update the application status
    $update_sql = "UPDATE job_applications 
                   SET application_status = :new_status,
                       status_updated_at = CURRENT_TIMESTAMP,
                       last_activity = CURRENT_TIMESTAMP,
                       employer_notes = CASE 
                                       WHEN :notes != '' THEN CONCAT(COALESCE(employer_notes, ''), '\n\n', :notes_update, ': ', :notes) 
                                       ELSE employer_notes 
                                       END
                   WHERE application_id = :application_id";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindValue(':new_status', $db_status);
    $update_stmt->bindValue(':notes', $notes);
    $update_stmt->bindValue(':notes_update', 'Status changed to ' . $db_status . ' on ' . date('Y-m-d H:i'));
    $update_stmt->bindValue(':application_id', $application_id);
    $update_stmt->execute();
    
    // Insert into status history
    $history_sql = "INSERT INTO application_status_history 
                    (application_id, previous_status, new_status, changed_by_employer, notes, changed_at)
                    VALUES (:application_id, :previous_status, :new_status, 1, :notes, CURRENT_TIMESTAMP)";
    
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bindValue(':application_id', $application_id);
    $history_stmt->bindValue(':previous_status', $current_status);
    $history_stmt->bindValue(':new_status', $db_status);
    $history_stmt->bindValue(':notes', $notes);
    $history_stmt->execute();
    
    // 🔥 ADD NOTIFICATION - ONLY IF SYSTEM IS AVAILABLE
    $notification_created = false;
    if ($notification_system_available) {
        try {
            $notificationSystem = getNotificationSystem();
            $notification_result = $notificationSystem->notifyApplicationStatusChange(
                $application_id, 
                $db_status, 
                $current_status
            );
            $notification_created = $notification_result ? true : false;
        } catch (Exception $e) {
            // Don't fail the main operation if notification fails
            error_log("Notification failed in update_application_status: " . $e->getMessage());
        }
    }
    
    // Log the activity
    logActivity("STATUS_UPDATE", "Updated application {$application_id} from {$current_status} to {$db_status}");
    
    $conn->commit();
    
    // Return success with updated data
    echo json_encode([
        'success' => true,
        'message' => "Application status updated from {$current_status} to {$db_status}",
        'data' => [
            'application_id' => $application_id,
            'previous_status' => $current_status,
            'new_status' => $db_status,
            'applicant_name' => $applicant_name,
            'job_title' => $job_title,
            'updated_at' => date('Y-m-d H:i:s'),
            'notes' => $notes,
            'notification_created' => $notification_created
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status: ' . $e->getMessage()
    ]);
}
?>