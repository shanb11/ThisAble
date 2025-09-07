<?php
/**
 * COMPLETELY FIXED: Get Application Details API for ThisAble Mobile
 * ALL database column names corrected to match your actual schema
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_application_details.php
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
    
    // Get application ID from query parameter
    $applicationId = $_GET['application_id'] ?? null;
    
    if (!$applicationId) {
        ApiResponse::error("Application ID is required", 400);
    }
    
    error_log("Application Details API: seeker_id=$seekerId, application_id=$applicationId");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== COMPLETELY FIXED SQL QUERY - MATCHES YOUR EXACT DATABASE SCHEMA =====
    $stmt = $conn->prepare("
        SELECT 
            -- Application details (ALL CORRECT)
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            ja.status_updated_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.candidate_notes,
            ja.match_score,
            ja.skills_matched,
            ja.skills_missing,
            ja.accommodation_compatibility,
            ja.rejection_reason,
            ja.last_activity,
            
            -- Job details (FIXED: Using actual column names from your database)
            jp.job_title,
            jp.job_description,
            jp.job_requirements,
            jp.location,
            jp.employment_type,
            jp.salary_range,
            jp.posted_at,
            jp.application_deadline,
            jp.job_status,
            jp.department,
            jp.remote_work_available,
            jp.flexible_schedule,
            jp.min_experience_years,
            
            -- Company details (FIXED: Using actual column names)
            e.employer_id,
            e.company_name,
            e.company_description,
            e.industry,
            e.company_size,
            e.company_website,
            e.company_logo_path,
            e.mission_vision,
            e.why_join_us,
            
            -- HR Contact details (ALL CORRECT)
            ec.first_name as hr_first_name,
            ec.last_name as hr_last_name,
            ec.position as hr_position,
            ec.contact_number as hr_phone,
            ec.email as hr_email
            
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
        WHERE ja.application_id = ? AND ja.seeker_id = ?
    ");
    
    $stmt->execute([$applicationId, $seekerId]);
    $applicationData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$applicationData) {
        ApiResponse::error("Application not found or access denied", 404);
    }
    
    // ===== STEP 2: GET APPLICATION STATUS HISTORY/TIMELINE =====
    $stmt = $conn->prepare("
        SELECT 
            history_id,
            previous_status,
            new_status,
            changed_by_employer,
            notes,
            changed_at
        FROM application_status_history 
        WHERE application_id = ?
        ORDER BY changed_at ASC
    ");
    
    $stmt->execute([$applicationId]);
    $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ===== STEP 3: CHECK IF INTERVIEW IS SCHEDULED =====
    $stmt = $conn->prepare("
        SELECT 
            interview_id,
            interview_type,
            scheduled_date,
            scheduled_time,
            duration_minutes,
            interview_platform,
            meeting_link,
            location_address,
            interview_status,
            accommodations_needed
        FROM interviews 
        WHERE application_id = ?
        ORDER BY scheduled_date DESC, scheduled_time DESC
        LIMIT 1
    ");
    
    $stmt->execute([$applicationId]);
    $interviewData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // ===== STEP 4: BUILD COMPREHENSIVE RESPONSE =====
    
    // FIXED: Handle salary correctly (single salary_range field)
    $salaryDisplay = 'Not specified';
    if (!empty($applicationData['salary_range'])) {
        $salary = $applicationData['salary_range'];
        // If it's just a number, format it nicely
        if (is_numeric($salary)) {
            $salaryDisplay = "₱" . number_format($salary);
        } else {
            // If it already contains currency or text, use as-is
            $salaryDisplay = $salary;
        }
    }
    
    // Format HR contact
    $hrContact = null;
    if ($applicationData['hr_first_name']) {
        $hrContact = [
            'name' => trim($applicationData['hr_first_name'] . ' ' . ($applicationData['hr_last_name'] ?? '')),
            'position' => $applicationData['hr_position'] ?? 'HR Representative',
            'email' => $applicationData['hr_email'],
            'phone' => $applicationData['hr_phone']
        ];
    }
    
    // Build timeline
    $timeline = [];
    foreach ($statusHistory as $history) {
        $timeline[] = [
            'status' => $history['new_status'],
            'date' => $history['changed_at'],
            'notes' => $history['notes'],
            'by_employer' => (bool)$history['changed_by_employer']
        ];
    }
    
    // Determine next steps based on current status
    $nextSteps = _getNextSteps($applicationData['application_status'], $interviewData);
    
    // FIXED: Build response using actual database field names
    $response = [
        'application' => [
            'application_id' => $applicationData['application_id'],
            'status' => $applicationData['application_status'],
            'applied_date' => $applicationData['applied_at'],
            'last_updated' => $applicationData['status_updated_at'],
            'match_score' => $applicationData['match_score'],
            'cover_letter' => $applicationData['cover_letter'],
            'employer_notes' => $applicationData['employer_notes'],
            'candidate_notes' => $applicationData['candidate_notes'],
            'rejection_reason' => $applicationData['rejection_reason']
        ],
        'job' => [
            'job_id' => $applicationData['job_id'],
            'title' => $applicationData['job_title'],
            'description' => $applicationData['job_description'],
            'requirements' => $applicationData['job_requirements'],
            'location' => $applicationData['location'],
            'employment_type' => $applicationData['employment_type'],
            'salary' => $salaryDisplay,
            'department' => $applicationData['department'],
            'posted_date' => $applicationData['posted_at'],
            'deadline' => $applicationData['application_deadline'],
            'status' => $applicationData['job_status'],
            'remote_work_available' => (bool)$applicationData['remote_work_available'],
            'flexible_schedule' => (bool)$applicationData['flexible_schedule'],
            'min_experience_years' => $applicationData['min_experience_years']
        ],
        'company' => [
            'employer_id' => $applicationData['employer_id'],
            'name' => $applicationData['company_name'],
            'description' => $applicationData['company_description'],
            'industry' => $applicationData['industry'],
            'size' => $applicationData['company_size'],
            'website' => $applicationData['company_website'], // FIXED: was website_url
            'logo' => $applicationData['company_logo_path'],
            'mission_vision' => $applicationData['mission_vision'],
            'why_join_us' => $applicationData['why_join_us']
        ],
        'contact' => $hrContact,
        'interview' => $interviewData ? [
            'interview_id' => $interviewData['interview_id'],
            'type' => $interviewData['interview_type'],
            'date' => $interviewData['scheduled_date'],
            'time' => $interviewData['scheduled_time'],
            'duration' => $interviewData['duration_minutes'],
            'platform' => $interviewData['interview_platform'],
            'meeting_link' => $interviewData['meeting_link'],
            'location' => $interviewData['location_address'],
            'status' => $interviewData['interview_status'],
            'accommodations' => $interviewData['accommodations_needed']
        ] : null,
        'timeline' => $timeline,
        'next_steps' => $nextSteps,
        'skills' => [
            'matched' => $applicationData['skills_matched'] ? json_decode($applicationData['skills_matched'], true) : [],
            'missing' => $applicationData['skills_missing'] ? json_decode($applicationData['skills_missing'], true) : []
        ],
        
        // FIXED: Add flat fields that your Flutter modal expects
        'job_description' => $applicationData['job_description'],
        'job_requirements' => $applicationData['job_requirements'],
        'hr_first_name' => $applicationData['hr_first_name'],
        'hr_last_name' => $applicationData['hr_last_name'],
        'hr_position' => $applicationData['hr_position'],
        'hr_email' => $applicationData['hr_email'],
        'hr_phone' => $applicationData['hr_phone'],
        'salary_range' => $salaryDisplay,
        'company_website' => $applicationData['company_website'], // FIXED: field name
        'status_updated_at' => $applicationData['status_updated_at']
    ];
    
    error_log("Application Details API: Successfully retrieved details for application $applicationId");
    
    ApiResponse::success($response, "Application details retrieved successfully");
    
} catch (Exception $e) {
    error_log("Application Details API Error: " . $e->getMessage());
    ApiResponse::error("Failed to retrieve application details: " . $e->getMessage(), 500);
}

/**
 * Get next steps based on application status
 */
function _getNextSteps($status, $interviewData) {
    switch ($status) {
        case 'submitted':
            return [
                'message' => 'Your application is being reviewed by the employer.',
                'action' => 'Wait for employer response',
                'estimated_time' => '1-2 weeks'
            ];
            
        case 'under_review':
            return [
                'message' => 'Your application is currently under review.',
                'action' => 'Employer is evaluating your qualifications',
                'estimated_time' => '3-5 business days'
            ];
            
        case 'interview_scheduled':
            if ($interviewData) {
                return [
                    'message' => 'Congratulations! Your interview has been scheduled.',
                    'action' => 'Prepare for your interview on ' . date('M j, Y', strtotime($interviewData['scheduled_date'])),
                    'estimated_time' => 'Interview upcoming'
                ];
            } else {
                return [
                    'message' => 'An interview will be scheduled soon.',
                    'action' => 'Wait for interview details',
                    'estimated_time' => '1-3 days'
                ];
            }
            
        case 'hired':
            return [
                'message' => 'Congratulations! You have been selected for this position.',
                'action' => 'Check your email for next steps',
                'estimated_time' => 'Immediate'
            ];
            
        case 'rejected':
            return [
                'message' => 'Unfortunately, you were not selected for this position.',
                'action' => 'Continue applying to other opportunities',
                'estimated_time' => 'N/A'
            ];
            
        case 'withdrawn':
            return [
                'message' => 'You have withdrawn your application.',
                'action' => 'Application is no longer active',
                'estimated_time' => 'N/A'
            ];
            
        default:
            return [
                'message' => 'Your application status will be updated soon.',
                'action' => 'Wait for updates',
                'estimated_time' => 'Unknown'
            ];
    }
}
?>