// Toggle password visibility
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = passwordInput.nextElementSibling.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Phone number validation for Philippine format
function validatePhoneNumber() {
    const phoneInput = document.getElementById('phoneNumber');
    const phoneError = document.getElementById('phoneError');
    
    const phoneNumber = phoneInput.value.trim();
    
    // Philippine phone number format: +63XXXXXXXXXX or 09XXXXXXXXXX
    const philippinePhoneRegex = /^(\+63|0)9\d{9}$/;
    
    if (philippinePhoneRegex.test(phoneNumber)) {
        // Valid phone number
        phoneError.style.display = 'none';
        phoneInput.classList.remove('error-input');
        return true;
    } else {
        // Invalid phone number
        phoneError.style.display = 'block';
        phoneInput.classList.add('error-input');
        return false;
    }
}

// Add listener for phone input
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.addEventListener('input', validatePhoneNumber);
        
        // Set initial state - hide error until user types
        document.getElementById('phoneError').style.display = 'none';
    }
});

function showStep(stepNumber) {
    console.log('Showing step:', stepNumber);
    
    // Get all form steps and progress indicators
    const steps = document.querySelectorAll('.form-step');
    const progressSteps = document.querySelectorAll('.progress-step');
    
    if (steps.length === 0 || progressSteps.length === 0) {
        console.error('Form steps or progress steps not found');
        return;
    }
    
    // Remove active class from all steps
    steps.forEach((step, index) => {
        step.classList.remove('active');
        progressSteps[index].classList.remove('active');
        progressSteps[index].classList.remove('completed');
    });
    
    // Make sure stepNumber is valid
    if (stepNumber < 1 || stepNumber > steps.length) {
        console.error('Invalid step number:', stepNumber);
        return;
    }
    
    // Mark current step as active
    steps[stepNumber - 1].classList.add('active');
    progressSteps[stepNumber - 1].classList.add('active');
    
    // Mark previous steps as completed
    for (let i = 0; i < stepNumber - 1; i++) {
        progressSteps[i].classList.add('completed');
    }
    
    console.log('Step transition complete');
}

// Step 1 to Step 2 (Continue with Email button)
document.addEventListener('DOMContentLoaded', function() {
    const continueWithEmailBtn = document.getElementById('continueWithEmailBtn');
    
    if (continueWithEmailBtn) {
        continueWithEmailBtn.addEventListener('click', function() {
            showStep(2);
        });
    } else {
        console.error('Continue with Email button not found in the DOM');
    }
});

// Step 2 to Step 3 (Profile to PWD Verification)
document.addEventListener('DOMContentLoaded', function() {
    const profileNextBtn = document.getElementById('profileNextBtn');
    
    if (profileNextBtn) {
        profileNextBtn.addEventListener('click', function() {
            // Check password match
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorMessage = document.getElementById('passwordError');

            if (password !== confirmPassword) {
                errorMessage.style.display = 'block';
                document.getElementById('confirmPassword').style.borderColor = '#ff0000';
                return false;
            } else {
                errorMessage.style.display = 'none';
                document.getElementById('confirmPassword').style.borderColor = '#CB6040';
                
                // Check phone number
                if (!validatePhoneNumber()) {
                    return false;
                }
                
                // Check if all required fields are filled
                const profileForm = document.getElementById('profileForm');
                if (profileForm.checkValidity()) {
                    showStep(3);
                    // Here you would typically save the profile info
                    console.log('Profile information saved');
                } else {
                    alert('Please fill in all required fields');
                }
            }
        });
    } else {
        console.error('Profile Next button not found in the DOM');
    }
});

// PWD ID file upload display
document.addEventListener('DOMContentLoaded', function() {
    const pwdIdUpload = document.getElementById('pwdIdUpload');
    
    if (pwdIdUpload) {
        pwdIdUpload.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'PWD ID';
            document.getElementById('fileDisplayText').textContent = fileName;
        });
    }
});

// Google Sign In Modal
document.addEventListener('DOMContentLoaded', function() {
    const googleSignInBtn = document.getElementById("googleSignInBtn");
    
    if (googleSignInBtn) {
        googleSignInBtn.addEventListener("click", function() {
            window.location.href = '../../backend/candidate/google_auth.php';
        });
    }
});

// Add CSS for error messages
document.addEventListener('DOMContentLoaded', function() {
    // Check if the style exists already
    let styleElement = document.getElementById('validation-styles');
    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = 'validation-styles';
        styleElement.textContent = `
            .error-message {
                color: #f44336;
                font-size: 12px;
                margin-top: 5px;
                display: none;
            }

            .error-input {
                border: 1px solid #f44336 !important;
            }
            
            /* Disable button styling */
            button:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(styleElement);
    }
    
    // Add the missing phone error element if not present
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        let phoneError = document.getElementById('phoneError');
        if (!phoneError) {
            phoneError = document.createElement('div');
            phoneError.id = 'phoneError';
            phoneError.className = 'error-message';
            phoneError.textContent = 'Please enter a valid Philippine phone number (e.g., 09123456789)';
            phoneInput.parentNode.appendChild(phoneError);
        }
    }
});

// PWD Verification through DOH Database
document.addEventListener('DOMContentLoaded', function() {
    const verifyPwdIdBtn = document.getElementById('verifyPwdIdBtn');
    const completeSignupBtn = document.getElementById('completeSignupBtn');
    const verificationStatus = document.getElementById('verificationStatus');
    const uploadSection = document.getElementById('uploadSection');
    let uploadRequired = false;
    
    if (verifyPwdIdBtn) {
        verifyPwdIdBtn.addEventListener('click', function() {
            // Get PWD ID details
            const pwdIdNumber = document.getElementById('pwdIdNumber').value.trim();
            const pwdIdIssuedDate = document.getElementById('pwdIdIssuedDate').value;
            const pwdIdIssuingLGU = document.getElementById('pwdIdIssuingLGU').value;
            
            // Validate input
            if (!pwdIdNumber) {
                showVerificationStatus('error', 'Please enter your PWD ID Number');
                return;
            }
            
            if (!pwdIdIssuedDate) {
                showVerificationStatus('error', 'Please enter the date your PWD ID was issued');
                return;
            }
            
            if (!pwdIdIssuingLGU) {
                showVerificationStatus('error', 'Please select the issuing LGU/Municipality');
                return;
            }
            
            // Show loading state
            verifyPwdIdBtn.disabled = true;
            verifyPwdIdBtn.innerHTML = '<span class="spinner"></span> Verifying...';
            showVerificationStatus('loading', 'Connecting to DOH database for verification. This may take a moment...');
            
            // Create form data for verification
            const formData = new FormData();
            formData.append('pwdIdNumber', pwdIdNumber);
            formData.append('pwdIdIssuedDate', pwdIdIssuedDate);
            formData.append('pwdIdIssuingLGU', pwdIdIssuingLGU);
            formData.append('action', 'verify'); // Tell backend this is a verification request
            formData.append('skipImage', 'true'); // Indicate this is verification without image first
            
            // Send verification request
            fetch('../../backend/candidate/pwd_verification.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                verifyPwdIdBtn.disabled = false;
                verifyPwdIdBtn.innerHTML = 'Verify PWD ID';
                
                if (data.status === 'success') {
                    // Success - DOH verification worked without image
                    showVerificationStatus('success', data.message || 'PWD ID successfully verified through DOH database!');
                    
                    // Enable complete signup button
                    completeSignupBtn.disabled = false;
                    
                    // Store verification token for signup process
                    sessionStorage.setItem('pwdVerificationToken', data.token);
                    
                    // Hide upload section as it's not needed
                    uploadSection.style.display = 'none';
                    uploadRequired = false;
                    
                } else if (data.status === 'warning') {
                    // Warning - DOH service down or uncertain, need manual verification
                    showVerificationStatus('warning', data.message || 'Automatic verification unavailable. Please upload your PWD ID for manual verification.');
                    
                    // Show upload section
                    uploadSection.style.display = 'block';
                    uploadRequired = true;
                    
                    // Don't enable complete button yet - need upload first
                    completeSignupBtn.disabled = true;
                    
                } else {
                    // Error - verification failed, try manual
                    showVerificationStatus('error', data.message || 'PWD ID verification failed. Please upload your PWD ID for manual verification.');
                    
                    // Show upload section
                    uploadSection.style.display = 'block';
                    uploadRequired = true;
                    
                    // Don't enable complete button yet - need upload first
                    completeSignupBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                verifyPwdIdBtn.disabled = false;
                verifyPwdIdBtn.innerHTML = 'Verify PWD ID';
                showVerificationStatus('error', 'A connection error occurred. Please try again or upload your PWD ID for manual verification.');
                
                // Show upload section
                uploadSection.style.display = 'block';
                uploadRequired = true;
            });
        });
    }
    
    // Handle file upload change event
    const pwdIdUpload = document.getElementById('pwdIdUpload');
    if (pwdIdUpload) {
        pwdIdUpload.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'PWD ID';
            document.getElementById('fileDisplayText').textContent = fileName;
            
            // If file is selected and upload is required, enable complete button
            if (uploadRequired && e.target.files.length > 0) {
                completeSignupBtn.disabled = false;
            }
        });
    }
    
    // UPDATED: Complete registration process - redirect to login instead of account setup
    if (completeSignupBtn) {
        completeSignupBtn.addEventListener('click', function() {
            // Show loading state
            completeSignupBtn.disabled = true;
            completeSignupBtn.textContent = 'Processing...';
            
            // Log for debugging
            console.log('Starting form submission...');
            
            // Collect all form data
            const formData = new FormData();
            formData.append('firstName', document.querySelector('input[name="firstName"]').value);
            formData.append('middleName', document.querySelector('input[name="middleName"]').value || '');
            formData.append('lastName', document.querySelector('input[name="lastName"]').value);
            formData.append('suffix', document.querySelector('input[name="suffix"]').value || '');
            formData.append('email', document.querySelector('input[name="email"]').value);
            formData.append('phone', document.getElementById('phoneNumber').value);
            formData.append('disability', document.querySelector('select[name="disabilityType"]').value);
            formData.append('password', document.getElementById('password').value);
            formData.append('pwdIdNumber', document.getElementById('pwdIdNumber').value);
            formData.append('pwdIdIssuedDate', document.getElementById('pwdIdIssuedDate').value);
            formData.append('pwdIdIssuingLGU', document.getElementById('pwdIdIssuingLGU').value);
            
            // Add file if available
            const pwdIdFile = document.getElementById('pwdIdUpload').files[0];
            if (pwdIdFile) {
                console.log('Adding file to form data:', pwdIdFile.name);
                formData.append('pwdIdFile', pwdIdFile);
            }
            
            // Send AJAX request
            fetch('../../backend/candidate/signup_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text(); // Get response as text first
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text); // Try to parse as JSON
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Server returned an invalid response format');
                }
            })
            .then(data => {
                console.log('Parsed response data:', data);
                completeSignupBtn.disabled = false;
                completeSignupBtn.textContent = 'Complete Sign Up';
                
                if (data.status === 'success') {
                    // UPDATED: Redirect to login page instead of account setup
                    alert('Registration successful! Please log in with your credentials.');
                    window.location.href = '../../frontend/candidate/login.php';
                } else {
                    alert(data.message || 'An error occurred with the registration.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                completeSignupBtn.disabled = false;
                completeSignupBtn.textContent = 'Complete Sign Up';
                alert('An error occurred: ' + error.message + '. Please try again.');
            });
        });
    }
});

// Helper function to show verification status
function showVerificationStatus(type, message) {
    const statusElement = document.getElementById('verificationStatus');
    if (!statusElement) return;
    
    // Remove all status classes
    statusElement.classList.remove('loading', 'success', 'error', 'warning');
    
    // Add appropriate class and message
    statusElement.classList.add(type);
    statusElement.innerHTML = message;
}
// backbtn
// Add event listeners for back buttons
document.getElementById('profileBackBtn').addEventListener('click', function() {
    goToStep(1);
});

document.getElementById('verificationBackBtn').addEventListener('click', function() {
    goToStep(2);
});

// Function to handle step navigation
function goToStep(step) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(function(formStep) {
        formStep.classList.remove('active');
    });
    
    // Remove active class from all progress steps
    document.querySelectorAll('.progress-step').forEach(function(progressStep) {
        progressStep.classList.remove('active');
    });
    
    // Activate the target step
    document.getElementById('step' + step + 'Form').classList.add('active');
    document.getElementById('step' + step).classList.add('active');
    
    // If going back to step 1, also activate the next steps completed so far
    for (let i = 1; i <= step; i++) {
        document.getElementById('step' + i).classList.add('active');
    }
}