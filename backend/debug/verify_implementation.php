<?php
// backend/debug/verify_implementation.php
// Step-by-step verification of the skills system implementation

echo "<h1>🔍 Implementation Verification</h1>";
echo "<style>
    body{font-family:Arial;margin:20px;background:#f5f5f5;}
    .section{background:white;padding:20px;margin:20px 0;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
    .success{color:#10b981;font-weight:bold;}
    .error{color:#ef4444;font-weight:bold;}
    .warning{color:#f59e0b;font-weight:bold;}
    .info{color:#3b82f6;font-weight:bold;}
    pre{background:#f1f5f9;padding:15px;border-radius:4px;overflow-x:auto;}
    table{width:100%;border-collapse:collapse;margin:10px 0;}
    th,td{padding:8px;border:1px solid #ddd;text-align:left;}
    th{background:#f9f9f9;}
</style>";

require_once '../../backend/db.php';

try {
    echo "<div class='section'>";
    echo "<h2>📋 Step 1: Check Skills API</h2>";
    
    // Test skills API endpoint
    $skills_api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/../employer/get_skills.php';
    echo "<p>Testing: <code>$skills_api_url</code></p>";
    
    $skills_response = @file_get_contents($skills_api_url);
    if ($skills_response) {
        $skills_data = json_decode($skills_response, true);
        if ($skills_data && $skills_data['success']) {
            echo "<span class='success'>✅ Skills API working!</span><br>";
            echo "Categories: " . $skills_data['data']['total_categories'] . "<br>";
            echo "Total Skills: " . $skills_data['data']['total_skills'] . "<br>";
        } else {
            echo "<span class='error'>❌ Skills API returns error</span><br>";
            echo "<pre>" . htmlspecialchars($skills_response) . "</pre>";
        }
    } else {
        echo "<span class='error'>❌ Skills API not accessible</span><br>";
    }
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>🗄️ Step 2: Check Database Tables</h2>";
    
    // Check skills and categories
    $skills_count_sql = "SELECT COUNT(*) as count FROM skills";
    $skills_count = $conn->query($skills_count_sql)->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Available skills in database: <strong>$skills_count</strong><br>";
    
    $categories_count_sql = "SELECT COUNT(*) as count FROM skill_categories";
    $categories_count = $conn->query($categories_count_sql)->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Skill categories in database: <strong>$categories_count</strong><br>";
    
    // Check if job_requirements table has any data
    $job_req_count_sql = "SELECT COUNT(*) as count FROM job_requirements";
    $job_req_count = $conn->query($job_req_count_sql)->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Structured job requirements: <strong>$job_req_count</strong>";
    
    if ($job_req_count == 0) {
        echo " <span class='warning'>⚠️ No structured skills found! You need to post a NEW job with skills selected.</span>";
    } else {
        echo " <span class='success'>✅ Good!</span>";
    }
    echo "<br>";
    
    // Check seeker skills
    $seeker_skills_count_sql = "SELECT COUNT(*) as count FROM seeker_skills";
    $seeker_skills_count = $conn->query($seeker_skills_count_sql)->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Candidate skills in database: <strong>$seeker_skills_count</strong>";
    
    if ($seeker_skills_count == 0) {
        echo " <span class='warning'>⚠️ No candidate skills found! Candidates need to have skills in their profiles.</span>";
    } else {
        echo " <span class='success'>✅ Good!</span>";
    }
    echo "<br>";
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>📊 Step 3: Analyze Current Jobs</h2>";
    
    // Check current jobs and their skills
    $jobs_sql = "
        SELECT 
            jp.job_id,
            jp.job_title,
            jp.employer_id,
            LENGTH(jp.job_requirements) as req_text_length,
            COUNT(jr.skill_id) as structured_skills_count
        FROM job_posts jp
        LEFT JOIN job_requirements jr ON jp.job_id = jr.job_id
        GROUP BY jp.job_id
        ORDER BY jp.created_at DESC
        LIMIT 5
    ";
    
    $jobs_stmt = $conn->prepare($jobs_sql);
    $jobs_stmt->execute();
    $jobs = $jobs_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($jobs)) {
        echo "<span class='error'>❌ No jobs found in database!</span>";
    } else {
        echo "<table>";
        echo "<tr><th>Job ID</th><th>Job Title</th><th>Employer ID</th><th>Text Requirements Length</th><th>Structured Skills Count</th><th>Status</th></tr>";
        
        foreach ($jobs as $job) {
            $status_class = 'error';
            $status_text = '❌ No skills data';
            
            if ($job['structured_skills_count'] > 0) {
                $status_class = 'success';
                $status_text = '✅ Has structured skills';
            } elseif ($job['req_text_length'] > 50) {
                $status_class = 'warning';
                $status_text = '⚠️ Text only';
            }
            
            echo "<tr>";
            echo "<td>{$job['job_id']}</td>";
            echo "<td>{$job['job_title']}</td>";
            echo "<td>{$job['employer_id']}</td>";
            echo "<td>{$job['req_text_length']} chars</td>";
            echo "<td>{$job['structured_skills_count']} skills</td>";
            echo "<td class='$status_class'>$status_text</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>👥 Step 4: Analyze Current Applications</h2>";
    
    // Check applications and candidate skills
    $apps_sql = "
        SELECT 
            ja.application_id,
            ja.job_id,
            ja.seeker_id,
            ja.match_score,
            jp.job_title,
            CONCAT(js.first_name, ' ', js.last_name) as applicant_name,
            COUNT(ss.skill_id) as candidate_skills_count,
            COUNT(jr.skill_id) as job_structured_skills
        FROM job_applications ja
        JOIN job_posts jp ON ja.job_id = jp.job_id
        JOIN job_seekers js ON ja.seeker_id = js.seeker_id
        LEFT JOIN seeker_skills ss ON ja.seeker_id = ss.seeker_id
        LEFT JOIN job_requirements jr ON ja.job_id = jr.job_id
        GROUP BY ja.application_id
        ORDER BY ja.applied_at DESC
        LIMIT 5
    ";
    
    $apps_stmt = $conn->prepare($apps_sql);
    $apps_stmt->execute();
    $applications = $apps_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($applications)) {
        echo "<span class='error'>❌ No job applications found!</span>";
    } else {
        echo "<table>";
        echo "<tr><th>App ID</th><th>Applicant</th><th>Job</th><th>Candidate Skills</th><th>Job Skills</th><th>Current Score</th><th>Matching Potential</th></tr>";
        
        foreach ($applications as $app) {
            $potential_class = 'error';
            $potential_text = '❌ No match possible';
            
            if ($app['candidate_skills_count'] > 0 && $app['job_structured_skills'] > 0) {
                $potential_class = 'success';
                $potential_text = '✅ Can calculate match';
            } elseif ($app['candidate_skills_count'] > 0) {
                $potential_class = 'warning';
                $potential_text = '⚠️ Needs job skills';
            } elseif ($app['job_structured_skills'] > 0) {
                $potential_class = 'warning';
                $potential_text = '⚠️ Needs candidate skills';
            }
            
            echo "<tr>";
            echo "<td>{$app['application_id']}</td>";
            echo "<td>{$app['applicant_name']}</td>";
            echo "<td>{$app['job_title']}</td>";
            echo "<td>{$app['candidate_skills_count']}</td>";
            echo "<td>{$app['job_structured_skills']}</td>";
            echo "<td>{$app['match_score']}%</td>";
            echo "<td class='$potential_class'>$potential_text</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>💡 Step 5: Recommendations</h2>";
    
    $recommendations = [];
    
    if ($job_req_count == 0) {
        $recommendations[] = "🔨 <strong>Create a NEW job with skills selected:</strong> Go to empjoblist.php → Post New Job → Select specific skills → Submit";
    }
    
    if ($seeker_skills_count == 0) {
        $recommendations[] = "👤 <strong>Add skills to candidate profiles:</strong> Candidates need to complete their skill profiles during registration";
    }
    
    if (!empty($applications)) {
        $has_potential = false;
        foreach ($applications as $app) {
            if ($app['candidate_skills_count'] > 0 && $app['job_structured_skills'] > 0) {
                $has_potential = true;
                break;
            }
        }
        
        if ($has_potential) {
            $recommendations[] = "🎯 <strong>Test Calculate Matches:</strong> You have applications with both job skills and candidate skills - the matching should work!";
        }
    }
    
    if (empty($recommendations)) {
        $recommendations[] = "✅ <strong>Data looks good!</strong> Try the Calculate Matches button - it should work now.";
    }
    
    echo "<ol>";
    foreach ($recommendations as $rec) {
        echo "<li>$rec</li>";
    }
    echo "</ol>";
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>🧪 Step 6: Quick Test</h2>";
    echo "<p><strong>Immediate Action Plan:</strong></p>";
    echo "<ol>";
    echo "<li><strong>If job_requirements table is empty:</strong> Post a NEW job with skills selected</li>";
    echo "<li><strong>If seeker_skills table is empty:</strong> Check candidate profiles have skills</li>";
    echo "<li><strong>If both have data:</strong> Calculate Matches should work</li>";
    echo "<li><strong>Create fresh test scenario:</strong> New job + New application = Clean test</li>";
    echo "</ol>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Debug Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>