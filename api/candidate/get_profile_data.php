<?php
/**
 * BULLETPROOF Get Profile Data API for ThisAble Mobile
 * Simple queries that match your exact database structure
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow GET requests
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
    error_log("BULLETPROOF Profile API: seeker_id=$seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: GET BASIC JOB SEEKER INFO (GUARANTEED TO EXIST) =====
    $stmt = $conn->prepare("SELECT * FROM job_seekers WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $basicInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$basicInfo) {
        error_log("BULLETPROOF: Job seeker not found for seeker_id=$seekerId");
        ApiResponse::error("Profile not found", 404);
    }
    
    error_log("BULLETPROOF: Basic info retrieved for: " . $basicInfo['first_name'] . " " . $basicInfo['last_name']);
    
    // ===== STEP 2: GET EMAIL (SEPARATE QUERY) =====
    $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $emailData = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $emailData['email'] ?? '';
    
    error_log("BULLETPROOF: Email found: " . ($email ? 'Yes' : 'No'));
    
    // ===== STEP 3: GET PROFILE DETAILS (SEPARATE QUERY - MAY NOT EXIST) =====
    $stmt = $conn->prepare("SELECT * FROM profile_details WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $profileDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle missing profile_details
    if (!$profileDetails) {
        error_log("BULLETPROOF: No profile_details found, using defaults");
        $profileDetails = [
            'bio' => '',
            'location' => '',
            'headline' => '',
            'profile_photo_path' => null,
            'cover_photo_path' => null
        ];
    } else {
        error_log("BULLETPROOF: Profile details found");
    }
    
    // ===== STEP 4: GET DISABILITY INFO (SEPARATE QUERY) =====
    $disabilityInfo = ['disability_name' => '', 'disability_category' => ''];
    if ($basicInfo['disability_id']) {
        $stmt = $conn->prepare("SELECT disability_name FROM disability_types WHERE disability_id = ?");
        $stmt->execute([$basicInfo['disability_id']]);
        $disabilityResult = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($disabilityResult) {
            $disabilityInfo['disability_name'] = $disabilityResult['disability_name'];
            
            // Get category separately
            $stmt = $conn->prepare("
                SELECT dc.category_name 
                FROM disability_types dt 
                JOIN disability_categories dc ON dt.category_id = dc.category_id 
                WHERE dt.disability_id = ?
            ");
            $stmt->execute([$basicInfo['disability_id']]);
            $categoryResult = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($categoryResult) {
                $disabilityInfo['disability_category'] = $categoryResult['category_name'];
            }
        }
    }
    
    error_log("BULLETPROOF: Disability info: " . $disabilityInfo['disability_name']);
    
    // ===== STEP 5: GET PWD ID (SEPARATE QUERY) =====
    $stmt = $conn->prepare("SELECT pwd_id_number, issued_at FROM pwd_ids WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $pwdData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ===== STEP 6: GET SKILLS (SEPARATE QUERY) =====
    $stmt = $conn->prepare("
        SELECT 
            s.skill_id,
            s.skill_name,
            s.skill_icon,
            s.skill_tooltip,
            sc.category_name,
            sc.category_icon
        FROM seeker_skills ss
        JOIN skills s ON ss.skill_id = s.skill_id
        JOIN skill_categories sc ON s.category_id = sc.category_id
        WHERE ss.seeker_id = ?
        ORDER BY sc.category_name, s.skill_name
    ");
    $stmt->execute([$seekerId]);
    $skillsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group skills by category
    $skills = [];
    foreach ($skillsData as $skill) {
        $categoryName = $skill['category_name'];
        if (!isset($skills[$categoryName])) {
            $skills[$categoryName] = [
                'category_name' => $categoryName,
                'category_icon' => $skill['category_icon'],
                'skills' => []
            ];
        }
        $skills[$categoryName]['skills'][] = [
            'skill_id' => $skill['skill_id'],
            'skill_name' => $skill['skill_name'],
            'skill_icon' => $skill['skill_icon'],
            'skill_tooltip' => $skill['skill_tooltip']
        ];
    }
    $skills = array_values($skills);
    
    error_log("BULLETPROOF: Skills found: " . count($skills) . " categories");
    
    // ===== STEP 7: GET WORK PREFERENCES (SEPARATE QUERY) =====
    $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $workPreferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$workPreferences) {
        $workPreferences = [
            'work_style' => null,
            'job_type' => null,
            'salary_range' => null,
            'availability' => null
        ];
    }
    
    // ===== STEP 8: GET ACCESSIBILITY NEEDS (SEPARATE QUERY) =====
    $stmt = $conn->prepare("SELECT * FROM workplace_accommodations WHERE seeker_id = ?");
    $stmt->execute([$seekerId]);
    $accessibilityData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $accessibilityNeeds = [];
    if ($accessibilityData) {
        if ($accessibilityData['no_accommodations_needed']) {
            $accessibilityNeeds = ['No accommodations needed'];
        } else {
            $accommodationsList = json_decode($accessibilityData['accommodation_list'], true);
            $accessibilityNeeds = is_array($accommodationsList) ? $accommodationsList : [];
        }
    }
    
    // ===== STEP 9: GET RESUMES (SEPARATE QUERY) =====
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
        ORDER BY upload_date DESC
    ");
    $stmt->execute([$seekerId]);
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format resume data
    foreach ($resumes as &$resume) {
        $resume['upload_date'] = date('F j, Y', strtotime($resume['upload_date']));
        $resume['file_size_formatted'] = formatFileSize($resume['file_size']);
        $resume['is_current'] = (bool)$resume['is_current'];
    }
    
    error_log("BULLETPROOF: Resumes found: " . count($resumes));
    
    // ===== STEP 10: GET EDUCATION (SEPARATE QUERY) =====
    $stmt = $conn->prepare("
        SELECT * FROM education
        WHERE seeker_id = ?
        ORDER BY start_date DESC
    ");
    $stmt->execute([$seekerId]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format education dates
    foreach ($education as &$edu) {
        $edu['start_date'] = date('M Y', strtotime($edu['start_date']));
        $edu['end_date'] = $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : null;
        $edu['is_current'] = (bool)$edu['is_current'];
        $edu['period'] = $edu['start_date'] . ' - ' . ($edu['is_current'] ? 'Present' : $edu['end_date']);
    }
    
    error_log("BULLETPROOF: Education found: " . count($education));
    
    // ===== STEP 11: GET EXPERIENCE (SEPARATE QUERY) =====
    $stmt = $conn->prepare("
        SELECT * FROM experience
        WHERE seeker_id = ?
        ORDER BY start_date DESC
    ");
    $stmt->execute([$seekerId]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format experience dates
    foreach ($experience as &$exp) {
        $exp['start_date'] = date('M Y', strtotime($exp['start_date']));
        $exp['end_date'] = $exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : null;
        $exp['is_current'] = (bool)$exp['is_current'];
        $exp['period'] = $exp['start_date'] . ' - ' . ($exp['is_current'] ? 'Present' : $exp['end_date']);
    }
    
    error_log("BULLETPROOF: Experience found: " . count($experience));
    
    // ===== STEP 12: CALCULATE PROFILE COMPLETION =====
    $completionScore = 0;
    $totalFields = 10;
    
    if (!empty($basicInfo['first_name'])) $completionScore++;
    if (!empty($email)) $completionScore++;
    if (!empty($basicInfo['contact_number'])) $completionScore++;
    if (!empty($profileDetails['bio'])) $completionScore++;
    if (!empty($profileDetails['headline'])) $completionScore++;
    if (!empty($skills)) $completionScore++;
    if (!empty($workPreferences['work_style']) || !empty($workPreferences['job_type'])) $completionScore++;
    if (!empty($resumes)) $completionScore++;
    if (!empty($education)) $completionScore++;
    if (!empty($experience)) $completionScore++;
    
    $profileCompletion = round(($completionScore / $totalFields) * 100);
    
    error_log("BULLETPROOF: Profile completion: $profileCompletion%");
    
    // ===== STEP 13: COMPILE RESPONSE =====
    $profileData = [
        'personal_info' => [
            'first_name' => $basicInfo['first_name'] ?? '',
            'middle_name' => $basicInfo['middle_name'] ?? '',
            'last_name' => $basicInfo['last_name'] ?? '',
            'suffix' => $basicInfo['suffix'] ?? '',
            'email' => $email,
            'contact_number' => $basicInfo['contact_number'] ?? '',
            'city' => $basicInfo['city'] ?? '',
            'province' => $basicInfo['province'] ?? '',
            'preferred_location' => $profileDetails['location'] ?? '',
            'bio' => $profileDetails['bio'] ?? '',
            'headline' => $profileDetails['headline'] ?? '',
            'profile_photo_path' => $profileDetails['profile_photo_path'],
            'cover_photo_path' => $profileDetails['cover_photo_path'],
            'disability_name' => $disabilityInfo['disability_name'] ?? '',
            'disability_category' => $disabilityInfo['disability_category'] ?? '',
            'pwd_id_number' => $pwdData['pwd_id_number'] ?? '',
            'pwd_issued_date' => $pwdData['issued_at'] ?? ''
        ],
        'skills' => $skills,
        'work_preferences' => $workPreferences,
        'accessibility_needs' => $accessibilityNeeds,
        'resumes' => $resumes,
        'education' => $education,
        'experience' => $experience,
        'profile_completion' => $profileCompletion,
        'debug_info' => [
            'seeker_id' => $seekerId,
            'basic_info_found' => !empty($basicInfo),
            'email_found' => !empty($email),
            'profile_details_found' => !empty($profileDetails['bio']),
            'sql_working' => true
        ]
    ];
    
    error_log("BULLETPROOF: Profile data compiled successfully");
    
    ApiResponse::success($profileData, "Profile data retrieved successfully");
    
} catch(PDOException $e) {
    error_log("BULLETPROOF Profile database error: " . $e->getMessage());
    error_log("BULLETPROOF SQL Error Info: " . json_encode($e->errorInfo ?? []));
    ApiResponse::serverError("Database query failed: " . $e->getMessage());
    
} catch(Exception $e) {
    error_log("BULLETPROOF Profile general error: " . $e->getMessage());
    ApiResponse::serverError("API error: " . $e->getMessage());
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes < 1024) {
        return $bytes . ' B';
    } elseif ($bytes < 1024 * 1024) {
        return round($bytes / 1024, 1) . ' KB';
    } else {
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
}
?>