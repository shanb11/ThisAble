<?php
// backend/debug/quick_match_test.php
// Quick test to verify match calculation after SQL fix

require_once '../db.php';
require_once '../employer/calculate_match_score.php';

echo "<h1>🧪 Quick Match Test</h1>\n";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:4px;}</style>";

try {
    // Get test applications
    $sql = "SELECT ja.application_id, ja.job_id, ja.seeker_id, 
                   jp.job_title, jp.job_requirements,
                   CONCAT(js.first_name, ' ', js.last_name) as applicant_name
            FROM job_applications ja
            JOIN job_posts jp ON ja.job_id = jp.job_id  
            JOIN job_seekers js ON ja.seeker_id = js.seeker_id
            LIMIT 3";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($applications)) {
        echo "<p class='error'>❌ No applications found for testing!</p>";
        exit;
    }
    
    echo "<h2>🎯 Testing Match Calculations</h2>";
    
    foreach ($applications as $app) {
        echo "<div style='border:1px solid #ccc; padding:15px; margin:10px 0; border-radius:8px;'>";
        echo "<h3>{$app['applicant_name']} → {$app['job_title']}</h3>";
        echo "<p><strong>Job Requirements:</strong> {$app['job_requirements']}</p>";
        
        // Test skill extraction first
        $extracted_skills = extractSkillsFromJobText($conn, $app['job_requirements']);
        echo "<p><strong>Extracted Skills:</strong> ";
        if (empty($extracted_skills)) {
            echo "<span class='error'>None found</span>";
        } else {
            $skill_names = array_column($extracted_skills, 'skill_name');
            echo "<span class='success'>" . implode(', ', $skill_names) . "</span>";
        }
        echo "</p>";
        
        // Test full match calculation
        $match_result = calculateJobMatch($conn, $app['job_id'], $app['seeker_id']);
        
        echo "<pre>";
        if ($match_result['success']) {
            $data = $match_result['data'];
            echo "✅ MATCH SUCCESSFUL!\n";
            echo "Overall Score: {$data['overall_score']}%\n\n";
            
            echo "Skills Match: {$data['skills_match']['score']}% (50% weight)\n";
            echo "  Total Job Skills: {$data['skills_match']['total_job_skills']}\n";
            echo "  Matched: " . implode(', ', $data['skills_match']['matched_skills']) . "\n";
            echo "  Missing: " . implode(', ', $data['skills_match']['missing_skills']) . "\n\n";
            
            echo "Accommodation Match: {$data['accommodation_match']['score']}% (20% weight)\n";
            echo "Preferences Match: {$data['preferences_match']['score']}% (20% weight)\n";
            echo "Experience Match: 75% (10% weight)\n";
            
            // Scoring breakdown
            echo "\nScoring Breakdown:\n";
            echo "Skills: " . ($data['skills_match']['score'] * 0.5) . " points\n";
            echo "Accommodations: " . ($data['accommodation_match']['score'] * 0.2) . " points\n";
            echo "Preferences: " . ($data['preferences_match']['score'] * 0.2) . " points\n";
            echo "Experience: 7.5 points\n";
            
        } else {
            echo "❌ MATCH FAILED: {$match_result['error']}\n";
        }
        echo "</pre>";
        echo "</div>";
    }
    
    echo "<h2>🚀 Next Steps</h2>";
    echo "<ol>";
    echo "<li>If you see realistic scores (not all 97.5%), proceed to frontend test</li>";
    echo "<li>Go to empapplicants.php and click 'Calculate Matches'</li>";
    echo "<li>You should see the same realistic scores in the frontend</li>";
    echo "<li>If frontend still shows 0%, check browser console for errors</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}
?>