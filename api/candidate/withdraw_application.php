<?php
/**
 * PHASE 4: Withdraw Application API for ThisAble Mobile
 * File: C:\xampp\htdocs\ThisAble\api\candidate\withdraw_application.php
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
    $applicationId = intval($input['application_id'] ?? 0);
    $withdrawalReason = $input['reason'] ?? 'Withdrawn by candidate';
    
    if ($applicationId <= 0) {
        ApiResponse::validationError(['application_id' => 'Valid application ID is required']);
    }
    
    error_log("Withdraw Application API: seeker_id=$seekerId, application_id=$applicationId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: VERIFY APPLICATION EXISTS AND BELONGS TO USER =====
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.application_status,
            ja.applied_at,
            jp.job_title,
            e.company_name
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.application_id = ? AND ja.seeker_id = ?
    ");
    
    $stmt->execute([$applicationId, $seekerId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        ApiResponse::error("Application not found or access denied", 404);
    }
    
    // ===== STEP 2: CHECK IF APPLICATION CAN BE WITHDRAWN =====
    $withdrawableStatuses = ['submitted', 'under_review', 'interview_scheduled'];
    if (!in_array($application['application_status'], $withdrawableStatuses)) {
        $statusDisplayName = ucwords(str_replace('_', ' ', $application['application_status']));
        ApiResponse::error("Cannot withdraw application with status: $statusDisplayName", 400);
    }
    
    // ===== STEP 3: WITHDRAW APPLICATION =====
    $conn->beginTransaction();
    
    try {
        // Update application status to withdrawn
        $stmt = $conn->prepare("
            UPDATE job_applications 
            SET 
                application_status = 'withdrawn',
                candidate_notes = CONCAT(
                    COALESCE(candidate_notes, ''), 
                    CASE 
                        WHEN candidate_notes IS NOT NULL AND candidate_notes != '' 
                        THEN '\n\n--- Withdrawal ---\n' 
                        ELSE '--- Withdrawal ---\n' 
                    END,
                    'Reason: ', ?,
                    '\nWithdrawn on: ', NOW()
                ),
                status_updated_at = NOW(),
                last_activity = NOW()
            WHERE application_id = ? AND seeker_id = ?
        ");
        
        $result = $stmt->execute([$withdrawalReason, $applicationId, $seekerId]);
        
        if (!$result || $stmt->rowCount() === 0) {
            throw new Exception("Failed to withdraw application");
        }
        
        // ===== STEP 4: ADD STATUS HISTORY RECORD =====
        $stmt = $conn->prepare("
            INSERT INTO application_status_history 
            (application_id, previous_status, new_status, changed_by_employer, notes, changed_at)
            VALUES (?, ?, 'withdrawn', 0, ?, NOW())
        ");
        
        $historyNote = "Application withdrawn by candidate. Reason: " . $withdrawalReason;
        $stmt->execute([$applicationId, $application['application_status'], $historyNote]);
        
        // ===== STEP 5: NOTIFY EMPLOYER (OPTIONAL) =====
        try {
            $stmt = $conn->prepare("
                INSERT INTO notifications 
                (recipient_type, recipient_id, type_id, title, message, related_application_id, created_at)
                SELECT 
                    'employer',
                    jp.employer_id,
                    3, -- application_status type
                    'Application Withdrawn',
                    CONCAT('A candidate has withdrawn their application for \"', jp.job_title, '\"'),
                    ?,
                    NOW()
                FROM job_applications ja
                JOIN job_posts jp ON ja.job_id = jp.job_id
                WHERE ja.application_id = ?
            ");
            $stmt->execute([$applicationId, $applicationId]);
        } catch (Exception $e) {
            // Don't fail the whole transaction if notification fails
            error_log("Failed to create withdrawal notification: " . $e->getMessage());
        }
        
        // Commit transaction
        $conn->commit();
        
        // ===== STEP 6: BUILD SUCCESS RESPONSE =====
        $response = [
            'application_id' => $applicationId,
            'job_title' => $application['job_title'],
            'company_name' => $application['company_name'],
            'previous_status' => $application['application_status'],
            'new_status' => 'withdrawn',
            'withdrawal_reason' => $withdrawalReason,
            'withdrawn_at' => date('Y-m-d H:i:s')
        ];
        
        error_log("Application $applicationId successfully withdrawn by user $seekerId");
        
        ApiResponse::success($response, "Application withdrawn successfully");
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Withdraw Application Database Error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred while withdrawing application");
    
} catch (Exception $e) {
    error_log("Withdraw Application Error: " . $e->getMessage());
    ApiResponse::error("Failed to withdraw application: " . $e->getMessage(), 500);
}
?>