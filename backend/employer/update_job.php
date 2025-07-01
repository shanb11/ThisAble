<?php
/**
 * Update Job API
 * Updates an existing job posting with accommodations
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Allow both PUT and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'Only PUT/POST requests are allowed'
    ]);
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
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    // Get job ID
    $job_id = isset($input['job_id']) ? intval($input['job_id']) : 0;
    
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
        'flexible_schedule' => isset($input['flexible_schedule']) ? (bool)$input['flexible_schedule'] : false
    ];

    // Validate employment type
    $valid_employment_types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Freelance'];
    if (!in_array($job_data['employment_type'], $valid_employment_types)) {
        throw new Exception('Invalid employment type');
    }

    // Validate application deadline (if provided)
    if ($job_data['application_deadline']) {
        $deadline_date = DateTime::createFromFormat('Y-m-d', $job_data['application_deadline']);
        if (!$deadline_date) {
            throw new Exception('Invalid application deadline format');
        }
        // Allow past dates for editing (in case deadline was already set)
    }

    // Start transaction
    $conn->beginTransaction();

    // Update job post
    $job_sql = "
        UPDATE job_posts SET
            job_title = :job_title,
            department = :department,
            location = :location,
            employment_type = :employment_type,
            salary_range = :salary_range,
            application_deadline = :application_deadline,
            job_description = :job_description,
            job_requirements = :job_requirements,
            remote_work_available = :remote_work_available,
            flexible_schedule = :flexible_schedule,
            updated_at = NOW()
        WHERE job_id = :job_id AND employer_id = :employer_id
    ";

    $job_stmt = $conn->prepare($job_sql);
    $job_params = array_merge($job_data, [
        'job_id' => $job_id,
        'employer_id' => $employer_id
    ]);

    $job_stmt->execute($job_params);

    // Check if job was actually updated
    if ($job_stmt->rowCount() == 0) {
        throw new Exception('No changes were made or job not found.');
    }

    // Update accommodations if provided
    if (isset($input['accommodations']) && is_array($input['accommodations'])) {
        $accommodations = $input['accommodations'];
        
        // Check if accommodations record exists
        $acc_check_sql = "SELECT COUNT(*) as count FROM job_accommodations WHERE job_id = :job_id";
        $acc_check_stmt = $conn->prepare($acc_check_sql);
        $acc_check_stmt->execute(['job_id' => $job_id]);
        $acc_exists = $acc_check_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

        $acc_data = [
            'job_id' => $job_id,
            'wheelchair_accessible' => isset($accommodations['wheelchair_accessible']) ? (bool)$accommodations['wheelchair_accessible'] : false,
            'assistive_technology' => isset($accommodations['assistive_technology']) ? (bool)$accommodations['assistive_technology'] : false,
            'remote_work_option' => isset($accommodations['remote_work_option']) ? (bool)$accommodations['remote_work_option'] : false,
            'screen_reader_compatible' => isset($accommodations['screen_reader_compatible']) ? (bool)$accommodations['screen_reader_compatible'] : false,
            'sign_language_interpreter' => isset($accommodations['sign_language_interpreter']) ? (bool)$accommodations['sign_language_interpreter'] : false,
            'modified_workspace' => isset($accommodations['modified_workspace']) ? (bool)$accommodations['modified_workspace'] : false,
            'transportation_support' => isset($accommodations['transportation_support']) ? (bool)$accommodations['transportation_support'] : false,
            'additional_accommodations' => isset($accommodations['additional_accommodations']) ? trim($accommodations['additional_accommodations']) : ''
        ];

        if ($acc_exists) {
            // Update existing accommodations
            $acc_sql = "
                UPDATE job_accommodations SET
                    wheelchair_accessible = :wheelchair_accessible,
                    assistive_technology = :assistive_technology,
                    remote_work_option = :remote_work_option,
                    screen_reader_compatible = :screen_reader_compatible,
                    sign_language_interpreter = :sign_language_interpreter,
                    modified_workspace = :modified_workspace,
                    transportation_support = :transportation_support,
                    additional_accommodations = :additional_accommodations,
                    updated_at = NOW()
                WHERE job_id = :job_id
            ";
        } else {
            // Insert new accommodations
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
        }

        $acc_stmt = $conn->prepare($acc_sql);
        $acc_stmt->execute($acc_data);
    }

    // Handle required skills update if provided
    if (isset($input['required_skills']) && is_array($input['required_skills'])) {
        // Delete existing skills
        $delete_skills_sql = "DELETE FROM job_requirements WHERE job_id = :job_id";
        $delete_skills_stmt = $conn->prepare($delete_skills_sql);
        $delete_skills_stmt->execute(['job_id' => $job_id]);

        // Insert new skills
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

    // Commit transaction
    $conn->commit();

    // Get the updated job data
    $updated_job = getUpdatedJobData($job_id, $employer_id, $conn);

    // Return success response
    $response = [
        'success' => true,
        'data' => [
            'job' => $updated_job,
            'job_id' => $job_id
        ],
        'message' => 'Job updated successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Database error in update_job.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'message' => 'Unable to update job posting. Please try again.',
        'debug' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Error in update_job.php: " . $e->getMessage());
    
    $status_code = ($e->getMessage() === 'Unauthorized access. Please log in.') ? 401 : 400;
    
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Unexpected error in update_job.php: " . $e->getMessage());
    
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
 * Get updated job data after modification
 */
function getUpdatedJobData($job_id, $employer_id, $conn) {
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
            'company_name' => $_SESSION['company_name'] ?? 'Company'
        ];
    }
    
    return null;
}
?>