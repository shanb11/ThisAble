// Employer Login JavaScript - Google OAuth + Regular Login
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById('login-btn');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const rememberMeInput = document.getElementById('remember-me');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const messageContainer = document.getElementById('message-container');
    const alertMessage = document.getElementById('alert-message');
    const alertText = document.getElementById('alert-text');
    
    // Add Google login button after other elements
    addGoogleLoginButton();
    
    // Check for URL parameters (verification success, errors, etc.)
    checkUrlParameters();
    
    // Add event listeners
    if (loginBtn) loginBtn.addEventListener('click', handleLogin);
    if (forgotPasswordLink) forgotPasswordLink.addEventListener('click', showForgotPassword);
    
    // Handle Enter key press
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && (emailInput === document.activeElement || passwordInput === document.activeElement)) {
            handleLogin();
        }
    });
    
    // Add Google login button
    function addGoogleLoginButton() {
        // Check if Google button already exists
        if (document.getElementById('google-login-btn')) return;
        
        // Find the login button and add Google login before it
        const loginButton = document.querySelector('.login-btn');
        if (loginButton) {
            const googleBtn = document.createElement('button');
            googleBtn.type = 'button';
            googleBtn.className = 'google-login-btn';
            googleBtn.id = 'google-login-btn';
            googleBtn.innerHTML = '<i class="fab fa-google"></i> Continue with Google';
            
            // Add Google button before regular login button
            loginButton.parentNode.insertBefore(googleBtn, loginButton);
            
            // Add divider
            const divider = document.createElement('div');
            divider.className = 'divider';
            divider.innerHTML = '<span>OR</span>';
            loginButton.parentNode.insertBefore(divider, loginButton);
            
            // Add event listener for Google login
            googleBtn.addEventListener('click', function() {
                window.location.href = '../../backend/employer/google_auth.php';
            });
            
            // Add CSS for Google login button
            addGoogleLoginStyles();
        }
    }
    
    // Add CSS for Google login button
    function addGoogleLoginStyles() {
        if (!document.querySelector('#google-login-styles')) {
            const styles = document.createElement('style');
            styles.id = 'google-login-styles';
            styles.textContent = `
                .google-login-btn {
                    width: 100%;
                    padding: 12px;
                    background: white;
                    border: 2px solid #e5e5e5;
                    border-radius: 8px;
                    font-size: 16px;
                    font-weight: 500;
                    color: #333;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 12px;
                    transition: all 0.2s ease;
                    margin-bottom: 16px;
                }
                .google-login-btn:hover {
                    border-color: #ccc;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                .google-login-btn i {
                    color: #4285f4;
                    font-size: 18px;
                }
                .divider {
                    display: flex;
                    align-items: center;
                    margin: 20px 0;
                    color: #666;
                    font-size: 14px;
                }
                .divider::before,
                .divider::after {
                    content: '';
                    flex: 1;
                    height: 1px;
                    background: #e5e5e5;
                }
                .divider span {
                    padding: 0 16px;
                }
            `;
            document.head.appendChild(styles);
        }
    }
    
    // Handle login
    async function handleLogin() {
        // Clear previous messages
        hideMessage();
        
        // Get form data
        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value : '';
        const rememberMe = rememberMeInput ? rememberMeInput.checked : false;
        
        // Basic validation
        if (!email || !password) {
            showMessage('Please enter both email and password.', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            showMessage('Please enter a valid email address.', 'error');
            if (emailInput) emailInput.focus();
            return;
        }
        
        try {
            // Show loading state
            setLoadingState(true);
            
            // Prepare login data
            const loginData = {
                email: email,
                password: password,
                remember_me: rememberMe
            };
            
            // Send login request to employer backend (using JSON)
            const response = await fetch('../../backend/employer/login_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success message
                showMessage('Login successful! Redirecting...', 'success');
                
                // Store employer data in session storage for immediate access
                if (result.data) {
                    sessionStorage.setItem('employer_data', JSON.stringify(result.data));
                }
                
                // Redirect after short delay
                setTimeout(() => {
                    if (result.redirect_url) {
                        window.location.href = result.redirect_url;
                    } else {
                        // Default redirect based on setup status
                        if (result.data && result.data.setup_info && !result.data.setup_info.setup_complete) {
                            window.location.href = 'empaccsetup.php';
                        } else {
                            window.location.href = 'empdashboard.php';
                        }
                    }
                }, 1500);
                
            } else {
                // Show error message
                showMessage(result.message || 'Login failed. Please try again.', 'error');
                
                // Focus on appropriate field based on error
                if (result.message && result.message.toLowerCase().includes('email')) {
                    if (emailInput) emailInput.focus();
                } else if (result.message && result.message.toLowerCase().includes('password')) {
                    if (passwordInput) passwordInput.focus();
                }
            }
            
        } catch (error) {
            console.error('Login error:', error);
            showMessage('Connection error. Please check your internet connection and try again.', 'error');
        } finally {
            setLoadingState(false);
        }
    }
    
    // Validate email format
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Show message
    function showMessage(message, type = 'info') {
        if (!alertText || !alertMessage || !messageContainer) return;
        
        alertText.textContent = message;
        alertMessage.className = `alert ${type}`;
        
        // Update icon based on type
        const icon = alertMessage.querySelector('i');
        if (icon) {
            icon.className = `fas fa-${type === 'success' ? 'check-circle' : 
                                       type === 'error' ? 'exclamation-circle' : 
                                       'info-circle'}`;
        }
        
        messageContainer.style.display = 'block';
        
        // Auto-hide info messages after 5 seconds
        if (type === 'info') {
            setTimeout(hideMessage, 5000);
        }
    }
    
    // Hide message
    function hideMessage() {
        if (messageContainer) {
            messageContainer.style.display = 'none';
        }
    }
    
    // Set loading state
    function setLoadingState(loading) {
        if (!loginBtn) return;
        
        if (loading) {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Signing In...';
        } else {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Sign In';
        }
    }
    
    // Check URL parameters for messages
    function checkUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check for verification success
        if (urlParams.get('verified') === '1') {
            showMessage('Email verified successfully! You can now log in to your account.', 'success');
        }
        
        // Check for registration success
        if (urlParams.get('registered') === '1') {
            showMessage('Registration successful! Please login to complete your profile setup.', 'success');
        }
        
        // Check for logout success
        if (urlParams.get('logout') === '1') {
            showMessage('You have been logged out successfully.', 'info');
        }
        
        // Check for session timeout
        if (urlParams.get('timeout') === '1') {
            showMessage('Your session has expired. Please log in again.', 'info');
        }
        
        // Check for errors
        const error = urlParams.get('error');
        if (error) {
            showMessage(decodeURIComponent(error), 'error');
        }
        
        // Clean up URL
        if (urlParams.has('registered') || urlParams.has('verified') || urlParams.has('logout') || urlParams.has('timeout') || urlParams.has('error')) {
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }
});

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return;
    
    const toggleIcon = passwordInput.nextElementSibling.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        }
    } else {
        passwordInput.type = 'password';
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
}

// Forgot Password Modal Functions
function showForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.add('active');
        const resetEmail = document.getElementById('reset-email');
        if (resetEmail) resetEmail.focus();
    }
}

function closeForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.classList.remove('active');
        
        // Clear form
        const resetEmail = document.getElementById('reset-email');
        if (resetEmail) resetEmail.value = '';
        
        const messageEl = document.getElementById('reset-message');
        if (messageEl) {
            messageEl.style.display = 'none';
        }
    }
}

async function sendResetEmail() {
    const resetEmailInput = document.getElementById('reset-email');
    const resetBtn = document.getElementById('reset-btn');
    const messageEl = document.getElementById('reset-message');
    
    if (!resetEmailInput) return;
    
    const resetEmail = resetEmailInput.value.trim();
    
    // Clear previous messages
    if (messageEl) {
        messageEl.style.display = 'none';
    }
    
    // Validate email
    if (!resetEmail) {
        showResetMessage('Please enter your email address.', 'error');
        return;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(resetEmail)) {
        showResetMessage('Please enter a valid email address.', 'error');
        return;
    }
    
    try {
        // Show loading state
        if (resetBtn) {
            resetBtn.disabled = true;
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        }
        
        // Simulate password reset email (replace with real API call)
        await simulateResetEmail(resetEmail);
        
        showResetMessage('Password reset link sent! Please check your email inbox.', 'success');
        
        // Close modal after successful send
        setTimeout(() => {
            closeForgotPassword();
        }, 2000);
        
    } catch (error) {
        console.error('Reset email error:', error);
        showResetMessage(error.message || 'Failed to send reset email. Please try again.', 'error');
    } finally {
        // Restore button state
        if (resetBtn) {
            resetBtn.disabled = false;
            resetBtn.innerHTML = 'Send Reset Link';
        }
    }
}

// Simulate password reset email (replace with real API call)
async function simulateResetEmail(email) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            console.log(`Password reset email would be sent to: ${email}`);
            resolve(true);
        }, 2000);
    });
}

function showResetMessage(message, type) {
    const messageEl = document.getElementById('reset-message');
    if (messageEl) {
        messageEl.textContent = message;
        messageEl.className = `message ${type}`;
        messageEl.style.display = 'block';
    }
}

// Selection Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectionModal = document.getElementById("selectionModal");
    const signupLink = document.getElementById("signupLink");
    const closeSelectionModal = document.getElementById("closeSelectionModal");

    if (signupLink && selectionModal) {
        signupLink.addEventListener("click", function(e) {
            e.preventDefault();
            
            // FORCE RESET ALL STYLES FIRST
            selectionModal.style.cssText = "";
            
            // APPLY CENTERING STYLES DIRECTLY
            selectionModal.style.display = "flex";
            selectionModal.style.position = "fixed";
            selectionModal.style.top = "0";
            selectionModal.style.left = "0";
            selectionModal.style.width = "100vw";
            selectionModal.style.height = "100vh";
            selectionModal.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
            selectionModal.style.zIndex = "9999";
            selectionModal.style.justifyContent = "center";
            selectionModal.style.alignItems = "center";
            selectionModal.style.margin = "0";
            selectionModal.style.padding = "0";
            
            // ENSURE MODAL CONTENT IS ALSO PROPERLY SET
            const modalContent = selectionModal.querySelector('.selection-modal-content');
            if (modalContent) {
                modalContent.style.position = "relative";
                modalContent.style.margin = "0";
                modalContent.style.transform = "none";
            }
            
            console.log("Modal should be centered now");
        });
    }

    if (closeSelectionModal && selectionModal) {
        closeSelectionModal.addEventListener("click", function() {
            selectionModal.style.display = "none";
        });
    }

    // Close when clicking outside - with proper targeting
    if (selectionModal) {
        selectionModal.addEventListener("click", function(e) {
            // Only close if clicking the backdrop, not the content
            if (e.target === selectionModal) {
                selectionModal.style.display = "none";
            }
        });
    }
});

// Close modals when clicking outside of them
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener("click", function(event) {
        const modals = [
            { modal: document.getElementById('forgotPasswordModal'), content: '.modal-content' },
            { modal: document.getElementById('selectionModal'), content: '.modal-content' }
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