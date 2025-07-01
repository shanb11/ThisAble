<?php
/**
 * Get Applications List API for ThisAble Mobile
 * Returns: filtered applications list with status tracking
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    error_log("Applications List API: seeker_id=$seekerId");

    // Get query parameters
    $status_filter = $_GET['status'] ?? 'all';
    $search_query = $_GET['search'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== BUILD QUERY =====
    $whereConditions = ["ao.seeker_id = ?"];
    $params = [$seekerId];
    
    // Status filter
    if ($status_filter !== 'all') {
        $whereConditions[] = "ao.application_status = ?";
        $params[] = $status_filter;
    }
    
    // Search filter
    if (!empty($search_query)) {
        $whereConditions[] = "(ao.job_title LIKE ? OR ao.company_name LIKE ? OR ao.job_location LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // ===== GET APPLICATIONS =====
    $stmt = $conn->prepare("
        SELECT 
            ao.*,
            CASE 
                WHEN ao.application_status = 'submitted' THEN 20
                WHEN ao.application_status = 'under_review' THEN 40
                WHEN ao.application_status = 'shortlisted' THEN 60
                WHEN ao.application_status = 'interview_scheduled' THEN 60
                WHEN ao.application_status = 'interviewed' THEN 80
                WHEN ao.application_status = 'hired' THEN 100
                ELSE 20
            END as progress_percentage,
            CASE 
                WHEN ao.application_status IN ('submitted', 'under_review') THEN 1
                ELSE 0
            END as can_withdraw
        FROM applicant_overview ao
        WHERE {$whereClause}
        ORDER BY ao.applied_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset;
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== GET APPLICATION TIMELINE FOR EACH =====
    foreach ($applications as &$application) {
        // Get timeline from application_status_history
        $timelineStmt = $conn->prepare("
            SELECT 
                changed_at as date,
                new_status as status,
                notes,
                CASE 
                    WHEN new_status = 'submitted' THEN 'Application Submitted'
                    WHEN new_status = 'under_review' THEN 'Application Reviewed'
                    WHEN new_status = 'shortlisted' THEN 'Shortlisted'
                    WHEN new_status = 'interview_scheduled' THEN 'Interview Scheduled'
                    WHEN new_status = 'interviewed' THEN 'Interview Completed'
                    WHEN new_status = 'hired' THEN 'Job Offer Received'
                    WHEN new_status = 'rejected' THEN 'Application Rejected'
                    ELSE 'Status Updated'
                END as title,
                CASE 
                    WHEN new_status = 'submitted' THEN 'Your application has been successfully submitted.'
                    WHEN new_status = 'under_review' THEN 'Your application is being reviewed by the hiring team.'
                    WHEN new_status = 'shortlisted' THEN 'Congratulations! You have been shortlisted for further consideration.'
                    WHEN new_status = 'interview_scheduled' THEN 'An interview has been scheduled. Check your email for details.'
                    WHEN new_status = 'interviewed' THEN 'Interview completed. Waiting for feedback.'
                    WHEN new_status = 'hired' THEN 'Congratulations! You have received a job offer.'
                    WHEN new_status = 'rejected' THEN 'Unfortunately, your application was not successful.'
                    ELSE COALESCE(notes, 'Application status updated.')
                END as description
            FROM application_status_history
            WHERE application_id = ?
            ORDER BY changed_at ASC
        ");
        $timelineStmt->execute([$application['application_id']]);
        $application['timeline'] = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format timeline dates
        foreach ($application['timeline'] as &$event) {
            $event['date'] = date('F j, Y', strtotime($event['date']));
        }
        
        // Format application date
        $application['applied_at'] = date('F j, Y', strtotime($application['applied_at']));
        
        // Add contact person info (from employer_contacts)
        $contactStmt = $conn->prepare("
            SELECT 
                CONCAT(first_name, ' ', last_name) as contact_person,
                email as contact_email
            FROM employer_contacts ec
            JOIN employers e ON ec.employer_id = e.employer_id
            JOIN job_posts jp ON e.employer_id = jp.employer_id
            WHERE jp.job_id = ? AND ec.is_primary = 1
            LIMIT 1
        ");
        $contactStmt->execute([$application['job_id']]);
        $contact = $contactStmt->fetch(PDO::FETCH_ASSOC);
        
        $application['contact_person'] = $contact['contact_person'] ?? 'HR Department';
        $application['contact_email'] = $contact['contact_email'] ?? '';
        
        // Add company logo placeholder
        $application['company_logo'] = substr($application['company_name'], 0, 2);
    }
    
    // ===== GET TOTAL COUNT =====
    $countParams = array_slice($params, 0, -2); // Remove limit and offset
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total_count
        FROM applicant_overview ao
        WHERE {$whereClause}
    ");
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // ===== GET STATS FOR FILTERS =====
    $statsStmt = $conn->prepare("
        SELECT 
            application_status,
            COUNT(*) as count
        FROM applicant_overview
        WHERE seeker_id = ?
        GROUP BY application_status
    ");
    $statsStmt->execute([$seekerId]);
    $statusStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array
    $stats = ['all' => $totalCount];
    foreach ($statusStats as $stat) {
        $stats[$stat['application_status']] = $stat['count'];
    }
    
    // ===== COMPILE RESPONSE =====
    $responseData = [
        'applications' => $applications,
        'pagination' => [
            'current_page' => $page,
            'total_count' => $totalCount,
            'per_page' => $limit,
            'total_pages' => ceil($totalCount / $limit)
        ],
        'filter_stats' => $stats
    ];
    
    ApiResponse::success($responseData, "Applications retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Applications list database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Applications list error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while retrieving applications");
}
?>