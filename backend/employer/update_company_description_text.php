<?php
/**
 * Update Company Description Only (for Settings page)
 * This is a simplified version that only updates text, no file upload
 */

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['employer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$employer_id = $_SESSION['employer_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get company description (optional)
$company_description = trim($input['company_description'] ?? '');
$why_join_us = trim($input['why_join_us'] ?? '');

try {
    // Update only if description is provided
    if (!empty($company_description)) {
        $stmt = $conn->prepare("
            UPDATE employers 
            SET company_description = ?,
                updated_at = NOW()
            WHERE employer_id = ?
        ");
        
        $stmt->execute([$company_description, $employer_id]);
    }
    
    // Update why_join_us if provided
    if (!empty($why_join_us)) {
        $stmt = $conn->prepare("
            UPDATE employers 
            SET why_join_us = ?,
                updated_at = NOW()
            WHERE employer_id = ?
        ");
        
        $stmt->execute([$why_join_us, $employer_id]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Company description updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Error updating company description: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update company description'
    ]);
}
?>