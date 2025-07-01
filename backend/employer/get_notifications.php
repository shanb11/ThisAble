<?php
session_start();
require_once('../db.php');

// Set content type
header('Content-Type: application/json');

// Check if employer is logged in
if (!isset($_SESSION['employer_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized - Please log in']);
    exit();
}

$employer_id = $_SESSION['employer_id'];

try {
    // Get filter parameters with defaults
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 20; // Fixed limit for now
    $offset = ($page - 1) * $limit;

    // First, let's check if notification_types table has data
    $checkTypesStmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $checkTypesStmt->execute();
    $typesCount = $checkTypesStmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($typesCount == 0) {
        // Return empty but valid response if no notification types exist
        echo json_encode([
            'success' => true,
            'notifications' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
                'total_count' => 0,
                'has_more' => false,
                'per_page' => $limit
            ],
            'unread_count' => 0,
            'filter' => $filter,
            'search' => $search,
            'message' => 'No notification types found. Please run setup first.'
        ]);
        exit();
    }

    // Base query for notifications
    $sql = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                n.read_at,
                n.related_job_id,
                n.related_application_id,
                n.related_interview_id,
                nt.type_name,
                nt.icon_class,
                nt.color_class,
                jp.job_title,
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN 'Just now'
                    WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' hours ago')
                    WHEN TIMESTAMPDIFF(DAY, n.created_at, NOW()) < 7 THEN CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' days ago')
                    WHEN TIMESTAMPDIFF(WEEK, n.created_at, NOW()) < 4 THEN CONCAT(TIMESTAMPDIFF(WEEK, n.created_at, NOW()), ' weeks ago')
                    ELSE DATE_FORMAT(n.created_at, '%M %d, %Y')
                END as time_ago
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.type_id
            LEFT JOIN job_posts jp ON n.related_job_id = jp.job_id
            WHERE n.recipient_type = 'employer' 
            AND n.recipient_id = ?";

    $params = [$employer_id];

    // Add filter conditions
    if ($filter !== 'all') {
        $filterMap = [
            'applicant' => ['new_application', 'application_status'],
            'job' => ['job_posted', 'job_expiring', 'job_performance'],
            'interview' => ['interview_scheduled', 'interview_reminder', 'interview_feedback'],
            'system' => ['system_update', 'subscription_renewal', 'profile_completion']
        ];

        if (isset($filterMap[$filter])) {
            $placeholders = implode(',', array_fill(0, count($filterMap[$filter]), '?'));
            $sql .= " AND nt.type_name IN ($placeholders)";
            $params = array_merge($params, $filterMap[$filter]);
        }
    }

    // Add search conditions
    if (!empty($search)) {
        $sql .= " AND (n.title LIKE ? OR n.message LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM notifications n 
                 JOIN notification_types nt ON n.type_id = nt.type_id
                 WHERE n.recipient_type = 'employer' AND n.recipient_id = ?";
    $countParams = [$employer_id];
    
    if ($filter !== 'all' && isset($filterMap[$filter])) {
        $placeholders = implode(',', array_fill(0, count($filterMap[$filter]), '?'));
        $countSql .= " AND nt.type_name IN ($placeholders)";
        $countParams = array_merge($countParams, $filterMap[$filter]);
    }
    
    if (!empty($search)) {
        $countSql .= " AND (n.title LIKE ? OR n.message LIKE ?)";
        $searchParam = "%$search%";
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
    }

    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($countParams);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Add ordering and pagination to main query
    $sql .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    // Execute main query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $unreadSql = "SELECT COUNT(*) as unread_count 
                  FROM notifications 
                  WHERE recipient_type = 'employer' 
                  AND recipient_id = ? 
                  AND is_read = 0";
    $unreadStmt = $conn->prepare($unreadSql);
    $unreadStmt->execute([$employer_id]);
    $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Format notifications for frontend
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $actions = [];
        
        // Generate actions based on notification type
        switch ($notification['type_name']) {
            case 'new_application':
            case 'application_status':
                $actions = [
                    ['text' => 'View Profile', 'type' => 'primary', 'link' => 'empapplicants.php'],
                    ['text' => 'Schedule Interview', 'type' => 'secondary', 'link' => 'empapplicants.php']
                ];
                break;
            case 'interview_scheduled':
            case 'interview_reminder':
                $actions = [
                    ['text' => 'View Details', 'type' => 'primary', 'link' => 'empapplicants.php'],
                    ['text' => 'Reschedule', 'type' => 'secondary', 'link' => 'empapplicants.php']
                ];
                break;
            case 'job_posted':
            case 'job_expiring':
            case 'job_performance':
                $actions = [
                    ['text' => 'View Job', 'type' => 'primary', 'link' => 'empjoblist.php'],
                    ['text' => 'Edit Job', 'type' => 'secondary', 'link' => 'empjoblist.php']
                ];
                break;
            default:
                $actions = [
                    ['text' => 'View Settings', 'type' => 'primary', 'link' => 'empsettings.php']
                ];
                break;
        }

        $formattedNotifications[] = [
            'id' => $notification['notification_id'],
            'type' => $notification['type_name'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'time' => $notification['time_ago'],
            'unread' => !$notification['is_read'],
            'actions' => $actions,
            'icon_class' => $notification['icon_class'] ?: 'fas fa-bell',
            'color_class' => $notification['color_class'] ?: 'blue',
            'job_title' => $notification['job_title'],
            'created_at' => $notification['created_at']
        ];
    }

    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);
    $hasMore = $page < $totalPages;

    $response = [
        'success' => true,
        'notifications' => $formattedNotifications,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_count' => $totalCount,
            'has_more' => $hasMore,
            'per_page' => $limit
        ],
        'unread_count' => $unreadCount,
        'filter' => $filter,
        'search' => $search
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in get_notifications: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => 'Please check server logs for details'
    ]);
} catch (Exception $e) {
    error_log("General error in get_notifications: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while fetching notifications',
        'message' => $e->getMessage()
    ]);
}
?>