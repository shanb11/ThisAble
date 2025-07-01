<?php
// File: backend/candidate/test_update_personal.php
session_start();
require_once '../db.php';

// Create a log file for this test
$log_file = __DIR__ . '/update_test.txt';
file_put_contents($log_file, "Test started: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($log_file, "Session: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    file_put_contents($log_file, "Not logged in\n", FILE_APPEND);
    echo "Not logged in. Please log in first.";
    exit();
}

$seeker_id = $_SESSION['seeker_id'];
file_put_contents($log_file, "Seeker ID: $seeker_id\n", FILE_APPEND);

// Set test data - we'll directly attempt to update the database
$test_first_name = "Test_" . rand(1000, 9999);
$test_last_name = "User_" . date('His');
$test_bio = "This is a test bio update at " . date('Y-m-d H:i:s');

file_put_contents($log_file, "Test data:\n", FILE_APPEND);
file_put_contents($log_file, "First name: $test_first_name\n", FILE_APPEND);
file_put_contents($log_file, "Last name: $test_last_name\n", FILE_APPEND);
file_put_contents($log_file, "Bio: $test_bio\n", FILE_APPEND);

try {
    // Enable exception mode
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    file_put_contents($log_file, "Transaction started\n", FILE_APPEND);
    
    // Update job_seekers table with a simple update
    $query = "UPDATE job_seekers SET 
              first_name = :first_name,
              last_name = :last_name
              WHERE seeker_id = :seeker_id";
    
    file_put_contents($log_file, "SQL Query: $query\n", FILE_APPEND);
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':first_name', $test_first_name);
    $stmt->bindParam(':last_name', $test_last_name);
    $stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    
    $stmt->execute();
    file_put_contents($log_file, "Query executed. Rows affected: " . $stmt->rowCount() . "\n", FILE_APPEND);
    
    // Now test updating profile_details
    // First check if a record exists
    $check_query = "SELECT profile_id FROM profile_details WHERE seeker_id = :seeker_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing record
        $bio_query = "UPDATE profile_details SET bio = :bio WHERE seeker_id = :seeker_id";
        file_put_contents($log_file, "Profile exists. Bio query: $bio_query\n", FILE_APPEND);
    } else {
        // Insert new record
        $bio_query = "INSERT INTO profile_details (seeker_id, bio, created_at) VALUES (:seeker_id, :bio, NOW())";
        file_put_contents($log_file, "Profile does not exist. Bio query: $bio_query\n", FILE_APPEND);
    }
    
    $bio_stmt = $conn->prepare($bio_query);
    $bio_stmt->bindParam(':bio', $test_bio);
    $bio_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $bio_stmt->execute();
    
    file_put_contents($log_file, "Bio query executed. Rows affected: " . $bio_stmt->rowCount() . "\n", FILE_APPEND);
    
    // Commit the transaction
    $conn->commit();
    file_put_contents($log_file, "Transaction committed\n", FILE_APPEND);
    
    echo "<h1>Update Test Results</h1>";
    echo "<p>Test completed successfully!</p>";
    echo "<p>Updated name to: $test_first_name $test_last_name</p>";
    echo "<p>Updated bio to: $test_bio</p>";
    echo "<p>Log file written to: $log_file</p>";
    echo "<p><a href='../../frontend/candidate/profile.php'>Return to Profile</a></p>";
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
        file_put_contents($log_file, "Transaction rolled back\n", FILE_APPEND);
    }
    
    file_put_contents($log_file, "PDO Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($log_file, "SQL State: " . $e->getCode() . "\n", FILE_APPEND);
    file_put_contents($log_file, "Error trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    echo "<h1>Update Test Error</h1>";
    echo "<p>An error occurred during the test:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<p>See log file for details: $log_file</p>";
}

file_put_contents($log_file, "Test completed\n\n", FILE_APPEND);
?>