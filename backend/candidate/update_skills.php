<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

try {
    if (isset($_POST['skills']) && is_array($_POST['skills'])) {
        $skills = $_POST['skills'];
        
        // Start transaction
        $conn->beginTransaction();
        
        // Delete existing skills for this seeker
        $delete_query = "DELETE FROM seeker_skills WHERE seeker_id = :seeker_id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $delete_stmt->execute();
        
        // Insert new skills
        foreach ($skills as $skill_name) {
            $skill_name = trim($skill_name);
            if (empty($skill_name)) continue;
            
            // Check if skill exists in skills table
            $check_skill_query = "SELECT skill_id FROM skills WHERE skill_name = :skill_name LIMIT 1";
            $check_skill_stmt = $conn->prepare($check_skill_query);
            $check_skill_stmt->bindParam(':skill_name', $skill_name, PDO::PARAM_STR);
            $check_skill_stmt->execute();
            
            $skill_row = $check_skill_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($skill_row) {
                // Skill exists, use existing skill_id
                $skill_id = $skill_row['skill_id'];
            } else {
                // Skill doesn't exist, create new one
                // Default to category_id = 1 (or find appropriate category)
                $default_category_id = 1;
                
                // Try to find appropriate category based on skill name
                if (in_array(strtolower($skill_name), ['communication', 'leadership', 'teamwork', 'problem solving', 'time management', 'adaptability', 'critical thinking', 'creativity', 'customer service', 'attention to detail', 'organization', 'multitasking'])) {
                    $default_category_id = 2; // Soft skills
                } elseif (in_array(strtolower($skill_name), ['filipino', 'english', 'spanish', 'mandarin', 'japanese', 'korean', 'french', 'german', 'arabic', 'sign language'])) {
                    $default_category_id = 3; // Languages
                } else {
                    $default_category_id = 1; // Technical skills
                }
                
                $insert_skill_query = "INSERT INTO skills (skill_name, category_id) VALUES (:skill_name, :category_id)";
                $insert_skill_stmt = $conn->prepare($insert_skill_query);
                $insert_skill_stmt->bindParam(':skill_name', $skill_name, PDO::PARAM_STR);
                $insert_skill_stmt->bindParam(':category_id', $default_category_id, PDO::PARAM_INT);
                $insert_skill_stmt->execute();
                
                $skill_id = $conn->lastInsertId();
            }
            
            // Add skill to seeker_skills table
            $insert_seeker_skill_query = "INSERT INTO seeker_skills (seeker_id, skill_id) VALUES (:seeker_id, :skill_id)";
            $insert_seeker_skill_stmt = $conn->prepare($insert_seeker_skill_query);
            $insert_seeker_skill_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
            $insert_seeker_skill_stmt->bindParam(':skill_id', $skill_id, PDO::PARAM_INT);
            $insert_seeker_skill_stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Skills updated successfully!'
        ]);
        
    } else {
        // No skills provided, just delete all existing skills
        $delete_query = "DELETE FROM seeker_skills WHERE seeker_id = :seeker_id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $delete_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'All skills removed successfully!'
        ]);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Skills update error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error updating skills: ' . $e->getMessage()
    ]);
}
?>