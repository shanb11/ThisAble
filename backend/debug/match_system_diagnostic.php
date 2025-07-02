<?php
// backend/debug/match_system_diagnostic.php
// Comprehensive diagnostic tool for match calculation system

require_once '../db.php';
session_start();

echo "<h1>🔍 ThisAble Match System Diagnostic Tool</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .success { color: #10b981; font-weight: bold; }
    .error { color: #ef4444; font-weight: bold; }
    .warning { color: #f59e0b; font-weight: bold; }
    .info { color: #3b82f6; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    th { background: #f9f9f9; }
    pre { background: #f1f5f9; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style>";

try {
    echo "<div class='section'>";
    echo "<h2>📊 Database Tables Analysis</h2>";
    
    // 1. Check basic table counts
    $tables_to_check = [
        'job_applications' => 'Total job applications',
        'job_posts' => 'Total job posts',
        'job_seekers' => 'Total job seekers',
        'seeker_skills' => 'Candidate skills entries',
        'skills' => 'Available skills in system',
        'workplace_accommodations' => 'PWD accommodation requests',
        'job_accommodations' => 'Job accommodation offerings',
        'employers' => 'Total employers'
    ];
    
    echo "<table>";
    echo "<tr><th>Table</th><th>Description</th><th>Record Count</th><th>Status</th></tr>";
    
    foreach ($tables_to_check as $table => $description) {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        $status_class = $count > 0 ? 'success' : 'error';
        $status_text = $count > 0 ? '✅ Good' : '❌ Empty';
        
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td>$description</td>";
        echo "<td>$count</td>";
        echo "<td class='$status_class'>$status_text</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 2. Check applicants with complete data
    echo "<div class='section'>";
    echo "<h2>👥 Applicants Data Completeness</h2>";
    
    $applicants_analysis_sql = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            jp.job_title,
            CONCAT(js.first_name, ' ', js.last_name) as applicant_name,
            
            -- Skills check
            (SELECT COUNT(*) FROM seeker_skills ss WHERE ss.seeker_id = ja.seeker_id) as skills_count,
            
            -- Accommodations check  
            (SELECT COUNT(*) FROM workplace_accommodations wa WHERE wa.seeker_id = ja.seeker_id) as accommodations_exists,
            
            -- Job accommodations check
            (SELECT COUNT(*) FROM job_accommodations jac WHERE jac.job_id = ja.job_id) as job_accommodations_exists,
            
            -- Match score check
            ja.match_score
            
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id  
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        ORDER BY ja.applied_at DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($applicants_analysis_sql);
    $stmt->execute();
    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($applicants)) {
        echo "<p class='error'>❌ No job applications found in database!</p>";
        echo "<p>This explains why you're getting 'Processed 0 applicants'</p>";
    } else {
        echo "<table>";
        echo "<tr><th>Application ID</th><th>Applicant</th><th>Job</th><th>Skills Count</th><th>Has Accommodations</th><th>Job Has Accommodations</th><th>Current Match Score</th><th>Data Status</th></tr>";
        
        foreach ($applicants as $app) {
            $data_issues = [];
            if ($app['skills_count'] == 0) $data_issues[] = 'No skills';
            if ($app['accommodations_exists'] == 0) $data_issues[] = 'No accommodations';
            if ($app['job_accommodations_exists'] == 0) $data_issues[] = 'Job missing accommodations';
            
            $status_class = empty($data_issues) ? 'success' : 'warning';
            $status_text = empty($data_issues) ? '✅ Complete' : '⚠️ ' . implode(', ', $data_issues);
            
            echo "<tr>";
            echo "<td>{$app['application_id']}</td>";
            echo "<td>{$app['applicant_name']}</td>";
            echo "<td>{$app['job_title']}</td>";
            echo "<td>{$app['skills_count']}</td>";
            echo "<td>" . ($app['accommodations_exists'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($app['job_accommodations_exists'] ? '✅' : '❌') . "</td>";
            echo "<td>" . ($app['match_score'] ?? 'NULL') . "%</td>";
            echo "<td class='$status_class'>$status_text</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    // 3. Skills system analysis
    echo "<div class='section'>";
    echo "<h2>🎯 Skills System Analysis</h2>";
    
    // Check skills distribution
    $skills_distribution_sql = "
        SELECT 
            sc.category_name,
            COUNT(s.skill_id) as available_skills,
            COUNT(ss.seeker_skill_id) as usage_count
        FROM skill_categories sc
        LEFT JOIN skills s ON sc.category_id = s.category_id
        LEFT JOIN seeker_skills ss ON s.skill_id = ss.skill_id
        GROUP BY sc.category_id, sc.category_name
        ORDER BY usage_count DESC
    ";
    
    $stmt = $conn->prepare($skills_distribution_sql);
    $stmt->execute();
    $skills_dist = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Skills Categories Usage:</h3>";
    echo "<table>";
    echo "<tr><th>Category</th><th>Available Skills</th><th>Times Used by Candidates</th><th>Status</th></tr>";
    
    foreach ($skills_dist as $cat) {
        $status_class = $cat['usage_count'] > 0 ? 'success' : 'warning';
        $status_text = $cat['usage_count'] > 0 ? '✅ In use' : '⚠️ Unused';
        
        echo "<tr>";
        echo "<td>{$cat['category_name']}</td>";
        echo "<td>{$cat['available_skills']}</td>";
        echo "<td>{$cat['usage_count']}</td>";
        echo "<td class='$status_class'>$status_text</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";

    // 4. Test one specific match calculation
    echo "<div class='section'>";
    echo "<h2>🧪 Live Match Calculation Test</h2>";
    
    if (!empty($applicants)) {
        $test_app = $applicants[0]; // Test first applicant
        
        echo "<h3>Testing Match Calculation for:</h3>";
        echo "<p><strong>Applicant:</strong> {$test_app['applicant_name']} (ID: {$test_app['seeker_id']})</p>";
        echo "<p><strong>Job:</strong> {$test_app['job_title']} (ID: {$test_app['job_id']})</p>";
        
        // Include the calculation functions
        include_once '../employer/calculate_match_score.php';
        
        echo "<h4>Detailed Match Calculation:</h4>";
        echo "<pre>";
        
        // Test the actual calculation
        $match_result = calculateJobMatch($conn, $test_app['job_id'], $test_app['seeker_id']);
        
        if ($match_result['success']) {
            $data = $match_result['data'];
            echo "✅ MATCH CALCULATION SUCCESSFUL\n\n";
            echo "Overall Score: {$data['overall_score']}%\n\n";
            
            echo "Skills Match:\n";
            echo "  - Score: {$data['skills_match']['score']}%\n";
            echo "  - Matched Skills: " . implode(', ', $data['skills_match']['matched_skills']) . "\n";
            echo "  - Missing Skills: " . implode(', ', $data['skills_match']['missing_skills']) . "\n";
            echo "  - Total Job Skills: {$data['skills_match']['total_job_skills']}\n\n";
            
            echo "Accommodation Match:\n";
            echo "  - Score: {$data['accommodation_match']['score']}%\n";
            echo "  - Compatible: " . implode(', ', $data['accommodation_match']['compatible_accommodations']) . "\n";
            echo "  - Missing: " . implode(', ', $data['accommodation_match']['missing_accommodations']) . "\n\n";
            
            echo "Preferences Match:\n";
            echo "  - Score: {$data['preferences_match']['score']}%\n";
            echo "  - Factors Evaluated: {$data['preferences_match']['factors_evaluated']}\n\n";
            
            echo "Scoring Breakdown:\n";
            echo "  - Skills (50%): " . ($data['skills_match']['score'] * 0.5) . "\n";
            echo "  - Accommodations (20%): " . ($data['accommodation_match']['score'] * 0.2) . "\n";
            echo "  - Preferences (20%): " . ($data['preferences_match']['score'] * 0.2) . "\n";
            echo "  - Experience (10%): " . (75 * 0.1) . "\n";
            
        } else {
            echo "❌ MATCH CALCULATION FAILED\n";
            echo "Error: {$match_result['error']}\n";
        }
        
        echo "</pre>";
    } else {
        echo "<p class='error'>Cannot test match calculation - no applicants found</p>";
    }
    echo "</div>";

    // 5. Recommendations
    echo "<div class='section'>";
    echo "<h2>💡 Recommendations</h2>";
    
    $recommendations = [];
    
    // Check for common issues
    $tables_empty = [];
    foreach ($tables_to_check as $table => $desc) {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] == 0) {
            $tables_empty[] = $table;
        }
    }
    
    if (in_array('job_applications', $tables_empty)) {
        $recommendations[] = "❌ <strong>Critical:</strong> No job applications exist. You need candidates to apply to jobs first.";
    }
    
    if (in_array('seeker_skills', $tables_empty)) {
        $recommendations[] = "❌ <strong>Critical:</strong> No candidate skills recorded. Check your candidate registration process - skills aren't being saved.";
    }
    
    if (in_array('job_accommodations', $tables_empty)) {
        $recommendations[] = "⚠️ <strong>Important:</strong> No job accommodations defined. PWD-specific matching will be limited.";
    }
    
    if (in_array('workplace_accommodations', $tables_empty)) {
        $recommendations[] = "⚠️ <strong>Important:</strong> No workplace accommodations recorded for candidates. Check candidate registration flow.";
    }
    
    if (empty($recommendations)) {
        $recommendations[] = "✅ Database structure looks good. Issue might be in the matching logic or data relationships.";
    }
    
    $recommendations[] = "🔧 <strong>Next Steps:</strong> Run the test calculation above to see detailed matching breakdown.";
    $recommendations[] = "📝 <strong>Quick Fix:</strong> Try manually adding test skills for one candidate and job requirements to verify the system works.";
    
    echo "<ul>";
    foreach ($recommendations as $rec) {
        echo "<li>$rec</li>";
    }
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Diagnostic Error</h2>";
    echo "<p>Error running diagnostic: " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<h2>🚀 Quick Test Actions</h2>";
echo "<p>To verify your matching system:</p>";
echo "<ol>";
echo "<li><strong>Check if you have test data:</strong> Ensure at least one candidate has skills and one job has requirements</li>";
echo "<li><strong>Manual calculation test:</strong> Run this diagnostic to see the detailed match breakdown</li>";
echo "<li><strong>Frontend test:</strong> Click the Calculate Matches button after ensuring you have proper test data</li>";
echo "<li><strong>API test:</strong> Direct API call to batch_calculate_matches.php with a valid job_id</li>";
echo "</ol>";
echo "</div>";
?>