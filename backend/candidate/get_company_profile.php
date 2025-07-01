<?php
// get_company_profile.php - Backend for getting real company profile data
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../db.php';

try {
    // Get employer ID from request
    $employer_id = isset($_GET['employer_id']) ? (int)$_GET['employer_id'] : 0;
    
    if (!$employer_id) {
        throw new Exception('Employer ID is required');
    }
    
    // Get comprehensive company information
    $stmt = $conn->prepare("
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
            e.created_at,
            
            -- Contact information
            ec.first_name as contact_first_name,
            ec.last_name as contact_last_name,
            ec.position as contact_position,
            ec.contact_number,
            ec.email as contact_email,
            
            -- Social links
            esl.website_url,
            esl.facebook_url,
            esl.linkedin_url,
            esl.twitter_url,
            esl.instagram_url,
            
            -- Hiring preferences
            ehp.open_to_pwd,
            ehp.disability_types,
            ehp.workplace_accommodations,
            ehp.additional_accommodations,
            
            -- Industry information
            i.industry_name,
            i.industry_icon
            
        FROM employers e
        LEFT JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
        LEFT JOIN employer_social_links esl ON e.employer_id = esl.employer_id
        LEFT JOIN employer_hiring_preferences ehp ON e.employer_id = ehp.employer_id
        LEFT JOIN industries i ON e.industry_id = i.industry_id
        WHERE e.employer_id = ? AND e.verification_status = 'verified'
    ");
    
    $stmt->execute([$employer_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        // Try to get basic company info even if not fully verified
        $stmt = $conn->prepare("
            SELECT 
                e.employer_id,
                e.company_name,
                e.industry,
                e.company_address,
                e.company_description,
                e.verification_status
            FROM employers e
            WHERE e.employer_id = ?
        ");
        
        $stmt->execute([$employer_id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$company) {
            throw new Exception('Company not found');
        }
    }
    
    // Get job statistics for this employer
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_jobs,
            COUNT(CASE WHEN job_status = 'active' THEN 1 END) as active_jobs,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_jobs
        FROM job_posts 
        WHERE employer_id = ?
    ");
    
    $stmt->execute([$employer_id]);
    $job_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get application statistics
    $stmt = $conn->prepare("
        SELECT 
            COUNT(ja.application_id) as total_applications,
            COUNT(CASE WHEN ja.application_status = 'hired' THEN 1 END) as hired_count
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        WHERE jp.employer_id = ?
    ");
    
    $stmt->execute([$employer_id]);
    $app_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format the response
    $response = [
        'success' => true,
        'company' => [
            'employer_id' => $company['employer_id'],
            'company_name' => $company['company_name'],
            'industry' => $company['industry'] ?: $company['industry_name'],
            'company_address' => $company['company_address'],
            'company_size' => $company['company_size'],
            'company_website' => $company['company_website'],
            'company_description' => $company['company_description'],
            'mission_vision' => $company['mission_vision'],
            'why_join_us' => $company['why_join_us'],
            'company_logo_path' => $company['company_logo_path'],
            'verification_status' => $company['verification_status'],
            'member_since' => date('Y', strtotime($company['created_at'])),
            
            // Contact information
            'contact' => [
                'name' => trim(($company['contact_first_name'] ?? '') . ' ' . ($company['contact_last_name'] ?? '')),
                'position' => $company['contact_position'],
                'phone' => $company['contact_number'],
                'email' => $company['contact_email']
            ],
            
            // Social links
            'social_links' => [
                'website' => $company['website_url'],
                'facebook' => $company['facebook_url'],
                'linkedin' => $company['linkedin_url'],
                'twitter' => $company['twitter_url'],
                'instagram' => $company['instagram_url']
            ],
            
            // PWD commitment
            'pwd_commitment' => [
                'open_to_pwd' => (bool)($company['open_to_pwd'] ?? true),
                'disability_types' => $company['disability_types'] ? json_decode($company['disability_types'], true) : [],
                'workplace_accommodations' => $company['workplace_accommodations'] ? json_decode($company['workplace_accommodations'], true) : [],
                'additional_accommodations' => $company['additional_accommodations']
            ],
            
            // Statistics
            'statistics' => [
                'total_jobs' => (int)($job_stats['total_jobs'] ?? 0),
                'active_jobs' => (int)($job_stats['active_jobs'] ?? 0),
                'recent_jobs' => (int)($job_stats['recent_jobs'] ?? 0),
                'total_applications' => (int)($app_stats['total_applications'] ?? 0),
                'hired_count' => (int)($app_stats['hired_count'] ?? 0)
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>