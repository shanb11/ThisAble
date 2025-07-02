<?php
// backend/debug/create_test_data.php
// Generate test data to verify match calculation system

require_once '../db.php';
session_start();

echo "<h1>🧪 Test Data Generator for Match System</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #10b981; font-weight: bold; }
    .error { color: #ef4444; font-weight: bold; }
    .warning { color: #f59e0b; font-weight: bold; }
    .info { color: #3b82f6; font-weight: bold; }
    button { background: #3b82f6; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    button:hover { background: #2563eb; }
    pre { background: #f1f5f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        
        if ($action === 'create_test_candidate') {
            echo "<div class='section'>";
            echo "<h2>👤 Creating Test Candidate with Skills</h2>";
            
            // Create test job seeker
            $seeker_sql = "INSERT INTO job_seekers (first_name, last_name, contact_number, city, province, disability_id) 
                          VALUES ('Test', 'Candidate', '09123456789', 'Manila', 'Metro Manila', 1)";
            $conn->exec($seeker_sql);
            $seeker_id = $conn->lastInsertId();
            
            echo "<p class='success'>✅ Created job seeker with ID: $seeker_id</p>";
            
            // Create user account
            $account_sql = "INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                           VALUES ($seeker_id, 'test.candidate@thisable.test', 'test_hash', 0)";
            $conn->exec($account_sql);
            
            // Add test skills (assuming skills already exist)
            $test_skills = [1, 2, 3, 4, 5]; // First 5 skills from skills table
            foreach ($test_skills as $skill_id) {
                $skill_sql = "INSERT IGNORE INTO seeker_skills (seeker_id, skill_id) VALUES ($seeker_id, $skill_id)";
                $conn->exec($skill_sql);
            }
            
            echo "<p class='success'>✅ Added 5 test skills to candidate</p>";
            
            // Add workplace accommodations
            $acc_sql = "INSERT INTO workplace_accommodations (seeker_id, accommodation_list, no_accommodations_needed) 
                       VALUES ($seeker_id, '{\"wheelchair_accessible\":true,\"screen_reader_compatible\":true}', 0)";
            $conn->exec($acc_sql);
            
            echo "<p class='success'>✅ Added workplace accommodations</p>";
            echo "</div>";
        }
        
        if ($action === 'create_test_job') {
            echo "<div class='section'>";
            echo "<h2>💼 Creating Test Job with Requirements</h2>";
            
            // Get first employer
            $emp_sql = "SELECT employer_id FROM employers LIMIT 1";
            $emp_stmt = $conn->prepare($emp_sql);
            $emp_stmt->execute();
            $employer = $emp_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$employer) {
                echo "<p class='error'>❌ No employers found. Please create an employer first.</p>";
            } else {
                $employer_id = $employer['employer_id'];
                
                // Create test job
                $job_sql = "INSERT INTO job_posts (employer_id, job_title, job_description, job_requirements, employment_type, location, salary_range, application_deadline, status) 
                           VALUES ($employer_id, 'Test Software Developer', 'Test job for matching system', 'PHP programming, JavaScript, MySQL, HTML/CSS, Problem solving', 'full-time', 'Manila, Philippines', '30000-50000', DATE_ADD(NOW(), INTERVAL 30 DAY), 'active')";
                $conn->exec($job_sql);
                $job_id = $conn->lastInsertId();
                
                echo "<p class='success'>✅ Created job post with ID: $job_id</p>";
                
                // Add job accommodations
                $job_acc_sql = "INSERT INTO job_accommodations (job_id, wheelchair_accessible, assistive_technology, remote_work_option, screen_reader_compatible, sign_language_interpreter, modified_workspace) 
                               VALUES ($job_id, 1, 1, 1, 1, 0, 1)";
                $conn->exec($job_acc_sql);
                
                echo "<p class='success'>✅ Added job accommodations</p>";
                echo "</div>";
            }
        }
        
        if ($action === 'create_test_application') {
            echo "<div class='section'>";
            echo "<h2>📝 Creating Test Job Application</h2>";
            
            // Get test candidate and job
            $candidate_sql = "SELECT seeker_id FROM job_seekers WHERE first_name = 'Test' AND last_name = 'Candidate' LIMIT 1";
            $candidate_stmt = $conn->prepare($candidate_sql);
            $candidate_stmt->execute();
            $candidate = $candidate_stmt->fetch(PDO::FETCH_ASSOC);
            
            $job_sql = "SELECT job_id FROM job_posts WHERE job_title = 'Test Software Developer' LIMIT 1";
            $job_stmt = $conn->prepare($job_sql);
            $job_stmt->execute();
            $job = $job_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$candidate || !$job) {
                echo "<p class='error'>❌ Test candidate or job not found. Create them first.</p>";
            } else {
                $seeker_id = $candidate['seeker_id'];
                $job_id = $job['job_id'];
                
                // Check if application already exists
                $check_sql = "SELECT application_id FROM job_applications WHERE seeker_id = $seeker_id AND job_id = $job_id";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->execute();
                $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    echo "<p class='warning'>⚠️ Application already exists with ID: {$existing['application_id']}</p>";
                } else {
                    $app_sql = "INSERT INTO job_applications (job_id, seeker_id, application_status, applied_at, cover_letter) 
                               VALUES ($job_id, $seeker_id, 'submitted', NOW(), 'Test cover letter for matching system verification')";
                    $conn->exec($app_sql);
                    $app_id = $conn->lastInsertId();
                    
                    echo "<p class='success'>✅ Created job application with ID: $app_id</p>";
                }
            }
            echo "</div>";
        }
        
        if ($action === 'test_match_calculation') {
            echo "<div class='section'>";
            echo "<h2>🎯 Testing Match Calculation</h2>";
            
            // Get test application
            $test_sql = "SELECT ja.*, jp.job_title, CONCAT(js.first_name, ' ', js.last_name) as applicant_name
                        FROM job_applications ja 
                        JOIN job_posts jp ON ja.job_id = jp.job_id 
                        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                        WHERE jp.job_title = 'Test Software Developer' 
                        AND js.first_name = 'Test' AND js.last_name = 'Candidate'
                        LIMIT 1";
            $test_stmt = $conn->prepare($test_sql);
            $test_stmt->execute();
            $test_app = $test_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$test_app) {
                echo "<p class='error'>❌ No test application found. Create test data first.</p>";
            } else {
                echo "<p><strong>Testing match for:</strong> {$test_app['applicant_name']} → {$test_app['job_title']}</p>";
                
                // Include calculation functions
                include_once '../employer/calculate_match_score.php';
                
                echo "<pre>";
                $match_result = calculateJobMatch($conn, $test_app['job_id'], $test_app['seeker_id']);
                
                if ($match_result['success']) {
                    $data = $match_result['data'];
                    echo "🎉 MATCH CALCULATION SUCCESSFUL!\n\n";
                    echo "Overall Score: {$data['overall_score']}%\n\n";
                    
                    echo "Detailed Breakdown:\n";
                    echo "==================\n";
                    echo "Skills Match: {$data['skills_match']['score']}% (Weight: 50%)\n";
                    echo "  → Contribution: " . ($data['skills_match']['score'] * 0.5) . " points\n";
                    echo "  → Matched: " . implode(', ', $data['skills_match']['matched_skills']) . "\n";
                    echo "  → Missing: " . implode(', ', $data['skills_match']['missing_skills']) . "\n\n";
                    
                    echo "Accommodation Match: {$data['accommodation_match']['score']}% (Weight: 20%)\n";
                    echo "  → Contribution: " . ($data['accommodation_match']['score'] * 0.2) . " points\n\n";
                    
                    echo "Preferences Match: {$data['preferences_match']['score']}% (Weight: 20%)\n";
                    echo "  → Contribution: " . ($data['preferences_match']['score'] * 0.2) . " points\n\n";
                    
                    echo "Experience Match: 75% (Weight: 10%)\n";
                    echo "  → Contribution: 7.5 points\n\n";
                    
                    if ($data['overall_score'] > 0) {
                        echo "✅ SUCCESS: Match calculation is working!\n";
                        echo "The candidate scored {$data['overall_score']}% overall match.\n";
                    } else {
                        echo "⚠️ WARNING: Score is 0%. Check skills and job requirements.\n";
                    }
                    
                } else {
                    echo "❌ CALCULATION FAILED: {$match_result['error']}\n";
                }
                echo "</pre>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h2>❌ Error</h2>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
}

// Show current data status
echo "<div class='section'>";
echo "<h2>📊 Current Test Data Status</h2>";

try {
    // Check for test data
    $candidate_check = "SELECT COUNT(*) as count FROM job_seekers WHERE first_name = 'Test' AND last_name = 'Candidate'";
    $job_check = "SELECT COUNT(*) as count FROM job_posts WHERE job_title = 'Test Software Developer'";
    $app_check = "SELECT COUNT(*) as count FROM job_applications ja 
                  JOIN job_posts jp ON ja.job_id = jp.job_id 
                  JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                  WHERE jp.job_title = 'Test Software Developer' 
                  AND js.first_name = 'Test' AND js.last_name = 'Candidate'";
    
    $candidate_stmt = $conn->prepare($candidate_check);
    $candidate_stmt->execute();
    $has_candidate = $candidate_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    $job_stmt = $conn->prepare($job_check);
    $job_stmt->execute();
    $has_job = $job_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    $app_stmt = $conn->prepare($app_check);
    $app_stmt->execute();
    $has_application = $app_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    echo "<ul>";
    echo "<li>Test Candidate: " . ($has_candidate ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</li>";
    echo "<li>Test Job: " . ($has_job ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</li>";
    echo "<li>Test Application: " . ($has_application ? "<span class='success'>✅ Exists</span>" : "<span class='error'>❌ Missing</span>") . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error checking test data: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Action buttons
echo "<div class='section'>";
echo "<h2>🚀 Quick Actions</h2>";
echo "<form method='POST'>";

echo "<h3>Step 1: Create Test Data</h3>";
echo "<button type='submit' name='action' value='create_test_candidate'>1. Create Test Candidate with Skills</button>";
echo "<button type='submit' name='action' value='create_test_job'>2. Create Test Job with Requirements</button>";
echo "<button type='submit' name='action' value='create_test_application'>3. Create Test Application</button>";

echo "<h3>Step 2: Test the System</h3>";
echo "<button type='submit' name='action' value='test_match_calculation'>4. Test Match Calculation</button>";

echo "</form>";

echo "<h3>Step 3: Test in Frontend</h3>";
echo "<p>After creating test data:</p>";
echo "<ol>";
echo "<li>Go to your empapplicants.php page</li>";
echo "<li>Click the 'Calculate Matches' button</li>";
echo "<li>You should see scores instead of 0%</li>";
echo "</ol>";

echo "</div>";
?>