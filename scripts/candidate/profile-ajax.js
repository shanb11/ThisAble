document.addEventListener('DOMContentLoaded', function() {
    console.log("🚀 Profile-ajax.js loaded successfully!");
    initializeFormSubmissions();
    
    /**
     * Initialize form submissions for all profile sections
     */
    function initializeFormSubmissions() {
        console.log("📝 Initializing form submissions...");
        
        // Personal Information Form
        const personalForm = document.getElementById('personal-info-form');
        if (personalForm) {
            console.log("✅ Personal form found");
            personalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormData(this, '../../backend/candidate/update_personal_info.php', 'personal');
            });
        }
        
        // Skills Form
        const skillsForm = document.getElementById('skills-form');
        if (skillsForm) {
            console.log("✅ Skills form found");
            skillsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormData(this, '../../backend/candidate/update_skills.php', 'skills');
            });
        }
        
        // Work Preferences Form
        const preferencesForm = document.getElementById('preferences-form');
        if (preferencesForm) {
            console.log("✅ Preferences form found");
            preferencesForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormData(this, '../../backend/candidate/update_preferences.php', 'preferences');
            });
        }
        
        // Accessibility Needs Form
        const accessibilityForm = document.getElementById('accessibility-form');
        if (accessibilityForm) {
            console.log("✅ Accessibility form found");
            accessibilityForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormData(this, '../../backend/candidate/update_accessibility.php', 'accessibility');
            });
        }
        
        // Education Form - FIXED
        const educationForm = document.getElementById('education-form');
        if (educationForm) {
            console.log("✅ Education form found");
            educationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log("📚 Education form submitted!");
                submitFormData(this, '../../backend/candidate/update_education.php', 'education');
            });
            
            // Handle current checkbox for education
            const currentCheckbox = document.getElementById('education-current');
            const endDateInput = document.getElementById('education-end');
            
            if (currentCheckbox && endDateInput) {
                currentCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        endDateInput.value = '';
                        endDateInput.disabled = true;
                        endDateInput.removeAttribute('required');
                    } else {
                        endDateInput.disabled = false;
                    }
                });
            }
        } else {
            console.log("❌ Education form not found");
        }
        
        // Experience Form - FIXED
        const experienceForm = document.getElementById('experience-form');
        if (experienceForm) {
            console.log("✅ Experience form found");
            experienceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log("💼 Experience form submitted!");
                submitFormData(this, '../../backend/candidate/update_experience.php', 'experience');
            });
            
            // Handle current checkbox for experience
            const currentCheckbox = document.getElementById('experience-current');
            const endDateInput = document.getElementById('experience-end');
            
            if (currentCheckbox && endDateInput) {
                currentCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        endDateInput.value = '';
                        endDateInput.disabled = true;
                        endDateInput.removeAttribute('required');
                    } else {
                        endDateInput.disabled = false;
                    }
                });
            }
        } else {
            console.log("❌ Experience form not found");
        }
        
        // Initialize Resume Upload Functionality
        initializeResumeUploadSystem();
        
        // Initialize Edit/Delete Button Handlers
        initializeEducationHandlers();
        initializeExperienceHandlers();
        
        console.log("✅ All form submissions initialized!");
    }
    
    /**
     * Initialize Education Button Handlers
     */
    function initializeEducationHandlers() {
        console.log("🎓 Initializing education handlers...");
        
        // Edit buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-item-btn[data-type="education"]')) {
                const btn = e.target.closest('.edit-item-btn');
                const educationId = btn.getAttribute('data-id');
                console.log("✏️ Edit education clicked:", educationId);
                loadEducationForEdit(educationId);
            }
        });
        
        // Delete buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-item-btn[data-type="education"]')) {
                const btn = e.target.closest('.delete-item-btn');
                const educationId = btn.getAttribute('data-id');
                console.log("🗑️ Delete education clicked:", educationId);
                confirmDeleteEducation(educationId);
            }
        });
    }
    
    /**
     * Initialize Experience Button Handlers
     */
    function initializeExperienceHandlers() {
        console.log("💼 Initializing experience handlers...");
        
        // Edit buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-item-btn[data-type="experience"]')) {
                const btn = e.target.closest('.edit-item-btn');
                const experienceId = btn.getAttribute('data-id');
                console.log("✏️ Edit experience clicked:", experienceId);
                loadExperienceForEdit(experienceId);
            }
        });
        
        // Delete buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-item-btn[data-type="experience"]')) {
                const btn = e.target.closest('.delete-item-btn');
                const experienceId = btn.getAttribute('data-id');
                console.log("🗑️ Delete experience clicked:", experienceId);
                confirmDeleteExperience(experienceId);
            }
        });
    }
    
    /**
     * Load education data for editing
     */
    function loadEducationForEdit(educationId) {
        console.log("📚 Loading education for edit:", educationId);
        
        // Find the education item in the DOM
        const educationItem = document.querySelector(`.education-item[data-id="${educationId}"]`);
        if (!educationItem) {
            console.error("❌ Education item not found");
            return;
        }
        
        // Extract data from the DOM (in a real app, you might fetch from API)
        const title = educationItem.querySelector('.item-title').textContent;
        const subtitle = educationItem.querySelector('.item-subtitle').textContent;
        const location = educationItem.querySelector('.item-location');
        const description = educationItem.querySelector('.item-description p');
        
        // Fill the form
        document.getElementById('education-id').value = educationId;
        document.getElementById('education-degree').value = title;
        document.getElementById('education-institution').value = subtitle;
        document.getElementById('education-location').value = location ? location.textContent.replace('🗺️ ', '') : '';
        document.getElementById('education-description').value = description ? description.textContent : '';
        
        // Show the form
        const form = document.getElementById('education-edit-form');
        form.classList.add('active');
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Update button text
        const saveBtn = form.querySelector('.save-btn');
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Education';
    }
    
    /**
     * Load experience data for editing
     */
    function loadExperienceForEdit(experienceId) {
        console.log("💼 Loading experience for edit:", experienceId);
        
        // Find the experience item in the DOM
        const experienceItem = document.querySelector(`.experience-item[data-id="${experienceId}"]`);
        if (!experienceItem) {
            console.error("❌ Experience item not found");
            return;
        }
        
        // Extract data from the DOM
        const title = experienceItem.querySelector('.item-title').textContent;
        const subtitle = experienceItem.querySelector('.item-subtitle').textContent;
        const location = experienceItem.querySelector('.item-location');
        const description = experienceItem.querySelector('.item-description p');
        
        // Fill the form
        document.getElementById('experience-id').value = experienceId;
        document.getElementById('experience-title').value = title;
        document.getElementById('experience-company').value = subtitle;
        document.getElementById('experience-location').value = location ? location.textContent.replace('🗺️ ', '') : '';
        document.getElementById('experience-description').value = description ? description.textContent : '';
        
        // Show the form
        const form = document.getElementById('experience-edit-form');
        form.classList.add('active');
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Update button text
        const saveBtn = form.querySelector('.save-btn');
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Update Experience';
    }
    
    /**
     * Confirm education deletion
     */
    function confirmDeleteEducation(educationId) {
        if (confirm('Are you sure you want to delete this education record? This action cannot be undone.')) {
            deleteEducation(educationId);
        }
    }
    
    /**
     * Confirm experience deletion
     */
    function confirmDeleteExperience(experienceId) {
        if (confirm('Are you sure you want to delete this experience record? This action cannot be undone.')) {
            deleteExperience(experienceId);
        }
    }
    
    /**
     * Delete education record
     */
    function deleteEducation(educationId) {
        console.log("🗑️ Deleting education:", educationId);
        showNotification('Deleting education...', 'info');
        
        fetch('../../backend/candidate/delete_education.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ education_id: educationId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Remove the item from DOM or reload page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Delete failed', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showNotification('Error deleting education', 'error');
        });
    }
    
    /**
     * Delete experience record
     */
    function deleteExperience(experienceId) {
        console.log("🗑️ Deleting experience:", experienceId);
        showNotification('Deleting experience...', 'info');
        
        fetch('../../backend/candidate/delete_experience.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ experience_id: experienceId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Remove the item from DOM or reload page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Delete failed', 'error');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showNotification('Error deleting experience', 'error');
        });
    }
    
    /**
     * Submit form data via AJAX - ENHANCED VERSION
     */
    function submitFormData(form, url, section) {
        console.log(`📤 Submitting ${section} form data to: ${url}`);
        
        const saveBtn = form.querySelector('.save-btn');
        if (!saveBtn) {
            console.error(`❌ Save button not found in ${section} form`);
            return;
        }
        
        // Show saving indicator
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
        
        // Prepare form data
        const formData = new FormData(form);
        
        // Debug: Log form data
        console.log(`📝 ${section} form data:`);
        for (let pair of formData.entries()) {
            console.log(`   ${pair[0]}: ${pair[1]}`);
        }
        
        // Send AJAX request
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log(`📥 Response status from ${url}:`, response.status);
            if (!response.ok) {
                throw new Error(`Server responded with status ${response.status}`);
            }
            return response.text();
        })
        .then(responseText => {
            console.log(`📄 Raw response from ${url}:`, responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
                console.log(`✅ Parsed JSON response:`, data);
            } catch (jsonError) {
                console.error(`❌ JSON parse error:`, jsonError);
                throw new Error("Invalid server response: " + responseText.substring(0, 100));
            }
            
            if (data.success) {
                saveBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                
                setTimeout(() => {
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                    
                    // Hide form
                    const editForm = document.getElementById(`${section}-edit-form`);
                    if (editForm) {
                        editForm.classList.remove('active');
                    }
                    
                    // Show success notification
                    showNotification(data.message || 'Changes saved successfully!', 'success');
                    
                    // Reload page to show updates
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }, 1000);
            } else {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
                showNotification(data.message || 'An error occurred while saving changes.', 'error');
            }
        })
        .catch(error => {
            console.error(`💥 Error with ${url}:`, error);
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            showNotification('An error occurred while saving changes: ' + error.message, 'error');
        });
    }
    
    /**
     * Complete Resume Upload System
     */
    function initializeResumeUploadSystem() {
        console.log("📁 Initializing resume upload system...");
        
        // Handle upload area clicks and drag/drop
        document.addEventListener('click', function(e) {
            const uploadArea = e.target.closest('#upload-area');
            if (uploadArea) {
                const fileInput = document.getElementById('resume-file');
                if (fileInput) {
                    fileInput.click();
                }
            }
        });
        
        // Handle file input changes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'resume-file') {
                const file = e.target.files[0];
                if (file) {
                    handleFileSelection(file);
                }
            }
        });
        
        // Handle form submission
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.id === 'resume-form') {
                e.preventDefault();
                handleResumeUpload(e.target);
            }
        });
    }
    
    /**
     * Handle file selection and preview
     */
    function handleFileSelection(file) {
        console.log("📋 Handling file selection:", file.name);
        
        const uploadArea = document.getElementById('upload-area');
        if (!uploadArea) return;
        
        // Validate file type
        const validTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!validTypes.includes(file.type)) {
            showNotification('Please upload a PDF or Word document only.', 'error');
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showNotification('File size must be less than 5MB.', 'error');
            return;
        }
        
        // Store file globally
        window.selectedResumeFile = file;
        
        // Update upload area
        const fileIcon = file.type.includes('pdf') ? 'fa-file-pdf' : 'fa-file-word';
        const iconColor = file.type.includes('pdf') ? '#f44336' : '#2196f3';
        
        uploadArea.innerHTML = `
            <i class="fas ${fileIcon}" style="color: ${iconColor}; font-size: 40px;"></i>
            <p style="margin: 15px 0 10px 0; font-weight: 600;">${file.name}</p>
            <p class="file-types">Click "Upload Resume" to save this file</p>
        `;
    }
    
    /**
     * Handle actual resume upload
     */
    function handleResumeUpload(form) {
        console.log("📤 Processing resume upload...");
        
        const fileInput = form.querySelector('#resume-file');
        const saveBtn = form.querySelector('.save-btn');
        
        if (!saveBtn) {
            showNotification('Save button not found!', 'error');
            return;
        }
        
        // Get file
        let file = null;
        if (fileInput && fileInput.files.length > 0) {
            file = fileInput.files[0];
        } else if (window.selectedResumeFile) {
            file = window.selectedResumeFile;
        } else {
            showNotification('Please select a file to upload.', 'error');
            return;
        }
        
        // Show uploading state
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        saveBtn.disabled = true;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('resume_file', file);
        
        // Send to backend
        fetch('../../backend/candidate/upload_resume_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(responseText => {
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (jsonError) {
                throw new Error("Invalid server response: " + responseText.substring(0, 100));
            }
            
            // Reset button
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            
            if (data.success) {
                showNotification(data.message || 'Resume uploaded successfully!', 'success');
                
                // Clear stored file
                if (window.selectedResumeFile) {
                    delete window.selectedResumeFile;
                }
                
                // Hide the upload form
                const editForm = document.getElementById('resume-edit-form');
                if (editForm) {
                    editForm.classList.remove('active');
                }
                
                // Reload page to show new resume
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
            } else {
                showNotification(data.message || 'Upload failed', 'error');
            }
        })
        .catch(error => {
            console.error("💥 Upload error:", error);
            
            // Reset button
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
            
            showNotification('Upload failed: ' + error.message, 'error');
        });
    }
    
    /**
     * Show notification - IMPROVED VERSION
     */
    function showNotification(message, type = 'info') {
        console.log(`📢 Notification: ${message} (${type})`);
        
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.ajax-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `ajax-notification ${type}`;
        
        let iconClass, backgroundColor, borderColor;
        switch (type) {
            case 'success':
                iconClass = 'fa-check-circle';
                backgroundColor = '#d4edda';
                borderColor = '#28a745';
                break;
            case 'error':
                iconClass = 'fa-exclamation-circle';
                backgroundColor = '#f8d7da';
                borderColor = '#dc3545';
                break;
            default:
                iconClass = 'fa-info-circle';
                backgroundColor = '#d1ecf1';
                borderColor = '#17a2b8';
        }
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Apply styles
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: ${backgroundColor};
            border: 1px solid ${borderColor};
            border-left: 4px solid ${borderColor};
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            z-index: 1001;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 400px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: #333;
        `;
        
        // Style content
        const content = notification.querySelector('.notification-content');
        content.style.cssText = `
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        `;
        
        const icon = notification.querySelector('.fas');
        icon.style.color = borderColor;
        icon.style.fontSize = '16px';
        
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            font-size: 14px;
            opacity: 0.7;
            transition: opacity 0.2s;
        `;
        
        // Add to body
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
            notification.style.opacity = '1';
        }, 10);
        
        // Close button
        closeBtn.addEventListener('click', () => {
            notification.style.transform = 'translateY(100px)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.remove();
                }
            }, 300);
        });
        
        // Auto-close after 5 seconds
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.style.transform = 'translateY(100px)';
                notification.style.opacity = '0';
                
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    console.log("✅ Profile-ajax.js initialization complete!");
});