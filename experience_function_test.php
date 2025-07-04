<?php
require_once 'backend/db.php';

// Copy just the experience functions directly here to test
function getTotalExperienceYears($conn, $seeker_id) {
    $sql = "SELECT 
                start_date,
                end_date,
                is_current
            FROM experience 
            WHERE seeker_id = :seeker_id 
            ORDER BY start_date";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':seeker_id', $seeker_id);
    $stmt->execute();
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($experiences)) {
        return 0;
    }
    
    $total_months = 0;
    
    foreach ($experiences as $exp) {
        $start_date = new DateTime($exp['start_date']);
        
        if ($exp['is_current']) {
            $end_date = new DateTime();
        } else {
            $end_date = new DateTime($exp['end_date']);
        }
        
        $interval = $start_date->diff($end_date);
        $months = ($interval->y * 12) + $interval->m;
        
        $total_months += $months;
    }
    
    return round($total_months / 12, 1);
}

function extractRequiredYears($job_data) {
    $text = strtolower($job_data['job_requirements'] . ' ' . $job_data['job_description']);
    
    $patterns = [
        '/(\d+)\+?\s*years?\s*(of\s*)?(experience|exp)/i',
        '/(?:minimum|at least)\s*(\d+)\s*years?/i',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $matches)) {
            return (int)$matches[1];
        }
    }
    
    return 0;
}

function calculateYearsScore($candidate_years, $required_years) {
    if ($required_years == 0) {
        if ($candidate_years >= 5) return 90;
        if ($candidate_years >= 3) return 85;
        if ($candidate_years >= 2) return 80;
        if ($candidate_years >= 1) return 75;
        if ($candidate_years >= 0.5) return 65;
        return 50;
    }
    
    if ($candidate_years >= $required_years) {
        $excess = $candidate_years - $required_years;
        if ($excess >= 3) return 100;
        elseif ($excess >= 1) return 95;
        else return 90;
    } else {
        $deficit = $required_years - $candidate_years;
        if ($deficit <= 0.5) return 85;
        elseif ($deficit <= 1) return 75;
        elseif ($deficit <= 2) return 65;
        else return 45;
    }
}

function calculateExperienceMatch($job_data, $candidate_data) {
    global $conn;
    
    $total_years = getTotalExperienceYears($conn, $candidate_data['seeker_id']);
    $required_years = extractRequiredYears($job_data);
    $score = calculateYearsScore($total_years, $required_years);
    
    return [
        'score' => $score,
        'candidate_years' => $total_years,
        'required_years' => $required_years,
        'analysis' => "Candidate: {$total_years} years, Required: {$required_years} years"
    ];
}

// Test the functions
$seeker_id = 4;
echo "<h2>Experience Function Test</h2>";

echo "<h3>Test 1: Basic Years Calculation</h3>";
$years = getTotalExperienceYears($conn, $seeker_id);
echo "Total years: {$years}<br>";

echo "<h3>Test 2: Job with No Requirements</h3>";
$job_no_req = [
    'job_requirements' => 'Looking for a dedicated person',
    'job_description' => 'Join our team'
];
$result1 = calculateExperienceMatch($job_no_req, ['seeker_id' => $seeker_id]);
echo "Score: {$result1['score']}% | {$result1['analysis']}<br>";

echo "<h3>Test 3: Job Requiring 1 Year</h3>";
$job_1_year = [
    'job_requirements' => 'Must have 1+ years experience',
    'job_description' => 'Entry level position'
];
$result2 = calculateExperienceMatch($job_1_year, ['seeker_id' => $seeker_id]);
echo "Score: {$result2['score']}% | {$result2['analysis']}<br>";

echo "<h3>Test 4: Job Requiring 3 Years</h3>";
$job_3_years = [
    'job_requirements' => 'Minimum 3 years experience required',
    'job_description' => 'Senior position'
];
$result3 = calculateExperienceMatch($job_3_years, ['seeker_id' => $seeker_id]);
echo "Score: {$result3['score']}% | {$result3['analysis']}<br>";

echo "<h3>Test 5: Job Requiring 5 Years</h3>";
$job_5_years = [
    'job_requirements' => 'We need 5+ years of experience',
    'job_description' => 'Expert level role'
];
$result4 = calculateExperienceMatch($job_5_years, ['seeker_id' => $seeker_id]);
echo "Score: {$result4['score']}% | {$result4['analysis']}<br>";
?>