<?php
// Fetch profile images from database
$profile_images_query = "SELECT profile_photo_path, cover_photo_path FROM profile_details WHERE seeker_id = :seeker_id";
$profile_images_stmt = $conn->prepare($profile_images_query);
$profile_images_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$profile_images_stmt->execute();
$profile_images = $profile_images_stmt->fetch(PDO::FETCH_ASSOC);

// Set default image paths (create these placeholder images if they don't exist)
$profile_photo_src = '../../images/profile-placeholder.png';
$cover_photo_src = '../../images/cover-placeholder.jpg';

// Use uploaded images if they exist and files actually exist
if ($profile_images) {
    if (!empty($profile_images['profile_photo_path']) && file_exists('../../' . $profile_images['profile_photo_path'])) {
        $profile_photo_src = '../../' . $profile_images['profile_photo_path'];
    }
    if (!empty($profile_images['cover_photo_path']) && file_exists('../../' . $profile_images['cover_photo_path'])) {
        $cover_photo_src = '../../' . $profile_images['cover_photo_path'];
    }
}
?>

<div class="profile-header">
    <div class="profile-cover">
        <img id="cover-photo" src="<?php echo htmlspecialchars($cover_photo_src); ?>" alt="Cover Photo">
        <button class="edit-cover-btn" aria-label="Edit cover photo">
            <i class="fas fa-camera"></i>
        </button>
        <input type="file" id="cover-file-input" accept="image/*" style="display: none">
    </div>
    <div class="profile-info">
        <div class="profile-picture">
            <img id="profile-photo" src="<?php echo htmlspecialchars($profile_photo_src); ?>" alt="Profile Picture">
            <button class="edit-picture-btn" aria-label="Edit profile picture">
                <i class="fas fa-camera"></i>
            </button>
            <input type="file" id="profile-file-input" accept="image/*" style="display: none">
        </div>
        <div class="profile-details">
            <h1><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></h1>
            <p class="profile-headline">
                <?php 
                // Fetch work preferences for headline
                $pref_query = "SELECT work_style, job_type FROM user_preferences WHERE seeker_id = :seeker_id";
                $pref_stmt = $conn->prepare($pref_query);
                $pref_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
                $pref_stmt->execute();
                $work_pref = $pref_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Get user's top skill
                $skill_query = "SELECT s.skill_name FROM seeker_skills ss 
                               JOIN skills s ON ss.skill_id = s.skill_id 
                               WHERE ss.seeker_id = :seeker_id LIMIT 1";
                $skill_stmt = $conn->prepare($skill_query);
                $skill_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
                $skill_stmt->execute();
                $skill_result = $skill_stmt->fetch(PDO::FETCH_ASSOC);
                
                $headline_parts = [];
                
                if ($skill_result && !empty($skill_result['skill_name'])) {
                    $headline_parts[] = $skill_result['skill_name'];
                }
                
                if ($work_pref) {
                    $location_text = ucfirst($work_pref['work_style']) . ", " . ucfirst($work_pref['job_type']);
                    $headline_parts[] = $location_text;
                }
                
                echo htmlspecialchars(implode(' | ', $headline_parts));
                ?>
            </p>
            <p class="profile-bio">
                <?php 
                // Fetch bio from profile_details (use this as the primary source)
                $bio_query = "SELECT bio FROM profile_details WHERE seeker_id = :seeker_id";
                $bio_stmt = $conn->prepare($bio_query);
                $bio_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
                $bio_stmt->execute();
                $bio_result = $bio_stmt->fetch(PDO::FETCH_ASSOC);
                
                // If bio exists in profile_details, use it
                if ($bio_result && !empty($bio_result['bio'])) {
                    echo htmlspecialchars($bio_result['bio']);
                } 
                // Fallback to job_seekers bio if available
                else if (!empty($user_data['bio'])) {
                    echo htmlspecialchars($user_data['bio']);
                }
                // Default text if no bio found
                else {
                    echo "I am seeking opportunities where I can contribute my skills in an inclusive environment.";
                }
                ?>
            </p>
        </div>
        <div class="profile-actions">
            <button class="btn primary-btn" id="edit-profile-btn">
                <i class="fas fa-edit"></i> Edit Profile
            </button>
        </div>
    </div>
</div>