<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Action required']);
        exit;
    }
    
    $action = $input['action'];
    
    switch ($action) {
        case 'mark_all_read':
            $filter = $input['filter'] ?? 'all';
            
            if ($filter === 'all') {
                $stmt = $conn->prepare("
                    UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE recipient_type = 'candidate' 
                    AND recipient_id = ? 
                    AND is_read = 0
                ");
                $stmt->execute([$seeker_id]);
            } else {
                // Filter-specific mark all as read
                $typeMapping = [
                    'application' => ['new_application', 'application_status', 'interview_scheduled', 'interview_reminder', 'interview_feedback'],
                    'job' => ['job_posted', 'deadline_reminder', 'job_expiring', 'job_performance'],
                    'system' => ['system_update', 'profile_completion', 'subscription_renewal']
                ];
                
                if (isset($typeMapping[$filter])) {
                    $dbTypes = $typeMapping[$filter];
                    $placeholders = str_repeat('?,', count($dbTypes) - 1) . '?';
                    $params = array_merge($dbTypes, [$seeker_id]);
                    
                    $stmt = $conn->prepare("
                        UPDATE notifications n
                        INNER JOIN notification_types nt ON n.type_id = nt.type_id
                        SET n.is_read = 1, n.read_at = NOW() 
                        WHERE nt.type_name IN ($placeholders)
                        AND n.recipient_type = 'candidate' 
                        AND n.recipient_id = ? 
                        AND n.is_read = 0
                    ");
                    $stmt->execute($params);
                }
            }
            
            $affected = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'message' => "Marked {$affected} notifications as read",
                'affected_count' => $affected
            ]);
            break;
            
        case 'delete_notification':
            $notification_id = $input['notification_id'] ?? null;
            
            if (!$notification_id) {
                echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                exit;
            }
            
            // Soft delete by marking as read and adding deleted flag
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE notification_id = ? 
                AND recipient_type = 'candidate' 
                AND recipient_id = ?
            ");
            $stmt->execute([$notification_id, $seeker_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Notification deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
            }
            break;
            
        case 'delete_all_read':
            $stmt = $conn->prepare("
                DELETE FROM notifications 
                WHERE recipient_type = 'candidate' 
                AND recipient_id = ? 
                AND is_read = 1 
                AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$seeker_id]);
            
            $affected = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'message' => "Deleted {$affected} old notifications",
                'affected_count' => $affected
            ]);
            break;
            
        case 'update_preferences':
            $preferences = $input['preferences'] ?? [];
            
            // Update notification preferences
            $stmt = $conn->prepare("
                UPDATE notification_settings 
                SET 
                    email_notifications = ?,
                    push_notifications = ?,
                    job_alerts = ?,
                    application_updates = ?,
                    message_notifications = ?,
                    marketing_notifications = ?
                WHERE seeker_id = ?
            ");
            
            $stmt->execute([
                $preferences['email_notifications'] ?? 1,
                $preferences['push_notifications'] ?? 1,
                $preferences['job_alerts'] ?? 1,
                $preferences['application_updates'] ?? 1,
                $preferences['message_notifications'] ?? 1,
                $preferences['marketing_notifications'] ?? 0,
                $seeker_id
            ]);
            
            if ($stmt->rowCount() === 0) {
                // Insert if not exists
                $stmt = $conn->prepare("
                    INSERT INTO notification_settings 
                    (seeker_id, email_notifications, push_notifications, job_alerts, 
                     application_updates, message_notifications, marketing_notifications)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $seeker_id,
                    $preferences['email_notifications'] ?? 1,
                    $preferences['push_notifications'] ?? 1,
                    $preferences['job_alerts'] ?? 1,
                    $preferences['application_updates'] ?? 1,
                    $preferences['message_notifications'] ?? 1,
                    $preferences['marketing_notifications'] ?? 0
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Notification preferences updated']);
            break;
            
        case 'get_preferences':
            $stmt = $conn->prepare("
                SELECT * FROM notification_settings 
                WHERE seeker_id = ?
            ");
            $stmt->execute([$seeker_id]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$preferences) {
                // Return default preferences
                $preferences = [
                    'email_notifications' => 1,
                    'push_notifications' => 1,
                    'job_alerts' => 1,
                    'application_updates' => 1,
                    'message_notifications' => 1,
                    'marketing_notifications' => 0
                ];
            }
            
            echo json_encode(['success' => true, 'preferences' => $preferences]);
            break;
            
        case 'snooze_notification':
            $notification_id = $input['notification_id'] ?? null;
            $snooze_until = $input['snooze_until'] ?? date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            if (!$notification_id) {
                echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                exit;
            }
            
            // Add a snooze field to notifications table or handle differently
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = ?
                WHERE notification_id = ? 
                AND recipient_type = 'candidate' 
                AND recipient_id = ?
            ");
            $stmt->execute([$snooze_until, $notification_id, $seeker_id]);
            
            echo json_encode(['success' => true, 'message' => 'Notification snoozed']);
            break;
            
        case 'get_notification_stats_detailed':
            // Get detailed statistics
            $stmt = $conn->prepare("
                SELECT 
                    nt.type_name,
                    nt.type_description,
                    COUNT(*) as total_count,
                    SUM(CASE WHEN n.is_read = 0 THEN 1 ELSE 0 END) as unread_count,
                    MAX(n.created_at) as latest_notification
                FROM notifications n
                INNER JOIN notification_types nt ON n.type_id = nt.type_id
                WHERE n.recipient_type = 'candidate' 
                AND n.recipient_id = ?
                GROUP BY nt.type_id, nt.type_name, nt.type_description
                ORDER BY unread_count DESC, total_count DESC
            ");
            $stmt->execute([$seeker_id]);
            $detailed_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'stats' => $detailed_stats]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Notification actions error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing notification action']);
}
?>