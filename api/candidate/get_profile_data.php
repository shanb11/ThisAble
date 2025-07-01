<?php
/**
 * Get Profile Data API for ThisAble Mobile
 * Returns: complete profile information for profile management
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
    error_log("Profile Data API: seeker_id=$seekerId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== PERSONAL INFORMATION =====
    $stmt = $conn->prepare("
        SELECT 
            js.*,
            ua.email,
            pd.bio,
            pd.location as preferred_location,
            pd.profile_photo_path,
            pd.cover_photo_path,
            pd.headline,
            dt.disability_name,
            dc.category_name as disability_category,
            pwd.pwd_id_number,
            pwd.issued_at as pwd_issued_date
        FROM job_seekers js
        LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
        LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
        LEFT JOIN disability_categories dc ON dt.category_id = dc.category_id
        LEFT JOIN pwd_ids pwd ON js.seeker_id = pwd.seeker_id
        WHERE js.seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $personalInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$personalInfo) {
        ApiResponse::error("Profile not found", 404);
    }
    
    // ===== SKILLS =====
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
    
    // Convert to indexed array
    $skills = array_values($skills);
    
    // ===== WORK PREFERENCES =====
    $stmt = $conn->prepare("
        SELECT 
            work_style,
            job_type,
            salary_range,
            availability
        FROM user_preferences
        WHERE seeker_id = ?
    ");
    $stmt->execute([$seekerId]);
    $workPreferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set defaults if no preferences found
    if (!$workPreferences) {
        $workPreferences = [
            'work_style' => null,
            'job_type' => null,
            'salary_range' => null,
            'availability' => null
        ];
    }
    
    // ===== ACCESSIBILITY NEEDS =====
    $stmt = $conn->prepare("
        SELECT 
            disability_type,
            accommodation_list,
            no_accommodations_needed
        FROM workplace_accommodations
        WHERE seeker_id = ?
    ");
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
    
    // ===== RESUME INFORMATION =====
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
    
    // Format resume data - FIXED: Use function directly, not $this->
    foreach ($resumes as &$resume) {
        $resume['upload_date'] = date('F j, Y', strtotime($resume['upload_date']));
        $resume['file_size_formatted'] = formatFileSize($resume['file_size']);
        $resume['is_current'] = (bool)$resume['is_current'];
    }
    
    // ===== EDUCATION =====
    $stmt = $conn->prepare("
        SELECT 
            education_id,
            degree,
            institution,
            location,
            start_date,
            end_date,
            is_current,
            description
        FROM education
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
    
    // ===== WORK EXPERIENCE =====
    $stmt = $conn->prepare("
        SELECT 
            experience_id,
            job_title,
            company,
            location,
            start_date,
            end_date,
            is_current,
            description
        FROM experience
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
    
    // ===== CALCULATE PROFILE COMPLETION =====
    $completionScore = 0;
    $totalFields = 10;
    
    // Basic info (3 points)
    if (!empty($personalInfo['first_name'])) $completionScore++;
    if (!empty($personalInfo['email'])) $completionScore++;
    if (!empty($personalInfo['contact_number'])) $completionScore++;
    
    // Profile details (2 points)
    if (!empty($personalInfo['bio'])) $completionScore++;
    if (!empty($personalInfo['headline'])) $completionScore++;
    
    // Skills (1 point)
    if (!empty($skills)) $completionScore++;
    
    // Work preferences (1 point)
    if (!empty($workPreferences['work_style']) || !empty($workPreferences['job_type'])) $completionScore++;
    
    // Resume (1 point)
    if (!empty($resumes)) $completionScore++;
    
    // Education (1 point)
    if (!empty($education)) $completionScore++;
    
    // Experience (1 point)
    if (!empty($experience)) $completionScore++;
    
    $profileCompletion = round(($completionScore / $totalFields) * 100);
    
    // ===== COMPILE RESPONSE =====
    $profileData = [
        'personal_info' => [
            'first_name' => $personalInfo['first_name'],
            'middle_name' => $personalInfo['middle_name'],
            'last_name' => $personalInfo['last_name'],
            'suffix' => $personalInfo['suffix'],
            'email' => $personalInfo['email'],
            'contact_number' => $personalInfo['contact_number'],
            'city' => $personalInfo['city'],
            'province' => $personalInfo['province'],
            'preferred_location' => $personalInfo['preferred_location'],
            'bio' => $personalInfo['bio'],
            'headline' => $personalInfo['headline'],
            'profile_photo_path' => $personalInfo['profile_photo_path'],
            'cover_photo_path' => $personalInfo['cover_photo_path'],
            'disability_name' => $personalInfo['disability_name'],
            'disability_category' => $personalInfo['disability_category'],
            'pwd_id_number' => $personalInfo['pwd_id_number'],
            'pwd_issued_date' => $personalInfo['pwd_issued_date']
        ],
        'skills' => $skills,
        'work_preferences' => $workPreferences,
        'accessibility_needs' => $accessibilityNeeds,
        'resumes' => $resumes,
        'education' => $education,
        'experience' => $experience,
        'profile_completion' => $profileCompletion
    ];
    
    ApiResponse::success($profileData, "Profile data retrieved successfully");
    
} catch(PDOException $e) {
    error_log("Profile data database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Profile data error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while retrieving profile data");
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