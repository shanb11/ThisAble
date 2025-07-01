<?php
/**
 * Update Hiring Preferences
 * Handles PWD hiring settings, disability types, and workplace accommodations
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
    $open_to_pwd = isset($input['open_to_pwd']) ? (bool)$input['open_to_pwd'] : false;
    $disability_types = $input['disability_types'] ?? [];
    $accessibility_options = $input['accessibility_options'] ?? [];
    $additional_accommodations = trim($input['additional_accommodations'] ?? '');
    
    // Validation array
    $errors = [];
    
    // Validate disability types array
    if (!is_array($disability_types)) {
        $errors[] = 'Invalid disability types data';
    } else {
        $valid_disability_types = ['visual', 'hearing', 'physical', 'cognitive'];
        foreach ($disability_types as $type) {
            if (!in_array($type, $valid_disability_types)) {
                $errors[] = 'Invalid disability type: ' . $type;
                break;
            }
        }
    }
    
    // Validate accessibility options array
    if (!is_array($accessibility_options)) {
        $errors[] = 'Invalid accessibility options data';
    } else {
        $valid_accessibility_options = [
            'wheelchair', 'remote', 'flexible', 'sign', 'assistive', 'assistant'
        ];
        foreach ($accessibility_options as $option) {
            if (!in_array($option, $valid_accessibility_options)) {
                $errors[] = 'Invalid accessibility option: ' . $option;
                break;
            }
        }
    }
    
    // Validate additional accommodations length
    if (strlen($additional_accommodations) > 1000) {
        $errors[] = 'Additional accommodations must be less than 1000 characters';
    }
    
    // Return validation errors
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
        // Prepare JSON data for storage
        $disability_types_json = !empty($disability_types) ? json_encode($disability_types) : null;
        $accessibility_options_json = !empty($accessibility_options) ? json_encode($accessibility_options) : null;
        
        // Insert or update hiring preferences
        $upsert_sql = "
            INSERT INTO employer_hiring_preferences 
            (employer_id, open_to_pwd, disability_types, workplace_accommodations, additional_accommodations, created_at, updated_at)
            VALUES (:employer_id, :open_to_pwd, :disability_types, :workplace_accommodations, :additional_accommodations, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            open_to_pwd = :open_to_pwd,
            disability_types = :disability_types,
            workplace_accommodations = :workplace_accommodations,
            additional_accommodations = :additional_accommodations,
            updated_at = NOW()
        ";
        
        $upsert_stmt = $conn->prepare($upsert_sql);
        $upsert_result = $upsert_stmt->execute([
            'employer_id' => $employer_id,
            'open_to_pwd' => $open_to_pwd ? 1 : 0,
            'disability_types' => $disability_types_json,
            'workplace_accommodations' => $accessibility_options_json,
            'additional_accommodations' => $additional_accommodations ?: null
        ]);
        
        if (!$upsert_result) {
            throw new Exception('Failed to update hiring preferences');
        }
        
        // Update setup progress for preferences completion
        $progress_sql = "
            INSERT INTO employer_setup_progress 
            (employer_id, preferences_complete, updated_at) 
            VALUES (:employer_id, 1, NOW())
            ON DUPLICATE KEY UPDATE 
            preferences_complete = 1, 
            updated_at = NOW()
        ";
        
        $progress_stmt = $conn->prepare($progress_sql);
        $progress_stmt->execute(['employer_id' => $employer_id]);
        
        // Calculate new completion percentage
        $completion_percentage = calculateCompletionPercentage($conn, $employer_id);
        
        // Update completion percentage
        $update_progress_sql = "
            UPDATE employer_setup_progress 
            SET completion_percentage = :percentage,
                setup_complete = :setup_complete,
                updated_at = NOW()
            WHERE employer_id = :employer_id
        ";
        
        $update_progress_stmt = $conn->prepare($update_progress_sql);
        $update_progress_stmt->execute([
            'percentage' => $completion_percentage,
            'setup_complete' => ($completion_percentage >= 100) ? 1 : 0,
            'employer_id' => $employer_id
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Hiring preferences updated successfully',
            'data' => [
                'open_to_pwd' => $open_to_pwd,
                'disability_types' => $disability_types,
                'accessibility_options' => $accessibility_options,
                'additional_accommodations' => $additional_accommodations,
                'completion_percentage' => $completion_percentage
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_hiring_preferences.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_hiring_preferences.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating hiring preferences. Please try again.'
    ]);
}

/**
 * Calculate completion percentage based on profile sections
 */
function calculateCompletionPercentage($conn, $employer_id) {
    try {
        // Get all relevant data for calculation
        $sql = "
            SELECT 
                e.company_name,
                e.industry,
                e.company_address,
                e.company_description,
                e.why_join_us,
                e.company_logo_path,
                ehp.open_to_pwd,
                esl.website_url,
                COUNT(ec.contact_id) as contact_count
            FROM employers e
            LEFT JOIN employer_hiring_preferences ehp ON e.employer_id = ehp.employer_id
            LEFT JOIN employer_social_links esl ON e.employer_id = esl.employer_id
            LEFT JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
            WHERE e.employer_id = :employer_id
            GROUP BY e.employer_id
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['employer_id' => $employer_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return 0;
        }
        
        $completed_sections = 0;
        $total_sections = 5; // 5 sections worth 20% each
        
        // 1. Basic Info (20%) - Company name, industry, address
        if (!empty($data['company_name']) && 
            !empty($data['industry']) && 
            !empty($data['company_address'])) {
            $completed_sections++;
        }
        
        // 2. Company Description (20%) - Description and why join us
        if (!empty($data['company_description']) && 
            strlen($data['company_description']) > 50 &&
            !empty($data['why_join_us']) && 
            strlen($data['why_join_us']) > 30) {
            $completed_sections++;
        }
        
        // 3. Hiring Preferences (20%) - PWD settings
        if ($data['open_to_pwd'] !== null) {
            $completed_sections++;
        }
        
        // 4. Social Links (20%) - At least website
        if (!empty($data['website_url'])) {
            $completed_sections++;
        }
        
        // 5. Logo (20%) - Logo uploaded
        if (!empty($data['company_logo_path'])) {
            $completed_sections++;
        }
        
        return round(($completed_sections / $total_sections) * 100);
        
    } catch (Exception $e) {
        error_log("Error calculating completion percentage: " . $e->getMessage());
        return 0;
    }
}
?>