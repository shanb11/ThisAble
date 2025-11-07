<?php
/**
 * Permanent Account Deletion Script
 * Run this script daily via cron job
 * 
 * Path: /backend/cron/delete_closed_accounts.php
 * 
 * CRON SETUP INSTRUCTIONS:
 * Add this to your crontab to run daily at 2 AM:
 * 0 2 * * * /usr/bin/php /path/to/your/backend/cron/delete_closed_accounts.php
 * 
 * Or use cPanel Cron Jobs interface:
 * Command: /usr/bin/php /home/yourusername/public_html/backend/cron/delete_closed_accounts.php
 * Schedule: Daily at 2:00 AM
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line');
}

require_once dirname(__DIR__) . '/db.php';

echo "========================================\n";
echo "Permanent Account Deletion Script\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    // Find accounts closed more than 30 days ago
    $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    echo "Finding accounts closed before: $cutoff_date\n\n";
    
    // ============================================
    // DELETE CANDIDATE ACCOUNTS
    // ============================================
    echo "Processing Candidate Accounts...\n";
    
    $stmt = $conn->prepare("
        SELECT seeker_id, first_name, last_name, closed_at 
        FROM job_seekers 
        WHERE account_status = 'closed' 
        AND closed_at <= :cutoff_date
    ");
    $stmt->execute([':cutoff_date' => $cutoff_date]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $candidate_count = count($candidates);
    echo "Found $candidate_count candidate account(s) to delete\n";
    
    foreach ($candidates as $candidate) {
        $conn->beginTransaction();
        
        try {
            echo "  - Deleting candidate ID: {$candidate['seeker_id']} ";
            echo "({$candidate['first_name']} {$candidate['last_name']}) ";
            echo "- Closed: {$candidate['closed_at']}\n";
            
            // Delete from job_seekers - CASCADE will handle related tables
            $stmt = $conn->prepare("DELETE FROM job_seekers WHERE seeker_id = ?");
            $stmt->execute([$candidate['seeker_id']]);
            
            $conn->commit();
            echo "    ✓ Deleted successfully\n";
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "    ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // ============================================
    // DELETE EMPLOYER ACCOUNTS
    // ============================================
    echo "\nProcessing Employer Accounts...\n";
    
    $stmt = $conn->prepare("
        SELECT employer_id, company_name, closed_at 
        FROM employers 
        WHERE account_status = 'closed' 
        AND closed_at <= :cutoff_date
    ");
    $stmt->execute([':cutoff_date' => $cutoff_date]);
    $employers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $employer_count = count($employers);
    echo "Found $employer_count employer account(s) to delete\n";
    
    foreach ($employers as $employer) {
        $conn->beginTransaction();
        
        try {
            echo "  - Deleting employer ID: {$employer['employer_id']} ";
            echo "({$employer['company_name']}) ";
            echo "- Closed: {$employer['closed_at']}\n";
            
            // Delete from employers - CASCADE will handle related tables
            $stmt = $conn->prepare("DELETE FROM employers WHERE employer_id = ?");
            $stmt->execute([$employer['employer_id']]);
            
            $conn->commit();
            echo "    ✓ Deleted successfully\n";
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "    ✗ Error: " . $e->getMessage() . "\n";
        }
    }
    
    // ============================================
    // SUMMARY
    // ============================================
    echo "\n========================================\n";
    echo "SUMMARY\n";
    echo "========================================\n";
    echo "Candidates deleted: $candidate_count\n";
    echo "Employers deleted: $employer_count\n";
    echo "Total accounts deleted: " . ($candidate_count + $employer_count) . "\n";
    echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
    echo "========================================\n";
    
    // Log to file (optional)
    $log_dir = dirname(__DIR__) . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/account_deletions_' . date('Y-m') . '.log';
    $log_entry = sprintf(
        "[%s] Deleted %d candidate(s) and %d employer(s)\n",
        date('Y-m-d H:i:s'),
        $candidate_count,
        $employer_count
    );
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
} catch (PDOException $e) {
    echo "\n✗ DATABASE ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
?>