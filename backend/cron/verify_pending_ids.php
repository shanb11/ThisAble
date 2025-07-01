<?php
/*
 * verify_pending_ids.php - Batch verification for PWD IDs
 * 
 * This script should be run as a cron job (scheduled task)
 * Example cron entry (run daily at 2 AM):
 * 0 2 * * * php /path/to/THISABLE/backend/cron/verify_pending_ids.php
 */

// Set execution time to unlimited for large batches
set_time_limit(0);

// Get the base directory
$baseDir = dirname(dirname(__DIR__));

// Include necessary files
require_once($baseDir . '/backend/db.php');
require_once($baseDir . '/backend/candidate/pwd_verification.php');

// Create a log file
$logFile = $baseDir . '/logs/pwd_verification_' . date('Y-m-d') . '.log';

// Ensure log directory exists
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Output to console if running from command line
    if (php_sapi_name() === 'cli') {
        echo $logMessage;
    }
    
    // Write to log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

logMessage("Starting PWD ID verification process");

try {
    // Get all unverified PWD IDs
    $stmt = $conn->prepare("SELECT pwd_id, seeker_id, pwd_id_number, verification_attempts 
                           FROM pwd_ids 
                           WHERE is_verified = 0 
                           AND verification_attempts < 3
                           LIMIT 50"); // Process in batches of 50
    $stmt->execute();
    $pendingIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPending = count($pendingIds);
    logMessage("Found $totalPending pending PWD IDs to verify");
    
    $verifiedCount = 0;
    $failedCount = 0;
    
    foreach ($pendingIds as $pendingId) {
        logMessage("Processing PWD ID: " . $pendingId['pwd_id_number'] . " (Attempt #" . ($pendingId['verification_attempts'] + 1) . ")");
        
        // Attempt verification
        $verification = verifyPwdId($pendingId['pwd_id_number']);
        
        // Update attempt count
        $attempts = $pendingId['verification_attempts'] + 1;
        
        if ($verification['success'] && $verification['verified']) {
            // Update as verified
            $stmt = $conn->prepare("UPDATE pwd_ids 
                                   SET is_verified = 1, 
                                       verification_date = NOW(), 
                                       verification_attempts = :attempts 
                                   WHERE pwd_id = :pwd_id");
            $stmt->bindParam(':attempts', $attempts);
            $stmt->bindParam(':pwd_id', $pendingId['pwd_id']);
            $stmt->execute();
            
            logMessage("  ✓ Verified successfully");
            $verifiedCount++;
            
            // Get user email to send notification
            $stmt = $conn->prepare("SELECT js.first_name, ua.email 
                                   FROM job_seekers js 
                                   JOIN user_accounts ua ON js.seeker_id = ua.seeker_id 
                                   WHERE js.seeker_id = :seeker_id");
            $stmt->bindParam(':seeker_id', $pendingId['seeker_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Send email notification (commented out - implement based on your email system)
                /*
                $to = $user['email'];
                $subject = "PWD ID Verification - ThisAble";
                $message = "Hello " . $user['first_name'] . ",\n\n";
                $message .= "We're pleased to inform you that your PWD ID has been verified successfully.\n";
                $message .= "Your ThisAble account is now fully active.\n\n";
                $message .= "Thank you for using ThisAble!\n";
                $message .= "The ThisAble Team";
                $headers = "From: noreply@thisable.com";
                
                mail($to, $subject, $message, $headers);
                */
                
                logMessage("  ✓ Notification prepared for: " . $user['email']);
            }
        } else {
            // Update attempts only
            $stmt = $conn->prepare("UPDATE pwd_ids 
                                   SET verification_attempts = :attempts 
                                   WHERE pwd_id = :pwd_id");
            $stmt->bindParam(':attempts', $attempts);
            $stmt->bindParam(':pwd_id', $pendingId['pwd_id']);
            $stmt->execute();
            
            logMessage("  ✗ Verification failed: " . $verification['message']);
            $failedCount++;
            
            // If this was the 3rd failed attempt, notify admin
            if ($attempts >= 3) {
                // Send admin notification (commented out - implement based on your email system)
                /*
                $to = "admin@thisable.com";
                $subject = "PWD ID Manual Verification Required";
                $message = "A PWD ID requires manual verification:\n\n";
                $message .= "PWD ID: " . $pendingId['pwd_id_number'] . "\n";
                $message .= "Seeker ID: " . $pendingId['seeker_id'] . "\n";
                $message .= "Reason: Maximum verification attempts reached\n";
                $headers = "From: system@thisable.com";
                
                mail($to, $subject, $message, $headers);
                */
                
                logMessage("  ! Maximum attempts reached - admin notification prepared");
            }
        }
        
        // Add a delay to avoid overwhelming the DOH server
        sleep(2);
    }
    
    logMessage("Verification process completed");
    logMessage("Summary: Total: $totalPending, Verified: $verifiedCount, Failed: $failedCount");
    
} catch (PDOException $e) {
    logMessage("DATABASE ERROR: " . $e->getMessage());
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
}
?>