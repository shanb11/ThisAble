<div class="right">
    <img src="../../images/thisablelogo.png" alt="Logo" class="logo">
    <div class="signup-box">
        <h2>Create Account</h2>
        
        <div class="progress-bar">
            <div class="progress-step active" id="step1">
                1
                <div class="step-label">Sign Up</div>
            </div>
            <div class="progress-step" id="step2">
                2
                <div class="step-label">Profile Details</div>
            </div>
            <div class="progress-step" id="step3">
                3
                <div class="step-label">PWD Verification</div>
            </div>
        </div>
        
        <!-- Step 1: Initial Sign Up with Google or Direct -->
        <div class="form-step active" id="step1Form">
            <!-- Google Sign In Button -->
            <button class="google-btn" id="googleSignInBtn">
                <i class="fab fa-google google-icon"></i>
                Sign up with Google
            </button>
            
            <!-- Divider between Google and form -->
            <div class="divider">
                <span>OR</span>
            </div>
            
            <button type="button" class="next-btn" id="continueWithEmailBtn">Continue with Email</button>
            <div class="login-link">Already have an account? <a href="../../frontend/candidate/login.php">Log in</a></div>
        </div>
        
        <!-- Step 2: Complete Profile (Updated with Contact Number) -->
        <div class="form-step" id="step2Form">
            <h3>Complete Your Profile</h3>
            <p>Please provide the following information to continue.</p>
            
            <form id="profileForm">
                <div class="input-row">
                    <div class="input-box">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="firstName" placeholder="First Name" required>
                    </div>
                    <div class="input-box">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="middleName" placeholder="Middle Name">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-box">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="lastName" placeholder="Last Name" required>
                    </div>
                    <div class="input-box">
                        <i class="fas fa-user-tag input-icon"></i>
                        <input type="text" name="suffix" placeholder="Suffix (Jr., Sr., etc.)">
                    </div>
                </div>
                <div class="input-box">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                <div class="input-box">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Phone Number (e.g., 09171234567)" required>
                    <div class="error-message" id="phoneError">Please enter a valid Philippine phone number</div>
                </div>
                <div class="input-box">
                    <i class="fas fa-wheelchair input-icon"></i>
                    <select name="disabilityType" required>
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
                <div class="input-box">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="input-box">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <div class="error-message" id="passwordError">Passwords do not match</div>
                </div>
                
                <div class="form-buttons">
                    <button type="button" class="back-btn" id="profileBackBtn">Back</button>
                    <button type="button" class="next-btn" id="profileNextBtn">Continue</button>
                </div>
            </form>
        </div>
        
        <!-- Step 3: PWD Verification -->
        <div class="form-step" id="step3Form">
            <h3>PWD Verification</h3>
            <p>Final step: Please provide your PWD ID details for verification through the DOH database.</p>
            
            <div class="input-box">
                <i class="fas fa-id-card input-icon"></i>
                <input type="text" id="pwdIdNumber" placeholder="PWD ID Number" required>
            </div>
            
            <div class="input-box">
                <i class="fas fa-calendar-alt input-icon"></i>
                <input type="date" id="pwdIdIssuedDate" placeholder="Date Issued" required>
                <div class="info-text">Please enter the date your PWD ID was issued</div>
            </div>
            
            <div class="input-box">
                <i class="fas fa-map-marker-alt input-icon"></i>
                <select id="pwdIdIssuingLGU" required>
                    <option value="" disabled selected>Select Issuing LGU/Municipality in Cavite</option>
                    <?php
                    // Include database connection
                    require_once('../../backend/db.php');
                    
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
            
            <!-- Verification buttons -->
            <button type="button" class="verify-btn" id="verifyPwdIdBtn">Verify PWD ID</button>
            
            <div id="verificationStatus" class="verification-status">
                <!-- Verification status will be displayed here -->
            </div>
            
            <!-- Conditional upload section - initially hidden -->
            <div id="uploadSection" style="display: none;">
                <div class="divider">
                    <span>Manual Verification</span>
                </div>
                
                <p class="upload-info">If automatic verification is unavailable, please upload your PWD ID image for manual verification:</p>
                
                <div class="input-box">
                    <i class="fas fa-id-card input-icon"></i>
                    <div class="file-upload-container">
                        <div class="file-input-display" id="fileDisplayText">PWD ID Image</div>
                        <input type="file" id="pwdIdUpload" class="file-input" accept="image/*,.pdf">
                        <button type="button" class="file-upload-btn" onclick="document.getElementById('pwdIdUpload').click()">
                            Choose File
                        </button>
                    </div>
                    <div class="info-text">Upload a clear image of your PWD ID (front side)</div>
                </div>
            </div>
            
            <div class="form-buttons">
                <button type="button" class="back-btn" id="verificationBackBtn">Back</button>
                <button type="button" class="signup-btn" id="completeSignupBtn" disabled>Complete Sign Up</button>
            </div>
        </div>
    </div>
</div>