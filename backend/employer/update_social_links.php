<?php
/**
 * Update Social Media Links
 * Handles website and social media URL updates
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
    $website_url = trim($input['website_url'] ?? '');
    $facebook_url = trim($input['facebook_url'] ?? '');
    $linkedin_url = trim($input['linkedin_url'] ?? '');
    $twitter_url = trim($input['twitter_url'] ?? '');
    $instagram_url = trim($input['instagram_url'] ?? '');
    
    // Validation array
    $errors = [];
    
    // Validate URLs
    $url_fields = [
        'website_url' => $website_url,
        'facebook_url' => $facebook_url,
        'linkedin_url' => $linkedin_url,
        'twitter_url' => $twitter_url,
        'instagram_url' => $instagram_url
    ];
    
    foreach ($url_fields as $field_name => $url) {
        if (!empty($url)) {
            // Add protocol if missing
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
                $url_fields[$field_name] = $url;
            }
            
            // Validate URL format
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $field_display = str_replace('_url', '', $field_name);
                $field_display = ucfirst(str_replace('_', ' ', $field_display));
                $errors[] = "Please enter a valid {$field_display} URL";
            }
            
            // Check URL length
            if (strlen($url) > 255) {
                $field_display = str_replace('_url', '', $field_name);
                $field_display = ucfirst(str_replace('_', ' ', $field_display));
                $errors[] = "{$field_display} URL must be less than 255 characters";
            }
        }
    }
    
    // Validate specific platform URLs
    if (!empty($url_fields['facebook_url']) && !preg_match('/facebook\.com|fb\.com/i', $url_fields['facebook_url'])) {
        $errors[] = 'Facebook URL must be a valid Facebook link';
    }
    
    if (!empty($url_fields['linkedin_url']) && !preg_match('/linkedin\.com/i', $url_fields['linkedin_url'])) {
        $errors[] = 'LinkedIn URL must be a valid LinkedIn link';
    }
    
    if (!empty($url_fields['twitter_url']) && !preg_match('/twitter\.com|x\.com/i', $url_fields['twitter_url'])) {
        $errors[] = 'Twitter URL must be a valid Twitter/X link';
    }
    
    if (!empty($url_fields['instagram_url']) && !preg_match('/instagram\.com/i', $url_fields['instagram_url'])) {
        $errors[] = 'Instagram URL must be a valid Instagram link';
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
        // Insert or update social links
        $upsert_sql = "
            INSERT INTO employer_social_links 
            (employer_id, website_url, facebook_url, linkedin_url, twitter_url, instagram_url, created_at, updated_at)
            VALUES (:employer_id, :website_url, :facebook_url, :linkedin_url, :twitter_url, :instagram_url, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            website_url = :website_url,
            facebook_url = :facebook_url,
            linkedin_url = :linkedin_url,
            twitter_url = :twitter_url,
            instagram_url = :instagram_url,
            updated_at = NOW()
        ";
        
        $upsert_stmt = $conn->prepare($upsert_sql);
        $upsert_result = $upsert_stmt->execute([
            'employer_id' => $employer_id,
            'website_url' => $url_fields['website_url'] ?: null,
            'facebook_url' => $url_fields['facebook_url'] ?: null,
            'linkedin_url' => $url_fields['linkedin_url'] ?: null,
            'twitter_url' => $url_fields['twitter_url'] ?: null,
            'instagram_url' => $url_fields['instagram_url'] ?: null
        ]);
        
        if (!$upsert_result) {
            throw new Exception('Failed to update social media links');
        }
        
        // Update setup progress for social links completion (if website is provided)
        if (!empty($url_fields['website_url'])) {
            $progress_sql = "
                INSERT INTO employer_setup_progress 
                (employer_id, social_complete, updated_at) 
                VALUES (:employer_id, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                social_complete = 1, 
                updated_at = NOW()
            ";
            
            $progress_stmt = $conn->prepare($progress_sql);
            $progress_stmt->execute(['employer_id' => $employer_id]);
        }
        
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
            'message' => 'Social media links updated successfully',
            'data' => [
                'website_url' => $url_fields['website_url'],
                'facebook_url' => $url_fields['facebook_url'],
                'linkedin_url' => $url_fields['linkedin_url'],
                'twitter_url' => $url_fields['twitter_url'],
                'instagram_url' => $url_fields['instagram_url'],
                'completion_percentage' => $completion_percentage
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_social_links.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_social_links.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating social media links. Please try again.'
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