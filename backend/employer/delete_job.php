<?php
/**
 * Delete Job API
 * Deletes a job posting and all related data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow both DELETE and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'Only DELETE/POST requests are allowed'
    ]);
    exit();
}

session_start();

// Include required files
require_once('../db.php');
require_once('../shared/session_helper.php');

try {
    // Check if employer is logged in
    if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $employer_id = $_SESSION['employer_id'];
    
    // Get job ID from URL parameter or JSON body
    $job_id = 0;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // For DELETE requests, job_id should be in URL parameter
        $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
    } else {
        // For POST requests, job_id might be in JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $job_id = isset($input['job_id']) ? intval($input['job_id']) : 
                  (isset($_GET['job_id']) ? intval($_GET['job_id']) : 0);
    }
    
    if (!$job_id) {
        throw new Exception('Job ID is required.');
    }

    // Verify job exists and belongs to this employer
    $job_check_sql = "
        SELECT job_id, job_title, job_status, applications_count 
        FROM job_posts 
        WHERE job_id = :job_id AND employer_id = :employer_id
    ";
    $job_check_stmt = $conn->prepare($job_check_sql);
    $job_check_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    $job = $job_check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$job) {
        throw new Exception('Job not found or access denied.');
    }

    // Check if job has applications
    $has_applications = $job['applications_count'] > 0;
    
    // Decide deletion strategy based on applications
    $deletion_type = 'hard'; // Default to hard delete
    
    if ($has_applications) {
        // For jobs with applications, we might want to soft delete
        // For now, we'll still allow hard delete but warn the user
        // You can modify this logic based on your business requirements
        
        // Optional: Implement soft delete by setting job_status to 'deleted'
        // and keeping the record for data integrity
        // $deletion_type = 'soft';
    }

    // Start transaction
    $conn->beginTransaction();

    if ($deletion_type === 'soft') {
        // Soft delete: Mark job as deleted but keep the record
        $soft_delete_sql = "
            UPDATE job_posts 
            SET job_status = 'deleted', updated_at = NOW() 
            WHERE job_id = :job_id AND employer_id = :employer_id
        ";
        $soft_delete_stmt = $conn->prepare($soft_delete_sql);
        $soft_delete_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
        
        $deletion_message = 'Job marked as deleted successfully';
    } else {
        // Hard delete: Remove job and all related data
        
        // Delete in correct order due to foreign key constraints
        
        // 1. Delete job requirements (skills)
        $delete_requirements_sql = "DELETE FROM job_requirements WHERE job_id = :job_id";
        $delete_requirements_stmt = $conn->prepare($delete_requirements_sql);
        $delete_requirements_stmt->execute(['job_id' => $job_id]);
        
        // 2. Delete job accommodations
        $delete_accommodations_sql = "DELETE FROM job_accommodations WHERE job_id = :job_id";
        $delete_accommodations_stmt = $conn->prepare($delete_accommodations_sql);
        $delete_accommodations_stmt->execute(['job_id' => $job_id]);
        
        // 3. Delete job views
        $delete_views_sql = "DELETE FROM job_views WHERE job_id = :job_id";
        $delete_views_stmt = $conn->prepare($delete_views_sql);
        $delete_views_stmt->execute(['job_id' => $job_id]);
        
        // 4. Delete saved jobs
        $delete_saved_sql = "DELETE FROM saved_jobs WHERE job_id = :job_id";
        $delete_saved_stmt = $conn->prepare($delete_saved_sql);
        $delete_saved_stmt->execute(['job_id' => $job_id]);
        
        // 5. Delete job analytics
        $delete_analytics_sql = "DELETE FROM job_analytics WHERE job_id = :job_id";
        $delete_analytics_stmt = $conn->prepare($delete_analytics_sql);
        $delete_analytics_stmt->execute(['job_id' => $job_id]);
        
        // 6. Handle applications and related data
        if ($has_applications) {
            // Get all application IDs for this job
            $app_ids_sql = "SELECT application_id FROM job_applications WHERE job_id = :job_id";
            $app_ids_stmt = $conn->prepare($app_ids_sql);
            $app_ids_stmt->execute(['job_id' => $job_id]);
            $app_ids = $app_ids_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($app_ids)) {
                $app_ids_placeholder = implode(',', array_fill(0, count($app_ids), '?'));
                
                // Delete interview feedback
                $delete_feedback_sql = "
                    DELETE FROM interview_feedback 
                    WHERE interview_id IN (
                        SELECT interview_id FROM interviews WHERE application_id IN ($app_ids_placeholder)
                    )
                ";
                $delete_feedback_stmt = $conn->prepare($delete_feedback_sql);
                $delete_feedback_stmt->execute($app_ids);
                
                // Delete interviews
                $delete_interviews_sql = "DELETE FROM interviews WHERE application_id IN ($app_ids_placeholder)";
                $delete_interviews_stmt = $conn->prepare($delete_interviews_sql);
                $delete_interviews_stmt->execute($app_ids);
                
                // Delete application status history
                $delete_history_sql = "DELETE FROM application_status_history WHERE application_id IN ($app_ids_placeholder)";
                $delete_history_stmt = $conn->prepare($delete_history_sql);
                $delete_history_stmt->execute($app_ids);
                
                // Delete notifications related to these applications
                $delete_notifications_sql = "DELETE FROM notifications WHERE related_application_id IN ($app_ids_placeholder)";
                $delete_notifications_stmt = $conn->prepare($delete_notifications_sql);
                $delete_notifications_stmt->execute($app_ids);
            }
            
            // Delete job applications
            $delete_applications_sql = "DELETE FROM job_applications WHERE job_id = :job_id";
            $delete_applications_stmt = $conn->prepare($delete_applications_sql);
            $delete_applications_stmt->execute(['job_id' => $job_id]);
        }
        
        // 7. Delete notifications related to this job
        $delete_job_notifications_sql = "DELETE FROM notifications WHERE related_job_id = :job_id";
        $delete_job_notifications_stmt = $conn->prepare($delete_job_notifications_sql);
        $delete_job_notifications_stmt->execute(['job_id' => $job_id]);
        
        // 8. Finally, delete the job post itself
        $delete_job_sql = "DELETE FROM job_posts WHERE job_id = :job_id AND employer_id = :employer_id";
        $delete_job_stmt = $conn->prepare($delete_job_sql);
        $delete_job_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
        
        // Check if job was actually deleted
        if ($delete_job_stmt->rowCount() === 0) {
            throw new Exception('Job could not be deleted or was already deleted.');
        }
        
        $deletion_message = 'Job deleted successfully';
    }

    // Commit transaction
    $conn->commit();

    // Log the deletion
    error_log("Job deleted: ID $job_id, Title: '{$job['job_title']}', Employer: $employer_id, Type: $deletion_type");

    // Return success response
    $response = [
        'success' => true,
        'data' => [
            'job_id' => $job_id,
            'job_title' => $job['job_title'],
            'deletion_type' => $deletion_type,
            'had_applications' => $has_applications,
            'applications_count' => (int)$job['applications_count']
        ],
        'message' => $deletion_message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Database error in delete_job.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Unable to delete job posting. Please try again.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Error in delete_job.php: " . $e->getMessage());
    
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
    
    error_log("Unexpected error in delete_job.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred',
        'message' => 'Please try again later or contact support.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>