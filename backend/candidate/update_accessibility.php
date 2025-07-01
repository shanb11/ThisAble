<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Get form data
$disability_type = $_POST['disability_type'] ?? '';
$accommodation_list = isset($_POST['accommodations']) ? $_POST['accommodations'] : [];
$no_accommodations = isset($_POST['no_accommodations']) ? 1 : 0;
$additional_notes = $_POST['additional_notes'] ?? '';

// Encode accommodation list as JSON
$accommodation_json = json_encode($accommodation_list);

try {
    // Check if user accommodations exist
    $check_query = "SELECT accommodation_id FROM workplace_accommodations WHERE seeker_id = :seeker_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // Update existing accommodations
        $query = "UPDATE workplace_accommodations SET 
                  disability_type = :disability_type,
                  accommodation_list = :accommodation_list,
                  no_accommodations_needed = :no_accommodations
                  WHERE seeker_id = :seeker_id";
    } else {
        // Insert new accommodations
        $query = "INSERT INTO workplace_accommodations (seeker_id, disability_type, accommodation_list, no_accommodations_needed) 
                  VALUES (:seeker_id, :disability_type, :accommodation_list, :no_accommodations)";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $stmt->bindParam(':disability_type', $disability_type);
    $stmt->bindParam(':accommodation_list', $accommodation_json);
    $stmt->bindParam(':no_accommodations', $no_accommodations, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Accessibility needs updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update accessibility needs']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>