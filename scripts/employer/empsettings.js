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
let hiringTeamData = [];

// Single loader instance
let loaderInstance = null;

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
            if (data.industries) {
                populateIndustriesDropdown(data.industries);
            }
            
            populateContactInfoForm(data.data.contact_person);
            populateCompanyInfoForm(data.data.company_identity, data.data.company_description);
            
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

function populateIndustriesDropdown(industries) {
    const industrySelect = document.getElementById('company-industry');
    if (industrySelect && industries) {
        industrySelect.innerHTML = '<option value="">Select Industry</option>';
        
        industries.forEach(industry => {
            const option = document.createElement('option');
            option.value = industry.industry_id;
            option.textContent = industry.industry_name;
            industrySelect.appendChild(option);
        });
        
        const othersOption = document.createElement('option');
        othersOption.value = 'others';
        othersOption.textContent = 'Others';
        industrySelect.appendChild(othersOption);
    }
}

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
        
        if (companyIndustry && companyData.industry_id) {
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

// ========== HIRING TEAM MANAGEMENT ==========
async function loadHiringTeam() {
    try {
        const response = await fetch('../../backend/employer/get_hiring_team.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            hiringTeamData = data.data || [];
            populateHiringTeamDisplay();
            return data.data;
        } else {
            console.log('No team members found');
            hiringTeamData = [];
            populateHiringTeamDisplay();
            return [];
        }
        
    } catch (error) {
        console.error('Error loading hiring team:', error);
        return [];
    }
}

function populateHiringTeamDisplay() {
    const teamList = document.getElementById('team-members-list');
    if (!teamList) return;
    
    teamList.innerHTML = '';
    
    if (hiringTeamData.length === 0) {
        teamList.innerHTML = '<p style="color: #999; text-align: center; padding: 20px;">No team members yet. Add your first team member!</p>';
        return;
    }
    
    hiringTeamData.forEach(member => {
        const initials = `${member.first_name.charAt(0)}${member.last_name.charAt(0)}`.toUpperCase();
        
        const memberElement = document.createElement('div');
        memberElement.className = 'team-member';
        memberElement.dataset.id = member.team_member_id;
        
        memberElement.innerHTML = `
            <div class="team-member-avatar">${initials}</div>
            <div class="team-member-info">
                <div class="team-member-name">${member.first_name} ${member.last_name}</div>
                <div class="team-member-role">${member.role}</div>
            </div>
            <div class="team-member-actions">
                <button type="button" class="edit-member" data-id="${member.team_member_id}"><i class="fas fa-pen"></i></button>
                <button type="button" class="delete-member" data-id="${member.team_member_id}"><i class="fas fa-trash"></i></button>
            </div>
        `;
        
        teamList.appendChild(memberElement);
    });
    
    setupTeamMemberButtons();
}

function setupTeamMemberButtons() {
    document.querySelectorAll('.edit-member').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const member = hiringTeamData.find(m => m.team_member_id == id);
            
            if (member) {
                document.getElementById('edit-member-name').value = `${member.first_name} ${member.last_name}`;
                document.getElementById('edit-member-role').value = member.role;
                document.getElementById('edit-member-id').value = id;
                
                openModal('edit-team-member-modal');
            }
        });
    });
    
    document.querySelectorAll('.delete-member').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = parseInt(this.getAttribute('data-id'));
            const member = hiringTeamData.find(m => m.team_member_id == id);
            
            if (member) {
                document.getElementById('delete-member-name').textContent = `${member.first_name} ${member.last_name}`;
                document.getElementById('delete-member-id').value = id;
                
                openModal('delete-team-member-modal');
            }
        });
    });
}

async function addTeamMember(name, email, role) {
    try {
        const nameParts = name.trim().split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        
        const response = await fetch('../../backend/employer/add_team_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                email: email,
                role: role
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Team member added successfully!');
            await loadHiringTeam();
            return true;
        } else {
            throw new Error(data.message || 'Failed to add team member');
        }
        
    } catch (error) {
        console.error('Error adding team member:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function updateTeamMember(memberId, name, role) {
    try {
        const nameParts = name.trim().split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        
        const response = await fetch('../../backend/employer/update_team_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                team_member_id: memberId,
                first_name: firstName,
                last_name: lastName,
                role: role
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Team member updated successfully!');
            await loadHiringTeam();
            return true;
        } else {
            throw new Error(data.message || 'Failed to update team member');
        }
        
    } catch (error) {
        console.error('Error updating team member:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

async function deleteTeamMember(memberId) {
    try {
        const response = await fetch('../../backend/employer/delete_team_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                team_member_id: memberId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Team member removed successfully!');
            await loadHiringTeam();
            return true;
        } else {
            throw new Error(data.message || 'Failed to remove team member');
        }
        
    } catch (error) {
        console.error('Error removing team member:', error);
        showToast('Error: ' + error.message);
        return false;
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

// ========== NOTIFICATION SETTINGS ==========
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
    
    const emailNotifications = document.getElementById('email-notifications');
    const smsNotifications = document.getElementById('sms-notifications');
    const pushNotifications = document.getElementById('push-notifications');
    
    if (emailNotifications) emailNotifications.checked = notificationData.email_notifications || false;
    if (smsNotifications) smsNotifications.checked = notificationData.sms_notifications || false;
    if (pushNotifications) pushNotifications.checked = notificationData.push_notifications || false;
    
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
    
    const emailFrequency = document.getElementById('email-frequency');
    if (emailFrequency) emailFrequency.value = notificationData.email_frequency || 'immediate';
    
    const enableQuietHours = document.getElementById('enable-quiet-hours');
    const quietFrom = document.getElementById('quiet-from');
    const quietTo = document.getElementById('quiet-to');
    
    if (enableQuietHours) enableQuietHours.checked = notificationData.enable_quiet_hours || false;
    if (quietFrom) quietFrom.value = notificationData.quiet_from ? notificationData.quiet_from.substring(0, 5) : '22:00';
    if (quietTo) quietTo.value = notificationData.quiet_to ? notificationData.quiet_to.substring(0, 5) : '08:00';
}

// ========== PRIVACY SETTINGS ==========
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
    
    const profileVisibility = document.getElementById('profile-visibility');
    if (profileVisibility) profileVisibility.checked = privacyData.profile_visibility || false;
    
    const shareCompanyInfo = document.getElementById('share-company-info');
    const shareContactInfo = document.getElementById('share-contact-info');
    
    if (shareCompanyInfo) shareCompanyInfo.checked = privacyData.share_company_info || false;
    if (shareContactInfo) shareContactInfo.checked = privacyData.share_contact_info || false;
    
    const jobVisibility = document.getElementById('job-visibility');
    if (jobVisibility) jobVisibility.value = privacyData.job_visibility || 'public';
    
    const allowDataCollection = document.getElementById('allow-data-collection');
    const allowMarketing = document.getElementById('allow-marketing');
    const allowThirdParty = document.getElementById('allow-third-party');
    
    if (allowDataCollection) allowDataCollection.checked = privacyData.allow_data_collection || false;
    if (allowMarketing) allowMarketing.checked = privacyData.allow_marketing || false;
    if (allowThirdParty) allowThirdParty.checked = privacyData.allow_third_party || false;
}

// ========== DISPLAY SETTINGS ==========
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
    
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.checked = radio.value === displayData.theme;
    });
    
    const fontSizeRadios = document.querySelectorAll('input[name="font-size"]');
    fontSizeRadios.forEach(radio => {
        radio.checked = radio.value === displayData.font_size;
    });
    
    const colorScheme = document.getElementById('color-scheme');
    if (colorScheme) colorScheme.value = displayData.color_scheme || 'default';
    
    const highContrast = document.getElementById('high-contrast');
    const reduceMotion = document.getElementById('reduce-motion');
    const screenReaderSupport = document.getElementById('screen-reader-support');
    
    if (highContrast) highContrast.checked = displayData.high_contrast || false;
    if (reduceMotion) reduceMotion.checked = displayData.reduce_motion || false;
    if (screenReaderSupport) screenReaderSupport.checked = displayData.screen_reader_support || false;
    
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
            // Note: password is not included - settings has separate password form
        };
        
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
        const companySize = document.getElementById('company-size')?.value || '';
        
        if (!companyName) {
            throw new Error('Company name is required');
        }
        if (!companyIndustry) {
            throw new Error('Please select an industry');
        }
        
        // Step 1: Update company identity (name, industry, website, size)
        // Note: company_address is not in settings form, so we send empty string
        const identityResponse = await fetch('../../backend/employer/update_company_identity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                company_name: companyName,
                industry_id: companyIndustry,
                custom_industry: '', // Settings doesn't have custom industry field
                company_address: companyAddress, 
                company_website: companyWebsite,
                company_size: companySize
            })
        });
        
        const identityData = await identityResponse.json();
        if (!identityData.success) {
            throw new Error(identityData.message || 'Failed to update company identity');
        }
        
        // Step 2: Update company description if provided
        // Note: The existing backend expects FormData (not JSON) because it handles file uploads
        // For settings, we only update description via direct SQL since no logo upload here
        if (companyDescription) {
            const descResponse = await fetch('../../backend/employer/update_company_description_text.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    company_description: companyDescription
                })
            });
            
            const descData = await descResponse.json();
            if (!descData.success) {
                // Don't fail if description update fails, just log it
                console.warn('Description update failed:', descData.message);
            }
        }
        
        showToast('Company information updated successfully!');
        return true;
        
    } catch (error) {
        console.error('Error updating company info:', error);
        showToast('Error: ' + error.message);
        return false;
    }
}

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

async function submitNotificationSettingsForm() {
    try {
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
        const selectedTheme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
        const selectedFontSize = document.querySelector('input[name="font-size"]:checked')?.value || 'medium';
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
            await loadCompanyValues();
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
            await loadCompanyValues();
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
            await loadCompanyValues();
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
            await loadFeedbackTemplates();
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
            await loadFeedbackTemplates();
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
            await loadFeedbackTemplates();
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
            
            // Load hiring team when clicking on hiring team management
            if (settingId === 'hiring-team') {
                loadHiringTeam();
            }
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
            if (form.id === 'contact-info-form') {
                success = await submitContactInfoForm();
            } else if (form.id === 'company-info-form') {
                success = await submitCompanyInfoForm();
            } else if (form.id === 'password-security-form') {
                success = await submitPasswordForm();
            } else if (form.id === 'notification-settings-form') {
                success = await submitNotificationSettingsForm();
            } else if (form.id === 'privacy-preferences-form') {
                success = await submitPrivacySettingsForm();
            } else if (form.id === 'display-form') {
                success = await submitDisplaySettingsForm();
            } else if (form.id === 'company-values-form') {
                success = true;
                showToast('Company values updated successfully!');
            } else if (form.id === 'feedback-settings-form') {
                success = true;
                showToast('Feedback settings updated successfully!');
            } else if (form.id === 'analytics-form') {
                success = await submitAnalyticsSettingsForm();
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
    closeAccountBtn.addEventListener('click', () => {
        openModal('close-account-modal');
    });
}

if (cancelCloseAccount) {
    cancelCloseAccount.addEventListener('click', () => {
        closeModal('close-account-modal');
    });
}

if (confirmCloseAccount) {
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
            
            setTimeout(() => {
                window.location.href = '../employer/emplogin.php';
            }, 1000);
        }, 800);
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

// ========== MODAL EVENT HANDLERS ==========

// Hiring Team Modal Handlers
const addTeamMemberBtn = document.getElementById('add-team-member-btn');
if (addTeamMemberBtn) {
    addTeamMemberBtn.addEventListener('click', () => {
        document.getElementById('member-name').value = '';
        document.getElementById('member-email').value = '';
        document.getElementById('member-role').value = 'HR Manager (Admin)';
        openModal('add-team-member-modal');
    });
}

const addMemberSubmit = document.getElementById('add-member-submit');
if (addMemberSubmit) {
    addMemberSubmit.addEventListener('click', async () => {
        const name = document.getElementById('member-name').value.trim();
        const email = document.getElementById('member-email').value.trim();
        const role = document.getElementById('member-role').value;
        
        if (!name || !email) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await addTeamMember(name, email, role);
        
        hideLoader();
        
        if (success) {
            closeModal('add-team-member-modal');
        }
    });
}

const editMemberSubmit = document.getElementById('edit-member-submit');
if (editMemberSubmit) {
    editMemberSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('edit-member-id').value);
        const name = document.getElementById('edit-member-name').value.trim();
        const role = document.getElementById('edit-member-role').value;
        
        if (!name) {
            alert('Please fill in all required fields.');
            return;
        }
        
        showLoader();
        
        const success = await updateTeamMember(id, name, role);
        
        hideLoader();
        
        if (success) {
            closeModal('edit-team-member-modal');
        }
    });
}

const deleteMemberSubmit = document.getElementById('delete-member-submit');
if (deleteMemberSubmit) {
    deleteMemberSubmit.addEventListener('click', async function() {
        const id = parseInt(document.getElementById('delete-member-id').value);
        
        showLoader();
        
        const success = await deleteTeamMember(id);
        
        hideLoader();
        
        if (success) {
            closeModal('delete-team-member-modal');
        }
    });
}

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
    if (window.innerWidth <= 768 && sidebar) {
        sidebar.classList.add('collapsed');
        if (toggleIcon) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        }
    }
});