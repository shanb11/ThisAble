<?php
/**
 * Application Actions API for ThisAble Mobile
 * Handles: withdraw applications, contact companies
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $applicationId = intval($input['application_id'] ?? 0);
    
    if (empty($action) || $applicationId <= 0) {
        ApiResponse::validationError(['action' => 'Action and application_id are required']);
    }
    
    error_log("Application Actions API: seeker_id=$seekerId, action=$action, application_id=$applicationId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Verify application belongs to user
    $stmt = $conn->prepare("
        SELECT ja.*, jp.job_title, e.company_name 
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.application_id = ? AND ja.seeker_id = ?
    ");
    $stmt->execute([$applicationId, $seekerId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        ApiResponse::error("Application not found", 404);
    }
    
    switch ($action) {
        case 'withdraw_application':
            // Check if application can be withdrawn
            if (in_array($application['application_status'], ['hired', 'rejected', 'withdrawn'])) {
                ApiResponse::error("Application cannot be withdrawn at this stage", 400);
            }
            
            $withdrawalReason = $input['reason'] ?? 'Withdrawn by candidate';
            
            $conn->beginTransaction();
            
            try {
                // Update application status
                $stmt = $conn->prepare("
                    UPDATE job_applications 
                    SET application_status = 'withdrawn', 
                        candidate_notes = CONCAT(COALESCE(candidate_notes, ''), '\n\nWithdrawal reason: ', ?),
                        status_updated_at = NOW()
                    WHERE application_id = ?
                ");
                $stmt->execute([$withdrawalReason, $applicationId]);
                
                // Insert into application status history
                $stmt = $conn->prepare("
                    INSERT INTO application_status_history 
                    (application_id, previous_status, new_status, notes, changed_at, changed_by_employer) 
                    VALUES (?, ?, 'withdrawn', ?, NOW(), 0)
                ");
                $stmt->execute([$applicationId, $application['application_status'], $withdrawalReason]);
                
                // Update job applications count
                $stmt = $conn->prepare("
                    UPDATE job_posts 
                    SET applications_count = applications_count - 1 
                    WHERE job_id = ?
                ");
                $stmt->execute([$application['job_id']]);
                
                $conn->commit();
                
                ApiResponse::success([
                    'withdrawn' => true,
                    'status' => 'withdrawn'
                ], "Application withdrawn successfully");
                
            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }
            break;
            
        case 'contact_company':
            // Get company contact information
            $stmt = $conn->prepare("
                SELECT 
                    ec.first_name,
                    ec.last_name,
                    ec.email,
                    ec.position,
                    e.company_name
                FROM employer_contacts ec
                JOIN employers e ON ec.employer_id = e.employer_id
                JOIN job_posts jp ON e.employer_id = jp.employer_id
                WHERE jp.job_id = ? AND ec.is_primary = 1
                LIMIT 1
            ");
            $stmt->execute([$application['job_id']]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$contact) {
                ApiResponse::error("Company contact information not available", 404);
            }
            
            $contactInfo = [
                'contact_person' => $contact['first_name'] . ' ' . $contact['last_name'],
                'email' => $contact['email'],
                'position' => $contact['position'],
                'company_name' => $contact['company_name'],
                'job_title' => $application['job_title']
            ];
            
            ApiResponse::success($contactInfo, "Company contact information retrieved");
            break;
            
        case 'share_application':
            // Generate shareable application summary
            $platform = $input['platform'] ?? 'unknown';
            
            $shareData = [
                'job_title' => $application['job_title'],
                'company_name' => $application['company_name'],
                'application_status' => $application['application_status'],
                'applied_date' => date('F j, Y', strtotime($application['applied_at']))
            ];
            
            // For now, just return the data - you could implement actual sharing links
            ApiResponse::success([
                'shared' => true,
                'share_data' => $shareData
            ], "Application share data generated");
            break;
            
        case 'request_feedback':
            // Only allow for rejected applications
            if ($application['application_status'] !== 'rejected') {
                ApiResponse::error("Feedback can only be requested for rejected applications", 400);
            }
            
            $message = $input['message'] ?? 'Could you please provide feedback on my application?';
            
            // You could implement a notification system here to send to employers
            // For now, just log the request
            
            ApiResponse::success(['feedback_requested' => true], "Feedback request sent to employer");
            break;
            
        default:
            ApiResponse::validationError(['action' => 'Invalid action specified']);
    }
    
} catch(PDOException $e) {
    error_log("Application actions database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Application actions error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while processing application action");
}
?>