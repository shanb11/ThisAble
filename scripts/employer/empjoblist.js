document.addEventListener('DOMContentLoaded', function() {
    // Core DOM elements
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');
    const jobListings = document.getElementById('job-listings');
    const emptyState = document.getElementById('empty-state');
    const postJobBtn = document.getElementById('open-job-form');
    const emptyStatePostJobBtn = document.getElementById('empty-state-post-job');
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const sortFilter = document.getElementById('sort-filter');
    const successMessage = document.getElementById('success-message');
    const successMessageText = document.getElementById('success-message-text');
    const errorMessage = document.getElementById('error-message');
    const errorMessageText = document.getElementById('error-message-text');
    const loadingState = document.getElementById('loading-state');
    const notificationIcon = document.querySelector('.notification-icons i');

    // Global variables
    let currentJobData = [];
    let currentPage = 1;
    let isLoading = false;
    let searchTimeout = null;
    let availableSkills = [];
    loadAvailableSkills();

    // Add notification redirection functionality
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function() {
            window.location.href = 'empnotifications.php';
        });
    }

    // Load job listings from API
    async function loadJobListings(search = '', status = 'all', sort = 'recent', page = 1) {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();

        try {
            const params = new URLSearchParams({
                search: search,
                status: status,
                sort: sort,
                page: page,
                limit: 20
            });

            const response = await fetch(`../../backend/employer/get_job_listings.php?${params}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to load job listings');
            }

            if (data.success) {
                currentJobData = data.data.jobs;
                displayJobListings(currentJobData);
                updateStatistics(data.data.statistics);
                updatePagination(data.data.pagination);
                
                hideLoading();
                
                // Check if we need to show empty state
                if (currentJobData.length === 0) {
                    showEmptyState(search || status !== 'all');
                } else {
                    hideEmptyState();
                }
            } else {
                throw new Error(data.message || 'Failed to load job listings');
            }

        } catch (error) {
            console.error('Error loading job listings:', error);
            hideLoading();
            showErrorMessage('Failed to load job listings: ' + error.message);
            showEmptyState(true);
        } finally {
            isLoading = false;
        }
    }

    // Create new job via API
    async function createJob(jobData) {
        try {
            console.log('🚀 Creating job with data:', jobData);
            
            const response = await fetch('../../backend/employer/create_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(jobData)
            });
            
            const responseText = await response.text();
            console.log('📥 Raw response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response from server: ' + responseText.substring(0, 100));
            }
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            if (data.success) {
                console.log('✅ Job created successfully:', data);
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to create job');
            }
            
        } catch (error) {
            console.error('Create job error:', error);
            throw error;
        }
    }

    // Display job listings in DOM
    function displayJobListings(jobs) {
        // Clear existing job cards
        jobListings.innerHTML = '';

        jobs.forEach(job => {
            const jobCard = createJobCard(job);
            jobListings.appendChild(jobCard);
        });

        // Setup action buttons for all job cards
        setupJobActionButtons();
    }

    // Create job card element
    function createJobCard(jobData) {
        const jobCard = document.createElement('div');
        jobCard.className = 'job-card';
        if (jobData.job_status === 'draft') {
            jobCard.className += ' draft';
        } else if (jobData.job_status === 'closed') {
            jobCard.className += ' closed';
        }
        
        jobCard.dataset.status = jobData.job_status;
        jobCard.dataset.title = jobData.job_title;
        jobCard.dataset.date = jobData.posted_at;
        jobCard.dataset.applicants = jobData.applications_count;
        jobCard.dataset.jobId = jobData.job_id;
        
        // Format the date
        const datePosted = new Date(jobData.posted_at);
        const formattedDate = `${datePosted.toLocaleString('default', { month: 'short' })} ${datePosted.getDate()}, ${datePosted.getFullYear()}`;
        
        // Create location display with work arrangements
        let locationDisplay = jobData.location;
        if (jobData.remote_work_available) {
            locationDisplay += ' / Remote';
        }
        
        // Set job card content
        jobCard.innerHTML = `
            <div class="job-card-header">
                <div>
                    <h3 class="job-title">${escapeHtml(jobData.job_title)}</h3>
                    <div class="job-company">${escapeHtml(jobData.company_name || window.employerData.company_name)}</div>
                    <div class="job-badges">
                        <span class="job-badge badge-location">
                            <i class="fas fa-map-marker-alt"></i>
                            ${escapeHtml(locationDisplay)}
                        </span>
                        <span class="job-badge badge-type">
                            <i class="fas fa-clock"></i>
                            ${escapeHtml(jobData.employment_type)}
                        </span>
                    </div>
                </div>
                <span class="job-status status-${jobData.job_status}">${capitalizeFirst(jobData.job_status)}</span>
            </div>
            <div class="job-card-body">
                <div class="job-info-group">
                    <div class="job-info">
                        <span class="job-info-label">Applicants</span>
                        <span class="job-info-value">
                            <i class="fas fa-users"></i>
                            ${jobData.applications_count}
                        </span>
                    </div>
                    <div class="job-info">
                        <span class="job-info-label">${jobData.job_status === 'draft' ? 'Date Created' : 'Date Posted'}</span>
                        <span class="job-info-value">
                            <i class="far fa-calendar-alt"></i>
                            ${formattedDate}
                        </span>
                    </div>
                </div>
                <div class="job-actions">
                    <button class="job-action-btn action-view" title="View Job">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="job-action-btn action-edit" title="Edit Job">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="job-action-btn action-delete" title="Delete Job">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="job-action-btn action-toggle" title="${getToggleTitle(jobData.job_status)}">
                        <i class="fas fa-${getToggleIcon(jobData.job_status)}"></i>
                    </button>
                </div>
            </div>
        `;
        
        return jobCard;
    }

    // REPLACE your existing createJobFormModal function with this enhanced version:
    function createJobFormModal(existingJobData = null) {
        // Create the modal overlay element
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay job-post-modal';
        
        // Determine if we're editing or creating new
        const isEditing = existingJobData !== null;
        const modalTitle = isEditing ? 'Edit Job Listing' : 'Post a New Job';
        const submitButtonText = isEditing ? 'Update Job' : 'Post Job';
        
        // Prepare data for form (aligned with database schema)
        const jobData = isEditing ? existingJobData : {
            job_title: '',
            department: '',
            location: '',
            employment_type: '',
            salary_range: '',
            application_deadline: '',
            job_description: '',
            job_requirements: '',
            remote_work_available: false,
            flexible_schedule: false,
            accommodations: {
                wheelchair_accessible: false,
                assistive_technology: false,
                remote_work_option: false,
                screen_reader_compatible: false,
                sign_language_interpreter: false,
                modified_workspace: false,
                transportation_support: false,
                additional_accommodations: ''
            },
            required_skills: [] // Will be populated from job_requirements table
        };
        
        // Create skills selection HTML
        const skillsSelectionHTML = generateSkillsSelectionHTML();
        
        // Create the modal content with enhanced skills section
        const modalHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">${modalTitle}</h3>
                    <button class="modal-close" id="close-job-form">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="job-post-form">
                        <!-- 1. Basic Job Information -->
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-briefcase"></i>
                                Basic Job Information
                            </h4>
                            
                            <div class="form-row">
                                <label class="form-label" for="job-title">Job Title*</label>
                                <input type="text" id="job-title" class="form-control" placeholder="e.g. Software Developer, Customer Service Representative" value="${jobData.job_title || ''}" required>
                            </div>
                            
                            <div class="form-row-group">
                                <div class="form-row">
                                    <label class="form-label" for="department">Department*</label>
                                    <select id="department" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <option value="Engineering" ${jobData.department === 'Engineering' ? 'selected' : ''}>Engineering</option>
                                        <option value="Design" ${jobData.department === 'Design' ? 'selected' : ''}>Design</option>
                                        <option value="Customer Service" ${jobData.department === 'Customer Service' ? 'selected' : ''}>Customer Service</option>
                                        <option value="Sales" ${jobData.department === 'Sales' ? 'selected' : ''}>Sales</option>
                                        <option value="Marketing" ${jobData.department === 'Marketing' ? 'selected' : ''}>Marketing</option>
                                        <option value="HR" ${jobData.department === 'HR' ? 'selected' : ''}>Human Resources</option>
                                        <option value="Finance" ${jobData.department === 'Finance' ? 'selected' : ''}>Finance</option>
                                        <option value="Operations" ${jobData.department === 'Operations' ? 'selected' : ''}>Operations</option>
                                        <option value="Other" ${jobData.department === 'Other' ? 'selected' : ''}>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <label class="form-label" for="job-location">Location*</label>
                                    <input type="text" id="job-location" class="form-control" placeholder="e.g. Manila, Philippines" value="${jobData.location || ''}" required>
                                </div>
                            </div>
                            
                            <div class="form-row-group">
                                <div class="form-row">
                                    <label class="form-label" for="employment-type">Employment Type*</label>
                                    <select id="employment-type" class="form-control" required>
                                        <option value="">Select Employment Type</option>
                                        <option value="Full-time" ${jobData.employment_type === 'Full-time' ? 'selected' : ''}>Full-time</option>
                                        <option value="Part-time" ${jobData.employment_type === 'Part-time' ? 'selected' : ''}>Part-time</option>
                                        <option value="Contract" ${jobData.employment_type === 'Contract' ? 'selected' : ''}>Contract</option>
                                        <option value="Internship" ${jobData.employment_type === 'Internship' ? 'selected' : ''}>Internship</option>
                                        <option value="Freelance" ${jobData.employment_type === 'Freelance' ? 'selected' : ''}>Freelance</option>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <label class="form-label" for="salary-range">Salary Range</label>
                                    <input type="text" id="salary-range" class="form-control" placeholder="e.g. 25,000 - 35,000" value="${jobData.salary_range || ''}">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label" for="application-deadline">Application Deadline</label>
                                <input type="date" id="application-deadline" class="form-control" value="${jobData.application_deadline || ''}">
                            </div>
                            
                            <div class="form-row-group">
                                <div class="form-row checkbox-row">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="remote-work" class="checkbox-input" ${jobData.remote_work_available ? 'checked' : ''}> 
                                            Remote Work Available
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-row checkbox-row">
                                    <div class="checkbox-group">
                                        <label class="checkbox-label">
                                            <input type="checkbox" id="flexible-schedule" class="checkbox-input" ${jobData.flexible_schedule ? 'checked' : ''}> 
                                            Flexible Schedule
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 2. Job Description -->
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-file-alt"></i>
                                Job Description
                            </h4>
                            
                            <div class="form-row">
                                <label class="form-label" for="job-description">Job Description*</label>
                                <textarea id="job-description" class="form-control" rows="4" placeholder="Describe the role, what the candidate will be doing, and the impact they'll have on the team." required>${jobData.job_description || ''}</textarea>
                            </div>
                        </div>
                        
                        <!-- 3. Requirements & Qualifications -->
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-graduation-cap"></i>
                                Job Requirements & Qualifications
                            </h4>
                            
                            <div class="form-row">
                                <label class="form-label" for="job-requirements">Additional Requirements & Qualifications</label>
                                <textarea id="job-requirements" class="form-control" rows="4" placeholder="List education requirements, experience levels, certifications, and other qualifications not covered by skills below...">${jobData.job_requirements || ''}</textarea>
                                <small class="form-help">Use this field for education requirements, years of experience, certifications, and other non-skill qualifications.</small>
                            </div>
                        </div>
                        
                        <!-- 4. SKILLS SELECTION SECTION -->
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-tools"></i>
                                Required Skills
                            </h4>
                            
                            <div class="skills-selection-container">
                                <div class="skills-selection-header">
                                    <p class="skills-help-text">Select the skills required for this position. You can mark skills as required or preferred.</p>
                                    <div class="skills-actions">
                                        <button type="button" class="btn-secondary" id="select-all-skills">Select All</button>
                                        <button type="button" class="btn-secondary" id="clear-all-skills">Clear All</button>
                                    </div>
                                </div>
                                
                                ${skillsSelectionHTML}
                            </div>
                        </div>
                        
                        <!-- 5. Workplace Accommodations -->
                        <div class="form-section">
                            <h4 class="form-section-title">
                                <i class="fas fa-universal-access"></i>
                                Workplace Accommodations for PWD Candidates
                            </h4>
                            
                            <div class="accommodations-section">
                                <div class="accommodation-item">
                                    <input type="checkbox" id="wheelchair-accessible" class="accommodation-checkbox" ${jobData.accommodations?.wheelchair_accessible ? 'checked' : ''}>
                                    <label for="wheelchair-accessible" class="accommodation-label">
                                        <strong>Wheelchair Accessible</strong>
                                        <div class="accommodation-description">Wheelchair accessible entrances, elevators, and workspaces</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="assistive-technology" class="accommodation-checkbox" ${jobData.accommodations?.assistive_technology ? 'checked' : ''}>
                                    <label for="assistive-technology" class="accommodation-label">
                                        <strong>Assistive Technology Support</strong>
                                        <div class="accommodation-description">Screen readers, voice recognition software, and other assistive tools</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="remote-work-option" class="accommodation-checkbox" ${jobData.accommodations?.remote_work_option ? 'checked' : ''}>
                                    <label for="remote-work-option" class="accommodation-label">
                                        <strong>Remote Work Option</strong>
                                        <div class="accommodation-description">Flexible work-from-home arrangements</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="screen-reader-compatible" class="accommodation-checkbox" ${jobData.accommodations?.screen_reader_compatible ? 'checked' : ''}>
                                    <label for="screen-reader-compatible" class="accommodation-label">
                                        <strong>Screen Reader Compatible</strong>
                                        <div class="accommodation-description">All digital tools and platforms work with screen readers</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="sign-language-interpreter" class="accommodation-checkbox" ${jobData.accommodations?.sign_language_interpreter ? 'checked' : ''}>
                                    <label for="sign-language-interpreter" class="accommodation-label">
                                        <strong>Sign Language Interpreter</strong>
                                        <div class="accommodation-description">Professional interpreters for meetings and communications</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="modified-workspace" class="accommodation-checkbox" ${jobData.accommodations?.modified_workspace ? 'checked' : ''}>
                                    <label for="modified-workspace" class="accommodation-label">
                                        <strong>Modified Workspace</strong>
                                        <div class="accommodation-description">Adjustable desks, ergonomic equipment, and workspace modifications</div>
                                    </label>
                                </div>
                                
                                <div class="accommodation-item">
                                    <input type="checkbox" id="transportation-support" class="accommodation-checkbox" ${jobData.accommodations?.transportation_support ? 'checked' : ''}>
                                    <label for="transportation-support" class="accommodation-label">
                                        <strong>Transportation Support</strong>
                                        <div class="accommodation-description">Accessible parking, shuttle services, or transportation allowances</div>
                                    </label>
                                </div>
                                
                                <div class="form-row">
                                    <label class="form-label" for="additional-accommodations">Additional Accommodations</label>
                                    <textarea id="additional-accommodations" class="form-control" rows="3" placeholder="Describe any additional accommodations available...">${jobData.accommodations?.additional_accommodations || ''}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="submit-row">
                            <button type="button" id="cancel-job-form" class="cancel-btn">Cancel</button>
                            <button type="submit" class="submit-btn" id="submit-job-btn">
                                <i class="fas fa-paper-plane"></i>
                                <span class="btn-text">${submitButtonText}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        // Add the modal HTML to the overlay
        modalOverlay.innerHTML = modalHTML;
        
        // Append the modal to the body
        document.body.appendChild(modalOverlay);
        
        // Show the modal with animation
        setTimeout(() => {
            modalOverlay.style.display = 'flex';
            setTimeout(() => {
                modalOverlay.classList.add('show');
            }, 10);
        }, 0);
        
        // Setup event listeners for the modal
        setupJobFormEventListeners(modalOverlay, isEditing, jobData);
        
        // Setup skills selection event listeners
        setupSkillsSelectionListeners(modalOverlay);
    }

    function setupJobFormEventListeners(modalOverlay, isEditing, jobData = null) {
        const closeBtn = modalOverlay.querySelector('#close-job-form');
        const cancelBtn = modalOverlay.querySelector('#cancel-job-form');
        const form = modalOverlay.querySelector('#job-post-form');
        const submitBtn = modalOverlay.querySelector('#submit-job-btn');
        
        // Close button events
        if (closeBtn) {
            closeBtn.addEventListener('click', () => closeModal(modalOverlay));
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => closeModal(modalOverlay));
        }
        
        // Close on overlay click
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal(modalOverlay);
            }
        });
        
        // Form submission with enhanced skills handling
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (submitBtn.disabled) return;
                
                const btnText = submitBtn.querySelector('.btn-text');
                const originalText = btnText.textContent;
                
                // Disable submit button and show loading
                submitBtn.disabled = true;
                btnText.textContent = isEditing ? 'Updating...' : 'Posting...';
                
                try {
                    // Get selected skills using the new function
                    const selectedSkills = getSelectedSkills(form);
                    
                    // Get form values with enhanced skills data
                    const formData = {
                        job_title: form.querySelector('#job-title').value.trim(),
                        department: form.querySelector('#department').value,
                        location: form.querySelector('#job-location').value.trim(),
                        employment_type: form.querySelector('#employment-type').value,
                        salary_range: form.querySelector('#salary-range').value.trim(),
                        application_deadline: form.querySelector('#application-deadline').value,
                        job_description: form.querySelector('#job-description').value.trim(),
                        job_requirements: form.querySelector('#job-requirements').value.trim(),
                        remote_work_available: form.querySelector('#remote-work').checked,
                        flexible_schedule: form.querySelector('#flexible-schedule').checked,
                        
                        accommodations: {
                            wheelchair_accessible: form.querySelector('#wheelchair-accessible').checked,
                            assistive_technology: form.querySelector('#assistive-technology').checked,
                            remote_work_option: form.querySelector('#remote-work-option').checked,
                            screen_reader_compatible: form.querySelector('#screen-reader-compatible').checked,
                            sign_language_interpreter: form.querySelector('#sign-language-interpreter').checked,
                            modified_workspace: form.querySelector('#modified-workspace').checked,
                            transportation_support: form.querySelector('#transportation-support').checked,
                            additional_accommodations: form.querySelector('#additional-accommodations').value.trim()
                        },
                        
                        // Enhanced: Include selected skills
                        required_skills: selectedSkills,
                        job_status: 'active'
                    };
                    
                    console.log('📋 Form Data with Skills:', formData);
                    console.log('🎯 Selected Skills:', selectedSkills);
                    
                    let result;
                    
                    if (isEditing) {
                        // Add job_id for update
                        formData.job_id = jobData.job_id;
                        
                        // Update the job
                        result = await updateJob(formData);
                        
                        // Update the job card in the DOM
                        updateJobCardInDOM(result);
                        
                        showSuccessMessage(`Job updated successfully! ${selectedSkills.length} skills selected.`);
                    } else {
                        // Create new job
                        result = await createJob(formData);
                        
                        // Reload job listings to show the new job
                        await loadJobListings();
                        
                        showSuccessMessage(`Job posted successfully! ${selectedSkills.length} skills selected.`);
                    }
                    
                    // Close modal
                    closeModal(modalOverlay);
                    
                } catch (error) {
                    console.error('Error submitting job:', error);
                    showErrorMessage('Failed to ' + (isEditing ? 'update' : 'post') + ' job: ' + error.message);
                } finally {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    btnText.textContent = originalText;
                }
            });
        }
    }

    // Close a modal
    function closeModal(modalOverlay) {
        modalOverlay.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(modalOverlay);
        }, 300);
    }

    // Show loading state
    function showLoading() {
        if (loadingState) {
            loadingState.style.display = 'block';
        }
        hideEmptyState();
    }

    // Hide loading state
    function hideLoading() {
        if (loadingState) {
            loadingState.style.display = 'none';
        }
    }

    // Show error message
    function showErrorMessage(message) {
        if (errorMessage && errorMessageText) {
            errorMessageText.textContent = message;
            errorMessage.classList.add('show');
            
            // Hide after 5 seconds
            setTimeout(() => {
                errorMessage.classList.remove('show');
            }, 5000);
        }
    }

    // Show success message
    function showSuccessMessage(message) {
        if (successMessage && successMessageText) {
            successMessageText.textContent = message;
            successMessage.classList.add('show');
            
            // Hide after 3 seconds
            setTimeout(() => {
                successMessage.classList.remove('show');
            }, 3000);
        }
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    // Capitalize first letter
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Get toggle button title
    function getToggleTitle(status) {
        switch (status) {
            case 'active': return 'Close Job';
            case 'draft': return 'Publish Job';
            case 'closed': return 'Reopen Job';
            case 'paused': return 'Resume Job';
            default: return 'Toggle Status';
        }
    }

    // Get toggle button icon
    function getToggleIcon(status) {
        switch (status) {
            case 'active': return 'lock';
            case 'draft': return 'paper-plane';
            case 'closed': return 'lock-open';
            case 'paused': return 'play';
            default: return 'toggle-on';
        }
    }

    // Show empty state
    function showEmptyState(filtered = false) {
        if (emptyState) {
            const title = emptyState.querySelector('.empty-title');
            const text = emptyState.querySelector('.empty-text');
            const button = emptyState.querySelector('#empty-state-post-job');
            
            if (filtered) {
                if (title) title.textContent = 'No Matching Job Listings';
                if (text) text.textContent = 'Try adjusting your search or filters.';
                if (button) button.style.display = 'none';
            } else {
                if (title) title.textContent = 'No Job Listings Found';
                if (text) text.textContent = "You haven't posted any job listings yet.";
                if (button) button.style.display = 'flex';
            }
            
            emptyState.style.display = 'block';
        }
    }

    // Hide empty state
    function hideEmptyState() {
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    // Update statistics display (placeholder)
    function updateStatistics(stats) {
        console.log('Statistics:', stats);
    }

    // Update pagination (placeholder)
    function updatePagination(pagination) {
        currentPage = pagination.current_page;
        console.log('Pagination:', pagination);
    }

    // Sidebar Toggle
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
    }

    // Job Posting Form
    if (postJobBtn) {
        postJobBtn.addEventListener('click', function() {
            createJobFormModal();
        });
    }

    if (emptyStatePostJobBtn) {
        emptyStatePostJobBtn.addEventListener('click', function() {
            createJobFormModal();
        });
    }

    // Search Functionality with debouncing
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 500);
        });
    }

    // Status Filter Change
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            performSearch();
        });
    }

    // Sort Filter Change
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            performSearch();
        });
    }

    // Perform search with current filters
    function performSearch() {
        const search = searchInput ? searchInput.value.trim() : '';
        const status = statusFilter ? statusFilter.value : 'all';
        const sort = sortFilter ? sortFilter.value : 'recent';
        
        loadJobListings(search, status, sort, 1);
    }

    // Set up action buttons for job cards
    function setupJobActionButtons(target = document) {
        const actionBtns = target.querySelectorAll('.job-action-btn');
        
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const action = this.classList.contains('action-view') ? 'view' :
                               this.classList.contains('action-edit') ? 'edit' :
                               this.classList.contains('action-delete') ? 'delete' : 'toggle';
                
                const jobCard = this.closest('.job-card');
                const jobTitle = jobCard.querySelector('.job-title').textContent;
                const jobId = jobCard.dataset.jobId;
                
                handleJobAction(action, jobCard, jobTitle, jobId);
            });
        });
    }

    // Load initial job listings
    loadJobListings();

    // Set up periodic refresh (optional)
    setInterval(() => {
        if (!isLoading) {
            const search = searchInput ? searchInput.value.trim() : '';
            const status = statusFilter ? statusFilter.value : 'all';
            const sort = sortFilter ? sortFilter.value : 'recent';
            
            loadJobListings(search, status, sort, currentPage);
        }
    }, 300000); // 5 minutes

    // Get job details from API
    async function getJobDetails(jobId) {
        try {
            const response = await fetch(`../../backend/employer/get_job_details.php?job_id=${jobId}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to get job details');
            }

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to get job details');
            }

        } catch (error) {
            console.error('Error getting job details:', error);
            throw error;
        }
    }

    // Show Job Details Modal (Updated to use real API)
    async function showJobDetailsModal(jobId) {
        try {
            // Show loading state
            showLoading();
            
            // Get job details from API
            const jobDetails = await getJobDetails(jobId);
            const job = jobDetails.job;
            const applicants = jobDetails.applicants;
            const stats = jobDetails.statistics;
            
            hideLoading();
            
            // Format the date
            const datePosted = new Date(job.posted_at);
            const formattedDate = `${datePosted.toLocaleString('default', { month: 'short' })} ${datePosted.getDate()}, ${datePosted.getFullYear()}`;
            
            // Create work arrangement badges
            let workArrangements = [];
            if (job.remote_work_available) workArrangements.push('Remote');
            if (job.flexible_schedule) workArrangements.push('Flexible Schedule');
            
            // Create accommodations display
            const accommodationsList = [];
            if (job.accommodations.wheelchair_accessible) accommodationsList.push('Wheelchair Accessible');
            if (job.accommodations.assistive_technology) accommodationsList.push('Assistive Technology');
            if (job.accommodations.remote_work_option) accommodationsList.push('Remote Work Option');
            if (job.accommodations.screen_reader_compatible) accommodationsList.push('Screen Reader Compatible');
            if (job.accommodations.sign_language_interpreter) accommodationsList.push('Sign Language Interpreter');
            if (job.accommodations.modified_workspace) accommodationsList.push('Modified Workspace');
            if (job.accommodations.transportation_support) accommodationsList.push('Transportation Support');
            
            // Create the modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.className = 'modal-overlay job-details-modal';
            
            // Create modal HTML with job details and applicants
            const modalHTML = `
                <div class="modal" style="max-width: 900px;">
                    <div class="modal-header">
                        <h3 class="modal-title">Job Details</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="job-view-tabs">
                            <div class="job-view-tab active" data-tab="details">Job Details</div>
                            <div class="job-view-tab" data-tab="applicants">Applicants (${applicants.length})</div>
                        </div>
                    
                        <div class="job-view-content active" id="details-tab">
                            <div class="job-details-section">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                                    <div>
                                        <h2 style="font-size: 24px; margin-bottom: 5px; color: var(--text-dark);">${escapeHtml(job.job_title)}</h2>
                                        <p style="font-size: 16px; color: var(--text-medium); margin-bottom: 10px;">${escapeHtml(job.company_name)} · ${escapeHtml(job.location)}</p>
                                        <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                                            <span class="job-badge badge-type">
                                                <i class="fas fa-clock"></i>
                                                ${escapeHtml(job.employment_type)}
                                            </span>
                                            <span class="job-badge badge-location">
                                                <i class="fas fa-building"></i>
                                                ${escapeHtml(job.department)}
                                            </span>
                                            ${workArrangements.map(arr => `
                                                <span class="job-badge badge-location">
                                                    <i class="fas fa-laptop-house"></i>
                                                    ${escapeHtml(arr)}
                                                </span>
                                            `).join('')}
                                        </div>
                                        ${job.salary_range ? `
                                            <div style="margin-bottom: 10px;">
                                                <strong>Salary Range:</strong> ${escapeHtml(job.salary_range)}
                                            </div>
                                        ` : ''}
                                        ${job.application_deadline ? `
                                            <div style="margin-bottom: 10px;">
                                                <strong>Application Deadline:</strong> ${new Date(job.application_deadline).toLocaleDateString()}
                                            </div>
                                        ` : ''}
                                    </div>
                                    <span class="job-status status-${job.job_status}" style="font-size: 14px;">
                                        ${capitalizeFirst(job.job_status)}
                                    </span>
                                </div>
                                
                                <div style="display: flex; gap: 20px; margin-bottom: 20px; color: var(--text-medium); flex-wrap: wrap;">
                                    <div>
                                        <i class="far fa-calendar-alt"></i> Posted: ${formattedDate}
                                    </div>
                                    <div>
                                        <i class="fas fa-users"></i> Applicants: ${job.applications_count}
                                    </div>
                                    <div>
                                        <i class="fas fa-eye"></i> Views: ${job.views_count}
                                    </div>
                                </div>
                                
                                <div style="margin-bottom: 20px;">
                                    <h4 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">Job Description</h4>
                                    <div style="line-height: 1.6; color: var(--text-medium); white-space: pre-line;">${escapeHtml(job.job_description)}</div>
                                </div>
                                
                                <div style="margin-bottom: 20px;">
                                    <h4 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">Requirements & Qualifications</h4>
                                    <div style="line-height: 1.6; color: var(--text-medium); white-space: pre-line;">${escapeHtml(job.job_requirements)}</div>
                                </div>
                                
                                <div style="margin-bottom: 20px;">
                                    <h4 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">
                                        <i class="fas fa-universal-access"></i> Workplace Accommodations
                                    </h4>
                                    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                                        ${accommodationsList.length > 0 ? `
                                            <ul style="margin: 0; padding-left: 20px;">
                                                ${accommodationsList.map(acc => `<li>${escapeHtml(acc)}</li>`).join('')}
                                            </ul>
                                        ` : '<p>No specific accommodations listed.</p>'}
                                        ${job.accommodations.additional_accommodations ? `
                                            <div style="margin-top: 10px;">
                                                <strong>Additional:</strong> ${escapeHtml(job.accommodations.additional_accommodations)}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="job-view-content" id="applicants-tab">
                            <!-- Applicants Section -->
                            <div class="applicants-section">
                                <div class="job-stats">
                                    <div class="job-stat-item">
                                        <div class="job-stat-title">Total</div>
                                        <div class="job-stat-value">${stats.total_applicants}</div>
                                    </div>
                                    <div class="job-stat-item">
                                        <div class="job-stat-title">New</div>
                                        <div class="job-stat-value">${stats.submitted}</div>
                                    </div>
                                    <div class="job-stat-item">
                                        <div class="job-stat-title">Under Review</div>
                                        <div class="job-stat-value">${stats.under_review}</div>
                                    </div>
                                    <div class="job-stat-item">
                                        <div class="job-stat-title">Shortlisted</div>
                                        <div class="job-stat-value">${stats.shortlisted}</div>
                                    </div>
                                    <div class="job-stat-item">
                                        <div class="job-stat-title">Hired</div>
                                        <div class="job-stat-value">${stats.hired}</div>
                                    </div>
                                </div>
                                
                                ${applicants.length > 0 ? `
                                    <div class="applicants-list" style="display: flex; flex-direction: column; gap: 15px;">
                                        ${applicants.map(applicant => `
                                            <div class="applicant-card" data-applicant-id="${applicant.application_id}">
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; align-items: start;">
                                                    <div style="flex: 1;">
                                                        <h4 style="font-size: 16px; margin-bottom: 5px; color: var(--text-dark);">${escapeHtml(applicant.name)}</h4>
                                                        <div style="color: var(--text-medium); font-size: 14px; margin-bottom: 5px;">
                                                            <i class="fas fa-envelope"></i> ${escapeHtml(applicant.email)}
                                                            ${applicant.phone ? ` | <i class="fas fa-phone"></i> ${escapeHtml(applicant.phone)}` : ''}
                                                        </div>
                                                        ${applicant.location !== 'Not specified' ? `
                                                            <div style="color: var(--text-medium); font-size: 14px; margin-bottom: 5px;">
                                                                <i class="fas fa-map-marker-alt"></i> ${escapeHtml(applicant.location)}
                                                            </div>
                                                        ` : ''}
                                                        ${applicant.headline ? `
                                                            <div style="color: var(--text-dark); font-size: 14px; font-weight: 500; margin-bottom: 5px;">
                                                                ${escapeHtml(applicant.headline)}
                                                            </div>
                                                        ` : ''}
                                                    </div>
                                                    <span class="status-badge status-${applicant.application.status.toLowerCase().replace('_', '-')}">${formatStatus(applicant.application.status)}</span>
                                                </div>
                                                
                                                <div style="margin-bottom: 10px;">
                                                    <div style="font-size: 14px; color: var(--text-medium); margin-bottom: 3px;">Applied</div>
                                                    <div style="font-weight: 500; color: var(--text-dark);">${formatDate(applicant.application.applied_at)}</div>
                                                </div>
                                                
                                                ${applicant.skills.length > 0 ? `
                                                    <div style="margin-bottom: 10px;">
                                                        <div style="font-size: 14px; color: var(--text-medium); margin-bottom: 3px;">Skills</div>
                                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                                            ${applicant.skills.map(skill => `
                                                                <span style="background-color: rgba(52, 152, 219, 0.1); color: var(--info); padding: 3px 8px; border-radius: 15px; font-size: 12px;">
                                                                    ${escapeHtml(skill)}
                                                                </span>
                                                            `).join('')}
                                                        </div>
                                                    </div>
                                                ` : ''}
                                                
                                                <div style="margin-bottom: 10px;">
                                                    <div style="font-size: 14px; color: var(--text-medium); margin-bottom: 3px;">Disability Information</div>
                                                    <div style="font-weight: 500; color: var(--text-dark);">${escapeHtml(applicant.disability.type)} (${escapeHtml(applicant.disability.category)})</div>
                                                </div>
                                                
                                                ${applicant.accommodations.needed ? `
                                                    <div style="margin-bottom: 10px;">
                                                        <div style="font-size: 14px; color: var(--text-medium); margin-bottom: 3px;">Requested Accommodations</div>
                                                        <div style="font-weight: 500; color: var(--text-dark);">${escapeHtml(applicant.accommodations.list)}</div>
                                                    </div>
                                                ` : ''}
                                                
                                                ${applicant.application.cover_letter ? `
                                                    <div style="margin-bottom: 10px;">
                                                        <div style="font-size: 14px; color: var(--text-medium); margin-bottom: 3px;">Cover Letter</div>
                                                        <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; font-size: 13px; line-height: 1.4; max-height: 100px; overflow-y: auto;">
                                                            ${escapeHtml(applicant.application.cover_letter.substring(0, 200))}${applicant.application.cover_letter.length > 200 ? '...' : ''}
                                                        </div>
                                                    </div>
                                                ` : ''}
                                                
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 10px; border-top: 1px solid #e9ecef;">
                                                    <div style="display: flex; gap: 10px;">
                                                        ${applicant.resume ? `
                                                            <button class="modal-btn resume-btn" data-resume="${applicant.resume.resume_id}" style="background-color: #6c757d; color: white; font-size: 12px; padding: 6px 12px;">
                                                                <i class="fas fa-download"></i> Resume
                                                            </button>
                                                        ` : ''}
                                                        <button class="modal-btn profile-btn" data-seeker-id="${applicant.seeker_id}" style="background-color: #17a2b8; color: white; font-size: 12px; padding: 6px 12px;">
                                                            <i class="fas fa-user"></i> View Profile
                                                        </button>
                                                    </div>
                                                    <button class="modal-btn update-status-btn" data-application-id="${applicant.application_id}" data-current-status="${applicant.application.status}" style="background-color: var(--primary); color: white; font-size: 12px; padding: 6px 12px;">
                                                        <i class="fas fa-edit"></i> Update Status
                                                    </button>
                                                </div>
                                                
                                                <!-- Status Update Dialog (Hidden by Default) -->
                                                <div class="status-update-dialog" id="status-dialog-${applicant.application_id}" style="display: none;">
                                                    <div style="margin-top: 10px; padding: 12px; background: #f8f9fa; border-radius: 6px;">
                                                        <div style="margin-bottom: 10px; font-weight: 500;">Update Application Status:</div>
                                                        <div class="status-options" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;">
                                                            <div class="status-option" data-status="submitted" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Submitted</div>
                                                            <div class="status-option" data-status="under_review" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Under Review</div>
                                                            <div class="status-option" data-status="shortlisted" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Shortlisted</div>
                                                            <div class="status-option" data-status="interview_scheduled" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Interview</div>
                                                            <div class="status-option" data-status="hired" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Hired</div>
                                                            <div class="status-option" data-status="rejected" style="padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 16px; cursor: pointer; font-size: 12px;">Rejected</div>
                                                        </div>
                                                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                                            <button class="modal-btn cancel-status-btn" data-application-id="${applicant.application_id}" style="background-color: #6c757d; color: white; font-size: 12px; padding: 6px 12px;">
                                                                Cancel
                                                            </button>
                                                            <button class="modal-btn save-status-btn" data-application-id="${applicant.application_id}" style="background-color: #28a745; color: white; font-size: 12px; padding: 6px 12px;">
                                                                Save
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : `
                                    <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 8px; margin-top: 20px;">
                                        <div style="font-size: 48px; color: #dee2e6; margin-bottom: 15px;">
                                            <i class="fas fa-user-slash"></i>
                                        </div>
                                        <h4 style="font-size: 18px; color: #495057; margin-bottom: 10px;">No Applicants Yet</h4>
                                        <p style="color: #6c757d;">No one has applied to this job posting yet.</p>
                                    </div>
                                `}
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                            <button class="modal-btn btn-cancel" id="close-details-btn" style="background-color: #6c757d; color: white; padding: 10px 20px;">Close</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Add the modal HTML to the overlay
            modalOverlay.innerHTML = modalHTML;
            
            // Append the modal to the body
            document.body.appendChild(modalOverlay);
            
            // Show the modal with animation
            setTimeout(() => {
                modalOverlay.style.display = 'flex';
                setTimeout(() => {
                    modalOverlay.classList.add('show');
                }, 10);
            }, 0);
            
            // Setup modal functionality
            setupJobDetailsModalEventListeners(modalOverlay);
            
        } catch (error) {
            hideLoading();
            console.error('Error showing job details:', error);
            showErrorMessage('Failed to load job details: ' + error.message);
        }
    }

    // Setup event listeners for job details modal
    function setupJobDetailsModalEventListeners(modalOverlay) {
        // Setup tabs
        const tabs = modalOverlay.querySelectorAll('.job-view-tab');
        const tabContents = modalOverlay.querySelectorAll('.job-view-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.dataset.tab;
                
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to selected tab and content
                tab.classList.add('active');
                modalOverlay.querySelector(`#${tabId}-tab`).classList.add('active');
            });
        });

        // Setup resume download buttons
        const resumeButtons = modalOverlay.querySelectorAll('.resume-btn');
        resumeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const resumeId = this.dataset.resume;
                // TODO: Implement resume download
                alert(`Download resume ID: ${resumeId}\nResume download will be implemented in next step.`);
            });
        });
        
        // Setup profile view buttons
        const profileButtons = modalOverlay.querySelectorAll('.profile-btn');
        profileButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const seekerId = this.dataset.seekerId;
                // TODO: Implement profile view
                alert(`View profile for seeker ID: ${seekerId}\nProfile view will be implemented in next step.`);
            });
        });
        
        // Setup update status buttons
        const updateStatusButtons = modalOverlay.querySelectorAll('.update-status-btn');
        updateStatusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.dataset.applicationId;
                const currentStatus = this.dataset.currentStatus;
                const statusDialog = modalOverlay.querySelector(`#status-dialog-${applicationId}`);
                
                // Highlight current status
                const statusOptions = statusDialog.querySelectorAll('.status-option');
                statusOptions.forEach(option => {
                    option.classList.remove('selected');
                    if (option.dataset.status === currentStatus) {
                        option.classList.add('selected');
                        option.style.backgroundColor = '#3b82f6';
                        option.style.color = 'white';
                    }
                });
                
                statusDialog.style.display = 'block';
            });
        });
        
        // Setup cancel status buttons
        const cancelStatusButtons = modalOverlay.querySelectorAll('.cancel-status-btn');
        cancelStatusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.dataset.applicationId;
                const statusDialog = modalOverlay.querySelector(`#status-dialog-${applicationId}`);
                statusDialog.style.display = 'none';
            });
        });
        
        // Setup status options
        const statusOptions = modalOverlay.querySelectorAll('.status-option');
        statusOptions.forEach(option => {
            option.addEventListener('click', function() {
                const parentDialog = this.closest('.status-update-dialog');
                parentDialog.querySelectorAll('.status-option').forEach(opt => {
                    opt.classList.remove('selected');
                    opt.style.backgroundColor = '';
                    opt.style.color = '';
                });
                this.classList.add('selected');
                this.style.backgroundColor = '#3b82f6';
                this.style.color = 'white';
            });
        });
        
        // Setup save status buttons
        const saveStatusButtons = modalOverlay.querySelectorAll('.save-status-btn');
        saveStatusButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.dataset.applicationId;
                const statusDialog = modalOverlay.querySelector(`#status-dialog-${applicationId}`);
                const selectedOption = statusDialog.querySelector('.status-option.selected');
                
                if (selectedOption) {
                    const newStatus = selectedOption.dataset.status;
                    
                    // TODO: Implement API call to update status
                    alert(`Update application ${applicationId} to status: ${newStatus}\nStatus update API will be implemented in next step.`);
                    
                    // Hide the dialog
                    statusDialog.style.display = 'none';
                } else {
                    alert('Please select a status first.');
                }
            });
        });
        
        // Setup close buttons
        const closeBtn = modalOverlay.querySelector('.modal-close');
        const cancelBtn = modalOverlay.querySelector('#close-details-btn');
        
        closeBtn.addEventListener('click', () => closeModal(modalOverlay));
        cancelBtn.addEventListener('click', () => closeModal(modalOverlay));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal(modalOverlay);
            }
        });
    }

    // Helper function to format application status
    function formatStatus(status) {
        const statusMap = {
            'submitted': 'Submitted',
            'under_review': 'Under Review',
            'shortlisted': 'Shortlisted',
            'interview_scheduled': 'Interview Scheduled',
            'interviewed': 'Interviewed',
            'hired': 'Hired',
            'rejected': 'Rejected',
            'withdrawn': 'Withdrawn'
        };
        return statusMap[status] || status;
    }

    // Helper function to format date
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return `${date.toLocaleString('default', { month: 'short' })} ${date.getDate()}, ${date.getFullYear()}`;
    }

    function handleJobAction(action, jobCard, jobTitle, jobId) {
        // Find the job data
        const jobData = currentJobData.find(job => job.job_id == jobId);
        
        if (!jobData && action !== 'view') {
            showErrorMessage('Job data not found');
            return;
        }

        switch(action) {
            case 'view':
                // Show job details modal - NOW FUNCTIONAL!
                showJobDetailsModal(jobId);
                break;
                
            case 'edit':
                // Open edit form (placeholder)
                openEditJobForm(jobId);
                break;
                
            case 'delete':
                // Show delete confirmation (placeholder)
                initiateJobDeletion(jobId);
                break;
                
            case 'toggle':
                // Toggle job status (placeholder)
                initiateJobStatusToggle(jobId);
                break;
        }
    }

    // Update existing job via API
    async function updateJob(jobData) {
        try {
            console.log('🔄 Updating job with data:', jobData);
            
            const response = await fetch('../../backend/employer/update_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(jobData)
            });
            
            const responseText = await response.text();
            console.log('📥 Raw response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid response from server: ' + responseText.substring(0, 100));
            }
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP error! status: ${response.status}`);
            }
            
            if (data.success) {
                console.log('✅ Job updated successfully:', data);
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to update job');
            }
            
        } catch (error) {
            console.error('Update job error:', error);
            throw error;
        }
    }

    // Get detailed job data for editing (use existing data or fetch fresh)
    async function getJobDataForEdit(jobId) {
        try {
            // Try to find job in current data first
            let jobData = currentJobData.find(job => job.job_id == jobId);
            
            if (jobData && jobData.accommodations) {
                // We have complete data, use it
                return jobData;
            }
            
            // Otherwise fetch detailed data
            const response = await fetch(`../../backend/employer/get_job_details.php?job_id=${jobId}`);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to get job data');
            }

            if (data.success) {
                return data.data.job;
            } else {
                throw new Error(data.message || 'Failed to get job data');
            }

        } catch (error) {
            console.error('Error getting job data for edit:', error);
            throw error;
        }
    }

    // Open edit job form with pre-populated data
    async function openEditJobForm(jobId) {
        try {
            showLoading();
            
            // Get complete job data
            const jobData = await getJobDataForEdit(jobId);
            
            hideLoading();
            
            // Open the job form modal in edit mode
            createJobFormModal(jobData);
            
        } catch (error) {
            hideLoading();
            console.error('Error opening edit form:', error);
            showErrorMessage('Failed to load job data for editing: ' + error.message);
        }
    }

    // Update job card in DOM after successful edit
    function updateJobCardInDOM(jobData) {
        // Find the job card with the matching job ID
        const jobCard = document.querySelector(`.job-card[data-job-id="${jobData.job_id}"]`);
        
        if (!jobCard) {
            console.error('Job card not found for ID:', jobData.job_id);
            return;
        }
        
        // Update the job card classes based on status
        jobCard.className = 'job-card';
        if (jobData.job_status === 'draft') {
            jobCard.className += ' draft';
        } else if (jobData.job_status === 'closed') {
            jobCard.className += ' closed';
        }
        
        // Update dataset attributes
        jobCard.dataset.status = jobData.job_status;
        jobCard.dataset.title = jobData.job_title;
        jobCard.dataset.date = jobData.posted_at;
        jobCard.dataset.applicants = jobData.applications_count;
        
        // Format the date
        const datePosted = new Date(jobData.posted_at);
        const formattedDate = `${datePosted.toLocaleString('default', { month: 'short' })} ${datePosted.getDate()}, ${datePosted.getFullYear()}`;
        
        // Create location display with work arrangements
        let locationDisplay = jobData.location;
        if (jobData.remote_work_available) {
            locationDisplay += ' / Remote';
        }
        
        // Update job card content
        jobCard.innerHTML = `
            <div class="job-card-header">
                <div>
                    <h3 class="job-title">${escapeHtml(jobData.job_title)}</h3>
                    <div class="job-company">${escapeHtml(jobData.company_name || window.employerData.company_name)}</div>
                    <div class="job-badges">
                        <span class="job-badge badge-location">
                            <i class="fas fa-map-marker-alt"></i>
                            ${escapeHtml(locationDisplay)}
                        </span>
                        <span class="job-badge badge-type">
                            <i class="fas fa-clock"></i>
                            ${escapeHtml(jobData.employment_type)}
                        </span>
                    </div>
                </div>
                <span class="job-status status-${jobData.job_status}">${capitalizeFirst(jobData.job_status)}</span>
            </div>
            <div class="job-card-body">
                <div class="job-info-group">
                    <div class="job-info">
                        <span class="job-info-label">Applicants</span>
                        <span class="job-info-value">
                            <i class="fas fa-users"></i>
                            ${jobData.applications_count}
                        </span>
                    </div>
                    <div class="job-info">
                        <span class="job-info-label">${jobData.job_status === 'draft' ? 'Date Created' : 'Date Posted'}</span>
                        <span class="job-info-value">
                            <i class="far fa-calendar-alt"></i>
                            ${formattedDate}
                        </span>
                    </div>
                </div>
                <div class="job-actions">
                    <button class="job-action-btn action-view" title="View Job">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="job-action-btn action-edit" title="Edit Job">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="job-action-btn action-delete" title="Delete Job">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button class="job-action-btn action-toggle" title="${getToggleTitle(jobData.job_status)}">
                        <i class="fas fa-${getToggleIcon(jobData.job_status)}"></i>
                    </button>
                </div>
            </div>
        `;
        
        // Add highlight effect to show it was updated
        jobCard.style.transition = 'background-color 0.3s ease';
        jobCard.style.backgroundColor = 'rgba(37, 113, 128, 0.1)';
        
        setTimeout(() => {
            jobCard.style.backgroundColor = '';
            
            // Re-setup action buttons for the updated job card
            setupJobActionButtons(jobCard);
            
            // Update the job in currentJobData array
            const jobIndex = currentJobData.findIndex(job => job.job_id == jobData.job_id);
            if (jobIndex !== -1) {
                currentJobData[jobIndex] = jobData;
            }
            
            // Refilter if needed
            if (statusFilter && statusFilter.value !== 'all') {
                filterJobs();
            }
        }, 1000);
    }

    function updateFormSubmissionHandler(modalOverlay, isEditing, jobData) {
        const form = modalOverlay.querySelector('#job-post-form');
        const submitBtn = modalOverlay.querySelector('#submit-job-btn');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            const btnText = submitBtn.querySelector('.btn-text');
            const originalText = btnText.textContent;
            btnText.textContent = isEditing ? 'Updating...' : 'Posting...';
            
            try {
                // Get form values (same as before)
                const formData = {
                    job_title: form.querySelector('#job-title').value.trim(),
                    department: form.querySelector('#department').value,
                    location: form.querySelector('#job-location').value.trim(),
                    employment_type: form.querySelector('#employment-type').value,
                    salary_range: form.querySelector('#salary-range').value.trim(),
                    application_deadline: form.querySelector('#application-deadline').value,
                    job_description: form.querySelector('#job-description').value.trim(),
                    job_requirements: form.querySelector('#job-requirements').value.trim(),
                    remote_work_available: form.querySelector('#remote-work').checked,
                    flexible_schedule: form.querySelector('#flexible-schedule').checked,
                    
                    accommodations: {
                        wheelchair_accessible: form.querySelector('#wheelchair-accessible').checked,
                        assistive_technology: form.querySelector('#assistive-technology').checked,
                        remote_work_option: form.querySelector('#remote-work-option').checked,
                        screen_reader_compatible: form.querySelector('#screen-reader-compatible').checked,
                        sign_language_interpreter: form.querySelector('#sign-language-interpreter').checked,
                        modified_workspace: form.querySelector('#modified-workspace').checked,
                        transportation_support: form.querySelector('#transportation-support').checked,
                        additional_accommodations: form.querySelector('#additional-accommodations').value.trim()
                    },
                    
                    required_skills: [] // To be implemented later
                };

                let result;
                
                if (isEditing) {
                    // Add job_id for update
                    formData.job_id = jobData.job_id;
                    
                    // Update the job
                    result = await updateJob(formData);
                    
                    // Update the job card in the DOM
                    updateJobCardInDOM(result);
                    
                    showSuccessMessage('Job updated successfully!');
                } else {
                    // Create new job
                    formData.job_status = 'active'; // Default to active for new jobs
                    result = await createJob(formData);
                    
                    // Reload job listings to show the new job
                    await loadJobListings();
                    
                    showSuccessMessage('Job posted successfully!');
                }
                
                // Close modal
                closeModal(modalOverlay);
                
            } catch (error) {
                console.error('Error submitting job:', error);
                showErrorMessage('Failed to ' + (isEditing ? 'update' : 'post') + ' job: ' + error.message);
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                btnText.textContent = originalText;
            }
        });
    }
/*
    function setupJobFormEventListeners(modalOverlay, isEditing, jobData = null) {
        const closeBtn = modalOverlay.querySelector('#close-job-form');
        const cancelBtn = modalOverlay.querySelector('#cancel-job-form');
        
        // Close modal events
        closeBtn.addEventListener('click', () => closeModal(modalOverlay));
        cancelBtn.addEventListener('click', () => closeModal(modalOverlay));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal(modalOverlay);
            }
        });
        
        // Setup accommodation checkboxes
        const accommodationItems = modalOverlay.querySelectorAll('.accommodation-item');
        accommodationItems.forEach(item => {
            const checkbox = item.querySelector('.accommodation-checkbox');
            
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    item.classList.add('checked');
                } else {
                    item.classList.remove('checked');
                }
            });
        });
        
        // Setup form submission with new handler
        updateFormSubmissionHandler(modalOverlay, isEditing, jobData);
    }
*/
    // Delete job via API
    async function deleteJob(jobId) {
        try {
            const response = await fetch(`../../backend/employer/delete_job.php?job_id=${jobId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to delete job');
            }

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to delete job');
            }

        } catch (error) {
            console.error('Error deleting job:', error);
            throw error;
        }
    }

    // Show delete confirmation modal
    function showDeleteConfirmationModal(jobData) {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay delete-confirmation-modal';
        
        // Check if job has applications
        const hasApplications = jobData.applications_count > 0;
        const warningMessage = hasApplications ? 
            `<div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 18px;"></i>
                    <div>
                        <strong>Warning:</strong> This job has ${jobData.applications_count} application${jobData.applications_count !== 1 ? 's' : ''}. 
                        Deleting this job will also remove all application data, interviews, and related information.
                    </div>
                </div>
            </div>` : '';
        
        const modalHTML = `
            <div class="modal" style="max-width: 500px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fas fa-trash" style="color: #dc3545; margin-right: 10px;"></i>
                        Confirm Delete
                    </h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 16px; margin-bottom: 10px;">
                            Are you sure you want to delete the job listing:
                        </p>
                        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #dc3545;">
                            <strong style="font-size: 16px; color: #495057;">"${escapeHtml(jobData.job_title)}"</strong>
                            <div style="font-size: 14px; color: #6c757d; margin-top: 5px;">
                                ${escapeHtml(jobData.department)} • ${escapeHtml(jobData.employment_type)}
                            </div>
                        </div>
                    </div>
                    
                    ${warningMessage}
                    
                    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 6px; margin: 15px 0;">
                        <div style="display: flex; align-items: center; gap: 10px; color: #721c24;">
                            <i class="fas fa-exclamation-circle" style="font-size: 16px;"></i>
                            <strong>This action cannot be undone!</strong>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 25px;">
                        <button class="modal-btn btn-cancel" id="cancel-delete" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="modal-btn btn-delete" id="confirm-delete" style="background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            <i class="fas fa-trash"></i> 
                            <span class="delete-btn-text">Delete Job</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        modalOverlay.innerHTML = modalHTML;
        document.body.appendChild(modalOverlay);
        
        // Show modal with animation
        setTimeout(() => {
            modalOverlay.style.display = 'flex';
            setTimeout(() => {
                modalOverlay.classList.add('show');
            }, 10);
        }, 0);
        
        // Setup event listeners
        const closeBtn = modalOverlay.querySelector('.modal-close');
        const cancelBtn = modalOverlay.querySelector('#cancel-delete');
        const deleteBtn = modalOverlay.querySelector('#confirm-delete');
        const deleteBtnText = modalOverlay.querySelector('.delete-btn-text');
        
        // Close events
        closeBtn.addEventListener('click', () => closeModal(modalOverlay));
        cancelBtn.addEventListener('click', () => closeModal(modalOverlay));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal(modalOverlay);
            }
        });
        
        // Delete confirmation
        deleteBtn.addEventListener('click', async () => {
            try {
                // Disable button and show loading
                deleteBtn.disabled = true;
                deleteBtnText.textContent = 'Deleting...';
                
                // Call delete API
                const result = await deleteJob(jobData.job_id);
                
                // Remove job card from DOM
                removeJobCardFromDOM(jobData.job_id);
                
                // Close modal
                closeModal(modalOverlay);
                
                // Show success message
                const successMsg = result.had_applications ? 
                    `Job "${jobData.job_title}" and ${result.applications_count} application${result.applications_count !== 1 ? 's' : ''} deleted successfully!` :
                    `Job "${jobData.job_title}" deleted successfully!`;
                
                showSuccessMessage(successMsg);
                
                // Check if we need to show empty state
                setTimeout(() => {
                    checkEmptyState();
                }, 500);
                
            } catch (error) {
                console.error('Error deleting job:', error);
                showErrorMessage('Failed to delete job: ' + error.message);
                
                // Re-enable button
                deleteBtn.disabled = false;
                deleteBtnText.textContent = 'Delete Job';
            }
        });
    }

    // Remove job card from DOM
    function removeJobCardFromDOM(jobId) {
        const jobCard = document.querySelector(`.job-card[data-job-id="${jobId}"]`);
        
        if (jobCard) {
            // Add animation for removal
            jobCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            jobCard.style.opacity = '0';
            jobCard.style.transform = 'translateY(-20px)';
            
            // Remove from DOM after animation
            setTimeout(() => {
                if (jobCard.parentNode) {
                    jobCard.parentNode.removeChild(jobCard);
                }
                
                // Remove from currentJobData array
                const jobIndex = currentJobData.findIndex(job => job.job_id == jobId);
                if (jobIndex !== -1) {
                    currentJobData.splice(jobIndex, 1);
                }
                
                // Update any statistics if needed
                updateStatistics({
                    total_jobs: currentJobData.length
                });
                
            }, 300);
        }
    }

    // Initiate job deletion process
    function initiateJobDeletion(jobId) {
        // Find job data
        const jobData = currentJobData.find(job => job.job_id == jobId);
        
        if (!jobData) {
            showErrorMessage('Job data not found');
            return;
        }
        
        // Show confirmation modal
        showDeleteConfirmationModal(jobData);
    }

    // Enhanced error message function for delete operations
    function showDeleteErrorMessage(message, details = null) {
        let fullMessage = message;
        
        if (details) {
            if (details.includes('foreign key')) {
                fullMessage = 'Cannot delete job: This job has applications or other related data. Please contact support if you need to remove this job.';
            } else if (details.includes('not found')) {
                fullMessage = 'Job not found or already deleted.';
            }
        }
        
        showErrorMessage(fullMessage);
    }

    // ALSO ADD this enhanced checkEmptyState function (if you don't have one):
    function checkEmptyState() {
        const jobCards = jobListings.querySelectorAll('.job-card');
        
        if (jobCards.length === 0) {
            // Check if we're in a filtered state
            const hasActiveFilters = (searchInput && searchInput.value.trim()) || 
                                    (statusFilter && statusFilter.value !== 'all');
            
            showEmptyState(hasActiveFilters);
        } else {
            hideEmptyState();
        }
    }

    // Enhanced success message for deletion
    function showDeletionSuccessMessage(jobTitle, hadApplications, applicationsCount) {
        let message = `Job "${jobTitle}" deleted successfully!`;
        
        if (hadApplications) {
            message += ` ${applicationsCount} application${applicationsCount !== 1 ? 's' : ''} were also removed.`;
        }
        
        showSuccessMessage(message);
    }

    // Keyboard shortcut for delete (optional enhancement)
    function setupDeleteKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Delete key when job card is focused
            if (e.key === 'Delete' && document.activeElement.classList.contains('job-card')) {
                const jobId = document.activeElement.dataset.jobId;
                if (jobId) {
                    initiateJobDeletion(jobId);
                }
            }
        });
    }

    // Toggle job status via API
    async function toggleJobStatus(jobId, newStatus) {
        try {
            const response = await fetch('../../backend/employer/toggle_job_status.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    job_id: jobId,
                    new_status: newStatus
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to update job status');
            }

            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Failed to update job status');
            }

        } catch (error) {
            console.error('Error toggling job status:', error);
            throw error;
        }
    }

    // Determine next status based on current status
    function getNextStatus(currentStatus) {
        const statusFlow = {
            'draft': 'active',      // Draft → Active (Publish)
            'active': 'closed',     // Active → Closed (Close)
            'closed': 'active',     // Closed → Active (Reopen) 
            'paused': 'active'      // Paused → Active (Resume)
        };
        
        return statusFlow[currentStatus] || 'active';
    }

    // Get user-friendly action text for status change
    function getStatusActionText(currentStatus, newStatus) {
        const actionTexts = {
            'draft_to_active': 'Publish Job',
            'active_to_closed': 'Close Job',
            'closed_to_active': 'Reopen Job',
            'paused_to_active': 'Resume Job',
            'active_to_paused': 'Pause Job'
        };
        
        const key = `${currentStatus}_to_${newStatus}`;
        return actionTexts[key] || 'Update Status';
    }

    // Get status change confirmation message
    function getStatusConfirmationMessage(jobTitle, currentStatus, newStatus) {
        const messages = {
            'draft_to_active': `Publish "${jobTitle}" and make it visible to job seekers?`,
            'active_to_closed': `Close "${jobTitle}" to new applications?`,
            'closed_to_active': `Reopen "${jobTitle}" for new applications?`,
            'paused_to_active': `Resume "${jobTitle}" and make it active again?`,
            'active_to_paused': `Pause "${jobTitle}" temporarily?`
        };
        
        const key = `${currentStatus}_to_${newStatus}`;
        return messages[key] || `Change status of "${jobTitle}" from ${currentStatus} to ${newStatus}?`;
    }

    // Get success message for status change
    function getStatusSuccessMessage(jobTitle, currentStatus, newStatus) {
        const messages = {
            'draft_to_active': `"${jobTitle}" has been published and is now live!`,
            'active_to_closed': `"${jobTitle}" has been closed to new applications.`,
            'closed_to_active': `"${jobTitle}" has been reopened for applications.`,
            'paused_to_active': `"${jobTitle}" has been resumed and is now active.`,
            'active_to_paused': `"${jobTitle}" has been paused temporarily.`
        };
        
        const key = `${currentStatus}_to_${newStatus}`;
        return messages[key] || `"${jobTitle}" status changed to ${newStatus}.`;
    }

    // Show status change confirmation modal
    function showStatusConfirmationModal(jobData, newStatus) {
        const currentStatus = jobData.job_status;
        const actionText = getStatusActionText(currentStatus, newStatus);
        const confirmMessage = getStatusConfirmationMessage(jobData.job_title, currentStatus, newStatus);
        
        // Determine modal styling based on action
        const isPublishing = currentStatus === 'draft' && newStatus === 'active';
        const isClosing = currentStatus === 'active' && newStatus === 'closed';
        
        const modalClass = isPublishing ? 'publish-modal' : (isClosing ? 'close-modal' : 'status-modal');
        const headerColor = isPublishing ? '#28a745' : (isClosing ? '#dc3545' : '#007bff');
        const buttonColor = isPublishing ? '#28a745' : (isClosing ? '#dc3545' : '#007bff');
        const iconClass = isPublishing ? 'fa-paper-plane' : (isClosing ? 'fa-lock' : 'fa-toggle-on');
        
        const modalOverlay = document.createElement('div');
        modalOverlay.className = `modal-overlay status-confirmation-modal ${modalClass}`;
        
        const modalHTML = `
            <div class="modal" style="max-width: 450px;">
                <div class="modal-header" style="border-bottom: 1px solid #dee2e6; background-color: ${headerColor}10;">
                    <h3 class="modal-title" style="color: ${headerColor};">
                        <i class="fas ${iconClass}" style="margin-right: 10px;"></i>
                        ${actionText}
                    </h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 16px; margin-bottom: 15px; line-height: 1.5;">
                            ${confirmMessage}
                        </p>
                        
                        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid ${headerColor};">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                                <strong style="color: #495057;">"${escapeHtml(jobData.job_title)}"</strong>
                            </div>
                            <div style="font-size: 14px; color: #6c757d;">
                                ${escapeHtml(jobData.department)} • ${escapeHtml(jobData.employment_type)}
                                ${jobData.applications_count > 0 ? ` • ${jobData.applications_count} applicant${jobData.applications_count !== 1 ? 's' : ''}` : ''}
                            </div>
                            <div style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                Current Status: <span style="font-weight: 500; text-transform: capitalize;">${currentStatus}</span>
                                →
                                New Status: <span style="font-weight: 500; text-transform: capitalize; color: ${headerColor};">${newStatus}</span>
                            </div>
                        </div>
                    </div>
                    
                    ${jobData.applications_count > 0 && newStatus === 'closed' ? `
                        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 6px; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px; color: #856404; font-size: 14px;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>This job has ${jobData.applications_count} application${jobData.applications_count !== 1 ? 's' : ''}. Closing it will stop new applications but existing ones will remain.</span>
                            </div>
                        </div>
                    ` : ''}
                    
                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                        <button class="modal-btn btn-cancel" id="cancel-status-change" style="background-color: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button class="modal-btn btn-confirm" id="confirm-status-change" style="background-color: ${buttonColor}; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                            <i class="fas ${iconClass}"></i> 
                            <span class="confirm-btn-text">${actionText}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        modalOverlay.innerHTML = modalHTML;
        document.body.appendChild(modalOverlay);
        
        // Show modal with animation
        setTimeout(() => {
            modalOverlay.style.display = 'flex';
            setTimeout(() => {
                modalOverlay.classList.add('show');
            }, 10);
        }, 0);
        
        // Setup event listeners
        const closeBtn = modalOverlay.querySelector('.modal-close');
        const cancelBtn = modalOverlay.querySelector('#cancel-status-change');
        const confirmBtn = modalOverlay.querySelector('#confirm-status-change');
        const confirmBtnText = modalOverlay.querySelector('.confirm-btn-text');
        
        // Close events
        closeBtn.addEventListener('click', () => closeModal(modalOverlay));
        cancelBtn.addEventListener('click', () => closeModal(modalOverlay));
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal(modalOverlay);
            }
        });
        
        // Confirm status change
        confirmBtn.addEventListener('click', async () => {
            try {
                // Disable button and show loading
                confirmBtn.disabled = true;
                confirmBtnText.textContent = 'Updating...';
                
                // Call toggle API
                const result = await toggleJobStatus(jobData.job_id, newStatus);
                
                // Update job card in DOM
                updateJobCardStatusInDOM(result.job);
                
                // Close modal
                closeModal(modalOverlay);
                
                // Show success message
                const successMsg = getStatusSuccessMessage(jobData.job_title, currentStatus, newStatus);
                showSuccessMessage(successMsg);
                
                // Update job in currentJobData array
                const jobIndex = currentJobData.findIndex(job => job.job_id == jobData.job_id);
                if (jobIndex !== -1) {
                    currentJobData[jobIndex] = result.job;
                }
                
            } catch (error) {
                console.error('Error changing job status:', error);
                showErrorMessage('Failed to update job status: ' + error.message);
                
                // Re-enable button
                confirmBtn.disabled = false;
                confirmBtnText.textContent = actionText;
            }
        });
    }

    // Update job card status in DOM
    function updateJobCardStatusInDOM(jobData) {
        const jobCard = document.querySelector(`.job-card[data-job-id="${jobData.job_id}"]`);
        
        if (!jobCard) {
            console.error('Job card not found for ID:', jobData.job_id);
            return;
        }
        
        // Update the job card classes
        jobCard.className = 'job-card';
        if (jobData.job_status === 'draft') {
            jobCard.className += ' draft';
        } else if (jobData.job_status === 'closed') {
            jobCard.className += ' closed';
        } else if (jobData.job_status === 'paused') {
            jobCard.className += ' paused';
        }
        
        // Update dataset attributes
        jobCard.dataset.status = jobData.job_status;
        jobCard.dataset.title = jobData.job_title;
        jobCard.dataset.date = jobData.posted_at;
        jobCard.dataset.applicants = jobData.applications_count;
        
        // Update status badge
        const statusBadge = jobCard.querySelector('.job-status');
        if (statusBadge) {
            statusBadge.className = `job-status status-${jobData.job_status}`;
            statusBadge.textContent = capitalizeFirst(jobData.job_status);
        }
        
        // Update toggle button
        const toggleBtn = jobCard.querySelector('.action-toggle');
        const toggleIcon = toggleBtn.querySelector('i');
        
        if (toggleBtn && toggleIcon) {
            const nextStatus = getNextStatus(jobData.job_status);
            const newTitle = getToggleTitle(jobData.job_status);
            const newIcon = getToggleIcon(jobData.job_status);
            
            toggleBtn.setAttribute('title', newTitle);
            toggleIcon.className = `fas fa-${newIcon}`;
        }
        
        // Update date label if status changed to/from draft
        const dateLabel = jobCard.querySelector('.job-info-label');
        if (dateLabel && dateLabel.textContent.includes('Date')) {
            dateLabel.textContent = jobData.job_status === 'draft' ? 'Date Created' : 'Date Posted';
        }
        
        // Add visual feedback for status change
        jobCard.style.transition = 'all 0.3s ease';
        jobCard.style.transform = 'scale(1.02)';
        jobCard.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        
        setTimeout(() => {
            jobCard.style.transform = 'scale(1)';
            jobCard.style.boxShadow = '';
            
            // Re-setup action buttons
            setupJobActionButtons(jobCard);
            
            // Refilter if needed
            if (statusFilter && statusFilter.value !== 'all') {
                setTimeout(() => {
                    filterJobs();
                }, 100);
            }
        }, 300);
    }

    // Initiate job status toggle process
    function initiateJobStatusToggle(jobId) {
        // Find job data
        const jobData = currentJobData.find(job => job.job_id == jobId);
        
        if (!jobData) {
            showErrorMessage('Job data not found');
            return;
        }
        
        const currentStatus = jobData.job_status;
        const newStatus = getNextStatus(currentStatus);
        
        // For simple toggles, we might skip confirmation for some cases
        const skipConfirmation = false; // Set to true if you want instant toggles
        
        if (skipConfirmation) {
            // Direct toggle without confirmation
            toggleJobStatusDirect(jobData, newStatus);
        } else {
            // Show confirmation modal
            showStatusConfirmationModal(jobData, newStatus);
        }
    }

    // Direct status toggle without confirmation (optional)
    async function toggleJobStatusDirect(jobData, newStatus) {
        try {
            showLoading();
            
            const result = await toggleJobStatus(jobData.job_id, newStatus);
            
            updateJobCardStatusInDOM(result.job);
            
            const successMsg = getStatusSuccessMessage(jobData.job_title, jobData.job_status, newStatus);
            showSuccessMessage(successMsg);
            
            // Update job in currentJobData array
            const jobIndex = currentJobData.findIndex(job => job.job_id == jobData.job_id);
            if (jobIndex !== -1) {
                currentJobData[jobIndex] = result.job;
            }
            
            hideLoading();
            
        } catch (error) {
            hideLoading();
            console.error('Error changing job status:', error);
            showErrorMessage('Failed to update job status: ' + error.message);
        }
    }

    // Enhanced getToggleTitle function (update existing one)
    function getToggleTitle(status) {
        switch (status) {
            case 'active': return 'Close Job';
            case 'draft': return 'Publish Job';
            case 'closed': return 'Reopen Job';
            case 'paused': return 'Resume Job';
            default: return 'Toggle Status';
        }
    }

    // Enhanced getToggleIcon function (update existing one)
    function getToggleIcon(status) {
        switch (status) {
            case 'active': return 'lock';
            case 'draft': return 'paper-plane';
            case 'closed': return 'lock-open';
            case 'paused': return 'play';
            default: return 'toggle-on';
        }
    }

    // Add status-specific styles (optional enhancement)
    function addStatusStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .job-card.paused {
                opacity: 0.7;
                border-left: 4px solid #ffc107;
            }
            
            .job-card.paused .job-status {
                background-color: #fff3cd;
                color: #856404;
            }
            
            .status-paused {
                background-color: #fff3cd !important;
                color: #856404 !important;
            }
            
            .status-confirmation-modal.publish-modal .modal {
                border-top: 4px solid #28a745;
            }
            
            .status-confirmation-modal.close-modal .modal {
                border-top: 4px solid #dc3545;
            }
            
            .status-confirmation-modal .modal-title {
                font-size: 18px;
            }
        `;
        document.head.appendChild(style);
    }

    // Function to load available skills
    async function loadAvailableSkills() {
        try {
            const response = await fetch('../../backend/employer/get_skills.php');
            const data = await response.json();
            
            if (data.success) {
                availableSkills = data.data.categories;
                console.log('✅ Loaded skills:', availableSkills.length, 'categories');
            } else {
                console.error('Failed to load skills:', data.message);
            }
        } catch (error) {
            console.error('Error loading skills:', error);
        }
    }

    // Generate skills selection HTML
    function generateSkillsSelectionHTML() {
        if (!availableSkills || availableSkills.length === 0) {
            return `
                <div class="skills-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading available skills...</p>
                </div>
            `;
        }
        
        let html = '<div class="skills-categories">';
        
        availableSkills.forEach(category => {
            html += `
                <div class="skill-category">
                    <div class="skill-category-header">
                        <i class="${category.category_icon}"></i>
                        <h5>${category.category_name}</h5>
                        <span class="skill-count">(${category.skills.length} skills)</span>
                    </div>
                    
                    <div class="skill-category-items">
            `;
            
            category.skills.forEach(skill => {
                html += `
                    <div class="skill-item">
                        <div class="skill-checkbox-group">
                            <input type="checkbox" 
                                id="skill-${skill.skill_id}" 
                                class="skill-checkbox" 
                                data-skill-id="${skill.skill_id}"
                                data-skill-name="${skill.skill_name}">
                            <label for="skill-${skill.skill_id}" class="skill-label">
                                ${skill.skill_name}
                            </label>
                        </div>
                        
                        <div class="skill-priority-group" style="display: none;">
                            <select class="skill-priority" data-skill-id="${skill.skill_id}">
                                <option value="important">Required</option>
                                <option value="preferred">Preferred</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        return html;
    }

    // Setup skills selection event listeners
    function setupSkillsSelectionListeners(modalOverlay) {
        // Handle skill checkbox changes
        modalOverlay.addEventListener('change', function(e) {
            if (e.target.classList.contains('skill-checkbox')) {
                const skillItem = e.target.closest('.skill-item');
                const priorityGroup = skillItem.querySelector('.skill-priority-group');
                
                if (e.target.checked) {
                    priorityGroup.style.display = 'block';
                } else {
                    priorityGroup.style.display = 'none';
                }
            }
        });
        
        // Select all skills
        const selectAllBtn = modalOverlay.querySelector('#select-all-skills');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const checkboxes = modalOverlay.querySelectorAll('.skill-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    const skillItem = checkbox.closest('.skill-item');
                    const priorityGroup = skillItem.querySelector('.skill-priority-group');
                    priorityGroup.style.display = 'block';
                });
            });
        }
        
        // Clear all skills
        const clearAllBtn = modalOverlay.querySelector('#clear-all-skills');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function() {
                const checkboxes = modalOverlay.querySelectorAll('.skill-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    const skillItem = checkbox.closest('.skill-item');
                    const priorityGroup = skillItem.querySelector('.skill-priority-group');
                    priorityGroup.style.display = 'none';
                });
            });
        }
    }

    // Function to get selected skills from form
    function getSelectedSkills(form) {
        const selectedSkills = [];
        const checkedSkills = form.querySelectorAll('.skill-checkbox:checked');
        
        checkedSkills.forEach(checkbox => {
            const skillId = checkbox.dataset.skillId;
            const skillName = checkbox.dataset.skillName;
            const prioritySelect = form.querySelector(`.skill-priority[data-skill-id="${skillId}"]`);
            const priority = prioritySelect ? prioritySelect.value : 'important';
            
            selectedSkills.push({
                skill_id: parseInt(skillId),
                skill_name: skillName,
                priority: priority,
                is_required: priority !== 'preferred'
            });
        });
        
        return selectedSkills;
    }
    // ADD STYLES FOR SKILLS SECTION
    const skillsStyles = `
    <style>
    .skills-selection-container {
        margin-top: 15px;
    }

    .skills-selection-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }

    .skills-help-text {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .skills-actions {
        display: flex;
        gap: 10px;
    }

    .btn-secondary {
        padding: 6px 12px;
        font-size: 12px;
        border: 1px solid #ddd;
        background: #f8f9fa;
        color: #495057;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .skills-categories {
        display: grid;
        gap: 20px;
    }

    .skill-category {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
    }

    .skill-category-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .skill-category-header h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #495057;
    }

    .skill-count {
        font-size: 12px;
        color: #6c757d;
        margin-left: auto;
    }

    .skill-category-items {
        padding: 16px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
    }

    .skill-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: #fff;
        transition: all 0.2s;
    }

    .skill-item:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }

    .skill-checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }

    .skill-checkbox {
        margin: 0;
    }

    .skill-label {
        margin: 0;
        font-size: 13px;
        color: #495057;
        cursor: pointer;
    }

    .skill-priority-group {
        margin-left: 10px;
    }

    .skill-priority {
        padding: 4px 8px;
        font-size: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
    }

    .skills-loading {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .skills-loading i {
        font-size: 24px;
        margin-bottom: 10px;
    }
    </style>
    `;

    document.head.insertAdjacentHTML('beforeend', skillsStyles);
});