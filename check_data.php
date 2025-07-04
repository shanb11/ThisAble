<?php
require_once 'backend/db.php';

// Simple function test without the complex calculate_match_score.php
function testExperienceCalc($conn, $seeker_id) {
    $sql = "SELECT start_date, end_date, is_current FROM experience WHERE seeker_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$seeker_id]);
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($experiences)) {
        return 0;
    }
    
    $total_months = 0;
    foreach ($experiences as $exp) {
        $start = new DateTime($exp['start_date']);
        $end = $exp['is_current'] ? new DateTime() : new DateTime($exp['end_date']);
        $interval = $start->diff($end);
        $months = ($interval->y * 12) + $interval->m;
        $total_months += $months;
    }
    
    return round($total_months / 12, 1);
}

$seeker_id = 4;
$years = testExperienceCalc($conn, $seeker_id);
echo "Seeker {$seeker_id} has {$years} years of experience";
?>