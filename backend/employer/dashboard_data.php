<?php
/**
 * Dashboard Data API for Employer Dashboard
 * Provides all data needed for the employer dashboard
 */

// Start session and include dependencies
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check if employer is logged in
if (!isset($_SESSION['employer_id']) || !isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required',
        'redirect' => 'emplogin.php'
    ]);
    exit;
}

$employer_id = $_SESSION['employer_id'];
$action = $_GET['action'] ?? 'dashboard_overview';

try {
    switch ($action) {
        case 'dashboard_overview':
            echo json_encode(getDashboardOverview($conn, $employer_id));
            break;
            
        case 'stats':
            echo json_encode(getDashboardStats($conn, $employer_id));
            break;
            
        case 'recent_jobs':
            echo json_encode(getRecentJobs($conn, $employer_id));
            break;
            
        case 'recent_applicants':
            echo json_encode(getRecentApplicants($conn, $employer_id));
            break;
            
        case 'upcoming_interviews':
            echo json_encode(getUpcomingInterviews($conn, $employer_id));
            break;
            
        case 'recent_notifications':
            echo json_encode(getRecentNotifications($conn, $employer_id));
            break;
            
        case 'search':
            $query = $_GET['q'] ?? '';
            echo json_encode(performSearch($conn, $employer_id, $query));
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching data'
    ]);
}

/**
 * Get complete dashboard overview data
 */
function getDashboardOverview($conn, $employer_id) {
    return [
        'success' => true,
        'data' => [
            'stats' => getDashboardStats($conn, $employer_id)['data'],
            'recent_jobs' => getRecentJobs($conn, $employer_id)['data'],
            'recent_applicants' => getRecentApplicants($conn, $employer_id)['data'],
            'upcoming_interviews' => getUpcomingInterviews($conn, $employer_id)['data'],
            'recent_notifications' => getRecentNotifications($conn, $employer_id)['data'],
            'company_info' => getCompanyInfo($conn, $employer_id)
        ]
    ];
}

/**
 * Get dashboard statistics
 */
function getDashboardStats($conn, $employer_id) {
    try {
        // Get active job listings count
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_posts WHERE employer_id = :employer_id AND job_status = 'active'");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        $activeJobs = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get total applicants count
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT ja.application_id) as count 
            FROM job_applications ja 
            JOIN job_posts jp ON ja.job_id = jp.job_id 
            WHERE jp.employer_id = :employer_id
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        $totalApplicants = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get PWD applicants count
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT ja.application_id) as count 
            FROM job_applications ja 
            JOIN job_posts jp ON ja.job_id = jp.job_id 
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id 
            WHERE jp.employer_id = :employer_id
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        $pwdApplicants = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Get upcoming interviews count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM interviews i 
            JOIN job_applications ja ON i.application_id = ja.application_id 
            JOIN job_posts jp ON ja.job_id = jp.job_id 
            WHERE jp.employer_id = :employer_id 
            AND i.interview_status = 'scheduled' 
            AND i.scheduled_date >= CURDATE()
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        $upcomingInterviews = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return [
            'success' => true,
            'data' => [
                'active_jobs' => intval($activeJobs),
                'total_applicants' => intval($totalApplicants),
                'pwd_applicants' => intval($pwdApplicants),
                'upcoming_interviews' => intval($upcomingInterviews)
            ]
        ];
    } catch (Exception $e) {
        error_log("Dashboard Stats Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error fetching statistics'
        ];
    }
}

/**
 * Get recent job posts
 */
function getRecentJobs($conn, $employer_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                jp.job_id,
                jp.job_title,
                jp.department,
                jp.location,
                jp.job_status,
                jp.employment_type,
                jp.created_at,
                jp.posted_at,
                COUNT(ja.application_id) as applicant_count
            FROM job_posts jp
            LEFT JOIN job_applications ja ON jp.job_id = ja.job_id
            WHERE jp.employer_id = :employer_id
            GROUP BY jp.job_id
            ORDER BY jp.created_at DESC
            LIMIT 10
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the data for frontend
        $formattedJobs = array_map(function($job) {
            return [
                'job_id' => $job['job_id'],
                'job_title' => $job['job_title'],
                'department' => $job['department'],
                'location' => $job['location'],
                'status' => $job['job_status'],
                'employment_type' => $job['employment_type'],
                'applicant_count' => intval($job['applicant_count']),
                'created_at' => $job['created_at'],
                'posted_at' => $job['posted_at'],
                'time_ago' => getTimeAgo($job['created_at'])
            ];
        }, $jobs);
        
        return [
            'success' => true,
            'data' => $formattedJobs
        ];
    } catch (Exception $e) {
        error_log("Recent Jobs Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error fetching recent jobs'
        ];
    }
}

/**
 * Get recent applicants
 */
function getRecentApplicants($conn, $employer_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                ja.application_id,
                ja.application_status,
                ja.applied_at,
                js.seeker_id,
                js.first_name,
                js.last_name,
                js.disability_id,
                jp.job_title,
                dt.disability_name,
                dc.category_name as disability_category
            FROM job_applications ja
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            JOIN disability_types dt ON js.disability_id = dt.disability_id
            JOIN disability_categories dc ON dt.category_id = dc.category_id
            WHERE jp.employer_id = :employer_id
            ORDER BY ja.applied_at DESC
            LIMIT 10
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        
        $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the data for frontend
        $formattedApplicants = array_map(function($applicant) {
            $fullName = trim($applicant['first_name'] . ' ' . $applicant['last_name']);
            $initials = getInitials($fullName);
            
            return [
                'application_id' => $applicant['application_id'],
                'seeker_id' => $applicant['seeker_id'],
                'full_name' => $fullName,
                'initials' => $initials,
                'job_title' => $applicant['job_title'],
                'disability_name' => $applicant['disability_name'],
                'disability_category' => $applicant['disability_category'],
                'application_status' => $applicant['application_status'],
                'applied_at' => $applicant['applied_at'],
                'time_ago' => getTimeAgo($applicant['applied_at'])
            ];
        }, $applicants);
        
        return [
            'success' => true,
            'data' => $formattedApplicants
        ];
    } catch (Exception $e) {
        error_log("Recent Applicants Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error fetching recent applicants'
        ];
    }
}

/**
 * Get upcoming interviews
 */
function getUpcomingInterviews($conn, $employer_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                i.interview_id,
                i.interview_type,
                i.scheduled_date,
                i.scheduled_time,
                i.interview_platform,
                i.location_address,
                i.interview_status,
                i.accommodations_needed,
                i.sign_language_interpreter,
                i.wheelchair_accessible_venue,
                i.screen_reader_materials,
                ja.application_id,
                js.seeker_id,
                js.first_name,
                js.last_name,
                js.disability_id,
                jp.job_title,
                dt.disability_name,
                dc.category_name as disability_category
            FROM interviews i
            JOIN job_applications ja ON i.application_id = ja.application_id
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            JOIN disability_types dt ON js.disability_id = dt.disability_id
            JOIN disability_categories dc ON dt.category_id = dc.category_id
            WHERE jp.employer_id = :employer_id 
            AND i.interview_status IN ('scheduled', 'confirmed')
            AND i.scheduled_date >= CURDATE()
            ORDER BY i.scheduled_date ASC, i.scheduled_time ASC
            LIMIT 10
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        
        $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the data for frontend
        $formattedInterviews = array_map(function($interview) {
            $fullName = trim($interview['first_name'] . ' ' . $interview['last_name']);
            $initials = getInitials($fullName);
            
            return [
                'interview_id' => $interview['interview_id'],
                'application_id' => $interview['application_id'],
                'seeker_id' => $interview['seeker_id'],
                'candidate_name' => $fullName,
                'initials' => $initials,
                'job_title' => $interview['job_title'],
                'interview_type' => $interview['interview_type'],
                'scheduled_date' => $interview['scheduled_date'],
                'scheduled_time' => $interview['scheduled_time'],
                'formatted_date' => date('F j, Y', strtotime($interview['scheduled_date'])),
                'formatted_time' => date('g:i A', strtotime($interview['scheduled_time'])),
                'interview_platform' => $interview['interview_platform'],
                'location_address' => $interview['location_address'],
                'interview_status' => $interview['interview_status'],
                'disability_name' => $interview['disability_name'],
                'disability_category' => $interview['disability_category'],
                'has_accommodations' => !empty($interview['accommodations_needed']) || 
                                       $interview['sign_language_interpreter'] || 
                                       $interview['wheelchair_accessible_venue'] || 
                                       $interview['screen_reader_materials'],
                'accommodations' => [
                    'sign_language_interpreter' => (bool)$interview['sign_language_interpreter'],
                    'wheelchair_accessible_venue' => (bool)$interview['wheelchair_accessible_venue'],
                    'screen_reader_materials' => (bool)$interview['screen_reader_materials'],
                    'additional_notes' => $interview['accommodations_needed']
                ]
            ];
        }, $interviews);
        
        return [
            'success' => true,
            'data' => $formattedInterviews
        ];
    } catch (Exception $e) {
        error_log("Upcoming Interviews Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error fetching upcoming interviews'
        ];
    }
}

/**
 * Get recent notifications
 */
function getRecentNotifications($conn, $employer_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.is_read,
                n.created_at,
                nt.type_name,
                nt.icon_class,
                nt.color_class,
                n.related_job_id,
                n.related_application_id,
                n.related_interview_id
            FROM notifications n
            JOIN notification_types nt ON n.type_id = nt.type_id
            WHERE n.recipient_type = 'employer' 
            AND n.recipient_id = :employer_id
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the data for frontend
        $formattedNotifications = array_map(function($notification) {
            return [
                'notification_id' => $notification['notification_id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'is_read' => (bool)$notification['is_read'],
                'type_name' => $notification['type_name'],
                'icon_class' => $notification['icon_class'],
                'color_class' => $notification['color_class'],
                'created_at' => $notification['created_at'],
                'time_ago' => getTimeAgo($notification['created_at']),
                'related_job_id' => $notification['related_job_id'],
                'related_application_id' => $notification['related_application_id'],
                'related_interview_id' => $notification['related_interview_id']
            ];
        }, $notifications);
        
        return [
            'success' => true,
            'data' => $formattedNotifications
        ];
    } catch (Exception $e) {
        error_log("Recent Notifications Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error fetching notifications'
        ];
    }
}

/**
 * Get company information
 */
function getCompanyInfo($conn, $employer_id) {
    try {
        $stmt = $conn->prepare("
            SELECT 
                e.company_name,
                e.company_logo_path,
                ec.first_name,
                ec.last_name
            FROM employers e
            JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
            WHERE e.employer_id = :employer_id
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();
        
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($company) {
            return [
                'company_name' => $company['company_name'],
                'company_logo' => $company['company_logo_path'],
                'contact_name' => trim($company['first_name'] . ' ' . $company['last_name'])
            ];
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Company Info Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Perform search across jobs, applicants, and interviews
 */
function performSearch($conn, $employer_id, $query) {
    if (empty($query)) {
        return ['success' => true, 'data' => []];
    }
    
    try {
        $searchResults = [];
        
        // Search in jobs
        $stmt = $conn->prepare("
            SELECT 'job' as type, job_id as id, job_title as title, department as subtitle, job_status as status
            FROM job_posts 
            WHERE employer_id = :employer_id 
            AND (job_title LIKE :query OR department LIKE :query OR location LIKE :query)
            LIMIT 5
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();
        $searchResults = array_merge($searchResults, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Search in applicants
        $stmt = $conn->prepare("
            SELECT 'applicant' as type, ja.application_id as id, 
                   CONCAT(js.first_name, ' ', js.last_name) as title, 
                   jp.job_title as subtitle, ja.application_status as status
            FROM job_applications ja
            JOIN job_posts jp ON ja.job_id = jp.job_id
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            WHERE jp.employer_id = :employer_id 
            AND (js.first_name LIKE :query OR js.last_name LIKE :query OR jp.job_title LIKE :query)
            LIMIT 5
        ");
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->bindParam(':query', $searchQuery);
        $stmt->execute();
        $searchResults = array_merge($searchResults, $stmt->fetchAll(PDO::FETCH_ASSOC));
        
        return [
            'success' => true,
            'data' => $searchResults
        ];
    } catch (Exception $e) {
        error_log("Search Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error performing search'
        ];
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
    return substr($initials, 0, 2); // Limit to 2 characters
}
?>