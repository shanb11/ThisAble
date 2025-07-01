<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Get selected skills
$skills = isset($_POST['skills']) ? $_POST['skills'] : [];

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete existing skills
    $delete_stmt = $conn->prepare("DELETE FROM seeker_skills WHERE seeker_id = :seeker_id");
    $delete_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $delete_stmt->execute();
    
    // Insert new skills
    if (!empty($skills)) {
        $insert_stmt = $conn->prepare("INSERT INTO seeker_skills (seeker_id, skill_id) VALUES (:seeker_id, :skill_id)");
        $insert_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        
        foreach ($skills as $skill_id) {
            $insert_stmt->bindParam(':skill_id', $skill_id, PDO::PARAM_INT);
            $insert_stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Skills updated successfully']);
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>