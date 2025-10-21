<div class="section-content">
    <div class="education-list">
        <?php
        // Fetch education data from database
        $education_query = "SELECT * FROM education WHERE seeker_id = :seeker_id ORDER BY start_date DESC";
        $education_stmt = $conn->prepare($education_query);
        $education_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $education_stmt->execute();
        $education_records = $education_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($education_records) > 0) {
            foreach ($education_records as $education) {
                $start_date = date('M Y', strtotime($education['start_date']));
                $end_date = $education['is_current'] ? 'Present' : 
                           ($education['end_date'] ? date('M Y', strtotime($education['end_date'])) : 'Present');
                
                echo '<div class="education-item" data-id="' . $education['education_id'] . '">';
                echo '<div class="item-header">';
                echo '<div class="item-main">';
                echo '<h3 class="item-title">' . htmlspecialchars($education['degree']) . '</h3>';
                echo '<h4 class="item-subtitle">' . htmlspecialchars($education['institution']) . '</h4>';
                if ($education['location']) {
                    echo '<p class="item-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($education['location']) . '</p>';
                }
                echo '<p class="item-duration"><i class="fas fa-calendar"></i> ' . $start_date . ' - ' . $end_date . '</p>';
                echo '</div>';
                echo '<div class="item-actions">';
                echo '<button class="btn-icon edit-item-btn" data-type="education" data-id="' . $education['education_id'] . '" title="Edit">';
                echo '<i class="fas fa-edit"></i>';
                echo '</button>';
                echo '<button class="btn-icon delete-item-btn" data-type="education" data-id="' . $education['education_id'] . '" title="Delete">';
                echo '<i class="fas fa-trash-alt"></i>';
                echo '</button>';
                echo '</div>';
                echo '</div>';
                
                if ($education['description']) {
                    echo '<div class="item-description">';
                    echo '<p>' . nl2br(htmlspecialchars($education['description'])) . '</p>';
                    echo '</div>';
                }
                echo '</div>';
            }
        } else {
            // Show empty state
            echo '<div class="empty-state" id="education-empty">';
            echo '<i class="fas fa-graduation-cap"></i>';
            echo '<h3>No education details added yet</h3>';
            echo '<p>Add your education history to showcase your academic background to employers.</p>';
            echo '</div>';
        }
        ?>
    </div>
</div>