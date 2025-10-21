<?php
/**
 * Test API with Supabase Connection
 */

// Use Supabase connection instead of regular db.php
require_once '../backend/db_supabase_test.php';

header('Content-Type: application/json');

try {
    // Test 1: Get job seekers
    $stmt = $conn_test->prepare("SELECT seeker_id, first_name, last_name, contact_number FROM job_seekers LIMIT 5");
    $stmt->execute();
    $seekers = $stmt->fetchAll();
    
    // Test 2: Get disability types
    $stmt = $conn_test->prepare("SELECT * FROM disability_types");
    $stmt->execute();
    $disabilities = $stmt->fetchAll();
    
    // Test 3: Test insert (we'll rollback)
    $conn_test->beginTransaction();
    $stmt = $conn_test->prepare("INSERT INTO connection_test (test_value) VALUES (?)");
    $stmt->execute(['Supabase test at ' . date('Y-m-d H:i:s')]);
    $conn_test->rollBack(); // Don't actually save
    
    echo json_encode([
        'success' => true,
        'message' => 'All tests passed!',
        'tests' => [
            'connection' => 'OK',
            'select' => 'OK',
            'insert' => 'OK'
        ],
        'data' => [
            'seekers_count' => count($seekers),
            'disabilities_count' => count($disabilities)
        ]
    ], JSON_PRETTY_PRINT);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
