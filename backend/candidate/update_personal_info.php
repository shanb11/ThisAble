<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Get form data
$first_name = $_POST['first_name'] ?? '';
$middle_name = $_POST['middle_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$suffix = $_POST['suffix'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';
$disability_id = $_POST['disability_id'] ?? '';
$city = $_POST['city'] ?? '';
$province = $_POST['province'] ?? '';
$bio = $_POST['bio'] ?? '';

// Validate required fields
if (empty($first_name) || empty($last_name) || empty($contact_number)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Update job_seekers table
    $stmt = $conn->prepare("UPDATE job_seekers SET first_name = :first_name, middle_name = :middle_name, last_name = :last_name, suffix = :suffix, contact_number = :contact_number, city = :city, province = :province, disability_id = :disability_id WHERE seeker_id = :seeker_id");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':middle_name', $middle_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':suffix', $suffix);
    $stmt->bindParam(':contact_number', $contact_number);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':disability_id', $disability_id);
    $stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Handle bio update in profile_details table
    if (!empty($bio)) {
        // Check if profile_details record exists
        $check_stmt = $conn->prepare("SELECT profile_id FROM profile_details WHERE seeker_id = :seeker_id");
        $check_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing record
            $bio_stmt = $conn->prepare("UPDATE profile_details SET bio = :bio, updated_at = NOW() WHERE seeker_id = :seeker_id");
        } else {
            // Insert new record
            $bio_stmt = $conn->prepare("INSERT INTO profile_details (seeker_id, bio, created_at, updated_at) VALUES (:seeker_id, :bio, NOW(), NOW())");
        }
        
        $bio_stmt->bindParam(':bio', $bio);
        $bio_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $bio_stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Personal information updated successfully']);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>