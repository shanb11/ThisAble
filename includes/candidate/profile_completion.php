<?php
// Include the shared profile completion handler
require_once __DIR__ . '/profile_completion_handler.php';

// Calculate the completion data using the shared function
$completion_info = calculateProfileCompletion($conn, $seeker_id);
$completion_percentage = $completion_info['percentage'];
$sections = $completion_info['sections'];

// Get completion message
$completion_message = getCompletionMessage($completion_percentage);
?>

<div class="profile-completion-status">
    <div class="completion-header">
        <h3>Profile Completion</h3>
        <span class="completion-percentage"><?php echo $completion_percentage; ?>%</span>
    </div>
    <div class="progress-bar">
        <div class="progress" style="width: <?php echo $completion_percentage; ?>%;"></div>
    </div>
    <div class="completion-tips">
        <p><i class="fas fa-info-circle"></i> <?php echo $completion_message; ?></p>
        <ul class="completion-checklist">
            <?php foreach ($sections as $key => $section): ?>
                <li class="<?php echo $section['completed'] ? 'completed' : 'incomplete'; ?>">
                    <i class="fas <?php echo $section['completed'] ? 'fa-check-circle' : 'far fa-circle'; ?>"></i>
                    <?php echo $section['label']; ?>
                    <span class="section-weight">(<?php echo $section['weight']; ?>%)</span>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <?php if ($completion_percentage < 100): ?>
            <div class="completion-actions">
                <p class="next-steps">
                    <strong>Next steps:</strong>
                    <?php
                    $incomplete_sections = array_filter($sections, function($section) {
                        return !$section['completed'];
                    });
                    
                    if (!empty($incomplete_sections)) {
                        // Get the section with highest weight that's incomplete
                        uasort($incomplete_sections, function($a, $b) {
                            return $b['weight'] - $a['weight'];
                        });
                        $next_section = array_keys($incomplete_sections)[0];
                        
                        switch ($next_section) {
                            case 'personal_info':
                                echo 'Complete your personal information and add a professional bio.';
                                break;
                            case 'skills':
                                echo 'Add at least 3 relevant skills to showcase your expertise.';
                                break;
                            case 'work_preferences':
                                echo 'Set your work style and job type preferences.';
                                break;
                            case 'accessibility_needs':
                                echo 'Specify your workplace accommodation needs.';
                                break;
                            case 'education':
                                echo 'Add your educational background.';
                                break;
                            case 'experience':
                                echo 'Include your work experience.';
                                break;
                            case 'resume':
                                echo 'Upload your resume to complete your profile.';
                                break;
                        }
                    }
                    ?>
                </p>
            </div>
        <?php else: ?>
            <div class="completion-celebration">
                <p class="celebration-message">
                    <i class="fas fa-trophy" style="color: #FD8B51;"></i>
                    <strong>Congratulations!</strong> Your profile is 100% complete!
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>