<?php
// backend/candidate/get_application_stats.php
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

try {
    // Get comprehensive application statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN ja.application_status IN ('under_review', 'shortlisted') THEN 1 ELSE 0 END) as reviewed_applications,
            SUM(CASE WHEN ja.application_status IN ('interview_scheduled', 'interviewed') THEN 1 ELSE 0 END) as interviews_scheduled,
            SUM(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) as job_offers,
            SUM(CASE WHEN ja.application_status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications,
            SUM(CASE WHEN ja.application_status = 'submitted' THEN 1 ELSE 0 END) as pending_applications
        FROM job_applications ja
        WHERE ja.seeker_id = :seeker_id
    ";
    
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent activity (applications in last 30 days)
    $recentQuery = "
        SELECT COUNT(*) as recent_applications
        FROM job_applications ja
        WHERE ja.seeker_id = :seeker_id
        AND ja.applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ";
    
    $recentStmt = $conn->prepare($recentQuery);
    $recentStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $recentStmt->execute();
    $recentStats = $recentStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get upcoming interviews
    $upcomingInterviewsQuery = "
        SELECT COUNT(*) as upcoming_interviews
        FROM interviews i
        INNER JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE ja.seeker_id = :seeker_id
        AND i.scheduled_date >= CURDATE()
        AND i.interview_status IN ('scheduled', 'confirmed')
    ";
    
    $interviewStmt = $conn->prepare($upcomingInterviewsQuery);
    $interviewStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $interviewStmt->execute();
    $interviewStats = $interviewStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get response rate (reviewed vs total)
    $responseRate = $stats['total_applications'] > 0 
        ? round(($stats['reviewed_applications'] / $stats['total_applications']) * 100) 
        : 0;
    
    // Get success rate (offers vs total)
    $successRate = $stats['total_applications'] > 0 
        ? round(($stats['job_offers'] / $stats['total_applications']) * 100) 
        : 0;
    
    // Calculate trends (compare with previous month)
    $trendsQuery = "
        SELECT 
            COUNT(*) as prev_month_applications
        FROM job_applications ja
        WHERE ja.seeker_id = :seeker_id
        AND ja.applied_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 30 DAY), INTERVAL 30 DAY)
        AND ja.applied_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ";
    
    $trendsStmt = $conn->prepare($trendsQuery);
    $trendsStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $trendsStmt->execute();
    $trendsStats = $trendsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate trend percentage
    $applicationTrend = 0;
    if ($trendsStats['prev_month_applications'] > 0) {
        $applicationTrend = round((($recentStats['recent_applications'] - $trendsStats['prev_month_applications']) / $trendsStats['prev_month_applications']) * 100);
    } elseif ($recentStats['recent_applications'] > 0) {
        $applicationTrend = 100; // New applications this month
    }
    
    // Format the response
    $formattedStats = [
        'total_applications' => (int)$stats['total_applications'],
        'reviewed_applications' => (int)$stats['reviewed_applications'],
        'interviews_scheduled' => (int)$stats['interviews_scheduled'],
        'job_offers' => (int)$stats['job_offers'],
        'rejected_applications' => (int)$stats['rejected_applications'],
        'pending_applications' => (int)$stats['pending_applications'],
        'recent_applications' => (int)$recentStats['recent_applications'],
        'upcoming_interviews' => (int)$interviewStats['upcoming_interviews'],
        'response_rate' => $responseRate,
        'success_rate' => $successRate,
        'application_trend' => $applicationTrend,
        'cards' => [
            [
                'icon' => 'fas fa-file-alt',
                'number' => (int)$stats['total_applications'],
                'label' => 'Total Applications',
                'trend' => $applicationTrend > 0 ? "+{$applicationTrend}%" : ($applicationTrend < 0 ? "{$applicationTrend}%" : ""),
                'trend_positive' => $applicationTrend >= 0
            ],
            [
                'icon' => 'fas fa-eye',
                'number' => (int)$stats['reviewed_applications'],
                'label' => 'Applications Reviewed',
                'percentage' => $responseRate,
                'trend_positive' => true
            ],
            [
                'icon' => 'fas fa-users',
                'number' => (int)$stats['interviews_scheduled'],
                'label' => 'Interviews Scheduled',
                'upcoming' => (int)$interviewStats['upcoming_interviews'],
                'trend_positive' => true
            ],
            [
                'icon' => 'fas fa-check-circle',
                'number' => (int)$stats['job_offers'],
                'label' => 'Job Offers',
                'percentage' => $successRate,
                'trend_positive' => true
            ]
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'stats' => $formattedStats
    ]);

} catch (Exception $e) {
    error_log("Error in get_application_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch application statistics'
    ]);
}
?>