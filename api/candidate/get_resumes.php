<?php
/**
 * Get Resumes API
 * Returns list of user's uploaded resumes
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_resumes.php
 */

require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    
    error_log("Get Resumes API: seeker_id=$seekerId");

    $conn = ApiDatabase::getConnection();
    
    // Get user's resumes
    $stmt = $conn->prepare("
        SELECT 
            resume_id,
            file_name,
            file_path,
            file_size,
            file_type,
            upload_date,
            is_current
        FROM resumes
        WHERE seeker_id = ?
        ORDER BY is_current DESC, upload_date DESC
    ");
    $stmt->execute([$seekerId]);
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format resumes
    foreach ($resumes as &$resume) {
        $resume['upload_date'] = date('F j, Y', strtotime($resume['upload_date']));
        $resume['file_size_formatted'] = number_format($resume['file_size'] / 1024, 2) . ' KB';
        $resume['is_current'] = (bool)$resume['is_current'];
    }
    
    ApiResponse::success([
        'resumes' => $resumes,
        'count' => count($resumes)
    ], "Resumes retrieved successfully");
    
} catch (Exception $e) {
    error_log("Get Resumes Error: " . $e->getMessage());
    ApiResponse::serverError("Failed to load resumes");
}
?>