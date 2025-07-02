<?php
// backend/debug/fix_job_requirements.php
// Update existing jobs with realistic requirements that contain actual skills

require_once '../db.php';
session_start();

echo "<h1>🔧 Fix Job Requirements with Real Skills</h1>\n";
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
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    th { background: #f9f9f9; }
</style>";

try {
    // First, let's see what skills are available
    echo "<div class='section'>";
    echo "<h2>💡 Available Skills in Database</h2>";
    
    $skills_sql = "SELECT s.skill_name, sc.category_name 
                  FROM skills s 
                  JOIN skill_categories sc ON s.category_id = sc.category_id 
                  ORDER BY sc.category_name, s.skill_name";
    $skills_stmt = $conn->prepare($skills_sql);
    $skills_stmt->execute();
    $all_skills = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Skill Category</th><th>Skill Name</th></tr>";
    foreach ($all_skills as $skill) {
        echo "<tr><td>{$skill['category_name']}</td><td>{$skill['skill_name']}</td></tr>";
    }
    echo "</table>";
    echo "</div>";

    // Show current job requirements
    echo "<div class='section'>";
    echo "<h2>📋 Current Job Requirements (BEFORE Fix)</h2>";
    
    $jobs_sql = "SELECT job_id, job_title, job_requirements FROM job_posts";
    $jobs_stmt = $conn->prepare($jobs_sql);
    $jobs_stmt->execute();
    $jobs = $jobs_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Job ID</th><th>Job Title</th><th>Current Requirements</th></tr>";
    foreach ($jobs as $job) {
        echo "<tr>";
        echo "<td>{$job['job_id']}</td>";
        echo "<td>{$job['job_title']}</td>";
        echo "<td><code>{$job['job_requirements']}</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // Handle the fix action
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fix_requirements') {
        echo "<div class='section'>";
        echo "<h2>🔨 Fixing Job Requirements</h2>";
        
        // Define realistic job requirements with actual skill names
        $realistic_requirements = [
            1 => "Looking for candidates with Digital Literacy, Data Entry, Microsoft Office, and Customer Service skills. Must have experience with basic troubleshooting and problem resolution.",
            2 => "Seeking professionals with Basic Coding skills, Web Development experience, Digital Literacy, and Data Entry capabilities. Knowledge of Microsoft Office and customer service preferred."
        ];
        
        foreach ($realistic_requirements as $job_id => $new_requirements) {
            $update_sql = "UPDATE job_posts SET job_requirements = :requirements WHERE job_id = :job_id";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                'requirements' => $new_requirements,
                'job_id' => $job_id
            ]);
            
            echo "<p class='success'>✅ Updated Job ID $job_id with realistic requirements</p>";
        }
        
        echo "<h3>Updated Requirements:</h3>";
        echo "<table>";
        echo "<tr><th>Job ID</th><th>New Requirements</th></tr>";
        foreach ($realistic_requirements as $job_id => $requirements) {
            echo "<tr>";
            echo "<td>$job_id</td>";
            echo "<td>$requirements</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Test the extraction after update
        echo "<div class='section'>";
        echo "<h2>🧪 Testing Skill Extraction After Fix</h2>";
        
        include_once '../employer/calculate_match_score.php';
        
        foreach ($realistic_requirements as $job_id => $requirements) {
            echo "<h3>Job ID $job_id:</h3>";
            echo "<p><strong>Requirements:</strong> $requirements</p>";
            
            $extracted_skills = extractSkillsFromJobText($conn, $requirements);
            
            echo "<p><strong>Extracted Skills:</strong></p>";
            if (empty($extracted_skills)) {
                echo "<p class='error'>❌ No skills extracted!</p>";
            } else {
                echo "<ul>";
                foreach ($extracted_skills as $skill) {
                    echo "<li class='success'>✅ {$skill['skill_name']} (Match type: {$skill['match_type']})</li>";
                }
                echo "</ul>";
                echo "<p class='success'>✅ Found " . count($extracted_skills) . " skills</p>";
            }
            echo "<hr>";
        }
        echo "</div>";
        
        // Test a full match calculation
        echo "<div class='section'>";
        echo "<h2>🎯 Testing Full Match Calculation After Fix</h2>";
        
        // Get first application to test
        $app_sql = "SELECT ja.*, jp.job_title, CONCAT(js.first_name, ' ', js.last_name) as applicant_name
                   FROM job_applications ja 
                   JOIN job_posts jp ON ja.job_id = jp.job_id 
                   JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                   LIMIT 1";
        $app_stmt = $conn->prepare($app_sql);
        $app_stmt->execute();
        $test_app = $app_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($test_app) {
            echo "<p><strong>Testing:</strong> {$test_app['applicant_name']} → {$test_app['job_title']}</p>";
            
            $match_result = calculateJobMatch($conn, $test_app['job_id'], $test_app['seeker_id']);
            
            echo "<pre>";
            if ($match_result['success']) {
                $data = $match_result['data'];
                echo "🎉 MATCH CALCULATION SUCCESSFUL!\n\n";
                echo "Overall Score: {$data['overall_score']}%\n\n";
                
                echo "Skills Match: {$data['skills_match']['score']}%\n";
                echo "  → Total Job Skills: {$data['skills_match']['total_job_skills']}\n";
                echo "  → Matched: " . implode(', ', $data['skills_match']['matched_skills']) . "\n";
                echo "  → Missing: " . implode(', ', $data['skills_match']['missing_skills']) . "\n\n";
                
                echo "✅ Skills extraction is now working!\n";
                if ($data['skills_match']['total_job_skills'] > 0) {
                    echo "✅ Job now has {$data['skills_match']['total_job_skills']} required skills\n";
                } else {
                    echo "❌ Still no skills found - check skill extraction logic\n";
                }
            } else {
                echo "❌ Match calculation failed: {$match_result['error']}\n";
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

if (!isset($_POST['action'])) {
    echo "<div class='section'>";
    echo "<h2>🚀 Ready to Fix?</h2>";
    echo "<p>This will update your job requirements to include actual skill names that can be matched against candidate skills.</p>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='action' value='fix_requirements'>Fix Job Requirements Now</button>";
    echo "</form>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>🎯 What This Fix Does</h2>";
echo "<ul>";
echo "<li><strong>Problem:</strong> Current job requirements (\"Test\", \"Test Qualifications\") don't contain recognizable skill names</li>";
echo "<li><strong>Solution:</strong> Updates requirements to include actual skill names from your skills database</li>";
echo "<li><strong>Result:</strong> Match calculation will find actual skill overlaps and give realistic scores</li>";
echo "<li><strong>Next Step:</strong> After fixing, test the Calculate Matches button in empapplicants.php</li>";
echo "</ul>";
echo "</div>";
?>