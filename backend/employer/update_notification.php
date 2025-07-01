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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$employer_id = $_SESSION['employer_id'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit();
    }

    $action = isset($input['action']) ? $input['action'] : '';
    $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : 0;

    if (empty($action) || $notification_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters: action and notification_id']);
        exit();
    }

    // Verify notification belongs to this employer
    $verifySql = "SELECT notification_id FROM notifications 
                  WHERE notification_id = ? 
                  AND recipient_type = 'employer' 
                  AND recipient_id = ?";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->execute([$notification_id, $employer_id]);
    
    if (!$verifyStmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Notification not found or access denied']);
        exit();
    }

    $response = ['success' => false, 'message' => ''];

    switch ($action) {
        case 'mark_read':
            $sql = "UPDATE notifications 
                    SET is_read = 1, read_at = NOW() 
                    WHERE notification_id = ? 
                    AND recipient_type = 'employer' 
                    AND recipient_id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$notification_id, $employer_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Notification marked as read';
            } else {
                $response['success'] = true; // Still success even if already read
                $response['message'] = 'Notification was already read';
            }
            break;

        case 'mark_unread':
            $sql = "UPDATE notifications 
                    SET is_read = 0, read_at = NULL 
                    WHERE notification_id = ? 
                    AND recipient_type = 'employer' 
                    AND recipient_id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$notification_id, $employer_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Notification marked as unread';
            } else {
                $response['success'] = true; // Still success even if already unread
                $response['message'] = 'Notification was already unread';
            }
            break;

        case 'delete':
            $sql = "DELETE FROM notifications 
                    WHERE notification_id = ? 
                    AND recipient_type = 'employer' 
                    AND recipient_id = ?";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([$notification_id, $employer_id]);
            
            if ($result && $stmt->rowCount() > 0) {
                $response['success'] = true;
                $response['message'] = 'Notification deleted successfully';
            } else {
                $response['message'] = 'Failed to delete notification or notification not found';
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action. Allowed actions: mark_read, mark_unread, delete']);
            exit();
    }

    // Get updated unread count
    $unreadSql = "SELECT COUNT(*) as unread_count 
                  FROM notifications 
                  WHERE recipient_type = 'employer' 
                  AND recipient_id = ? 
                  AND is_read = 0";
    $unreadStmt = $conn->prepare($unreadSql);
    $unreadStmt->execute([$employer_id]);
    $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    $response['unread_count'] = $unreadCount;
    $response['notification_id'] = $notification_id;
    $response['action'] = $action;

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in update_notification: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => 'Please check server logs for details'
    ]);
} catch (Exception $e) {
    error_log("General error in update_notification: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while updating notification',
        'message' => $e->getMessage()
    ]);
}
?>