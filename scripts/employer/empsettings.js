// DOM Elements
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const toggleIcon = document.getElementById('toggle-icon');
const settingsMain = document.getElementById('settings-main');
const settingItems = document.querySelectorAll('.setting-item');
const backButtons = document.querySelectorAll('.back-btn');
const forms = document.querySelectorAll('form');
const toast = document.getElementById('toast');
const toastMessage = document.getElementById('toast-message');

// Modal elements
const signOutBtn = document.getElementById('sign-out-btn');
const closeAccountBtn = document.getElementById('close-account-btn');
const signOutModal = document.getElementById('sign-out-modal');
const closeAccountModal = document.getElementById('close-account-modal');
const cancelSignOut = document.getElementById('cancel-sign-out');
const confirmSignOut = document.getElementById('confirm-sign-out');
const cancelCloseAccount = document.getElementById('cancel-close-account');
const confirmCloseAccount = document.getElementById('confirm-close-account');

// Single loader instance
let loaderInstance = null;
let isGoogleAccount = false;

// Create loader once
function initLoader() {
    if (!loaderInstance) {
        loaderInstance = document.createElement('div');
        loaderInstance.id = 'global-loader';
        loaderInstance.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        `;
        
        const spinner = document.createElement('div');
        spinner.style.cssText = `
            border: 5px solid #f3f3f3;
            border-top: 5px solid #257180;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        `;
        
        const style = document.createElement('style');
        style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
        
        document.head.appendChild(style);
        loaderInstance.appendChild(spinner);
        document.body.appendChild(loaderInstance);
    }
}

// Show/hide loader
function showLoader() {
    if (loaderInstance) {
        loaderInstance.style.display = 'flex';
    }
}

function hideLoader() {
    if (loaderInstance) {
        loaderInstance.style.display = 'none';
    }
}

// ========== ACCOUNT TYPE & SECURITY FUNCTIONS ==========
async function checkAccountType() {
    try {
        const response = await fetch('../../backend/employer/check_account_type.php');
        const result = await response.json();
        
        if (result.success) {
            isGoogleAccount = result.is_google_account;
        }
    } catch (error) {
        console.error('Error checking account type:', error);
    }
}

// Call this when settings page loads
if (closeAccountBtn) {
    checkAccountType();
}

function handleAccountTypeUI(accountData) {
    const passwordForm = document.getElementById('password-security-form');
    if (!passwordForm) return;
    
    if (accountData.is_google_account) {
        const passwordFields = passwordForm.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            const formGroup = field.closest('.form-group');
            if (formGroup) {
                formGroup.style.display = 'none';
            }
        });
        
        const submitButton = passwordForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.style.display = 'none';
        }
        
        if (!passwordForm.querySelector('.google-account-info')) {
            const googleMessage = document.createElement('div');
            googleMessage.className = 'google-account-info alert alert-info';
            googleMessage.style.cssText = `
                background-color: #e3f2fd;
                border: 1px solid #2196f3;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            
            googleMessage.innerHTML = `
                <i class="fab fa-google" style="color: #4285f4; font-size: 24px;"></i>
                <div>
                    <strong>Google Account</strong><br>
                    <span style="color: #666;">You signed in with Google (${accountData.email}). Password changes are managed through your Google account settings.</span>
                </div>
            `;
            
            passwordForm.insertBefore(googleMessage, passwordForm.firstChild);
        }
    }
}

// ========== FORM SUBMISSION HANDLERS ==========
async function submitPasswordForm() {
    try {
        const accountData = await checkAccountType();
        
        if (accountData && accountData.is_google_account) {
            showToast('Password cannot be changed for Google accounts.');
            return false;
        }
        
        const currentPassword = document.getElementById('current-password')?.value || '';
        const newPassword = document.getElementById('new-password')?.value || '';
        const confirmPassword = document.getElementById('confirm-password')?.value || '';
        const twoFactor = document.getElementById('two-factor')?.checked || false;
        const rememberLogin = document.getElementById('remember-login')?.checked || false;
        
        if (!currentPassword) {
            throw new Error('Current password is required');
        }
        
        if (!newPassword) {
            throw new Error('New password is required');
        }
        
        if (newPassword.length < 8) {
            throw new Error('New password must be at least 8 characters long');
        }
        
        if (newPassword !== confirmPassword) {
            throw new Error('New passwords do not match');
        }
        
        const requestData = {
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword,
            two_factor: twoFactor,
            remember_login: rememberLogin
        };
        
        const response = await fetch('../../backend/employer/update_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Password updated successfully!');
            
            document.getElementById('current-password').value = '';
            document.getElementById('new-password').value = '';
            document.getElementById('confirm-password').value = '';
            
            return true;
        } else {
            throw new Error(data.message || 'Failed to update password');
        }
        
    } catch (error) {
        console.error('Error updating password:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

// ========== EVENT LISTENERS ==========

// Toggle sidebar
if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        } else {
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
    });
}

// Show specific setting detail
settingItems.forEach(item => {
    item.addEventListener('click', () => {
        const settingId = item.getAttribute('data-setting');
        const detailContainer = document.getElementById(`${settingId}-detail`);
        
        if (detailContainer) {
            hideAllDetailContainers();
            detailContainer.classList.add('show');
            settingsMain.style.display = 'none';
            window.scrollTo(0, 0);
            saveActiveTab(detailContainer.id);
        }
    });
});

// Back button functionality
backButtons.forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.getAttribute('data-target');
        const target = document.getElementById(targetId);
        
        if (target) {
            hideAllDetailContainers();
            target.style.display = 'block';
            sessionStorage.removeItem('activeSettingTab');
        }
    });
});

// Form submission
forms.forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        showLoader();
        
        let success = false;
        
        try {
            if (form.id === 'password-security-form') {
                success = await submitPasswordForm();
            } else {
                await new Promise(resolve => setTimeout(resolve, 800));
                success = true;
                showToast('Settings updated successfully!');
            }
            
            if (success) {
                setTimeout(() => {
                    hideAllDetailContainers();
                    settingsMain.style.display = 'block';
                    sessionStorage.removeItem('activeSettingTab');
                }, 1000);
            }
            
        } catch (error) {
            console.error('Form submission error:', error);
            showToast('An error occurred. Please try again.');
        } finally {
            hideLoader();
        }
    });
});

// Helper function to hide all detail containers
function hideAllDetailContainers() {
    document.querySelectorAll('.setting-detail-container').forEach(container => {
        container.classList.remove('show');
    });
}

// Show toast notification
function showToast(message) {
    toastMessage.textContent = message;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Modal utility functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.classList.remove('show');
    });
}

// Sign Out functionality
if (signOutBtn) {
    signOutBtn.addEventListener('click', () => {
        openModal('sign-out-modal');
    });
}

if (cancelSignOut) {
    cancelSignOut.addEventListener('click', () => {
        closeModal('sign-out-modal');
    });
}

if (confirmSignOut) {
    confirmSignOut.addEventListener('click', () => {
        showLoader();
        
        setTimeout(() => {
            hideLoader();
            showToast('Signed out successfully.');
            closeModal('sign-out-modal');
            
            setTimeout(() => {
                window.location.href = '../employer/emplogin.php';
            }, 1000);
        }, 800);
    });
}

// Close Account functionality
if (closeAccountBtn) {
    closeAccountBtn.addEventListener('click', async () => {
        // Update modal content based on account type
        const modalContent = document.querySelector('#close-account-modal .modal-body');
        
        if (isGoogleAccount) {
            // Google account - show confirmation checkbox instead of password
            modalContent.innerHTML = `
                <h4 style="margin-bottom: 15px;">Close Company Account</h4>
                <p style="margin: 15px 0;">You're about to close your company account. This action will:</p>
                <ul style="margin-left: 20px; margin-bottom: 15px;">
                    <li>Deactivate your company profile immediately</li>
                    <li>Close all active job postings</li>
                    <li>Give you 30 days to reactivate by logging in</li>
                </ul>
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Note:</strong> Since you signed in with Google, you can reactivate your account anytime within 30 days by simply logging in again with Google.
                </div>
                <div style="margin: 20px 0;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="confirm-google-closure" style="margin-right: 10px;">
                        <span>I understand and want to close the company account</span>
                    </label>
                </div>
            `;
        } else {
            // Regular account - show password field
            modalContent.innerHTML = `
                <h4 style="margin-bottom: 15px;">Close Company Account</h4>
                <p style="margin: 15px 0;">Please enter your password to confirm account closure.</p>
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Note:</strong> You have 30 days to reactivate by logging in again.
                </div>
                <input type="password" 
                       id="confirm-password-close" 
                       class="form-control" 
                       placeholder="Enter your password"
                       style="width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #ddd; border-radius: 5px;">
            `;
        }
        
        openModal('close-account-modal');
    });
}

if (cancelCloseAccount) {
    cancelCloseAccount.addEventListener('click', () => {
        closeModal('close-account-modal');
    });
}

if (confirmCloseAccount) {
    confirmCloseAccount.addEventListener('click', async () => {
        let formData = new FormData();
        
        if (isGoogleAccount) {
            // Google account - check confirmation checkbox
            const checkbox = document.getElementById('confirm-google-closure');
            if (!checkbox || !checkbox.checked) {
                showToast('Please confirm that you want to close your account.');
                return;
            }
            formData.append('confirm_closure', 'yes');
        } else {
            // Regular account - get password
            const password = document.getElementById('confirm-password-close').value;
            if (!password) {
                showToast('Please enter your password to confirm account closure.');
                return;
            }
            formData.append('password', password);
        }
        
        // Disable button and show loading
        confirmCloseAccount.disabled = true;
        const originalText = confirmCloseAccount.textContent;
        confirmCloseAccount.textContent = 'Closing Account...';
        
        showLoader();
        
        try {
            const response = await fetch('../../backend/employer/close_account.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            hideLoader();
            
            if (result.success) {
                // Show success toast/message
                showToast(result.message || 'Your company account has been closed successfully.');
                
                // Close modal
                closeModal('close-account-modal');
                
                // Clear any stored session data
                sessionStorage.clear();
                
                // Redirect to login page
                setTimeout(() => {
                    window.location.href = '../employer/emplogin.php';
                }, 2000);
                
            } else {
                // Show error message
                showToast(result.message || 'Failed to close account. Please try again.');
                
                // Re-enable button
                confirmCloseAccount.disabled = false;
                confirmCloseAccount.textContent = originalText;
            }
            
        } catch (error) {
            console.error('Close account error:', error);
            hideLoader();
            showToast('An error occurred while closing your account. Please try again.');
            
            // Re-enable button
            confirmCloseAccount.disabled = false;
            confirmCloseAccount.textContent = originalText;
        }
    });
}

// Logout from all devices functionality
const logoutAllDevicesBtn = document.getElementById('logout-all-devices');
if (logoutAllDevicesBtn) {
    logoutAllDevicesBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to sign out from all devices? You will need to sign in again on all devices.')) {
            showLoader();
            
            // Here you would make an API call to logout from all devices
            setTimeout(() => {
                hideLoader();
                showToast('Signed out from all devices successfully.');
                
                setTimeout(() => {
                    window.location.href = '../employer/emplogin.php';
                }, 1500);
            }, 800);
        }
    });
}

// Close modal buttons
document.querySelectorAll('.modal-close').forEach(closeBtn => {
    closeBtn.addEventListener('click', () => {
        closeBtn.closest('.modal-overlay').classList.remove('show');
    });
});

document.querySelectorAll('.modal-cancel-btn').forEach(cancelBtn => {
    cancelBtn.addEventListener('click', () => {
        cancelBtn.closest('.modal-overlay').classList.remove('show');
    });
});

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('show');
        }
    });
});

// Session storage functions
function saveActiveTab(tabId) {
    sessionStorage.setItem('activeSettingTab', tabId);
}

function restoreActiveTab() {
    const activeTabId = sessionStorage.getItem('activeSettingTab');
    if (activeTabId) {
        const detailContainer = document.getElementById(activeTabId);
        if (detailContainer) {
            hideAllDetailContainers();
            detailContainer.classList.add('show');
            settingsMain.style.display = 'none';
        }
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize loader
    initLoader();
    
    // Check account type for security settings
    checkAccountType();
    
    // Restore active tab if any
    restoreActiveTab();
    
    // Mobile responsiveness
    if (window.innerWidth <= 768 && sidebar) {
        sidebar.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        }
    }
});