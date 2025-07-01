<div class="edit-form" id="personal-edit-form">
    <form id="personal-info-form" method="post">
        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user_data['middle_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="suffix">Suffix</label>
                <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($user_data['suffix'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user_data['contact_number']); ?>" required>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user_data['province'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="disability_id">Type of Disability</label>
                <select id="disability_id" name="disability_id">
                    <?php
                    // Fetch all disability types
                    $disability_query = "SELECT * FROM disability_types ORDER BY disability_name";
                    $disability_stmt = $conn->query($disability_query);
                    
                    while ($disability = $disability_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($disability['disability_id'] == $user_data['disability_id']) ? 'selected' : '';
                        echo '<option value="' . $disability['disability_id'] . '" ' . $selected . '>' . 
                             htmlspecialchars($disability['disability_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group full-width">
                <label for="bio">Professional Bio</label>
                <?php
                // Fetch bio from profile_details
                $bio_query = "SELECT bio FROM profile_details WHERE seeker_id = :seeker_id";
                $bio_stmt = $conn->prepare($bio_query);
                $bio_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
                $bio_stmt->execute();
                $bio_result = $bio_stmt->fetch(PDO::FETCH_ASSOC);
                
                // Use bio from profile_details if available, otherwise use from job_seekers
                $bio = "";
                if ($bio_result && !empty($bio_result['bio'])) {
                    $bio = $bio_result['bio'];
                } else if (!empty($user_data['bio'])) {
                    $bio = $user_data['bio'];
                }
                ?>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell employers about yourself, your skills, and career goals..."><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="personal">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="personal">Save Changes</button>
        </div>
    </form>
</div>