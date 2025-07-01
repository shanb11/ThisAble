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
    // Get filter parameters
    $filter = $_GET['filter'] ?? 'all';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(50, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    // Base query with all necessary joins
    $query = "
        SELECT 
            n.notification_id,
            n.title,
            n.message,
            n.is_read,
            n.read_at,
            n.created_at,
            n.related_job_id,
            n.related_application_id,
            n.related_interview_id,
            nt.type_name,
            nt.type_description,
            nt.icon_class,
            nt.color_class,
            jp.job_title,
            jp.location as job_location,
            e.company_name,
            ja.application_status,
            i.scheduled_date as interview_date,
            i.scheduled_time as interview_time,
            i.interview_type
        FROM notifications n
        INNER JOIN notification_types nt ON n.type_id = nt.type_id
        LEFT JOIN job_posts jp ON n.related_job_id = jp.job_id
        LEFT JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN job_applications ja ON n.related_application_id = ja.application_id
        LEFT JOIN interviews i ON n.related_interview_id = i.interview_id
        WHERE n.recipient_type = 'candidate' 
        AND n.recipient_id = ?
    ";
    
    $params = [$seeker_id];
    
    // Add filter conditions
    if ($filter !== 'all') {
        switch ($filter) {
            case 'application':
                $query .= " AND nt.type_name IN ('new_application', 'application_status', 'interview_scheduled', 'interview_reminder', 'interview_feedback')";
                break;
            case 'job':
                $query .= " AND nt.type_name IN ('job_posted', 'deadline_reminder', 'job_expiring', 'job_performance')";
                break;
            case 'system':
                $query .= " AND nt.type_name IN ('system_update', 'profile_completion', 'subscription_renewal')";
                break;
        }
    }
    
    // Add ordering
    $query .= " ORDER BY n.created_at DESC";
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM notifications n
        INNER JOIN notification_types nt ON n.type_id = nt.type_id
        WHERE n.recipient_type = 'candidate' 
        AND n.recipient_id = ?
    ";
    
    $countParams = [$seeker_id];
    
    if ($filter !== 'all') {
        switch ($filter) {
            case 'application':
                $countQuery .= " AND nt.type_name IN ('new_application', 'application_status', 'interview_scheduled', 'interview_reminder', 'interview_feedback')";
                break;
            case 'job':
                $countQuery .= " AND nt.type_name IN ('job_posted', 'deadline_reminder', 'job_expiring', 'job_performance')";
                break;
            case 'system':
                $countQuery .= " AND nt.type_name IN ('system_update', 'profile_completion', 'subscription_renewal')";
                break;
        }
    }
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalNotifications = $countStmt->fetch()['total'];
    
    // Add pagination using MySQL/MariaDB syntax
    $query .= " LIMIT " . intval($offset) . ", " . intval($limit);
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no notifications exist, create a welcome notification
    if (empty($notifications) && $filter === 'all') {
        try {
            $insertStmt = $conn->prepare("
                INSERT INTO notifications (recipient_type, recipient_id, type_id, title, message, is_read, created_at)
                VALUES ('candidate', ?, 5, 'Welcome to ThisAble!', 'Thank you for joining our platform. Complete your profile to get started finding your next opportunity.', 0, NOW())
            ");
            $insertStmt->execute([$seeker_id]);
            
            // Re-fetch notifications after creating welcome message
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalNotifications = 1;
        } catch (Exception $e) {
            error_log("Error creating welcome notification: " . $e->getMessage());
        }
    }
    
    // Process notifications to match frontend format
    $processedNotifications = [];
    
    foreach ($notifications as $notification) {
        // Determine frontend notification type
        $frontendType = getFrontendNotificationType($notification['type_name']);
        
        // Generate time ago string
        $timeAgo = getTimeAgo($notification['created_at']);
        
        // Generate actions based on notification type
        $actions = generateNotificationActions($notification);
        
        // Create enhanced message with context
        $enhancedMessage = enhanceNotificationMessage($notification);
        
        $processedNotifications[] = [
            'id' => $notification['notification_id'],
            'type' => $frontendType,
            'title' => $notification['title'],
            'body' => $enhancedMessage,
            'time' => $timeAgo,
            'unread' => !$notification['is_read'],
            'actions' => $actions,
            'icon_class' => $notification['icon_class'],
            'color_class' => $notification['color_class'],
            'created_at' => $notification['created_at'],
            'related_job_id' => $notification['related_job_id'],
            'related_application_id' => $notification['related_application_id'],
            'related_interview_id' => $notification['related_interview_id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'notifications' => $processedNotifications,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalNotifications / $limit),
            'total_notifications' => $totalNotifications,
            'limit' => $limit
        ],
        'filter' => $filter
    ]);

} catch (Exception $e) {
    error_log("Get notifications error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching notifications: ' . $e->getMessage()]);
}

// Helper function to map database notification types to frontend types
function getFrontendNotificationType($dbType) {
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

// Helper function to enhance notification message with context
function enhanceNotificationMessage($notification) {
    $message = $notification['message'];
    
    // Add company and job context if available
    if ($notification['company_name'] && $notification['job_title']) {
        $jobContext = " for {$notification['job_title']} at {$notification['company_name']}";
        
        // Replace generic job references with specific ones
        $message = str_replace([
            ' position',
            ' role',
            ' job'
        ], [
            $jobContext,
            $jobContext, 
            $jobContext
        ], $message);
    }
    
    // Add interview details if available
    if ($notification['interview_date'] && $notification['interview_time']) {
        $interviewDateTime = date('M j, Y \a\t g:i A', strtotime($notification['interview_date'] . ' ' . $notification['interview_time']));
        $message = str_replace('interview', "interview on {$interviewDateTime}", $message);
    }
    
    return $message;
}

// Helper function to generate action buttons based on notification type
function generateNotificationActions($notification) {
    $actions = [];
    
    switch ($notification['type_name']) {
        case 'application_status':
        case 'new_application':
            $actions[] = [
                'text' => 'View Application',
                'type' => 'primary',
                'link' => 'applications.php'
            ];
            break;
            
        case 'interview_scheduled':
        case 'interview_reminder':
            $actions[] = [
                'text' => 'View Interview',
                'type' => 'primary', 
                'link' => 'applications.php'
            ];
            $actions[] = [
                'text' => 'Reschedule',
                'type' => 'secondary',
                'link' => '#'
            ];
            break;
            
        case 'job_posted':
        case 'deadline_reminder':
        case 'job_performance':
            if ($notification['related_job_id']) {
                $actions[] = [
                    'text' => 'View Job',
                    'type' => 'primary',
                    'link' => 'joblistings.php'
                ];
            }
            break;
            
        case 'profile_completion':
            $actions[] = [
                'text' => 'Complete Profile',
                'type' => 'primary',
                'link' => 'profile.php'
            ];
            $actions[] = [
                'text' => 'Dismiss',
                'type' => 'secondary',
                'link' => '#'
            ];
            break;
            
        case 'system_update':
            $actions[] = [
                'text' => 'Learn More',
                'type' => 'primary',
                'link' => 'settings.php'
            ];
            break;
            
        default:
            $actions[] = [
                'text' => 'View Details',
                'type' => 'primary', 
                'link' => '#'
            ];
            break;
    }
    
    return $actions;
}
?>