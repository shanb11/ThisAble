<?php
/**
 * Toggle Job Status API + NOTIFICATIONS
 * Changes job status between active, closed, draft, and paused
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow both PUT and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'Only PUT/POST requests are allowed'
    ]);
    exit();
}

session_start();

// Include required files
require_once('../db.php');
require_once('../shared/session_helper.php');
require_once('notification_system.php'); // ADD NOTIFICATION SYSTEM

try {
    // Check if employer is logged in
    if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $employer_id = $_SESSION['employer_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Get job ID and new status
    $job_id = isset($input['job_id']) ? intval($input['job_id']) : 0;
    $new_status = isset($input['new_status']) ? trim($input['new_status']) : '';
    
    if (!$job_id) {
        throw new Exception('Job ID is required.');
    }
    
    if (!$new_status) {
        throw new Exception('New status is required.');
    }

    // Validate new status
    $valid_statuses = ['draft', 'active', 'paused', 'closed'];
    if (!in_array($new_status, $valid_statuses)) {
        throw new Exception('Invalid job status. Valid statuses: ' . implode(', ', $valid_statuses));
    }

    // Get current job data and verify ownership
    $job_sql = "
        SELECT job_id, job_title, job_status, posted_at, applications_count 
        FROM job_posts 
        WHERE job_id = :job_id AND employer_id = :employer_id
    ";
    $job_stmt = $conn->prepare($job_sql);
    $job_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    $job = $job_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        throw new Exception('Job not found or access denied.');
    }

    $current_status = $job['job_status'];
    $job_title = $job['job_title'];
    
    // Check if status is actually changing
    if ($current_status === $new_status) {
        throw new Exception("Job is already in '$new_status' status.");
    }

    // Business logic validation for status transitions
    $status_transitions = validateStatusTransition($current_status, $new_status, $job);
    
    if (!$status_transitions['allowed']) {
        throw new Exception($status_transitions['message']);
    }

    // Start transaction
    $conn->beginTransaction();

    // Prepare update data
    $update_data = [
        'job_status' => $new_status,
        'job_id' => $job_id,
        'employer_id' => $employer_id
    ];

    // Handle posted_at date based on status change
    $posted_at_update = '';
    if ($new_status === 'active' && ($current_status === 'draft' || $job['posted_at'] === null)) {
        // When publishing from draft or if never published, set posted_at
        $posted_at_update = ', posted_at = NOW()';
        $update_data['set_posted_at'] = true;
    } elseif ($new_status === 'draft' && $current_status !== 'draft') {
        // When moving to draft from active status, keep posted_at as is
        // (don't change posted_at when moving to draft)
    }

    // Update job status
    $update_sql = "
        UPDATE job_posts 
        SET job_status = :job_status, updated_at = NOW() $posted_at_update
        WHERE job_id = :job_id AND employer_id = :employer_id
    ";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->execute($update_data);

    // Check if update was successful
    if ($update_stmt->rowCount() === 0) {
        throw new Exception('Failed to update job status.');
    }

    // 🔥 ADD NOTIFICATIONS FOR JOB STATUS CHANGES
    $notification_created = false;
    try {
        if ($new_status === 'active' && ($current_status === 'draft' || $current_status === 'paused')) {
            // Job published/reactivated
            notify_job_posted($employer_id, $job_title, $job_id);
            $notification_created = true;
        } elseif ($new_status === 'closed') {
            // Job closed - could add specific notification for this
            notify_system_update($employer_id, 'Job Closed', "Your job posting for \"$job_title\" has been closed to new applications.");
            $notification_created = true;
        } elseif ($new_status === 'paused') {
            // Job paused
            notify_system_update($employer_id, 'Job Paused', "Your job posting for \"$job_title\" has been paused temporarily.");
            $notification_created = true;
        }
        // For 'draft' status, we typically don't need notifications
    } catch (Exception $e) {
        // Don't fail the main operation if notification fails
        error_log("Notification failed in toggle_job_status: " . $e->getMessage());
    }

    // Get updated job data
    $updated_job_sql = "
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.department,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.application_deadline,
            jp.job_description,
            jp.job_requirements,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.job_status,
            jp.posted_at,
            jp.created_at,
            jp.updated_at,
            jp.applications_count,
            jp.views_count,
            
            -- Accommodation data
            COALESCE(ja.wheelchair_accessible, 0) as wheelchair_accessible,
            COALESCE(ja.assistive_technology, 0) as assistive_technology,
            COALESCE(ja.remote_work_option, 0) as remote_work_option,
            COALESCE(ja.screen_reader_compatible, 0) as screen_reader_compatible,
            COALESCE(ja.sign_language_interpreter, 0) as sign_language_interpreter,
            COALESCE(ja.modified_workspace, 0) as modified_workspace,
            COALESCE(ja.transportation_support, 0) as transportation_support,
            COALESCE(ja.additional_accommodations, '') as additional_accommodations
            
        FROM job_posts jp
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_id = :job_id AND jp.employer_id = :employer_id
    ";
    
    $updated_job_stmt = $conn->prepare($updated_job_sql);
    $updated_job_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    $updated_job = $updated_job_stmt->fetch(PDO::FETCH_ASSOC);

    // Commit transaction
    $conn->commit();

    // Format the updated job data
    $accommodations = [
        'wheelchair_accessible' => (bool)$updated_job['wheelchair_accessible'],
        'assistive_technology' => (bool)$updated_job['assistive_technology'],
        'remote_work_option' => (bool)$updated_job['remote_work_option'],
        'screen_reader_compatible' => (bool)$updated_job['screen_reader_compatible'],
        'sign_language_interpreter' => (bool)$updated_job['sign_language_interpreter'],
        'modified_workspace' => (bool)$updated_job['modified_workspace'],
        'transportation_support' => (bool)$updated_job['transportation_support'],
        'additional_accommodations' => $updated_job['additional_accommodations'] ?? ''
    ];

    $formatted_job = [
        'job_id' => $updated_job['job_id'],
        'job_title' => $updated_job['job_title'],
        'department' => $updated_job['department'],
        'location' => $updated_job['location'],
        'employment_type' => $updated_job['employment_type'],
        'salary_range' => $updated_job['salary_range'],
        'application_deadline' => $updated_job['application_deadline'] ? date('Y-m-d', strtotime($updated_job['application_deadline'])) : null,
        'job_description' => $updated_job['job_description'],
        'job_requirements' => $updated_job['job_requirements'],
        'remote_work_available' => (bool)$updated_job['remote_work_available'],
        'flexible_schedule' => (bool)$updated_job['flexible_schedule'],
        'job_status' => $updated_job['job_status'],
        'posted_at' => $updated_job['posted_at'] ? date('Y-m-d', strtotime($updated_job['posted_at'])) : date('Y-m-d', strtotime($updated_job['created_at'])),
        'applications_count' => (int)$updated_job['applications_count'],
        'views_count' => (int)$updated_job['views_count'],
        'accommodations' => $accommodations,
        'company_name' => $_SESSION['company_name'] ?? 'Company'
    ];

    // Log the status change
    error_log("Job status changed: ID $job_id, '{$job['job_title']}', $current_status -> $new_status, Employer: $employer_id");

    // Determine user-friendly action message
    $action_messages = [
        'active' => 'Job published and is now active',
        'closed' => 'Job closed to new applications', 
        'draft' => 'Job moved to draft status',
        'paused' => 'Job paused temporarily'
    ];

    // Return success response
    $response = [
        'success' => true,
        'data' => [
            'job' => $formatted_job,
            'status_change' => [
                'from' => $current_status,
                'to' => $new_status,
                'posted_at_updated' => isset($update_data['set_posted_at'])
            ]
        ],
        'message' => $action_messages[$new_status] ?? "Job status changed to $new_status",
        'notification_created' => $notification_created,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Database error in toggle_job_status.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Unable to update job status. Please try again.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Error in toggle_job_status.php: " . $e->getMessage());
    
    $status_code = ($e->getMessage() === 'Unauthorized access. Please log in.') ? 401 : 400;
    
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Unexpected error in toggle_job_status.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred',
        'message' => 'Please try again later or contact support.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

/**
 * Validate status transition and return business logic result
 */
function validateStatusTransition($current_status, $new_status, $job) {
    $job_title = $job['job_title'];
    $applications_count = (int)$job['applications_count'];
    
    // Define allowed transitions
    $allowed_transitions = [
        'draft' => ['active', 'closed'],  // Draft can go to active or closed
        'active' => ['closed', 'paused'], // Active can go to closed or paused
        'closed' => ['active', 'draft'],  // Closed can go to active or draft
        'paused' => ['active', 'closed']  // Paused can go to active or closed
    ];
    
    // Check if transition is allowed
    if (!isset($allowed_transitions[$current_status]) || 
        !in_array($new_status, $allowed_transitions[$current_status])) {
        return [
            'allowed' => false,
            'message' => "Cannot change status from '$current_status' to '$new_status'. Invalid transition."
        ];
    }
    
    // Special business rules
    
    // Rule 1: Cannot move job with applications back to draft
    if ($new_status === 'draft' && $applications_count > 0) {
        return [
            'allowed' => false,
            'message' => "Cannot move job to draft status because it has $applications_count application" . 
                        ($applications_count > 1 ? 's' : '') . ". Jobs with applications can only be closed or reactivated."
        ];
    }
    
    // Rule 2: Warn about closing jobs with active applications (allow but warn)
    if ($new_status === 'closed' && $applications_count > 0) {
        // This is allowed but we could log it or return a warning
        error_log("Warning: Closing job '$job_title' (ID: {$job['job_id']}) with $applications_count applications");
    }
    
    // All validation passed
    return [
        'allowed' => true,
        'message' => "Status change from '$current_status' to '$new_status' is allowed."
    ];
}
?>