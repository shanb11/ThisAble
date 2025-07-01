<?php
// backend/candidate/get_application_details.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$seeker_id = get_seeker_id();
$application_id = $_GET['application_id'] ?? 0;

if (!$application_id) {
    echo json_encode(['success' => false, 'error' => 'Application ID required']);
    exit;
}

try {
    // Get detailed application information with proper company join
    $appQuery = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            ja.application_status,
            ja.applied_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.candidate_notes,
            ja.resume_id,
            jp.job_title,
            jp.employer_id,
            jp.employment_type,
            jp.location AS job_location,
            jp.salary_range,
            jp.job_description,
            jp.job_requirements,
            e.company_name,
            e.company_logo_path,
            r.file_path AS resume_path,
            r.file_name AS resume_filename,
            r.file_type AS resume_type
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN resumes r ON ja.resume_id = r.resume_id
        WHERE ja.application_id = :application_id 
        AND ja.seeker_id = :seeker_id
    ";
    
    $appStmt = $conn->prepare($appQuery);
    $appStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $appStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $appStmt->execute();
    $application = $appStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode(['success' => false, 'error' => 'Application not found']);
        exit;
    }
    
    // Get application status history timeline
    $timelineQuery = "
        SELECT 
            ash.previous_status,
            ash.new_status,
            ash.changed_by_employer,
            ash.notes,
            ash.changed_at
        FROM application_status_history ash
        WHERE ash.application_id = :application_id
        ORDER BY ash.changed_at ASC
    ";
    
    $timelineStmt = $conn->prepare($timelineQuery);
    $timelineStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $timelineStmt->execute();
    $timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get interview information - FIXED: Changed 'if' to 'ifb' alias
    $interviewQuery = "
        SELECT 
            i.*,
            ifb.technical_score,
            ifb.communication_score,
            ifb.cultural_fit_score,
            ifb.overall_rating,
            ifb.strengths,
            ifb.areas_for_improvement,
            ifb.recommendation,
            ifb.detailed_feedback
        FROM interviews i
        LEFT JOIN interview_feedback ifb ON i.interview_id = ifb.interview_id
        WHERE i.application_id = :application_id
        ORDER BY i.scheduled_date DESC, i.scheduled_time DESC
    ";
    
    $interviewStmt = $conn->prepare($interviewQuery);
    $interviewStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $interviewStmt->execute();
    $interviews = $interviewStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format timeline for frontend
    $timeline = [];
    foreach ($timelineData as $event) {
        $timeline[] = [
            'date' => formatTimelineDate($event['changed_at']),
            'event' => formatStatusEvent($event['new_status'], $event['changed_by_employer']),
            'description' => generateEventDescription(
                $event['new_status'], 
                $event['notes'], 
                $application['job_title'],
                $application['company_name']
            ),
            'icon' => getStatusIcon($event['new_status']),
            'status' => $event['new_status']
        ];
    }
    
    // Add interview events to timeline
    foreach ($interviews as $interview) {
        $interviewDate = $interview['scheduled_date'] . ' ' . $interview['scheduled_time'];
        
        // Add interview scheduled event
        $timeline[] = [
            'date' => formatTimelineDate($interviewDate),
            'event' => 'Interview Scheduled',
            'description' => "Interview scheduled for " . formatInterviewDateTime($interview['scheduled_date'], $interview['scheduled_time']) . " via " . ucfirst($interview['interview_type']),
            'icon' => 'fa-calendar-alt',
            'status' => 'interview_scheduled',
            'interview_details' => [
                'type' => $interview['interview_type'],
                'date' => $interview['scheduled_date'],
                'time' => $interview['scheduled_time'],
                'duration' => $interview['duration_minutes'] ?? 60,
                'platform' => $interview['interview_platform'],
                'meeting_link' => $interview['meeting_link'],
                'location' => $interview['location_address'],
                'status' => $interview['interview_status'],
                'accommodations' => $interview['accommodations_needed']
            ]
        ];
        
        // Add interview feedback if completed
        if ($interview['interview_status'] === 'completed' && !empty($interview['overall_rating'])) {
            $timeline[] = [
                'date' => formatTimelineDate($interview['updated_at']),
                'event' => 'Interview Completed',
                'description' => "Interview completed with overall rating of {$interview['overall_rating']}/5",
                'icon' => 'fa-star',
                'status' => 'interviewed',
                'feedback' => [
                    'technical_score' => $interview['technical_score'],
                    'communication_score' => $interview['communication_score'],
                    'cultural_fit_score' => $interview['cultural_fit_score'],
                    'overall_rating' => $interview['overall_rating'],
                    'strengths' => $interview['strengths'],
                    'areas_for_improvement' => $interview['areas_for_improvement'],
                    'recommendation' => $interview['recommendation'],
                    'detailed_feedback' => $interview['detailed_feedback']
                ]
            ];
        }
    }
    
    // Sort timeline by date
    usort($timeline, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    // Calculate progress percentage
    $progress = calculateProgress($application['application_status']);
    
    // Format the detailed application data
    $applicationDetails = [
        'id' => $application['application_id'],
        'jobTitle' => $application['job_title'],
        'company' => $application['company_name'],
        'logo' => generateCompanyLogo($application['company_name']),
        'location' => $application['job_location'],
        'type' => $application['employment_type'],
        'salary' => $application['salary_range'] ?: '',
        'dateApplied' => formatApplicationDate($application['applied_at']),
        'status' => mapDatabaseStatus($application['application_status']),
        'progress' => $progress,
        'details' => [
            'description' => generateJobDescription($application),
            'requirements' => generateJobRequirements($application),
            'contactPerson' => 'HR Team', // Could be enhanced with real contact info
            'contactEmail' => generateContactEmail($application['company_name']),
            'timeline' => $timeline
        ],
        'application_data' => [
            'cover_letter' => $application['cover_letter'],
            'resume_filename' => $application['resume_filename'] ?? 'Resume.pdf',
            'resume_path' => $application['resume_path'],
            'candidate_notes' => $application['candidate_notes'],
            'employer_notes' => $application['employer_notes']
        ],
        'can_withdraw' => in_array($application['application_status'], ['submitted', 'under_review']),
        'next_steps' => generateNextSteps($application['application_status'], $interviews)
    ];
    
    echo json_encode([
        'success' => true,
        'application' => $applicationDetails
    ]);

} catch (Exception $e) {
    error_log("Error in get_application_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch application details'
    ]);
}

// Helper functions
function mapDatabaseStatus($dbStatus) {
    $statusMap = [
        'submitted' => 'applied',
        'under_review' => 'reviewed',
        'shortlisted' => 'reviewed',
        'interview_scheduled' => 'interview',
        'interviewed' => 'interview',
        'hired' => 'offered',
        'rejected' => 'rejected',
        'withdrawn' => 'rejected'
    ];
    
    return $statusMap[$dbStatus] ?? 'applied';
}

function calculateProgress($status) {
    $progressMap = [
        'submitted' => 20,
        'under_review' => 40,
        'shortlisted' => 60,
        'interview_scheduled' => 70,
        'interviewed' => 80,
        'hired' => 100,
        'rejected' => 0,
        'withdrawn' => 0
    ];
    
    return $progressMap[$status] ?? 20;
}

function formatTimelineDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

function formatApplicationDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

function formatInterviewDateTime($date, $time) {
    $dateTime = new DateTime($date . ' ' . $time);
    return $dateTime->format('F j, Y \a\t g:i A');
}

function formatStatusEvent($status, $changedByEmployer) {
    $eventMap = [
        'submitted' => 'Application Submitted',
        'under_review' => 'Application Under Review',
        'shortlisted' => 'Application Shortlisted',
        'interview_scheduled' => 'Interview Scheduled',
        'interviewed' => 'Interview Completed',
        'hired' => 'Job Offer Received',
        'rejected' => 'Application Rejected',
        'withdrawn' => 'Application Withdrawn'
    ];
    
    return $eventMap[$status] ?? 'Status Updated';
}

function generateEventDescription($status, $notes, $jobTitle, $companyName) {
    $descriptions = [
        'submitted' => "Your application for {$jobTitle} at {$companyName} has been successfully submitted.",
        'under_review' => "Your application is being reviewed by the hiring team at {$companyName}.",
        'shortlisted' => "Congratulations! You've been shortlisted for the {$jobTitle} position.",
        'interview_scheduled' => "An interview has been scheduled for the {$jobTitle} position.",
        'interviewed' => "You've completed the interview for the {$jobTitle} position.",
        'hired' => "Congratulations! You've been selected for the {$jobTitle} position at {$companyName}.",
        'rejected' => "Thank you for your interest. We've decided to move forward with other candidates.",
        'withdrawn' => "You have withdrawn your application for the {$jobTitle} position."
    ];
    
    $baseDescription = $descriptions[$status] ?? "Your application status has been updated.";
    
    return $notes ? $baseDescription . " " . $notes : $baseDescription;
}

function getStatusIcon($status) {
    $iconMap = [
        'submitted' => 'fa-file-alt',
        'under_review' => 'fa-eye',
        'shortlisted' => 'fa-list-alt',
        'interview_scheduled' => 'fa-calendar-alt',
        'interviewed' => 'fa-users',
        'hired' => 'fa-check-circle',
        'rejected' => 'fa-times-circle',
        'withdrawn' => 'fa-undo'
    ];
    
    return $iconMap[$status] ?? 'fa-circle';
}

function generateCompanyLogo($companyName) {
    $words = explode(' ', $companyName);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($companyName, 0, 2));
    }
}

function generateJobDescription($application) {
    return $application['job_description'] ?: "We are seeking a qualified {$application['job_title']} to join our team at {$application['company_name']}. This is a {$application['employment_type']} position based in {$application['job_location']}.";
}

function generateJobRequirements($application) {
    return $application['job_requirements'] ?: "Requirements for this position include relevant experience in the field, strong communication skills, and the ability to work in a collaborative environment.";
}

function generateContactEmail($companyName) {
    return 'hr@' . strtolower(str_replace([' ', '.', ','], '', $companyName)) . '.com';
}

function generateNextSteps($status, $interviews) {
    switch ($status) {
        case 'submitted':
            return "Your application is being reviewed. You'll be notified once there's an update.";
        case 'under_review':
            return "The hiring team is reviewing your application. This typically takes 3-5 business days.";
        case 'shortlisted':
            return "You've been shortlisted! Expect to hear about next steps within 2-3 business days.";
        case 'interview_scheduled':
            $upcomingInterview = null;
            foreach ($interviews as $interview) {
                if ($interview['interview_status'] === 'scheduled' && $interview['scheduled_date'] >= date('Y-m-d')) {
                    $upcomingInterview = $interview;
                    break;
                }
            }
            if ($upcomingInterview) {
                return "Prepare for your upcoming interview on " . formatInterviewDateTime($upcomingInterview['scheduled_date'], $upcomingInterview['scheduled_time']);
            }
            return "Interview details will be shared with you soon.";
        case 'interviewed':
            return "Thank you for interviewing with us. We'll be in touch with next steps soon.";
        case 'hired':
            return "Congratulations! Please check your email for onboarding details.";
        case 'rejected':
            return "Thank you for your interest. We encourage you to apply for future opportunities.";
        default:
            return "We'll keep you updated on your application status.";
    }
}
?>