<?php
// backend/employer/get_interviews.php
// API to fetch scheduled interviews for employer

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    // Get filter parameters
    $date_filter = $_GET['date'] ?? ''; // today, week, month, all
    $status_filter = $_GET['status'] ?? ''; // scheduled, completed, cancelled, etc.
    $application_id = $_GET['application_id'] ?? null; // specific application
    
    // Base query
    $sql = "SELECT 
                i.interview_id,
                i.interview_type,
                i.scheduled_date,
                i.scheduled_time,
                i.duration_minutes,
                i.interview_platform,
                i.meeting_link,
                i.location_address,
                i.interviewer_notes,
                i.candidate_notes,
                i.interview_status,
                i.accommodations_needed,
                i.sign_language_interpreter,
                i.wheelchair_accessible_venue,
                i.screen_reader_materials,
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
                
                -- Applicant info
                js.first_name,
                js.last_name,
                js.contact_number,
                js.city,
                js.province,
                
                -- Disability info
                dt.disability_name,
                dc.category_name as disability_category,
                
                -- Profile info
                pd.headline,
                pd.profile_photo_path,
                
                -- Contact info
                ua.email
                
            FROM interviews i
            JOIN job_applications ja ON i.application_id = ja.application_id
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
            LEFT JOIN disability_categories dc ON dt.category_id = dc.category_id
            LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
            LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
            WHERE jp.employer_id = :employer_id";
    
    $params = ['employer_id' => $employer_id];
    $where_conditions = [];
    
    // Application filter
    if ($application_id) {
        $where_conditions[] = "i.application_id = :application_id";
        $params['application_id'] = $application_id;
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $where_conditions[] = "i.interview_status = :status";
        $params['status'] = $status_filter;
    }
    
    // Date filter
    if (!empty($date_filter)) {
        switch ($date_filter) {
            case 'today':
                $where_conditions[] = "DATE(i.scheduled_date) = CURDATE()";
                break;
            case 'week':
                $where_conditions[] = "i.scheduled_date >= CURDATE() AND i.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where_conditions[] = "i.scheduled_date >= CURDATE() AND i.scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'upcoming':
                $where_conditions[] = "i.scheduled_date >= CURDATE()";
                break;
            case 'past':
                $where_conditions[] = "i.scheduled_date < CURDATE()";
                break;
        }
    }
    
    // Add where conditions
    if (!empty($where_conditions)) {
        $sql .= " AND " . implode(" AND ", $where_conditions);
    }
    
    // Order by scheduled date and time
    $sql .= " ORDER BY i.scheduled_date ASC, i.scheduled_time ASC";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    $stmt->execute();
    $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    foreach ($interviews as &$interview) {
        // Format dates and times
        $interview['scheduled_date_formatted'] = date('M j, Y', strtotime($interview['scheduled_date']));
        $interview['scheduled_time_formatted'] = date('g:i A', strtotime($interview['scheduled_time']));
        $interview['scheduled_datetime_formatted'] = date('M j, Y \a\t g:i A', strtotime($interview['scheduled_date'] . ' ' . $interview['scheduled_time']));
        
        // Duration formatting
        $hours = floor($interview['duration_minutes'] / 60);
        $minutes = $interview['duration_minutes'] % 60;
        if ($hours > 0) {
            $interview['duration_formatted'] = $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
        } else {
            $interview['duration_formatted'] = $minutes . ' minutes';
        }
        
        // Applicant info
        $interview['applicant_name'] = $interview['first_name'] . ' ' . $interview['last_name'];
        $interview['avatar'] = strtoupper(substr($interview['first_name'], 0, 1) . substr($interview['last_name'], 0, 1));
        
        // Location formatting
        if ($interview['city'] && $interview['province']) {
            $interview['applicant_location'] = $interview['city'] . ', ' . $interview['province'];
        } else {
            $interview['applicant_location'] = 'Location not specified';
        }
        
        // Interview status formatting
        $interview['status_display'] = ucfirst(str_replace('_', ' ', $interview['interview_status']));
        
        // Accommodation summary
        $accommodations = [];
        if ($interview['sign_language_interpreter']) $accommodations[] = 'Sign Language Interpreter';
        if ($interview['wheelchair_accessible_venue']) $accommodations[] = 'Wheelchair Accessible';
        if ($interview['screen_reader_materials']) $accommodations[] = 'Screen Reader Materials';
        if ($interview['accommodations_needed']) $accommodations[] = $interview['accommodations_needed'];
        
        $interview['accommodation_summary'] = $accommodations;
        $interview['has_accommodations'] = !empty($accommodations);
        
        // Time until interview (for upcoming interviews)
        $now = new DateTime();
        $interview_datetime = new DateTime($interview['scheduled_date'] . ' ' . $interview['scheduled_time']);
        
        if ($interview_datetime > $now) {
            $interval = $now->diff($interview_datetime);
            
            if ($interval->days > 0) {
                $interview['time_until'] = $interval->days . ' day' . ($interval->days > 1 ? 's' : '');
            } elseif ($interval->h > 0) {
                $interview['time_until'] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
            } else {
                $interview['time_until'] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
            }
            $interview['is_upcoming'] = true;
        } else {
            $interview['time_until'] = null;
            $interview['is_upcoming'] = false;
        }
        
        // Platform/location info
        if ($interview['interview_type'] === 'online') {
            $interview['platform_info'] = $interview['interview_platform'] ?: 'Online Interview';
            $interview['meeting_info'] = $interview['meeting_link'] ?: null;
        } elseif ($interview['interview_type'] === 'in_person') {
            $interview['platform_info'] = 'In-Person Interview';
            $interview['meeting_info'] = $interview['location_address'] ?: null;
        } else {
            $interview['platform_info'] = 'Phone Interview';
            $interview['meeting_info'] = null;
        }
    }
    
    // Get summary statistics
    $stats_sql = "SELECT 
                    COUNT(*) as total_interviews,
                    SUM(CASE WHEN interview_status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN interview_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN interview_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN scheduled_date = CURDATE() THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN scheduled_date >= CURDATE() AND scheduled_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week
                  FROM interviews i
                  JOIN job_applications ja ON i.application_id = ja.application_id
                  JOIN job_posts jp ON ja.job_id = jp.job_id
                  WHERE jp.employer_id = :employer_id";
    
    $stats_stmt = $conn->prepare($stats_sql);
    $stats_stmt->bindValue(':employer_id', $employer_id);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'interviews' => $interviews,
        'total_count' => count($interviews),
        'stats' => $stats,
        'filters_applied' => [
            'date' => $date_filter,
            'status' => $status_filter,
            'application_id' => $application_id
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}