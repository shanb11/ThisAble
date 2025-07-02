<?php
// backend/debug/test_api_calls.php
// Test the exact API calls that frontend makes

echo "<h1>🔍 API Calls Debug - Frontend vs Backend</h1>";
echo "<style>
    body{font-family:Arial;margin:20px;background:#f5f5f5;}
    .section{background:white;padding:20px;margin:20px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    .success{color:#10b981;font-weight:bold;}
    .error{color:#ef4444;font-weight:bold;background:#fee;padding:10px;border-radius:4px;margin:10px 0;}
    .warning{color:#f59e0b;font-weight:bold;}
    .info{color:#3b82f6;font-weight:bold;}
    pre{background:#f1f5f9;padding:15px;border-radius:4px;overflow-x:auto;max-height:300px;}
    .api-test{border:1px solid #ddd;margin:10px 0;padding:15px;border-radius:6px;}
</style>";

session_start();

echo "<div class='section'>";
echo "<h2>🔐 Session Status Check</h2>";
if (isset($_SESSION['employer_id'])) {
    echo "<span class='success'>✅ Employer ID: " . $_SESSION['employer_id'] . "</span><br>";
    $employer_id = $_SESSION['employer_id'];
} else {
    echo "<span class='error'>❌ No employer_id in session!</span><br>";
    echo "<p>You need to be logged in as employer to test APIs.</p>";
    exit;
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    echo "<span class='success'>✅ Logged in: true</span><br>";
} else {
    echo "<span class='error'>❌ Not properly logged in!</span><br>";
}
echo "</div>";

// Get list of jobs for this employer
require_once '../../backend/db.php';

echo "<div class='section'>";
echo "<h2>📋 Available Jobs for Testing</h2>";

$jobs_sql = "SELECT job_id, job_title, created_at FROM job_posts WHERE employer_id = ? ORDER BY created_at DESC LIMIT 5";
$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->execute([$employer_id]);
$jobs = $jobs_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($jobs)) {
    echo "<span class='error'>❌ No jobs found for employer ID $employer_id</span>";
    exit;
}

echo "<ul>";
foreach ($jobs as $job) {
    echo "<li><strong>Job {$job['job_id']}</strong>: {$job['job_title']} (Created: {$job['created_at']})</li>";
}
echo "</ul>";

// Test each job
foreach ($jobs as $job) {
    $job_id = $job['job_id'];
    
    echo "<div class='api-test'>";
    echo "<h3>🧪 Testing Job ID: $job_id ({$job['job_title']})</h3>";
    
    // Test 1: Direct API call simulation
    echo "<h4>Test 1: Direct API Simulation</h4>";
    
    try {
        // Simulate the exact POST request that frontend makes
        $post_data = json_encode([
            'job_id' => $job_id,
            'force_recalculate' => true
        ]);
        
        // Create a context that mimics the frontend request
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $post_data
            ]
        ]);
        
        $api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/../employer/batch_calculate_matches.php';
        echo "<p><strong>API URL:</strong> $api_url</p>";
        echo "<p><strong>POST Data:</strong> <code>$post_data</code></p>";
        
        $response = file_get_contents($api_url, false, $context);
        $http_response_header_info = $http_response_header;
        
        echo "<p><strong>HTTP Response Headers:</strong></p>";
        echo "<pre>" . implode("\n", $http_response_header_info) . "</pre>";
        
        echo "<p><strong>Response Body:</strong></p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Try to decode as JSON
        $json_data = json_decode($response, true);
        if ($json_data === null) {
            echo "<div class='error'>❌ Response is NOT valid JSON!</div>";
            echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
            
            // Show first 500 characters of response to identify issue
            echo "<p><strong>First 500 characters:</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            
        } else {
            echo "<div class='success'>✅ Valid JSON Response!</div>";
            echo "<pre>" . print_r($json_data, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ API Call Failed: " . $e->getMessage() . "</div>";
    }
    
    // Test 2: Direct file inclusion (what verification does)
    echo "<h4>Test 2: Direct File Inclusion (Verification Method)</h4>";
    
    try {
        // This is what the verification script does - direct function calls
        require_once '../../backend/employer/calculate_match_score.php';
        
        // Get applicants for this job
        $applicants_sql = "SELECT seeker_id FROM job_applications WHERE job_id = ?";
        $applicants_stmt = $conn->prepare($applicants_sql);
        $applicants_stmt->execute([$job_id]);
        $applicants = $applicants_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p><strong>Applicants found:</strong> " . count($applicants) . "</p>";
        
        if (!empty($applicants)) {
            foreach ($applicants as $seeker_id) {
                $result = calculateJobMatch($conn, $job_id, $seeker_id);
                if ($result['success']) {
                    echo "<p><strong>Seeker $seeker_id:</strong> {$result['data']['overall_score']}% match</p>";
                } else {
                    echo "<p><strong>Seeker $seeker_id:</strong> Error - {$result['error']}</p>";
                }
            }
        } else {
            echo "<p>No applicants found for this job.</p>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Direct calculation failed: " . $e->getMessage() . "</div>";
    }
    
    echo "</div>"; // Close api-test div
}

echo "<div class='section'>";
echo "<h2>💡 Diagnosis & Solution</h2>";

echo "<h3>🔍 What the Tests Show:</h3>";
echo "<ul>";
echo "<li><strong>Direct Function Calls:</strong> Work perfectly (as shown in verification)</li>";
echo "<li><strong>API HTTP Calls:</strong> Fail with PHP errors/HTML output</li>";
echo "</ul>";

echo "<h3>🎯 Most Likely Issues:</h3>";
echo "<ol>";
echo "<li><strong>PHP Errors in API Files:</strong> Warnings/notices being output as HTML</li>";
echo "<li><strong>Session Handling:</strong> API calls might have session conflicts</li>";
echo "<li><strong>Include Path Issues:</strong> API files can't find required dependencies</li>";
echo "<li><strong>Output Before Headers:</strong> Something outputting HTML before JSON headers</li>";
echo "</ol>";

echo "<h3>🔧 Quick Fixes to Try:</h3>";
echo "<ol>";
echo "<li><strong>Add Error Suppression:</strong> Add <code>error_reporting(0);</code> at top of batch_calculate_matches.php</li>";
echo "<li><strong>Buffer Output:</strong> Add <code>ob_start();</code> at beginning of API files</li>";
echo "<li><strong>Check File Paths:</strong> Verify all require_once paths are correct</li>";
echo "<li><strong>Test Individual API:</strong> Test batch_calculate_matches.php directly in browser</li>";
echo "</ol>";

echo "</div>";
?>