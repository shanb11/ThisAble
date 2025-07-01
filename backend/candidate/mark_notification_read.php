<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    $notification_id = $input['notification_id'] ?? null;
    $notification_ids = $input['notification_ids'] ?? [];
    
    switch ($action) {
        case 'mark_single_read':
            if (!$notification_id) {
                echo json_encode(['success' => false, 'message' => 'Notification ID required']);
                exit;
            }
            
            // Verify notification belongs to this user and mark as read
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE notification_id = ? 
                AND recipient_type = 'candidate' 
                AND recipient_id = ? 
                AND is_read = 0
            ");
            
            $stmt->execute([$notification_id, $seeker_id]);
            $affected = $stmt->rowCount();
            
            if ($affected > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Notification marked as read',
                    'affected_count' => $affected
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Notification not found or already read'
                ]);
            }
            break;
            
        case 'mark_multiple_read':
            if (empty($notification_ids) || !is_array($notification_ids)) {
                echo json_encode(['success' => false, 'message' => 'Notification IDs array required']);
                exit;
            }
            
            // Create placeholders for IN clause
            $placeholders = str_repeat('?,', count($notification_ids) - 1) . '?';
            $params = array_merge($notification_ids, [$seeker_id]);
            
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE notification_id IN ($placeholders) 
                AND recipient_type = 'candidate' 
                AND recipient_id = ? 
                AND is_read = 0
            ");
            
            $stmt->execute($params);
            $affected = $stmt->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "{$affected} notifications marked as read",
                'affected_count' => $affected
            ]);
            break;
            
        case 'mark_all_read':
            // Mark all unread notifications as read for this user
            $stmt = $conn->prepare("
                UPDATE notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE recipient_type = 'candidate' 
                AND recipient_id = ? 
                AND is_read = 0
            ");
            
            $stmt->execute([$seeker_id]);
            $affected = $stmt->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "All {$affected} notifications marked as read",
                'affected_count' => $affected
            ]);
            break;
            
        case 'mark_type_read':
            $type_filter = $input['type_filter'] ?? '';
            
            if (!$type_filter) {
                echo json_encode(['success' => false, 'message' => 'Type filter required']);
                exit;
            }
            
            // Map frontend types to database types
            $typeMapping = [
                'application' => ['new_application', 'application_status', 'interview_scheduled', 'interview_reminder', 'interview_feedback'],
                'job' => ['job_posted', 'deadline_reminder', 'job_expiring', 'job_performance'],
                'system' => ['system_update', 'profile_completion', 'subscription_renewal']
            ];
            
            if (!isset($typeMapping[$type_filter])) {
                echo json_encode(['success' => false, 'message' => 'Invalid type filter']);
                exit;
            }
            
            $dbTypes = $typeMapping[$type_filter];
            $typePlaceholders = str_repeat('?,', count($dbTypes) - 1) . '?';
            $params = array_merge($dbTypes, [$seeker_id]);
            
            $stmt = $conn->prepare("
                UPDATE notifications n
                INNER JOIN notification_types nt ON n.type_id = nt.type_id
                SET n.is_read = 1, n.read_at = NOW() 
                WHERE nt.type_name IN ($typePlaceholders)
                AND n.recipient_type = 'candidate' 
                AND n.recipient_id = ? 
                AND n.is_read = 0
            ");
            
            $stmt->execute($params);
            $affected = $stmt->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "{$affected} {$type_filter} notifications marked as read",
                'affected_count' => $affected
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Mark notification read error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating notification status']);
}
?>