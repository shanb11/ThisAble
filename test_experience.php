<?php
// test_experience.php (sa root directory mo)
require_once 'backend/db.php';  // ✅ Correct path from root
require_once 'backend/employer/calculate_match_score.php';  // ✅ Correct path

// Replace with actual seeker_id from your database
$test_seeker_id = 4; 

debugExperienceCalculation($conn, $test_seeker_id);
?>