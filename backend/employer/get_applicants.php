<?php
// backend/employer/get_applicants.php
// Main API to fetch all applicants for employer's jobs

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Session is handled by session_helper.php, so don't call session_start() here
require_once '../db.php';
require_once 'session_check.php';

// Only include calculate_match_score.php if it exists
if (file_exists('calculate_match_score.php')) {
    require_once 'calculate_match_score.php';
}

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get filter parameters
    $job_filter = $_GET['job'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $date_filter = $_GET['date'] ?? '';
    $search = $_GET['search'] ?? '';
    $skills_filter = $_GET['skills'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = ($page - 1) * $limit;
    $calculate_matches = $_GET['calculate_matches'] ?? 'true';
    $sort_by_match = $_GET['sort_by_match'] ?? 'false';

    // Build the main SQL query
    $sql = "SELECT 
        ja.application_id,
        ja.job_id, 
        ja.seeker_id,
        ja.application_status,
        ja.applied_at,
        ja.cover_letter,
        ja.employer_notes,
        COALESCE(ja.interview_score, 0) as interview_score,
        COALESCE(ja.match_score, 0) as match_score,
        ja.skills_matched,
        ja.skills_missing,
        ja.accommodation_compatibility,
        COALESCE(ja.last_activity, ja.applied_at) as last_activity,
        
        -- Job details
        jp.job_title,
        jp.employment_type,
        jp.location as job_location,
        jp.department,
        
        -- Seeker details
        js.first_name,
        js.last_name,
        js.middle_name,
        js.suffix,
        js.contact_number,
        js.city,
        js.province,
        
        -- User account info
        ua.email,
        
        -- Disability info
        dt.disability_name,
        dc.category_name as disability_category,
        
        -- Profile details
        COALESCE(pd.bio, '') as bio,
        COALESCE(pd.headline, '') as headline,
        pd.profile_photo_path,
        
        -- Resume info
        r.resume_id,
        r.file_name as resume_filename,
        r.file_path as resume_path,
        r.upload_date as resume_upload_date
        
    FROM job_applications ja
    INNER JOIN job_posts jp ON ja.job_id = jp.job_id
    INNER JOIN job_seekers js ON ja.seeker_id = js.seeker_id
    INNER JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
    INNER JOIN disability_types dt ON js.disability_id = dt.disability_id
    INNER JOIN disability_categories dc ON dt.category_id = dc.category_id
    LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
    LEFT JOIN resumes r ON ja.resume_id = r.resume_id
    WHERE jp.employer_id = :employer_id";
    
    $params = ['employer_id' => $employer_id];
    $where_conditions = [];
    
    // Add filters
    if (!empty($job_filter)) {
        $where_conditions[] = "ja.job_id = :job_filter";
        $params['job_filter'] = (int)$job_filter;
    }
    
    if (!empty($status_filter)) {
        // Map frontend status names to database values
        $status_map = [
            'new' => 'submitted',
            'reviewed' => 'under_review',
            'interview' => ['shortlisted', 'interview_scheduled', 'interviewed'],
            'hired' => 'hired',
            'rejected' => 'rejected'
        ];
        
        if (isset($status_map[$status_filter])) {
            if (is_array($status_map[$status_filter])) {
                $placeholders = [];
                $i = 0;
                foreach ($status_map[$status_filter] as $status) {
                    $placeholders[] = ":status$i";
                    $params["status$i"] = $status;
                    $i++;
                }
                $where_conditions[] = "ja.application_status IN (" . implode(', ', $placeholders) . ")";
            } else {
                $where_conditions[] = "ja.application_status = :status";
                $params['status'] = $status_map[$status_filter];
            }
        }
    }
    
    // Search filter (name, email, skills)
    if (!empty($search)) {
        $where_conditions[] = "(
            CONCAT(js.first_name, ' ', js.last_name) LIKE :search OR
            pd.headline LIKE :search OR
            pd.bio LIKE :search OR
            ua.email LIKE :search
        )";
        $params['search'] = "%{$search}%";
    }
    
    // Date filter
    if (!empty($date_filter)) {
        switch ($date_filter) {
            case 'today':
                $where_conditions[] = "DATE(ja.applied_at) = CURDATE()";
                break;
            case 'week':
                $where_conditions[] = "ja.applied_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where_conditions[] = "ja.applied_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
        }
    }
    
    // Add where conditions to query
    if (!empty($where_conditions)) {
        $sql .= " AND " . implode(" AND ", $where_conditions);
    }
    
    // Add ordering and pagination
    $sql .= " ORDER BY ja.applied_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get skills for each applicant
    foreach ($applicants as &$applicant) {
        // Get skills
        $skills_sql = "SELECT s.skill_name, sc.category_name 
                      FROM seeker_skills ss 
                      JOIN skills s ON ss.skill_id = s.skill_id 
                      JOIN skill_categories sc ON s.category_id = sc.category_id 
                      WHERE ss.seeker_id = :seeker_id";
        
        $skills_stmt = $conn->prepare($skills_sql);
        $skills_stmt->bindValue(':seeker_id', $applicant['seeker_id']);
        $skills_stmt->execute();
        $applicant['skills'] = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get workplace accommodations
        $acc_sql = "SELECT accommodation_list, no_accommodations_needed
                   FROM workplace_accommodations 
                   WHERE seeker_id = :seeker_id";
        $acc_stmt = $conn->prepare($acc_sql);
        $acc_stmt->execute(['seeker_id' => $applicant['seeker_id']]);
        $accommodations = $acc_stmt->fetch(PDO::FETCH_ASSOC);

        if ($accommodations) {
            if ($accommodations['no_accommodations_needed']) {
                $applicant['accommodations'] = [];
            } else {
                $acc_list = $accommodations['accommodation_list'];
                $decoded = json_decode($acc_list, true);
                if ($decoded) {
                    $applicant['accommodations'] = $decoded;
                } else {
                    $applicant['accommodations'] = array_filter(array_map('trim', explode(',', $acc_list)));
                }
            }
        } else {
            $applicant['accommodations'] = [];
        }

        // Process match score data
        $applicant['match_score'] = (float)($applicant['match_score'] ?? 0.00);
        $applicant['skills_matched_array'] = $applicant['skills_matched'] ? json_decode($applicant['skills_matched'], true) : [];
        $applicant['skills_missing_array'] = $applicant['skills_missing'] ? json_decode($applicant['skills_missing'], true) : [];
        $applicant['accommodation_compatibility'] = (float)($applicant['accommodation_compatibility'] ?? 0.00);
        
        // Format dates
        $applicant['applied_at_formatted'] = date('M j, Y', strtotime($applicant['applied_at']));
        $applicant['last_activity_formatted'] = date('M j, Y g:i A', strtotime($applicant['last_activity']));
        
        // Create avatar initials
        $applicant['avatar'] = strtoupper(substr($applicant['first_name'], 0, 1) . substr($applicant['last_name'], 0, 1));
        
        // Map status for frontend
        $status_map_reverse = [
            'submitted' => 'new',
            'under_review' => 'reviewed',
            'shortlisted' => 'interview',
            'interview_scheduled' => 'interview',
            'interviewed' => 'interview',
            'hired' => 'hired',
            'rejected' => 'rejected'
        ];
        $applicant['status_display'] = $status_map_reverse[$applicant['application_status']] ?? $applicant['application_status'];
        
        // Full name
        $applicant['full_name'] = trim($applicant['first_name'] . ' ' . $applicant['last_name']);
    }

    // Calculate match statistics
    $match_stats = [
        'excellent_matches' => 0,
        'good_matches' => 0,
        'fair_matches' => 0,
        'poor_matches' => 0,
        'average_score' => 0,
        'highest_score' => 0,
        'lowest_score' => 100
    ];
    
    if (!empty($applicants)) {
        $total_score = 0;
        foreach ($applicants as $applicant) {
            $score = $applicant['match_score'];
            $total_score += $score;
            
            if ($score >= 90) $match_stats['excellent_matches']++;
            elseif ($score >= 75) $match_stats['good_matches']++;
            elseif ($score >= 60) $match_stats['fair_matches']++;
            else $match_stats['poor_matches']++;
            
            $match_stats['highest_score'] = max($match_stats['highest_score'], $score);
            $match_stats['lowest_score'] = min($match_stats['lowest_score'], $score);
        }
        
        $match_stats['average_score'] = round($total_score / count($applicants), 2);
    }

    // Get total count for pagination - CORRECTED VERSION
    $count_sql = "SELECT COUNT(*) as total 
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        INNER JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        INNER JOIN disability_types dt ON js.disability_id = dt.disability_id
        INNER JOIN disability_categories dc ON dt.category_id = dc.category_id
        LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
        LEFT JOIN resumes r ON ja.resume_id = r.resume_id
        WHERE jp.employer_id = :employer_id";
    
    // Add the same where conditions as the main query
    if (!empty($where_conditions)) {
        $count_sql .= " AND " . implode(" AND ", $where_conditions);
    }
    
    $count_stmt = $conn->prepare($count_sql);
    foreach ($params as $key => $value) {
        if ($key !== 'limit' && $key !== 'offset') {
            $count_stmt->bindValue(":$key", $value);
        }
    }
    $count_stmt->execute();
    $total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get basic statistics for the dashboard
    $stats_sql = "SELECT 
        COUNT(*) as total_applications,
        COUNT(CASE WHEN ja.application_status = 'submitted' THEN 1 END) as new_applications,
        COUNT(CASE WHEN ja.application_status = 'under_review' THEN 1 END) as under_review,
        COUNT(CASE WHEN ja.application_status IN ('interview_scheduled', 'interviewed') THEN 1 END) as interviews_scheduled,
        COUNT(CASE WHEN ja.application_status = 'hired' THEN 1 END) as hired
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        WHERE jp.employer_id = :employer_id";
    
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->execute(['employer_id' => $employer_id]);
    $basic_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Merge basic stats with match stats
    $combined_stats = array_merge($basic_stats, $match_stats);
    
    // Return the complete response - SINGLE JSON OUTPUT
    echo json_encode([
        'success' => true,
        'applicants' => $applicants,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total_count / $limit),
            'total_count' => (int)$total_count,
            'per_page' => $limit
        ],
        'stats' => $combined_stats,
        'filters_applied' => [
            'job' => $job_filter,
            'status' => $status_filter,
            'date' => $date_filter,
            'search' => $search,
            'skills' => $skills_filter
        ],
        'employer_info' => [
            'company_name' => $employer_data['company_name']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Get Applicants Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__
        ]
    ]);
}

// NO ADDITIONAL echo statements after this point!
?>