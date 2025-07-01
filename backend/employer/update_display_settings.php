<?php
/**
 * Update Display Settings
 * Handles employer display and UI preferences
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON data'
        ]);
        exit;
    }
    
    // Extract and validate input data
    $theme = $input['theme'] ?? 'light';
    $font_size = $input['font_size'] ?? 'medium';
    $color_scheme = $input['color_scheme'] ?? 'default';
    $high_contrast = isset($input['high_contrast']) ? (bool)$input['high_contrast'] : false;
    $reduce_motion = isset($input['reduce_motion']) ? (bool)$input['reduce_motion'] : false;
    $screen_reader_support = isset($input['screen_reader_support']) ? (bool)$input['screen_reader_support'] : true;
    $default_view = $input['default_view'] ?? 'dashboard';
    
    // Validation
    $errors = [];
    
    // Validate theme
    $valid_themes = ['light', 'dark', 'system'];
    if (!in_array($theme, $valid_themes)) {
        $errors[] = 'Invalid theme selection';
    }
    
    // Validate font size
    $valid_font_sizes = ['small', 'medium', 'large'];
    if (!in_array($font_size, $valid_font_sizes)) {
        $errors[] = 'Invalid font size selection';
    }
    
    // Validate color scheme
    $valid_color_schemes = ['default', 'blue', 'purple', 'red', 'custom'];
    if (!in_array($color_scheme, $valid_color_schemes)) {
        $errors[] = 'Invalid color scheme selection';
    }
    
    // Validate default view
    $valid_views = ['dashboard', 'job-listings', 'applicants', 'company-profile'];
    if (!in_array($default_view, $valid_views)) {
        $errors[] = 'Invalid default view selection';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $errors[0],
            'errors' => $errors
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Insert or update display settings
        $upsert_sql = "
            INSERT INTO employer_display_settings 
            (employer_id, theme, font_size, color_scheme, high_contrast,
             reduce_motion, screen_reader_support, default_view, created_at, updated_at)
            VALUES (:employer_id, :theme, :font_size, :color_scheme, :high_contrast,
                    :reduce_motion, :screen_reader_support, :default_view, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            theme = :theme,
            font_size = :font_size,
            color_scheme = :color_scheme,
            high_contrast = :high_contrast,
            reduce_motion = :reduce_motion,
            screen_reader_support = :screen_reader_support,
            default_view = :default_view,
            updated_at = NOW()
        ";
        
        $upsert_stmt = $conn->prepare($upsert_sql);
        $upsert_result = $upsert_stmt->execute([
            'employer_id' => $employer_id,
            'theme' => $theme,
            'font_size' => $font_size,
            'color_scheme' => $color_scheme,
            'high_contrast' => $high_contrast ? 1 : 0,
            'reduce_motion' => $reduce_motion ? 1 : 0,
            'screen_reader_support' => $screen_reader_support ? 1 : 0,
            'default_view' => $default_view
        ]);
        
        if (!$upsert_result) {
            throw new Exception('Failed to update display settings');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Display settings updated successfully',
            'data' => [
                'theme' => $theme,
                'font_size' => $font_size,
                'color_scheme' => $color_scheme,
                'high_contrast' => $high_contrast,
                'reduce_motion' => $reduce_motion,
                'screen_reader_support' => $screen_reader_support,
                'default_view' => $default_view
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_display_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_display_settings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating display settings. Please try again.'
    ]);
}
?>