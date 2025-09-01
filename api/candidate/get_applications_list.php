<?php
/**
 * FIXED Get Applications List API for ThisAble Mobile
 * Now includes search functionality to match Flutter app requirements
 * File: C:\xampp\htdocs\ThisAble\api\candidate\get_applications_list.php
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
    error_log("Applications API: seeker_id=$seekerId");

    // ✅ FIXED: Get query parameters (including search)
    $status_filter = $_GET['status'] ?? 'all';
    $search_query = $_GET['search'] ?? '';  // ✅ ADDED: Handle search parameter from Flutter
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // ===== STEP 1: BUILD QUERY WITH SEARCH SUPPORT =====
    $whereConditions = ["ja.seeker_id = ?"];
    $params = [$seekerId];
    
    // Status filter
    if ($status_filter !== 'all') {
        $whereConditions[] = "ja.application_status = ?";
        $params[] = $status_filter;
    }
    
    // ✅ ADDED: Search filter support
    if (!empty($search_query)) {
        $whereConditions[] = "(jp.job_title LIKE ? OR e.company_name LIKE ? OR jp.location LIKE ?)";
        $searchParam = "%{$search_query}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        error_log("Applications API: Search query = '$search_query'");
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // ===== STEP 2: GET APPLICATIONS WITH JOIN FOR SEARCH =====
    $stmt = $conn->prepare("
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.application_status,
            ja.applied_at,
            ja.cover_letter,
            ja.employer_notes,
            ja.last_activity,
            ja.resume_id,
            jp.job_title,
            jp.location,
            jp.employment_type,
            jp.employer_id,
            e.company_name,
            e.logo_url
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE {$whereClause}
        ORDER BY ja.applied_at DESC
        LIMIT $limit OFFSET $offset
    ");
    
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("Applications API: Found " . count($applications) . " applications");
    
    // ===== STEP 3: GET TOTAL COUNT FOR PAGINATION =====
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN employers e ON jp.employer_id = e.employer_id
        WHERE {$whereClause}
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // ===== STEP 4: FORMAT RESPONSE =====
    $totalPages = ceil($totalCount / $limit);
    
    // Format applications for Flutter
    $formattedApplications = [];
    foreach ($applications as $app) {
        $formattedApplications[] = [
            'application_id' => intval($app['application_id']),
            'job_id' => intval($app['job_id']),
            'job_title' => $app['job_title'],
            'company_name' => $app['company_name'],
            'company_logo' => $app['logo_url'],
            'location' => $app['location'],
            'employment_type' => $app['employment_type'],
            'application_status' => $app['application_status'],
            'applied_at' => $app['applied_at'],
            'cover_letter' => $app['cover_letter'],
            'employer_notes' => $app['employer_notes'],
            'last_activity' => $app['last_activity'],
            'resume_id' => $app['resume_id'] ? intval($app['resume_id']) : null,
        ];
    }
    
    ApiResponse::success([
        'applications' => $formattedApplications,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => intval($totalCount),
            'items_per_page' => $limit,
            'has_next' => $page < $totalPages,
            'has_previous' => $page > 1
        ],
        'search_query' => $search_query,  // ✅ ADDED: Return search query for debugging
        'status_filter' => $status_filter
    ], 'Applications retrieved successfully');
    
} catch (Exception $e) {
    error_log("Applications API Error: " . $e->getMessage());
    ApiResponse::error("Failed to retrieve applications: " . $e->getMessage());
}
?>