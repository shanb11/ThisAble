<?php
// Working notifications API - compatible with your existing session system
session_start();
require_once('../db.php');
require_once('../shared/session_helper.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Use your existing session functions
    if (!isEmployerLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Please log in as employer',
            'redirect' => 'emplogin.php'
        ]);
        exit();
    }

    $employer_id = getCurrentEmployerId();
    
    // Get parameters
    $filter = $_GET['filter'] ?? 'all';
    $search = $_GET['search'] ?? '';

    // Check if notification system is set up
    $typeCheckStmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $typeCheckStmt->execute();
    $typeCount = $typeCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($typeCount == 0) {
        echo json_encode([
            'success' => true,
            'notifications' => [],
            'unread_count' => 0,
            'setup_needed' => true,
            'message' => 'Notification system needs setup. Please run setup_notifications.php'
        ]);
        exit();
    }

    // Build the query
    $sql = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                n.related_job_id,
                n.related_application_id,
                nt.type_name,
                nt.icon_class,
                nt.color_class,
                jp.job_title,
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 5 THEN 'Just now'
                    WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' minutes ago')
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

    // Add filters
    if ($filter !== 'all') {
        $filterConditions = [
            'applicant' => ['new_application', 'application_status'],
            'job' => ['job_posted', 'job_expiring', 'job_performance'],
            'interview' => ['interview_scheduled', 'interview_reminder'],
            'system' => ['system_update', 'subscription_renewal', 'profile_completion']
        ];

        if (isset($filterConditions[$filter])) {
            $placeholders = implode(',', array_fill(0, count($filterConditions[$filter]), '?'));
            $sql .= " AND nt.type_name IN ($placeholders)";
            $params = array_merge($params, $filterConditions[$filter]);
        }
    }

    // Add search
    if (!empty($search)) {
        $sql .= " AND (n.title LIKE ? OR n.message LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY n.created_at DESC LIMIT 50";

    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $unreadStmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE recipient_type = 'employer' AND recipient_id = ? AND is_read = 0");
    $unreadStmt->execute([$employer_id]);
    $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        // Generate actions based on type
        $actions = [];
        switch ($notification['type_name']) {
            case 'new_application':
            case 'application_status':
                $actions = [
                    ['text' => 'View Applicants', 'type' => 'primary', 'link' => 'empapplicants.php'],
                    ['text' => 'View Profile', 'type' => 'secondary', 'link' => 'empapplicants.php']
                ];
                break;
            case 'interview_scheduled':
            case 'interview_reminder':
                $actions = [
                    ['text' => 'View Interview', 'type' => 'primary', 'link' => 'empapplicants.php'],
                    ['text' => 'Reschedule', 'type' => 'secondary', 'link' => 'empapplicants.php']
                ];
                break;
            case 'job_posted':
            case 'job_expiring':
            case 'job_performance':
                $actions = [
                    ['text' => 'View Jobs', 'type' => 'primary', 'link' => 'empjoblist.php'],
                    ['text' => 'Edit Job', 'type' => 'secondary', 'link' => 'empjoblist.php']
                ];
                break;
            default:
                $actions = [
                    ['text' => 'View Details', 'type' => 'primary', 'link' => 'empdashboard.php']
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
            'created_at' => $notification['created_at'],
            'job_title' => $notification['job_title']
        ];
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'notifications' => $formattedNotifications,
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 1,
            'total_count' => count($formattedNotifications),
            'has_more' => false,
            'per_page' => 50
        ],
        'unread_count' => $unreadCount,
        'filter' => $filter,
        'search' => $search,
        'employer_info' => [
            'employer_id' => $employer_id,
            'company_name' => getCurrentCompanyName()
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in working_notifications: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => 'Please check server logs'
    ]);
} catch (Exception $e) {
    error_log("General error in working_notifications: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>