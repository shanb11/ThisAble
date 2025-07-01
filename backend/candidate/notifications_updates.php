<?php
/**
 * Real-time Notification Updates
 * Provides real-time notification count and latest notifications via AJAX polling
 */

session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

try {
    $action = $_GET['action'] ?? 'get_updates';
    
    switch ($action) {
        case 'get_updates':
            echo json_encode(getNotificationUpdates($seeker_id));
            break;
            
        case 'get_badge_count':
            echo json_encode(getBadgeCount($seeker_id));
            break;
            
        case 'get_latest':
            $limit = intval($_GET['limit'] ?? 5);
            echo json_encode(getLatestNotifications($seeker_id, $limit));
            break;
            
        case 'check_new':
            $last_check = $_GET['last_check'] ?? null;
            echo json_encode(checkNewNotifications($seeker_id, $last_check));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Notification updates error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching updates']);
}

/**
 * Get comprehensive notification updates
 */
function getNotificationUpdates($seeker_id) {
    global $conn;
    
    try {
        // Get badge count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM notifications 
            WHERE recipient_type = 'candidate' 
            AND recipient_id = ? 
            AND is_read = 0
        ");
        $stmt->execute([$seeker_id]);
        $unread_count = $stmt->fetchColumn();
        
        // Get latest notifications (last 5)
        $stmt = $conn->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                nt.type_name,
                nt.icon_class,
                nt.color_class
            FROM notifications n
            INNER JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE n.recipient_type = 'candidate' 
            AND n.recipient_id = ?
            ORDER BY n.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$seeker_id]);
        $latest_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process notifications
        foreach ($latest_notifications as &$notification) {
            $notification['time_ago'] = getTimeAgo($notification['created_at']);
            $notification['frontend_type'] = getFrontendType($notification['type_name']);
        }
        
        // Get counts by type
        $stmt = $conn->prepare("
            SELECT 
                nt.type_name,
                COUNT(*) as total_count,
                SUM(CASE WHEN n.is_read = 0 THEN 1 ELSE 0 END) as unread_count
            FROM notifications n
            INNER JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE n.recipient_type = 'candidate' 
            AND n.recipient_id = ?
            GROUP BY nt.type_name
        ");
        $stmt->execute([$seeker_id]);
        $type_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize by frontend categories
        $category_counts = [
            'application' => 0,
            'job' => 0,
            'system' => 0
        ];
        
        foreach ($type_counts as $type) {
            $frontend_type = getFrontendType($type['type_name']);
            if (isset($category_counts[$frontend_type])) {
                $category_counts[$frontend_type] += $type['unread_count'];
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'unread_count' => $unread_count,
                'latest_notifications' => $latest_notifications,
                'category_counts' => $category_counts,
                'last_updated' => date('Y-m-d H:i:s')
            ]
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get just the badge count for sidebar
 */
function getBadgeCount($seeker_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM notifications 
            WHERE recipient_type = 'candidate' 
            AND recipient_id = ? 
            AND is_read = 0
        ");
        $stmt->execute([$seeker_id]);
        $unread_count = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'unread_count' => $unread_count
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get latest notifications
 */
function getLatestNotifications($seeker_id, $limit = 5) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                nt.type_name,
                nt.icon_class,
                nt.color_class
            FROM notifications n
            INNER JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE n.recipient_type = 'candidate' 
            AND n.recipient_id = ?
            ORDER BY n.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$seeker_id, $limit]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process notifications
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = getTimeAgo($notification['created_at']);
            $notification['frontend_type'] = getFrontendType($notification['type_name']);
        }
        
        return [
            'success' => true,
            'notifications' => $notifications
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Check for new notifications since last check
 */
function checkNewNotifications($seeker_id, $last_check) {
    global $conn;
    
    try {
        $where_clause = "n.recipient_type = 'candidate' AND n.recipient_id = ?";
        $params = [$seeker_id];
        
        if ($last_check) {
            $where_clause .= " AND n.created_at > ?";
            $params[] = $last_check;
        } else {
            // If no last check, only get notifications from last 5 minutes
            $where_clause .= " AND n.created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        }
        
        $stmt = $conn->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                nt.type_name,
                nt.icon_class,
                nt.color_class
            FROM notifications n
            INNER JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE {$where_clause}
            ORDER BY n.created_at DESC
        ");
        $stmt->execute($params);
        $new_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process notifications
        foreach ($new_notifications as &$notification) {
            $notification['time_ago'] = getTimeAgo($notification['created_at']);
            $notification['frontend_type'] = getFrontendType($notification['type_name']);
        }
        
        // Get current unread count
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE recipient_type = 'candidate' 
            AND recipient_id = ? 
            AND is_read = 0
        ");
        $stmt->execute([$seeker_id]);
        $unread_count = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'has_new' => count($new_notifications) > 0,
            'new_notifications' => $new_notifications,
            'total_unread' => $unread_count,
            'check_time' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Helper functions
 */
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j', strtotime($datetime));
}

function getFrontendType($dbType) {
    $mapping = [
        'new_application' => 'application',
        'application_status' => 'application', 
        'interview_scheduled' => 'application',
        'interview_reminder' => 'application',
        'interview_feedback' => 'application',
        'job_posted' => 'job',
        'deadline_reminder' => 'job',
        'job_expiring' => 'job', 
        'job_performance' => 'job',
        'system_update' => 'system',
        'profile_completion' => 'system',
        'subscription_renewal' => 'system'
    ];
    
    return $mapping[$dbType] ?? 'system';
}
?>