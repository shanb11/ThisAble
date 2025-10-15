// Complete empprofile.js with Phase 2 Implementation
// Replace your entire empprofile.js file with this

// Global variables to store profile data
let profileData = {};
let logoRemovalFlag = false;
let industriesList = [];

// Toast Notification System
function showToast(message, type = 'success') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    // Toast icons based on type
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="${icons[type]}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast with animation
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

// Loading state management
function setLoadingState(isLoading) {
    const loadingOverlay = document.querySelector('.loading-overlay') || createLoadingOverlay();
    
    if (isLoading) {
        loadingOverlay.style.display = 'flex';
    } else {
        loadingOverlay.style.display = 'none';
    }
}

function createLoadingOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Loading profile data...</span>
        </div>
    `;
    document.body.appendChild(overlay);
    return overlay;
}

// Fetch profile data from backend
async function loadProfileData() {
    try {
        setLoadingState(true);
        console.log('📡 Loading profile data from backend...');
        
        const response = await fetch('../../backend/employer/get_company_profile.php');
        const result = await response.json();
        
        console.log('📥 Backend response:', result);
        
        if (result.success) {
    profileData = result.data;
    industriesList = result.industries;
    
    console.log('📊 Loaded profile data:', profileData);
    console.log('🏢 Loaded industries:', industriesList);
    
    // Populate all sections
    populateProfileData();
    showToast('Profile loaded');  // <-- PINALITAN
} else {
    console.error('❌ Backend error:', result);
    showToast(result.message || 'Load failed', 'error');  // <-- PINALITAN
            if (result.redirect) {
                setTimeout(() => window.location.href = result.redirect, 2000);
            }
        }
    } catch (error) {
    console.error('💥 Network error loading profile:', error);
    showToast('Network error', 'error');  // <-- PINALITAN
} finally {
        setLoadingState(false);
    }
}

// Populate all profile sections with real data
function populateProfileData() {
    populateCompanyIdentity();
    populateContactPerson();
    populateCompanyDescription();
    populateHiringPreferences();
    populateSocialLinks();
    updateProgressBar();
    
    // Animate progress after data is loaded
    setTimeout(() => {
        animateProgressOnLoad();
        initializeProgressItemNavigation();
    }, 500);
}

function populateCompanyIdentity() {
    const identity = profileData.company_identity;
    
    // Display values
    document.getElementById('display-company-name').textContent = identity.company_name || 'Not provided';
    document.getElementById('display-industry').textContent = identity.industry || 'Not provided';
    document.getElementById('display-company-address').textContent = identity.company_address || 'Not provided';
    
    // Modal form values
    document.getElementById('companyName').value = identity.company_name || '';
    document.getElementById('companyAddress').value = identity.company_address || '';
    
    // Populate industry dropdown
    populateIndustryDropdown(identity.industry_id, identity.industry);
}

function populateContactPerson() {
    const contact = profileData.contact_person;
    
    // Display values
    document.getElementById('display-first-name').textContent = contact.first_name || 'Not provided';
    document.getElementById('display-last-name').textContent = contact.last_name || 'Not provided';
    document.getElementById('display-position').textContent = contact.position || 'Not provided';
    document.getElementById('display-contact-number').textContent = contact.contact_number || 'Not provided';
    document.getElementById('display-email').textContent = contact.email || 'Not provided';
    document.getElementById('display-contact-person').textContent = 
        `${contact.first_name || ''} ${contact.last_name || ''}`.trim() || 'Not provided';
    
    // Modal form values
    document.getElementById('firstName').value = contact.first_name || '';
    document.getElementById('lastName').value = contact.last_name || '';
    document.getElementById('position').value = contact.position || '';
    document.getElementById('contactNumber').value = contact.contact_number || '';
    document.getElementById('email').value = contact.email || '';
}

function populateCompanyDescription() {
    const description = profileData.company_description;
    
    // Display values
    document.getElementById('display-about-us').textContent = description.company_description || 'Not provided';
    document.getElementById('display-why-join-us').textContent = description.why_join_us || 'Not provided';
    
    // Modal form values
    document.getElementById('aboutUs').value = description.company_description || '';
    document.getElementById('whyJoinUs').value = description.why_join_us || '';
    
    // Logo handling
    const logoImg = document.getElementById('logo-img');
    const logoPlaceholder = document.getElementById('logo-placeholder');
    const modalLogoImg = document.getElementById('modal-logo-img');
    const modalLogoPlaceholder = document.getElementById('modal-logo-placeholder');
    
    if (description.company_logo_path) {
        const logoPath = `../../${description.company_logo_path}`;
        logoImg.src = logoPath;
        logoImg.style.display = 'block';
        logoPlaceholder.style.display = 'none';
        
        modalLogoImg.src = logoPath;
        modalLogoImg.style.display = 'block';
        modalLogoPlaceholder.style.display = 'none';
    }
}

function populateHiringPreferences() {
    console.log('🔄 Populating hiring preferences...');
    const preferences = profileData.hiring_preferences || {};
    
    // Display PWD preference
    document.getElementById('display-hire-pwd').textContent = preferences.open_to_pwd ? 'Yes' : 'No';
    
    // Modal form values
    document.getElementById('hirePwd').checked = preferences.open_to_pwd || false;
    
    // Update display sections
    updateDisplayDisabilityTypes(preferences.disability_types || []);
    updateDisplayAccessibilityOptions(preferences.accessibility_options || []);
    
    console.log('✅ Loaded preferences:', preferences);
}

function populateSocialLinks() {
    const social = profileData.social_links;
    
    // Display values
    document.getElementById('display-website').textContent = social.website_url || 'Not provided';
    document.getElementById('display-linkedin').textContent = social.linkedin_url || 'Not provided';
    
    // Modal form values
    document.getElementById('website').value = social.website_url || '';
    document.getElementById('linkedin').value = social.linkedin_url || '';
}

// Enhanced industry dropdown population
function populateIndustryDropdown(selectedIndustryId, customIndustry) {
    const industryField = document.getElementById('industry');
    
    if (!industryField) return;
    
    // Clear existing options
    industryField.innerHTML = '';
    
    // Add placeholder option
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = 'Select an industry';
    placeholderOption.disabled = true;
    industryField.appendChild(placeholderOption);
    
    // Add industries from database
    industriesList.forEach(industry => {
        const option = document.createElement('option');
        option.value = industry.industry_id;
        option.textContent = industry.industry_name;
        
        if (selectedIndustryId && industry.industry_id == selectedIndustryId) {
            option.selected = true;
        }
        
        industryField.appendChild(option);
    });
    
    // Add "Others" option
    const othersOption = document.createElement('option');
    othersOption.value = 'others';
    othersOption.textContent = 'Others (specify below)';
    industryField.appendChild(othersOption);
    
    // If custom industry (not in predefined list), select "Others"
    if (customIndustry && !selectedIndustryId) {
        othersOption.selected = true;
        showCustomIndustryInput(customIndustry);
    }
    
    // Add event listener for industry change
    industryField.addEventListener('change', function() {
        if (this.value === 'others') {
            showCustomIndustryInput();
        } else {
            hideCustomIndustryInput();
        }
    });
}

function showCustomIndustryInput(value = '') {
    let customInput = document.getElementById('custom-industry');
    
    if (!customInput) {
        // Create custom industry input
        const industryGroup = document.getElementById('industry').parentNode;
        
        const inputContainer = document.createElement('div');
        inputContainer.className = 'custom-industry-container';
        inputContainer.style.marginTop = '10px';
        
        const label = document.createElement('label');
        label.textContent = 'Specify Your Industry';
        label.className = 'custom-industry-label';
        label.style.display = 'block';
        label.style.marginBottom = '5px';
        label.style.fontSize = '13px';
        label.style.color = 'var(--text-medium)';
        
        customInput = document.createElement('input');
        customInput.type = 'text';
        customInput.id = 'custom-industry';
        customInput.className = 'form-control';
        customInput.placeholder = 'e.g., Renewable Energy, EdTech, FinTech';
        customInput.required = true;
        
        inputContainer.appendChild(label);
        inputContainer.appendChild(customInput);
        industryGroup.appendChild(inputContainer);
    }
    
    customInput.value = value;
    const container = customInput.closest('.custom-industry-container');
    if (container) {
        container.style.display = 'block';
    }
    
    // Focus on the input
    setTimeout(() => customInput.focus(), 100);
}

function hideCustomIndustryInput() {
    const customInput = document.getElementById('custom-industry');
    if (customInput) {
        const container = customInput.closest('.custom-industry-container');
        if (container) {
            container.style.display = 'none';
        }
        customInput.value = '';
    }
}

// Enhanced Progress Bar Management - Candidate Style
function updateProgressBar() {
    if (!profileData || !profileData.setup_progress) {
        return;
    }
    
    const percentage = calculateCompletionPercentage();
    updateMainProgressBar(percentage);
    updateProgressItems();
    updateStatusMessage(percentage);
    
    // Update the progress data
    profileData.setup_progress.completion_percentage = percentage;
}

function updateMainProgressBar(percentage) {
    const percentageDisplay = document.getElementById('completion-percentage-display');
    const progressFill = document.getElementById('main-progress-fill');
    
    if (!percentageDisplay || !progressFill) return;
    
    // Add updating animation
    progressFill.classList.add('progress-updating');
    
    setTimeout(() => {
        // Update percentage display
        percentageDisplay.textContent = `${percentage}%`;
        
        // Update progress bar width
        progressFill.style.width = `${percentage}%`;
        
        // Remove updating animation
        progressFill.classList.remove('progress-updating');
        
        // Show congratulations if 100%
        const congratsMessage = document.getElementById('congratulations-message');
        if (congratsMessage) {
            if (percentage === 100) {
                congratsMessage.style.display = 'flex';
            } else {
                congratsMessage.style.display = 'none';
            }
        }
        
    }, 300);
}

// Enhanced progress bar update function
function updateProgressBarWithValue(percentage) {
    const progressBar = document.getElementById('main-progress-fill');
    const completionText = document.getElementById('completion-percentage-display');
    const completionMessage = document.getElementById('completion-status-message');
    const completionBadge = document.getElementById('congratulations-message');
    
    if (!progressBar || !completionText) return;
    
    // Add updating animation
    progressBar.classList.add('progress-updating');
    
    setTimeout(() => {
        // Update progress bar width
        progressBar.style.width = `${percentage}%`;
        
        // Update completion text
        completionText.textContent = `${percentage}%`;
        
        // Update completion message based on percentage
        let message = '';
        let messageClass = '';
        
        if (percentage === 100) {
            message = '🎉 Excellent! Your profile is complete and highly visible to candidates.';
            messageClass = 'success';
            if (completionBadge) completionBadge.style.display = 'flex';
        } else if (percentage >= 80) {
            message = '🚀 Almost there! Complete the remaining sections to maximize your profile visibility.';
            messageClass = 'success';
            if (completionBadge) completionBadge.style.display = 'none';
        } else if (percentage >= 60) {
            message = '📈 Good progress! Keep adding details to improve your profile attractiveness.';
            messageClass = 'warning';
            if (completionBadge) completionBadge.style.display = 'none';
        } else if (percentage >= 40) {
            message = '💪 Getting started! Continue completing sections to attract more candidates.';
            messageClass = 'info';
            if (completionBadge) completionBadge.style.display = 'none';
        } else {
            message = '🎯 Complete your profile to attract more candidates and improve visibility.';
            messageClass = 'info';
            if (completionBadge) completionBadge.style.display = 'none';
        }
        
        if (completionMessage) {
            const messageIcon = completionMessage.querySelector('i');
            const messageText = completionMessage.querySelector('span');
            
            // Remove existing classes
            completionMessage.classList.remove('success', 'warning', 'info');
            completionMessage.classList.add(messageClass);
            
            if (messageText) messageText.textContent = message;
        }
        
        // Remove updating animation
        progressBar.classList.remove('progress-updating');
        
    }, 300);
}

function updateProgressItems() {
    // Update each progress item based on completion status
    updateProgressItem('progress-company-info', isBasicInfoComplete());
    updateProgressItem('progress-description', isDescriptionComplete());
    updateProgressItem('progress-preferences', isPreferencesComplete());
    updateProgressItem('progress-social', isSocialLinksComplete());
    updateProgressItem('progress-logo', isLogoComplete());
}

function updateProgressItem(itemId, isComplete) {
    const item = document.getElementById(itemId);
    if (!item) return;
    
    if (isComplete) {
        item.classList.add('completed');
    } else {
        item.classList.remove('completed');
    }
}

function updateStatusMessage(percentage) {
    const statusMessage = document.getElementById('completion-status-message');
    if (!statusMessage) return;
    
    const messageIcon = statusMessage.querySelector('i');
    const messageText = statusMessage.querySelector('span');
    
    // Remove existing classes
    statusMessage.classList.remove('success', 'warning');
    
    if (percentage === 100) {
        messageIcon.className = 'fas fa-check-circle';
        messageText.textContent = 'Excellent! Your profile is complete and highly visible to candidates.';
        statusMessage.classList.add('success');
    } else if (percentage >= 80) {
        messageIcon.className = 'fas fa-info-circle';
        messageText.textContent = 'Almost there! Complete the remaining sections to maximize your profile visibility.';
        statusMessage.classList.add('success');
    } else if (percentage >= 60) {
        messageIcon.className = 'fas fa-exclamation-triangle';
        messageText.textContent = 'Good progress! Keep adding details to improve your profile attractiveness.';
        statusMessage.classList.add('warning');
    } else if (percentage >= 40) {
        messageIcon.className = 'fas fa-info-circle';
        messageText.textContent = 'Getting started! Continue completing sections to attract more candidates.';
    } else {
        messageIcon.className = 'fas fa-info-circle';
        messageText.textContent = 'Complete your profile to attract more candidates and improve visibility.';
    }
}

// Add progress item click handlers for navigation
function initializeProgressItemNavigation() {
    const progressItems = document.querySelectorAll('.progress-item');
    
    progressItems.forEach(item => {
        item.addEventListener('click', function() {
            const sectionType = this.dataset.section;
            navigateToProfileSection(sectionType);
        });
        
        // Add hover effect
        item.style.cursor = 'pointer';
    });
}

function navigateToProfileSection(sectionType) {
    let editButtonId = '';
    
    switch(sectionType) {
        case 'company_info':
            editButtonId = 'edit-identity';
            break;
        case 'description':
            editButtonId = 'edit-logo-description';
            break;
        case 'preferences':
            editButtonId = 'edit-preferences';
            break;
        case 'social':
            editButtonId = 'edit-social';
            break;
        case 'logo':
            editButtonId = 'edit-logo-description';
            break;
    }
    
    if (editButtonId) {
        const editButton = document.getElementById(editButtonId);
        if (editButton) {
            // Scroll to section first
            editButton.closest('.profile-section').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            
            // Highlight the section briefly
            const section = editButton.closest('.profile-section');
            section.style.boxShadow = '0 0 20px rgba(37, 113, 128, 0.3)';
            
            setTimeout(() => {
                section.style.boxShadow = '';
            }, 2000);
        }
    }
}

// Progress Animation on Load
function animateProgressOnLoad() {
    const progressFill = document.getElementById('main-progress-fill');
    const percentageDisplay = document.getElementById('completion-percentage-display');
    
    if (!progressFill || !percentageDisplay) return;
    
    const originalWidth = progressFill.style.width;
    const targetPercentage = parseInt(percentageDisplay.textContent);
    
    // Start from 0
    progressFill.style.width = '0%';
    percentageDisplay.textContent = '0%';
    
    // Animate to actual value
    setTimeout(() => {
        progressFill.style.width = originalWidth;
        
        // Animate percentage counter
        animateCounter(percentageDisplay, 0, targetPercentage, 800);
    }, 500);
}

function animateCounter(element, start, end, duration) {
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easedProgress = 1 - Math.pow(1 - progress, 3);
        const currentValue = Math.round(start + (end - start) * easedProgress);
        
        element.textContent = `${currentValue}%`;
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

// Keep existing calculation functions
function calculateCompletionPercentage() {
    let completedSections = 0;
    const totalSections = 5; // 5 sections worth 20% each
    
    console.log('🧮 Calculating completion percentage...');
    
    // 1. Basic Info (20%) - Company name, industry, address
    if (isBasicInfoComplete()) {
        completedSections++;
        console.log('✅ Basic Info: Complete');
    } else {
        console.log('❌ Basic Info: Incomplete');
    }
    
    // 2. Company Description (20%) - Description and why join us
    if (isDescriptionComplete()) {
        completedSections++;
        console.log('✅ Description: Complete');
    } else {
        console.log('❌ Description: Incomplete');
    }
    
    // 3. Hiring Preferences (20%) - PWD settings
    if (isPreferencesComplete()) {
        completedSections++;
        console.log('✅ Preferences: Complete');
    } else {
        console.log('❌ Preferences: Incomplete');
    }
    
    // 4. Social Links (20%) - At least website
    if (isSocialLinksComplete()) {
        completedSections++;
        console.log('✅ Social Links: Complete');
    } else {
        console.log('❌ Social Links: Incomplete');
    }
    
    // 5. Logo (20%) - Logo uploaded
    if (isLogoComplete()) {
        completedSections++;
        console.log('✅ Logo: Complete');
    } else {
        console.log('❌ Logo: Incomplete');
    }
    
    const percentage = Math.round((completedSections / totalSections) * 100);
    console.log(`📊 Total completion: ${percentage}% (${completedSections}/${totalSections} sections)`);
    
    return percentage;
}

function isBasicInfoComplete() {
    const identity = profileData.company_identity;
    return !!(identity.company_name && 
             identity.industry && 
             identity.company_address);
}

function isDescriptionComplete() {
    const description = profileData.company_description;
    return !!(description.company_description && 
             description.company_description.length > 50 &&
             description.why_join_us && 
             description.why_join_us.length > 30);
}

function isPreferencesComplete() {
    const preferences = profileData.hiring_preferences;
    // At minimum, they should have set their PWD preference
    return preferences.hasOwnProperty('open_to_pwd');
}

function isSocialLinksComplete() {
    const social = profileData.social_links;
    return !!(social.website_url && social.website_url.length > 0);
}

function isLogoComplete() {
    const description = profileData.company_description;
    return !!(description.company_logo_path && description.company_logo_path.length > 0);
}

// PHASE 2: SAVING FUNCTIONALITY

// Initialize Identity Saving
function initializeIdentitySaving() {
    document.getElementById('save-identity')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const saveBtn = this;
        const companyName = document.getElementById('companyName').value.trim();
        const industrySelect = document.getElementById('industry');
        const industryValue = industrySelect.value;
        const customIndustry = document.getElementById('custom-industry')?.value.trim() || '';
        const companyAddress = document.getElementById('companyAddress').value.trim();
        
        // Client-side validation
        const errors = [];
        if (!companyName) errors.push('Company name is required');
        if (!industryValue) errors.push('Please select an industry');
        if (industryValue === 'others' && !customIndustry) errors.push('Please specify your industry');
        if (!companyAddress) errors.push('Company address is required');
        
        if (errors.length > 0) {
            showToast(errors[0], 'error');
            return;
        }
        
        // Show loading state
        saveBtn.classList.add('loading');
        saveBtn.disabled = true;
        
        try {
            const requestData = {
                company_name: companyName,
                industry_id: industryValue === 'others' ? 'others' : industryValue,
                custom_industry: customIndustry,
                company_address: companyAddress
            };
            
            const response = await fetch('../../backend/employer/update_company_identity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update displayed values
                document.getElementById('display-company-name').textContent = result.data.company_name;
                document.getElementById('display-industry').textContent = result.data.industry;
                document.getElementById('display-company-address').textContent = result.data.company_address;
                
                // Update contact person display if names are available
                const firstName = document.getElementById('firstName').value;
                const lastName = document.getElementById('lastName').value;
                if (firstName && lastName) {
                    document.getElementById('display-contact-person').textContent = firstName + ' ' + lastName;
                }
                
                // Update progress bar
                if (result.data.completion_percentage) {
                    updateProgressBarWithValue(result.data.completion_percentage);
                    updateProgressItems();
                }
                
                // Update local profile data
                profileData.company_identity = {
                    ...profileData.company_identity,
                    company_name: result.data.company_name,
                    industry: result.data.industry,
                    industry_id: result.data.industry_id,
                    company_address: result.data.company_address
                };
                
                closeModal('identity-modal');
showToast('Identity updated');  // <-- PINALITAN
                
            } else {
                if (result.errors && result.errors.length > 0) {
                    showToast(result.errors[0], 'error');
                } else {
                    showToast(result.message || 'Failed to update company identity', 'error');
                }
            }
            
        } catch (error) {
            console.error('Error updating company identity:', error);
            showToast('Network error occurred. Please try again.', 'error');
        } finally {
            saveBtn.classList.remove('loading');
            saveBtn.disabled = false;
        }
    });
}

// Initialize Contact Saving
function initializeContactSaving() {
    document.getElementById('save-contact')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const saveBtn = this;
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const position = document.getElementById('position').value.trim();
        const contactNumber = document.getElementById('contactNumber').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        
        // Client-side validation
        const errors = [];
        if (!firstName) errors.push('First name is required');
        if (!lastName) errors.push('Last name is required');
        if (!position) errors.push('Position is required');
        if (!contactNumber) errors.push('Contact number is required');
        if (!email) errors.push('Email is required');
        
        // Email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            errors.push('Please enter a valid email address');
        }
        
        // Password validation (only if changed)
        const isPasswordChanged = password && password !== '••••••••';
        if (isPasswordChanged) {
            if (password.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }
            if (!/[A-Z]/.test(password)) {
                errors.push('Password must contain at least one uppercase letter');
            }
            if (!/[a-z]/.test(password)) {
                errors.push('Password must contain at least one lowercase letter');
            }
            if (!/\d/.test(password)) {
                errors.push('Password must contain at least one number');
            }
        }
        
        if (errors.length > 0) {
            showToast(errors[0], 'error');
            return;
        }
        
        // Show loading state
        saveBtn.classList.add('loading');
        saveBtn.disabled = true;
        
        try {
            const requestData = {
                first_name: firstName,
                last_name: lastName,
                position: position,
                contact_number: contactNumber,
                email: email,
                password: isPasswordChanged ? password : ''
            };
            
            const response = await fetch('../../backend/employer/update_contact_info.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update displayed values
                document.getElementById('display-first-name').textContent = result.data.first_name;
                document.getElementById('display-last-name').textContent = result.data.last_name;
                document.getElementById('display-position').textContent = result.data.position;
                document.getElementById('display-contact-number').textContent = result.data.contact_number;
                document.getElementById('display-email').textContent = result.data.email;
                document.getElementById('display-contact-person').textContent = result.data.full_name;
                
                // Update local profile data
                profileData.contact_person = {
                    ...profileData.contact_person,
                    first_name: result.data.first_name,
                    last_name: result.data.last_name,
                    position: result.data.position,
                    contact_number: result.data.contact_number,
                    email: result.data.email
                };
                
                // Clear password field
                document.getElementById('password').value = '';
                
                closeModal('contact-modal');

let message = 'Contact updated';  // <-- PINALITAN
if (result.data.password_updated) {
    message = 'Contact & password updated';  // <-- PINALITAN
}
showToast(message);
                
            } else {
                if (result.errors && result.errors.length > 0) {
                    showToast(result.errors[0], 'error');
                } else {
                    showToast(result.message || 'Failed to update contact information', 'error');
                }
            }
            
        } catch (error) {
            console.error('Error updating contact information:', error);
            showToast('Network error occurred. Please try again.', 'error');
        } finally {
            saveBtn.classList.remove('loading');
            saveBtn.disabled = false;
        }
    });
}

// Enhanced modal management
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Add fade-in animation
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
        
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Focus on first input
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 300);
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Add fade-out animation
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 200);
        
        // Clear form validation states
        const formControls = modal.querySelectorAll('.form-control');
        formControls.forEach(control => {
            control.classList.remove('error', 'success');
        });
        
        // Hide custom industry input if open
        hideCustomIndustryInput();
    }
}

function initializePreferencesHandlers() {
    console.log('🔧 Initializing preferences handlers...');
    
    // Remove any existing event listeners first to prevent duplicates
    const existingCategoryCards = document.querySelectorAll('.category-card');
    const existingIconCircles = document.querySelectorAll('.icon-circle');
    
    console.log('Found category cards:', existingCategoryCards.length);
    console.log('Found icon circles:', existingIconCircles.length);
    
    // Category cards click handlers
    existingCategoryCards.forEach((card, index) => {
        // Remove existing event listeners by cloning
        const newCard = card.cloneNode(true);
        card.parentNode.replaceChild(newCard, card);
        
        console.log(`Setting up card ${index}:`, {
            dataCategory: newCard.getAttribute('data-category'),
            initialClasses: Array.from(newCard.classList)
        });
        
        newCard.addEventListener('click', function(event) {
            // Stop event bubbling
            event.preventDefault();
            event.stopPropagation();
            
            const categoryType = this.getAttribute('data-category');
            console.log('🖱️ Category clicked:', categoryType);
            console.log('Before toggle - classes:', Array.from(this.classList));
            
            // Check current state BEFORE toggle
            const wasSelected = this.classList.contains('selected');
            console.log('Was selected before click:', wasSelected);
            
            // Toggle selected state
            this.classList.toggle('selected');
            
            // Check state AFTER toggle  
            const isNowSelected = this.classList.contains('selected');
            console.log('After toggle - classes:', Array.from(this.classList));
            console.log('Is now selected:', isNowSelected);
            
            // Visual feedback based on NEW state
            if (isNowSelected) {
                console.log('✅ Category NOW SELECTED:', categoryType);
                this.style.borderColor = 'var(--primary)';
                this.style.background = 'rgba(37, 113, 128, 0.05)';
            } else {
                console.log('❌ Category NOW DESELECTED:', categoryType);
                this.style.borderColor = '';
                this.style.background = '';
            }
        });
    });
    
    // Get updated list after cloning
    const categoryCards = document.querySelectorAll('.category-card');
    
    // Accessibility icons click handlers  
    existingIconCircles.forEach((circle, index) => {
        // Remove existing event listeners by cloning
        const newCircle = circle.cloneNode(true);
        circle.parentNode.replaceChild(newCircle, circle);
        
        console.log(`Setting up icon ${index}:`, {
            dataOption: newCircle.getAttribute('data-option'),
            initialClasses: Array.from(newCircle.classList)
        });
        
        newCircle.addEventListener('click', function(event) {
            // Stop event bubbling
            event.preventDefault();
            event.stopPropagation();
            
            const optionType = this.getAttribute('data-option');
            console.log('🖱️ Accessibility option clicked:', optionType);
            console.log('Before toggle - classes:', Array.from(this.classList));
            
            // Check current state BEFORE toggle
            const wasActive = this.classList.contains('active');
            console.log('Was active before click:', wasActive);
            
            // Toggle active state
            this.classList.toggle('active');
            
            // Check state AFTER toggle
            const isNowActive = this.classList.contains('active');
            console.log('After toggle - classes:', Array.from(this.classList));
            console.log('Is now active:', isNowActive);
            
            // Visual feedback based on NEW state
            if (isNowActive) {
                console.log('✅ Accessibility option NOW ACTIVE:', optionType);
                this.style.background = 'var(--primary)';
                this.style.color = 'white';
                this.style.borderColor = 'var(--primary)';
            } else {
                console.log('❌ Accessibility option NOW INACTIVE:', optionType);
                this.style.background = '';
                this.style.color = '';
                this.style.borderColor = '';
            }
        });
    });
    
    // Get updated list after cloning
    const iconCircles = document.querySelectorAll('.icon-circle');
    
    console.log('✅ Preferences handlers initialized successfully!');
    console.log('Final count - Categories:', categoryCards.length, 'Icons:', iconCircles.length);
}

// ALSO ADD this function to clear and reload preferences properly:

function clearAndReloadPreferences() {
    console.log('🧹 Clearing all preference selections...');
    
    // Clear category selections
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.classList.remove('selected');
        card.style.borderColor = '';
        card.style.background = '';
        console.log('Cleared category:', card.getAttribute('data-category'));
    });
    
    // Clear accessibility selections
    const iconCircles = document.querySelectorAll('.icon-circle');
    iconCircles.forEach(circle => {
        circle.classList.remove('active');
        circle.style.background = '';
        circle.style.color = '';
        circle.style.borderColor = '';
        console.log('Cleared icon:', circle.getAttribute('data-option'));
    });
    
    console.log('✅ All selections cleared');
}

// FIXED: Display Update Functions
function updateDisplayDisabilityTypes(disabilityTypes) {
    const displayContainer = document.getElementById('display-disability-types');
    if (!displayContainer) return;
    
    console.log('Updating display disability types:', disabilityTypes);
    
    // Clear current display
    displayContainer.innerHTML = '';
    
    if (!disabilityTypes || disabilityTypes.length === 0) {
        displayContainer.innerHTML = '<p style="color: #6c757d; font-style: italic;">No specific disabilities selected</p>';
        return;
    }
    
    // Category mapping
    const categoryMap = {
        'visual': { icon: 'fa-eye', label: 'Visual Impairment', color: 'visual-icon' },
        'hearing': { icon: 'fa-deaf', label: 'Hearing Impairment', color: 'hearing-icon' },
        'physical': { icon: 'fa-wheelchair', label: 'Physical/Mobility', color: 'physical-icon' },
        'cognitive': { icon: 'fa-brain', label: 'Neurodiverse/Cognitive', color: 'cognitive-icon' }
    };
    
    // Create display elements
    disabilityTypes.forEach(type => {
        const category = categoryMap[type];
        if (category) {
            const categoryElement = document.createElement('div');
            categoryElement.className = 'category-card selected';
            categoryElement.innerHTML = `
                <div class="category-icon ${category.color}">
                    <i class="fas ${category.icon}"></i>
                </div>
                <div class="category-text">
                    ${category.label}
                </div>
            `;
            displayContainer.appendChild(categoryElement);
        }
    });
}

function updateDisplayAccessibilityOptions(accessibilityOptions) {
    const displayContainer = document.getElementById('display-accessibility-options');
    if (!displayContainer) return;
    
    console.log('Updating display accessibility options:', accessibilityOptions);
    
    // Clear current display
    displayContainer.innerHTML = '';
    
    if (!accessibilityOptions || accessibilityOptions.length === 0) {
        displayContainer.innerHTML = '<p style="color: #6c757d; font-style: italic;">No accessibility options selected</p>';
        return;
    }
    
    // Option mapping
    const optionMap = {
        'wheelchair': { icon: 'fa-wheelchair', label: 'Wheelchair-accessible' },
        'remote': { icon: 'fa-home', label: 'Remote work' },
        'flexible': { icon: 'fa-clock', label: 'Flexible hours' },
        'sign': { icon: 'fa-american-sign-language-interpreting', label: 'Sign language' },
        'assistive': { icon: 'fa-assistive-listening-systems', label: 'Assistive tech' },
        'assistant': { icon: 'fa-hands-helping', label: 'Personal assistant' }
    };
    
    // Create display elements
    accessibilityOptions.forEach(option => {
        const optionData = optionMap[option];
        if (optionData) {
            const optionElement = document.createElement('div');
            optionElement.className = 'accessibility-icon';
            optionElement.innerHTML = `
                <div class="icon-circle active">
                    <i class="fas ${optionData.icon}"></i>
                </div>
                <div class="icon-label">${optionData.label}</div>
            `;
            displayContainer.appendChild(optionElement);
        }
    });
}

function debugPreferences() {
    console.log('🔍 DIAGNOSTIC REPORT:');
    
    // Check modal existence
    const modal = document.getElementById('preferences-modal');
    console.log('Modal exists:', !!modal);
    
    // Check category cards
    const categoryCards = document.querySelectorAll('.category-card');
    console.log('Category cards found:', categoryCards.length);
    
    categoryCards.forEach((card, index) => {
        console.log(`Category ${index}:`, {
            element: card,
            dataCategory: card.getAttribute('data-category'),
            hasSelected: card.classList.contains('selected'),
            classList: Array.from(card.classList),
            styles: {
                borderColor: card.style.borderColor,
                background: card.style.background
            }
        });
    });
    
    // Check accessibility icons
    const iconCircles = document.querySelectorAll('.icon-circle');
    console.log('Icon circles found:', iconCircles.length);
    
    iconCircles.forEach((circle, index) => {
        console.log(`Icon ${index}:`, {
            element: circle,
            dataOption: circle.getAttribute('data-option'),
            hasActive: circle.classList.contains('active'),
            classList: Array.from(circle.classList),
            styles: {
                background: circle.style.background,
                color: circle.style.color,
                borderColor: circle.style.borderColor
            }
        });
    });
    
    // Check if handlers are attached (this will be visible in behavior)
    console.log('Click a category or icon now to test handlers...');
}

// Load saved preferences into modal
// Fixed loadSavedPreferences function
function loadSavedPreferences() {
    if (!profileData.hiring_preferences) {
        console.log('No saved preferences to load');
        return;
    }
    
    const preferences = profileData.hiring_preferences;
    console.log('📥 Loading saved preferences:', preferences);
    
    // Set PWD toggle
    const hirePwdToggle = document.getElementById('hirePwd');
    if (hirePwdToggle) {
        hirePwdToggle.checked = preferences.open_to_pwd || false;
        console.log('Set PWD toggle:', preferences.open_to_pwd);
    }
    
    // Set saved disability types with proper styling
    if (preferences.disability_types && Array.isArray(preferences.disability_types)) {
        console.log('Loading disability types:', preferences.disability_types);
        
        preferences.disability_types.forEach(type => {
            const categoryCard = document.querySelector(`.category-card[data-category="${type}"]`);
            if (categoryCard) {
                categoryCard.classList.add('selected');
                categoryCard.style.borderColor = 'var(--primary)';
                categoryCard.style.background = 'rgba(37, 113, 128, 0.05)';
                console.log('✅ Loaded and styled disability type:', type);
            } else {
                console.log('❌ Could not find category card for:', type);
            }
        });
    }
    
    // Set saved accessibility options with proper styling
    if (preferences.accessibility_options && Array.isArray(preferences.accessibility_options)) {
        console.log('Loading accessibility options:', preferences.accessibility_options);
        
        preferences.accessibility_options.forEach(option => {
            const iconCircle = document.querySelector(`.icon-circle[data-option="${option}"]`);
            if (iconCircle) {
                iconCircle.classList.add('active');
                iconCircle.style.background = 'var(--primary)';
                iconCircle.style.color = 'white';
                iconCircle.style.borderColor = 'var(--primary)';
                console.log('✅ Loaded and styled accessibility option:', option);
            } else {
                console.log('❌ Could not find icon circle for:', option);
            }
        });
    }
    
    console.log('✅ Finished loading saved preferences');
}

// Clear preference selections
function clearPreferencesSelection() {
    // Clear category selections
    const categoryCards = document.querySelectorAll('.category-card');
    categoryCards.forEach(card => {
        card.classList.remove('selected');
    });
    
    // Clear accessibility selections
    const iconCircles = document.querySelectorAll('.icon-circle');
    iconCircles.forEach(circle => {
        circle.classList.remove('active');
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Phase 2 - Enhanced Profile System Loading...');
    
    // Load profile data first
    loadProfileData();
    
    // Initialize UI components
    initializeUIComponents();
    
    console.log('✅ Phase 2 - All systems initialized successfully!');
});

// Initialize all UI components (modals, toggles, etc.)
function initializeUIComponents() {
    // Toggle sidebar
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');
    
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
    
    // Logo upload preview
    const logoInput = document.getElementById('logo-input');
    const logoImg = document.getElementById('logo-img');
    const modalLogoImg = document.getElementById('modal-logo-img');
    const logoPlaceholder = document.getElementById('logo-placeholder');
    const modalLogoPlaceholder = document.getElementById('modal-logo-placeholder');
    const uploadLogoBtn = document.getElementById('upload-logo-btn');
    const removeLogoBtn = document.getElementById('remove-logo-btn');
    
    if (uploadLogoBtn) {
        uploadLogoBtn.addEventListener('click', () => {
            logoInput.click();
        });
    }
    
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    showToast('Please select a valid image file', 'error');
                    return;
                }
                
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('File size must be less than 2MB', 'error');
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    modalLogoImg.src = e.target.result;
                    modalLogoImg.style.display = 'block';
                    modalLogoPlaceholder.style.display = 'none';
                    
                    // Reset removal flag when new file is selected
                    logoRemovalFlag = false;
                }
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    if (removeLogoBtn) {
        removeLogoBtn.addEventListener('click', () => {
            // Clear preview
            modalLogoImg.src = '';
            modalLogoImg.style.display = 'none';
            modalLogoPlaceholder.style.display = 'block';
            logoInput.value = '';
            
            // Set removal flag
            logoRemovalFlag = true;
            
            showToast('Logo marked for removal', 'info');
        });
    }
    
    // Password toggle
    const passwordInput = document.getElementById('password');
    const passwordToggle = document.getElementById('password-toggle');
    
    if (passwordToggle) {
        passwordToggle.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const passwordToggleIcon = passwordToggle.querySelector('i');
            if (type === 'text') {
                passwordToggleIcon.classList.remove('fa-eye');
                passwordToggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordToggleIcon.classList.remove('fa-eye-slash');
                passwordToggleIcon.classList.add('fa-eye');
            }
        });
    }
    
    // Initialize all modal functionality
    initializeModals();
    
    // Initialize password strength
    initializePasswordStrength();
    
    // Initialize preferences handlers
    initializePreferencesHandlers();
    
    // Prevent form submission
    const profileForm = document.getElementById('company-profile-form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });
    }
    
    console.log('✅ All UI components initialized');
}

// Modal functionality with Phase 2 enhancements
function initializeModals() {
    // Initialize saving functionality
    initializeIdentitySaving();
    initializeContactSaving();
    
    // Identity Modal
    document.getElementById('edit-identity')?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('identity-modal');
    });
    
    document.getElementById('close-identity-modal')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('identity-modal');
    });
    
    document.getElementById('cancel-identity')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('identity-modal');
    });
    
    // Contact Modal
    document.getElementById('edit-contact')?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('contact-modal');
    });
    
    document.getElementById('close-contact-modal')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('contact-modal');
    });
    
    document.getElementById('cancel-contact')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('contact-modal');
    });
    
    // Logo Description Modal
    document.getElementById('edit-logo-description')?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('logo-description-modal');
    });
    
    document.getElementById('close-logo-description-modal')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('logo-description-modal');
    });
    
    document.getElementById('cancel-logo-description')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('logo-description-modal');
    });
    
    // PHASE 3: Logo/Description Save Handler
    document.getElementById('save-logo-description')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const saveBtn = this;
        const aboutUs = document.getElementById('aboutUs').value.trim();
        const whyJoinUs = document.getElementById('whyJoinUs').value.trim();
        const logoInput = document.getElementById('logo-input');
        const modalLogoImg = document.getElementById('modal-logo-img');
        
        // Client-side validation
        const errors = [];
        if (!aboutUs) {
            errors.push('Company description is required');
        } else if (aboutUs.length < 50) {
            errors.push('Company description must be at least 50 characters long');
        }
        
        if (whyJoinUs && whyJoinUs.length < 30) {
            errors.push('Why Join Us section must be at least 30 characters long if provided');
        }
        
        if (errors.length > 0) {
            showToast(errors[0], 'error');
            return;
        }
        
        // Show loading state
        saveBtn.classList.add('loading');
        saveBtn.disabled = true;
        
        try {
            // Prepare FormData for file upload
            const formData = new FormData();
            formData.append('company_description', aboutUs);
            formData.append('why_join_us', whyJoinUs);
            
            // Handle logo upload or removal
            if (logoInput.files && logoInput.files[0]) {
                // New logo file selected
                formData.append('logo', logoInput.files[0]);
            } else if (logoRemovalFlag) {
                // Logo was explicitly removed
                formData.append('remove_logo', 'true');
            }
            
            const response = await fetch('../../backend/employer/update_company_description.php', {
                method: 'POST',
                body: formData // Don't set Content-Type header for FormData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update displayed values
                document.getElementById('display-about-us').textContent = result.data.company_description;
                document.getElementById('display-why-join-us').textContent = result.data.why_join_us;
                
                // Update logo display
                const logoImg = document.getElementById('logo-img');
                const logoPlaceholder = document.getElementById('logo-placeholder');
                
                if (result.data.company_logo_path) {
                    const logoPath = `../../${result.data.company_logo_path}`;
                    logoImg.src = logoPath;
                    logoImg.style.display = 'block';
                    logoPlaceholder.style.display = 'none';
                } else {
                    logoImg.style.display = 'none';
                    logoPlaceholder.style.display = 'block';
                }
                
                // Update progress bar if provided
                if (result.data.completion_percentage !== undefined) {
                    updateProgressBarWithValue(result.data.completion_percentage);
                    updateProgressItems();
                }
                
                // Update local profile data
                profileData.company_description = {
                    ...profileData.company_description,
                    company_description: result.data.company_description,
                    why_join_us: result.data.why_join_us,
                    company_logo_path: result.data.company_logo_path
                };
                
                // Clear logo input and reset flags
                logoInput.value = '';
                logoRemovalFlag = false;
                
                closeModal('logo-description-modal');

let message = 'Description updated';  // <-- PINALITAN
if (result.data.logo_uploaded) {
    message = 'Description & logo updated';  // <-- PINALITAN
} else if (result.data.logo_removed) {
    message = 'Description updated, logo removed';  // <-- PINALITAN
}
showToast(message);
                
            } else {
                if (result.errors && result.errors.length > 0) {
                    showToast(result.errors[0], 'error');
                } else {
                    showToast(result.message || 'Failed to update company description', 'error');
                }
            }
            
        } catch (error) {
            console.error('Error updating company description:', error);
            showToast('Network error occurred. Please try again.', 'error');
        } finally {
            saveBtn.classList.remove('loading');
            saveBtn.disabled = false;
        }
    });
    
    // Preferences Modal
    document.getElementById('edit-preferences')?.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('🔓 Opening preferences modal...');
        
        // Clear all selections first
        clearAndReloadPreferences();
        
        // Re-initialize handlers to ensure they work
        setTimeout(() => {
            initializePreferencesHandlers();
            
            // Load saved preferences if available
            if (profileData.hiring_preferences) {
                loadSavedPreferences();
            }
            
            openModal('preferences-modal');
        }, 100);
    });
    
    document.getElementById('close-preferences-modal')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('preferences-modal');
    });
    
    document.getElementById('cancel-preferences')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('preferences-modal');
    });
    
    // PHASE 4: Preferences Save Handler
    // TEMPORARY: Replace your save-preferences handler with this debug version
    document.getElementById('save-preferences')?.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const saveBtn = this;
        const hirePwd = document.getElementById('hirePwd').checked;
        
        // Collect selected disability categories
        const selectedCategories = [];
        const categoryCards = document.querySelectorAll('#preferences-modal .category-card.selected');
        categoryCards.forEach(card => {
            const categoryType = card.getAttribute('data-category');
            if (categoryType) {
                selectedCategories.push(categoryType);
            }
        });
        
        // Collect selected accessibility options
        const selectedAccessibilityOptions = [];
        const accessibilityIcons = document.querySelectorAll('#preferences-modal .icon-circle.active');
        accessibilityIcons.forEach(icon => {
            const optionType = icon.getAttribute('data-option');
            if (optionType) {
                selectedAccessibilityOptions.push(optionType);
            }
        });
        
        // Show loading state
        saveBtn.classList.add('loading');
        saveBtn.disabled = true;
        
        try {
            const requestData = {
                open_to_pwd: hirePwd,
                disability_types: selectedCategories,
                accessibility_options: selectedAccessibilityOptions,
                additional_accommodations: ''
            };
            
            const response = await fetch('../../backend/employer/update_hiring_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update displayed PWD preference
                document.getElementById('display-hire-pwd').textContent = result.data.open_to_pwd ? 'Yes' : 'No';
                
                // Update display sections immediately
                updateDisplayDisabilityTypes(result.data.disability_types);
                updateDisplayAccessibilityOptions(result.data.accessibility_options);
                
                // Update progress bar if provided
                if (result.data.completion_percentage !== undefined) {
                    updateProgressBarWithValue(result.data.completion_percentage);
                    updateProgressItems();
                }
                
                // Update local profile data
                if (!profileData.hiring_preferences) {
                    profileData.hiring_preferences = {};
                }
                profileData.hiring_preferences = {
                    ...profileData.hiring_preferences,
                    open_to_pwd: result.data.open_to_pwd,
                    disability_types: result.data.disability_types,
                    accessibility_options: result.data.accessibility_options
                };
                
                // Close modal and show success message
                closeModal('preferences-modal');
                showToast('Hiring preferences updated successfully!');
                
            } else {
                if (result.errors && result.errors.length > 0) {
                    showToast(result.errors[0], 'error');
                } else {
                    showToast(result.message || 'Failed to update hiring preferences', 'error');
                }
            }
            
        } catch (error) {
            console.error('Error updating hiring preferences:', error);
            showToast('Network error occurred. Please try again.', 'error');
        } finally {
            saveBtn.classList.remove('loading');
            saveBtn.disabled = false;
        }
    });
    
    // Social Media Modal
    document.getElementById('edit-social')?.addEventListener('click', (e) => {
        e.preventDefault();
        openModal('social-modal');
    });
    
    document.getElementById('close-social-modal')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('social-modal');
    });
    
    document.getElementById('cancel-social')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal('social-modal');
    });
    
    // PHASE 4: Social Media Save Handler
document.getElementById('save-social')?.addEventListener('click', async function(e) {
    e.preventDefault();
    
    const saveBtn = this;
    const website = document.getElementById('website').value.trim();
    const linkedin = document.getElementById('linkedin').value.trim();
    
    // Basic client-side URL validation
    const errors = [];
    const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    
    const urls = {
        'Website': website,
        'LinkedIn': linkedin
    };
    
    for (const [name, url] of Object.entries(urls)) {
        if (url && !urlPattern.test(url)) {
            errors.push(`Please enter a valid ${name} URL`);
            break;
        }
    }
    
    if (errors.length > 0) {
        showToast(errors[0], 'error');
        return;
    }
    
    // Show loading state
    saveBtn.classList.add('loading');
    saveBtn.disabled = true;
    
    try {
        const requestData = {
            website_url: website,
            linkedin_url: linkedin
        };
        
        const response = await fetch('../../backend/employer/update_social_links.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update displayed values
            document.getElementById('display-website').textContent = result.data.website_url || 'Not provided';
            document.getElementById('display-linkedin').textContent = result.data.linkedin_url || 'Not provided';
            
            // Update progress bar if provided
            if (result.data.completion_percentage !== undefined) {
                updateProgressBarWithValue(result.data.completion_percentage);
                updateProgressItems();
            }
            
            // Update local profile data
            profileData.social_links = {
                ...profileData.social_links,
                website_url: result.data.website_url,
                linkedin_url: result.data.linkedin_url
            };
            
            closeModal('social-modal');
            showToast('Social links updated');
            
        } else {
            if (result.errors && result.errors.length > 0) {
                showToast(result.errors[0], 'error');
            } else {
                showToast(result.message || 'Failed to update', 'error');
            }
        }
        
    } catch (error) {
        console.error('Error updating social links:', error);
        showToast('Network error', 'error');
    } finally {
        saveBtn.classList.remove('loading');
        saveBtn.disabled = false;
    }
});
    
    // Close modals when clicking outside
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                const modalId = modal.id;
                closeModal(modalId);
            }
        });
    });
    
    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="flex"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

// Initialize password strength visualization
function initializePasswordStrength() {
    const passwordField = document.getElementById('password');
    const strengthSegments = document.querySelectorAll('.strength-segment');
    const strengthLabel = document.querySelector('.strength-label');
    
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            strengthSegments.forEach(segment => {
                segment.classList.remove('active');
            });
            
            if (password.length >= 8) {
                strength++;
            }
            
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                strength++;
            }
            
            if (password.match(/\d/)) {
                strength++;
            }
            
            if (password.match(/[^a-zA-Z0-9]/)) {
                strength++;
            }
            
            for (let i = 0; i < strength; i++) {
                strengthSegments[i].classList.add('active');
            }
            
            strengthLabel.className = 'strength-label';
            
            if (strength === 0) {
                strengthLabel.textContent = 'Weak';
                strengthLabel.classList.add('weak');
            } else if (strength === 1 || strength === 2) {
                strengthLabel.textContent = 'Fair';
                strengthLabel.classList.add('fair');
            } else if (strength === 3) {
                strengthLabel.textContent = 'Good';
                strengthLabel.classList.add('good');
            } else {
                strengthLabel.textContent = 'Strong';
                strengthLabel.classList.add('strong');
            }
        });
    }
}