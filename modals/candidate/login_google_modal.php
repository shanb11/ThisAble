<div class="modal" id="googleSignInModal">
    <div class="google-modal-content">
        <span class="close-modal" id="closeGoogleModal">&times;</span>
        <div class="google-header">
            <img src="../../images/googlelogo.png" alt="Google" class="google-logo">
            <div class="google-title">Continue with Google</div>
            <div class="google-subtitle">Choose your Google Account</div>
        </div>
        
        <!-- Google Sign-In button will be rendered here -->
        <div id="googleButtonContainer" class="google-button-container"></div>
        
        <div class="google-footer">
            <p>By continuing, you will be redirected to Google to sign in securely.</p>
        </div>
    </div>
</div>

<!-- Add PWD details modal for Google users -->
<div class="modal" id="googlePwdDetailsModal" style="display: none;">
    <div class="google-modal-content">
        <span class="close-modal" id="closeGooglePwdModal">&times;</span>
        <div class="google-header">
            <img src="../../images/thisablelogo.png" alt="ThisAble" class="small-logo">
            <div class="google-title">Complete Your Profile</div>
            <div class="google-subtitle">Please provide your disability information</div>
        </div>
        
        <form id="googlePwdDetailsForm" class="pwd-details-form">
            <input type="tel" name="phone" placeholder="Phone Number" required>
            
            <select name="disability" required>
                <option value="" disabled selected>Select Type of Disability</option>
                <?php
                // Require database connection
                require_once('../../backend/db.php');
                
                try {
                    // Fetch disability types from database
                    $stmt = $conn->prepare("SELECT disability_id, disability_name FROM disability_types ORDER BY disability_name");
                    $stmt->execute();
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $row['disability_id'] . '">' . htmlspecialchars($row['disability_name']) . '</option>';
                    }
                } catch(PDOException $e) {
                    echo '<option value="">Error loading disabilities</option>';
                }
                ?>
            </select>
            
            <input type="text" name="pwdIdNumber" placeholder="PWD ID Number" required>
            
            <div class="file-upload-container">
                <div class="file-input-display" id="googlePwdFileDisplay">PWD ID</div>
                <input type="file" id="googlePwdIdUpload" name="pwdIdFile" class="file-input" accept="image/*,.pdf" required>
                <button type="button" class="file-upload-btn" onclick="document.getElementById('googlePwdIdUpload').click()">
                    Choose File
                </button>
            </div>
            
            <button type="submit" class="google-submit-btn">Complete Registration</button>
        </form>
    </div>
</div>