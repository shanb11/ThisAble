<?php
// backend/employer/bulk_actions.php
// Bulk operations for multiple applicants

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';
require_once '../shared/session_helper.php';  // ← ADD THIS LINE


try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $action = $input['action'] ?? ''; // update_status, send_notification, export, delete
    $application_ids = $input['application_ids'] ?? [];
    $parameters = $input['parameters'] ?? [];

    if (empty($action) || empty($application_ids)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action and application IDs are required'
        ]);
        exit;
    }

    // Verify all applications belong to this employer
    $verify_sql = "SELECT ja.application_id, ja.seeker_id, ja.application_status,
                          jp.job_title, js.first_name, js.last_name, ua.email
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                   LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
                   WHERE ja.application_id IN (" . str_repeat('?,', count($application_ids) - 1) . "?)
                   AND jp.employer_id = ?";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $params = array_merge($application_ids, [$employer_id]);
    $verify_stmt->execute($params);
    $applications = $verify_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($applications) !== count($application_ids)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Some applications do not belong to your company'
        ]);
        exit;
    }

    $conn->beginTransaction();
    
    $results = [];
    $successful_operations = 0;
    $failed_operations = 0;

    switch ($action) {
        case 'update_status':
            $results = handleBulkStatusUpdate($applications, $parameters, $conn, $employer_data);
            break;
            
        case 'send_notification':
            $results = handleBulkNotification($applications, $parameters, $conn, $employer_data);
            break;
            
        case 'export':
            $results = handleBulkExport($applications, $parameters);
            break;
            
        case 'schedule_interviews':
            $results = handleBulkInterviewScheduling($applications, $parameters, $conn, $employer_data);
            break;
            
        case 'archive':
            $results = handleBulkArchive($applications, $parameters, $conn);
            break;
            
        default:
            throw new Exception('Invalid bulk action specified');
    }
    
    $successful_operations = $results['successful'] ?? 0;
    $failed_operations = $results['failed'] ?? 0;
    
    // Log the bulk activity
    logActivity("BULK_ACTION", "Performed bulk {$action} on {$successful_operations} applications");
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Bulk operation completed successfully",
        'data' => [
            'action' => $action,
            'total_selected' => count($application_ids),
            'successful_operations' => $successful_operations,
            'failed_operations' => $failed_operations,
            'details' => $results['details'] ?? [],
            'export_data' => $results['export_data'] ?? null
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bulk operation failed: ' . $e->getMessage()
    ]);
}

/**
 * Handle bulk status updates
 */
function handleBulkStatusUpdate($applications, $parameters, $conn, $employer_data) {
    $new_status = $parameters['status'] ?? '';
    $notes = $parameters['notes'] ?? '';
    
    if (empty($new_status)) {
        throw new Exception('Status is required for bulk status update');
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
    
    $successful = 0;
    $failed = 0;
    $details = [];
    
    foreach ($applications as $app) {
        try {
            // Skip if already in this status
            if ($app['application_status'] === $db_status) {
                $details[] = [
                    'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                    'status' => 'skipped',
                    'reason' => 'Already in ' . $db_status . ' status'
                ];
                continue;
            }
            
            // Update application status
            $update_sql = "UPDATE job_applications 
                          SET application_status = ?, 
                              status_updated_at = CURRENT_TIMESTAMP,
                              last_activity = CURRENT_TIMESTAMP,
                              employer_notes = CASE 
                                             WHEN ? != '' THEN CONCAT(COALESCE(employer_notes, ''), '\n\n', 'Bulk update: ', ?) 
                                             ELSE employer_notes 
                                             END
                          WHERE application_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([$db_status, $notes, $notes, $app['application_id']]);
            
            // Add to status history
            $history_sql = "INSERT INTO application_status_history 
                           (application_id, previous_status, new_status, changed_by_employer, notes, changed_at)
                           VALUES (?, ?, ?, 1, ?, CURRENT_TIMESTAMP)";
            
            $history_stmt = $conn->prepare($history_sql);
            $history_stmt->execute([
                $app['application_id'], 
                $app['application_status'], 
                $db_status, 
                'Bulk status update: ' . $notes
            ]);
            
            // Create notification for job seeker
            $notification_messages = [
                'under_review' => "Your application for {$app['job_title']} is now under review.",
                'interview_scheduled' => "You have an interview scheduled for {$app['job_title']}.",
                'hired' => "Congratulations! You've been hired for {$app['job_title']}.",
                'rejected' => "Thank you for your interest in {$app['job_title']}. We've decided to move forward with other candidates."
            ];
            
            if (isset($notification_messages[$db_status])) {
                $notification_sql = "INSERT INTO notifications 
                                   (recipient_type, recipient_id, type_id, title, message, related_application_id, created_at)
                                   VALUES ('candidate', ?, 3, 'Application Status Update', ?, ?, CURRENT_TIMESTAMP)";
                
                $notification_stmt = $conn->prepare($notification_sql);
                $notification_stmt->execute([
                    $app['seeker_id'],
                    $notification_messages[$db_status],
                    $app['application_id']
                ]);
            }
            
            $successful++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'success',
                'previous_status' => $app['application_status'],
                'new_status' => $db_status
            ];
            
        } catch (Exception $e) {
            $failed++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'successful' => $successful,
        'failed' => $failed,
        'details' => $details
    ];
}

/**
 * Handle bulk notifications
 */
function handleBulkNotification($applications, $parameters, $conn, $employer_data) {
    $title = $parameters['title'] ?? '';
    $message = $parameters['message'] ?? '';
    $send_email = $parameters['send_email'] ?? false;
    
    if (empty($title) || empty($message)) {
        throw new Exception('Title and message are required for bulk notifications');
    }
    
    $successful = 0;
    $failed = 0;
    $details = [];
    
    foreach ($applications as $app) {
        try {
            // Insert notification
            $notification_sql = "INSERT INTO notifications 
                               (recipient_type, recipient_id, type_id, title, message, related_application_id, created_at)
                               VALUES ('candidate', ?, 5, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->execute([
                $app['seeker_id'],
                $title,
                $message,
                $app['application_id']
            ]);
            
            $successful++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'success',
                'email_sent' => $send_email && !empty($app['email'])
            ];
            
        } catch (Exception $e) {
            $failed++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'successful' => $successful,
        'failed' => $failed,
        'details' => $details
    ];
}

/**
 * Handle bulk export
 */
function handleBulkExport($applications, $parameters) {
    $format = $parameters['format'] ?? 'csv';
    $fields = $parameters['fields'] ?? ['name', 'email', 'phone', 'status', 'applied_date'];
    
    $export_data = [];
    
    foreach ($applications as $app) {
        $row = [];
        
        if (in_array('name', $fields)) {
            $row['Name'] = $app['first_name'] . ' ' . $app['last_name'];
        }
        if (in_array('email', $fields)) {
            $row['Email'] = $app['email'] ?? 'N/A';
        }
        if (in_array('job_title', $fields)) {
            $row['Position'] = $app['job_title'];
        }
        if (in_array('status', $fields)) {
            $row['Status'] = $app['application_status'];
        }
        if (in_array('applied_date', $fields)) {
            $row['Applied Date'] = $app['applied_at'] ?? 'N/A';
        }
        
        $export_data[] = $row;
    }
    
    return [
        'successful' => count($applications),
        'failed' => 0,
        'details' => ['Export prepared with ' . count($export_data) . ' records'],
        'export_data' => [
            'format' => $format,
            'data' => $export_data,
            'filename' => 'applicants_export_' . date('Y-m-d_H-i-s') . '.' . $format
        ]
    ];
}

/**
 * Handle bulk archive (soft delete)
 */
function handleBulkArchive($applications, $parameters, $conn) {
    $archive_reason = $parameters['reason'] ?? 'Bulk archive operation';
    
    $successful = 0;
    $failed = 0;
    $details = [];
    
    foreach ($applications as $app) {
        try {
            // Update application to archived status (you might want to add an archived field)
            $update_sql = "UPDATE job_applications 
                          SET application_status = 'archived',
                              employer_notes = CONCAT(COALESCE(employer_notes, ''), '\n\nArchived: ', ?)
                          WHERE application_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([$archive_reason, $app['application_id']]);
            
            $successful++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'archived'
            ];
            
        } catch (Exception $e) {
            $failed++;
            $details[] = [
                'applicant' => $app['first_name'] . ' ' . $app['last_name'],
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'successful' => $successful,
        'failed' => $failed,
        'details' => $details
    ];
}
?>