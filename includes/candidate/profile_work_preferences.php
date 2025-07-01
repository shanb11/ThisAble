<div class="section-content">
    <div class="preferences-grid">
        <?php
        // Fetch user preferences
        $pref_query = "SELECT * FROM user_preferences WHERE seeker_id = :seeker_id";
        $pref_stmt = $conn->prepare($pref_query);
        $pref_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $pref_stmt->execute();
        $preferences = $pref_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Define preference items with their icons and default values
        $preference_items = [
            [
                'icon' => 'fa-laptop-house',
                'title' => 'Work Style',
                'value' => $preferences ? ucfirst($preferences['work_style']) : 'Not specified',
            ],
            [
                'icon' => 'fa-business-time',
                'title' => 'Job Type',
                'value' => $preferences ? ucfirst($preferences['job_type']) : 'Not specified',
            ],
            [
                'icon' => 'fa-money-bill-wave',
                'title' => 'Expected Salary',
                'value' => ($preferences && !empty($preferences['salary_range'])) ? htmlspecialchars($preferences['salary_range']) : 'Not specified',
            ],
            [
                'icon' => 'fa-calendar-alt',
                'title' => 'Availability',
                'value' => ($preferences && !empty($preferences['availability'])) ? htmlspecialchars($preferences['availability']) : 'Not specified',
            ]
        ];
        
        // Display each preference item
        foreach ($preference_items as $item) {
            echo '<div class="preference-item">';
            echo '<div class="preference-icon"><i class="fas ' . $item['icon'] . '"></i></div>';
            echo '<div class="preference-details">';
            echo '<h3>' . $item['title'] . '</h3>';
            echo '<span class="preference-value">' . $item['value'] . '</span>';
            echo '</div></div>';
        }
        ?>
    </div>
</div>