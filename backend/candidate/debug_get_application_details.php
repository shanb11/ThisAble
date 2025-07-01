<?php
// backend/candidate/debug_get_application_details.php
header('Content-Type: text/html'); // Change to HTML for better debugging

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../db.php';
require_once '../../includes/candidate/session_check.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo "Not logged in. Current session: ";
    print_r($_SESSION);
    exit;
}

$seeker_id = get_seeker_id();
$application_id = $_GET['application_id'] ?? 0;

echo "<h2>Debug Application Details</h2>";
echo "<p>Seeker ID: $seeker_id</p>";
echo "<p>Application ID: $application_id</p>";

if (!$application_id) {
    echo "❌ No Application ID provided";
    exit;
}

try {
    echo "<h3>Step 1: Testing Basic Application Query</h3>";
    
    // Simple query first
    $simpleQuery = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            ja.application_status,
            ja.applied_at
        FROM job_applications ja
        WHERE ja.application_id = :application_id 
        AND ja.seeker_id = :seeker_id
    ";
    
    $simpleStmt = $conn->prepare($simpleQuery);
    $simpleStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
    $simpleStmt->bindValue(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $simpleStmt->execute();
    $simpleResult = $simpleStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$simpleResult) {
        echo "❌ Application not found with simple query<br>";
        echo "Checking if application exists at all...<br>";
        
        $checkQuery = "SELECT * FROM job_applications WHERE application_id = :application_id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $checkStmt->execute();
        $checkResult = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($checkResult) {
            echo "✅ Application exists but belongs to seeker_id: {$checkResult['seeker_id']}<br>";
            echo "Current user seeker_id: $seeker_id<br>";
        } else {
            echo "❌ Application does not exist at all<br>";
        }
        exit;
    }
    
    echo "✅ Basic application found:<br>";
    print_r($simpleResult);
    echo "<br><br>";
    
    echo "<h3>Step 2: Testing Full Query</h3>";
    
    // Full query
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
        echo "❌ Full query failed. Let's check what's missing...<br>";
        
        // Check job_posts
        $jobQuery = "SELECT * FROM job_posts WHERE job_id = (SELECT job_id FROM job_applications WHERE application_id = :application_id)";
        $jobStmt = $conn->prepare($jobQuery);
        $jobStmt->bindValue(':application_id', $application_id, PDO::PARAM_INT);
        $jobStmt->execute();
        $job = $jobStmt->fetch();
        
        if (!$job) {
            echo "❌ Job post not found<br>";
        } else {
            echo "✅ Job post found: {$job['job_title']}<br>";
            
            // Check employer
            $empQuery = "SELECT * FROM employers WHERE employer_id = :employer_id";
            $empStmt = $conn->prepare($empQuery);
            $empStmt->bindValue(':employer_id', $job['employer_id'], PDO::PARAM_INT);
            $empStmt->execute();
            $employer = $empStmt->fetch();
            
            if (!$employer) {
                echo "❌ Employer not found for employer_id: {$job['employer_id']}<br>";
            } else {
                echo "✅ Employer found: {$employer['company_name']}<br>";
            }
        }
        exit;
    }
    
    echo "✅ Full application data retrieved:<br>";
    echo "<pre>";
    print_r($application);
    echo "</pre>";
    
    echo "<h3>Step 3: Testing Timeline Query</h3>";
    
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
    
    echo "✅ Timeline data (" . count($timelineData) . " entries):<br>";
    echo "<pre>";
    print_r($timelineData);
    echo "</pre>";
    
    echo "<h3>Step 4: Testing Interview Query</h3>";
    
    // Get interview information
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
    
    echo "✅ Interview data (" . count($interviews) . " entries):<br>";
    echo "<pre>";
    print_r($interviews);
    echo "</pre>";
    
    echo "<h3>✅ All queries successful!</h3>";
    echo "<p>The issue might be in the JSON formatting or helper functions.</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error occurred:</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>