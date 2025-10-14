<?php
/**
 * FIXED Dashboard API for ThisAble Mobile
 * Location: C:\xampp\htdocs\ThisAble\api\candidate\get_dashboard_home.php
 */

require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    // CRITICAL FIX: Ensure we get the correct seeker_id
    $seekerId = $user['seeker_id'] ?? $user['user_id'] ?? null;
    
    if (!$seekerId) {
        error_log("❌ DASHBOARD ERROR: No seeker_id found in user data: " . json_encode($user));
        ApiResponse::serverError("No seeker_id found");
    }
    
    // LOG the seeker_id being used for debugging
    error_log("✅ DASHBOARD API: Using seeker_id = $seekerId for user: " . ($user['email'] ?? 'unknown'));

    $conn = ApiDatabase::getConnection();
    
    // 1. GET APPLICATIONS COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_applications WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $apps_count = (int)$result['count'];
    error_log("📊 Applications count: $apps_count");
    
    // 2. GET SAVED JOBS COUNT
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $saved_count = (int)$result['count'];
    error_log("📊 Saved jobs count: $saved_count");
    
    // 3. GET INTERVIEWS COUNT (upcoming only)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM interviews i
        JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE ja.seeker_id = ? 
        AND i.interview_status IN ('scheduled', 'confirmed')
        AND i.scheduled_date >= CURDATE()
    ");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $interviews_count = (int)$result['count'];
    error_log("📊 Interviews count: $interviews_count");
    
    // 4. GET NOTIFICATIONS COUNT (unread only)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE recipient_type = 'candidate' 
        AND recipient_id = ? 
        AND is_read = 0
    ");
    $stmt->execute([$seekerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $notifications_count = (int)$result['count'];
    error_log("📊 Notifications count: $notifications_count");
    
    // BUILD STATS OBJECT
    $stats = [
        'applications_count' => $apps_count,
        'saved_jobs_count' => $saved_count,
        'interview_scheduled_count' => $interviews_count,
        'notifications_count' => $notifications_count
    ];
    
    error_log("📊 FINAL STATS: " . json_encode($stats));
    
    // GET RECENT APPLICATIONS (last 5)
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            jp.job_title,
            jp.location,
            jp.employment_type,
            e.company_name
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = ?
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seekerId]);
    $recent_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // GET UPCOMING INTERVIEWS (next 3)
    $stmt = $conn->prepare("
        SELECT 
            i.interview_id,
            i.scheduled_date,
            i.scheduled_time,
            i.interview_type,
            i.interview_status,
            jp.job_title,
            e.company_name,
            ja.application_id
        FROM interviews i
        JOIN job_applications ja ON i.application_id = ja.application_id
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = ?
        AND i.interview_status IN ('scheduled', 'confirmed')
        AND i.scheduled_date >= CURDATE()
        ORDER BY i.scheduled_date ASC, i.scheduled_time ASC
        LIMIT 3
    ");
    $stmt->execute([$seekerId]);
    $upcoming_interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // PREPARE RESPONSE DATA
    $responseData = [
        'stats' => $stats,
        'recent_applications' => $recent_applications,
        'upcoming_interviews' => $upcoming_interviews,
        'seeker_id' => $seekerId // For debugging
    ];
    
    error_log("✅ DASHBOARD SUCCESS: Returning data for seeker_id $seekerId");
    
    ApiResponse::success($responseData, 'Dashboard data retrieved successfully');
    
} catch (Exception $e) {
    error_log("❌ DASHBOARD ERROR: " . $e->getMessage());
    error_log("❌ Stack trace: " . $e->getTraceAsString());
    ApiResponse::serverError("Failed to load dashboard data");
}