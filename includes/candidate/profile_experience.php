<div class="section-content">
    <div class="experience-list">
        <?php
        // Fetch experience data from database
        $experience_query = "SELECT * FROM experience WHERE seeker_id = :seeker_id ORDER BY start_date DESC";
        $experience_stmt = $conn->prepare($experience_query);
        $experience_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $experience_stmt->execute();
        $experience_records = $experience_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($experience_records) > 0) {
            foreach ($experience_records as $experience) {
                $start_date = date('M Y', strtotime($experience['start_date']));
                $end_date = $experience['is_current'] ? 'Present' : 
                           ($experience['end_date'] ? date('M Y', strtotime($experience['end_date'])) : 'Present');
                
                echo '<div class="experience-item" data-id="' . $experience['experience_id'] . '">';
                echo '<div class="item-header">';
                echo '<div class="item-main">';
                echo '<h3 class="item-title">' . htmlspecialchars($experience['job_title']) . '</h3>';
                echo '<h4 class="item-subtitle">' . htmlspecialchars($experience['company']) . '</h4>';
                if ($experience['location']) {
                    echo '<p class="item-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($experience['location']) . '</p>';
                }
                echo '<p class="item-duration"><i class="fas fa-calendar"></i> ' . $start_date . ' - ' . $end_date . '</p>';
                echo '</div>';
                echo '<div class="item-actions">';
                echo '<button class="btn-icon edit-item-btn" data-type="experience" data-id="' . $experience['experience_id'] . '" title="Edit">';
                echo '<i class="fas fa-edit"></i>';
                echo '</button>';
                echo '<button class="btn-icon delete-item-btn" data-type="experience" data-id="' . $experience['experience_id'] . '" title="Delete">';
                echo '<i class="fas fa-trash-alt"></i>';
                echo '</button>';
                echo '</div>';
                echo '</div>';
                
                if ($experience['description']) {
                    echo '<div class="item-description">';
                    echo '<p>' . nl2br(htmlspecialchars($experience['description'])) . '</p>';
                    echo '</div>';
                }
                echo '</div>';
            }
        } else {
            // Show empty state
            echo '<div class="empty-state" id="experience-empty">';
            echo '<i class="fas fa-briefcase"></i>';
            echo '<h3>No work experience added yet</h3>';
            echo '<p>Add your work experience to showcase your skills and expertise to potential employers.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>