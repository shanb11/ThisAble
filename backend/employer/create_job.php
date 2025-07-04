<?php
/**
 * Enhanced Create Job API
 * Creates job with structured skills in job_requirements table
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed',
        'message' => 'Only POST requests are allowed'
    ]);
    exit();
}

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once('../db.php');
require_once('session_check.php');

function combineRequirements($experience_requirements, $other_requirements) {
    $combined = [];
    
    if (!empty($experience_requirements) && $experience_requirements !== 'No specific requirement') {
        $combined[] = "Experience: " . $experience_requirements;
    }
    
    if (!empty($other_requirements)) {
        $combined[] = trim($other_requirements);
    }
    
    return implode("\n", $combined);
}

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['job_title', 'department', 'location', 'employment_type', 'job_description'];
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
        'job_requirements' => combineRequirements(
            isset($input['experience_requirements']) ? trim($input['experience_requirements']) : '',
            isset($input['job_requirements']) ? trim($input['job_requirements']) : ''
        ),
        'remote_work_available' => isset($input['remote_work_available']) ? (bool)$input['remote_work_available'] : false,
        'flexible_schedule' => isset($input['flexible_schedule']) ? (bool)$input['flexible_schedule'] : false,
        'job_status' => isset($input['job_status']) ? $input['job_status'] : 'active'
    ];
    
    // Get selected skills (new feature)
    $selected_skills = isset($input['required_skills']) ? $input['required_skills'] : [];
    
    // Get accommodations data
    $accommodations = isset($input['accommodations']) ? $input['accommodations'] : [];
    
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
    
    $job_params = [
        'employer_id' => $employer_id,
        'job_title' => $job_data['job_title'],
        'department' => $job_data['department'],
        'location' => $job_data['location'],
        'employment_type' => $job_data['employment_type'],
        'salary_range' => $job_data['salary_range'],
        'application_deadline' => $job_data['application_deadline'],
        'job_description' => $job_data['job_description'],
        'job_requirements' => $job_data['job_requirements'],
        'remote_work_available' => $job_data['remote_work_available'],
        'flexible_schedule' => $job_data['flexible_schedule'],
        'job_status' => $job_data['job_status'],
        'posted_at' => $posted_at
    ];
    
    $job_stmt->execute($job_params);
    $job_id = $conn->lastInsertId();
    
    // Insert structured skills into job_requirements table
    if (!empty($selected_skills)) {
        $skills_sql = "
            INSERT INTO job_requirements (job_id, skill_id, is_required, priority)
            VALUES (:job_id, :skill_id, :is_required, :priority)
        ";
        $skills_stmt = $conn->prepare($skills_sql);
        
        foreach ($selected_skills as $skill) {
            $skills_params = [
                'job_id' => $job_id,
                'skill_id' => $skill['skill_id'],
                'is_required' => isset($skill['is_required']) ? (bool)$skill['is_required'] : true,
                'priority' => isset($skill['priority']) ? $skill['priority'] : 'important'
            ];
            
            $skills_stmt->execute($skills_params);
        }
    }
    
    // Insert job accommodations
    if (!empty($accommodations)) {
        $accommodations_sql = "
            INSERT INTO job_accommodations (
                job_id, wheelchair_accessible, assistive_technology, 
                remote_work_option, screen_reader_compatible, 
                sign_language_interpreter, modified_workspace, 
                transportation_support, additional_accommodations
            ) VALUES (
                :job_id, :wheelchair_accessible, :assistive_technology,
                :remote_work_option, :screen_reader_compatible,
                :sign_language_interpreter, :modified_workspace,
                :transportation_support, :additional_accommodations
            )
        ";
        
        $accommodations_stmt = $conn->prepare($accommodations_sql);
        $accommodations_params = [
            'job_id' => $job_id,
            'wheelchair_accessible' => isset($accommodations['wheelchair_accessible']) ? (bool)$accommodations['wheelchair_accessible'] : false,
            'assistive_technology' => isset($accommodations['assistive_technology']) ? (bool)$accommodations['assistive_technology'] : false,
            'remote_work_option' => isset($accommodations['remote_work_option']) ? (bool)$accommodations['remote_work_option'] : false,
            'screen_reader_compatible' => isset($accommodations['screen_reader_compatible']) ? (bool)$accommodations['screen_reader_compatible'] : false,
            'sign_language_interpreter' => isset($accommodations['sign_language_interpreter']) ? (bool)$accommodations['sign_language_interpreter'] : false,
            'modified_workspace' => isset($accommodations['modified_workspace']) ? (bool)$accommodations['modified_workspace'] : false,
            'transportation_support' => isset($accommodations['transportation_support']) ? (bool)$accommodations['transportation_support'] : false,
            'additional_accommodations' => isset($accommodations['additional_accommodations']) ? trim($accommodations['additional_accommodations']) : null
        ];
        
        $accommodations_stmt->execute($accommodations_params);
    }
    
    // Commit transaction
    $conn->commit();
    
    // Fetch complete job data for response
    $complete_job_sql = "
        SELECT 
            jp.*,
            ja.wheelchair_accessible,
            ja.assistive_technology,
            ja.remote_work_option,
            ja.screen_reader_compatible,
            ja.sign_language_interpreter,
            ja.modified_workspace,
            ja.transportation_support,
            ja.additional_accommodations
        FROM job_posts jp
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        WHERE jp.job_id = :job_id
    ";
    
    $complete_stmt = $conn->prepare($complete_job_sql);
    $complete_stmt->execute(['job_id' => $job_id]);
    $complete_job = $complete_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get selected skills for response
    $job_skills_sql = "
        SELECT 
            jr.skill_id,
            jr.is_required,
            jr.priority,
            s.skill_name,
            sc.category_name
        FROM job_requirements jr
        JOIN skills s ON jr.skill_id = s.skill_id
        JOIN skill_categories sc ON s.category_id = sc.category_id
        WHERE jr.job_id = :job_id
        ORDER BY sc.category_name, s.skill_name
    ";
    
    $job_skills_stmt = $conn->prepare($job_skills_sql);
    $job_skills_stmt->execute(['job_id' => $job_id]);
    $job_skills = $job_skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format response data
    $response_data = [
        'job' => $complete_job,
        'selected_skills' => $job_skills,
        'skills_count' => count($job_skills),
        'accommodations' => [
            'wheelchair_accessible' => (bool)$complete_job['wheelchair_accessible'],
            'assistive_technology' => (bool)$complete_job['assistive_technology'],
            'remote_work_option' => (bool)$complete_job['remote_work_option'],
            'screen_reader_compatible' => (bool)$complete_job['screen_reader_compatible'],
            'sign_language_interpreter' => (bool)$complete_job['sign_language_interpreter'],
            'modified_workspace' => (bool)$complete_job['modified_workspace'],
            'transportation_support' => (bool)$complete_job['transportation_support'],
            'additional_accommodations' => $complete_job['additional_accommodations']
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'message' => "Job posted successfully with " . count($job_skills) . " skills",
        'data' => $response_data,
        'job_id' => $job_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn && $conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Create job error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => 'Failed to create job',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>