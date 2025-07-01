<?php
// backend/candidate/get_applications.php
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
    $statusFilter = $_GET['status'] ?? 'all';
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

    // Build query with proper company join (since applicant_overview doesn't have company data)
    $query = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            ja.application_status,
            ja.applied_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.resume_id,
            jp.job_title,
            jp.employer_id,
            jp.employment_type,
            jp.location AS job_location,
            jp.salary_range,
            e.company_name,
            e.company_logo_path,
            r.file_path AS resume_path,
            r.file_name AS resume_filename,
            CASE 
                WHEN ja.application_status = 'submitted' THEN 20
                WHEN ja.application_status = 'under_review' THEN 40
                WHEN ja.application_status = 'shortlisted' THEN 60
                WHEN ja.application_status = 'interview_scheduled' THEN 70
                WHEN ja.application_status = 'interviewed' THEN 80
                WHEN ja.application_status = 'hired' THEN 100
                WHEN ja.application_status = 'rejected' THEN 0
                WHEN ja.application_status = 'withdrawn' THEN 0
                ELSE 20
            END as progress_percentage,
            i.interview_id,
            i.scheduled_date as interview_date,
            i.scheduled_time as interview_time,
            i.interview_type,
            i.meeting_link,
            i.interview_status
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        LEFT JOIN resumes r ON ja.resume_id = r.resume_id
        LEFT JOIN interviews i ON ja.application_id = i.application_id 
            AND i.interview_status IN ('scheduled', 'confirmed')
        WHERE ja.seeker_id = :seeker_id
    ";
    
    $params = [':seeker_id' => $seeker_id];
    
    // Add status filter
    if ($statusFilter !== 'all') {
        $statusMap = [
            'applied' => 'submitted',
            'reviewed' => 'under_review',
            'interview' => ['interview_scheduled', 'interviewed'],
            'offered' => 'hired',
            'rejected' => 'rejected'
        ];
        
        if (isset($statusMap[$statusFilter])) {
            if (is_array($statusMap[$statusFilter])) {
                $placeholders = [];
                foreach ($statusMap[$statusFilter] as $i => $status) {
                    $placeholder = ":status_$i";
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $status;
                }
                $query .= " AND ja.application_status IN (" . implode(',', $placeholders) . ")";
            } else {
                $query .= " AND ja.application_status = :status";
                $params[':status'] = $statusMap[$statusFilter];
            }
        }
    }
    
    // Add search filter
    if (!empty($searchQuery)) {
        $query .= " AND (jp.job_title LIKE :search 
                    OR e.company_name LIKE :search 
                    OR jp.location LIKE :search)";
        $params[':search'] = '%' . $searchQuery . '%';
    }
    
    // Add date filters
    if (!empty($dateFrom)) {
        $query .= " AND DATE(ja.applied_at) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $query .= " AND DATE(ja.applied_at) <= :date_to";
        $params[':date_to'] = $dateTo;
    }
    
    // Add ordering and pagination
    $query .= " ORDER BY ja.applied_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === ':seeker_id' || $key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format applications data
    $formattedApplications = [];
    foreach ($applications as $app) {
        $formattedApplications[] = [
            'id' => $app['application_id'],
            'jobTitle' => $app['job_title'],
            'company' => $app['company_name'],
            'logo' => generateCompanyLogo($app['company_name']),
            'location' => $app['job_location'],
            'type' => $app['employment_type'],
            'salary' => $app['salary_range'] ?: '',
            'dateApplied' => formatApplicationDate($app['applied_at']),
            'status' => mapDatabaseStatus($app['application_status']),
            'progress' => (int)$app['progress_percentage'],
            'details' => [
                'description' => $app['job_title'] . ' position at ' . $app['company_name'],
                'requirements' => 'Requirements for this position include relevant experience and skills.',
                'contactPerson' => 'HR Team',
                'contactEmail' => 'hr@' . strtolower(str_replace(' ', '', $app['company_name'])) . '.com',
                'timeline' => [] // Will be populated separately
            ],
            'interview' => $app['interview_id'] ? [
                'id' => $app['interview_id'],
                'date' => $app['interview_date'],
                'time' => $app['interview_time'],
                'type' => $app['interview_type'],
                'status' => $app['interview_status'],
                'meeting_link' => $app['meeting_link']
            ] : null,
            'resume_path' => $app['resume_path'],
            'cover_letter' => $app['cover_letter'],
            'employer_notes' => $app['employer_notes']
        ];
    }
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM job_applications ja
        INNER JOIN job_posts jp ON ja.job_id = jp.job_id
        INNER JOIN employers e ON jp.employer_id = e.employer_id
        WHERE ja.seeker_id = :seeker_id
    ";
    
    $countParams = [':seeker_id' => $seeker_id];
    
    // Add same filters to count query
    if ($statusFilter !== 'all') {
        $statusMap = [
            'applied' => 'submitted',
            'reviewed' => 'under_review', 
            'interview' => ['interview_scheduled', 'interviewed'],
            'offered' => 'hired',
            'rejected' => 'rejected'
        ];
        
        if (isset($statusMap[$statusFilter])) {
            if (is_array($statusMap[$statusFilter])) {
                $placeholders = [];
                foreach ($statusMap[$statusFilter] as $i => $status) {
                    $placeholder = ":status_$i";
                    $placeholders[] = $placeholder;
                    $countParams[$placeholder] = $status;
                }
                $countQuery .= " AND ja.application_status IN (" . implode(',', $placeholders) . ")";
            } else {
                $countQuery .= " AND ja.application_status = :status";
                $countParams[':status'] = $statusMap[$statusFilter];
            }
        }
    }
    
    if (!empty($searchQuery)) {
        $countQuery .= " AND (jp.job_title LIKE :search 
                        OR e.company_name LIKE :search 
                        OR jp.location LIKE :search)";
        $countParams[':search'] = '%' . $searchQuery . '%';
    }
    
    if (!empty($dateFrom)) {
        $countQuery .= " AND DATE(ja.applied_at) >= :date_from";
        $countParams[':date_from'] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $countQuery .= " AND DATE(ja.applied_at) <= :date_to";
        $countParams[':date_to'] = $dateTo;
    }
    
    $countStmt = $conn->prepare($countQuery);
    foreach ($countParams as $key => $value) {
        if ($key === ':seeker_id') {
            $countStmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $countStmt->bindValue($key, $value);
        }
    }
    $countStmt->execute();
    $totalApplications = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'applications' => $formattedApplications,
        'total' => (int)$totalApplications,
        'pagination' => [
            'current_page' => floor($offset / $limit) + 1,
            'per_page' => $limit,
            'total_pages' => ceil($totalApplications / $limit),
            'has_more' => ($offset + $limit) < $totalApplications
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_applications.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch applications'
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

function formatApplicationDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('F j, Y');
}

function generateCompanyLogo($companyName) {
    // Generate 2-letter logo from company name
    $words = explode(' ', $companyName);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        return strtoupper(substr($companyName, 0, 2));
    }
}
?>