<?php
// backend/candidate/get_candidate_interviews.php
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

try {
    // Get filter parameters
    $status_filter = $_GET['status'] ?? 'upcoming'; // upcoming, completed, all
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $date_range = $_GET['date_range'] ?? 'all'; // today, week, month, all
    
    // Base query for candidate interviews
    $query = "
        SELECT 
            i.interview_id,
            i.interview_type,
            i.scheduled_date,
            i.scheduled_time,
            i.duration_minutes,
            i.interview_platform,
            i.meeting_link,
            i.meeting_id,
            i.location_address,
            i.interviewer_notes,
            i.candidate_notes,
            i.interview_status,
            i.accommodations_needed,
            i.sign_language_interpreter,
            i.wheelchair_accessible_venue,
            i.screen_reader_materials,
            i.reminder_sent,
            i.created_at,
            i.updated_at,
            
            -- Application info
            ja.application_id,
            ja.application_status,
            ja.applied_at,
            
            -- Job info
            jp.job_title,
            jp.employment_type,
            jp.location as job_location,
            jp.salary_range,
            
            -- Company info
            e.company_name,
            e.company_logo_path,
            
            -- Interview feedback (if available)
            if_fb.technical_score,
            if_fb.communication_score,
            if_fb.cultural_fit_score,
            if_fb.overall_rating,
            if_fb.strengths,
            if_fb.areas_for_improvement,
            if_fb.recommendation,
            if_fb.detailed_feedback,
            if_fb.accessibility_needs_discussed,
            if_fb.accommodation_feasibility
            
        FROM interviews i
        INNER JOIN job_applications ja ON i.application_id = ja.application_id
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN interview_feedback if_fb ON i.interview_id = if_fb.interview_id
        WHERE ja.seeker_id = :seeker_id
    ";
    
    $params = [':seeker_id' => $seeker_id];
    
    // Add status filter
    if ($status_filter === 'upcoming') {
        $query .= " AND i.scheduled_date >= CURDATE() AND i.interview_status IN ('scheduled', 'confirmed')";
    } elseif ($status_filter === 'completed') {
        $query .= " AND i.interview_status = 'completed'";
    } elseif ($status_filter === 'cancelled') {
        $query .= " AND i.interview_status = 'cancelled'";
    }
    
    // Add date range filter
    if ($date_range === 'today') {
        $query .= " AND DATE(i.scheduled_date) = CURDATE()";
    } elseif ($date_range === 'week') {
        $query .= " AND i.scheduled_date >= CURDATE() AND i.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($date_range === 'month') {
        $query .= " AND i.scheduled_date >= CURDATE() AND i.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
    }
    
    // Order by scheduled date and time
    $query .= " ORDER BY i.scheduled_date ASC, i.scheduled_time ASC";
    
    // Add limit
    if ($limit > 0) {
        $query .= " LIMIT :limit";
        $params[':limit'] = $limit;
    }
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === ':seeker_id' || $key === ':limit') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    $formattedInterviews = [];
    foreach ($interviews as $interview) {
        // Calculate time until interview
        $now = new DateTime();
        $interviewDateTime = new DateTime($interview['scheduled_date'] . ' ' . $interview['scheduled_time']);
        
        $timeUntil = null;
        $isUpcoming = false;
        if ($interviewDateTime > $now) {
            $interval = $now->diff($interviewDateTime);
            
            if ($interval->days > 0) {
                $timeUntil = $interval->days . ' day' . ($interval->days > 1 ? 's' : '');
            } elseif ($interval->h > 0) {
                $timeUntil = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
            } else {
                $timeUntil = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
            }
            $isUpcoming = true;
        }
        
        // Format accommodations
        $accommodations = [];
        if ($interview['sign_language_interpreter']) $accommodations[] = 'Sign Language Interpreter';
        if ($interview['wheelchair_accessible_venue']) $accommodations[] = 'Wheelchair Accessible';
        if ($interview['screen_reader_materials']) $accommodations[] = 'Screen Reader Materials';
        if ($interview['accommodations_needed']) $accommodations[] = $interview['accommodations_needed'];
        
        // Format duration
        $hours = floor($interview['duration_minutes'] / 60);
        $minutes = $interview['duration_minutes'] % 60;
        $durationFormatted = '';
        if ($hours > 0) {
            $durationFormatted = $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
        } else {
            $durationFormatted = $minutes . ' minutes';
        }
        
        // Determine meeting info based on type
        $meetingInfo = '';
        if ($interview['interview_type'] === 'online') {
            $meetingInfo = $interview['interview_platform'] ?: 'Online Interview';
            if ($interview['meeting_link']) {
                $meetingInfo .= ' - ' . $interview['meeting_link'];
            }
        } elseif ($interview['interview_type'] === 'in_person') {
            $meetingInfo = $interview['location_address'] ?: 'Location TBD';
        } else {
            $meetingInfo = 'Phone Interview';
        }
        
        $formattedInterviews[] = [
            'id' => $interview['interview_id'],
            'application_id' => $interview['application_id'],
            'job_title' => $interview['job_title'],
            'company_name' => $interview['company_name'],
            'company_logo' => generateCompanyLogo($interview['company_name']),
            'interview_type' => $interview['interview_type'],
            'interview_type_display' => ucfirst(str_replace('_', ' ', $interview['interview_type'])),
            'scheduled_date' => $interview['scheduled_date'],
            'scheduled_time' => $interview['scheduled_time'],
            'formatted_date' => date('F j, Y', strtotime($interview['scheduled_date'])),
            'formatted_time' => date('g:i A', strtotime($interview['scheduled_time'])),
            'formatted_datetime' => date('M j, Y \a\t g:i A', strtotime($interview['scheduled_date'] . ' ' . $interview['scheduled_time'])),
            'duration_minutes' => $interview['duration_minutes'],
            'duration_formatted' => $durationFormatted,
            'interview_platform' => $interview['interview_platform'],
            'meeting_link' => $interview['meeting_link'],
            'meeting_id' => $interview['meeting_id'],
            'location_address' => $interview['location_address'],
            'meeting_info' => $meetingInfo,
            'interview_status' => $interview['interview_status'],
            'status_display' => ucfirst(str_replace('_', ' ', $interview['interview_status'])),
            'application_status' => $interview['application_status'],
            'time_until' => $timeUntil,
            'is_upcoming' => $isUpcoming,
            'accommodations' => $accommodations,
            'has_accommodations' => !empty($accommodations),
            'interviewer_notes' => $interview['interviewer_notes'],
            'candidate_notes' => $interview['candidate_notes'],
            'reminder_sent' => (bool)$interview['reminder_sent'],
            'employment_type' => $interview['employment_type'],
            'job_location' => $interview['job_location'],
            'salary_range' => $interview['salary_range'],
            'applied_at' => $interview['applied_at'],
            'feedback' => null
        ];
        
        // Add feedback if interview is completed
        if ($interview['interview_status'] === 'completed' && $interview['overall_rating']) {
            $formattedInterviews[count($formattedInterviews) - 1]['feedback'] = [
                'technical_score' => $interview['technical_score'],
                'communication_score' => $interview['communication_score'],
                'cultural_fit_score' => $interview['cultural_fit_score'],
                'overall_rating' => $interview['overall_rating'],
                'strengths' => $interview['strengths'],
                'areas_for_improvement' => $interview['areas_for_improvement'],
                'recommendation' => $interview['recommendation'],
                'detailed_feedback' => $interview['detailed_feedback'],
                'accessibility_needs_discussed' => (bool)$interview['accessibility_needs_discussed'],
                'accommodation_feasibility' => $interview['accommodation_feasibility']
            ];
        }
    }
    
    // Get summary statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_interviews,
            SUM(CASE WHEN interview_status IN ('scheduled', 'confirmed') AND scheduled_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN interview_status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN interview_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN DATE(scheduled_date) = CURDATE() AND interview_status IN ('scheduled', 'confirmed') THEN 1 ELSE 0 END) as today
        FROM interviews i
        INNER JOIN job_applications ja ON i.application_id = ja.application_id
        WHERE ja.seeker_id = :seeker_id
    ";
    
    $statsStmt = $conn->prepare($statsQuery);
    $statsStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'interviews' => $formattedInterviews,
        'total_count' => count($formattedInterviews),
        'stats' => [
            'total_interviews' => (int)$stats['total_interviews'],
            'upcoming' => (int)$stats['upcoming'],
            'completed' => (int)$stats['completed'],
            'cancelled' => (int)$stats['cancelled'],
            'today' => (int)$stats['today']
        ],
        'filters_applied' => [
            'status' => $status_filter,
            'date_range' => $date_range,
            'limit' => $limit
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_candidate_interviews.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch interview data'
    ]);
}

// Helper function to generate company logo
function generateCompanyLogo($companyName) {
    $words = explode(' ', $companyName);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($companyName, 0, 2));
    }
}
?>