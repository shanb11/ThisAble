<div class="section-content">
    <div class="accessibility-needs-container">
        <p class="accessibility-description">These accessibility needs will be shared with employers when you apply for jobs to ensure they can provide suitable accommodations.</p>
        
        <div class="accessibility-tags-container">
            <?php
            // Fetch workplace accommodations
            $accom_query = "SELECT * FROM workplace_accommodations WHERE seeker_id = :seeker_id";
            $accom_stmt = $conn->prepare($accom_query);
            $accom_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
            $accom_stmt->execute();
            $accommodations = $accom_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($accommodations) {
                // Parse the JSON list of accommodations
                $accommodation_list = json_decode($accommodations['accommodation_list'], true);
                
                if (is_array($accommodation_list) && count($accommodation_list) > 0) {
                    foreach ($accommodation_list as $accommodation) {
                        // Choose appropriate icon based on accommodation
                        $icon = 'fa-universal-access'; // Default icon
                        
                        if (stripos($accommodation, 'screen reader') !== false) {
                            $icon = 'fa-low-vision';
                        } elseif (stripos($accommodation, 'print') !== false) {
                            $icon = 'fa-text-height';
                        } elseif (stripos($accommodation, 'contrast') !== false) {
                            $icon = 'fa-palette';
                        } elseif (stripos($accommodation, 'restroom') !== false) {
                            $icon = 'fa-toilet';
                        } elseif (stripos($accommodation, 'space') !== false) {
                            $icon = 'fa-volume-mute';
                        } elseif (stripos($accommodation, 'reading') !== false) {
                            $icon = 'fa-book-reader';
                        }
                        
                        echo '<span class="accessibility-tag"><i class="fas ' . $icon . '"></i> ' . htmlspecialchars($accommodation) . '</span>';
                    }
                } elseif ($accommodations['no_accommodations_needed'] == 1) {
                    echo '<span class="accessibility-tag"><i class="fas fa-check-circle"></i> No specific accommodations needed</span>';
                } else {
                    echo '<span class="accessibility-tag"><i class="fas fa-info-circle"></i> No accommodations specified</span>';
                }
            } else {
                echo '<span class="accessibility-tag"><i class="fas fa-info-circle"></i> No accommodations specified</span>';
            }
            ?>
        </div>
        
        <div class="custom-needs">
            <h3>Additional Notes</h3>
            <p>I prefer digital documents over printed materials and appreciate when websites have keyboard navigation support.</p>
        </div>
    </div>
    
    <div class="disclosure-preferences">
        <h3>Disclosure Preferences</h3>
        <div class="disclosure-option">
            <input type="checkbox" id="disclose-application" checked>
            <label for="disclose-application">Disclose my disability in job applications</label>
        </div>
        <div class="disclosure-option">
            <input type="checkbox" id="disclose-interview" checked>
            <label for="disclose-interview">Disclose my accessibility needs for interviews</label>
        </div>
    </div>
</div>