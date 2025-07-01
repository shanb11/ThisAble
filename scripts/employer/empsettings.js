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

// Global data storage
let companyValuesData = [];
let feedbackTemplatesData = [];

// Create a loader element if it doesn't exist
if (!document.getElementById('loader')) {
    const loader = document.createElement('div');
    loader.id = 'loader';
    loader.className = 'loader';
    loader.innerHTML = '<div class="loader-spinner"></div>';
    loader.style.position = 'fixed';
    loader.style.top = '0';
    loader.style.left = '0';
    loader.style.width = '100%';
    loader.style.height = '100%';
    loader.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    loader.style.display = 'flex';
    loader.style.justifyContent = 'center';
    loader.style.alignItems = 'center';
    loader.style.zIndex = '2000';
    loader.style.display = 'none';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.style.border = '5px solid #f3f3f3';
    spinner.style.borderTop = '5px solid var(--primary)';
    spinner.style.borderRadius = '50%';
    spinner.style.width = '50px';
    spinner.style.height = '50px';
    spinner.style.animation = 'spin 1s linear infinite';
    
    const style = document.createElement('style');
    style.innerHTML = '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
    
    document.head.appendChild(style);
    loader.appendChild(spinner);
    document.body.appendChild(loader);
}

// ========== DATA LOADING FUNCTIONS ==========
async function loadCompanyProfileData() {
    try {
        showLoader();
        
        const response = await fetch('../../backend/employer/get_company_profile.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Populate industries dropdown first
            if (data.industries) {
                populateIndustriesDropdown(data.industries);
            }
            
            // Then populate all form fields with real data
            populateContactInfoForm(data.data.contact_person);
            populateCompanyInfoForm(data.data.company_identity, data.data.company_description);
            populateSocialLinksForm(data.data.social_links);
            populateHiringPreferencesForm(data.data.hiring_preferences);
            
            console.log('Profile data loaded successfully');
        } else {
            console.error('Failed to load profile data:', data.message);
            showToast('Failed to load profile data: ' + data.message);
        }
        
    } catch (error) {
        console.error('Error loading profile data:', error);
        showToast('Error loading profile data. Please refresh the page.');
    } finally {
        hideLoader();
    }
}

// ========== POPULATE INDUSTRIES DROPDOWN ==========
function populateIndustriesDropdown(industries) {
    const industrySelect = document.getElementById('company-industry');
    if (industrySelect && industries) {
        // Clear existing options except the first one
        industrySelect.innerHTML = '<option value="">Select Industry</option>';
        
        // Add industries from database
        industries.forEach(industry => {
            const option = document.createElement('option');
            option.value = industry.industry_id;
            option.textContent = industry.industry_name;
            industrySelect.appendChild(option);
        });
        
        // Add "Others" option
        const othersOption = document.createElement('option');
        othersOption.value = 'others';
        othersOption.textContent = 'Others';
        industrySelect.appendChild(othersOption);
    }
}

// Helper functions to populate forms
function populateContactInfoForm(contactData) {
    if (contactData) {
        const repName = document.getElementById('rep-name');
        const position = document.getElementById('position');
        const email = document.getElementById('email');
        const phone = document.getElementById('phone');
        
        if (repName) repName.value = `${contactData.first_name || ''} ${contactData.last_name || ''}`.trim();
        if (position) position.value = contactData.position || '';
        if (email) email.value = contactData.email || '';
        if (phone) phone.value = contactData.contact_number || '';
    }
}

function populateCompanyInfoForm(companyData, descriptionData) {
    if (companyData) {
        const companyName = document.getElementById('company-name');
        const companyIndustry = document.getElementById('company-industry');
        const companyDescription = document.getElementById('company-description');
        const companyWebsite = document.getElementById('company-website');
        const companySize = document.getElementById('company-size');
        
        if (companyName) companyName.value = companyData.company_name || '';
        if (companyWebsite) companyWebsite.value = companyData.company_website || '';
        if (companySize) companySize.value = companyData.company_size || '';
        
        // Set industry after dropdown is populated
        if (companyIndustry && companyData.industry_id) {
            // Use setTimeout to ensure dropdown is populated first
            setTimeout(() => {
                companyIndustry.value = companyData.industry_id;
            }, 100);
        }
    }
    
    if (descriptionData) {
        const companyDescription = document.getElementById('company-description');
        if (companyDescription) companyDescription.value = descriptionData.company_description || '';
    }
}

function populateSocialLinksForm(socialData) {
    if (socialData) {
        const inputs = document.querySelectorAll('#company-info-detail input[type="url"]');
        
        inputs.forEach(input => {
            const placeholder = input.placeholder.toLowerCase();
            if (placeholder.includes('linkedin') && socialData.linkedin_url) {
                input.value = socialData.linkedin_url;
            } else if (placeholder.includes('twitter') && socialData.twitter_url) {
                input.value = socialData.twitter_url;
            } else if (placeholder.includes('facebook') && socialData.facebook_url) {
                input.value = socialData.facebook_url;
            }
        });
    }
}

function populateHiringPreferencesForm(hiringData) {
    if (hiringData) {
        const openToPwd = document.getElementById('auto-tag-pwd');
        if (openToPwd) openToPwd.checked = hiringData.open_to_pwd || false;
        
        // Populate disability types checkboxes
        if (hiringData.disability_types && Array.isArray(hiringData.disability_types)) {
            hiringData.disability_types.forEach(type => {
                const checkbox = document.querySelector(`input[value="${type}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
        
        // Populate accessibility options checkboxes
        if (hiringData.accessibility_options && Array.isArray(hiringData.accessibility_options)) {
            hiringData.accessibility_options.forEach(option => {
                const checkbox = document.querySelector(`input[value="${option}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }
    }
}

// ========== ACCOUNT TYPE & SECURITY FUNCTIONS ==========
async function checkAccountType() {
    try {
        const response = await fetch('../../backend/employer/get_account_type.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            handleAccountTypeUI(data.data);
            return data.data;
        } else {
            console.error('Failed to check account type:', data.message);
            return null;
        }
        
    } catch (error) {
        console.error('Error checking account type:', error);
        return null;
    }
}

function handleAccountTypeUI(accountData) {
    const passwordForm = document.getElementById('password-security-form');
    if (!passwordForm) return;
    
    if (accountData.is_google_account) {
        // Hide password change fields
        const passwordFields = passwordForm.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            const formGroup = field.closest('.form-group');
            if (formGroup) {
                formGroup.style.display = 'none';
            }
        });
        
        // Hide password update button
        const submitButton = passwordForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.style.display = 'none';
        }
        
        // Add Google account info message
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
        
        // Show only security preferences
        const securitySection = passwordForm.querySelector('.form-group');
        if (securitySection) {
            const twoFactorGroup = Array.from(passwordForm.querySelectorAll('.form-group')).find(group => 
                group.textContent.includes('Two-Factor') || group.textContent.includes('Login Session')
            );
            if (twoFactorGroup) {
                twoFactorGroup.style.display = 'block';
            }
        }
        
    } else {
        // Regular account - show all fields
        const allFields = passwordForm.querySelectorAll('.form-group');
        allFields.forEach(group => {
            group.style.display = 'block';
        });
    }
}

// ========== NOTIFICATION SETTINGS FUNCTIONS ==========
async function loadNotificationSettings() {
    try {
        const response = await fetch('../../backend/employer/get_notification_settings.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            populateNotificationSettingsForm(data.data);
            return data.data;
        } else {
            console.log('Using default notification settings');
            return null;
        }
        
    } catch (error) {
        console.error('Error loading notification settings:', error);
        return null;
    }
}

function populateNotificationSettingsForm(notificationData) {
    if (!notificationData) return;
    
    // Notification methods
    const emailNotifications = document.getElementById('email-notifications');
    const smsNotifications = document.getElementById('sms-notifications');
    const pushNotifications = document.getElementById('push-notifications');
    
    if (emailNotifications) emailNotifications.checked = notificationData.email_notifications || false;
    if (smsNotifications) smsNotifications.checked = notificationData.sms_notifications || false;
    if (pushNotifications) pushNotifications.checked = notificationData.push_notifications || false;
    
    // Notification categories
    const newApplications = document.getElementById('new-applications');
    const applicationStatus = document.getElementById('application-status');
    const messageNotifications = document.getElementById('message-notifications');
    const systemUpdates = document.getElementById('system-updates');
    const marketingNotifications = document.getElementById('marketing-notifications');
    
    if (newApplications) newApplications.checked = notificationData.new_applications || false;
    if (applicationStatus) applicationStatus.checked = notificationData.application_status || false;
    if (messageNotifications) messageNotifications.checked = notificationData.message_notifications || false;
    if (systemUpdates) systemUpdates.checked = notificationData.system_updates || false;
    if (marketingNotifications) marketingNotifications.checked = notificationData.marketing_notifications || false;
    
    // Email frequency
    const emailFrequency = document.getElementById('email-frequency');
    if (emailFrequency) emailFrequency.value = notificationData.email_frequency || 'immediate';
    
    // Quiet hours
    const enableQuietHours = document.getElementById('enable-quiet-hours');
    const quietFrom = document.getElementById('quiet-from');
    const quietTo = document.getElementById('quiet-to');
    
    if (enableQuietHours) enableQuietHours.checked = notificationData.enable_quiet_hours || false;
    if (quietFrom) quietFrom.value = notificationData.quiet_from ? notificationData.quiet_from.substring(0, 5) : '22:00';
    if (quietTo) quietTo.value = notificationData.quiet_to ? notificationData.quiet_to.substring(0, 5) : '08:00';
}

// ========== PRIVACY SETTINGS FUNCTIONS ==========
async function loadPrivacySettings() {
    try {
        const response = await fetch('../../backend/employer/get_privacy_settings.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            populatePrivacySettingsForm(data.data);
            return data.data;
        } else {
            console.log('Using default privacy settings');
            return null;
        }
        
    } catch (error) {
        console.error('Error loading privacy settings:', error);
        return null;
    }
}

function populatePrivacySettingsForm(privacyData) {
    if (!privacyData) return;
    
    // Profile visibility
    const profileVisibility = document.getElementById('profile-visibility');
    if (profileVisibility) profileVisibility.checked = privacyData.profile_visibility || false;
    
    // Information sharing
    const shareCompanyInfo = document.getElementById('share-company-info');
    const shareContactInfo = document.getElementById('share-contact-info');
    
    if (shareCompanyInfo) shareCompanyInfo.checked = privacyData.share_company_info || false;
    if (shareContactInfo) shareContactInfo.checked = privacyData.share_contact_info || false;
    
    // Job visibility
    const jobVisibility = document.getElementById('job-visibility');
    if (jobVisibility) jobVisibility.value = privacyData.job_visibility || 'public';
    
    // Data collection
    const allowDataCollection = document.getElementById('allow-data-collection');
    const allowMarketing = document.getElementById('allow-marketing');
    const allowThirdParty = document.getElementById('allow-third-party');
    
    if (allowDataCollection) allowDataCollection.checked = privacyData.allow_data_collection || false;
    if (allowMarketing) allowMarketing.checked = privacyData.allow_marketing || false;
    if (allowThirdParty) allowThirdParty.checked = privacyData.allow_third_party || false;
}

// ========== DISPLAY SETTINGS FUNCTIONS ==========
async function loadDisplaySettings() {
    try {
        const response = await fetch('../../backend/employer/get_display_settings.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            populateDisplaySettingsForm(data.data);
            return data.data;
        } else {
            console.log('Using default display settings');
            return null;
        }
        
    } catch (error) {
        console.error('Error loading display settings:', error);
        return null;
    }
}

function populateDisplaySettingsForm(displayData) {
    if (!displayData) return;
    
    // Theme selection
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.checked = radio.value === displayData.theme;
    });
    
    // Font size selection
    const fontSizeRadios = document.querySelectorAll('input[name="font-size"]');
    fontSizeRadios.forEach(radio => {
        radio.checked = radio.value === displayData.font_size;
    });
    
    // Color scheme
    const colorScheme = document.getElementById('color-scheme');
    if (colorScheme) colorScheme.value = displayData.color_scheme || 'default';
    
    // Accessibility features
    const highContrast = document.getElementById('high-contrast');
    const reduceMotion = document.getElementById('reduce-motion');
    const screenReaderSupport = document.getElementById('screen-reader-support');
    
    if (highContrast) highContrast.checked = displayData.high_contrast || false;
    if (reduceMotion) reduceMotion.checked = displayData.reduce_motion || false;
    if (screenReaderSupport) screenReaderSupport.checked = displayData.screen_reader_support || false;
    
    // Default view
    const defaultView = document.getElementById('default-view');
    if (defaultView) defaultView.value = displayData.default_view || 'dashboard';
}

// ========== COMPANY VALUES MANAGEMENT ==========
async function loadCompanyValues() {
    try {
        const response = await fetch('../../backend/employer/company_values.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            companyValuesData = data.data;
            populateCompanyValuesDisplay();
            return data.data;
        } else {
            console.log('No company values found');
            return [];
        }
        
    } catch (error) {
        console.error('Error loading company values:', error);
        return [];
    }
}

function populateCompanyValuesDisplay() {
    const valuesContainer = document.getElementById('values-container');
    if (!valuesContainer) return;
    
    valuesContainer.innerHTML = '';
    
    companyValuesData.forEach((value, index) => {
        const valueElement = document.createElement('div');
        valueElement.className = 'value-item';
        valueElement.dataset.id = value.value_id;
        
        valueElement.innerHTML = `
            <div class="value-item-number">${index + 1}</div>
            <div class="value-item-content">
                <div class="value-item-title">${value.value_title}</div>
                <div class="value-item-description">${value.value_description}</div>
            </div>
            <div class="value-item-actions">
                <button type="button" class="edit-value" data-id="${value.value_id}"><i class="fas fa-pen"></i></button>
                <button type="button" class="delete-value" data-id="${value.value_id}"><i class="fas fa-trash"></i></button>
            </div>
        `;
        
        valuesContainer.appendChild(valueElement);
    });
    
    setupCompanyValueButtons();
}

function setupCompanyValueButtons() {
    // Edit buttons
    document.querySelectorAll('.edit-value').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const value = companyValuesData.find(v => v.value_id == id);
            
            if (value) {
                document.getElementById('edit-value-title').value = value.value_title;
                document.getElementById('edit-value-description').value = value.value_description;
                document.getElementById('edit-value-id').value = id;
                
                openModal('edit-value-modal');
            }
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-value').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const value = companyValuesData.find(v => v.value_id == id);
            
            if (value) {
                document.getElementById('delete-value-title').textContent = value.value_title;
                document.getElementById('delete-value-id').value = id;
                
                openModal('delete-value-modal');
            }
        });
    });
}

// ========== FEEDBACK TEMPLATES MANAGEMENT ==========
async function loadFeedbackTemplates() {
    try {
        const response = await fetch('../../backend/employer/feedback_templates.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            feedbackTemplatesData = data.data;
            populateFeedbackTemplatesDisplay();
            return data.data;
        } else {
            console.log('No feedback templates found');
            return [];
        }
        
    } catch (error) {
        console.error('Error loading feedback templates:', error);
        return [];
    }
}

function populateFeedbackTemplatesDisplay() {
    const templatesContainer = document.getElementById('feedback-templates-container');
    if (!templatesContainer) return;
    
    templatesContainer.innerHTML = '';
    
    feedbackTemplatesData.forEach(template => {
        const templateElement = document.createElement('div');
        templateElement.className = 'feedback-template';
        templateElement.dataset.id = template.template_id;
        
        templateElement.innerHTML = `
            <div class="feedback-template-header">
                <div class="feedback-template-title">${template.template_title}</div>
                <div class="feedback-template-actions">
                    <button type="button" class="edit-template" data-id="${template.template_id}"><i class="fas fa-pen"></i></button>
                    <button type="button" class="delete-template" data-id="${template.template_id}"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="feedback-template-content">${template.template_content}</div>
        `;
        
        templatesContainer.appendChild(templateElement);
    });
    
    setupFeedbackTemplateButtons();
}

function setupFeedbackTemplateButtons() {
    // Edit buttons
    document.querySelectorAll('.edit-template').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const template = feedbackTemplatesData.find(t => t.template_id == id);
            
            if (template) {
                document.getElementById('edit-template-title').value = template.template_title;
                document.getElementById('edit-template-content').value = template.template_content;
                document.getElementById('edit-template-id').value = id;
                
                openModal('edit-template-modal');
            }
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-template').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const template = feedbackTemplatesData.find(t => t.template_id == id);
            
            if (template) {
                document.getElementById('delete-template-title').textContent = template.template_title;
                document.getElementById('delete-template-id').value = id;
                
                openModal('delete-template-modal');
            }
        });
    });
}

// ========== FORM SUBMISSION HANDLERS ==========
async function submitContactInfoForm() {
    try {
        const repName = document.getElementById('rep-name')?.value || '';
        const nameParts = repName.trim().split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        
        const requestData = {
            first_name: firstName,
            last_name: lastName,
            position: document.getElementById('position')?.value || '',
            contact_number: document.getElementById('phone')?.value || '',
            email: document.getElementById('email')?.value || ''
        };
        
        // Validation
        if (!requestData.first_name) {
            throw new Error('First name is required');
        }
        if (!requestData.email) {
            throw new Error('Email is required');
        }
        
        const response = await fetch('../../backend/employer/update_contact_info.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Contact information updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update contact information');
        }
        
    } catch (error) {
        console.error('Error updating contact info:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitCompanyInfoForm() {
    try {
        const companyName = document.getElementById('company-name')?.value || '';
        const companyIndustry = document.getElementById('company-industry')?.value || '';
        const companyDescription = document.getElementById('company-description')?.value || '';
        const companyWebsite = document.getElementById('company-website')?.value || '';
        const companyAddress = document.getElementById('address')?.value || 'Default Address';
        
        // Validation
        if (!companyName) {
            throw new Error('Company name is required');
        }
        if (!companyIndustry) {
            throw new Error('Please select an industry');
        }
        
        const requestData = {
            company_name: companyName,
            industry_id: companyIndustry,
            company_address: companyAddress,
            custom_industry: ''
        };
        
        const response = await fetch('../../backend/employer/update_company_identity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Company information updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update company information');
        }
        
    } catch (error) {
        console.error('Error updating company info:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitSocialLinksForm() {
    try {
        const inputs = document.querySelectorAll('#company-info-detail input[type="url"]');
        const requestData = {
            website_url: '',
            facebook_url: '',
            linkedin_url: '',
            twitter_url: '',
            instagram_url: ''
        };
        
        inputs.forEach(input => {
            const placeholder = input.placeholder.toLowerCase();
            if (placeholder.includes('linkedin')) {
                requestData.linkedin_url = input.value;
            } else if (placeholder.includes('twitter')) {
                requestData.twitter_url = input.value;
            } else if (placeholder.includes('facebook')) {
                requestData.facebook_url = input.value;
            }
        });
        
        const response = await fetch('../../backend/employer/update_social_links.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Social media links updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update social media links');
        }
        
    } catch (error) {
        console.error('Error updating social links:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitHiringPreferencesForm() {
    try {
        const openToPwd = document.getElementById('auto-tag-pwd')?.checked || false;
        
        // Collect disability types
        const disabilityTypes = [];
        document.querySelectorAll('input[name="disability-types"]:checked').forEach(checkbox => {
            disabilityTypes.push(checkbox.value);
        });
        
        // Collect accessibility options
        const accessibilityOptions = [];
        document.querySelectorAll('input[name="accessibility-options"]:checked').forEach(checkbox => {
            accessibilityOptions.push(checkbox.value);
        });
        
        const requestData = {
            open_to_pwd: openToPwd,
            disability_types: disabilityTypes,
            accessibility_options: accessibilityOptions,
            additional_accommodations: ''
        };
        
        const response = await fetch('../../backend/employer/update_hiring_preferences.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Hiring preferences updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update hiring preferences');
        }
        
    } catch (error) {
        console.error('Error updating hiring preferences:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitPasswordForm() {
    try {
        // Check account type first
        const accountData = await checkAccountType();
        
        if (accountData && accountData.is_google_account) {
            showToast('Password cannot be changed for Google accounts.');
            return false;
        }
        
        // Get form values
        const currentPassword = document.getElementById('current-password')?.value || '';
        const newPassword = document.getElementById('new-password')?.value || '';
        const confirmPassword = document.getElementById('confirm-password')?.value || '';
        const twoFactor = document.getElementById('two-factor')?.checked || false;
        const rememberLogin = document.getElementById('remember-login')?.checked || false;
        
        // Client-side validation
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
            
            // Clear password fields
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

async function submitNotificationSettingsForm() {
    try {
        // Get form values
        const emailNotifications = document.getElementById('email-notifications')?.checked || false;
        const smsNotifications = document.getElementById('sms-notifications')?.checked || false;
        const pushNotifications = document.getElementById('push-notifications')?.checked || false;
        const newApplications = document.getElementById('new-applications')?.checked || false;
        const applicationStatus = document.getElementById('application-status')?.checked || false;
        const messageNotifications = document.getElementById('message-notifications')?.checked || false;
        const systemUpdates = document.getElementById('system-updates')?.checked || false;
        const marketingNotifications = document.getElementById('marketing-notifications')?.checked || false;
        const emailFrequency = document.getElementById('email-frequency')?.value || 'immediate';
        const enableQuietHours = document.getElementById('enable-quiet-hours')?.checked || false;
        const quietFrom = document.getElementById('quiet-from')?.value || '22:00';
        const quietTo = document.getElementById('quiet-to')?.value || '08:00';
        
        const requestData = {
            email_notifications: emailNotifications,
            sms_notifications: smsNotifications,
            push_notifications: pushNotifications,
            new_applications: newApplications,
            application_status: applicationStatus,
            message_notifications: messageNotifications,
            system_updates: systemUpdates,
            marketing_notifications: marketingNotifications,
            email_frequency: emailFrequency,
            enable_quiet_hours: enableQuietHours,
            quiet_from: quietFrom,
            quiet_to: quietTo
        };
        
        const response = await fetch('../../backend/employer/update_notification_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Notification settings updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update notification settings');
        }
        
    } catch (error) {
        console.error('Error updating notification settings:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitPrivacySettingsForm() {
    try {
        // Get form values
        const profileVisibility = document.getElementById('profile-visibility')?.checked || false;
        const shareCompanyInfo = document.getElementById('share-company-info')?.checked || false;
        const shareContactInfo = document.getElementById('share-contact-info')?.checked || false;
        const jobVisibility = document.getElementById('job-visibility')?.value || 'public';
        const allowDataCollection = document.getElementById('allow-data-collection')?.checked || false;
        const allowMarketing = document.getElementById('allow-marketing')?.checked || false;
        const allowThirdParty = document.getElementById('allow-third-party')?.checked || false;
        
        const requestData = {
            profile_visibility: profileVisibility,
            share_company_info: shareCompanyInfo,
            share_contact_info: shareContactInfo,
            job_visibility: jobVisibility,
            allow_data_collection: allowDataCollection,
            allow_marketing: allowMarketing,
            allow_third_party: allowThirdParty
        };
        
        const response = await fetch('../../backend/employer/update_privacy_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Privacy settings updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update privacy settings');
        }
        
    } catch (error) {
        console.error('Error updating privacy settings:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitDisplaySettingsForm() {
    try {
        // Get theme selection
        const selectedTheme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
        
        // Get font size selection
        const selectedFontSize = document.querySelector('input[name="font-size"]:checked')?.value || 'medium';
        
        // Get other form values
        const colorScheme = document.getElementById('color-scheme')?.value || 'default';
        const highContrast = document.getElementById('high-contrast')?.checked || false;
        const reduceMotion = document.getElementById('reduce-motion')?.checked || false;
        const screenReaderSupport = document.getElementById('screen-reader-support')?.checked || false;
        const defaultView = document.getElementById('default-view')?.value || 'dashboard';
        
        const requestData = {
            theme: selectedTheme,
            font_size: selectedFontSize,
            color_scheme: colorScheme,
            high_contrast: highContrast,
            reduce_motion: reduceMotion,
            screen_reader_support: screenReaderSupport,
            default_view: defaultView
        };
        
        const response = await fetch('../../backend/employer/update_display_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Display settings updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update display settings');
        }
        
    } catch (error) {
        console.error('Error updating display settings:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function submitAnalyticsSettingsForm() {
    try {
        // Get form values
        const enableAnalytics = document.getElementById('enable-analytics')?.checked || false;
        const trackTimeToHire = document.getElementById('track-time-to-hire')?.checked || false;
        const trackCostPerHire = document.getElementById('track-cost-per-hire')?.checked || false;
        const trackApplicationCompletion = document.getElementById('track-application-completion')?.checked || false;
        const trackDiversity = document.getElementById('track-diversity')?.checked || false;
        const trackSourceEffectiveness = document.getElementById('track-source-effectiveness')?.checked || false;
        const weeklyReport = document.getElementById('weekly-report')?.checked || false;
        const monthlyReport = document.getElementById('monthly-report')?.checked || false;
        const quarterlyReport = document.getElementById('quarterly-report')?.checked || false;
        const dataRetention = document.getElementById('data-retention')?.value || '1-year';
        const integrateGoogleAnalytics = document.getElementById('integrate-google-analytics')?.checked || false;
        const integrateHrSystem = document.getElementById('integrate-hr-system')?.checked || false;
        
        const requestData = {
            enable_analytics: enableAnalytics,
            track_time_to_hire: trackTimeToHire,
            track_cost_per_hire: trackCostPerHire,
            track_application_completion: trackApplicationCompletion,
            track_diversity: trackDiversity,
            track_source_effectiveness: trackSourceEffectiveness,
            weekly_report: weeklyReport,
            monthly_report: monthlyReport,
            quarterly_report: quarterlyReport,
            data_retention: dataRetention,
            integrate_google_analytics: integrateGoogleAnalytics,
            integrate_hr_system: integrateHrSystem
        };
        
        const response = await fetch('../../backend/employer/update_analytics_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Analytics settings updated successfully!');
            return true;
        } else {
            throw new Error(data.message || 'Failed to update analytics settings');
        }
        
    } catch (error) {
        console.error('Error updating analytics settings:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

// ========== COMPANY VALUES CRUD OPERATIONS ==========
async function createCompanyValue(title, description) {
    try {
        const response = await fetch('../../backend/employer/company_values.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: title,
                description: description
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Company value added successfully!');
            await loadCompanyValues(); // Reload values
            return true;
        } else {
            throw new Error(data.message || 'Failed to create company value');
        }
        
    } catch (error) {
        console.error('Error creating company value:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function updateCompanyValue(valueId, title, description) {
    try {
        const response = await fetch('../../backend/employer/company_values.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                value_id: valueId,
                title: title,
                description: description
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Company value updated successfully!');
            await loadCompanyValues(); // Reload values
            return true;
        } else {
            throw new Error(data.message || 'Failed to update company value');
        }
        
    } catch (error) {
        console.error('Error updating company value:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function deleteCompanyValue(valueId) {
    try {
        const response = await fetch('../../backend/employer/company_values.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                value_id: valueId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Company value deleted successfully!');
            await loadCompanyValues(); // Reload values
            return true;
        } else {
            throw new Error(data.message || 'Failed to delete company value');
        }
        
    } catch (error) {
        console.error('Error deleting company value:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

// ========== FEEDBACK TEMPLATES CRUD OPERATIONS ==========
async function createFeedbackTemplate(title, content, type = 'rejection') {
    try {
        const response = await fetch('../../backend/employer/feedback_templates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                title: title,
                content: content,
                type: type
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Feedback template added successfully!');
            await loadFeedbackTemplates(); // Reload templates
            return true;
        } else {
            throw new Error(data.message || 'Failed to create feedback template');
        }
        
    } catch (error) {
        console.error('Error creating feedback template:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function updateFeedbackTemplate(templateId, title, content) {
    try {
        const response = await fetch('../../backend/employer/feedback_templates.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                template_id: templateId,
                title: title,
                content: content
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Feedback template updated successfully!');
            await loadFeedbackTemplates(); // Reload templates
            return true;
        } else {
            throw new Error(data.message || 'Failed to update feedback template');
        }
        
    } catch (error) {
        console.error('Error updating feedback template:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function deleteFeedbackTemplate(templateId) {
    try {
        const response = await fetch('../../backend/employer/feedback_templates.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                template_id: templateId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Feedback template deleted successfully!');
            await loadFeedbackTemplates(); // Reload templates
            return true;
        } else {
            throw new Error(data.message || 'Failed to delete feedback template');
        }
        
    } catch (error) {
        console.error('Error deleting feedback template:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

// Toggle sidebar
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
        } else {
            console.warn(`Detail container for ${settingId} not found`);
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

// Form submission with real backend integration (COMPLETE)
forms.forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Show loader
        showLoader();
        
        let success = false;
        
        try {
            // Determine which form is being submitted and call appropriate handler
            if (form.id === 'contact-info-form') {
                success = await submitContactInfoForm();
            } else if (form.id === 'company-info-form') {
                success = await submitCompanyInfoForm();
            } else if (form.closest('#company-info-detail')) {
                success = await submitSocialLinksForm();
            } else if (form.id === 'applicant-management-form') {
                success = await submitHiringPreferencesForm();
            } else if (form.id === 'password-security-form') {
                success = await submitPasswordForm();
            } else if (form.id === 'notification-settings-form') {
                success = await submitNotificationSettingsForm();
            } else if (form.id === 'privacy-preferences-form') {
                success = await submitPrivacySettingsForm();
            } else if (form.id === 'display-form') {
                success = await submitDisplaySettingsForm();
            } else if (form.id === 'company-values-form') {
                // Company values are handled by modals, not direct form submission
                success = true;
                showToast('Company values updated successfully!');
            } else if (form.id === 'feedback-settings-form') {
                // Feedback templates are handled by modals, not direct form submission  
                success = true;
                showToast('Feedback settings updated successfully!');
            } else if (form.id === 'analytics-form') {
                success = await submitAnalyticsSettingsForm();
            } else {
                // For other forms, use dummy functionality for now
                await new Promise(resolve => setTimeout(resolve, 800));
                success = true;
                showToast('Settings updated successfully!');
            }
            
            if (success) {
                // Go back to main menu after delay
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

// Show/hide loader
function showLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'none';
    }
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
signOutBtn.addEventListener('click', () => {
    openModal('sign-out-modal');
});

cancelSignOut.addEventListener('click', () => {
    closeModal('sign-out-modal');
});

confirmSignOut.addEventListener('click', () => {
    showLoader();
    
    setTimeout(() => {
        hideLoader();
        showToast('Signed out successfully.');
        closeModal('sign-out-modal');
        
        // Redirect to login page
        setTimeout(() => {
            window.location.href = '../employer/emplogin.php';
        }, 1000);
    }, 800);
});

// Close Account functionality
closeAccountBtn.addEventListener('click', () => {
    openModal('close-account-modal');
});

cancelCloseAccount.addEventListener('click', () => {
    closeModal('close-account-modal');
});

confirmCloseAccount.addEventListener('click', () => {
    const password = document.getElementById('confirm-password').value;
    
    if (!password) {
        alert('Please enter your password to confirm account closure.');
        return;
    }
    
    showLoader();
    
    setTimeout(() => {
        hideLoader();
        showToast('Account closed successfully.');
        closeModal('close-account-modal');
        
        // Redirect to login page
        setTimeout(() => {
            window.location.href = '../employer/emplogin.php';
        }, 1000);
    }, 800);
});

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

// ========== MODAL EVENT HANDLERS ==========

// Company Values Modal Handlers
const addValueBtn = document.getElementById('add-value-btn');
if (addValueBtn) {
    addValueBtn.addEventListener('click', () => {
        document.getElementById('value-title').value = '';
        document.getElementById('value-description').value = '';
        openModal('add-value-modal');
    });
}

const addValueSubmit = document.getElementById('add-value-submit');
if (addValueSubmit) {
    addValueSubmit.addEventListener('click', async () => {
        const title = document.getElementById('value-title').value.trim();
        const description = document.getElementById('value-description').value.trim();
        
        if (!title || !description) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await createCompanyValue(title, description);
        
        hideLoader();
        
        if (success) {
            closeModal('add-value-modal');
        }
    });
}

const editValueSubmit = document.getElementById('edit-value-submit');
if (editValueSubmit) {
    editValueSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('edit-value-id').value);
        const title = document.getElementById('edit-value-title').value.trim();
        const description = document.getElementById('edit-value-description').value.trim();
        
        if (!title || !description) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await updateCompanyValue(id, title, description);
        
        hideLoader();
        
        if (success) {
            closeModal('edit-value-modal');
        }
    });
}

const deleteValueSubmit = document.getElementById('delete-value-submit');
if (deleteValueSubmit) {
    deleteValueSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('delete-value-id').value);
        
        showLoader();
        
        const success = await deleteCompanyValue(id);
        
        hideLoader();
        
        if (success) {
            closeModal('delete-value-modal');
        }
    });
}

// Feedback Templates Modal Handlers
const addTemplateBtn = document.getElementById('add-template-btn');
if (addTemplateBtn) {
    addTemplateBtn.addEventListener('click', () => {
        document.getElementById('template-title').value = '';
        document.getElementById('template-content').value = '';
        openModal('add-template-modal');
    });
}

const addTemplateSubmit = document.getElementById('add-template-submit');
if (addTemplateSubmit) {
    addTemplateSubmit.addEventListener('click', async () => {
        const title = document.getElementById('template-title').value.trim();
        const content = document.getElementById('template-content').value.trim();
        
        if (!title || !content) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await createFeedbackTemplate(title, content);
        
        hideLoader();
        
        if (success) {
            closeModal('add-template-modal');
        }
    });
}

const editTemplateSubmit = document.getElementById('edit-template-submit');
if (editTemplateSubmit) {
    editTemplateSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('edit-template-id').value);
        const title = document.getElementById('edit-template-title').value.trim();
        const content = document.getElementById('edit-template-content').value.trim();
        
        if (!title || !content) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await updateFeedbackTemplate(id, title, content);
        
        hideLoader();
        
        if (success) {
            closeModal('edit-template-modal');
        }
    });
}

const deleteTemplateSubmit = document.getElementById('delete-template-submit');
if (deleteTemplateSubmit) {
    deleteTemplateSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('delete-template-id').value);
        
        showLoader();
        
        const success = await deleteFeedbackTemplate(id);
        
        hideLoader();
        
        if (success) {
            closeModal('delete-template-modal');
        }
    });
}

// ========== FORM VALIDATION ==========
function validateForm(form) {
    let isValid = true;
    
    // Check required fields
    form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            markInvalid(field, 'This field is required');
            isValid = false;
        } else {
            markValid(field);
        }
    });
    
    // Validate email fields
    form.querySelectorAll('input[type="email"]').forEach(field => {
        if (field.value.trim() && !validateEmail(field.value.trim())) {
            markInvalid(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Validate phone fields
    form.querySelectorAll('input[type="tel"]').forEach(field => {
        if (field.value.trim() && !validatePhone(field.value.trim())) {
            markInvalid(field, 'Please enter a valid phone number');
            isValid = false;
        }
    });
    
    // Validate URL fields
    form.querySelectorAll('input[type="url"]').forEach(field => {
        if (field.value.trim() && !validateURL(field.value.trim())) {
            markInvalid(field, 'Please enter a valid URL (e.g., https://example.com)');
            isValid = false;
        }
    });
    
    return isValid;
}

function markInvalid(field, message) {
    field.classList.add('invalid');
    
    // Remove existing error message if any
    const existingError = field.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error message element
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--danger)';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '5px';
    field.parentElement.appendChild(errorElement);
}

function markValid(field) {
    field.classList.remove('invalid');
    
    // Remove error message if exists
    const errorElement = field.parentElement.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
}

// Utility validation functions
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function validatePhone(phone) {
    const re = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
    return re.test(String(phone));
}

function validateURL(url) {
    try {
        new URL(url);
        return true;
    } catch (_) {
        return false;
    }
}

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

// Initialize page (FINAL VERSION)
document.addEventListener('DOMContentLoaded', function() {
    // Load company profile data when page loads
    loadCompanyProfileData();
    
    // Check account type for security settings
    checkAccountType();
    
    // Load all settings
    loadNotificationSettings();
    loadPrivacySettings();
    loadDisplaySettings();
    
    // Load Phase 3 data
    loadCompanyValues();
    loadFeedbackTemplates();
    
    // Restore active tab if any
    restoreActiveTab();
    
    // Mobile responsiveness
    if (window.innerWidth <= 768) {
        sidebar.classList.add('collapsed');
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
    }
});