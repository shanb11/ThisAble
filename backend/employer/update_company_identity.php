<?php
/**
 * Update Company Identity
 * Handles company name, industry, address, website, and size updates
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
    // Validate session and get employer ID using existing function
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
    $company_name = trim($input['company_name'] ?? '');
    $industry_id = $input['industry_id'] ?? '';
    $custom_industry = trim($input['custom_industry'] ?? '');
    $company_address = trim($input['company_address'] ?? '');
    $company_website = trim($input['company_website'] ?? '');
    $company_size = trim($input['company_size'] ?? '');
    
    // Validation array
    $errors = [];
    
    // Validate required fields
    if (empty($company_name)) {
        $errors[] = 'Company name is required';
    } elseif (strlen($company_name) > 255) {
        $errors[] = 'Company name must be less than 255 characters';
    }
    
    if (empty($industry_id)) {
        $errors[] = 'Please select an industry';
    }
    
    if ($industry_id === 'others' && empty($custom_industry)) {
        $errors[] = 'Please specify your industry';
    } elseif ($industry_id === 'others' && strlen($custom_industry) > 100) {
        $errors[] = 'Custom industry must be less than 100 characters';
    }
    
    // Validate website URL if provided
    if (!empty($company_website)) {
        if (!filter_var($company_website, FILTER_VALIDATE_URL)) {
            $errors[] = 'Please enter a valid website URL';
        }
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
    
    // Determine industry values for database
    $final_industry_id = null;
    $final_industry_name = '';
    
    if ($industry_id === 'others') {
        // Custom industry
        $final_industry_name = $custom_industry;
    } else {
        // Predefined industry - get industry name
        $industry_stmt = $conn->prepare("SELECT industry_name FROM industries WHERE industry_id = :industry_id");
        $industry_stmt->execute(['industry_id' => $industry_id]);
        $industry_result = $industry_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$industry_result) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid industry selected'
            ]);
            exit;
        }
        
        $final_industry_id = (int)$industry_id;
        $final_industry_name = $industry_result['industry_name'];
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Update employer record with new fields
        $update_sql = "
            UPDATE employers 
            SET company_name = :company_name,
                industry = :industry,
                industry_id = :industry_id,
                company_address = :company_address,
                company_website = :company_website,
                company_size = :company_size,
                updated_at = NOW()
            WHERE employer_id = :employer_id
        ";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_result = $update_stmt->execute([
            'company_name' => $company_name,
            'industry' => $final_industry_name,
            'industry_id' => $final_industry_id,
            'company_address' => $company_address,
            'company_website' => $company_website,
            'company_size' => $company_size,
            'employer_id' => $employer_id
        ]);
        
        if (!$update_result) {
            throw new Exception('Failed to update company information');
        }
        
        // Update or create setup progress
        $progress_sql = "
            INSERT INTO employer_setup_progress 
            (employer_id, basic_info_complete, updated_at) 
            VALUES (:employer_id, 1, NOW())
            ON DUPLICATE KEY UPDATE 
            basic_info_complete = 1, 
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
        
        // Update session data
        $_SESSION['company_name'] = $company_name;
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Company identity updated successfully',
            'data' => [
                'company_name' => $company_name,
                'industry' => $final_industry_name,
                'industry_id' => $final_industry_id,
                'company_address' => $company_address,
                'company_website' => $company_website,
                'company_size' => $company_size,
                'completion_percentage' => $completion_percentage
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_company_identity.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_company_identity.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating company identity. Please try again.'
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
                e.company_website,
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
        if (!empty($data['website_url']) || !empty($data['company_website'])) {
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