<div class="section-content">
    <div class="skills-container">
        <?php
        // Get all skill categories first
        $category_query = "SELECT DISTINCT sc.category_name, sc.category_icon 
                           FROM skill_categories sc 
                           JOIN skills s ON sc.category_id = s.category_id 
                           JOIN seeker_skills ss ON s.skill_id = ss.skill_id 
                           WHERE ss.seeker_id = :seeker_id";
        $category_stmt = $conn->prepare($category_query);
        $category_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $category_stmt->execute();
        
        while ($category = $category_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="skill-category">';
            echo '<h3>' . htmlspecialchars($category['category_name']) . '</h3>';
            echo '<div class="skill-tags">';
            
            // Get skills for this category
            $skills_query = "SELECT s.skill_name 
                            FROM skills s 
                            JOIN seeker_skills ss ON s.skill_id = ss.skill_id 
                            JOIN skill_categories sc ON s.category_id = sc.category_id 
                            WHERE ss.seeker_id = :seeker_id AND sc.category_name = :category_name";
            $skills_stmt = $conn->prepare($skills_query);
            $skills_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
            $skills_stmt->bindParam(':category_name', $category['category_name'], PDO::PARAM_STR);
            $skills_stmt->execute();
            
            while ($skill = $skills_stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<span class="skill-tag">' . htmlspecialchars($skill['skill_name']) . '</span>';
            }
            
            echo '</div>'; // Close skill-tags
            echo '</div>'; // Close skill-category
        }
        
        // If no skills found, show a message
        if ($category_stmt->rowCount() == 0) {
            echo '<div class="no-skills-message">';
            echo '<p>No skills added yet. Click the Edit button to add your skills.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>