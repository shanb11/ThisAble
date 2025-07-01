<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$priority = $_POST['priority'] ?? 'normal';
$include_account_info = isset($_POST['include_account_info']) ? 1 : 0;

// Validation
if (empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit;
}

try {
    // Create support_requests table if not exists
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS support_requests (
            request_id INT NOT NULL AUTO_INCREMENT,
            seeker_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            priority ENUM('normal', 'urgent') DEFAULT 'normal',
            status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
            include_account_info TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (request_id),
            FOREIGN KEY (seeker_id) REFERENCES job_seekers(seeker_id) ON DELETE CASCADE
        )
    ";
    $conn->exec($create_table_sql);
    
    // Insert support request
    $stmt = $conn->prepare("
        INSERT INTO support_requests (seeker_id, subject, message, priority, include_account_info)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$seeker_id, $subject, $message, $priority, $include_account_info]);
    
    $request_id = $conn->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Support request submitted successfully',
        'request_id' => $request_id
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your request: ' . $e->getMessage()]);
}
?>