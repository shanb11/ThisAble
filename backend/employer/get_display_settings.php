<?php
/**
 * Get Display Settings
 * Fetches current employer display preferences
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get display settings
    $sql = "
        SELECT 
            theme,
            font_size,
            color_scheme,
            high_contrast,
            reduce_motion,
            screen_reader_support,
            default_view
        FROM employer_display_settings
        WHERE employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // Return default settings if none exist
        $settings = [
            'theme' => 'light',
            'font_size' => 'medium',
            'color_scheme' => 'default',
            'high_contrast' => false,
            'reduce_motion' => false,
            'screen_reader_support' => true,
            'default_view' => 'dashboard'
        ];
    }
    
    // Convert boolean values
    foreach (['high_contrast', 'reduce_motion', 'screen_reader_support'] as $field) {
        $settings[$field] = (bool)($settings[$field] ?? false);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $settings
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_display_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_display_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>