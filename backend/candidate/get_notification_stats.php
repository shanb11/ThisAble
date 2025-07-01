<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

try {
    // Get overall notification statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_notifications,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_count,
            SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
            MAX(created_at) as latest_notification
        FROM notifications 
        WHERE recipient_type = 'candidate' 
        AND recipient_id = ?
    ");
    
    $stmt->execute([$seeker_id]);
    $overall_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get notification counts by type (frontend categories)
    $stmt = $conn->prepare("
        SELECT 
            nt.type_name,
            COUNT(*) as total_count,
            SUM(CASE WHEN n.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
            SUM(CASE WHEN n.is_read = 1 THEN 1 ELSE 0 END) as read_count
        FROM notifications n
        INNER JOIN notification_types nt ON n.type_id = nt.type_id
        WHERE n.recipient_type = 'candidate' 
        AND n.recipient_id = ?
        GROUP BY nt.type_name
        ORDER BY unread_count DESC
    ");
    
    $stmt->execute([$seeker_id]);
    $type_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group by frontend categories
    $frontend_categories = [
        'application' => ['new_application', 'application_status', 'interview_scheduled', 'interview_reminder', 'interview_feedback'],
        'job' => ['job_posted', 'deadline_reminder', 'job_expiring', 'job_performance'],
        'system' => ['system_update', 'profile_completion', 'subscription_renewal']
    ];
    
    $category_stats = [
        'all' => [
            'total' => (int)$overall_stats['total_notifications'],
            'unread' => (int)$overall_stats['unread_count'],
            'read' => (int)$overall_stats['read_count']
        ],
        'application' => ['total' => 0, 'unread' => 0, 'read' => 0],
        'job' => ['total' => 0, 'unread' => 0, 'read' => 0],
        'system' => ['total' => 0, 'unread' => 0, 'read' => 0]
    ];
    
    // Aggregate stats by frontend categories
    foreach ($type_stats as $stat) {
        $type_name = $stat['type_name'];
        
        foreach ($frontend_categories as $category => $types) {
            if (in_array($type_name, $types)) {
                $category_stats[$category]['total'] += (int)$stat['total_count'];
                $category_stats[$category]['unread'] += (int)$stat['unread_count'];
                $category_stats[$category]['read'] += (int)$stat['read_count'];
                break;
            }
        }
    }
    
    // Get recent activity (last 7 days)
    $stmt = $conn->prepare("
        SELECT 
            DATE(created_at) as notification_date,
            COUNT(*) as daily_count
        FROM notifications 
        WHERE recipient_type = 'candidate' 
        AND recipient_id = ?
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY notification_date DESC
    ");
    
    $stmt->execute([$seeker_id]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get priority notifications (unread application/interview related)
    $stmt = $conn->prepare("
        SELECT 
            n.notification_id,
            n.title,
            n.message,
            n.created_at,
            nt.type_name,
            nt.icon_class,
            nt.color_class
        FROM notifications n
        INNER JOIN notification_types nt ON n.type_id = nt.type_id
        WHERE n.recipient_type = 'candidate' 
        AND n.recipient_id = ?
        AND n.is_read = 0
        AND nt.type_name IN ('interview_scheduled', 'interview_reminder', 'application_status', 'deadline_reminder')
        ORDER BY n.created_at DESC
        LIMIT 5
    ");
    
    $stmt->execute([$seeker_id]);
    $priority_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format time for priority notifications
    foreach ($priority_notifications as &$notification) {
        $notification['time_ago'] = getTimeAgo($notification['created_at']);
    }
    
    // Calculate trends (compare last 7 days vs previous 7 days)
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'current_week'
                WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) THEN 'previous_week'
            END as week_period,
            COUNT(*) as notification_count
        FROM notifications 
        WHERE recipient_type = 'candidate' 
        AND recipient_id = ?
        AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
        GROUP BY week_period
    ");
    
    $stmt->execute([$seeker_id]);
    $trend_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $current_week = (int)($trend_data['current_week'] ?? 0);
    $previous_week = (int)($trend_data['previous_week'] ?? 0);
    
    $trend_percentage = 0;
    if ($previous_week > 0) {
        $trend_percentage = round((($current_week - $previous_week) / $previous_week) * 100, 1);
    } elseif ($current_week > 0) {
        $trend_percentage = 100; // New notifications this week
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'overall' => $overall_stats,
            'by_category' => $category_stats,
            'recent_activity' => $recent_activity,
            'priority_notifications' => $priority_notifications,
            'trends' => [
                'current_week' => $current_week,
                'previous_week' => $previous_week,
                'percentage_change' => $trend_percentage
            ],
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get notification stats error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching notification statistics']);
}

// Helper function to generate time ago string
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>