// DOM elements
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const toggleIcon = document.getElementById('toggle-icon');
const backButtons = document.querySelectorAll('.back-btn');
const settingsMain = document.getElementById('settings-main');
const detailContainers = document.querySelectorAll('.setting-detail-container');
const signOutBtn = document.getElementById('sign-out-btn');
const closeAccountBtn = document.getElementById('close-account-btn');
const signOutModal = document.getElementById('sign-out-modal');
const closeAccountModal = document.getElementById('close-account-modal');
const cancelSignOut = document.getElementById('cancel-sign-out');
const confirmSignOut = document.getElementById('confirm-sign-out');
const cancelCloseAccount = document.getElementById('cancel-close-account');
const confirmCloseAccount = document.getElementById('confirm-close-account');

let isGoogleAccount = false;

async function checkAccountType() {
    try {
        const response = await fetch('../../backend/candidate/check_account_type.php');
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

// Back button functionality
backButtons.forEach(button => {
    button.addEventListener('click', () => {
        detailContainers.forEach(container => {
            container.classList.remove('show');
        });
        settingsMain.style.display = 'block';
    });
});

// Sign Out functionality
if (signOutBtn) {
    signOutBtn.addEventListener('click', () => {
        signOutModal.classList.add('show');
    });
}

if (cancelSignOut) {
    cancelSignOut.addEventListener('click', () => {
        signOutModal.classList.remove('show');
    });
}

if (confirmSignOut) {
    confirmSignOut.addEventListener('click', () => {
        // Clear localStorage items
        localStorage.removeItem('seekerId');
        localStorage.removeItem('userName');
        localStorage.removeItem('loggedIn');
        localStorage.removeItem('preferredWorkStyle');
        localStorage.removeItem('preferredJobType');
        localStorage.removeItem('disabilityType');
        localStorage.removeItem('selectedApparentNeeds');
        localStorage.removeItem('selectedNonApparentNeeds');
        localStorage.removeItem('noNeedsSelected');
        localStorage.removeItem('noNeedsSelectedNonApparent');
        localStorage.removeItem('selectedSkillsArray');
        localStorage.removeItem('resumeUploaded');
        localStorage.removeItem('setupComplete');
        localStorage.removeItem('uploadedFileName');
        localStorage.removeItem('uploadedFileSize');
        
        // Clear sessionStorage items
        sessionStorage.removeItem('seekerId');
        sessionStorage.removeItem('userName');
        sessionStorage.removeItem('loggedIn');
        
        // Redirect to logout.php to destroy server-side session
        window.location.href = '../../backend/candidate/logout.php';
    });
}

// Close Account functionality
if (closeAccountBtn) {
    closeAccountBtn.addEventListener('click', async () => {
        // Update modal content based on account type
        const modalContent = document.querySelector('#close-account-modal .modal-content');
        
        if (isGoogleAccount) {
            // Google account - show confirmation checkbox instead of password
            modalContent.innerHTML = `
                <h3>Close Your Account</h3>
                <p style="margin: 15px 0;">You're about to close your account. This action will:</p>
                <ul style="margin-left: 20px; margin-bottom: 15px;">
                    <li>Deactivate your profile immediately</li>
                    <li>Withdraw all pending job applications</li>
                    <li>Give you 30 days to reactivate by logging in</li>
                </ul>
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Note:</strong> Since you signed in with Google, you can reactivate your account anytime within 30 days by simply logging in again with Google.
                </div>
                <div style="margin: 20px 0;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" id="confirm-google-closure" style="margin-right: 10px;">
                        <span>I understand and want to close my account</span>
                    </label>
                </div>
            `;
        } else {
            // Regular account - show password field
            modalContent.innerHTML = `
                <h3>Close Your Account</h3>
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
        
        closeAccountModal.classList.add('show');
    });
}

if (cancelCloseAccount) {
    cancelCloseAccount.addEventListener('click', () => {
        closeAccountModal.classList.remove('show');
    });
}

if (confirmCloseAccount) {
    confirmCloseAccount.addEventListener('click', async () => {
        let formData = new FormData();
        
        if (isGoogleAccount) {
            // Google account - check confirmation checkbox
            const checkbox = document.getElementById('confirm-google-closure');
            if (!checkbox || !checkbox.checked) {
                alert('Please confirm that you want to close your account.');
                return;
            }
            formData.append('confirm_closure', 'yes');
        } else {
            // Regular account - get password
            const password = document.getElementById('confirm-password-close').value;
            if (!password) {
                alert('Please enter your password to confirm account closure.');
                return;
            }
            formData.append('password', password);
        }
        
        // Disable button to prevent double submission
        confirmCloseAccount.disabled = true;
        confirmCloseAccount.textContent = 'Closing...';
        
        try {
            const response = await fetch('../../backend/candidate/close_account.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Clear all localStorage
                localStorage.clear();
                
                // Clear all sessionStorage
                sessionStorage.clear();
                
                // Show success message
                alert(result.message || 'Your account has been closed successfully. You have 30 days to reactivate by logging in again.');
                
                // Close modal
                closeAccountModal.classList.remove('show');
                
                // Redirect to login page
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1000);
                
            } else {
                // Show error message
                alert(result.message || 'Failed to close account. Please try again.');
                
                // Re-enable button
                confirmCloseAccount.disabled = false;
                confirmCloseAccount.textContent = 'Close Account';
            }
            
        } catch (error) {
            console.error('Close account error:', error);
            alert('An error occurred while closing your account. Please try again.');
            
            // Re-enable button
            confirmCloseAccount.disabled = false;
            confirmCloseAccount.textContent = 'Close Account';
        }
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupFormHandlers();
    initializeFAQToggle();
    initializeSettingItems();
});

// Setup form handlers
function setupFormHandlers() {
    // Password Form
    const passwordForm = document.getElementById('password-security-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await updatePassword();
        });
    }
}

// Initialize setting items click handlers
function initializeSettingItems() {
    const settingItems = document.querySelectorAll('.setting-item');
    const settingsMain = document.getElementById('settings-main');
    
    settingItems.forEach(item => {
        item.addEventListener('click', () => {
            const settingId = item.dataset.setting;
            const detailContainer = document.getElementById(`${settingId}-detail`);
            
            if (detailContainer) {
                settingsMain.style.display = 'none';
                detailContainer.classList.add('show');
            }
        });
    });
}

// Update password
async function updatePassword() {
    const formData = new FormData();
    formData.append('current_password', document.getElementById('current-password').value);
    formData.append('new_password', document.getElementById('new-password').value);
    formData.append('confirm_password', document.getElementById('confirm-password').value);
    
    try {
        showLoading();
        const response = await fetch('../../backend/candidate/update_password.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Password updated successfully', 'success');
            document.getElementById('password-security-form').reset();
            returnToSettingsMain();
        } else {
            showToast('Error: ' + result.message, 'error');
        }
    } catch (error) {
        showToast('Network error occurred', 'error');
        console.error('Error:', error);
    } finally {
        hideLoading();
    }
}

// Helper function to return to settings main
function returnToSettingsMain() {
    document.querySelectorAll('.setting-detail-container').forEach(container => {
        container.classList.remove('show');
    });
    document.getElementById('settings-main').style.display = 'block';
}

// FAQ and Resource Toggle Functionality
function initializeFAQToggle() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        item.addEventListener('click', function() {
            const faqId = this.getAttribute('data-faq');
            const answer = document.getElementById('faq-' + faqId);
            const arrow = this.querySelector('.setting-arrow i');
            
            if (answer && (answer.style.display === 'none' || answer.style.display === '')) {
                // Close all other FAQ answers
                document.querySelectorAll('.faq-answer').forEach(ans => {
                    ans.style.display = 'none';
                });
                document.querySelectorAll('.faq-item .setting-arrow i').forEach(arr => {
                    arr.classList.remove('fa-chevron-down');
                    arr.classList.add('fa-chevron-right');
                });
                
                // Open this answer
                answer.style.display = 'block';
                arrow.classList.remove('fa-chevron-right');
                arrow.classList.add('fa-chevron-down');
            } else if (answer) {
                // Close this answer
                answer.style.display = 'none';
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-right');
            }
        });
    });
    
    // Resource Items Toggle
    const resourceItems = document.querySelectorAll('.resource-item');
    resourceItems.forEach(item => {
        item.addEventListener('click', function() {
            const resourceId = this.getAttribute('data-resource');
            const content = document.getElementById('resource-' + resourceId);
            const arrow = this.querySelector('.setting-arrow i');
            
            if (content && (content.style.display === 'none' || content.style.display === '')) {
                // Close all other resource contents
                document.querySelectorAll('.resource-content').forEach(cont => {
                    cont.style.display = 'none';
                });
                document.querySelectorAll('.resource-item .setting-arrow i').forEach(arr => {
                    arr.classList.remove('fa-chevron-down');
                    arr.classList.add('fa-chevron-right');
                });
                
                // Open this content
                content.style.display = 'block';
                arrow.classList.remove('fa-chevron-right');
                arrow.classList.add('fa-chevron-down');
            } else if (content) {
                // Close this content
                content.style.display = 'none';
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-right');
            }
        });
    });
}

// Close modals when clicking outside
window.addEventListener('click', (e) => {
    if (signOutModal && e.target === signOutModal) {
        signOutModal.classList.remove('show');
    }
    if (closeAccountModal && e.target === closeAccountModal) {
        closeAccountModal.classList.remove('show');
    }
});

// Mobile sidebar toggle
const mobileWidth = 768;
function checkMobileView() {
    if (window.innerWidth <= mobileWidth && sidebar) {
        sidebar.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        }
    }
}

checkMobileView();
window.addEventListener('resize', checkMobileView);

// Helper functions for showing loading and toast notifications
function showLoading() {
    if (document.getElementById('loading-overlay')) {
        document.getElementById('loading-overlay').style.display = 'flex';
        return;
    }
    
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.style.position = 'fixed';
    loadingOverlay.style.top = '0';
    loadingOverlay.style.left = '0';
    loadingOverlay.style.width = '100%';
    loadingOverlay.style.height = '100%';
    loadingOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    loadingOverlay.style.display = 'flex';
    loadingOverlay.style.justifyContent = 'center';
    loadingOverlay.style.alignItems = 'center';
    loadingOverlay.style.zIndex = '9999';
    
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    spinner.style.width = '50px';
    spinner.style.height = '50px';
    spinner.style.border = '5px solid #f3f3f3';
    spinner.style.borderTop = '5px solid var(--primary)';
    spinner.style.borderRadius = '50%';
    spinner.style.animation = 'spin 2s linear infinite';
    
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    
    document.head.appendChild(style);
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

function showToast(message, type = 'info') {
    if (!document.getElementById('toast-container')) {
        const toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.bottom = '20px';
        toastContainer.style.right = '20px';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = message;
    
    toast.style.padding = '12px 20px';
    toast.style.borderRadius = '4px';
    toast.style.marginBottom = '10px';
    toast.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.2)';
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    
    switch (type) {
        case 'success':
            toast.style.backgroundColor = '#28a745';
            toast.style.color = 'white';
            break;
        case 'warning':
            toast.style.backgroundColor = '#ffc107';
            toast.style.color = '#212529';
            break;
        case 'error':
            toast.style.backgroundColor = '#dc3545';
            toast.style.color = 'white';
            break;
        default:
            toast.style.backgroundColor = '#17a2b8';
            toast.style.color = 'white';
    }
    
    document.getElementById('toast-container').appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            document.getElementById('toast-container').removeChild(toast);
        }, 300);
    }, 3000);
}