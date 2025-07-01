<?php
/**
 * Get Company Profile Data
 * Fetches all employer profile information for the profile page
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Get comprehensive employer data
    $sql = "
        SELECT 
            e.employer_id,
            e.company_name,
            e.industry,
            e.company_address,
            e.company_size,
            e.company_website,
            e.company_description,
            e.mission_vision,
            e.why_join_us,
            e.company_logo_path,
            e.verification_status,
            e.industry_id,
            i.industry_name,
            
            ec.contact_id,
            ec.first_name,
            ec.last_name,
            ec.position,
            ec.contact_number,
            ec.email,
            
            ehp.open_to_pwd,
            ehp.disability_types,
            ehp.workplace_accommodations,
            ehp.additional_accommodations,
            
            esl.website_url,
            esl.facebook_url,
            esl.linkedin_url,
            esl.twitter_url,
            esl.instagram_url,
            
            esp.completion_percentage,
            esp.basic_info_complete,
            esp.company_description_complete,
            esp.hiring_preferences_complete,
            esp.social_links_complete,
            esp.logo_uploaded,
            esp.setup_complete
            
        FROM employers e
        LEFT JOIN industries i ON e.industry_id = i.industry_id
        LEFT JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
        LEFT JOIN employer_hiring_preferences ehp ON e.employer_id = ehp.employer_id
        LEFT JOIN employer_social_links esl ON e.employer_id = esl.employer_id
        LEFT JOIN employer_setup_progress esp ON e.employer_id = esp.employer_id
        WHERE e.employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Company profile not found'
        ]);
        exit;
    }
    
    // Parse JSON fields
    $disability_types = null;
    $workplace_accommodations = null;
    
    if ($result['disability_types']) {
        $disability_types = json_decode($result['disability_types'], true);
    }
    
    if ($result['workplace_accommodations']) {
        $workplace_accommodations = json_decode($result['workplace_accommodations'], true);
    }
    
    // Format the response
    $profile_data = [
        'company_identity' => [
            'company_name' => $result['company_name'] ?? '',
            'industry' => $result['industry'] ?? '',
            'industry_id' => $result['industry_id'],
            'industry_name' => $result['industry_name'] ?? '',
            'company_address' => $result['company_address'] ?? '',
            'company_size' => $result['company_size'] ?? '',
            'verification_status' => $result['verification_status'] ?? 'pending'
        ],
        'contact_person' => [
            'contact_id' => $result['contact_id'],
            'first_name' => $result['first_name'] ?? '',
            'last_name' => $result['last_name'] ?? '',
            'position' => $result['position'] ?? '',
            'contact_number' => $result['contact_number'] ?? '',
            'email' => $result['email'] ?? ''
        ],
        'company_description' => [
            'company_description' => $result['company_description'] ?? '',
            'mission_vision' => $result['mission_vision'] ?? '',
            'why_join_us' => $result['why_join_us'] ?? '',
            'company_logo_path' => $result['company_logo_path'] ?? ''
        ],
        'hiring_preferences' => [
            'open_to_pwd' => isset($result['open_to_pwd']) ? (bool)$result['open_to_pwd'] : false,
            'disability_types' => !empty($result['disability_types']) ? json_decode($result['disability_types'], true) : [],
            'accessibility_options' => !empty($result['workplace_accommodations']) ? json_decode($result['workplace_accommodations'], true) : [],
            'additional_accommodations' => $result['additional_accommodations'] ?? ''
        ],
        'social_links' => [
            'website_url' => $result['website_url'] ?? '',
            'facebook_url' => $result['facebook_url'] ?? '',
            'linkedin_url' => $result['linkedin_url'] ?? '',
            'twitter_url' => $result['twitter_url'] ?? '',
            'instagram_url' => $result['instagram_url'] ?? ''
        ],
        'setup_progress' => [
            'completion_percentage' => (int)($result['completion_percentage'] ?? 0),
            'basic_info_complete' => (bool)($result['basic_info_complete'] ?? false),
            'company_description_complete' => (bool)($result['company_description_complete'] ?? false),
            'hiring_preferences_complete' => (bool)($result['hiring_preferences_complete'] ?? false),
            'social_links_complete' => (bool)($result['social_links_complete'] ?? false),
            'logo_uploaded' => (bool)($result['logo_uploaded'] ?? false),
            'setup_complete' => (bool)($result['setup_complete'] ?? false)
        ]
    ];
    
    // Get industries list for dropdown
    $industries_sql = "SELECT industry_id, industry_name FROM industries ORDER BY industry_name";
    $industries_stmt = $conn->prepare($industries_sql);
    $industries_stmt->execute();
    $industries = $industries_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $profile_data,
        'industries' => $industries,
        'message' => 'Profile data loaded successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_company_profile.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    
} catch (Exception $e) {
    error_log("General error in get_company_profile.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while loading profile data'
    ]);
}
?>