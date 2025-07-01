<?php
// Simplified notifications API - no external dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once('../db.php');

// Set content type
header('Content-Type: application/json');

try {
    // Simple session check
    if (!isset($_SESSION['employer_id'])) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Unauthorized - Please log in',
            'session_debug' => $_SESSION ?? []
        ]);
        exit();
    }

    $employer_id = $_SESSION['employer_id'];

    // Get filter parameters
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Check if notification_types table exists and has data
    $checkTypesStmt = $conn->prepare("SELECT COUNT(*) as count FROM notification_types");
    $checkTypesStmt->execute();
    $typesCount = $checkTypesStmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($typesCount == 0) {
        echo json_encode([
            'success' => true,
            'notifications' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
                'total_count' => 0,
                'has_more' => false,
                'per_page' => 20
            ],
            'unread_count' => 0,
            'filter' => $filter,
            'search' => $search,
            'message' => 'No notification types found. Please run setup first.'
        ]);
        exit();
    }

    // Simple query for notifications
    $sql = "SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                nt.type_name,
                nt.icon_class,
                nt.color_class,
                CASE 
                    WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN 'Just now'
                    WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' hours ago')
                    WHEN TIMESTAMPDIFF(DAY, n.created_at, NOW()) < 7 THEN CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' days ago')
                    ELSE DATE_FORMAT(n.created_at, '%M %d, %Y')
                END as time_ago
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE n.recipient_type = 'employer' 
            AND n.recipient_id = ?
            ORDER BY n.created_at DESC
            LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$employer_id]);
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

    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $actions = [
            ['text' => 'View Details', 'type' => 'primary', 'link' => 'empapplicants.php']
        ];

        $formattedNotifications[] = [
            'id' => $notification['notification_id'],
            'type' => $notification['type_name'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'time' => $notification['time_ago'],
            'unread' => !$notification['is_read'],
            'actions' => $actions
        ];
    }

    $response = [
        'success' => true,
        'notifications' => $formattedNotifications,
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 1,
            'total_count' => count($formattedNotifications),
            'has_more' => false,
            'per_page' => 20
        ],
        'unread_count' => $unreadCount,
        'filter' => $filter,
        'search' => $search
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_notifications_simple: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>