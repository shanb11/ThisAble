// DOM elements
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const toggleIcon = document.getElementById('toggle-icon');
const settingItems = document.querySelectorAll('.setting-item');
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
const forms = document.querySelectorAll('form');

// Global user data
let userData = null;

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

// Show setting detail
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

// Back button functionality
backButtons.forEach(button => {
    button.addEventListener('click', () => {
        detailContainers.forEach(container => {
            container.classList.remove('show');
        });
        settingsMain.style.display = 'block';
    });
});

// Sign Out functionality (KEEP THIS EXACTLY AS IS)
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
    closeAccountBtn.addEventListener('click', () => {
        closeAccountModal.classList.add('show');
    });
}

if (cancelCloseAccount) {
    cancelCloseAccount.addEventListener('click', () => {
        closeAccountModal.classList.remove('show');
    });
}

if (confirmCloseAccount) {
    confirmCloseAccount.addEventListener('click', () => {
        const password = document.getElementById('confirm-password-close').value;
        
        if (password) {
            alert('Your account has been closed successfully.');
            closeAccountModal.classList.remove('show');
            window.location.href = 'login.html';
        } else {
            alert('Please enter your password to confirm account closure.');
        }
    });
}

// NEW FUNCTIONALITY STARTS HERE

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadUserData();
    setupFormHandlers();
    initializeAccessibilityFeatures();
});

// Load user data from backend
async function loadUserData() {
    try {
        showLoading();
        const response = await fetch('../../backend/candidate/get_user_data.php');
        const result = await response.json();
        
        if (result.success) {
            userData = result.data;
            populateUserData();
        } else {
            showToast('Failed to load user data: ' + result.message, 'error');
        }
    } catch (error) {
        showToast('Error loading user data', 'error');
        console.error('Error:', error);
    } finally {
        hideLoading();
    }
}

// Populate forms with user data
function populateUserData() {
    if (!userData) return;
    
    const userInfo = userData.user_info;
    const settings = userData.settings;
    
    // Populate contact info form
    const nameField = document.getElementById('name');
    const emailField = document.getElementById('email');
    const phoneField = document.getElementById('phone');
    const addressField = document.getElementById('address');
    
    if (nameField && userInfo) {
        nameField.value = `${userInfo.first_name || ''} ${userInfo.middle_name || ''} ${userInfo.last_name || ''}`.trim();
    }
    if (emailField && userInfo) {
        emailField.value = userInfo.email || '';
    }
    if (phoneField && userInfo) {
        phoneField.value = userInfo.contact_number || '';
    }
    if (addressField && userInfo) {
        addressField.value = `${userInfo.city || ''}, ${userInfo.province || ''}`.replace(', ,', '').replace(/^,|,$/, '');
    }
    
    // Populate settings if they exist
    if (settings) {
        // Accessibility settings
        const highContrastToggle = document.getElementById('high-contrast-toggle');
        const textSizeSlider = document.getElementById('font-size-slider');
        const screenReaderToggle = document.getElementById('screen-reader-toggle');
        const keyboardNavToggle = document.getElementById('keyboard-nav-toggle');
        const motionReductionToggle = document.getElementById('motion-reduction-toggle');
        
        if (highContrastToggle) highContrastToggle.checked = settings.high_contrast == 1;
        if (screenReaderToggle) screenReaderToggle.checked = settings.screen_reader_support == 1;
        if (keyboardNavToggle) keyboardNavToggle.checked = settings.keyboard_navigation == 1;
        if (motionReductionToggle) motionReductionToggle.checked = settings.motion_reduction == 1;
        
        if (textSizeSlider) {
            const sizeMap = { 'small': 1, 'medium': 2, 'large': 3 };
            textSizeSlider.value = sizeMap[settings.text_size] || 2;
        }
        
        // Display settings
        const themeRadios = document.getElementsByName('theme');
        const fontSizeRadios = document.getElementsByName('font-size');
        
        themeRadios.forEach(radio => {
            if (radio.value === (settings.theme || 'light')) {
                radio.checked = true;
            }
        });
        
        fontSizeRadios.forEach(radio => {
            if (radio.value === (settings.font_size || 'medium')) {
                radio.checked = true;
            }
        });
        
        // Notification settings
        const emailNotifCheckbox = document.getElementById('email-notifications');
        const smsNotifCheckbox = document.getElementById('sms-notifications');
        const pushNotifCheckbox = document.getElementById('push-notifications');
        const jobAlertsCheckbox = document.getElementById('job-alerts');
        const appUpdatesCheckbox = document.getElementById('application-updates');
        const msgNotifCheckbox = document.getElementById('message-notifications');
        const marketingNotifCheckbox = document.getElementById('marketing-notifications');
        
        if (emailNotifCheckbox) emailNotifCheckbox.checked = settings.email_notifications == 1;
        if (smsNotifCheckbox) smsNotifCheckbox.checked = settings.sms_notifications == 1;
        if (pushNotifCheckbox) pushNotifCheckbox.checked = settings.push_notifications == 1;
        if (jobAlertsCheckbox) jobAlertsCheckbox.checked = settings.job_alerts == 1;
        if (appUpdatesCheckbox) appUpdatesCheckbox.checked = settings.application_updates == 1;
        if (msgNotifCheckbox) msgNotifCheckbox.checked = settings.message_notifications == 1;
        if (marketingNotifCheckbox) marketingNotifCheckbox.checked = settings.marketing_notifications == 1;
        
        // Privacy settings
        const profileVisibilitySelect = document.getElementById('profile-visibility');
        const peerVisibilityToggle = document.getElementById('peer-visibility-toggle');
        const searchListingCheckbox = document.getElementById('search-listing');
        const dataCollectionCheckbox = document.getElementById('data-collection');
        const thirdPartyCheckbox = document.getElementById('third-party-sharing');
        
        if (profileVisibilitySelect) profileVisibilitySelect.value = settings.profile_visibility || 'all';
        if (peerVisibilityToggle) peerVisibilityToggle.checked = settings.peer_visibility == 1;
        if (searchListingCheckbox) searchListingCheckbox.checked = settings.search_listing == 1;
        if (dataCollectionCheckbox) dataCollectionCheckbox.checked = settings.data_collection == 1;
        if (thirdPartyCheckbox) thirdPartyCheckbox.checked = settings.third_party_sharing == 1;
        
        // Application preferences
        const autoFillToggle = document.getElementById('auto-fill-toggle');
        const includeCoverLetterCheckbox = document.getElementById('include-cover-letter');
        const followCompaniesCheckbox = document.getElementById('follow-companies');
        const defaultCoverLetterText = document.getElementById('default-cover-letter-text');
        const saveHistoryCheckbox = document.getElementById('save-application-history');
        const receiveFeedbackCheckbox = document.getElementById('receive-application-feedback');
        
        if (autoFillToggle) autoFillToggle.checked = settings.auto_fill == 1;
        if (includeCoverLetterCheckbox) includeCoverLetterCheckbox.checked = settings.include_cover_letter == 1;
        if (followCompaniesCheckbox) followCompaniesCheckbox.checked = settings.follow_companies == 1;
        if (defaultCoverLetterText) defaultCoverLetterText.value = settings.default_cover_letter || '';
        if (saveHistoryCheckbox) saveHistoryCheckbox.checked = settings.save_application_history == 1;
        if (receiveFeedbackCheckbox) receiveFeedbackCheckbox.checked = settings.receive_application_feedback == 1;
        
        // Job alert settings
        const alertFrequencyRadios = document.getElementsByName('alert-frequency');
        const emailAlertsCheckbox = document.getElementById('email-alerts');
        const smsAlertsCheckbox = document.getElementById('sms-alerts');
        const appAlertsCheckbox = document.getElementById('app-alerts');
        const jobKeywordsField = document.getElementById('job-keywords');
        const jobLocationField = document.getElementById('job-location');
        
        alertFrequencyRadios.forEach(radio => {
            if (radio.value === (settings.alert_frequency || 'daily')) {
                radio.checked = true;
            }
        });
        
        if (emailAlertsCheckbox) emailAlertsCheckbox.checked = settings.email_alerts == 1;
        if (smsAlertsCheckbox) smsAlertsCheckbox.checked = settings.sms_alerts == 1;
        if (appAlertsCheckbox) appAlertsCheckbox.checked = settings.app_alerts == 1;
        if (jobKeywordsField) jobKeywordsField.value = settings.job_keywords || '';
        if (jobLocationField) jobLocationField.value = settings.job_location || '';
    }
}

// Setup form handlers
function setupFormHandlers() {
    // Contact Info Form
    const contactForm = document.getElementById('contact-info-form');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveContactInfo();
        });
    }
    
    // Password Form
    const passwordForm = document.getElementById('password-security-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await updatePassword();
        });
    }
    
    // Accessibility Form
    const accessibilityForm = document.getElementById('accessibility-form');
    if (accessibilityForm) {
        accessibilityForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('accessibility');
        });
    }
    
    // Display Form
    const displayForm = document.getElementById('display-form');
    if (displayForm) {
        displayForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('display');
        });
    }
    
    // Notification Settings Form
    const notificationForm = document.getElementById('notification-settings-form');
    if (notificationForm) {
        notificationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('notifications');
        });
    }
    
    // Privacy Form
    const privacyForm = document.getElementById('privacy-preferences-form');
    if (privacyForm) {
        privacyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('privacy');
        });
    }
    
    // Job Alert Form
    const jobAlertForm = document.getElementById('job-alert-form');
    if (jobAlertForm) {
        jobAlertForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('job_alerts');
        });
    }
    
    // Application Preferences Form
    const appPrefsForm = document.getElementById('application-prefs-form');
    if (appPrefsForm) {
        appPrefsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await saveSettings('application_prefs');
        });
    }
    
    // Support Form - Find the submit button in support section
    const supportSubmitBtn = document.querySelector('#support-help-detail .btn-primary');
    if (supportSubmitBtn) {
        supportSubmitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await submitSupportRequest();
        });
    }
    
    // Resume/Document Settings Form
    const resumeDocsForm = document.getElementById('resume-docs-form');
    if (resumeDocsForm) {
        resumeDocsForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            showToast('Document settings saved successfully', 'success');
            returnToSettingsMain();
        });
    }
}

// Save contact info
async function saveContactInfo() {
    const formData = new FormData();
    const fullName = document.getElementById('name').value.trim();
    const nameParts = fullName.split(' ');
    
    formData.append('first_name', nameParts[0] || '');
    formData.append('middle_name', nameParts.length > 2 ? nameParts.slice(1, -1).join(' ') : '');
    formData.append('last_name', nameParts[nameParts.length - 1] || '');
    formData.append('suffix', '');
    formData.append('email', document.getElementById('email').value);
    formData.append('contact_number', document.getElementById('phone').value);
    
    const address = document.getElementById('address').value.split(',');
    formData.append('city', address[0] ? address[0].trim() : '');
    formData.append('province', address[1] ? address[1].trim() : '');
    
    try {
        showLoading();
        const response = await fetch('../../backend/candidate/update_contact_info.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Contact information updated successfully', 'success');
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

// Save settings
async function saveSettings(settingType) {
    const formData = new FormData();
    formData.append('setting_type', settingType);
    
    // Collect form data based on setting type
    switch (settingType) {
        case 'accessibility':
            if (document.getElementById('high-contrast-toggle') && document.getElementById('high-contrast-toggle').checked) {
                formData.append('high_contrast', '1');
            }
            formData.append('text_size', getTextSizeFromSlider());
            if (document.getElementById('screen-reader-toggle') && document.getElementById('screen-reader-toggle').checked) {
                formData.append('screen_reader', '1');
            }
            if (document.getElementById('keyboard-nav-toggle') && document.getElementById('keyboard-nav-toggle').checked) {
                formData.append('keyboard_nav', '1');
            }
            if (document.getElementById('motion-reduction-toggle') && document.getElementById('motion-reduction-toggle').checked) {
                formData.append('motion_reduction', '1');
            }
            
            const assistiveTools = [];
            document.querySelectorAll('#assistive-tools option:checked').forEach(option => {
                assistiveTools.push(option.value);
            });
            formData.append('assistive_tools', JSON.stringify(assistiveTools));
            break;
            
        case 'display':
            const selectedTheme = document.querySelector('input[name="theme"]:checked');
            if (selectedTheme) formData.append('theme', selectedTheme.value);
            
            const selectedFontSize = document.querySelector('input[name="font-size"]:checked');
            if (selectedFontSize) formData.append('font_size', selectedFontSize.value);
            break;
            
        case 'notifications':
            const notifCheckboxes = ['email-notifications', 'sms-notifications', 'push-notifications', 
                                   'job-alerts', 'application-updates', 'message-notifications', 'marketing-notifications'];
            notifCheckboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox && checkbox.checked) {
                    formData.append(id.replace('-', '_'), '1');
                }
            });
            break;
            
        case 'privacy':
            const profileVisibility = document.getElementById('profile-visibility');
            if (profileVisibility) formData.append('profile_visibility', profileVisibility.value);
            
            const privacyCheckboxes = ['peer-visibility-toggle', 'search-listing', 'data-collection', 'third-party-sharing'];
            privacyCheckboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox && checkbox.checked) {
                    const fieldName = id.replace('-toggle', '').replace('-', '_');
                    formData.append(fieldName, '1');
                }
            });
            break;
            
        case 'job_alerts':
            const alertFreq = document.querySelector('input[name="alert-frequency"]:checked');
            if (alertFreq) formData.append('alert_frequency', alertFreq.value);
            
            const alertCheckboxes = ['email-alerts', 'sms-alerts', 'app-alerts'];
            alertCheckboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox && checkbox.checked) {
                    formData.append(id.replace('-', '_'), '1');
                }
            });
            
            const selectedCategories = [];
            document.querySelectorAll('.category-chip.selected').forEach(chip => {
                selectedCategories.push(chip.dataset.category);
            });
            formData.append('job_categories', JSON.stringify(selectedCategories));
            
            const keywords = document.getElementById('job-keywords');
            if (keywords) formData.append('job_keywords', keywords.value);
            
            const location = document.getElementById('job-location');
            if (location) formData.append('job_location', location.value);
            break;
            
        case 'application_prefs':
            if (document.getElementById('auto-fill-toggle') && document.getElementById('auto-fill-toggle').checked) {
                formData.append('auto_fill', '1');
            }
            
            const appPrefCheckboxes = ['include-cover-letter', 'follow-companies', 'save-application-history', 'receive-application-feedback'];
            appPrefCheckboxes.forEach(id => {
                const checkbox = document.getElementById(id);
                if (checkbox && checkbox.checked) {
                    formData.append(id.replace('-', '_'), '1');
                }
            });
            
            const coverLetter = document.getElementById('default-cover-letter-text');
            if (coverLetter) formData.append('default_cover_letter', coverLetter.value);
            break;
    }
    
    try {
        showLoading();
        const response = await fetch('../../backend/candidate/save_user_settings.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Settings saved successfully', 'success');
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

// Submit support request
async function submitSupportRequest() {
    const subjectField = document.getElementById('support-subject');
    const messageField = document.getElementById('support-message');
    
    if (!subjectField || !messageField) {
        showToast('Support form fields not found', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('subject', subjectField.value);
    formData.append('message', messageField.value);
    
    const priority = document.querySelector('input[name="support-priority"]:checked');
    if (priority) formData.append('priority', priority.value);
    
    const includeAccountInfo = document.getElementById('include-account-info');
    if (includeAccountInfo && includeAccountInfo.checked) {
        formData.append('include_account_info', '1');
    }
    
    try {
        showLoading();
        const response = await fetch('../../backend/candidate/submit_support_request.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Support request submitted successfully. Request ID: ' + result.request_id, 'success');
            // Clear form
            subjectField.value = '';
            messageField.value = '';
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

// Helper function to get text size from slider
function getTextSizeFromSlider() {
    const slider = document.getElementById('font-size-slider');
    if (!slider) return 'medium';
    
    const value = slider.value;
    switch (value) {
        case '1': return 'small';
        case '2': return 'medium';
        case '3': return 'large';
        default: return 'medium';
    }
}

// Category chips toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const categoryChips = document.querySelectorAll('.category-chip');
    categoryChips.forEach(chip => {
        chip.addEventListener('click', () => {
            chip.classList.toggle('selected');
        });
    });
    
    // FAQ Toggle Functionality
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
});

// Toggle switches with corresponding settings
const highContrastToggle = document.getElementById('high-contrast-toggle');
if (highContrastToggle) {
    highContrastToggle.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.style.setProperty('--primary', '#000000');
            document.documentElement.style.setProperty('--text-primary', '#ffffff');
            document.documentElement.style.setProperty('--bg-color', '#000000');
        } else {
            document.documentElement.style.setProperty('--primary', '#257180');
            document.documentElement.style.setProperty('--text-primary', '#333333');
            document.documentElement.style.setProperty('--bg-color', '#ffffff');
        }
    });
}

// Font size slider functionality
const fontSizeSlider = document.getElementById('font-size-slider');
if (fontSizeSlider) {
    fontSizeSlider.addEventListener('input', function() {
        let fontSize;
        switch(this.value) {
            case '1':
                fontSize = '14px';
                break;
            case '2':
                fontSize = '16px';
                break;
            case '3':
                fontSize = '18px';
                break;
            default:
                fontSize = '16px';
        }
        document.body.style.fontSize = fontSize;
    });
}

// Document upload handling
const resumeUpload = document.getElementById('resume-upload');
if (resumeUpload) {
    resumeUpload.addEventListener('change', function() {
        if (this.files.length > 0) {
            showToast(`File "${this.files[0].name}" ready for upload!`, 'info');
            // You can implement actual file upload here later
        }
    });
}

const coverLetterUpload = document.getElementById('cover-letter-upload');
if (coverLetterUpload) {
    coverLetterUpload.addEventListener('change', function() {
        if (this.files.length > 0) {
            showToast(`File "${this.files[0].name}" ready for upload!`, 'info');
            // You can implement actual file upload here later
        }
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

// Initialize accessibility features
function initializeAccessibilityFeatures() {
    // Add event listener to accessibility toggle
    const accessibilityToggle = document.querySelector('.accessibility-toggle');
    if (accessibilityToggle) {
        accessibilityToggle.addEventListener('click', function() {
            const panel = document.querySelector('.accessibility-panel');
            if (panel) {
                if (panel.style.display === 'block') {
                    panel.style.display = 'none';
                } else {
                    panel.style.display = 'block';
                }
            }
        });
        
        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            const panel = document.querySelector('.accessibility-panel');
            if (panel && !e.target.closest('.accessibility-panel') && !e.target.closest('.accessibility-toggle') && panel.style.display === 'block') {
                panel.style.display = 'none';
            }
        });
    }
    
    // High contrast mode
    const highContrastToggle = document.getElementById('high-contrast');
    if (highContrastToggle) {
        if (localStorage.getItem('highContrast') === 'true') {
            document.body.classList.add('high-contrast');
            highContrastToggle.checked = true;
        }
        
        highContrastToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('high-contrast');
                localStorage.setItem('highContrast', 'true');
            } else {
                document.body.classList.remove('high-contrast');
                localStorage.setItem('highContrast', 'false');
            }
        });
    }
    
    // Reduce motion
    const reduceMotionToggle = document.getElementById('reduce-motion');
    if (reduceMotionToggle) {
        if (localStorage.getItem('reduceMotion') === 'true') {
            document.body.classList.add('reduce-motion');
            reduceMotionToggle.checked = true;
        }
        
        reduceMotionToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('reduce-motion');
                localStorage.setItem('reduceMotion', 'true');
            } else {
                document.body.classList.remove('reduce-motion');
                localStorage.setItem('reduceMotion', 'false');
            }
        });
    }
}

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

    // FAQ and Resource Toggle Functionality
document.querySelectorAll('.faq-item').forEach(item => {
    item.addEventListener('click', function() {
        const faqId = this.getAttribute('data-faq');
        const answer = document.getElementById('faq-' + faqId);
        const arrow = this.querySelector('.setting-arrow i');
        
        if (answer.style.display === 'none' || answer.style.display === '') {
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
        } else {
            // Close this answer
            answer.style.display = 'none';
            arrow.classList.remove('fa-chevron-down');
            arrow.classList.add('fa-chevron-right');
        }
    });
});

// Resource Items Toggle
document.querySelectorAll('.resource-item').forEach(item => {
    item.addEventListener('click', function() {
        const resourceId = this.getAttribute('data-resource');
        const content = document.getElementById('resource-' + resourceId);
        const arrow = this.querySelector('.setting-arrow i');
        
        if (content.style.display === 'none' || content.style.display === '') {
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
        } else {
            // Close this content
            content.style.display = 'none';
            arrow.classList.remove('fa-chevron-down');
            arrow.classList.add('fa-chevron-right');
        }
    });
});
}