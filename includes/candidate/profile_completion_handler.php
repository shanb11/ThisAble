<?php
/**
 * Shared Profile Completion Calculator
 * This ensures consistent completion percentage across all pages
 */

if (!function_exists('calculateProfileCompletion')) {
    function calculateProfileCompletion($conn, $seeker_id) {
        $completion_data = [
            'percentage' => 0,
            'sections' => [
                'personal_info' => ['completed' => false, 'weight' => 20, 'label' => 'Personal Information'],
                'skills' => ['completed' => false, 'weight' => 15, 'label' => 'Skills'],
                'work_preferences' => ['completed' => false, 'weight' => 15, 'label' => 'Work Preferences'],
                'accessibility_needs' => ['completed' => false, 'weight' => 10, 'label' => 'Accessibility Needs'],
                'education' => ['completed' => false, 'weight' => 15, 'label' => 'Education History'],
                'experience' => ['completed' => false, 'weight' => 15, 'label' => 'Work Experience'],
                'resume' => ['completed' => false, 'weight' => 10, 'label' => 'Resume Upload']
            ]
        ];
        
        try {
            // Check Personal Information (basic required fields + bio)
            $stmt = $conn->prepare("
                SELECT js.first_name, js.last_name, js.contact_number, js.city, js.province, 
                       pd.bio, ua.email 
                FROM job_seekers js 
                LEFT JOIN profile_details pd ON js.seeker_id = pd.seeker_id
                LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
                WHERE js.seeker_id = ?
            ");
            $stmt->execute([$seeker_id]);
            $personal = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($personal && 
                !empty($personal['first_name']) && 
                !empty($personal['last_name']) && 
                !empty($personal['contact_number']) && 
                !empty($personal['email']) &&
                (!empty($personal['city']) || !empty($personal['province']) || !empty($personal['bio']))) {
                $completion_data['sections']['personal_info']['completed'] = true;
            }
            
            // Check Skills (at least 3 skills required)
            $stmt = $conn->prepare("SELECT COUNT(*) as skill_count FROM seeker_skills WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $skills = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($skills && $skills['skill_count'] >= 3) {
                $completion_data['sections']['skills']['completed'] = true;
            }
            
            // Check Work Preferences
            $stmt = $conn->prepare("
                SELECT work_style, job_type 
                FROM user_preferences 
                WHERE seeker_id = ?
            ");
            $stmt->execute([$seeker_id]);
            $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($preferences && 
                !empty($preferences['work_style']) && 
                !empty($preferences['job_type'])) {
                $completion_data['sections']['work_preferences']['completed'] = true;
            }
            
            // Check Accessibility Needs
            $stmt = $conn->prepare("
                SELECT accommodation_list, no_accommodations_needed 
                FROM workplace_accommodations 
                WHERE seeker_id = ?
            ");
            $stmt->execute([$seeker_id]);
            $accessibility = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($accessibility && 
                (!empty($accessibility['accommodation_list']) || 
                 $accessibility['no_accommodations_needed'] == 1)) {
                $completion_data['sections']['accessibility_needs']['completed'] = true;
            }
            
            // Check Education
            $stmt = $conn->prepare("SELECT COUNT(*) as edu_count FROM education WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $education = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($education && $education['edu_count'] > 0) {
                $completion_data['sections']['education']['completed'] = true;
            }
            
            // Check Experience
            $stmt = $conn->prepare("SELECT COUNT(*) as exp_count FROM experience WHERE seeker_id = ?");
            $stmt->execute([$seeker_id]);
            $experience = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($experience && $experience['exp_count'] > 0) {
                $completion_data['sections']['experience']['completed'] = true;
            }
            
            // Check Resume
            $stmt = $conn->prepare("SELECT COUNT(*) as resume_count FROM resumes WHERE seeker_id = ? AND is_current = 1");
            $stmt->execute([$seeker_id]);
            $resume = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resume && $resume['resume_count'] > 0) {
                $completion_data['sections']['resume']['completed'] = true;
            }
            
            // Calculate total percentage
            $total_weight = 0;
            $completed_weight = 0;
            
            foreach ($completion_data['sections'] as $section) {
                $total_weight += $section['weight'];
                if ($section['completed']) {
                    $completed_weight += $section['weight'];
                }
            }
            
            $completion_data['percentage'] = round(($completed_weight / $total_weight) * 100);
            
            // Debug logging (remove in production)
            error_log("Profile completion debug for seeker_id $seeker_id:");
            error_log("Completed weight: $completed_weight, Total weight: $total_weight");
            error_log("Percentage: " . $completion_data['percentage']);
            foreach ($completion_data['sections'] as $key => $section) {
                error_log("Section $key: " . ($section['completed'] ? 'completed' : 'incomplete') . " (weight: {$section['weight']})");
            }
            
        } catch (Exception $e) {
            error_log("Profile completion calculation error: " . $e->getMessage());
            // Return default values on error
            $completion_data['percentage'] = 0;
        }
        
        return $completion_data;
    }
}

if (!function_exists('getCompletionMessage')) {
    function getCompletionMessage($percentage) {
        if ($percentage >= 90) {
            return 'Excellent! Your profile is nearly complete and highly visible to employers.';
        } elseif ($percentage >= 70) {
            return 'Great progress! Complete a few more sections to maximize your visibility.';
        } elseif ($percentage >= 50) {
            return 'Good start! Adding more information will help employers find you.';
        } else {
            return 'Complete your profile to increase your visibility to employers by up to 40%.';
        }
    }
}
?>