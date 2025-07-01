<?php
/**
 * Dynamic Dashboard Welcome Section - Debug Version
 * This will help us see what's happening on refresh
 */

// DEBUG: Log the current state
error_log("=== DASHBOARD WELCOME DEBUG START ===");
error_log("Current file: " . __FILE__);
error_log("Current directory: " . __DIR__);
error_log("Database connection exists: " . (isset($conn) ? 'YES' : 'NO'));
error_log("Session seeker_id: " . (isset($_SESSION['seeker_id']) ? $_SESSION['seeker_id'] : 'NOT SET'));

// Ensure database connection is available
if (!isset($conn)) {
    error_log("No database connection, attempting to include db.php");
    require_once __DIR__ . '/../../backend/db.php';
    error_log("Database connection after include: " . (isset($conn) ? 'YES' : 'NO'));
}

// Try different paths for the profile completion handler
$handler_paths = [
    __DIR__ . '/profile_completion_handler.php',
    __DIR__ . '/../candidate/profile_completion_handler.php',
    '../../includes/candidate/profile_completion_handler.php'
];

$handler_loaded = false;
foreach ($handler_paths as $path) {
    error_log("Trying handler path: " . $path);
    if (file_exists($path)) {
        error_log("Handler found at: " . $path);
        require_once $path;
        $handler_loaded = true;
        break;
    } else {
        error_log("Handler NOT found at: " . $path);
    }
}

if (!$handler_loaded) {
    error_log("ERROR: Could not load profile completion handler!");
    // Define fallback functions
    if (!function_exists('calculateProfileCompletion')) {
        function calculateProfileCompletion($conn, $seeker_id) {
            error_log("Using fallback calculateProfileCompletion function");
            return ['percentage' => 0, 'sections' => []];
        }
    }
    if (!function_exists('getCompletionMessage')) {
        function getCompletionMessage($percentage) {
            return 'Let\'s get your profile set up to attract employers.';
        }
    }
} else {
    error_log("Profile completion handler loaded successfully");
}

// Initialize default values
$user_name = 'there';
$completion_percentage = 0;
$completion_sections = [];
$completion_message = 'Let\'s get your profile set up to attract employers.';

try {
    // Check if we have the required data
    if (!isset($conn)) {
        throw new Exception("Database connection not available");
    }
    
    if (!isset($_SESSION['seeker_id'])) {
        throw new Exception("Session seeker_id not available");
    }
    
    $seeker_id = $_SESSION['seeker_id'];
    error_log("Processing for seeker_id: " . $seeker_id);
    
    // Test database connection
    $test_query = $conn->query("SELECT 1");
    error_log("Database connection test: SUCCESS");
    
    // Fetch user's first name
    $user_query = "SELECT first_name FROM job_seekers WHERE seeker_id = :seeker_id";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
    $user_stmt->execute();
    $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    $user_name = $user_data['first_name'] ?? $_SESSION['user_name'] ?? 'there';
    error_log("User name: " . $user_name);
    
    // Calculate profile completion
    error_log("Calling calculateProfileCompletion...");
    $completion_info = calculateProfileCompletion($conn, $seeker_id);
    $completion_percentage = $completion_info['percentage'];
    $completion_sections = $completion_info['sections'];
    
    error_log("Profile completion calculated: " . $completion_percentage . "%");
    
    // Get completion message
    $completion_message = getCompletionMessage($completion_percentage);
    error_log("Completion message: " . $completion_message);
    
} catch (Exception $e) {
    error_log("DASHBOARD WELCOME ERROR: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // Keep default values
}

error_log("Final values - Percentage: $completion_percentage, User: $user_name");
error_log("=== DASHBOARD WELCOME DEBUG END ===");

// Get incomplete sections for quick actions
$incomplete_sections = array_filter($completion_sections, function($section) {
    return !$section['completed'];
});

// Sort by weight (highest priority first)
if (!empty($incomplete_sections)) {
    uasort($incomplete_sections, function($a, $b) {
        return $b['weight'] - $a['weight'];
    });
}

// Get top 2 incomplete sections for action buttons
$top_incomplete = array_slice($incomplete_sections, 0, 2, true);
?>

<div class="welcome-section">
    <div class="welcome-content">
        <div class="welcome-greeting">
            <h1>Hi <span id="welcomeUserName"><?php echo htmlspecialchars($user_name); ?></span> 👋</h1>
            <p class="welcome-subtitle"><?php echo $completion_message; ?></p>
        </div>
        
        <div class="profile-completion-dashboard">
            <div class="completion-header">
                <div class="completion-info">
                    <span class="completion-label">Profile Completion</span>
                    <span class="completion-percentage"><?php echo $completion_percentage; ?>%</span>                    
                </div>
                <button class="view-profile-btn" onclick="window.location.href='profile.php'">
                    <i class="fas fa-user"></i>
                    View Profile
                </button>
            </div>
            
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo $completion_percentage; ?>%;"></div>
            </div>
            
            <?php if ($completion_percentage < 100): ?>
            <div class="completion-tips">
                <p class="tip-text">
                    <i class="fas fa-lightbulb"></i>
                    Complete your profile to increase visibility by up to 40%
                </p>
            </div>
            <?php else: ?>
            <div class="completion-celebration">
                <p class="celebration-text">
                    <i class="fas fa-trophy"></i>
                    Perfect! Your profile is 100% complete
                </p>
            </div>
            <?php endif; ?>
        </div>
         
        <div class="profile-actions">
            <?php 
            $action_count = 0;
            foreach ($top_incomplete as $key => $section): 
                if ($action_count >= 2) break;
                $action_count++;
                
                // Determine button icon and action based on section
                $icon = 'fas fa-plus';
                $action_text = $section['label'];
                $onclick = "window.location.href='profile.php'";
                
                switch ($key) {
                    case 'skills':
                        $icon = 'fas fa-plus';
                        $action_text = 'Add Skills';
                        break;
                    case 'resume':
                        $icon = 'fas fa-upload';
                        $action_text = 'Upload Resume';
                        break;
                    case 'personal_info':
                        $icon = 'fas fa-user-edit';
                        $action_text = 'Complete Profile';
                        break;
                    case 'work_preferences':
                        $icon = 'fas fa-briefcase';
                        $action_text = 'Set Preferences';
                        break;
                    case 'education':
                        $icon = 'fas fa-graduation-cap';
                        $action_text = 'Add Education';
                        break;
                    case 'experience':
                        $icon = 'fas fa-briefcase';
                        $action_text = 'Add Experience';
                        break;
                    case 'accessibility_needs':
                        $icon = 'fas fa-universal-access';
                        $action_text = 'Set Needs';
                        break;
                }
            ?>
            
            <button class="profile-action-btn" onclick="<?php echo $onclick; ?>" title="Complete <?php echo $section['label']; ?>">
                <i class="<?php echo $icon; ?>"></i>
                <?php echo $action_text; ?>
            </button>
            
            <?php endforeach; ?>
            
            <?php if ($action_count < 2): ?>
            <!-- Fill remaining slots with default actions -->
            <?php if ($action_count == 0): ?>
            <button class="profile-action-btn completed" onclick="window.location.href='profile.php'">
                <i class="fas fa-check-circle"></i>
                View Profile
            </button>
            <button class="profile-action-btn" onclick="window.location.href='joblistings.php'">
                <i class="fas fa-search"></i>
                Find Jobs
            </button>
            <?php elseif ($action_count == 1): ?>
            <button class="profile-action-btn" onclick="window.location.href='profile.php'">
                <i class="fas fa-edit"></i>
                Edit Profile
            </button>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Quick Stats -->
        <?php if ($completion_percentage >= 50): ?>
        <div class="quick-stats">
            <div class="stat-item">
                <i class="fas fa-eye"></i>
                <span>Profile Views</span>
                <span class="stat-value">--</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-paper-plane"></i>
                <span>Applications</span>
                <span class="stat-value">--</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-heart"></i>
                <span>Saved Jobs</span>
                <span class="stat-value">--</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log values
    console.log('=== DASHBOARD WELCOME CLIENT DEBUG ===');
    console.log('Completion Percentage:', <?php echo $completion_percentage; ?>);
    console.log('User Name:', '<?php echo htmlspecialchars($user_name); ?>');
    console.log('=====================================');
    
    // REMOVED: Progress bar animation - let it stay static
    // The progress bar will show immediately with the correct width
    const progressBar = document.querySelector('.welcome-section .progress');
    if (progressBar) {
        console.log('Progress bar found, current width:', progressBar.style.width);
        // Don't animate - let it show immediately with the PHP-set width
        // progressBar.style.width = '0%'; // REMOVED THIS LINE
        // No setTimeout animation needed
    }
    
    // Store completion percentage for other scripts
    localStorage.setItem('profileCompletion', '<?php echo $completion_percentage; ?>');
    
    // Update last completion check
    localStorage.setItem('lastCompletionCheck', Date.now().toString());
});
</script>