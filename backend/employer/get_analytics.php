<?php
// backend/employer/get_analytics.php
// Comprehensive analytics dashboard for applicant management

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
    
    // Get date range parameters
    $date_range = $_GET['date_range'] ?? 'last_30_days'; // last_7_days, last_30_days, last_90_days, all_time
    $include_pwd_analytics = $_GET['include_pwd'] ?? true;
    
    // Calculate date filter
    $date_filter = '';
    switch ($date_range) {
        case 'last_7_days':
            $date_filter = "AND ja.applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'last_30_days':
            $date_filter = "AND ja.applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'last_90_days':
            $date_filter = "AND ja.applied_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
            break;
        case 'all_time':
        default:
            $date_filter = "";
            break;
    }
    
    // Get overview statistics
    $overview_sql = "SELECT 
                        COUNT(ja.application_id) as total_applications,
                        COUNT(DISTINCT ja.seeker_id) as unique_applicants,
                        COUNT(DISTINCT ja.job_id) as jobs_with_applications,
                        AVG(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) * 100 as hire_rate,
                        AVG(DATEDIFF(COALESCE(ja.status_updated_at, NOW()), ja.applied_at)) as avg_time_to_process,
                        SUM(CASE WHEN ja.application_status = 'submitted' THEN 1 ELSE 0 END) as pending_applications,
                        SUM(CASE WHEN ja.application_status = 'under_review' THEN 1 ELSE 0 END) as under_review,
                        SUM(CASE WHEN ja.application_status = 'interview_scheduled' THEN 1 ELSE 0 END) as interviews_scheduled,
                        SUM(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) as hired,
                        SUM(CASE WHEN ja.application_status = 'rejected' THEN 1 ELSE 0 END) as rejected
                     FROM job_applications ja
                     JOIN job_posts jp ON ja.job_id = jp.job_id
                     WHERE jp.employer_id = :employer_id {$date_filter}";
    
    $overview_stmt = $conn->prepare($overview_sql);
    $overview_stmt->bindValue(':employer_id', $employer_id);
    $overview_stmt->execute();
    $overview = $overview_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get application trends by date
    $trends_sql = "SELECT 
                    DATE(ja.applied_at) as application_date,
                    COUNT(ja.application_id) as applications_count,
                    SUM(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) as hired_count,
                    SUM(CASE WHEN ja.application_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   GROUP BY DATE(ja.applied_at)
                   ORDER BY application_date DESC
                   LIMIT 30";
    
    $trends_stmt = $conn->prepare($trends_sql);
    $trends_stmt->bindValue(':employer_id', $employer_id);
    $trends_stmt->execute();
    $trends = $trends_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get job performance analytics
    $job_performance_sql = "SELECT 
                            jp.job_title,
                            jp.job_id,
                            jp.employment_type,
                            jp.posted_at,
                            COUNT(ja.application_id) as total_applications,
                            AVG(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) * 100 as hire_rate,
                            AVG(DATEDIFF(COALESCE(ja.status_updated_at, NOW()), ja.applied_at)) as avg_processing_time,
                            SUM(CASE WHEN ja.application_status = 'submitted' THEN 1 ELSE 0 END) as pending,
                            SUM(CASE WHEN ja.application_status = 'interview_scheduled' THEN 1 ELSE 0 END) as interviews,
                            SUM(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) as hired,
                            SUM(CASE WHEN ja.application_status = 'rejected' THEN 1 ELSE 0 END) as rejected
                           FROM job_posts jp
                           LEFT JOIN job_applications ja ON jp.job_id = ja.job_id {$date_filter}
                           WHERE jp.employer_id = :employer_id
                           AND jp.job_status IN ('active', 'paused', 'closed')
                           GROUP BY jp.job_id, jp.job_title, jp.employment_type, jp.posted_at
                           ORDER BY total_applications DESC";
    
    $job_performance_stmt = $conn->prepare($job_performance_sql);
    $job_performance_stmt->bindValue(':employer_id', $employer_id);
    $job_performance_stmt->execute();
    $job_performance = $job_performance_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get status distribution
    $status_distribution_sql = "SELECT 
                                ja.application_status,
                                COUNT(ja.application_id) as count,
                                (COUNT(ja.application_id) * 100.0 / (
                                    SELECT COUNT(*) 
                                    FROM job_applications ja2 
                                    JOIN job_posts jp2 ON ja2.job_id = jp2.job_id 
                                    WHERE jp2.employer_id = :employer_id {$date_filter}
                                )) as percentage
                               FROM job_applications ja
                               JOIN job_posts jp ON ja.job_id = jp.job_id
                               WHERE jp.employer_id = :employer_id {$date_filter}
                               GROUP BY ja.application_status
                               ORDER BY count DESC";
    
    $status_stmt = $conn->prepare($status_distribution_sql);
    $status_stmt->bindValue(':employer_id', $employer_id);
    $status_stmt->execute();
    $status_distribution = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get interview analytics
    $interview_analytics_sql = "SELECT 
                                COUNT(i.interview_id) as total_interviews,
                                AVG(i.duration_minutes) as avg_duration,
                                SUM(CASE WHEN i.interview_status = 'completed' THEN 1 ELSE 0 END) as completed_interviews,
                                SUM(CASE WHEN i.interview_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_interviews,
                                SUM(CASE WHEN i.interview_status = 'no_show' THEN 1 ELSE 0 END) as no_shows,
                                SUM(CASE WHEN i.interview_type = 'online' THEN 1 ELSE 0 END) as online_interviews,
                                SUM(CASE WHEN i.interview_type = 'in_person' THEN 1 ELSE 0 END) as in_person_interviews,
                                SUM(CASE WHEN i.interview_type = 'phone' THEN 1 ELSE 0 END) as phone_interviews
                               FROM interviews i
                               JOIN job_applications ja ON i.application_id = ja.application_id
                               JOIN job_posts jp ON ja.job_id = jp.job_id
                               WHERE jp.employer_id = :employer_id {$date_filter}";
    
    $interview_stmt = $conn->prepare($interview_analytics_sql);
    $interview_stmt->bindValue(':employer_id', $employer_id);
    $interview_stmt->execute();
    $interview_analytics = $interview_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Get PWD-specific analytics if requested
    $pwd_analytics = [];
    if ($include_pwd_analytics) {
        // Disability type distribution
        $disability_sql = "SELECT 
                            dt.disability_name,
                            dc.category_name,
                            COUNT(ja.application_id) as applications_count,
                            AVG(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) * 100 as hire_rate,
                            SUM(CASE WHEN ja.application_status = 'interview_scheduled' THEN 1 ELSE 0 END) as interviews_scheduled
                           FROM job_applications ja
                           JOIN job_posts jp ON ja.job_id = jp.job_id
                           JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                           JOIN disability_types dt ON js.disability_id = dt.disability_id
                           JOIN disability_categories dc ON dt.category_id = dc.category_id
                           WHERE jp.employer_id = :employer_id {$date_filter}
                           GROUP BY dt.disability_id, dt.disability_name, dc.category_name
                           ORDER BY applications_count DESC";
        
        $disability_stmt = $conn->prepare($disability_sql);
        $disability_stmt->bindValue(':employer_id', $employer_id);
        $disability_stmt->execute();
        $disability_distribution = $disability_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Accommodation requests analytics
        $accommodations_sql = "SELECT 
                                COUNT(DISTINCT ja.seeker_id) as total_applicants_with_accommodations,
                                AVG(CASE WHEN wa.no_accommodations_needed = 1 THEN 0 ELSE 1 END) * 100 as accommodation_request_rate,
                                SUM(CASE WHEN i.sign_language_interpreter = 1 THEN 1 ELSE 0 END) as sign_language_requests,
                                SUM(CASE WHEN i.wheelchair_accessible_venue = 1 THEN 1 ELSE 0 END) as wheelchair_access_requests,
                                SUM(CASE WHEN i.screen_reader_materials = 1 THEN 1 ELSE 0 END) as screen_reader_requests
                              FROM job_applications ja
                              JOIN job_posts jp ON ja.job_id = jp.job_id
                              JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                              LEFT JOIN workplace_accommodations wa ON js.seeker_id = wa.seeker_id
                              LEFT JOIN interviews i ON ja.application_id = i.application_id
                              WHERE jp.employer_id = :employer_id {$date_filter}";
        
        $accommodations_stmt = $conn->prepare($accommodations_sql);
        $accommodations_stmt->bindValue(':employer_id', $employer_id);
        $accommodations_stmt->execute();
        $accommodations_analytics = $accommodations_stmt->fetch(PDO::FETCH_ASSOC);
        
        $pwd_analytics = [
            'disability_distribution' => $disability_distribution,
            'accommodations_analytics' => $accommodations_analytics
        ];
    }
    
    // Get skills demand analytics
    $skills_sql = "SELECT 
                    s.skill_name,
                    sc.category_name,
                    COUNT(DISTINCT ja.application_id) as applicants_with_skill,
                    AVG(CASE WHEN ja.application_status = 'hired' THEN 1 ELSE 0 END) * 100 as hire_rate_with_skill
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                   JOIN seeker_skills ss ON js.seeker_id = ss.seeker_id
                   JOIN skills s ON ss.skill_id = s.skill_id
                   JOIN skill_categories sc ON s.category_id = sc.category_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   GROUP BY s.skill_id, s.skill_name, sc.category_name
                   HAVING applicants_with_skill >= 2
                   ORDER BY applicants_with_skill DESC
                   LIMIT 20";
    
    $skills_stmt = $conn->prepare($skills_sql);
    $skills_stmt->bindValue(':employer_id', $employer_id);
    $skills_stmt->execute();
    $skills_analytics = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get time-based conversion funnel
    $funnel_sql = "SELECT 
                    'Applied' as stage,
                    COUNT(ja.application_id) as count,
                    1 as stage_order
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   
                   UNION ALL
                   
                   SELECT 
                    'Under Review' as stage,
                    COUNT(ja.application_id) as count,
                    2 as stage_order
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   AND ja.application_status IN ('under_review', 'shortlisted', 'interview_scheduled', 'interviewed', 'hired')
                   
                   UNION ALL
                   
                   SELECT 
                    'Interviewed' as stage,
                    COUNT(ja.application_id) as count,
                    3 as stage_order
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   AND ja.application_status IN ('interview_scheduled', 'interviewed', 'hired')
                   
                   UNION ALL
                   
                   SELECT 
                    'Hired' as stage,
                    COUNT(ja.application_id) as count,
                    4 as stage_order
                   FROM job_applications ja
                   JOIN job_posts jp ON ja.job_id = jp.job_id
                   WHERE jp.employer_id = :employer_id {$date_filter}
                   AND ja.application_status = 'hired'
                   
                   ORDER BY stage_order";
    
    $funnel_stmt = $conn->prepare($funnel_sql);
    $funnel_stmt->bindValue(':employer_id', $employer_id);
    $funnel_stmt->execute();
    $conversion_funnel = $funnel_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate conversion rates
    foreach ($conversion_funnel as &$stage) {
        if ($stage['stage'] === 'Applied') {
            $stage['conversion_rate'] = 100;
            $total_applied = $stage['count'];
        } else {
            $stage['conversion_rate'] = $total_applied > 0 ? ($stage['count'] / $total_applied) * 100 : 0;
        }
    }
    
    // Format data for frontend
    foreach ($trends as &$trend) {
        $trend['application_date_formatted'] = date('M j', strtotime($trend['application_date']));
    }
    
    foreach ($job_performance as &$job) {
        $job['posted_at_formatted'] = $job['posted_at'] ? date('M j, Y', strtotime($job['posted_at'])) : 'N/A';
        $job['hire_rate'] = round($job['hire_rate'] ?? 0, 1);
        $job['avg_processing_time'] = round($job['avg_processing_time'] ?? 0, 1);
    }
    
    // Log analytics access
    logActivity("ANALYTICS_VIEW", "Viewed applicant analytics dashboard");
    
    echo json_encode([
        'success' => true,
        'data' => [
            'overview' => $overview,
            'trends' => array_reverse($trends), // Most recent first for charts
            'job_performance' => $job_performance,
            'status_distribution' => $status_distribution,
            'interview_analytics' => $interview_analytics,
            'skills_analytics' => $skills_analytics,
            'conversion_funnel' => $conversion_funnel,
            'pwd_analytics' => $pwd_analytics
        ],
        'meta' => [
            'date_range' => $date_range,
            'generated_at' => date('Y-m-d H:i:s'),
            'employer_id' => $employer_id,
            'company_name' => $employer_data['company_name']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate analytics: ' . $e->getMessage()
    ]);
}