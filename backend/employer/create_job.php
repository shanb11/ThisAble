<?php
/**
 * Create Job API - FIXED FOR YOUR SESSION STRUCTURE + NOTIFICATIONS
 */

// Prevent any output before JSON headers
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'Only POST requests are allowed'
    ]);
    exit();
}

session_start();

// Include required files
require_once('../db.php');
require_once('../shared/session_helper.php');
require_once('notification_system.php'); // ADD NOTIFICATION SYSTEM

try {
    // Check if employer is logged in - USING YOUR SESSION STRUCTURE
    if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        throw new Exception('Unauthorized access. Please log in.');
    }

    $employer_id = $_SESSION['employer_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    $required_fields = ['job_title', 'department', 'location', 'employment_type', 'job_description', 'job_requirements'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Required field missing: $field");
        }
    }

    // Sanitize and prepare job data
    $job_data = [
        'job_title' => trim($input['job_title']),
        'department' => trim($input['department']),
        'location' => trim($input['location']),
        'employment_type' => trim($input['employment_type']),
        'salary_range' => !empty($input['salary_range']) ? trim($input['salary_range']) : null,
        'application_deadline' => !empty($input['application_deadline']) ? $input['application_deadline'] : null,
        'job_description' => trim($input['job_description']),
        'job_requirements' => trim($input['job_requirements']),
        'remote_work_available' => isset($input['remote_work_available']) ? (bool)$input['remote_work_available'] : false,
        'flexible_schedule' => isset($input['flexible_schedule']) ? (bool)$input['flexible_schedule'] : false,
        'job_status' => isset($input['job_status']) ? $input['job_status'] : 'active'
    ];

    // Validate employment type
    $valid_employment_types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
    if (!in_array($job_data['employment_type'], $valid_employment_types)) {
        throw new Exception('Invalid employment type');
    }

    // Validate job status
    $valid_statuses = ['draft', 'active', 'paused', 'closed'];
    if (!in_array($job_data['job_status'], $valid_statuses)) {
        throw new Exception('Invalid job status');
    }

    // Validate application deadline (if provided)
    if ($job_data['application_deadline']) {
        $deadline_date = DateTime::createFromFormat('Y-m-d', $job_data['application_deadline']);
        if (!$deadline_date || $deadline_date < new DateTime()) {
            throw new Exception('Application deadline must be a future date');
        }
    }

    // Start transaction
    $conn->beginTransaction();

    // Insert job post
    $job_sql = "
        INSERT INTO job_posts (
            employer_id, job_title, department, location, employment_type,
            salary_range, application_deadline, job_description, job_requirements,
            remote_work_available, flexible_schedule, job_status, posted_at,
            created_at, updated_at
        ) VALUES (
            :employer_id, :job_title, :department, :location, :employment_type,
            :salary_range, :application_deadline, :job_description, :job_requirements,
            :remote_work_available, :flexible_schedule, :job_status, 
            :posted_at, NOW(), NOW()
        )
    ";

    $job_stmt = $conn->prepare($job_sql);
    
    // Set posted_at based on status
    $posted_at = ($job_data['job_status'] === 'active') ? date('Y-m-d H:i:s') : null;
    
    $job_params = array_merge($job_data, [
        'employer_id' => $employer_id,
        'posted_at' => $posted_at
    ]);

    $job_stmt->execute($job_params);
    $job_id = $conn->lastInsertId();

    // Insert accommodations if provided
    if (isset($input['accommodations']) && is_array($input['accommodations'])) {
        $accommodations = $input['accommodations'];
        
        $acc_sql = "
            INSERT INTO job_accommodations (
                job_id, wheelchair_accessible, assistive_technology, remote_work_option,
                screen_reader_compatible, sign_language_interpreter, modified_workspace,
                transportation_support, additional_accommodations, created_at, updated_at
            ) VALUES (
                :job_id, :wheelchair_accessible, :assistive_technology, :remote_work_option,
                :screen_reader_compatible, :sign_language_interpreter, :modified_workspace,
                :transportation_support, :additional_accommodations, NOW(), NOW()
            )
        ";

        $acc_stmt = $conn->prepare($acc_sql);
        $acc_stmt->execute([
            'job_id' => $job_id,
            'wheelchair_accessible' => isset($accommodations['wheelchair_accessible']) ? (bool)$accommodations['wheelchair_accessible'] : false,
            'assistive_technology' => isset($accommodations['assistive_technology']) ? (bool)$accommodations['assistive_technology'] : false,
            'remote_work_option' => isset($accommodations['remote_work_option']) ? (bool)$accommodations['remote_work_option'] : false,
            'screen_reader_compatible' => isset($accommodations['screen_reader_compatible']) ? (bool)$accommodations['screen_reader_compatible'] : false,
            'sign_language_interpreter' => isset($accommodations['sign_language_interpreter']) ? (bool)$accommodations['sign_language_interpreter'] : false,
            'modified_workspace' => isset($accommodations['modified_workspace']) ? (bool)$accommodations['modified_workspace'] : false,
            'transportation_support' => isset($accommodations['transportation_support']) ? (bool)$accommodations['transportation_support'] : false,
            'additional_accommodations' => isset($accommodations['additional_accommodations']) ? trim($accommodations['additional_accommodations']) : ''
        ]);
    }

    // Handle required skills if provided
    if (isset($input['required_skills']) && is_array($input['required_skills'])) {
        foreach ($input['required_skills'] as $skill_id) {
            if (is_numeric($skill_id)) {
                $skill_sql = "
                    INSERT INTO job_requirements (job_id, skill_id, is_required, experience_level)
                    VALUES (:job_id, :skill_id, 1, 'intermediate')
                ";
                $skill_stmt = $conn->prepare($skill_sql);
                $skill_stmt->execute([
                    'job_id' => $job_id,
                    'skill_id' => $skill_id
                ]);
            }
        }
    }

    // 🔥 ADD NOTIFICATION FOR EMPLOYER - Job Posted Successfully
    $notification_created = false;
    if ($job_data['job_status'] === 'active') {
        try {
            notify_job_posted($employer_id, $job_data['job_title'], $job_id);
            $notification_created = true;
        } catch (Exception $e) {
            // Don't fail the main operation if notification fails
            error_log("Notification failed in create_job: " . $e->getMessage());
        }
    }

    // Commit transaction
    $conn->commit();

    // Get the complete job data to return
    $complete_job = getCompleteJobData($job_id, $employer_id, $conn);

    // Return success response
    $response = [
        'success' => true,
        'data' => [
            'job' => $complete_job,
            'job_id' => $job_id
        ],
        'message' => $job_data['job_status'] === 'active' ? 'Job posted successfully and is now live!' : 'Job created successfully',
        'notification_created' => $notification_created,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(201);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Database error in create_job.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Unable to create job posting. Please try again.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Error in create_job.php: " . $e->getMessage());
    
    $status_code = ($e->getMessage() === 'Unauthorized access. Please log in.') ? 401 : 400;
    
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'debug' => [
            'session_data' => $_SESSION ?? [],
            'employer_id_exists' => isset($_SESSION['employer_id']),
            'logged_in_exists' => isset($_SESSION['logged_in']),
            'logged_in_value' => $_SESSION['logged_in'] ?? 'not set'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Unexpected error in create_job.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred',
        'message' => 'Please try again later or contact support.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

/**
 * Get complete job data after creation
 */
function getCompleteJobData($job_id, $employer_id, $conn) {
    $sql = "
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
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['job_id' => $job_id, 'employer_id' => $employer_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job) {
        // Format accommodations
        $accommodations = [
            'wheelchair_accessible' => (bool)$job['wheelchair_accessible'],
            'assistive_technology' => (bool)$job['assistive_technology'],
            'remote_work_option' => (bool)$job['remote_work_option'],
            'screen_reader_compatible' => (bool)$job['screen_reader_compatible'],
            'sign_language_interpreter' => (bool)$job['sign_language_interpreter'],
            'modified_workspace' => (bool)$job['modified_workspace'],
            'transportation_support' => (bool)$job['transportation_support'],
            'additional_accommodations' => $job['additional_accommodations'] ?? ''
        ];

        // Format dates
        $posted_at = $job['posted_at'] ? date('Y-m-d', strtotime($job['posted_at'])) : date('Y-m-d', strtotime($job['created_at']));
        $application_deadline = $job['application_deadline'] ? date('Y-m-d', strtotime($job['application_deadline'])) : null;

        return [
            'job_id' => $job['job_id'],
            'job_title' => $job['job_title'],
            'department' => $job['department'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'salary_range' => $job['salary_range'],
            'application_deadline' => $application_deadline,
            'job_description' => $job['job_description'],
            'job_requirements' => $job['job_requirements'],
            'remote_work_available' => (bool)$job['remote_work_available'],
            'flexible_schedule' => (bool)$job['flexible_schedule'],
            'job_status' => $job['job_status'],
            'posted_at' => $posted_at,
            'applications_count' => (int)$job['applications_count'],
            'views_count' => (int)$job['views_count'],
            'accommodations' => $accommodations,
            'company_name' => $_SESSION['company_name'] ?? 'Unknown Company'
        ];
    }
    
    return null;
}
?>