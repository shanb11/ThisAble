<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if this is Google setup flow
$isGoogleSetup = isset($_GET['google_setup']) && $_GET['google_setup'] == '1' && isset($_SESSION['google_employer_data']);
$googleData = $isGoogleSetup ? $_SESSION['google_employer_data'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThisAble - Employer Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../styles/employer/empsignup.css">
</head>
<body>
    <div class="left">
        <img src="../../images/login-image.png" alt="Illustration">
        <h2>Empowering Inclusive Employment</h2>
        <p>Create opportunities that embrace diversity and inclusivity.</p>
    </div>
    <div class="right">
        <img src="../../images/thisablelogo.png" alt="Logo" class="logo">
        
        <?php if (!$isGoogleSetup): ?>
            <!-- Google OAuth Signup -->
            <div class="signup-box">
                <h2>Create Employer Account</h2>
                <p class="subtitle">Join thousands of inclusive employers</p>
                
                <button class="google-signup-btn" id="googleSignupBtn">
                    <i class="fab fa-google"></i>
                    Sign up with Google
                </button>
                
                <div class="divider">
                    <span>Quick and secure registration</span>
                </div>
                
                <div class="benefits">
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Connect with talented PWD candidates</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Build an inclusive workplace</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Access diversity hiring resources</span>
                    </div>
                </div>
                
                <div class="login-link">
                    Already have an account? <a href="emplogin.php">Log in</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Company Setup Form (after Google OAuth) -->
            <div class="signup-box">
                <h2>Complete Your Company Profile</h2>
                <!-- <p class="subtitle">Welcome, <?php echo htmlspecialchars($googleData['first_name']); ?>! Let's set up your company.</p> -->
                
                <form id="companySetupForm">
                    <div class="section-title">
                        <i class="fas fa-building"></i> Company Information
                    </div>
                    <div class="section-content">
                        <div class="input-box">
                            <i class="fas fa-building input-icon"></i>
                            <input type="text" placeholder="Company Name" id="company-name" name="company_name" required>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-industry input-icon"></i>
                            <select id="industry" name="industry_id" required>
                                <option value="" disabled selected>Select Industry</option>
                                <!-- Industries will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" placeholder="Company Address" id="company-address" name="company_address" required>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-users input-icon"></i>
                            <select id="company-size" name="company_size">
                                <option value="" disabled selected>Company Size (Optional)</option>
                                <option value="1-10">1-10 employees</option>
                                <option value="11-50">11-50 employees</option>
                                <option value="51-200">51-200 employees</option>
                                <option value="201-500">201-500 employees</option>
                                <option value="501-1000">501-1000 employees</option>
                                <option value="1000+">1000+ employees</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-globe input-icon"></i>
                            <input type="url" placeholder="Company Website (Optional)" id="company-website" name="company_website">
                        </div>
                        <div class="input-box">
                            <i class="fas fa-align-left input-icon"></i>
                            <textarea placeholder="Company Description (Optional)" id="company-description" name="company_description" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="section-title">
                        <i class="fas fa-user"></i> Your Information
                    </div>
                    <div class="section-content">
                        <div class="row">
                            <div class="input-box">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" placeholder="First Name" id="first-name" value="<?php echo htmlspecialchars($googleData['first_name']); ?>" readonly>
                            </div>
                            <div class="input-box">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" placeholder="Last Name" id="last-name" value="<?php echo htmlspecialchars($googleData['last_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-briefcase input-icon"></i>
                            <input type="text" placeholder="Your Position" id="position" name="position" required>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-phone input-icon"></i>
                            <input type="tel" placeholder="Contact Number" id="contact-number" name="contact_number" required>
                        </div>
                        <div class="input-box">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" placeholder="Email Address" id="email" value="<?php echo htmlspecialchars($googleData['email']); ?>" readonly>
                        </div>
                    </div>
                    
                    <button type="submit" class="signup-btn" id="complete-setup-btn">
                        <i class="fas fa-check"></i>
                        Complete Company Setup
                    </button>
                </form>
                
                <div class="login-link">
                    Need to use a different Google account? <a href="emplogin.php">Start over</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="success-modal-overlay" style="display: none;">
        <div class="success-modal">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Registration Successful!</h3>
            <p>Your employer account has been created. Please login to complete your profile setup.</p>
            <button class="continue-btn" id="continue-to-dashboard">Continue to Login</button>
        </div>
    </div>

    <script src="../../scripts/employer/empsignup.js"></script>
</body>
</html>