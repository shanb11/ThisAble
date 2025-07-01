<div class="edit-form" id="skills-edit-form">
    <form id="skills-form">
        <?php
        // Fetch all skill categories
        $cat_query = "SELECT * FROM skill_categories ORDER BY category_name";
        $cat_stmt = $conn->query($cat_query);
        
        // Fetch user's current skills
        $user_skills_query = "SELECT skill_id FROM seeker_skills WHERE seeker_id = :seeker_id";
        $user_skills_stmt = $conn->prepare($user_skills_query);
        $user_skills_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $user_skills_stmt->execute();
        
        $user_skills = [];
        while ($skill = $user_skills_stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_skills[] = $skill['skill_id'];
        }
        
        // Display skills by category
        while ($category = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="form-group">';
            echo '<label>' . htmlspecialchars($category['category_name']) . '</label>';
            echo '<div class="skills-checkbox-container">';
            
            // Fetch skills for this category
            $skills_query = "SELECT * FROM skills WHERE category_id = :category_id ORDER BY skill_name";
            $skills_stmt = $conn->prepare($skills_query);
            $skills_stmt->bindParam(':category_id', $category['category_id'], PDO::PARAM_INT);
            $skills_stmt->execute();
            
            while ($skill = $skills_stmt->fetch(PDO::FETCH_ASSOC)) {
                $checked = in_array($skill['skill_id'], $user_skills) ? 'checked' : '';
                
                echo '<div class="checkbox-item">';
                echo '<input type="checkbox" id="skill-' . $skill['skill_id'] . '" name="skills[]" value="' . $skill['skill_id'] . '" ' . $checked . '>';
                echo '<label for="skill-' . $skill['skill_id'] . '">' . htmlspecialchars($skill['skill_name']) . '</label>';
                echo '</div>';
            }
            
            echo '</div>'; // Close skills-checkbox-container
            echo '</div>'; // Close form-group
        }
        ?>
        
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="skills">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="skills">Save Changes</button>
        </div>
    </form>
</div>