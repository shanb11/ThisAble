<?php
/**
 * Get Job Details API
 * Fetches complete job details with applicants for the logged-in employer
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Include required files
require_once('../db.php');
require_once('../shared/session_helper.php');

try {
    // Check if employer is logged in
    if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $employer_id = $_SESSION['employer_id'];
    
    // Get job ID from URL parameters
    $job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
    
    if (!$job_id) {
        throw new Exception('Job ID is required.');
    }

    // Verify job belongs to this employer
    $ownership_sql = "SELECT COUNT(*) as count FROM job_posts WHERE job_id = :job_id AND employer_id = :employer_id";
    $ownership_stmt = $conn->prepare($ownership_sql);
    $ownership_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    
    if ($ownership_stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
        throw new Exception('Job not found or access denied.');
    }

    // Get complete job details with accommodations
    $job_sql = "
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.department,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.application_deadline,
            jp.job_description,
            jp.job_requirements,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.job_status,
            jp.posted_at,
            jp.created_at,
            jp.updated_at,
            jp.applications_count,
            jp.views_count,
            
            -- Accommodation data
            COALESCE(ja.wheelchair_accessible, 0) as wheelchair_accessible,
            COALESCE(ja.assistive_technology, 0) as assistive_technology,
            COALESCE(ja.remote_work_option, 0) as remote_work_option,
            COALESCE(ja.screen_reader_compatible, 0) as screen_reader_compatible,
            COALESCE(ja.sign_language_interpreter, 0) as sign_language_interpreter,
            COALESCE(ja.modified_workspace, 0) as modified_workspace,
            COALESCE(ja.transportation_support, 0) as transportation_support,
            COALESCE(ja.additional_accommodations, '') as additional_accommodations
            
        FROM job_posts jp
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_id = :job_id AND jp.employer_id = :employer_id
    ";

    $job_stmt = $conn->prepare($job_sql);
    $job_stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    $job = $job_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        throw new Exception('Job not found.');
    }

    // Get job applicants with their details
    $applicants_sql = "
        SELECT 
            ja.application_id,
            ja.application_status,
            ja.applied_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.interview_score,
            
            -- Job seeker details
            js.seeker_id,
            js.first_name,
            js.middle_name,
            js.last_name,
            js.suffix,
            js.contact_number,
            js.city,
            js.province,
            
            -- Disability information
            dt.disability_name,
            dc.category_name as disability_category,
            
            -- User account info
            ua.email,
            
            -- Resume information
            r.resume_id,
            r.file_name as resume_filename,
            r.file_path as resume_path,
            r.upload_date as resume_upload_date,
            
            -- Profile details
            pd.bio,
            pd.headline,
            pd.profile_photo_path
            
        FROM job_applications ja
        INNER JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        INNER JOIN disability_types dt ON js.disability_id = dt.disability_id
        INNER JOIN disability_categories dc ON dt.category_id = dc.category_id
        INNER JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
        LEFT JOIN resumes r ON ja.resume_id = r.resume_id
        LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
        WHERE ja.job_id = :job_id
        ORDER BY ja.applied_at DESC
    ";

    $applicants_stmt = $conn->prepare($applicants_sql);
    $applicants_stmt->execute(['job_id' => $job_id]);
    $applicants = $applicants_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get skills for each applicant
    $formatted_applicants = [];
    foreach ($applicants as $applicant) {
        // Get applicant skills
        $skills_sql = "
            SELECT s.skill_name 
            FROM seeker_skills ss
            INNER JOIN skills s ON ss.skill_id = s.skill_id
            WHERE ss.seeker_id = :seeker_id
        ";
        $skills_stmt = $conn->prepare($skills_sql);
        $skills_stmt->execute(['seeker_id' => $applicant['seeker_id']]);
        $skills = $skills_stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get workplace accommodations for this seeker
        $accommodations_sql = "
            SELECT accommodation_list, no_accommodations_needed
            FROM workplace_accommodations 
            WHERE seeker_id = :seeker_id
        ";
        $acc_stmt = $conn->prepare($accommodations_sql);
        $acc_stmt->execute(['seeker_id' => $applicant['seeker_id']]);
        $accommodations = $acc_stmt->fetch(PDO::FETCH_ASSOC);

        // Format applicant name
        $full_name = trim($applicant['first_name'] . ' ' . 
                         ($applicant['middle_name'] ? $applicant['middle_name'] . ' ' : '') . 
                         $applicant['last_name'] . 
                         ($applicant['suffix'] ? ' ' . $applicant['suffix'] : ''));

        // Format location
        $location = trim(($applicant['city'] ? $applicant['city'] : '') . 
                        ($applicant['city'] && $applicant['province'] ? ', ' : '') . 
                        ($applicant['province'] ? $applicant['province'] : ''));

        $formatted_applicants[] = [
            'application_id' => $applicant['application_id'],
            'seeker_id' => $applicant['seeker_id'],
            'name' => $full_name,
            'email' => $applicant['email'],
            'phone' => $applicant['contact_number'],
            'location' => $location ?: 'Not specified',
            'headline' => $applicant['headline'] ?: '',
            'bio' => $applicant['bio'] ?: '',
            'profile_photo' => $applicant['profile_photo_path'] ?: '',
            'disability' => [
                'type' => $applicant['disability_name'],
                'category' => $applicant['disability_category']
            ],
            'accommodations' => [
                'needed' => !($accommodations['no_accommodations_needed'] ?? true),
                'list' => $accommodations['accommodation_list'] ?? 'None specified'
            ],
            'skills' => $skills,
            'application' => [
                'status' => $applicant['application_status'],
                'applied_at' => $applicant['applied_at'],
                'cover_letter' => $applicant['cover_letter'],
                'employer_notes' => $applicant['employer_notes'],
                'interview_score' => $applicant['interview_score']
            ],
            'resume' => $applicant['resume_id'] ? [
                'resume_id' => $applicant['resume_id'],
                'filename' => $applicant['resume_filename'],
                'upload_date' => $applicant['resume_upload_date']
            ] : null
        ];
    }

    // Get application statistics
    $stats_sql = "
        SELECT 
            application_status,
            COUNT(*) as count
        FROM job_applications 
        WHERE job_id = :job_id
        GROUP BY application_status
    ";
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->execute(['job_id' => $job_id]);
    $status_stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format statistics
    $statistics = [
        'total_applicants' => count($applicants),
        'submitted' => 0,
        'under_review' => 0,
        'shortlisted' => 0,
        'interview_scheduled' => 0,
        'interviewed' => 0,
        'hired' => 0,
        'rejected' => 0,
        'withdrawn' => 0
    ];

    foreach ($status_stats as $stat) {
        $status = $stat['application_status'];
        if (isset($statistics[$status])) {
            $statistics[$status] = (int)$stat['count'];
        }
    }

    // Format job data
    $accommodations = [
        'wheelchair_accessible' => (bool)$job['wheelchair_accessible'],
        'assistive_technology' => (bool)$job['assistive_technology'],
        'remote_work_option' => (bool)$job['remote_work_option'],
        'screen_reader_compatible' => (bool)$job['screen_reader_compatible'],
        'sign_language_interpreter' => (bool)$job['sign_language_interpreter'],
        'modified_workspace' => (bool)$job['modified_workspace'],
        'transportation_support' => (bool)$job['transportation_support'],
        'additional_accommodations' => $job['additional_accommodations']
    ];

    $formatted_job = [
        'job_id' => $job['job_id'],
        'job_title' => $job['job_title'],
        'department' => $job['department'],
        'location' => $job['location'],
        'employment_type' => $job['employment_type'],
        'salary_range' => $job['salary_range'],
        'application_deadline' => $job['application_deadline'] ? date('Y-m-d', strtotime($job['application_deadline'])) : null,
        'job_description' => $job['job_description'],
        'job_requirements' => $job['job_requirements'],
        'remote_work_available' => (bool)$job['remote_work_available'],
        'flexible_schedule' => (bool)$job['flexible_schedule'],
        'job_status' => $job['job_status'],
        'posted_at' => $job['posted_at'] ? date('Y-m-d', strtotime($job['posted_at'])) : date('Y-m-d', strtotime($job['created_at'])),
        'created_at' => date('Y-m-d H:i:s', strtotime($job['created_at'])),
        'updated_at' => date('Y-m-d H:i:s', strtotime($job['updated_at'])),
        'applications_count' => (int)$job['applications_count'],
        'views_count' => (int)$job['views_count'],
        'accommodations' => $accommodations,
        'company_name' => $_SESSION['company_name'] ?? 'Company'
    ];

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'job' => $formatted_job,
            'applicants' => $formatted_applicants,
            'statistics' => $statistics
        ],
        'message' => 'Job details retrieved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log("Database error in get_job_details.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Unable to fetch job details. Please try again.',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Error in get_job_details.php: " . $e->getMessage());
    
    $status_code = ($e->getMessage() === 'Unauthorized access. Please log in.') ? 401 : 400;
    
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    error_log("Unexpected error in get_job_details.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred',
        'message' => 'Please try again later or contact support.',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>