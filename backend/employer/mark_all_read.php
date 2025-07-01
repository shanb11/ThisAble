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
    // Get JSON input (optional - for future filter support)
    $input = json_decode(file_get_contents('php://input'), true);
    $filter = isset($input['filter']) ? $input['filter'] : 'all';

    // Base SQL for marking all as read
    $sql = "UPDATE notifications n 
            JOIN notification_types nt ON n.type_id = nt.type_id
            SET n.is_read = 1, n.read_at = NOW() 
            WHERE n.recipient_type = 'employer' 
            AND n.recipient_id = ? 
            AND n.is_read = 0";

    $params = [$employer_id];

    // Add filter conditions if specified
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

    // Execute the update
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
        $affectedRows = $stmt->rowCount();
        
        // Get updated unread count
        $unreadSql = "SELECT COUNT(*) as unread_count 
                      FROM notifications 
                      WHERE recipient_type = 'employer' 
                      AND recipient_id = ? 
                      AND is_read = 0";
        $unreadStmt = $conn->prepare($unreadSql);
        $unreadStmt->execute([$employer_id]);
        $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

        $message = $affectedRows > 0 ? 
            "Marked $affectedRows notification(s) as read" : 
            "No unread notifications found";

        $response = [
            'success' => true,
            'message' => $message,
            'affected_rows' => $affectedRows,
            'unread_count' => $unreadCount,
            'filter_applied' => $filter
        ];

        // Log the action for audit purposes
        error_log("Employer $employer_id marked $affectedRows notifications as read (filter: $filter)");

    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to mark notifications as read',
            'affected_rows' => 0
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Database error in mark_all_read: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => 'Please check server logs for details'
    ]);
} catch (Exception $e) {
    error_log("General error in mark_all_read: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while marking notifications as read',
        'message' => $e->getMessage()
    ]);
}
?>