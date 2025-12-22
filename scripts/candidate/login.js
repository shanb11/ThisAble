// Toggle password visibility
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId || 'password');
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
    
    if (!phoneInput || !phoneError) return false;
    
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
        const phoneError = document.getElementById('phoneError');
        if (phoneError) {
            phoneError.style.display = 'none';
        }
    }
});

// Forgot Password Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordModal = document.getElementById("forgotPasswordModal");
    const forgotPasswordLink = document.getElementById("forgotPasswordLink");
    const closeForgotModal = document.getElementById("closeForgotModal");
    const sendResetLink = document.getElementById("sendResetLink");
    const resetSuccessMessage = document.getElementById("resetSuccessMessage");

    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener("click", function() {
            forgotPasswordModal.style.display = "flex";
        });
    }

    if (closeForgotModal) {
        closeForgotModal.addEventListener("click", function() {
            forgotPasswordModal.style.display = "none";
            if (resetSuccessMessage) resetSuccessMessage.style.display = "none";
            const resetEmail = document.getElementById("resetEmail");
            if (resetEmail) resetEmail.value = "";
        });
    }

    if (sendResetLink) {
        sendResetLink.addEventListener("click", function() {
            const email = document.getElementById("resetEmail").value;
            if (email) {
                // Here you would typically send an API request to initiate the password reset
                // For this example, we'll just show the success message
                if (resetSuccessMessage) resetSuccessMessage.style.display = "block";
                
                // Auto-close the modal after 3 seconds
                setTimeout(function() {
                    forgotPasswordModal.style.display = "none";
                    if (resetSuccessMessage) resetSuccessMessage.style.display = "none";
                    const resetEmail = document.getElementById("resetEmail");
                    if (resetEmail) resetEmail.value = "";
                }, 3000);
            }
        });
    }
});

// Google Sign-in Button Handler
document.addEventListener('DOMContentLoaded', function() {
    const googleSignInBtn = document.getElementById("googleSignInBtn");
    
    if (googleSignInBtn) {
        googleSignInBtn.addEventListener("click", function() {
            window.location.href = '../../backend/candidate/google_auth.php';
        });
    }
});

// Selection Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectionModal = document.getElementById("selectionModal");
    const signupLink = document.getElementById("signupLink");
    const closeSelectionModal = document.getElementById("closeSelectionModal");

    if (signupLink && selectionModal) {
        signupLink.addEventListener("click", function(e) {
            e.preventDefault();
            selectionModal.style.display = "flex";
        });
        
        // Add inline handler for extra safety
        signupLink.setAttribute('onclick', "event.preventDefault(); document.getElementById('selectionModal').style.display='flex'; return false;");
    }

    if (closeSelectionModal) {
        closeSelectionModal.addEventListener("click", function() {
            selectionModal.style.display = "none";
        });
    }
});

// Add event listener for login form submission
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.querySelector('.login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', function() {
            const emailInput = document.querySelector('input[type="email"]');
            const passwordInput = document.getElementById('password');
            
            if (!emailInput || !passwordInput) {
                console.error('Email or password input not found');
                return;
            }
            
            const email = emailInput.value;
            const password = passwordInput.value;
            
            if (!email || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            // Disable button to prevent double submission
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing in...';
            
            // Create form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            
            // Send AJAX request
            fetch('../../backend/candidate/login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                
                return response.json();
            })
            .then(data => {
                // Re-enable button
                loginBtn.disabled = false;
                loginBtn.textContent = 'Sign In';
                
                if (data.status === 'success') {
                    // Store seeker_id and user_name in localStorage
                    localStorage.setItem('seekerId', data.seeker_id);
                    localStorage.setItem('userName', data.user_name);
                    localStorage.setItem('loggedIn', 'true');
                    
                    // Store setup completion status
                    localStorage.setItem('accountSetupComplete', data.setup_complete ? 'true' : 'false');
                    
                    // Redirect based on account setup status
                    if (data.setup_complete) {
                        window.location.href = window.APP_BASE_URL + 'frontend/candidate/dashboard.php';
                    } else {
                        // Use the redirect_page from server response
                        const redirectPage = data.redirect_page || 'accountsetup.php';
                        window.location.href = window.APP_BASE_URL + 'frontend/candidate/' + redirectPage;
                    }
                } else {
                    alert(data.message || 'Login failed');
                }
            })
            .catch(error => {
                // Re-enable button
                loginBtn.disabled = false;
                loginBtn.textContent = 'Sign In';
                
                console.error('Login error:', error);
                alert('An error occurred during login. Please try again.');
            });
        });
    }
});

// PWD ID file upload display
document.addEventListener('DOMContentLoaded', function() {
    const pwdIdUpload = document.getElementById('pwdIdUpload');
    
    if (pwdIdUpload) {
        pwdIdUpload.addEventListener('change', function(e) {
            const fileDisplayText = document.getElementById('fileDisplayText');
            if (fileDisplayText) {
                const fileName = e.target.files[0]?.name || 'PWD ID';
                fileDisplayText.textContent = fileName;
            }
        });
    }
});

// Close modals when clicking outside of them
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener("click", function(event) {
        const modals = [
            { modal: document.getElementById('forgotPasswordModal'), content: '.modal-content' },
            { modal: document.getElementById('googleSignInModal'), content: '.modal-content' },
            { modal: document.getElementById('selectionModal'), content: '.modal-content' },
            { modal: document.getElementById('pwdDetailsModal'), content: '.modal-content' }
        ];
        
        modals.forEach(item => {
            if (item.modal && event.target === item.modal) {
                item.modal.style.display = "none";
                
                // For forgot password modal, also reset fields
                if (item.modal.id === 'forgotPasswordModal') {
                    const resetSuccessMessage = document.getElementById("resetSuccessMessage");
                    if (resetSuccessMessage) resetSuccessMessage.style.display = "none";
                    const resetEmail = document.getElementById("resetEmail");
                    if (resetEmail) resetEmail.value = "";
                }
            }
        });
    });
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
});

// Check for PWD details modal for Google users
document.addEventListener('DOMContentLoaded', function() {
    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('show_pwd_details') && urlParams.get('show_pwd_details') === '1') {
        // Show the PWD details modal
        const pwdDetailsModal = document.getElementById('pwdDetailsModal');
        if (pwdDetailsModal) {
            pwdDetailsModal.style.display = 'flex';
        }
    }
    
    // Handle error messages
    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        if (error === 'google_auth_failed') {
            alert('Google authentication failed. Please try again.');
        } else if (error === 'google_userinfo_failed') {
            alert('Failed to retrieve your Google account information. Please try again.');
        }
    }
});

// Handle PWD Verification for Google sign-up
document.addEventListener('DOMContentLoaded', function() {
    const verifyPwdIdBtn = document.getElementById('verifyPwdIdBtn');
    const completeProfileBtn = document.getElementById('completeProfileBtn');
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
                    completeProfileBtn.disabled = false;
                    
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
                    completeProfileBtn.disabled = true;
                    
                } else {
                    // Error - verification failed, try manual
                    showVerificationStatus('error', data.message || 'PWD ID verification failed. Please upload your PWD ID for manual verification.');
                    
                    // Show upload section
                    uploadSection.style.display = 'block';
                    uploadRequired = true;
                    
                    // Don't enable complete button yet - need upload first
                    completeProfileBtn.disabled = true;
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
                completeProfileBtn.disabled = false;
            }
        });
    }
});

// Handle Google PWD Details Form submission
document.addEventListener('DOMContentLoaded', function() {
    const googleProfileForm = document.getElementById('googleProfileForm');
    
    if (googleProfileForm) {
        googleProfileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form inputs
            if (!validateGoogleProfileForm()) {
                return;
            }
            
            // Get the submit button and set loading state
            const submitBtn = document.getElementById('completeProfileBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
            }
            
            // Create FormData object
            const formData = new FormData(googleProfileForm);
            
            // Add verification token if available
            const verificationToken = sessionStorage.getItem('pwdVerificationToken') || '';
            if (verificationToken) {
                formData.append('verificationToken', verificationToken);
            }
            
            // Add the PWD ID file if available
            const pwdIdFile = document.getElementById('pwdIdUpload').files[0];
            if (pwdIdFile) {
                formData.append('pwdIdFile', pwdIdFile);
            }
            
            // Send AJAX request
            fetch('../../backend/candidate/complete_google_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Registration';
                }
                
                if (data.status === 'success') {
                    // Clear verification token
                    sessionStorage.removeItem('pwdVerificationToken');
                    
                    alert('Profile completed successfully! Redirecting to account setup...');
                    const baseUrl = (typeof window.APP_BASE_URL !== 'undefined') ? window.APP_BASE_URL : (typeof API !== 'undefined' && API.baseUrl) ? API.baseUrl : '/';
                    window.location.href = baseUrl + 'frontend/candidate/accountsetup.php';
                } else {
                    const formErrors = document.getElementById('formErrors');
                    if (formErrors) {
                        formErrors.style.display = 'block';
                        formErrors.textContent = data.message || 'An error occurred. Please try again.';
                    } else {
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Registration';
                }
                
                const formErrors = document.getElementById('formErrors');
                if (formErrors) {
                    formErrors.style.display = 'block';
                    formErrors.textContent = 'An error occurred. Please try again.';
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }
});

// Validate Google profile form
function validateGoogleProfileForm() {
    let valid = true;
    
    // Validate phone number
    if (!validatePhoneNumber()) {
        valid = false;
    }
    
    // Check required fields
    const requiredSelectors = [
        '#phoneNumber',
        'select[name="disability"]',
        '#pwdIdNumber',
        '#pwdIdIssuedDate',
        '#pwdIdIssuingLGU'
    ];
    
    requiredSelectors.forEach(selector => {
        const field = document.querySelector(selector);
        if (field && !field.value.trim()) {
            field.classList.add('error-input');
            valid = false;
        } else if (field) {
            field.classList.remove('error-input');
        }
    });
    
    // Show error message if form is invalid
    const formErrors = document.getElementById('formErrors');
    if (!valid && formErrors) {
        formErrors.style.display = 'block';
        formErrors.textContent = 'Please fill in all required fields correctly.';
    } else if (formErrors) {
        formErrors.style.display = 'none';
    }
    
    return valid;
}

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