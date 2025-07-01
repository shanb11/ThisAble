<?php
// backend/employer/send_notification.php
// Advanced notification system with email integration

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $notification_type = $input['type'] ?? 'custom'; // status_update, interview_reminder, custom
    $recipient_ids = $input['recipient_ids'] ?? []; // Array of seeker IDs
    $title = $input['title'] ?? '';
    $message = $input['message'] ?? '';
    $send_email = $input['send_email'] ?? false;
    $related_application_id = $input['related_application_id'] ?? null;
    $related_interview_id = $input['related_interview_id'] ?? null;

    if (empty($recipient_ids) || empty($title) || empty($message)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Recipients, title, and message are required'
        ]);
        exit;
    }

    $conn->beginTransaction();
    
    $successful_notifications = 0;
    $failed_notifications = 0;
    $email_results = [];
    
    // Get notification type ID
    $type_map = [
        'status_update' => 3,
        'interview_reminder' => 7,
        'interview_scheduled' => 2,
        'custom' => 5
    ];
    $type_id = $type_map[$notification_type] ?? 5;
    
    foreach ($recipient_ids as $seeker_id) {
        try {
            // Get recipient details for email
            $recipient_sql = "SELECT 
                                js.first_name,
                                js.last_name,
                                ua.email,
                                js.contact_number
                              FROM job_seekers js
                              LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
                              WHERE js.seeker_id = :seeker_id";
            
            $recipient_stmt = $conn->prepare($recipient_sql);
            $recipient_stmt->bindValue(':seeker_id', $seeker_id);
            $recipient_stmt->execute();
            $recipient = $recipient_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$recipient) {
                $failed_notifications++;
                continue;
            }
            
            // Insert notification into database
            $notification_sql = "INSERT INTO notifications 
                               (recipient_type, recipient_id, type_id, title, message, 
                                related_application_id, related_interview_id, created_at)
                               VALUES ('candidate', :seeker_id, :type_id, :title, :message,
                                       :related_application_id, :related_interview_id, CURRENT_TIMESTAMP)";
            
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bindValue(':seeker_id', $seeker_id);
            $notification_stmt->bindValue(':type_id', $type_id);
            $notification_stmt->bindValue(':title', $title);
            $notification_stmt->bindValue(':message', $message);
            $notification_stmt->bindValue(':related_application_id', $related_application_id);
            $notification_stmt->bindValue(':related_interview_id', $related_interview_id);
            $notification_stmt->execute();
            
            $notification_id = $conn->lastInsertId();
            
            // Send email if requested and email exists
            if ($send_email && $recipient['email']) {
                $email_result = sendNotificationEmail(
                    $recipient['email'],
                    $recipient['first_name'] . ' ' . $recipient['last_name'],
                    $title,
                    $message,
                    $employer_data['company_name'],
                    $notification_type
                );
                
                $email_results[] = [
                    'recipient' => $recipient['first_name'] . ' ' . $recipient['last_name'],
                    'email' => $recipient['email'],
                    'sent' => $email_result['success'],
                    'error' => $email_result['error'] ?? null
                ];
                
                // Update notification with email status
                if ($email_result['success']) {
                    $update_email_sql = "UPDATE notifications 
                                        SET email_sent = 1, email_sent_at = CURRENT_TIMESTAMP 
                                        WHERE notification_id = :notification_id";
                    $update_stmt = $conn->prepare($update_email_sql);
                    $update_stmt->bindValue(':notification_id', $notification_id);
                    $update_stmt->execute();
                }
            }
            
            $successful_notifications++;
            
        } catch (Exception $e) {
            $failed_notifications++;
            error_log("Failed to send notification to seeker {$seeker_id}: " . $e->getMessage());
        }
    }
    
    // Log the activity
    logActivity("BULK_NOTIFICATION", "Sent notifications to {$successful_notifications} recipients");
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Notifications sent successfully",
        'data' => [
            'successful_notifications' => $successful_notifications,
            'failed_notifications' => $failed_notifications,
            'total_recipients' => count($recipient_ids),
            'email_results' => $email_results,
            'email_sent' => $send_email
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send notifications: ' . $e->getMessage()
    ]);
}

/**
 * Send notification email
 */
function sendNotificationEmail($email, $name, $title, $message, $company_name, $type) {
    try {
        // Email configuration (adjust for your email provider)
        $to = $email;
        $subject = "[{$company_name}] {$title}";
        
        // Create HTML email content
        $html_content = createEmailTemplate($name, $title, $message, $company_name, $type);
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $company_name . ' <noreply@thisable.com>',
            'Reply-To: noreply@thisable.com',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email using PHP mail() function
        // Note: In production, consider using PHPMailer, SendGrid, or similar service
        $sent = mail($to, $subject, $html_content, implode("\r\n", $headers));
        
        if ($sent) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Mail function failed'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Create professional email template
 */
function createEmailTemplate($name, $title, $message, $company_name, $type) {
    $icon_map = [
        'status_update' => '📋',
        'interview_reminder' => '📅',
        'interview_scheduled' => '🗓️',
        'custom' => '📢'
    ];
    
    $icon = $icon_map[$type] ?? '📢';
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { padding: 30px; }
            .message-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eee; }
            .cta-button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .accessibility-note { background: #e8f4fd; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . $icon . ' ' . htmlspecialchars($title) . '</h1>
                <p>Notification from ' . htmlspecialchars($company_name) . '</p>
            </div>
            
            <div class="content">
                <h2>Hello ' . htmlspecialchars($name) . ',</h2>
                
                <div class="message-box">
                    ' . nl2br(htmlspecialchars($message)) . '
                </div>
                
                <a href="http://localhost/thisable/frontend/candidate/dashboard.php" class="cta-button">
                    View in Dashboard
                </a>
                
                <div class="accessibility-note">
                    <strong>♿ Accessibility Notice:</strong> ThisAble is committed to inclusive hiring. 
                    If you need any accommodations during the application or interview process, 
                    please don\'t hesitate to contact us.
                </div>
                
                <p>Best regards,<br>
                <strong>' . htmlspecialchars($company_name) . ' Hiring Team</strong></p>
            </div>
            
            <div class="footer">
                <p><small>
                    This email was sent by ' . htmlspecialchars($company_name) . ' through ThisAble Job Portal.<br>
                    If you no longer wish to receive these notifications, you can update your preferences in your dashboard.
                </small></p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>