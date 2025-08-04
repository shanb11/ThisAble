<?php header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../db.php';

try {
    // Category mapping - maps database departments to landing page categories
    $categoryMapping = [
        'Engineering' => 'office',
        'Design' => 'office',
        'Customer Service' => 'customer',
        'Marketing' => 'business',
        'Sales' => 'business',
        'Education' => 'education',
        'Healthcare' => 'healthcare',
        'Finance' => 'finance',
        'Accounting' => 'finance'
    ];

    // Get active jobs with company information
    $jobsQuery = "
        SELECT 
            jp.job_id as id,
            jp.job_title as title,
            e.company_name as company,
            jp.location,
            jp.employment_type as type,
            jp.department,
            jp.salary_range as salary,
            jp.job_description as description,
            jp.created_at,
            jp.posted_at
        FROM job_posts jp
        LEFT JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_status = 'active'
        ORDER BY jp.posted_at DESC
        LIMIT 20
    ";

    $stmt = $conn->prepare($jobsQuery);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format jobs for frontend
    $formattedJobs = [];
    $categoryCounts = [
        'education' => 0,
        'office' => 0,
        'customer' => 0,
        'business' => 0,
        'healthcare' => 0,
        'finance' => 0
    ];

    foreach ($jobs as $job) {
        // Map department to category
        $category = isset($categoryMapping[$job['department']]) 
            ? $categoryMapping[$job['department']] 
            : 'office'; // default category

        // Format salary
        $salary = $job['salary'] ? $job['salary'] : 'Competitive';
        if ($salary && !str_contains($salary, '₱') && !str_contains($salary, '$')) {
            $salary = '₱' . $salary;
        }

        // Calculate days ago
        $daysAgo = '';
        if ($job['posted_at']) {
            $postedDate = new DateTime($job['posted_at']);
            $now = new DateTime();
            $diff = $now->diff($postedDate);
            
            if ($diff->days == 0) {
                $daysAgo = 'Posted today';
            } elseif ($diff->days == 1) {
                $daysAgo = 'Posted yesterday';
            } else {
                $daysAgo = 'Posted ' . $diff->days . ' days ago';
            }
        }

        $formattedJobs[] = [
            'id' => (int)$job['id'],
            'title' => $job['title'],
            'company' => $job['company'] ?: 'Company Name',
            'location' => $job['location'] ?: 'Location TBD',
            'type' => $job['type'] ?: 'Full-time',
            'category' => $category,
            'salary' => $salary,
            'description' => substr($job['description'], 0, 150) . '...',
            'posted' => $daysAgo
        ];

        // Count jobs per category
        $categoryCounts[$category]++;
    }

    // Get total active jobs count
    $totalQuery = "SELECT COUNT(*) as total FROM job_posts WHERE job_status = 'active'";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute();
    $totalJobs = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total companies count
    $companiesQuery = "SELECT COUNT(DISTINCT employer_id) as total FROM job_posts WHERE job_status = 'active'";
    $companiesStmt = $conn->prepare($companiesQuery);
    $companiesStmt->execute();
    $totalCompanies = $companiesStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Response
    $response = [
        'success' => true,
        'jobs' => $formattedJobs,
        'categories' => $categoryCounts,
        'stats' => [
            'total_jobs' => (int)$totalJobs,
            'total_companies' => (int)$totalCompanies,
            'jobs_this_week' => count($formattedJobs) // Simple approximation
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // Error response
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'jobs' => [],
        'categories' => [
            'education' => 0,
            'office' => 0,
            'customer' => 0,
            'business' => 0,
            'healthcare' => 0,
            'finance' => 0
        ],
        'stats' => [
            'total_jobs' => 0,
            'total_companies' => 0,
            'jobs_this_week' => 0
        ]
    ]);
}
?>