<?php
// backend/candidate/get_compatibility_score.php
// NEW FILE - ADD-ON compatibility feature, doesn't touch existing job listings

header('Content-Type: application/json');
require_once '../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['seeker_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$job_id = $_GET['job_id'] ?? null;
$seeker_id = $_SESSION['seeker_id'];

if (!$job_id) {
    echo json_encode(['success' => false, 'error' => 'Job ID required']);
    exit;
}

try {
    // Get job details
    $job_query = "
        SELECT jp.*, ja.*, e.company_name 
        FROM job_posts jp 
        LEFT JOIN job_accommodations ja ON jp.job_id = ja.job_id
        LEFT JOIN employers e ON jp.employer_id = e.employer_id
        WHERE jp.job_id = ?
    ";
    $job_stmt = $conn->prepare($job_query);
    $job_stmt->execute([$job_id]);
    $job = $job_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) {
        echo json_encode(['success' => false, 'error' => 'Job not found']);
        exit;
    }

    // Get seeker profile for compatibility
    $seeker_query = "
        SELECT js.*, dt.disability_name, dc.category_name as disability_category,
               up.work_style, up.job_type, wa.accommodation_list, wa.no_accommodations_needed
        FROM job_seekers js
        LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
        LEFT JOIN disability_categories dc ON dt.category_id = dc.category_id
        LEFT JOIN user_preferences up ON js.seeker_id = up.seeker_id
        LEFT JOIN workplace_accommodations wa ON js.seeker_id = wa.seeker_id
        WHERE js.seeker_id = ?
    ";
    $seeker_stmt = $conn->prepare($seeker_query);
    $seeker_stmt->execute([$seeker_id]);
    $seeker = $seeker_stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate compatibility
    $compatibility = calculateJobCompatibility($job, $seeker);

    echo json_encode([
        'success' => true,
        'compatibility' => $compatibility
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Compatibility calculation failed']);
}

function calculateJobCompatibility($job, $seeker) {
    $scores = [];
    $total_weight = 0;
    $weighted_score = 0;

    // 1. Accommodation match (40% weight)
    $acc_score = calculateAccommodationScore($job, $seeker);
    $scores['accommodations'] = $acc_score;
    $weighted_score += $acc_score['score'] * 0.4;
    $total_weight += 0.4;

    // 2. Work style match (30% weight)
    $work_score = calculateWorkStyleScore($job, $seeker);
    $scores['work_style'] = $work_score;
    $weighted_score += $work_score['score'] * 0.3;
    $total_weight += 0.3;

    // 3. Employment type (20% weight)
    $emp_score = calculateEmploymentScore($job, $seeker);
    $scores['employment'] = $emp_score;
    $weighted_score += $emp_score['score'] * 0.2;
    $total_weight += 0.2;

    // 4. Location (10% weight)
    $loc_score = calculateLocationScore($job, $seeker);
    $scores['location'] = $loc_score;
    $weighted_score += $loc_score['score'] * 0.1;
    $total_weight += 0.1;

    $final_percentage = round($weighted_score / $total_weight);
    
    $level = 'low';
    if ($final_percentage >= 85) $level = 'excellent';
    elseif ($final_percentage >= 70) $level = 'high';
    elseif ($final_percentage >= 55) $level = 'medium';

    return [
        'percentage' => $final_percentage,
        'level' => $level,
        'factors' => $scores,
        'summary' => generateSummary($final_percentage, $scores)
    ];
}

function calculateAccommodationScore($job, $seeker) {
    if ($seeker['no_accommodations_needed']) {
        return [
            'score' => 100,
            'explanation' => 'Perfect match - no specific accommodations needed'
        ];
    }

    $needed = json_decode($seeker['accommodation_list'] ?: '[]', true);
    if (empty($needed)) {
        return [
            'score' => 80,
            'explanation' => 'Good match - accommodation needs not specified'
        ];
    }

    $available = [
        'wheelchair_accessible' => $job['wheelchair_accessible'] ?? 0,
        'flexible_schedule' => $job['flexible_schedule'] ?? 0,
        'assistive_technology' => $job['assistive_technology'] ?? 0,
        'remote_work_option' => $job['remote_work_option'] ?? 0,
        'screen_reader_compatible' => $job['screen_reader_compatible'] ?? 0
    ];

    $matched = 0;
    $total = count($needed);

    foreach ($needed as $need) {
        $need_key = strtolower(str_replace(' ', '_', $need));
        if (isset($available[$need_key]) && $available[$need_key]) {
            $matched++;
        }
    }

    $score = $total > 0 ? ($matched / $total) * 100 : 80;
    
    return [
        'score' => $score,
        'explanation' => "$matched of $total accommodations available"
    ];
}

function calculateWorkStyleScore($job, $seeker) {
    $seeker_style = $seeker['work_style'] ?? 'onsite';
    $job_remote = $job['remote_work_available'] ?? 0;
    $job_flexible = $job['flexible_schedule'] ?? 0;

    $score = 70; // default
    $explanation = 'Compatible work arrangement';

    if ($seeker_style === 'remote' && $job_remote) {
        $score = 100;
        $explanation = 'Perfect match - remote work available';
    } elseif ($seeker_style === 'hybrid' && ($job_remote || $job_flexible)) {
        $score = 90;
        $explanation = 'Excellent match - flexible work options';
    } elseif ($seeker_style === 'onsite') {
        $score = 85;
        $explanation = 'Good match - traditional work setup';
    }

    return ['score' => $score, 'explanation' => $explanation];
}

function calculateEmploymentScore($job, $seeker) {
    $seeker_type = $seeker['job_type'] ?? 'fulltime';
    $job_type = strtolower(str_replace('-', '', $job['employment_type'] ?? ''));

    $score = 60; // default
    if ($seeker_type === $job_type) {
        $score = 100;
    }

    return [
        'score' => $score,
        'explanation' => $score === 100 ? 'Perfect employment type match' : 'Different employment type'
    ];
}

function calculateLocationScore($job, $seeker) {
    $job_location = strtolower($job['location'] ?? '');
    $seeker_city = strtolower($seeker['city'] ?? '');
    
    $score = 60; // default
    $explanation = 'Location compatibility check';

    if (stripos($job_location, 'remote') !== false) {
        $score = 95;
        $explanation = 'Remote work eliminates location barriers';
    } elseif (!empty($seeker_city) && stripos($job_location, $seeker_city) !== false) {
        $score = 100;
        $explanation = 'Job located in your city';
    }

    return ['score' => $score, 'explanation' => $explanation];
}

function generateSummary($percentage, $factors) {
    if ($percentage >= 85) return 'Excellent match for your profile';
    if ($percentage >= 70) return 'Strong compatibility with your needs';
    if ($percentage >= 55) return 'Good match with some considerations';
    return 'Limited compatibility - review details';
}
?>