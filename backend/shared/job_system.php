<?php
/**
 * Shared Job System API
 * Core job operations used by both employers and candidates
 */

// Start session and include dependencies
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Handle different request methods
try {
    switch ($method) {
        case 'GET':
            handleGetRequest($conn, $action);
            break;
        case 'POST':
            handlePostRequest($conn, $action);
            break;
        case 'PUT':
            handlePutRequest($conn, $action);
            break;
        case 'DELETE':
            handleDeleteRequest($conn, $action);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Job System API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ]);
}

/**
 * Handle GET requests
 */
function handleGetRequest($conn, $action) {
    switch ($action) {
        case 'job_details':
            $job_id = $_GET['job_id'] ?? null;
            echo json_encode(getJobDetails($conn, $job_id));
            break;
            
        case 'job_accommodations':
            $job_id = $_GET['job_id'] ?? null;
            echo json_encode(getJobAccommodations($conn, $job_id));
            break;
            
        case 'job_requirements':
            $job_id = $_GET['job_id'] ?? null;
            echo json_encode(getJobRequirements($conn, $job_id));
            break;
            
        case 'job_applications':
            $job_id = $_GET['job_id'] ?? null;
            echo json_encode(getJobApplications($conn, $job_id));
            break;
            
        case 'application_details':
            $application_id = $_GET['application_id'] ?? null;
            echo json_encode(getApplicationDetails($conn, $application_id));
            break;
            
        case 'skills_list':
            echo json_encode(getSkillsList($conn));
            break;
            
        case 'industries_list':
            echo json_encode(getIndustriesList($conn));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($conn, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create_job':
            echo json_encode(createJob($conn, $input));
            break;
            
        case 'apply_to_job':
            echo json_encode(applyToJob($conn, $input));
            break;
            
        case 'schedule_interview':
            echo json_encode(scheduleInterview($conn, $input));
            break;
            
        case 'update_application_status':
            echo json_encode(updateApplicationStatus($conn, $input));
            break;
            
        case 'add_notification':
            echo json_encode(addNotification($conn, $input));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($conn, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update_job':
            echo json_encode(updateJob($conn, $input));
            break;
            
        case 'update_interview':
            echo json_encode(updateInterview($conn, $input));
            break;
            
        case 'mark_notification_read':
            echo json_encode(markNotificationRead($conn, $input));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($conn, $action) {
    switch ($action) {
        case 'delete_job':
            $job_id = $_GET['job_id'] ?? null;
            echo json_encode(deleteJob($conn, $job_id));
            break;
            
        case 'cancel_interview':
            $interview_id = $_GET['interview_id'] ?? null;
            echo json_encode(cancelInterview($conn, $interview_id));
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

/**
 * Get detailed job information
 */
function getJobDetails($conn, $job_id) {
    if (!$job_id) {
        return ['success' => false, 'message' => 'Job ID is required'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                jp.*,
                e.company_name,
                e.company_logo_path,
                i.industry_name,
                COUNT(ja.application_id) as total_applications
            FROM job_posts jp
            JOIN employers e ON jp.employer_id = e.employer_id
            LEFT JOIN industries i ON e.industry_id = i.industry_id
            LEFT JOIN job_applications ja ON jp.job_id = ja.job_id
            WHERE jp.job_id = :job_id
            GROUP BY jp.job_id
        ");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            return ['success' => false, 'message' => 'Job not found'];
        }
        
        // Get job accommodations
        $accommodations = getJobAccommodations($conn, $job_id)['data'] ?? [];
        
        // Get job requirements
        $requirements = getJobRequirements($conn, $job_id)['data'] ?? [];
        
        return [
            'success' => true,
            'data' => [
                'job_info' => $job,
                'accommodations' => $accommodations,
                'requirements' => $requirements
            ]
        ];
    } catch (Exception $e) {
        error_log("Get Job Details Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching job details'];
    }
}

/**
 * Get job accommodations
 */
function getJobAccommodations($conn, $job_id) {
    if (!$job_id) {
        return ['success' => false, 'message' => 'Job ID is required'];
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM job_accommodations WHERE job_id = :job_id");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        $accommodations = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $accommodations ?: []
        ];
    } catch (Exception $e) {
        error_log("Get Job Accommodations Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching accommodations'];
    }
}

/**
 * Get job requirements (skills)
 */
function getJobRequirements($conn, $job_id) {
    if (!$job_id) {
        return ['success' => false, 'message' => 'Job ID is required'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                jr.*,
                s.skill_name,
                s.skill_icon,
                sc.category_name
            FROM job_requirements jr
            JOIN skills s ON jr.skill_id = s.skill_id
            JOIN skill_categories sc ON s.category_id = sc.category_id
            WHERE jr.job_id = :job_id
            ORDER BY jr.is_required DESC, s.skill_name ASC
        ");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        $requirements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $requirements
        ];
    } catch (Exception $e) {
        error_log("Get Job Requirements Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching requirements'];
    }
}

/**
 * Get job applications
 */
function getJobApplications($conn, $job_id) {
    if (!$job_id) {
        return ['success' => false, 'message' => 'Job ID is required'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                ja.*,
                js.first_name,
                js.last_name,
                js.contact_number,
                js.city,
                js.province,
                dt.disability_name,
                dc.category_name as disability_category,
                r.file_name as resume_filename,
                r.file_path as resume_path
            FROM job_applications ja
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            JOIN disability_types dt ON js.disability_id = dt.disability_id
            JOIN disability_categories dc ON dt.category_id = dc.category_id
            LEFT JOIN resumes r ON ja.resume_id = r.resume_id
            WHERE ja.job_id = :job_id
            ORDER BY ja.applied_at DESC
        ");
        $stmt->bindParam(':job_id', $job_id);
        $stmt->execute();
        
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format applications
        $formattedApplications = array_map(function($app) {
            return [
                'application_id' => $app['application_id'],
                'seeker_id' => $app['seeker_id'],
                'full_name' => trim($app['first_name'] . ' ' . $app['last_name']),
                'initials' => getInitials($app['first_name'] . ' ' . $app['last_name']),
                'contact_number' => $app['contact_number'],
                'location' => trim($app['city'] . ', ' . $app['province']),
                'disability_name' => $app['disability_name'],
                'disability_category' => $app['disability_category'],
                'application_status' => $app['application_status'],
                'applied_at' => $app['applied_at'],
                'time_ago' => getTimeAgo($app['applied_at']),
                'cover_letter' => $app['cover_letter'],
                'employer_notes' => $app['employer_notes'],
                'resume_filename' => $app['resume_filename'],
                'resume_path' => $app['resume_path']
            ];
        }, $applications);
        
        return [
            'success' => true,
            'data' => $formattedApplications
        ];
    } catch (Exception $e) {
        error_log("Get Job Applications Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching applications'];
    }
}

/**
 * Get application details
 */
function getApplicationDetails($conn, $application_id) {
    if (!$application_id) {
        return ['success' => false, 'message' => 'Application ID is required'];
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                ja.*,
                js.first_name,
                js.last_name,
                js.contact_number,
                js.city,
                js.province,
                dt.disability_name,
                dc.category_name as disability_category,
                jp.job_title,
                jp.department,
                jp.location as job_location,
                e.company_name,
                r.file_name as resume_filename,
                r.file_path as resume_path
            FROM job_applications ja
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            JOIN disability_types dt ON js.disability_id = dt.disability_id
            JOIN disability_categories dc ON dt.category_id = dc.category_id
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN employers e ON jp.employer_id = e.employer_id
            LEFT JOIN resumes r ON ja.resume_id = r.resume_id
            WHERE ja.application_id = :application_id
        ");
        $stmt->bindParam(':application_id', $application_id);
        $stmt->execute();
        
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$application) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        
        // Get applicant skills
        $skillsStmt = $conn->prepare("
            SELECT s.skill_name, s.skill_icon, sc.category_name
            FROM seeker_skills ss
            JOIN skills s ON ss.skill_id = s.skill_id
            JOIN skill_categories sc ON s.category_id = sc.category_id
            WHERE ss.seeker_id = :seeker_id
        ");
        $skillsStmt->bindParam(':seeker_id', $application['seeker_id']);
        $skillsStmt->execute();
        $skills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get workplace accommodations
        $accommodationsStmt = $conn->prepare("
            SELECT * FROM workplace_accommodations WHERE seeker_id = :seeker_id
        ");
        $accommodationsStmt->bindParam(':seeker_id', $application['seeker_id']);
        $accommodationsStmt->execute();
        $accommodations = $accommodationsStmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'application' => $application,
                'skills' => $skills,
                'accommodations' => $accommodations
            ]
        ];
    } catch (Exception $e) {
        error_log("Get Application Details Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching application details'];
    }
}

/**
 * Create a new job posting
 */
function createJob($conn, $data) {
    // Validate required fields
    $required = ['employer_id', 'job_title', 'job_description', 'job_requirements', 'department', 'location', 'employment_type'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
        }
    }
    
    try {
        $conn->beginTransaction();
        
        // Insert job post
        $stmt = $conn->prepare("
            INSERT INTO job_posts (
                employer_id, job_title, job_description, job_requirements, 
                department, location, employment_type, salary_range, 
                application_deadline, remote_work_available, flexible_schedule,
                job_status, posted_at
            ) VALUES (
                :employer_id, :job_title, :job_description, :job_requirements,
                :department, :location, :employment_type, :salary_range,
                :application_deadline, :remote_work_available, :flexible_schedule,
                'active', NOW()
            )
        ");
        
        $stmt->execute([
            ':employer_id' => $data['employer_id'],
            ':job_title' => $data['job_title'],
            ':job_description' => $data['job_description'],
            ':job_requirements' => $data['job_requirements'],
            ':department' => $data['department'],
            ':location' => $data['location'],
            ':employment_type' => $data['employment_type'],
            ':salary_range' => $data['salary_range'] ?? null,
            ':application_deadline' => $data['application_deadline'] ?? null,
            ':remote_work_available' => $data['remote_work_available'] ?? 0,
            ':flexible_schedule' => $data['flexible_schedule'] ?? 0
        ]);
        
        $job_id = $conn->lastInsertId();
        
        // Insert job accommodations if provided
        if (isset($data['accommodations'])) {
            $acc = $data['accommodations'];
            $accStmt = $conn->prepare("
                INSERT INTO job_accommodations (
                    job_id, wheelchair_accessible, flexible_schedule, assistive_technology,
                    remote_work_option, screen_reader_compatible, sign_language_interpreter,
                    modified_workspace, transportation_support, additional_accommodations
                ) VALUES (
                    :job_id, :wheelchair_accessible, :flexible_schedule, :assistive_technology,
                    :remote_work_option, :screen_reader_compatible, :sign_language_interpreter,
                    :modified_workspace, :transportation_support, :additional_accommodations
                )
            ");
            
            $accStmt->execute([
                ':job_id' => $job_id,
                ':wheelchair_accessible' => $acc['wheelchair_accessible'] ?? 0,
                ':flexible_schedule' => $acc['flexible_schedule'] ?? 0,
                ':assistive_technology' => $acc['assistive_technology'] ?? 0,
                ':remote_work_option' => $acc['remote_work_option'] ?? 0,
                ':screen_reader_compatible' => $acc['screen_reader_compatible'] ?? 0,
                ':sign_language_interpreter' => $acc['sign_language_interpreter'] ?? 0,
                ':modified_workspace' => $acc['modified_workspace'] ?? 0,
                ':transportation_support' => $acc['transportation_support'] ?? 0,
                ':additional_accommodations' => $acc['additional_accommodations'] ?? null
            ]);
        }
        
        // Insert job requirements (skills) if provided
        if (isset($data['required_skills']) && is_array($data['required_skills'])) {
            $reqStmt = $conn->prepare("
                INSERT INTO job_requirements (job_id, skill_id, is_required, experience_level)
                VALUES (:job_id, :skill_id, :is_required, :experience_level)
            ");
            
            foreach ($data['required_skills'] as $skill) {
                $reqStmt->execute([
                    ':job_id' => $job_id,
                    ':skill_id' => $skill['skill_id'],
                    ':is_required' => $skill['is_required'] ?? 1,
                    ':experience_level' => $skill['experience_level'] ?? 'intermediate'
                ]);
            }
        }
        
        // Add notification for job posted
        addNotification($conn, [
            'recipient_type' => 'employer',
            'recipient_id' => $data['employer_id'],
            'type_name' => 'job_posted',
            'title' => 'Job Posted Successfully',
            'message' => 'Your job posting "' . $data['job_title'] . '" is now live and accepting applications.',
            'related_job_id' => $job_id
        ]);
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Job posted successfully',
            'data' => ['job_id' => $job_id]
        ];
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Create Job Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error creating job posting'];
    }
}

/**
 * Update application status
 */
function updateApplicationStatus($conn, $data) {
    $required = ['application_id', 'new_status'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
        }
    }
    
    try {
        $conn->beginTransaction();
        
        // Get current application details
        $stmt = $conn->prepare("
            SELECT ja.*, jp.job_title, jp.employer_id, js.first_name, js.last_name
            FROM job_applications ja
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            WHERE ja.application_id = :application_id
        ");
        $stmt->bindParam(':application_id', $data['application_id']);
        $stmt->execute();
        
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$application) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        
        $previous_status = $application['application_status'];
        
        // Update application status
        $updateStmt = $conn->prepare("
            UPDATE job_applications 
            SET application_status = :new_status, 
                employer_notes = :employer_notes,
                status_updated_at = NOW()
            WHERE application_id = :application_id
        ");
        
        $updateStmt->execute([
            ':new_status' => $data['new_status'],
            ':employer_notes' => $data['employer_notes'] ?? null,
            ':application_id' => $data['application_id']
        ]);
        
        // Add status history
        $historyStmt = $conn->prepare("
            INSERT INTO application_status_history 
            (application_id, previous_status, new_status, notes, changed_by_employer)
            VALUES (:application_id, :previous_status, :new_status, :notes, 1)
        ");
        
        $historyStmt->execute([
            ':application_id' => $data['application_id'],
            ':previous_status' => $previous_status,
            ':new_status' => $data['new_status'],
            ':notes' => $data['status_notes'] ?? null
        ]);
        
        // Add notification for candidate
        $statusMessages = [
            'under_review' => 'Your application is now under review.',
            'shortlisted' => 'Congratulations! You have been shortlisted.',
            'interview_scheduled' => 'An interview has been scheduled for your application.',
            'interviewed' => 'Thank you for the interview. We will get back to you soon.',
            'hired' => 'Congratulations! You have been selected for the position.',
            'rejected' => 'Thank you for your interest. Unfortunately, we have decided to move forward with other candidates.'
        ];
        
        $message = $statusMessages[$data['new_status']] ?? 'Your application status has been updated.';
        
        addNotification($conn, [
            'recipient_type' => 'candidate',
            'recipient_id' => $application['seeker_id'],
            'type_name' => 'application_status',
            'title' => 'Application Status Updated',
            'message' => $message . ' - ' . $application['job_title'],
            'related_application_id' => $data['application_id']
        ]);
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Application status updated successfully'
        ];
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Update Application Status Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error updating application status'];
    }
}

/**
 * Schedule an interview
 */
function scheduleInterview($conn, $data) {
    $required = ['application_id', 'interview_type', 'scheduled_date', 'scheduled_time'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
        }
    }
    
    try {
        $conn->beginTransaction();
        
        // Insert interview
        $stmt = $conn->prepare("
            INSERT INTO interviews (
                application_id, interview_type, scheduled_date, scheduled_time,
                duration_minutes, interview_platform, meeting_link, meeting_id,
                location_address, interviewer_notes, accommodations_needed,
                sign_language_interpreter, wheelchair_accessible_venue, screen_reader_materials
            ) VALUES (
                :application_id, :interview_type, :scheduled_date, :scheduled_time,
                :duration_minutes, :interview_platform, :meeting_link, :meeting_id,
                :location_address, :interviewer_notes, :accommodations_needed,
                :sign_language_interpreter, :wheelchair_accessible_venue, :screen_reader_materials
            )
        ");
        
        $stmt->execute([
            ':application_id' => $data['application_id'],
            ':interview_type' => $data['interview_type'],
            ':scheduled_date' => $data['scheduled_date'],
            ':scheduled_time' => $data['scheduled_time'],
            ':duration_minutes' => $data['duration_minutes'] ?? 60,
            ':interview_platform' => $data['interview_platform'] ?? null,
            ':meeting_link' => $data['meeting_link'] ?? null,
            ':meeting_id' => $data['meeting_id'] ?? null,
            ':location_address' => $data['location_address'] ?? null,
            ':interviewer_notes' => $data['interviewer_notes'] ?? null,
            ':accommodations_needed' => $data['accommodations_needed'] ?? null,
            ':sign_language_interpreter' => $data['sign_language_interpreter'] ?? 0,
            ':wheelchair_accessible_venue' => $data['wheelchair_accessible_venue'] ?? 0,
            ':screen_reader_materials' => $data['screen_reader_materials'] ?? 0
        ]);
        
        $interview_id = $conn->lastInsertId();
        
        // Update application status to interview_scheduled
        $updateStmt = $conn->prepare("
            UPDATE job_applications 
            SET application_status = 'interview_scheduled'
            WHERE application_id = :application_id
        ");
        $updateStmt->bindParam(':application_id', $data['application_id']);
        $updateStmt->execute();
        
        // Get application details for notifications
        $appStmt = $conn->prepare("
            SELECT ja.seeker_id, jp.job_title, jp.employer_id, js.first_name, js.last_name
            FROM job_applications ja
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            WHERE ja.application_id = :application_id
        ");
        $appStmt->bindParam(':application_id', $data['application_id']);
        $appStmt->execute();
        $appDetails = $appStmt->fetch(PDO::FETCH_ASSOC);
        
        // Add notifications
        if ($appDetails) {
            // Notification for candidate
            addNotification($conn, [
                'recipient_type' => 'candidate',
                'recipient_id' => $appDetails['seeker_id'],
                'type_name' => 'interview_scheduled',
                'title' => 'Interview Scheduled',
                'message' => 'Your interview for ' . $appDetails['job_title'] . ' has been scheduled for ' . 
                           date('F j, Y', strtotime($data['scheduled_date'])) . ' at ' . 
                           date('g:i A', strtotime($data['scheduled_time'])),
                'related_interview_id' => $interview_id
            ]);
            
            // Notification for employer
            addNotification($conn, [
                'recipient_type' => 'employer',
                'recipient_id' => $appDetails['employer_id'],
                'type_name' => 'interview_scheduled',
                'title' => 'Interview Scheduled',
                'message' => 'Interview scheduled with ' . $appDetails['first_name'] . ' ' . $appDetails['last_name'] . 
                           ' for ' . $appDetails['job_title'],
                'related_interview_id' => $interview_id
            ]);
        }
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Interview scheduled successfully',
            'data' => ['interview_id' => $interview_id]
        ];
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Schedule Interview Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error scheduling interview'];
    }
}

/**
 * Add notification
 */
function addNotification($conn, $data) {
    try {
        // Get notification type ID
        $typeStmt = $conn->prepare("SELECT type_id FROM notification_types WHERE type_name = :type_name");
        $typeStmt->bindParam(':type_name', $data['type_name']);
        $typeStmt->execute();
        $type = $typeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$type) {
            return ['success' => false, 'message' => 'Invalid notification type'];
        }
        
        // Insert notification
        $stmt = $conn->prepare("
            INSERT INTO notifications (
                recipient_type, recipient_id, type_id, title, message,
                related_job_id, related_application_id, related_interview_id
            ) VALUES (
                :recipient_type, :recipient_id, :type_id, :title, :message,
                :related_job_id, :related_application_id, :related_interview_id
            )
        ");
        
        $stmt->execute([
            ':recipient_type' => $data['recipient_type'],
            ':recipient_id' => $data['recipient_id'],
            ':type_id' => $type['type_id'],
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':related_job_id' => $data['related_job_id'] ?? null,
            ':related_application_id' => $data['related_application_id'] ?? null,
            ':related_interview_id' => $data['related_interview_id'] ?? null
        ]);
        
        return ['success' => true, 'message' => 'Notification added successfully'];
    } catch (Exception $e) {
        error_log("Add Notification Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error adding notification'];
    }
}

/**
 * Get skills list
 */
function getSkillsList($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT s.*, sc.category_name
            FROM skills s
            JOIN skill_categories sc ON s.category_id = sc.category_id
            ORDER BY sc.category_name, s.skill_name
        ");
        $stmt->execute();
        
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $skills];
    } catch (Exception $e) {
        error_log("Get Skills List Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching skills'];
    }
}

/**
 * Get industries list
 */
function getIndustriesList($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM industries ORDER BY industry_name");
        $stmt->execute();
        
        $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $industries];
    } catch (Exception $e) {
        error_log("Get Industries List Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error fetching industries'];
    }
}

/**
 * Mark notification as read
 */
function markNotificationRead($conn, $data) {
    try {
        $stmt = $conn->prepare("
            UPDATE notifications 
            SET is_read = 1, read_at = NOW() 
            WHERE notification_id = :notification_id
        ");
        $stmt->bindParam(':notification_id', $data['notification_id']);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Notification marked as read'];
    } catch (Exception $e) {
        error_log("Mark Notification Read Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error updating notification'];
    }
}

/**
 * Helper function to get time ago string
 */
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    $time = ($time < 1) ? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
    
    return 'just now';
}

/**
 * Helper function to get initials from name
 */
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return substr($initials, 0, 2);
}
?>