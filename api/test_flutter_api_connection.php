<?php
/**
 * Flutter API Database Connection Test
 * This tests if your /api folder can connect to Supabase and find the user
 */

// Include the same database config that google.php uses
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$results = [];

try {
    // Test 1: Check connection and database type
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $results['connection'] = '✅ Connected';
    $results['database_version'] = $version['version'];
    $results['database_type'] = strpos($version['version'], 'PostgreSQL') !== false ? 'PostgreSQL (Supabase)' : 'MySQL (Localhost)';
    
    // Test 2: Count total users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM user_accounts");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    $results['total_users_in_db'] = $count['total'];
    
    // Test 3: Check if baccayshan@gmail.com exists (exact query from google.php)
    $stmt = $conn->prepare("SELECT account_id, seeker_id FROM user_accounts WHERE LOWER(email) = LOWER(?)");
    $stmt->execute(['baccayshan@gmail.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $results['baccayshan_exists'] = $user !== false;
    $results['baccayshan_data'] = $user ?: 'NOT FOUND';
    
    // Test 4: List all emails in database
    $stmt = $conn->query("SELECT email, google_account FROM user_accounts ORDER BY account_id");
    $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results['all_users_in_db'] = $emails;
    
    // Test 5: Get full user data if exists
    if ($user) {
        $seekerId = $user['seeker_id'];
        $userStmt = $conn->prepare("
            SELECT 
                js.seeker_id, js.first_name, js.middle_name, js.last_name, 
                js.suffix, js.disability_id, js.contact_number, js.setup_complete,
                js.city, js.province,
                dt.disability_name,
                ua.email, ua.google_account
            FROM job_seekers js 
            LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
            LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
            WHERE js.seeker_id = ?
        ");
        $userStmt->execute([$seekerId]);
        $fullUser = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        $results['full_user_data'] = $fullUser;
    }
    
    $results['success'] = true;
    $results['verdict'] = $user !== false 
        ? '✅ USER FOUND! API should work correctly.' 
        : '❌ USER NOT FOUND! This is the problem - API cannot see the user.';
    
} catch(Exception $e) {
    $results['success'] = false;
    $results['error'] = $e->getMessage();
    $results['error_type'] = get_class($e);
    $results['verdict'] = '❌ CONNECTION FAILED! Check database configuration.';
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>