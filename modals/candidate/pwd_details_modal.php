<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure we have Google data
if (!isset($_SESSION['google_data'])) {
    return;
}

// Get Google user data
$userData = $_SESSION['google_data'];
?>

<div class="modal" id="pwdDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Complete Your Registration</h2>
            <span class="close-modal" id="closePwdDetailsModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <p>Welcome <?php echo htmlspecialchars($userData['first_name']); ?>! We need some additional information to complete your account setup.</p>
            
            <form id="googleProfileForm">
                <!-- Pre-filled user data (not editable) -->
                <div class="input-box disabled">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly>
                    <div class="info-text">Email address from your Google account</div>
                </div>
                
                <div class="input-row">
                    <div class="input-box disabled">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" value="<?php echo htmlspecialchars($userData['first_name']); ?>" readonly>
                    </div>
                    <div class="input-box disabled">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" value="<?php echo htmlspecialchars($userData['last_name']); ?>" readonly>
                    </div>
                </div>
                
                <!-- Required additional information -->
                <div class="input-box">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="tel" id="phoneNumber" name="phone" placeholder="Phone Number (e.g., 09171234567)" required>
                    <div class="error-message" id="phoneError">Please enter a valid Philippine phone number</div>
                </div>
                
                <div class="input-box">
                    <i class="fas fa-wheelchair input-icon"></i>
                    <select name="disability" required>
                        <option value="" disabled selected>Select Type of Disability</option>
                        <?php
                        // Include database connection
                        require_once('../../backend/db.php');
                        
                        // Query to get all disability types from database
                        $stmt = $conn->prepare("SELECT disability_id, disability_name FROM disability_types ORDER BY disability_id");
                        $stmt->execute();
                        
                        // Loop through results and create an option for each disability type
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"" . $row['disability_id'] . "\">" . $row['disability_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <!-- PWD ID Information -->
                <div class="input-box">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="pwdIdNumber" name="pwdIdNumber" placeholder="PWD ID Number" required>
                </div>
                
                <div class="input-box">
                    <i class="fas fa-calendar-alt input-icon"></i>
                    <input type="date" id="pwdIdIssuedDate" name="pwdIdIssuedDate" placeholder="Date Issued" required>
                    <div class="info-text">Please enter the date your PWD ID was issued</div>
                </div>
                
                <div class="input-box">
                    <i class="fas fa-map-marker-alt input-icon"></i>
                    <select id="pwdIdIssuingLGU" name="pwdIdIssuingLGU" required>
                        <option value="" disabled selected>Select Issuing LGU/Municipality in Cavite</option>
                        <?php
                        // Array of municipalities and cities in Cavite province
                        $lgus = [
                            // Cities
                            "Bacoor City", 
                            "Cavite City",
                            "Dasmariñas City", 
                            "General Trias City", 
                            "Imus City",
                            "Tagaytay City",
                            "Trece Martires City",
                            
                            // Municipalities
                            "Alfonso",
                            "Amadeo",
                            "Carmona",
                            "General Mariano Alvarez (GMA)",
                            "Indang",
                            "Kawit",
                            "Magallanes",
                            "Maragondon",
                            "Mendez",
                            "Naic",
                            "Noveleta",
                            "Rosario",
                            "Silang",
                            "Tanza",
                            "Ternate"
                        ];
                        
                        // Create options for each LGU
                        foreach ($lgus as $lgu) {
                            echo "<option value=\"" . htmlspecialchars($lgu) . "\">" . htmlspecialchars($lgu) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <!-- Verification button -->
                <button type="button" class="verify-btn" id="verifyPwdIdBtn">Verify PWD ID</button>
                
                <div id="verificationStatus" class="verification-status">
                    <!-- Verification status will be displayed here -->
                </div>
                
                <!-- PWD ID File upload section - initially hidden -->
                <div id="uploadSection" style="display: none;">
                    <div class="divider">
                        <span>Manual Verification</span>
                    </div>
                    
                    <p class="upload-info">If automatic verification is unavailable, please upload your PWD ID image for manual verification:</p>
                    
                    <div class="input-box">
                        <i class="fas fa-id-card input-icon"></i>
                        <div class="file-upload-container">
                            <div class="file-input-display" id="fileDisplayText">PWD ID Image</div>
                            <input type="file" id="pwdIdUpload" name="pwdIdFile" class="file-input" accept="image/*,.pdf">
                            <button type="button" class="file-upload-btn" onclick="document.getElementById('pwdIdUpload').click()">
                                Choose File
                            </button>
                        </div>
                        <div class="info-text">Upload a clear image of your PWD ID (front side)</div>
                    </div>
                </div>
                
                <div id="formErrors" class="error-message"></div>
                
                <button type="submit" class="submit-btn" id="completeProfileBtn" disabled>Complete Registration</button>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    max-width: 90%;
    width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.close-modal {
    font-size: 24px;
    cursor: pointer;
    color: #888;
}

.close-modal:hover {
    color: #000;
}

.input-box.disabled input {
    background-color: #f5f5f5;
    color: #888;
}

.file-upload-container {
    display: flex;
    align-items: center;
    width: 100%;
}

.file-input {
    display: none;
}

.file-input-display {
    flex: 1;
    padding: 8px 10px;
    margin-right: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.file-upload-btn {
    padding: 8px 15px;
    background-color: #CB6040;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background-color: #CB6040;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}

.submit-btn:hover {
    background-color: #b54e30;
}

.verify-btn {
    background-color: #257180;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    width: 100%;
    margin-top: 15px;
    transition: background-color 0.3s;
}

.verify-btn:hover {
    background-color: #1a5761;
}

.error-message {
    color: #f44336;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

/* Add styles to match your existing design */
.input-box {
    position: relative;
    margin-bottom: 15px;
}

.input-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #CB6040;
}

.input-box input,
.input-box select {
    width: 100%;
    padding: 12px 12px 12px 35px;
    border: 1px solid #CB6040;
    border-radius: 5px;
    font-size: 14px;
}

.input-row {
    display: flex;
    gap: 15px;
}

.input-row .input-box {
    flex: 1;
}

.info-text {
    font-size: 12px;
    color: #666;
    margin-top: 4px;
}

/* PWD Verification Styles */
.verification-status {
    margin: 15px 0;
    padding: 10px;
    border-radius: 5px;
    display: none;
}

.verification-status.loading {
    display: block;
    background-color: #f5f5f5;
    color: #333;
    text-align: center;
}

.verification-status.success {
    display: block;
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.verification-status.error {
    display: block;
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.verification-status.warning {
    display: block;
    background-color: #fff8e1;
    color: #f57f17;
    border: 1px solid #ffe082;
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 15px 0;
    color: #767676;
}

.divider::before, .divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #ddd;
}

.divider span {
    padding: 0 10px;
    font-size: 14px;
}

.upload-info {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
    text-align: center;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
    margin-right: 10px;
    vertical-align: middle;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking on the X
    const closeBtn = document.getElementById('closePwdDetailsModal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            document.getElementById('pwdDetailsModal').style.display = 'none';
        });
    }
});
</script>