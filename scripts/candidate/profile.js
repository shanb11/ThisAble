document.addEventListener('DOMContentLoaded', function() {
    // Add dynamic styles for notifications, modals, and animations
    addDynamicStyles();
    
    // Initialize all UI components
    initializeSidebar();
    initializeProfileActions();
    initializeEditForms();
    initializeEducationExperience();
    initializeSkills();
    // initializeResumeButtons();
    //initializeResumeUpload();
    initializeAccessibilityPanel();
    initializeProfileImageUploads();
    initializeProfileCompletionInteractions(); // ADD THIS LINE

    
    // Initialize keyboard navigation for accessibility
    initializeKeyboardNavigation();
    
    /**
     * Add dynamic styles for notifications and modals
     */
    function addDynamicStyles() {
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            /* Notification Styles */
            .notification {
                position: fixed;
                bottom: 20px;
                left: 20px;
                background-color: white;
                border-radius: 8px;
                padding: 15px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                z-index: 1000;
                transform: translateY(100px);
                opacity: 0;
                transition: all 0.3s;
                max-width: 350px;
            }
            
            .notification.visible {
                transform: translateY(0);
                opacity: 1;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .notification.success .notification-content i {
                color: #4CAF50;
            }
            
            .notification.error .notification-content i {
                color: #F44336;
            }
            
            .notification-close {
                background: none;
                border: none;
                color: #999;
                cursor: pointer;
                padding: 5px;
            }
            
            /* Modal Styles */
            .confirmation-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .confirmation-modal.visible {
                opacity: 1;
            }
            
            .modal-content {
                background-color: white;
                border-radius: 8px;
                padding: 25px;
                width: 90%;
                max-width: 450px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .modal-content h3 {
                font-family: 'Poppins', sans-serif;
                color: #257180;
                margin-bottom: 15px;
            }
            
            .modal-content p {
                margin-bottom: 20px;
                line-height: 1.5;
                color: #666666;
            }
            
            .modal-actions {
                display: flex;
                justify-content: flex-end;
                gap: 15px;
            }
            
            .delete-confirm-btn {
                background-color: #F44336 !important;
                color: white !important;
            }
            
            /* Upload Area Styles */
            .upload-area.dragover {
                border-color: #257180;
                background-color: rgba(37, 113, 128, 0.1);
            }
            
            /* Tips modal styles */
            .tips-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .tips-modal.visible {
                opacity: 1;
            }
            
            .tips-content {
                background-color: white;
                border-radius: 8px;
                padding: 25px;
                width: 90%;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .tips-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .tips-header h2 {
                font-family: 'Poppins', sans-serif;
                color: #257180;
                font-size: 20px;
                margin: 0;
            }
            
            .tips-close {
                background: none;
                border: none;
                font-size: 20px;
                color: #999;
                cursor: pointer;
            }
            
            .tips-section {
                margin-bottom: 20px;
            }
            
            .tips-section h3 {
                font-family: 'Poppins', sans-serif;
                color: #257180;
                font-size: 16px;
                margin-bottom: 10px;
            }
            
            .tips-list {
                list-style-type: none;
                padding: 0;
            }
            
            .tips-list li {
                padding: 10px 0;
                border-bottom: 1px solid #f0f0f0;
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }
            
            .tips-list li:last-child {
                border-bottom: none;
            }
            
            .tips-list li i {
                color: #FD8B51;
                font-size: 16px;
                margin-top: 2px;
            }
            
            .tips-actions {
                display: flex;
                justify-content: flex-end;
                margin-top: 20px;
            }
            
            /* Ensure the edit forms are hidden by default with !important */
            .edit-form {
                display: none !important;
            }
            
            /* When we want to show a form */
            .edit-form.active {
                display: block !important;
            }
            
            /* Image upload preview animation */
            @keyframes fadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }
            
            .fadeIn {
                animation: fadeIn 0.5s ease-in-out;
            }
        `;
        
        document.head.appendChild(styleElement);
    }
    
    /**
     * Initialize sidebar functionality
     */
    function initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const toggleIcon = document.getElementById('toggle-icon');
        
        if (toggleBtn && sidebar && toggleIcon) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
            });
            
            // Check localStorage for sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }
            
            // Save sidebar state to localStorage
            sidebar.addEventListener('transitionend', function() {
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }
    }
    
    /**
     * Initialize profile actions
     */
    function initializeProfileActions() {
        const editProfileBtn = document.getElementById('edit-profile-btn');
        
        if (editProfileBtn) {
            editProfileBtn.addEventListener('click', function() {
                // Find the personal section edit button and click it
                const personalEditBtn = document.querySelector('.edit-section-btn[data-section="personal"]');
                if (personalEditBtn) {
                    // Scroll to the section first
                    const personalSection = personalEditBtn.closest('.profile-section');
                    if (personalSection) {
                        personalSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Click the edit button after scrolling
                        setTimeout(() => {
                            personalEditBtn.click();
                        }, 500);
                    }
                }
            });
        }
    }
    
   /**
     * Initialize profile image uploads
     */
    function initializeProfileImageUploads() {
        // Profile picture upload
        const profilePicBtn = document.querySelector('.edit-picture-btn');
        const profileFileInput = document.getElementById('profile-file-input');
        const profileImg = document.getElementById('profile-photo');
        
        if (profilePicBtn && profileFileInput && profileImg) {
            // Open file dialog when the edit button is clicked
            profilePicBtn.addEventListener('click', function() {
                profileFileInput.click();
            });
            
            // Handle file selection
            profileFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    uploadProfileImage(file, 'profile', profileImg);
                }
            });
        }
        
        // Cover photo upload
        const coverPicBtn = document.querySelector('.edit-cover-btn');
        const coverFileInput = document.getElementById('cover-file-input');
        const coverImg = document.getElementById('cover-photo');
        
        if (coverPicBtn && coverFileInput && coverImg) {
            // Open file dialog when the edit button is clicked
            coverPicBtn.addEventListener('click', function() {
                coverFileInput.click();
            });
            
            // Handle file selection
            coverFileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    uploadProfileImage(file, 'cover', coverImg);
                }
            });
        }
    }

    /**
     * Upload profile image to server
     */
    function uploadProfileImage(file, imageType, imgElement) {
        // Validate file type
        if (!file.type.match('image.*')) {
            showNotification('Please select an image file (JPEG, PNG, GIF, WebP).', 'error');
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('Please select an image smaller than 5MB.', 'error');
            return;
        }
        
        // Show loading notification
        showNotification(`Uploading ${imageType} photo...`, 'info');
        
        // Create FormData object
        const formData = new FormData();
        formData.append(imageType + '_image', file);
        formData.append('image_type', imageType);
        
        // Send AJAX request
        fetch('../../backend/candidate/upload_profile_image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server responded with status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update image source with the uploaded image
                imgElement.src = data.image_url + '?t=' + new Date().getTime(); // Add timestamp to prevent caching
                imgElement.classList.add('fadeIn');
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    imgElement.classList.remove('fadeIn');
                }, 500);
                
                // Show success notification
                showNotification(data.message, 'success');
            } else {
                // Show error notification
                showNotification(data.message || 'Upload failed', 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showNotification('An error occurred while uploading the image: ' + error.message, 'error');
        });
    }
    
    /**
     * Initialize edit forms
    */
    function initializeEditForms() {
        // Edit section buttons
        const editSectionBtns = document.querySelectorAll('.edit-section-btn');
        
        editSectionBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const section = this.getAttribute('data-section');
                toggleEditForm(section);
            });
        });
        
        // Cancel buttons
        const cancelBtns = document.querySelectorAll('.cancel-btn');
        
        cancelBtns.forEach(btn => {
            if (btn.getAttribute('data-section')) {
                btn.addEventListener('click', function() {
                    const section = this.getAttribute('data-section');
                    toggleEditForm(section);
                });
            }
        });
        
        // DO NOT add save button event listeners here - profile-ajax.js handles them
    }
    
    /**
     * Toggle edit form visibility
     */
    function toggleEditForm(section) {
        const form = document.getElementById(`${section}-edit-form`);
        
        if (form) {
            // Hide all forms first
            document.querySelectorAll('.edit-form').forEach(f => {
                f.classList.remove('active');
            });
            
            // Toggle current form
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                form.classList.add('active');
                
                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Focus on first input
                const firstInput = form.querySelector('input, select, textarea');
                if (firstInput) {
                    setTimeout(() => {
                        firstInput.focus();
                    }, 300);
                }
            }
        }
    }
    
    /**
     * Update UI from form values
     */
    function updateUIFromForm(section) {
        // This is a simplified implementation - in a real app, you would save to backend
        // Here we just update the UI to reflect the changes
        
        switch (section) {
            case 'personal':
                // Update personal info
                const fullName = document.getElementById('full-name')?.value;
                const email = document.getElementById('email')?.value;
                const phone = document.getElementById('phone')?.value;
                const location = document.getElementById('location')?.value;
                const pwdId = document.getElementById('pwd-id')?.value;
                const disabilityType = document.getElementById('disability-type')?.options[
                    document.getElementById('disability-type')?.selectedIndex
                ]?.text;
                
                // Update UI elements
                if (fullName) {
                    document.querySelector('.profile-details h1').textContent = fullName;
                    document.querySelector('.info-item:nth-child(1) .info-value').textContent = fullName;
                }
                if (email) {
                    document.querySelector('.info-item:nth-child(2) .info-value').textContent = email;
                }
                if (phone) {
                    document.querySelector('.info-item:nth-child(3) .info-value').textContent = phone;
                }
                if (location) {
                    document.querySelector('.info-item:nth-child(4) .info-value').textContent = location;
                }
                if (pwdId) {
                    document.querySelector('.info-item:nth-child(5) .info-value').textContent = pwdId;
                }
                if (disabilityType) {
                    document.querySelector('.info-item:nth-child(6) .info-value').textContent = disabilityType;
                }
                break;
                
            case 'skills':
                // In a real app, you would update the skills based on the form values
                // Here we just update the displayed skills based on the editable containers
                updateSkillsFromForm();
                break;
                
            case 'preferences':
                // Update work preferences
                const workStyle = document.getElementById('work-style')?.options[
                    document.getElementById('work-style')?.selectedIndex
                ]?.text;
                const jobType = document.getElementById('job-type')?.options[
                    document.getElementById('job-type')?.selectedIndex
                ]?.text;
                const salaryRange = document.getElementById('salary-range')?.options[
                    document.getElementById('salary-range')?.selectedIndex
                ]?.text;
                const availability = document.getElementById('availability')?.options[
                    document.getElementById('availability')?.selectedIndex
                ]?.text;
                
                // Update UI elements
                if (workStyle) {
                    document.querySelector('.preference-item:nth-child(1) .preference-value').textContent = workStyle;
                }
                if (jobType) {
                    document.querySelector('.preference-item:nth-child(2) .preference-value').textContent = jobType;
                }
                if (salaryRange) {
                    document.querySelector('.preference-item:nth-child(3) .preference-value').textContent = salaryRange;
                }
                if (availability) {
                    document.querySelector('.preference-item:nth-child(4) .preference-value').textContent = availability;
                }
                break;
                
            case 'accessibility':
                // In a real app, you would update the accessibility needs
                // Here we just simulate updating the UI
                updateAccessibilityFromForm();
                break;
                
            default:
                break;
        }
    }
    
    /**
     * Update skills from form
     */
    function updateSkillsFromForm() {
        // Get the skill containers
        const editableTechnicalSkills = document.getElementById('technical-skills-container');
        const editableSoftSkills = document.getElementById('soft-skills-container');
        const editableLanguages = document.getElementById('languages-container');
        
        // Get the display containers
        const displaySkillsContainers = document.querySelectorAll('.skill-category .skill-tags:not(.editable)');
        
        if (editableTechnicalSkills && editableSoftSkills && editableLanguages && displaySkillsContainers.length >= 3) {
            // Update technical skills
            const technicalSkills = Array.from(editableTechnicalSkills.querySelectorAll('.skill-tag'))
                .map(tag => tag.textContent.replace(/\s*✕\s*$/, ''));
            
            // Update soft skills
            const softSkills = Array.from(editableSoftSkills.querySelectorAll('.skill-tag'))
                .map(tag => tag.textContent.replace(/\s*✕\s*$/, ''));
            
            // Update languages
            const languages = Array.from(editableLanguages.querySelectorAll('.skill-tag'))
                .map(tag => tag.textContent.replace(/\s*✕\s*$/, ''));
            
            // Update display containers
            updateSkillsContainer(displaySkillsContainers[0], technicalSkills);
            updateSkillsContainer(displaySkillsContainers[1], softSkills);
            updateSkillsContainer(displaySkillsContainers[2], languages);
        }
    }
    
    /**
     * Update a skills container with new skills
     */
    function updateSkillsContainer(container, skills) {
        // Clear the container
        container.innerHTML = '';
        
        // Add the new skills
        skills.forEach(skill => {
            const skillTag = document.createElement('span');
            skillTag.className = 'skill-tag';
            skillTag.textContent = skill;
            container.appendChild(skillTag);
        });
    }
    
    /**
     * Update accessibility from form
     */
    function updateAccessibilityFromForm() {
        // Get the form values
        const screenReader = document.getElementById('need-screen-reader')?.checked;
        const largePrint = document.getElementById('need-large-print')?.checked;
        const highContrast = document.getElementById('need-high-contrast')?.checked;
        const keyboard = document.getElementById('need-keyboard')?.checked;
        const captions = document.getElementById('need-captions')?.checked;
        const clearLanguage = document.getElementById('need-clear-language')?.checked;
        const customNeeds = document.getElementById('custom-needs')?.value;
        const discloseApplication = document.getElementById('edit-disclose-application')?.checked;
        const discloseInterview = document.getElementById('edit-disclose-interview')?.checked;
        
        // Get the display containers
        const accessibilityTagsContainer = document.querySelector('.accessibility-tags-container');
        const customNeedsContainer = document.querySelector('.custom-needs p');
        const disclosurePreferences = document.querySelectorAll('.disclosure-option input');
        
        if (accessibilityTagsContainer) {
            // Clear the container
            accessibilityTagsContainer.innerHTML = '';
            
            // Add the new tags
            if (screenReader) {
                addAccessibilityTag(accessibilityTagsContainer, 'low-vision', 'Screen reader compatible documents');
            }
            if (largePrint) {
                addAccessibilityTag(accessibilityTagsContainer, 'text-height', 'Large print materials');
            }
            if (highContrast) {
                addAccessibilityTag(accessibilityTagsContainer, 'palette', 'High contrast interfaces');
            }
            if (keyboard) {
                addAccessibilityTag(accessibilityTagsContainer, 'keyboard', 'Keyboard navigation support');
            }
            if (captions) {
                addAccessibilityTag(accessibilityTagsContainer, 'closed-captioning', 'Closed captions for videos');
            }
            if (clearLanguage) {
                addAccessibilityTag(accessibilityTagsContainer, 'align-left', 'Simple, clear language');
            }
        }
        
        // Update custom needs
        if (customNeedsContainer && customNeeds) {
            customNeedsContainer.textContent = customNeeds;
        }
        
        // Update disclosure preferences
        if (disclosurePreferences.length >= 2) {
            disclosurePreferences[0].checked = discloseApplication;
            disclosurePreferences[1].checked = discloseInterview;
        }
    }
    
    /**
     * Add an accessibility tag to a container
     */
    function addAccessibilityTag(container, icon, text) {
        const tag = document.createElement('span');
        tag.className = 'accessibility-tag';
        tag.innerHTML = `<i class="fas fa-${icon}"></i> ${text}`;
        container.appendChild(tag);
    }
    
    /**
     * Initialize education and experience sections
     */
    function initializeEducationExperience() {
        // Add buttons
        const addEducationBtn = document.getElementById('add-education-btn');
        const addEducationEmptyBtn = document.getElementById('add-education-empty-btn');
        const addExperienceBtn = document.getElementById('add-experience-btn');
        const addExperienceEmptyBtn = document.getElementById('add-experience-empty-btn');
        
        // Add event listeners
        if (addEducationBtn) {
            addEducationBtn.addEventListener('click', function() {
                prepareFormForAdd('education');
            });
        }
        
        if (addEducationEmptyBtn) {
            addEducationEmptyBtn.addEventListener('click', function() {
                prepareFormForAdd('education');
            });
        }
        
        if (addExperienceBtn) {
            addExperienceBtn.addEventListener('click', function() {
                prepareFormForAdd('experience');
            });
        }
        
        if (addExperienceEmptyBtn) {
            addExperienceEmptyBtn.addEventListener('click', function() {
                prepareFormForAdd('experience');
            });
        }
        
        // Edit buttons
        const editItemBtns = document.querySelectorAll('.edit-item-btn');
        
        editItemBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const id = this.getAttribute('data-id');
                
                prepareFormForEdit(type, id);
            });
        });
        
        // Delete buttons
        const deleteItemBtns = document.querySelectorAll('.delete-item-btn');
        
        deleteItemBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const id = this.getAttribute('data-id');
                
                confirmDelete(type, id, this);
            });
        });
    }
    
    /**
     * Prepare a form for adding a new item
     */
    function prepareFormForAdd(type) {
        const form = document.getElementById(`${type}-edit-form`);
        
        if (form) {
            // Clear form inputs
            form.querySelectorAll('input, textarea').forEach(input => {
                input.value = '';
            });
            
            form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Add data-mode attribute for form submission
            form.setAttribute('data-mode', 'add');
            
            // Show form
            form.classList.add('active');
            
            // Update save button text
            const saveBtn = form.querySelector('.save-btn');
            if (saveBtn) {
                saveBtn.innerHTML = `<i class="fas fa-plus"></i> Add ${type.charAt(0).toUpperCase() + type.slice(1)}`;
            }
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Focus on first input
            const firstInput = form.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                }, 300);
            }
        }
    }
    
    /**
     * Prepare a form for editing an existing item
     */
    function prepareFormForEdit(type, id) {
        const form = document.getElementById(`${type}-edit-form`);
        
        if (form) {
            // In a real app, you would fetch data from an API
            // For demo, we use hardcoded values
            if (type === 'education') {
                form.querySelector('#education-degree').value = 'Bachelor of Science in Computer Science';
                form.querySelector('#education-institution').value = 'Cavite State University';
                form.querySelector('#education-location').value = 'Indang, Cavite';
                form.querySelector('#education-start').value = '2017-09';
                form.querySelector('#education-end').value = '2021-06';
                form.querySelector('#education-description').value = 'Graduated with honors. Focus on web development and user interface design.';
            } else if (type === 'experience') {
                form.querySelector('#experience-title').value = 'Junior Web Developer';
                form.querySelector('#experience-company').value = 'Tech Solutions Inc.';
                form.querySelector('#experience-location').value = 'Makati City, Philippines';
                form.querySelector('#experience-start').value = '2022-01';
                form.querySelector('#experience-end').value = '2024-12';
                form.querySelector('#experience-description').value = 'Developed responsive web applications using HTML, CSS, and JavaScript\nCollaborated with designers to implement user interfaces\nOptimized website performance and accessibility';
            }
            
            // Add data-mode attribute for form submission
            form.setAttribute('data-mode', 'edit');
            form.setAttribute('data-id', id);
            
            // Show form
            form.classList.add('active');
            
            // Update save button text
            const saveBtn = form.querySelector('.save-btn');
            if (saveBtn) {
                saveBtn.innerHTML = `<i class="fas fa-save"></i> Save Changes`;
            }
            
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Focus on first input
            const firstInput = form.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                }, 300);
            }
        }
    }
    
    /**
     * Confirm deletion of an item
     */
    function confirmDelete(type, id, button) {
        // Create confirmation modal
        const modal = document.createElement('div');
        modal.className = 'confirmation-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete this ${type}? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button class="btn cancel-btn">Cancel</button>
                    <button class="btn delete-confirm-btn">Delete</button>
                </div>
            </div>
        `;
        
        // Add to body
        document.body.appendChild(modal);
        
        // Show modal with delay for transition
        setTimeout(() => {
            modal.classList.add('visible');
        }, 10);
        
        // Cancel button
        modal.querySelector('.cancel-btn').addEventListener('click', () => {
            modal.classList.remove('visible');
            setTimeout(() => {
                modal.remove();
            }, 300);
        });
        
        // Delete confirm button
        modal.querySelector('.delete-confirm-btn').addEventListener('click', () => {
            // In a real app, you would delete via API
            // For demo, just remove the element
            deleteItem(type, id, button);
            
            // Close modal
            modal.classList.remove('visible');
            setTimeout(() => {
                modal.remove();
            }, 300);
        });
    }
    
    /**
     * Delete an item
     */
    function deleteItem(type, id, button) {
        const item = button.closest(`.${type}-item`);
        
        if (item) {
            // Fade out and remove
            item.style.opacity = '0';
            item.style.transform = 'translateY(-20px)';
            item.style.transition = 'opacity 0.3s, transform 0.3s';
            
            setTimeout(() => {
                item.remove();
                
                // Check if any items remain
                const list = document.querySelector(`.${type}-list`);
                const empty = document.getElementById(`${type}-empty`);
                
                if (list && empty && list.children.length === 0) {
                    empty.style.display = 'block';
                }
                
                // Show notification
                showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully!`, 'success');
            }, 300);
        }
    }

    // ADD THIS FUNCTION TO YOUR EXISTING profile.js FILE
    // Place it after your other function definitions (around line 800+ in your file)

    /**
     * Initialize profile completion interactions
     */
    function initializeProfileCompletionInteractions() {
        // Make incomplete sections clickable
        const incompleteItems = document.querySelectorAll('.completion-checklist li.incomplete');
        
        incompleteItems.forEach(item => {
            // Add hover cursor
            item.style.cursor = 'pointer';
            
            // Add click event
            item.addEventListener('click', function() {
                const sectionText = this.textContent.toLowerCase();
                
                // Determine which section to focus on based on the text
                if (sectionText.includes('personal information')) {
                    focusOnSection('personal');
                } else if (sectionText.includes('skills')) {
                    focusOnSection('skills');
                } else if (sectionText.includes('work preferences')) {
                    focusOnSection('preferences');
                } else if (sectionText.includes('accessibility needs')) {
                    focusOnSection('accessibility');
                } else if (sectionText.includes('education')) {
                    scrollToSection('education', () => {
                        const addBtn = document.getElementById('add-education-btn') || 
                                    document.getElementById('add-education-empty-btn');
                        if (addBtn) addBtn.click();
                    });
                } else if (sectionText.includes('work experience') || sectionText.includes('experience')) {
                    scrollToSection('experience', () => {
                        const addBtn = document.getElementById('add-experience-btn') || 
                                    document.getElementById('add-experience-empty-btn');
                        if (addBtn) addBtn.click();
                    });
                } else if (sectionText.includes('resume')) {
                    focusOnSection('resume');
                }
            });
            
            // Add visual feedback on hover
            item.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(253, 139, 81, 0.1)';
                this.style.borderRadius = '4px';
                this.style.paddingLeft = '12px';
                this.style.paddingRight = '8px';
                this.style.transform = 'translateX(4px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
                this.style.borderRadius = '';
                this.style.paddingLeft = '';
                this.style.paddingRight = '';
                this.style.transform = '';
            });
        });
        
        // Helper function to focus on a section
        function focusOnSection(sectionName) {
            // Find the edit button for the section
            const editBtn = document.querySelector(`.edit-section-btn[data-section="${sectionName}"]`);
            
            if (editBtn) {
                // Scroll to the section
                const section = editBtn.closest('.profile-section');
                if (section) {
                    section.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    
                    // Highlight the section briefly
                    section.style.transition = 'box-shadow 0.3s ease';
                    section.style.boxShadow = '0 0 20px rgba(253, 139, 81, 0.3)';
                    
                    setTimeout(() => {
                        section.style.boxShadow = '';
                    }, 2000);
                    
                    // Click the edit button after scrolling
                    setTimeout(() => {
                        editBtn.click();
                    }, 500);
                }
            }
        }
        
        // Helper function to scroll to section
        function scrollToSection(sectionName, callback) {
            // Find the section by looking for elements with specific IDs or classes
            let section = null;
            
            // Search by section headers
            const headers = document.querySelectorAll('.profile-section h2');
            headers.forEach(header => {
                if (header.textContent.toLowerCase().includes(sectionName)) {
                    section = header.closest('.profile-section');
                }
            });
            
            if (section) {
                section.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // Highlight the section
                section.style.transition = 'box-shadow 0.3s ease';
                section.style.boxShadow = '0 0 20px rgba(253, 139, 81, 0.3)';
                
                setTimeout(() => {
                    section.style.boxShadow = '';
                    if (callback) callback();
                }, 1000);
            }
        }
        
        // Add tooltip text to incomplete items
        incompleteItems.forEach(item => {
            item.title = 'Click to complete this section';
            item.setAttribute('aria-label', 'Click to complete this section');
        });
        
        // Add completion progress animation
        const progressBar = document.querySelector('.progress');
        if (progressBar) {
            // Animate progress bar on page load
            const targetWidth = progressBar.style.width;
            progressBar.style.width = '0%';
            
            setTimeout(() => {
                progressBar.style.width = targetWidth;
            }, 500);
        }
        
        // Refresh completion status periodically
        setInterval(checkForCompletionUpdates, 30000); // Check every 30 seconds
        
        function checkForCompletionUpdates() {
            const lastFormSubmission = localStorage.getItem('lastProfileUpdate');
            const lastCheck = localStorage.getItem('lastCompletionCheck') || '0';
            
            if (lastFormSubmission && parseInt(lastFormSubmission) > parseInt(lastCheck)) {
                refreshProfileCompletion();
                localStorage.setItem('lastCompletionCheck', Date.now().toString());
            }
        }
        
        function refreshProfileCompletion() {
            const completionStatus = document.querySelector('.profile-completion-status');
            if (completionStatus) {
                const refreshNote = document.createElement('small');
                refreshNote.style.color = '#666';
                refreshNote.style.fontSize = '11px';
                refreshNote.style.fontStyle = 'italic';
                refreshNote.textContent = 'Refresh the page to see updated completion status';
                
                completionStatus.appendChild(refreshNote);
                
                setTimeout(() => {
                    if (refreshNote.parentNode) {
                        refreshNote.remove();
                    }
                }, 5000);
            }
        }
    }
    
    /**
     * Initialize skills functionality
     */
    function initializeSkills() {
        // Add skill buttons
        const addSkillBtns = document.querySelectorAll('.add-skill-btn');
        
        addSkillBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                const inputId = category === 'technical' ? 'technical-skill-input' :
                              category === 'soft' ? 'soft-skill-input' : 'language-input';
                const containerId = category === 'technical' ? 'technical-skills-container' :
                                  category === 'soft' ? 'soft-skills-container' : 'languages-container';
                
                addSkill(inputId, containerId);
            });
        });
        
        // Input enter key press
        const skillInputs = document.querySelectorAll('.skill-input-container input');
        
        skillInputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Get the nearest add button and click it
                    const addBtn = this.nextElementSibling;
                    if (addBtn && addBtn.classList.contains('add-skill-btn')) {
                        addBtn.click();
                    }
                }
            });
        });
        
        // Remove skill buttons
        const removeSkillBtns = document.querySelectorAll('.remove-skill');
        
        removeSkillBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.remove();
            });
        });
    }
    
    /**
     * Add a skill
     */
    function addSkill(inputId, containerId) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        
        if (input && container && input.value.trim() !== '') {
            // Create skill tag
            const skillTag = document.createElement('span');
            skillTag.className = 'skill-tag';
            skillTag.innerHTML = `${input.value.trim()}<i class="fas fa-times remove-skill"></i>`;
            
            // Add to container
            container.appendChild(skillTag);
            
            // Add event listener to remove button
            const removeBtn = skillTag.querySelector('.remove-skill');
            removeBtn.addEventListener('click', function() {
                skillTag.remove();
            });
            
            // Clear input and focus
            input.value = '';
            input.focus();
        }
    }
    
    /**
     * Initialize resume buttons
     */
    // function initializeResumeButtons() {
    //     // Download resume button
    //     const downloadResumeBtn = document.querySelector('.download-resume-btn');
    //     if (downloadResumeBtn) {
    //         downloadResumeBtn.addEventListener('click', function() {
    //             // In a real app, this would be a real download link
    //             // For demo, we'll show a notification
    //             showNotification('Resume download started!', 'success');
                
    //             // Simulate download after a short delay
    //             setTimeout(() => {
    //                 // Create a dummy download link (this would be a real file in production)
    //                 const link = document.createElement('a');
    //                 link.href = '#';
    //                 link.download = 'Jowel_Dacara_Resume.pdf';
    //                 document.body.appendChild(link);
                    
    //                 // Try to make it work like a real download
    //                 try {
    //                     const event = new MouseEvent('click');
    //                     link.dispatchEvent(event);
    //                 } catch (e) {
    //                     console.log('Download simulation failed');
    //                 }
                    
    //                 document.body.removeChild(link);
    //             }, 500);
    //         });
    //     }
        
    //     // View resume button
    //     const viewResumeBtn = document.querySelector('.view-resume-btn');
    //     if (viewResumeBtn) {
    //         viewResumeBtn.addEventListener('click', function() {
    //             // In a real app, this would open the resume in a new tab or modal
    //             // For demo, we'll show a notification
    //             showNotification('Opening resume viewer...', 'success');
                
    //             // Simulate opening a new tab with the resume
    //             setTimeout(() => {
    //                 window.open('#', '_blank');
    //             }, 500);
    //         });
    //     }
        
    //     // Get optimization tips button
    //     const optimizeBtn = document.querySelector('.optimize-btn');
    //     if (optimizeBtn) {
    //         optimizeBtn.addEventListener('click', function() {
    //             showOptimizationTips();
    //         });
    //     }
    // }
    
    /**
     * Show resume optimization tips
     */
    // function showOptimizationTips() {
    //     // Create tips modal
    //     const modal = document.createElement('div');
    //     modal.className = 'tips-modal';
    //     modal.innerHTML = `
    //         <div class="tips-content">
    //             <div class="tips-header">
    //                 <h2>Resume Optimization Tips</h2>
    //                 <button class="tips-close" aria-label="Close tips">&times;</button>
    //             </div>
                
    //             <div class="tips-section">
    //                 <h3>Tailoring Your Resume</h3>
    //                 <ul class="tips-list">
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Customize your resume for each job application to match the specific requirements and keywords in the job description.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Move the most relevant skills and experiences to the top of your resume to grab the employer's attention.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Include measurable achievements rather than just listing responsibilities (e.g., "Increased website traffic by 40%" instead of "Managed website content").</span>
    //                     </li>
    //                 </ul>
    //             </div>
                
    //             <div class="tips-section">
    //                 <h3>Formatting Improvements</h3>
    //                 <ul class="tips-list">
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Use a clean, professional format with consistent spacing and alignment throughout the document.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Ensure your resume is screen reader friendly with proper headings and document structure.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Keep your resume to 1-2 pages maximum, focusing on your most recent and relevant experiences.</span>
    //                     </li>
    //                 </ul>
    //             </div>
                
    //             <div class="tips-section">
    //                 <h3>Keyword Optimization</h3>
    //                 <ul class="tips-list">
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Include industry-specific keywords and phrases from job descriptions to pass through Applicant Tracking Systems (ATS).</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Add a skills section that clearly lists both technical and soft skills relevant to your target positions.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Use standard section headings like "Experience," "Education," and "Skills" that ATS systems can easily recognize.</span>
    //                     </li>
    //                 </ul>
    //             </div>
                
    //             <div class="tips-section">
    //                 <h3>Accessibility Considerations</h3>
    //                 <ul class="tips-list">
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Use a readable font size (minimum 11pt) and a professional, easy-to-read font.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Ensure high contrast between text and background for better readability.</span>
    //                     </li>
    //                     <li>
    //                         <i class="fas fa-check-circle"></i>
    //                         <span>Add alt text to any graphics or charts included in your resume.</span>
    //                     </li>
    //                 </ul>
    //             </div>
                
    //             <div class="tips-actions">
    //                 <button class="btn primary-btn">Apply These Tips to My Resume</button>
    //             </div>
    //         </div>
    //     `;
        
    //     // Add to body
    //     document.body.appendChild(modal);
        
    //     // Show modal with delay for transition
    //     setTimeout(() => {
    //         modal.classList.add('visible');
    //     }, 10);
        
    //     // Close button
    //     modal.querySelector('.tips-close').addEventListener('click', () => {
    //         modal.classList.remove('visible');
    //         setTimeout(() => {
    //             modal.remove();
    //         }, 300);
        // });
        
    //     // Apply button
    //     modal.querySelector('.tips-actions .btn').addEventListener('click', () => {
    //         // In a real app, this would apply the tips to the resume
    //         // For demo, just show a notification
    //         showNotification('Tips will be applied to your resume by our AI assistant!', 'success');
            
    //         // Close modal
    //         modal.classList.remove('visible');
    //         setTimeout(() => {
    //             modal.remove();
    //         }, 300);
    //     });
    // }
    
    /**
     * Initialize accessibility panel
     */
    function initializeAccessibilityPanel() {
        const accessibilityToggle = document.querySelector('.accessibility-toggle');
        const accessibilityPanel = document.querySelector('.accessibility-panel');
        
        if (accessibilityToggle && accessibilityPanel) {
            // Toggle panel
            accessibilityToggle.addEventListener('click', function() {
                if (accessibilityPanel.style.display === 'block') {
                    accessibilityPanel.style.display = 'none';
                } else {
                    accessibilityPanel.style.display = 'block';
                }
            });
            
            // Close panel when clicking outside
            document.addEventListener('click', function(e) {
                if (accessibilityPanel.style.display === 'block' && 
                    !accessibilityPanel.contains(e.target) && 
                    e.target !== accessibilityToggle) {
                    accessibilityPanel.style.display = 'none';
                }
            });
            
            // Accessibility features
            initializeAccessibilityFeatures();
        }
    }
    
    /**
     * Initialize accessibility features
     */
    function initializeAccessibilityFeatures() {
        const highContrastToggle = document.getElementById('high-contrast');
        const reduceMotionToggle = document.getElementById('reduce-motion');
        const increaseFont = document.getElementById('increase-font');
        const decreaseFont = document.getElementById('decrease-font');
        const fontSizeValue = document.querySelector('.font-size-value');
        const screenReaderMode = document.getElementById('screen-reader-mode');
        const textSpacing = document.getElementById('text-spacing');
        
        // Initialize font size
        let fontSizePercent = 100;
        
        // Load saved preferences
        if (localStorage.getItem('highContrast') === 'true' && highContrastToggle) {
            document.body.classList.add('high-contrast');
            highContrastToggle.checked = true;
        }
        
        if (localStorage.getItem('reduceMotion') === 'true' && reduceMotionToggle) {
            document.body.classList.add('reduce-motion');
            reduceMotionToggle.checked = true;
        }
        
        if (localStorage.getItem('fontSize') && fontSizeValue) {
            fontSizePercent = parseInt(localStorage.getItem('fontSize'));
            updateFontSize();
        }
        
        if (localStorage.getItem('screenReaderMode') === 'true' && screenReaderMode) {
            document.body.classList.add('screen-reader-mode');
            screenReaderMode.checked = true;
        }
        
        if (localStorage.getItem('textSpacing') === 'true' && textSpacing) {
            document.body.classList.add('text-spacing');
            textSpacing.checked = true;
        }
        
        // High contrast mode
        if (highContrastToggle) {
            highContrastToggle.addEventListener('change', function() {
                document.body.classList.toggle('high-contrast');
                localStorage.setItem('highContrast', this.checked);
            });
        }
        
        // Reduce motion
        if (reduceMotionToggle) {
            reduceMotionToggle.addEventListener('change', function() {
                document.body.classList.toggle('reduce-motion');
                localStorage.setItem('reduceMotion', this.checked);
            });
        }
        
        // Font size controls
        if (increaseFont && decreaseFont && fontSizeValue) {
            increaseFont.addEventListener('click', function() {
                if (fontSizePercent < 150) {
                    fontSizePercent += 10;
                    updateFontSize();
                }
            });
            
            decreaseFont.addEventListener('click', function() {
                if (fontSizePercent > 90) {
                    fontSizePercent -= 10;
                    updateFontSize();
                }
            });
        }
        
        // Screen reader mode
        if (screenReaderMode) {
            screenReaderMode.addEventListener('change', function() {
                document.body.classList.toggle('screen-reader-mode');
                localStorage.setItem('screenReaderMode', this.checked);
            });
        }
        
        // Text spacing
        if (textSpacing) {
            textSpacing.addEventListener('change', function() {
                document.body.classList.toggle('text-spacing');
                localStorage.setItem('textSpacing', this.checked);
            });
        }
        
        // Update font size
        function updateFontSize() {
            // Update display
            if (fontSizeValue) {
                fontSizeValue.textContent = `${fontSizePercent}%`;
            }
            
            // Update body classes
            document.body.classList.remove('large-text', 'larger-text');
            
            if (fontSizePercent >= 120) {
                document.body.classList.add('larger-text');
            } else if (fontSizePercent >= 110) {
                document.body.classList.add('large-text');
            }
            
            // Save to localStorage
            localStorage.setItem('fontSize', fontSizePercent);
        }
    }
    
    /**
     * Initialize keyboard navigation
     */
    function initializeKeyboardNavigation() {
        // Global key events
        document.addEventListener('keydown', function(e) {
            // Escape key
            if (e.key === 'Escape') {
                handleEscapeKey();
            }
            
            // Tab key (for accessibility)
            if (e.key === 'Tab') {
                handleTabKey(e);
            }
        });
    }
    
    /**
     * Handle escape key press
     */
    function handleEscapeKey() {
        // Check modals first
        const modal = document.querySelector('.confirmation-modal.visible, .tips-modal.visible');
        if (modal) {
            const closeBtn = modal.querySelector('.cancel-btn, .tips-close');
            if (closeBtn) {
                closeBtn.click();
            }
            return;
        }
        
        // Check accessibility panel
        const accessibilityPanel = document.querySelector('.accessibility-panel');
        if (accessibilityPanel && accessibilityPanel.style.display === 'block') {
            accessibilityPanel.style.display = 'none';
            
            // Focus on toggle button
            const accessibilityToggle = document.querySelector('.accessibility-toggle');
            if (accessibilityToggle) {
                accessibilityToggle.focus();
            }
            return;
        }
        
        // Check open forms
        const activeForm = document.querySelector('.edit-form.active');
        if (activeForm) {
            const sectionAttr = activeForm.id.replace('-edit-form', '');
            const editBtn = document.querySelector(`.edit-section-btn[data-section="${sectionAttr}"]`);
            
            // Hide form
            activeForm.classList.remove('active');
            
            // Focus on edit button
            if (editBtn) {
                editBtn.focus();
            }
            return;
        }
    }
    
    /**
     * Handle tab key for accessibility
     */
    function handleTabKey(e) {
        // Add visible focus indicator for keyboard users
        if (!document.body.classList.contains('keyboard-user')) {
            document.body.classList.add('keyboard-user');
            
            // Add CSS for keyboard focus
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                body.keyboard-user :focus {
                    outline: 3px solid #FD8B51 !important;
                    outline-offset: 2px !important;
                }
            `;
            document.head.appendChild(styleElement);
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Show with delay for animation
        setTimeout(() => {
            notification.classList.add('visible');
        }, 10);
        
        // Close button
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            closeNotification(notification);
        });
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            closeNotification(notification);
        }, 5000);
    }
    
    /**
     * Close notification
     */
    function closeNotification(notification) {
        if (document.body.contains(notification)) {
            notification.classList.remove('visible');
            
            // Remove after animation
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.remove();
                }
            }, 300);
        }
    }
});