<div class="section-content">
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Full Name</span>
            <span class="info-value">
                <?php 
                echo htmlspecialchars($user_data['first_name'] . ' ' . 
                    ($user_data['middle_name'] ? $user_data['middle_name'] . ' ' : '') . 
                    $user_data['last_name'] . 
                    ($user_data['suffix'] ? ' ' . $user_data['suffix'] : ''));
                ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Email</span>
            <span class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Phone</span>
            <span class="info-value"><?php echo htmlspecialchars($user_data['contact_number']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">Location</span>
            <span class="info-value">
                <?php 
                $location_parts = [];
                
                // Add city if available
                if (!empty($user_data['city'])) {
                    $location_parts[] = htmlspecialchars($user_data['city']);
                }
                
                // Add province if available
                if (!empty($user_data['province'])) {
                    $location_parts[] = htmlspecialchars($user_data['province']);
                }
                
                // If we have city and/or province, display them
                if (!empty($location_parts)) {
                    echo implode(', ', $location_parts);
                    // Add Philippines if not already included
                    if (!in_array('Philippines', $location_parts)) {
                        echo ', Philippines';
                    }
                } else {
                    // Default fallback if no location data
                    echo 'Cavite, Philippines';
                }
                ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">PWD ID</span>
            <span class="info-value">
                <?php 
                // Fetch PWD ID
                $pwd_query = "SELECT pwd_id_number, verification_status FROM pwd_ids WHERE seeker_id = :seeker_id";
                $pwd_stmt = $conn->prepare($pwd_query);
                $pwd_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
                $pwd_stmt->execute();
                $pwd_data = $pwd_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($pwd_data) {
                    echo htmlspecialchars($pwd_data['pwd_id_number']);
                    
                    // Display verification status badge
                    $status_class = 'status-pending';
                    $status_text = 'Pending';
                    
                    if ($pwd_data['verification_status'] == 'verified') {
                        $status_class = 'status-verified';
                        $status_text = 'Verified';
                    } elseif ($pwd_data['verification_status'] == 'rejected') {
                        $status_class = 'status-rejected';
                        $status_text = 'Rejected';
                    }
                    
                    echo " <span class='verification-badge $status_class'>$status_text</span>";
                } else {
                    echo "Not provided";
                }
                ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">Type of Disability</span>
            <span class="info-value"><?php echo htmlspecialchars($user_data['disability_name']); ?></span>
        </div>
    </div>
</div>