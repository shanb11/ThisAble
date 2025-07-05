// Complete Applicants JavaScript with Interview System
document.addEventListener('DOMContentLoaded', function() {
    // ===================================
    // DOM ELEMENTS
    // ===================================
    
    // Sidebar elements
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');
    
    // Main content elements
    const applicantsGrid = document.getElementById('applicants-grid');
    const searchInput = document.querySelector('.search-input input');
    const jobFilter = document.getElementById('job-filter');
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const skillsFilter = document.getElementById('skills-filter');
    const applyFilterBtn = document.querySelector('.apply-filter');
    const resetFilterBtn = document.querySelector('.reset-filter');
    const loadingOverlay = document.getElementById('loading-overlay');
    const noResults = document.getElementById('no-results');
    const activeFiltersContainer = document.querySelector('.active-filters-container');
    const activeFiltersDiv = document.getElementById('active-filters');
    const statusConfirmation = document.getElementById('status-confirmation');
    
    // Modal elements
    const applicantModal = document.getElementById('applicantModal');
    const confirmStatusModal = document.getElementById('confirmStatusModal');
    const confirmStatusMessage = document.getElementById('confirm-status-message');
    const confirmStatusBtn = document.getElementById('confirm-status-btn');
    const cancelStatusBtn = document.getElementById('cancel-status-btn');

    // Interview modal elements
    const scheduleInterviewModal = document.getElementById('scheduleInterviewModal');
    const interviewSuccessModal = document.getElementById('interviewSuccessModal');
    const scheduleInterviewForm = document.getElementById('scheduleInterviewForm');
    const scheduleInterviewBtn = document.getElementById('schedule-interview-btn');

    // Interview form elements
    const interviewTypeSelect = document.getElementById('interview-type');
    const interviewDateInput = document.getElementById('interview-date');
    const interviewTimeInput = document.getElementById('interview-time');
    const onlineFields = document.getElementById('online-fields');
    const inpersonFields = document.getElementById('inperson-fields');
    const phoneFields = document.getElementById('phone-fields');
    const platformSectionTitle = document.getElementById('platform-section-title');

    // ===================================
    // STATE VARIABLES
    // ===================================
    
    let currentApplicantData = null;
    let currentApplicationId = null;
    let pendingStatusUpdate = null;
    let applicantsData = [];
    let currentFilters = {};

    // Interview-specific state
    let currentInterviewApplicationId = null;
    let currentInterviewApplicantData = null;

    // API Base URL
    const API_BASE = '../../backend/employer/';

    // ===================================
    // INITIALIZATION
    // ===================================
    
    function init() {
        console.log('Initializing Applicants Page...');
        initEventListeners();
        initInterviewSystem();
        initBulkActionsSystem();  // ← ADD THIS LINE
        loadJobFilterOptions();
        fetchApplicants();
        console.log('Applicants Page Initialized');
    }

    // ===================================
    // SIDEBAR FUNCTIONALITY
    // ===================================
    
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

    // ===================================
    // UTILITY FUNCTIONS
    // ===================================
    
    function showLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }
    }

    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }

    function showSuccessMessage(message) {
        if (statusConfirmation) {
            statusConfirmation.textContent = message;
            statusConfirmation.style.display = 'block';
            statusConfirmation.style.opacity = '1';
            
            setTimeout(() => {
                statusConfirmation.style.opacity = '0';
                setTimeout(() => {
                    statusConfirmation.style.display = 'none';
                }, 500);
            }, 3000);
        }
    }

    function showError(message) {
        console.error('Error:', message);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 10000;
            max-width: 400px;
        `;
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            if (document.body.contains(errorDiv)) {
                document.body.removeChild(errorDiv);
            }
        }, 5000);
    }

    // ===================================
    // APPLICANTS DATA FUNCTIONS
    // ===================================
    
    async function fetchApplicants(filters = {}) {
        try {
            showLoading();
            
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.job) params.append('job', filters.job);
            if (filters.status) params.append('status', filters.status);
            if (filters.date) params.append('date', filters.date);
            if (filters.skills) params.append('skills', filters.skills);
            
            const response = await fetch(`${API_BASE}get_applicants.php?${params.toString()}`);
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'emplogin.php';
                    return;
                }
                throw new Error(data.message || 'Failed to fetch applicants');
            }
            
            if (data.success) {
                applicantsData = data.applicants;
                renderApplicants(data.applicants);
                updateActiveFiltersDisplay(filters);
                updateDashboardStats(data.stats);
                console.log('Applicants loaded:', data.applicants.length);
            } else {
                throw new Error(data.message || 'Failed to load applicants');
            }
            
        } catch (error) {
            showError(error.message);
            console.error('Fetch applicants error:', error);
        } finally {
            hideLoading();
        }
    }

    function updateDashboardStats(stats) {
        const elements = {
            'total-applications': stats.total_applications,
            'new-applications': stats.new_applications,
            'under-review': stats.under_review,
            'interviews-scheduled': stats.interviews_scheduled,
            'hired-count': stats.hired
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value || 0;
            }
        });
    }

    function renderApplicants(applicants) {
        if (!applicantsGrid) return;
        
        if (applicants.length === 0) {
            applicantsGrid.innerHTML = `
                <div class="no-applicants-message">
                    <i class="fas fa-users"></i>
                    <h3>No applicants found</h3>
                    <p>No applications match your current filters. Try adjusting your search criteria.</p>
                </div>
            `;
            if (noResults) noResults.style.display = 'none';
            return;
        }
        
        if (noResults) noResults.style.display = 'none';
        
        const applicantsHTML = applicants.map(applicant => `
            <div class="applicant-card" data-status="${applicant.status_display}" data-job="${applicant.job_title}">
                <span class="application-status status-${applicant.status_display}">${getStatusDisplayName(applicant.status_display)}</span>
                <div class="applicant-header">
                    <div class="applicant-avatar">${applicant.avatar}</div>
                    <div class="applicant-basic-info">
                        <div class="applicant-name">${applicant.full_name}</div>
                        <div class="applicant-title">${applicant.headline || 'Job Seeker'}</div>
                        <div class="application-date">Applied: ${applicant.applied_at_formatted}</div>
                    </div>
                </div>
                <div class="applicant-details">
                    <div class="detail-item">
                        <i class="fas fa-envelope"></i>
                        <div class="detail-text">${applicant.email || 'Email not available'}</div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone"></i>
                        <div class="detail-text">${applicant.contact_number || 'Phone not available'}</div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="detail-text">${getLocationText(applicant)}</div>
                    </div>
                    ${applicant.disability_name ? `
                    <div class="detail-item">
                        <i class="fas fa-accessibility"></i>
                        <div class="detail-text">${applicant.disability_name}</div>
                    </div>
                    ` : ''}
                </div>
                <div class="applied-for">
                    <div class="applied-label">Applied For</div>
                    <div class="job-title">${applicant.job_title}</div>
                </div>
                <div class="action-buttons">
                    <button class="action-btn view-btn" data-application-id="${applicant.application_id}">View Profile</button>
                    ${applicant.resume_path ? `
                        <button class="action-btn resume-btn" data-application-id="${applicant.application_id}" title="View Resume">
                            <i class="fas fa-file-alt"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        applicantsGrid.innerHTML = applicantsHTML;
        
        // Add event listeners to new buttons
        applicantsGrid.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-application-id');
                openApplicantModal(applicationId);
            });
        });
        
        applicantsGrid.querySelectorAll('.resume-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const applicationId = this.getAttribute('data-application-id');
                viewResume(applicationId);
            });
        });
        
        // Animate cards
        applicantsGrid.querySelectorAll('.applicant-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
                card.style.transition = 'all 0.3s ease';
            }, index * 100);
        });

        // Clear any existing selections when rendering new data
        selectedApplicants.clear();
        updateSelectionUI();
        
        addSelectionFunctionality();

    }

    function getLocationText(applicant) {
        if (applicant.city && applicant.province) {
            return `${applicant.city}, ${applicant.province}`;
        } else if (applicant.preferred_location) {
            return applicant.preferred_location;
        } else {
            return 'Location not specified';
        }
    }

    function getStatusDisplayName(status) {
        const statusNames = {
            'new': 'New',
            'reviewed': 'Reviewed',
            'interview': 'Interview',
            'hired': 'Hired',
            'rejected': 'Rejected'
        };
        return statusNames[status] || status;
    }

    function viewResume(applicationId) {
        const viewUrl = `${API_BASE}view_resume.php?application_id=${applicationId}`;
        window.open(viewUrl, '_blank');
    }

    // ===================================
    // APPLICANT MODAL FUNCTIONS
    // ===================================
    
    async function fetchApplicantDetails(applicationId) {
        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}get_applicant_details.php?application_id=${applicationId}`);
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'emplogin.php';
                    return null;
                }
                throw new Error(data.message || 'Failed to fetch applicant details');
            }
            
            if (data.success) {
                return data;
            } else {
                throw new Error(data.message || 'Failed to load applicant details');
            }
            
        } catch (error) {
            showError(error.message);
            console.error('Fetch applicant details error:', error);
            return null;
        } finally {
            hideLoading();
        }
    }

    async function openApplicantModal(applicationId) {
        const applicantData = await fetchApplicantDetails(applicationId);
        
        if (!applicantData) return;
        
        currentApplicantData = applicantData;
        currentApplicationId = applicationId;
        
        populateModal(applicantData);
        
        if (applicantModal) {
            applicantModal.style.display = 'flex';
        }
    }

    function populateModal(data) {
        const applicant = data.applicant;
        
        // Basic info
        const elements = {
            'profile-avatar': applicant.avatar,
            'profile-name': applicant.full_name,
            'profile-title': applicant.headline || 'Job Seeker',
            'profile-email': applicant.email || 'Email not available',
            'profile-phone': applicant.contact_number || 'Phone not available',
            'profile-location': getLocationText(applicant)
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        
        // About section
        const aboutElement = document.getElementById('about-text');
        if (aboutElement) {
            aboutElement.textContent = applicant.bio || 'No bio available';
        }
        
        // Skills
        const skillsList = document.getElementById('skills-list');
        if (skillsList && data.skills) {
            skillsList.innerHTML = '';
            Object.keys(data.skills).forEach(category => {
                const categorySkills = data.skills[category].skills;
                categorySkills.forEach(skill => {
                    const skillTag = document.createElement('span');
                    skillTag.className = 'skill-tag';
                    skillTag.textContent = skill;
                    skillsList.appendChild(skillTag);
                });
            });
        }
        
        // Preferences
        if (data.preferences) {
            const prefElements = {
                'work-setup': data.preferences.work_style || 'Not specified',
                'job-type': data.preferences.job_type || 'Not specified'
            };
            
            Object.entries(prefElements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            });
        }
        
        // Accommodations
        const accommodationsSection = document.getElementById('accommodations-section');
        const accommodationsList = document.getElementById('accommodation-list');
        
        if (accommodationsSection && accommodationsList) {
            if (data.accommodations && !data.accommodations.none_needed && data.accommodations.length > 0) {
                accommodationsSection.style.display = 'block';
                accommodationsList.innerHTML = '';
                
                const accommodationIntro = document.createElement('p');
                accommodationIntro.style.marginBottom = '10px';
                accommodationIntro.textContent = `${applicant.first_name} requires the following accommodations:`;
                accommodationsList.appendChild(accommodationIntro);
                
                data.accommodations.forEach(accommodation => {
                    const accommodationItem = document.createElement('div');
                    accommodationItem.className = 'accommodation-item';
                    accommodationItem.innerHTML = `
                        <i class="fas fa-check-circle"></i>
                        <span>${accommodation}</span>
                    `;
                    accommodationsList.appendChild(accommodationItem);
                });
            } else {
                accommodationsSection.style.display = 'none';
            }
        }
        
        // Application details
        const appElements = {
            'date-applied': applicant.applied_at_formatted,
            'job-applied': applicant.job_title
        };
        
        Object.entries(appElements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        
        // Status
        const statusPill = document.getElementById('status-pill');
        if (statusPill) {
            statusPill.textContent = applicant.status_display;
            statusPill.className = 'status-pill';
            statusPill.classList.add(`status-${applicant.status_display.toLowerCase()}`);
        }
        
        // Resume section
        const resumeSection = document.getElementById('resume-section');
        if (resumeSection && applicant.resume_path) {
            resumeSection.innerHTML = `
                <h4><i class="fas fa-file-alt"></i> Resume</h4>
                <div class="resume-actions">
                    <button class="action-btn" onclick="window.open('${API_BASE}view_resume.php?application_id=${applicant.application_id}', '_blank')">
                        <i class="fas fa-eye"></i> View Resume
                    </button>
                    <button class="action-btn" onclick="window.open('${API_BASE}download_resume.php?application_id=${applicant.application_id}', '_blank')">
                        <i class="fas fa-download"></i> Download Resume
                    </button>
                </div>
            `;
        }
    }

    // ===================================
    // STATUS UPDATE FUNCTIONS
    // ===================================
    
    async function updateApplicationStatus(newStatus, notes = '') {
        try {
            showLoading();
            
            const requestData = {
                application_id: currentApplicationId,
                status: newStatus,
                notes: notes
            };
            
            const response = await fetch(`${API_BASE}update_application_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'emplogin.php';
                    return false;
                }
                throw new Error(data.message || 'Failed to update status');
            }
            
            if (data.success) {
                if (!data.no_change) {
                    showSuccessMessage(`Status updated to ${getStatusDisplayName(newStatus)}`);
                    await fetchApplicants(currentFilters);
                }
                closeModal();
                return true;
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
            
        } catch (error) {
            showError(error.message);
            console.error('Update status error:', error);
            return false;
        } finally {
            hideLoading();
        }
    }

    function showStatusConfirmation(newStatus) {
        if (!currentApplicantData) return;
        
        const applicant = currentApplicantData.applicant;
        
        // If scheduling interview, open interview modal instead
        if (newStatus === 'interview') {
            openScheduleInterviewModal(currentApplicationId, currentApplicantData);
            return;
        }
        
        const currentStatus = applicant.status_display;
        
        if (confirmStatusMessage) {
            confirmStatusMessage.innerHTML = `
                Are you sure you want to change <strong>${applicant.full_name}'s</strong> status from 
                <span class="status-pill status-${currentStatus.toLowerCase()}">${getStatusDisplayName(currentStatus)}</span> to 
                <span class="status-pill status-${newStatus.toLowerCase()}">${getStatusDisplayName(newStatus)}</span>?
            `;
        }
        
        pendingStatusUpdate = newStatus;
        
        if (confirmStatusModal) {
            confirmStatusModal.style.display = 'flex';
        }
    }

    function closeModal() {
        if (applicantModal) {
            applicantModal.style.display = 'none';
        }
        if (confirmStatusModal) {
            confirmStatusModal.style.display = 'none';
        }
        pendingStatusUpdate = null;
    }

    // ===================================
    // INTERVIEW SYSTEM FUNCTIONS
    // ===================================
    
    function initInterviewSystem() {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        if (interviewDateInput) {
            interviewDateInput.setAttribute('min', today);
        }
        
        // Interview type change handler
        if (interviewTypeSelect) {
            interviewTypeSelect.addEventListener('change', handleInterviewTypeChange);
        }
        
        // Schedule interview button
        if (scheduleInterviewBtn) {
            scheduleInterviewBtn.addEventListener('click', handleScheduleInterview);
        }
        
        // View interviews button
        const viewInterviewsBtn = document.getElementById('view-interviews-btn');
        if (viewInterviewsBtn) {
            viewInterviewsBtn.addEventListener('click', function() {
                window.location.href = 'empinterviews.php';
            });
        }
    }

    function handleInterviewTypeChange() {
        const interviewType = interviewTypeSelect.value;
        
        // Hide all platform fields
        if (onlineFields) onlineFields.style.display = 'none';
        if (inpersonFields) inpersonFields.style.display = 'none';
        if (phoneFields) phoneFields.style.display = 'none';
        
        // Show relevant fields and update title
        switch (interviewType) {
            case 'online':
                if (onlineFields) onlineFields.style.display = 'block';
                if (platformSectionTitle) {
                    platformSectionTitle.innerHTML = '<i class="fas fa-video"></i> Platform Details';
                }
                break;
            case 'in_person':
                if (inpersonFields) inpersonFields.style.display = 'block';
                if (platformSectionTitle) {
                    platformSectionTitle.innerHTML = '<i class="fas fa-map-marker-alt"></i> Location Details';
                }
                break;
            case 'phone':
                if (phoneFields) phoneFields.style.display = 'block';
                if (platformSectionTitle) {
                    platformSectionTitle.innerHTML = '<i class="fas fa-phone"></i> Phone Details';
                }
                break;
        }
    }

    function openScheduleInterviewModal(applicationId, applicantData) {
        const applicant = applicantData.applicant;
        
        currentInterviewApplicationId = applicationId;
        currentInterviewApplicantData = applicantData;
        
        // Populate applicant information
        const elements = {
            'interview-avatar': applicant.avatar,
            'interview-applicant-name': applicant.full_name,
            'interview-job-title': `${applicant.job_title} Interview`,
            'interview-applicant-email': applicant.email,
            'interview-applicant-phone': applicant.contact_number,
            'interview-disability-type': applicant.disability_name || 'No specific disability noted'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        
        // Set application ID in form
        const applicationIdInput = document.getElementById('interview-application-id');
        if (applicationIdInput) {
            applicationIdInput.value = applicationId;
        }
        
        // Show/hide disability info
        const disabilityInfo = document.getElementById('interview-disability-info');
        if (disabilityInfo) {
            if (applicant.disability_name) {
                disabilityInfo.style.display = 'flex';
            } else {
                disabilityInfo.style.display = 'none';
            }
        }
        
        // Reset form
        if (scheduleInterviewForm) {
            scheduleInterviewForm.reset();
        }
        
        // Set default date (tomorrow)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        if (interviewDateInput) {
            interviewDateInput.value = tomorrow.toISOString().split('T')[0];
        }
        
        // Set default time (10:00 AM)
        if (interviewTimeInput) {
            interviewTimeInput.value = '10:00';
        }
        
        // Trigger interview type change to show correct fields
        handleInterviewTypeChange();
        
        // Show modal
        if (scheduleInterviewModal) {
            scheduleInterviewModal.style.display = 'flex';
            scheduleInterviewModal.style.animation = 'fadeIn 0.3s ease';
        }
    }

    async function handleScheduleInterview() {
        if (!scheduleInterviewForm) return;
        
        const formData = new FormData(scheduleInterviewForm);
        const interviewData = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (key.includes('_interpreter') || key.includes('_venue') || key.includes('_materials')) {
                interviewData[key] = value === 'on';
            } else {
                interviewData[key] = value;
            }
        }
        
        // Add application ID
        interviewData.application_id = currentInterviewApplicationId;
        
        // Validate required fields
        if (!interviewData.scheduled_date || !interviewData.scheduled_time) {
            showError('Please fill in all required fields');
            return;
        }
        
        // Validate interview type specific fields
        if (interviewData.interview_type === 'in_person' && !interviewData.location_address) {
            showError('Please provide the interview location');
            return;
        }
        
        try {
            showLoading();
            
            const response = await fetch(`${API_BASE}schedule_interview.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(interviewData)
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'emplogin.php';
                    return;
                }
                throw new Error(data.message || 'Failed to schedule interview');
            }
            
            if (data.success) {
                closeInterviewModal('scheduleInterviewModal');
                showInterviewSuccessModal(data.data);
                await fetchApplicants(currentFilters);
                closeModal();
            } else {
                throw new Error(data.message || 'Failed to schedule interview');
            }
            
        } catch (error) {
            showError(error.message);
            console.error('Schedule interview error:', error);
        } finally {
            hideLoading();
        }
    }

    function showInterviewSuccessModal(interviewData) {
        // Populate success modal with interview details
        const elements = {
            'success-applicant-name': interviewData.applicant_name,
            'success-job-title': `${interviewData.job_title} Interview`,
            'success-datetime': interviewData.scheduled_datetime_formatted,
            'success-duration': `${interviewData.duration_minutes} minutes`,
            'success-platform': getInterviewPlatformText(interviewData)
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        
        // Show accommodations if any
        const accommodationsSection = document.getElementById('success-accommodations');
        const accommodationsList = document.getElementById('success-accommodations-list');
        
        if (accommodationsSection && accommodationsList) {
            const accommodations = [];
            const accData = interviewData.accommodations;
            
            if (accData.sign_language_interpreter) accommodations.push('Sign Language Interpreter');
            if (accData.wheelchair_accessible_venue) accommodations.push('Wheelchair Accessible');
            if (accData.screen_reader_materials) accommodations.push('Screen Reader Materials');
            if (accData.additional_notes) accommodations.push(accData.additional_notes);
            
            if (accommodations.length > 0) {
                accommodationsList.textContent = accommodations.join(', ');
                accommodationsSection.style.display = 'flex';
            } else {
                accommodationsSection.style.display = 'none';
            }
        }
        
        // Show success modal
        if (interviewSuccessModal) {
            interviewSuccessModal.style.display = 'flex';
            interviewSuccessModal.style.animation = 'fadeIn 0.3s ease';
        }
    }

    function getInterviewPlatformText(interviewData) {
        switch (interviewData.interview_type) {
            case 'online':
                return `Online via ${interviewData.interview_platform || 'Video Call'}`;
            case 'in_person':
                return 'In-Person Interview';
            case 'phone':
                return 'Phone Interview';
            default:
                return interviewData.interview_type;
        }
    }

    function closeInterviewModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
        
        // Reset form if closing schedule modal
        if (modalId === 'scheduleInterviewModal' && scheduleInterviewForm) {
            scheduleInterviewForm.reset();
        }
        
        // Clear current interview data
        currentInterviewApplicationId = null;
        currentInterviewApplicantData = null;
    }

    // ===================================
    // FILTERING FUNCTIONS
    // ===================================
    
    function applyFilters() {
        const filters = {
            search: searchInput?.value || '',
            job: jobFilter?.value || '',
            status: statusFilter?.value || '',
            date: dateFilter?.value || '',
            skills: skillsFilter?.value || ''
        };
        
        currentFilters = filters;
        fetchApplicants(filters);
    }

    function resetFilters() {
        const filterElements = [searchInput, jobFilter, statusFilter, dateFilter, skillsFilter];
        filterElements.forEach(element => {
            if (element) element.value = '';
        });
        
        currentFilters = {};
        fetchApplicants();
        
        if (activeFiltersContainer) {
            activeFiltersContainer.style.display = 'none';
        }
    }

    function updateActiveFiltersDisplay(filters) {
        if (!activeFiltersContainer || !activeFiltersDiv) return;
        
        activeFiltersDiv.innerHTML = '';
        
        const hasActiveFilters = Object.values(filters).some(value => value);
        
        if (hasActiveFilters) {
            activeFiltersContainer.style.display = 'block';
            
            Object.entries(filters).forEach(([key, value]) => {
                if (value) {
                    addFilterTag(key, value);
                }
            });
        } else {
            activeFiltersContainer.style.display = 'none';
        }
    }

    function addFilterTag(filterType, value) {
        const filterTag = document.createElement('div');
        filterTag.className = 'filter-tag';
        
        const labels = {
            search: 'Search',
            job: 'Position',
            status: 'Status',
            date: 'Date',
            skills: 'Skills'
        };
        
        filterTag.innerHTML = `
            <span class="filter-label">${labels[filterType]}:</span>
            <span class="filter-value">${value}</span>
            <span class="remove-filter" data-filter-type="${filterType}">×</span>
        `;
        
        activeFiltersDiv.appendChild(filterTag);
        
        const removeBtn = filterTag.querySelector('.remove-filter');
        removeBtn.addEventListener('click', function() {
            removeFilter(filterType);
        });
    }

    function removeFilter(filterType) {
        const filterElements = {
            search: searchInput,
            job: jobFilter,
            status: statusFilter,
            date: dateFilter,
            skills: skillsFilter
        };
        
        if (filterElements[filterType]) {
            filterElements[filterType].value = '';
        }
        
        applyFilters();
    }

    async function loadJobFilterOptions() {
        try {
            const response = await fetch(`${API_BASE}get_employer_jobs.php?filter=with_applications`);
            const data = await response.json();
            
            if (data.success && jobFilter) {
                jobFilter.innerHTML = '<option value="">All Positions</option>';
                
                data.jobs.forEach(job => {
                    const option = document.createElement('option');
                    option.value = job.job_title;
                    option.textContent = job.display_name;
                    jobFilter.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading job filter options:', error);
        }
    }

    // ===================================
    // EVENT LISTENERS
    // ===================================
    
    function initEventListeners() {
        // Search and filters
        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }
        
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', applyFilters);
        }
        
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', resetFilters);
        }
        
        // Modal close buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal');
                if (modalId) {
                    closeInterviewModal(modalId);
                } else {
                    closeModal();
                }
            });
        });
        
        // Status update buttons
        document.querySelectorAll('.footer-btn[data-status]').forEach(btn => {
            btn.addEventListener('click', function() {
                const newStatus = this.getAttribute('data-status');
                showStatusConfirmation(newStatus);
            });
        });
        
        // Confirm status update
        if (confirmStatusBtn) {
            confirmStatusBtn.addEventListener('click', function() {
                if (pendingStatusUpdate) {
                    updateApplicationStatus(pendingStatusUpdate);
                    pendingStatusUpdate = null;
                }
            });
        }
        
        // Cancel status update
        if (cancelStatusBtn) {
            cancelStatusBtn.addEventListener('click', closeModal);
        }
        
        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === applicantModal) {
                closeModal();
            }
            if (e.target === confirmStatusModal) {
                closeModal();
            }
            if (e.target === scheduleInterviewModal) {
                closeInterviewModal('scheduleInterviewModal');
            }
            if (e.target === interviewSuccessModal) {
                closeInterviewModal('interviewSuccessModal');
            }
        });

        const calculateBtn = document.getElementById('calculate-matches-btn');
        if (calculateBtn) {
            calculateBtn.addEventListener('click', calculateMatchesForJob);
        }
    }

    // ===================================
    // START INITIALIZATION
    // ===================================
    
    init();

    // ===================================
    // BULK ACTIONS SYSTEM
    // Add this code to your existing empapplicants.js file
    // ===================================

    // Add these variables to your existing state variables section
    let selectedApplicants = new Set();
    let bulkActionInProgress = false;

    // Add these DOM elements to your existing DOM elements section
    const bulkActionsModal = document.getElementById('bulkActionsModal');
    const bulkConfirmationModal = document.getElementById('bulkConfirmationModal');
    const bulkResultsModal = document.getElementById('bulkResultsModal');
    const bulkSelectionHeader = document.createElement('div');
    const selectAllContainer = document.createElement('div');

    // Add this function to initialize bulk actions system
    function initBulkActionsSystem() {
        createBulkSelectionUI();
        initBulkActionEventListeners();
        setupBulkActionCards();
    }

    // Create bulk selection UI elements
    function createBulkSelectionUI() {
        // Create bulk selection header
        const bulkSelectionHeader = document.createElement('div');
        bulkSelectionHeader.className = 'bulk-selection-header';
        bulkSelectionHeader.id = 'bulk-selection-header'; // Add ID for easy reference
        bulkSelectionHeader.style.cssText = `
            display: none;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 15px 0;
            font-family: 'Inter', sans-serif;
        `;
        bulkSelectionHeader.innerHTML = `
            <div class="bulk-selection-info" style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-square" style="color: #257180;"></i>
                <div>
                    <span class="bulk-count" style="font-weight: 600; color: #257180;">0</span>
                    <span style="color: #6c757d;"> applicants selected</span>
                </div>
            </div>
            <div class="bulk-actions-buttons" style="display: flex; gap: 10px;">
                <button class="bulk-action-btn primary" id="open-bulk-actions" style="
                    background: #FD8B51; color: white; border: none; padding: 8px 16px; 
                    border-radius: 5px; cursor: pointer; font-size: 14px; display: flex; 
                    align-items: center; gap: 8px;">
                    <i class="fas fa-tasks"></i>
                    Bulk Actions
                </button>
                <button class="bulk-action-btn" id="clear-all-selections" style="
                    background: #6c757d; color: white; border: none; padding: 8px 16px; 
                    border-radius: 5px; cursor: pointer; font-size: 14px; display: flex; 
                    align-items: center; gap: 8px;">
                    <i class="fas fa-times"></i>
                    Clear All
                </button>
            </div>
        `;
        
        // Create select all container
        const selectAllContainer = document.createElement('div');
        selectAllContainer.className = 'select-all-container';
        selectAllContainer.id = 'select-all-container'; // Add ID for easy reference
        selectAllContainer.style.cssText = `
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 15px;
        `;
        selectAllContainer.innerHTML = `
            <input type="checkbox" class="select-all-checkbox" id="select-all-checkbox" style="margin-right: 8px;">
            <label for="select-all-checkbox" style="font-size: 14px; color: #495057; cursor: pointer;">Select all visible applicants</label>
            <span class="select-count-info" id="select-count-info" style="color: #257180; font-weight: 500;"></span>
        `;
        
        // Insert into page after page header
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.insertAdjacentElement('afterend', bulkSelectionHeader);
            bulkSelectionHeader.insertAdjacentElement('afterend', selectAllContainer);
            console.log('✅ Bulk UI elements created and inserted');
        } else {
            console.error('❌ Page header not found - cannot insert bulk UI');
        }
    }

    // Initialize bulk action event listeners
    function initBulkActionEventListeners() {
        // Open bulk actions modal
        document.addEventListener('click', function(e) {
            if (e.target.id === 'open-bulk-actions' || e.target.closest('#open-bulk-actions')) {
                openBulkActionsModal();
            }
            
            if (e.target.id === 'clear-all-selections' || e.target.closest('#clear-all-selections')) {
                clearAllSelections();
            }
        });
        
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    selectAllVisibleApplicants();
                } else {
                    clearAllSelections();
                }
            });
        }
        
        // Bulk action card selection
        document.addEventListener('change', function(e) {
            if (e.target.name === 'bulk_action') {
                handleBulkActionSelection(e.target.value);
            }
        });
        
        // Execute bulk action
        document.addEventListener('click', function(e) {
            if (e.target.id === 'execute-bulk-action') {
                showBulkConfirmation();
            }
            
            if (e.target.id === 'confirm-bulk-action') {
                executeBulkAction();
            }
            
            if (e.target.id === 'clear-selection-btn') {
                clearAllSelections();
            }
            
            if (e.target.id === 'refresh-applicants-btn') {
                fetchApplicants(currentFilters);
                closeBulkModal('bulkResultsModal');
            }
        });
        
        // Character count for notification message
        const notificationMessage = document.getElementById('notification-message');
        if (notificationMessage) {
            notificationMessage.addEventListener('input', function() {
                const charCount = this.value.length;
                const charCountElement = this.parentElement.querySelector('.char-count');
                if (charCountElement) {
                    charCountElement.textContent = `${charCount}/500 characters`;
                    charCountElement.style.color = charCount > 450 ? '#dc3545' : '#666';
                }
            });
        }
        
        // Remove individual selections
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-selection-btn')) {
                const applicationId = e.target.getAttribute('data-application-id');
                removeFromSelection(applicationId);
            }
        });
    }

    // Setup bulk action cards behavior
    function setupBulkActionCards() {
        document.querySelectorAll('.bulk-action-card').forEach(card => {
            const header = card.querySelector('.action-header');
            const radio = card.querySelector('input[type="radio"]');
            
            header.addEventListener('click', function() {
                radio.checked = true;
                handleBulkActionSelection(radio.value);
            });
        });
    }

    // Handle applicant selection (update your existing renderApplicants function)
    function addSelectionFunctionality() {
        // Remove any existing checkboxes first
        document.querySelectorAll('.selection-checkbox').forEach(checkbox => {
            checkbox.remove();
        });
        
        // Remove selected class from all cards
        document.querySelectorAll('.applicant-card').forEach(card => {
            card.classList.remove('selected');
            card.style.background = '';
            card.style.border = '';
            card.style.transform = '';
            card.style.boxShadow = '';
        });
        
        // Add selection capability to all applicant cards
        document.querySelectorAll('.applicant-card').forEach(card => {
            card.style.position = 'relative';
            card.style.cursor = 'pointer';
            
            // Remove any existing click listeners to avoid duplicates
            card.removeEventListener('click', cardClickHandler);
            card.addEventListener('click', cardClickHandler);
        });
    }

    function cardClickHandler(e) {
        // Don't select if clicking on action buttons
        if (e.target.closest('.action-buttons') || 
            e.target.closest('.view-btn') || 
            e.target.closest('.resume-btn')) {
            return;
        }
        
        const applicationId = this.querySelector('.view-btn').getAttribute('data-application-id');
        toggleApplicantSelection(applicationId, this);
    }

    function toggleApplicantSelection(applicationId, cardElement) {
        if (selectedApplicants.has(applicationId)) {
            // Deselect
            selectedApplicants.delete(applicationId);
            cardElement.classList.remove('selected');
            
            // Reset card styles
            cardElement.style.background = '';
            cardElement.style.border = '';
            cardElement.style.transform = '';
            cardElement.style.boxShadow = '';
            
            // Remove checkbox
            const checkbox = cardElement.querySelector('.selection-checkbox');
            if (checkbox) {
                checkbox.remove();
            }
        } else {
            // Select
            selectedApplicants.add(applicationId);
            cardElement.classList.add('selected');
            
            // Apply selection styles
            cardElement.style.background = 'linear-gradient(135deg, #F2E5BF 0%, #faf8f1 100%)';
            cardElement.style.border = '2px solid #257180';
            cardElement.style.transform = 'scale(1.02)';
            cardElement.style.boxShadow = '0 5px 15px rgba(37, 113, 128, 0.2)';
            
            // Add checkbox on LEFT side ONLY when selected
            const checkbox = document.createElement('div');
            checkbox.className = 'selection-checkbox';
            checkbox.innerHTML = '<i class="fas fa-check"></i>';
            cardElement.appendChild(checkbox);
        }
        
        updateSelectionUI();
    }

    // Toggle applicant selection
    function toggleApplicantSelection(applicationId, cardElement) {
        if (selectedApplicants.has(applicationId)) {
            selectedApplicants.delete(applicationId);
            cardElement.classList.remove('selected');
        } else {
            selectedApplicants.add(applicationId);
            cardElement.classList.add('selected');
        }
        
        updateSelectionUI();
    }

    // Select all visible applicants
    function selectAllVisibleApplicants() {
        document.querySelectorAll('.applicant-card').forEach(card => {
            const applicationId = card.querySelector('.view-btn').getAttribute('data-application-id');
            selectedApplicants.add(applicationId);
            card.classList.add('selected');
        });
        
        updateSelectionUI();
    }

    // Clear all selections
    function clearAllSelections() {
        selectedApplicants.clear();
        document.querySelectorAll('.applicant-card.selected').forEach(card => {
            card.classList.remove('selected');
        });
        
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        updateSelectionUI();
    }

    // Remove from selection
    function removeFromSelection(applicationId) {
        selectedApplicants.delete(applicationId);
        
        const card = document.querySelector(`[data-application-id="${applicationId}"]`).closest('.applicant-card');
        if (card) {
            card.classList.remove('selected');
        }
        
        updateSelectionUI();
        updateSelectedApplicantsList();
    }

    // Update selection UI
    function updateSelectionUI() {
        const count = selectedApplicants.size;
        
        // Find bulk selection header by ID
        const bulkSelectionHeader = document.getElementById('bulk-selection-header');
        const selectAllContainer = document.getElementById('select-all-container');
        
        if (bulkSelectionHeader) {
            const bulkCount = bulkSelectionHeader.querySelector('.bulk-count');
            if (bulkCount) {
                bulkCount.textContent = count;
            }
            
            // Show/hide bulk selection elements
            if (count > 0) {
                bulkSelectionHeader.style.display = 'flex';
                if (selectAllContainer) {
                    selectAllContainer.style.display = 'flex';
                }
            } else {
                bulkSelectionHeader.style.display = 'none';
                if (selectAllContainer) {
                    selectAllContainer.style.display = 'none';
                }
            }
        }
        
        // Update select count info
        const selectCountInfo = document.getElementById('select-count-info');
        if (selectCountInfo) {
            selectCountInfo.textContent = count > 0 ? `${count} selected` : '';
        }
        
        console.log(`Selection UI updated: ${count} applicants selected`);
    }

    // Open bulk actions modal
    function openBulkActionsModal() {
        if (selectedApplicants.size === 0) {
            showError('Please select at least one applicant');
            return;
        }
        
        updateBulkModalSelection();
        updateSelectedApplicantsList();
        
        if (bulkActionsModal) {
            bulkActionsModal.style.display = 'flex';
        }
    }

    // Update bulk modal selection count
    function updateBulkModalSelection() {
        const selectedCount = document.getElementById('bulk-selected-count');
        if (selectedCount) {
            selectedCount.textContent = selectedApplicants.size;
        }
    }

    // Update selected applicants list in modal
    function updateSelectedApplicantsList() {
        const list = document.getElementById('selected-applicants-list');
        if (!list) return;
        
        list.innerHTML = '';
        
        selectedApplicants.forEach(applicationId => {
            const card = document.querySelector(`[data-application-id="${applicationId}"]`).closest('.applicant-card');
            if (card) {
                const name = card.querySelector('.applicant-name').textContent;
                const title = card.querySelector('.applicant-title').textContent;
                const avatar = card.querySelector('.applicant-avatar').textContent;
                
                const item = document.createElement('div');
                item.className = 'selected-applicant-item';
                item.innerHTML = `
                    <div class="selected-applicant-avatar">${avatar}</div>
                    <div class="selected-applicant-info">
                        <h5>${name}</h5>
                        <p>${title}</p>
                    </div>
                    <button class="remove-selection-btn" data-application-id="${applicationId}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                list.appendChild(item);
            }
        });
    }

    // Handle bulk action selection
    function handleBulkActionSelection(action) {
        // Hide all action details
        document.querySelectorAll('.action-details').forEach(detail => {
            detail.style.display = 'none';
        });
        
        // Remove selected class from all cards
        document.querySelectorAll('.bulk-action-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Show selected action details
        const selectedCard = document.querySelector(`[data-action="${action}"]`);
        const selectedDetails = document.getElementById(`${action.replace('_', '')}_details`);
        
        if (selectedCard && selectedDetails) {
            selectedCard.classList.add('selected');
            selectedDetails.style.display = 'block';
        }
        
        // Enable execute button
        const executeBtn = document.getElementById('execute-bulk-action');
        if (executeBtn) {
            executeBtn.disabled = false;
        }
    }

    // Show bulk confirmation
    function showBulkConfirmation() {
        const selectedAction = document.querySelector('input[name="bulk_action"]:checked');
        if (!selectedAction) {
            showError('Please select an action');
            return;
        }
        
        const action = selectedAction.value;
        const actionData = getBulkActionData(action);
        
        if (!actionData.valid) {
            showError(actionData.error);
            return;
        }
        
        // Update confirmation modal
        const confirmationTitle = document.getElementById('confirmation-title');
        const confirmationMessage = document.getElementById('confirmation-message');
        const actionSummary = document.getElementById('action-summary');
        
        if (confirmationTitle) {
            confirmationTitle.textContent = `Confirm ${actionData.title}`;
        }
        
        if (confirmationMessage) {
            confirmationMessage.textContent = `Are you sure you want to ${actionData.description} for ${selectedApplicants.size} selected applicants?`;
        }
        
        if (actionSummary) {
            actionSummary.innerHTML = `
                <h5>Action Summary:</h5>
                <ul>
                    <li><strong>Action:</strong> ${actionData.title}</li>
                    <li><strong>Applicants affected:</strong> ${selectedApplicants.size}</li>
                    ${actionData.summary ? `<li><strong>Details:</strong> ${actionData.summary}</li>` : ''}
                </ul>
            `;
        }
        
        // Close main modal and show confirmation
        closeBulkModal('bulkActionsModal');
        if (bulkConfirmationModal) {
            bulkConfirmationModal.style.display = 'flex';
        }
    }

    // Get bulk action data for confirmation
    function getBulkActionData(action) {
        switch (action) {
            case 'update_status':
                const status = document.getElementById('bulk-status').value;
                const notes = document.getElementById('bulk-status-notes').value;
                
                if (!status) {
                    return { valid: false, error: 'Please select a status' };
                }
                
                return {
                    valid: true,
                    title: 'Update Status',
                    description: `update status to "${getStatusDisplayName(status)}"`,
                    summary: notes ? `Notes: ${notes}` : 'No additional notes'
                };
                
            case 'send_notification':
                const title = document.getElementById('notification-title').value;
                const message = document.getElementById('notification-message').value;
                
                if (!title || !message) {
                    return { valid: false, error: 'Please fill in title and message' };
                }
                
                return {
                    valid: true,
                    title: 'Send Notification',
                    description: `send notification "${title}"`,
                    summary: `Message: ${message.substring(0, 100)}${message.length > 100 ? '...' : ''}`
                };
                
            case 'export':
                const format = document.getElementById('export-format').value;
                const selectedFields = Array.from(document.querySelectorAll('.checkbox-group input:checked')).map(cb => cb.value);
                
                if (selectedFields.length === 0) {
                    return { valid: false, error: 'Please select at least one field to export' };
                }
                
                return {
                    valid: true,
                    title: 'Export Data',
                    description: `export data in ${format.toUpperCase()} format`,
                    summary: `Fields: ${selectedFields.join(', ')}`
                };
                
            case 'archive':
                const reason = document.getElementById('archive-reason').value;
                
                return {
                    valid: true,
                    title: 'Archive Applications',
                    description: 'archive applications',
                    summary: `Reason: ${reason}`
                };
                
            default:
                return { valid: false, error: 'Unknown action' };
        }
    }

    // Execute bulk action
    async function executeBulkAction() {
        if (bulkActionInProgress) return;
        
        const selectedAction = document.querySelector('input[name="bulk_action"]:checked');
        if (!selectedAction) {
            showError('No action selected');
            return;
        }
        
        const action = selectedAction.value;
        const parameters = getBulkActionParameters(action);
        
        try {
            bulkActionInProgress = true;
            showLoading();
            
            const requestData = {
                action: action,
                application_ids: Array.from(selectedApplicants),
                parameters: parameters
            };
            
            const response = await fetch(`${API_BASE}bulk_actions.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = 'emplogin.php';
                    return;
                }
                throw new Error(data.message || 'Bulk action failed');
            }
            
            if (data.success) {
                showBulkResults(data.data);
                
                // Refresh applicants list
                await fetchApplicants(currentFilters);
                
                // Clear selections
                clearAllSelections();
                
            } else {
                throw new Error(data.message || 'Bulk action failed');
            }
            
        } catch (error) {
            showError(error.message);
            console.error('Bulk action error:', error);
        } finally {
            bulkActionInProgress = false;
            hideLoading();
            closeBulkModal('bulkConfirmationModal');
        }
    }

    // Get bulk action parameters
    function getBulkActionParameters(action) {
        switch (action) {
            case 'update_status':
                return {
                    status: document.getElementById('bulk-status').value,
                    notes: document.getElementById('bulk-status-notes').value,
                    notify: document.getElementById('notify-status-change').checked
                };
                
            case 'send_notification':
                return {
                    title: document.getElementById('notification-title').value,
                    message: document.getElementById('notification-message').value,
                    send_email: document.getElementById('send-email-notification').checked
                };
                
            case 'export':
                const selectedFields = Array.from(document.querySelectorAll('.checkbox-group input:checked')).map(cb => cb.value);
                return {
                    format: document.getElementById('export-format').value,
                    fields: selectedFields
                };
                
            case 'archive':
                return {
                    reason: document.getElementById('archive-reason').value
                };
                
            default:
                return {};
        }
    }

    // Show bulk results
    function showBulkResults(data) {
        // Update stats
        const successfulCount = document.getElementById('successful-count');
        const failedCount = document.getElementById('failed-count');
        const totalProcessed = document.getElementById('total-processed');
        
        if (successfulCount) successfulCount.textContent = data.successful_operations || 0;
        if (failedCount) failedCount.textContent = data.failed_operations || 0;
        if (totalProcessed) totalProcessed.textContent = data.total_selected || 0;
        
        // Update results details
        const resultsDetails = document.getElementById('results-details');
        if (resultsDetails && data.details) {
            resultsDetails.innerHTML = '';
            
            data.details.forEach(detail => {
                const item = document.createElement('div');
                item.className = 'result-item';
                
                const statusClass = detail.status === 'success' ? 'success' : 
                                detail.status === 'failed' ? 'failed' : 'skipped';
                
                const statusIcon = detail.status === 'success' ? 'check' : 
                                detail.status === 'failed' ? 'times' : 'minus';
                
                item.innerHTML = `
                    <div class="result-status ${statusClass}">
                        <i class="fas fa-${statusIcon}"></i>
                    </div>
                    <div class="result-info">
                        <strong>${detail.applicant || 'Unknown'}</strong>
                        <small>${detail.reason || detail.error || detail.new_status || 'Processed'}</small>
                    </div>
                `;
                
                resultsDetails.appendChild(item);
            });
        }
        
        // Handle export data
        const exportSection = document.getElementById('export-section');
        if (exportSection && data.export_data) {
            exportSection.style.display = 'block';
            
            const downloadBtn = document.getElementById('download-export-btn');
            if (downloadBtn) {
                downloadBtn.onclick = function() {
                    downloadExportData(data.export_data);
                };
            }
        } else if (exportSection) {
            exportSection.style.display = 'none';
        }
        
        // Show results modal
        if (bulkResultsModal) {
            bulkResultsModal.style.display = 'flex';
        }
    }

    // Download export data
    function downloadExportData(exportData) {
        if (!exportData || !exportData.data) return;
        
        const format = exportData.format;
        const data = exportData.data;
        const filename = exportData.filename;
        
        let content = '';
        let mimeType = '';
        
        if (format === 'csv') {
            // Convert to CSV
            if (data.length > 0) {
                const headers = Object.keys(data[0]);
                content = headers.join(',') + '\n';
                content += data.map(row => headers.map(header => `"${row[header] || ''}"`).join(',')).join('\n');
            }
            mimeType = 'text/csv';
        } else if (format === 'excel') {
            // For Excel, we'll use CSV format (browser limitation)
            if (data.length > 0) {
                const headers = Object.keys(data[0]);
                content = headers.join('\t') + '\n';
                content += data.map(row => headers.map(header => row[header] || '').join('\t')).join('\n');
            }
            mimeType = 'application/vnd.ms-excel';
        }
        
        // Create and trigger download
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    // Close bulk modals
    function closeBulkModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
        
        // Reset forms
        if (modalId === 'bulkActionsModal') {
            document.querySelectorAll('input[name="bulk_action"]').forEach(radio => {
                radio.checked = false;
            });
            document.querySelectorAll('.action-details').forEach(detail => {
                detail.style.display = 'none';
            });
            document.querySelectorAll('.bulk-action-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            const executeBtn = document.getElementById('execute-bulk-action');
            if (executeBtn) {
                executeBtn.disabled = true;
            }
        }
    }

    // Update your existing renderApplicants function to include selection functionality
    // Add this call at the end of your renderApplicants function:
    // addSelectionFunctionality();

    // Update your existing init function to include bulk actions
    // Add this call to your existing init() function:
    // initBulkActionsSystem();

    // Add bulk modal close handlers to your existing modal close event listeners
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-modal')) {
            const modalId = e.target.getAttribute('data-modal');
            if (modalId && ['bulkActionsModal', 'bulkConfirmationModal', 'bulkResultsModal'].includes(modalId)) {
                closeBulkModal(modalId);
            }
        }
    });

    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === bulkActionsModal) {
            closeBulkModal('bulkActionsModal');
        }
        if (e.target === bulkConfirmationModal) {
            closeBulkModal('bulkConfirmationModal');
        }
        if (e.target === bulkResultsModal) {
            closeBulkModal('bulkResultsModal');
        }
    });

    // ===================================
    // END OF BULK ACTIONS SYSTEM
    // Add the above code to your existing empapplicants.js file
    // ===================================


    // Add this to your existing empapplicants.js file
    // Enhanced applicant rendering with match scores

    // Add these functions after your existing functions:

    /**
     * Enhanced renderApplicants function with match scoring
     */
    function renderApplicantsWithMatching(applicants) {
        if (!applicantsGrid) return;
        
        if (applicants.length === 0) {
            showEmptyState();
            return;
        }
        
        // Clear existing content
        applicantsGrid.innerHTML = '';
        
        // Sort applicants by match score (highest first)
        const sortedApplicants = [...applicants].sort((a, b) => (b.match_score || 0) - (a.match_score || 0));
        
        sortedApplicants.forEach(applicant => {
            const applicantCard = createEnhancedApplicantCard(applicant);
            applicantsGrid.appendChild(applicantCard);
        });
    }

    /**
     * Create enhanced applicant card with match score
     */
    function createEnhancedApplicantCard(applicant) {
        const card = document.createElement('div');
        card.className = 'applicant-card enhanced-card';
        card.setAttribute('data-application-id', applicant.application_id);
        card.setAttribute('data-match-score', applicant.match_score || 0);
        
        const matchScore = applicant.match_score || 0;
        const matchLevel = applicant.match_level || 'poor';
        const matchColor = applicant.match_color || '#ef4444';
        
        card.innerHTML = `
            <!-- Match Score Banner -->
            <div class="match-score-banner" style="background: linear-gradient(90deg, ${matchColor}22 0%, ${matchColor}11 100%); border-left: 4px solid ${matchColor};">
                <div class="match-score-info">
                    <span class="match-percentage" style="color: ${matchColor}; font-weight: bold;">${matchScore}%</span>
                    <span class="match-label">${applicant.match_label || 'Match Score'}</span>
                </div>
                <div class="match-indicators">
                    ${applicant.accommodation_compatibility > 0 ? `
                        <span class="accommodation-indicator" title="Accommodation Compatibility: ${applicant.accommodation_compatibility}%">
                            <i class="fas fa-universal-access"></i> ${Math.round(applicant.accommodation_compatibility)}%
                        </span>
                    ` : ''}
                    ${applicant.skills_matched_array && applicant.skills_matched_array.length > 0 ? `
                        <span class="skills-indicator" title="Skills Matched">
                            <i class="fas fa-check-circle"></i> ${applicant.skills_matched_array.length} skills
                        </span>
                    ` : ''}
                </div>
            </div>
            
            <!-- Application Status -->
            <span class="application-status status-${applicant.application_status.replace('_', '-')}">${formatStatus(applicant.application_status)}</span>
            
            <!-- Applicant Header -->
            <div class="applicant-header">
                <div class="applicant-avatar" style="background-color: ${matchColor}33;">
                    ${applicant.avatar || getInitials(applicant.first_name, applicant.last_name)}
                </div>
                <div class="applicant-basic-info">
                    <div class="applicant-name">${applicant.full_name || `${applicant.first_name} ${applicant.last_name}`}</div>
                    <div class="applicant-title">${applicant.headline || 'Job Seeker'}</div>
                    <div class="application-date">Applied: ${formatDate(applicant.applied_at)}</div>
                </div>
            </div>
            
            <!-- Skills Match Breakdown -->
            <div class="skills-breakdown">
                <div class="skills-progress">
                    <div class="progress-label">Skills Match</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${matchScore}%; background-color: ${matchColor};"></div>
                    </div>
                    <div class="progress-details">
                        ${applicant.skills_matched_array ? applicant.skills_matched_array.length : 0} of ${
                            (applicant.skills_matched_array ? applicant.skills_matched_array.length : 0) + 
                            (applicant.skills_missing_array ? applicant.skills_missing_array.length : 0)
                        } skills
                    </div>
                </div>
                
                ${applicant.skills_matched_array && applicant.skills_matched_array.length > 0 ? `
                    <div class="matched-skills">
                        <div class="skills-label"><i class="fas fa-check text-green"></i> Matched Skills:</div>
                        <div class="skills-tags">
                            ${applicant.skills_matched_array.slice(0, 3).map(skill => 
                                `<span class="skill-tag matched">${skill}</span>`
                            ).join('')}
                            ${applicant.skills_matched_array.length > 3 ? 
                                `<span class="skill-tag more">+${applicant.skills_matched_array.length - 3} more</span>` : ''
                            }
                        </div>
                    </div>
                ` : ''}
                
                ${applicant.skills_missing_array && applicant.skills_missing_array.length > 0 ? `
                    <div class="missing-skills">
                        <div class="skills-label"><i class="fas fa-times text-red"></i> Missing Skills:</div>
                        <div class="skills-tags">
                            ${applicant.skills_missing_array.slice(0, 2).map(skill => 
                                `<span class="skill-tag missing">${skill}</span>`
                            ).join('')}
                            ${applicant.skills_missing_array.length > 2 ? 
                                `<span class="skill-tag more">+${applicant.skills_missing_array.length - 2} more</span>` : ''
                            }
                        </div>
                    </div>
                ` : ''}
            </div>
            
            <!-- Applicant Details -->
            <div class="applicant-details">
                <div class="detail-item">
                    <i class="fas fa-envelope"></i>
                    <div class="detail-text">${applicant.email || 'Email not available'}</div>
                </div>
                <div class="detail-item">
                    <i class="fas fa-phone"></i>
                    <div class="detail-text">${applicant.contact_number || 'Phone not available'}</div>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="detail-text">${applicant.city || 'Location not specified'}${applicant.province ? `, ${applicant.province}` : ''}</div>
                </div>
                ${applicant.pwd_insight ? `
                    <div class="detail-item pwd-insight">
                        <i class="fas fa-universal-access"></i>
                        <div class="detail-text">${applicant.pwd_insight}</div>
                    </div>
                ` : ''}
            </div>
            
            <!-- Applied For -->
            <div class="applied-for">
                <div class="applied-label">Applied For</div>
                <div class="job-title">${applicant.job_title || 'Position'}</div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="action-btn view-btn enhanced-btn" data-application-id="${applicant.application_id}">
                    <i class="fas fa-eye"></i> View Details
                </button>
                <button class="action-btn feedback-btn" data-application-id="${applicant.application_id}" title="Generate Feedback">
                    <i class="fas fa-comment-alt"></i> Feedback
                </button>
            </div>
        `;
        
        // Add click handlers
        card.querySelector('.view-btn').addEventListener('click', () => {
            openEnhancedApplicantModal(applicant.application_id);
        });
        
        card.querySelector('.feedback-btn').addEventListener('click', () => {
            showRejectionFeedbackModal(applicant);
        });
        
        return card;
    }

    /**
     * Show rejection feedback modal with skills analysis
     */
    function showRejectionFeedbackModal(applicant) {
        const modal = document.createElement('div');
        modal.className = 'feedback-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Application Feedback for ${applicant.first_name} ${applicant.last_name}</h3>
                    <button class="close-btn">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="feedback-summary">
                        <div class="score-display">
                            <div class="score-circle" style="background: conic-gradient(${applicant.match_color} 0deg ${applicant.match_score * 3.6}deg, #f0f0f0 ${applicant.match_score * 3.6}deg 360deg);">
                                <div class="score-inner">
                                    <span class="score-number">${applicant.match_score}%</span>
                                    <span class="score-label">Match</span>
                                </div>
                            </div>
                            <div class="score-details">
                                <h4>Overall Assessment</h4>
                                <p>${generateFeedbackMessage(applicant)}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feedback-sections">
                        ${applicant.skills_matched_array && applicant.skills_matched_array.length > 0 ? `
                            <div class="feedback-section positive">
                                <h4><i class="fas fa-check-circle"></i> Strengths</h4>
                                <p>This candidate has the following required skills:</p>
                                <ul>
                                    ${applicant.skills_matched_array.map(skill => `<li>${skill}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        ${applicant.skills_missing_array && applicant.skills_missing_array.length > 0 ? `
                            <div class="feedback-section improvement">
                                <h4><i class="fas fa-exclamation-triangle"></i> Areas for Improvement</h4>
                                <p>To be a stronger candidate for this role, consider developing these skills:</p>
                                <ul>
                                    ${applicant.skills_missing_array.map(skill => `<li>${skill}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <div class="feedback-section recommendations">
                            <h4><i class="fas fa-lightbulb"></i> Recommendations</h4>
                            ${generateRecommendations(applicant)}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary close-modal">Close</button>
                    <button class="btn btn-primary send-feedback" data-application-id="${applicant.application_id}">
                        Send Feedback to Candidate
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event handlers
        modal.querySelector('.close-btn').addEventListener('click', () => modal.remove());
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('.send-feedback').addEventListener('click', () => {
            sendFeedbackToCandidate(applicant);
            modal.remove();
        });
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    /**
     * Generate feedback message based on match score
     */
    function generateFeedbackMessage(applicant) {
        const score = applicant.match_score || 0;
        
        if (score >= 90) {
            return "This candidate is an excellent match for the position with strong skills alignment and compatibility.";
        } else if (score >= 75) {
            return "This candidate shows good potential with most required skills present. Minor skill gaps can be addressed through training.";
        } else if (score >= 60) {
            return "This candidate has some relevant skills but may need additional development to fully meet the role requirements.";
        } else {
            return "This candidate would benefit from significant skill development to be competitive for this position.";
        }
    }

    /**
     * Generate personalized recommendations
     */
    function generateRecommendations(applicant) {
        const missing = applicant.skills_missing_array || [];
        const matched = applicant.skills_matched_array || [];
        
        let recommendations = '<ul>';
        
        if (missing.length > 0) {
            recommendations += `<li>Focus on developing <strong>${missing.slice(0, 2).join(' and ')}</strong> skills through online courses or training programs.</li>`;
        }
        
        if (matched.length > 0) {
            recommendations += `<li>Continue strengthening your <strong>${matched[0]}</strong> expertise as it's highly relevant to this role.</li>`;
        }
        
        recommendations += '<li>Consider applying to similar positions that may have fewer skill requirements.</li>';
        recommendations += '<li>Build a portfolio showcasing your current skills and any relevant projects.</li>';
        
        if (applicant.accommodation_compatibility < 100 && applicant.accommodation_compatibility > 0) {
            recommendations += '<li>Discuss accommodation needs early in the application process to ensure proper support.</li>';
        }
        
        recommendations += '</ul>';
        
        return recommendations;
    }

    /**
     * Send feedback to candidate (placeholder for now)
     */
    function sendFeedbackToCandidate(applicant) {
        // This would integrate with your notification system
        showNotification(`Feedback sent to ${applicant.first_name} ${applicant.last_name}`, 'success');
    }

    /**
     * Add CSS for enhanced styling
     */
    function addMatchScoringCSS() {
        const css = `
            <style>
            .enhanced-card {
                border-left: 4px solid var(--primary);
                transition: all 0.3s ease;
            }
            
            .match-score-banner {
                padding: 12px 16px;
                margin: -16px -16px 16px -16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 8px 8px 0 0;
            }
            
            .match-score-info {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .match-percentage {
                font-size: 18px;
                font-weight: 700;
            }
            
            .match-label {
                font-size: 14px;
                color: #666;
            }
            
            .match-indicators {
                display: flex;
                gap: 12px;
                font-size: 12px;
            }
            
            .accommodation-indicator, .skills-indicator {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 4px 8px;
                background: rgba(255,255,255,0.9);
                border-radius: 12px;
                color: #555;
            }
            
            .skills-breakdown {
                margin: 16px 0;
                padding: 12px;
                background: #f8f9fa;
                border-radius: 6px;
            }
            
            .skills-progress {
                margin-bottom: 12px;
            }
            
            .progress-label {
                font-size: 12px;
                color: #666;
                margin-bottom: 4px;
            }
            
            .progress-bar {
                height: 8px;
                background: #e9ecef;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .progress-fill {
                height: 100%;
                transition: width 0.3s ease;
            }
            
            .progress-details {
                font-size: 11px;
                color: #666;
                margin-top: 4px;
            }
            
            .matched-skills, .missing-skills {
                margin-bottom: 8px;
            }
            
            .skills-label {
                font-size: 12px;
                font-weight: 500;
                margin-bottom: 4px;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            
            .skills-tags {
                display: flex;
                gap: 4px;
                flex-wrap: wrap;
            }
            
            .skill-tag {
                font-size: 10px;
                padding: 2px 6px;
                border-radius: 10px;
                white-space: nowrap;
            }
            
            .skill-tag.matched {
                background: #d1fae5;
                color: #065f46;
            }
            
            .skill-tag.missing {
                background: #fee2e2;
                color: #991b1b;
            }
            
            .skill-tag.more {
                background: #e5e7eb;
                color: #6b7280;
            }
            
            .pwd-insight {
                background: #eff6ff;
                border-radius: 4px;
                padding: 8px;
                margin-top: 8px;
            }
            
            .pwd-insight .detail-text {
                color: #1e40af;
                font-size: 12px;
            }
            
            .text-green { color: #10b981; }
            .text-red { color: #ef4444; }
            
            .feedback-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }
            
            .feedback-modal .modal-content {
                background: white;
                border-radius: 8px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .score-display {
                display: flex;
                gap: 20px;
                align-items: center;
                margin-bottom: 20px;
            }
            
            .score-circle {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }
            
            .score-inner {
                background: white;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .score-number {
                font-size: 16px;
                font-weight: bold;
            }
            
            .score-label {
                font-size: 10px;
                color: #666;
            }
            
            .feedback-section {
                margin-bottom: 20px;
                padding: 16px;
                border-radius: 6px;
            }
            
            .feedback-section.positive {
                background: #f0fdf4;
                border-left: 4px solid #10b981;
            }
            
            .feedback-section.improvement {
                background: #fef2f2;
                border-left: 4px solid #ef4444;
            }
            
            .feedback-section.recommendations {
                background: #fef3c7;
                border-left: 4px solid #f59e0b;
            }
            </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', css);
    }

    // Initialize enhanced features
    document.addEventListener('DOMContentLoaded', function() {
        addMatchScoringCSS();
        
        // Override the existing renderApplicants function
        window.renderApplicants = renderApplicantsWithMatching;
        
        console.log('✅ Job matching features loaded');
    });

    // ===================================
    // CALCULATE MATCHES FUNCTIONALITY  
    // ===================================

    async function calculateMatchesForJob() {
        try {
            // Check if we have any applicants loaded
            if (!applicantsData || applicantsData.length === 0) {
                showError('No applicants found. Please ensure there are job applications to calculate matches for.');
                return;
            }
            
            // Get unique job IDs from current applicants
            const jobIds = [...new Set(applicantsData.map(applicant => applicant.job_id))];
            
            if (jobIds.length === 0) {
                showError('No jobs found in current applicants data.');
                return;
            }
            
            // Show progress
            showCalculationProgress();
            
            let totalProcessed = 0;
            let totalErrors = 0;
            let allResults = [];
            
            // Calculate matches for each job
            for (const jobId of jobIds) {
                try {
                    const response = await fetch('../../backend/employer/batch_calculate_matches.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            job_id: jobId,
                            force_recalculate: true
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        totalProcessed += data.summary.processed || 0;
                        allResults.push(data.summary);
                    } else {
                        console.error(`Error calculating matches for job ${jobId}:`, data.error);
                        totalErrors++;
                    }
                } catch (error) {
                    console.error(`Error calculating matches for job ${jobId}:`, error);
                    totalErrors++;
                }
            }
            
            // Calculate overall statistics
            const overallSummary = {
                excellent_matches: allResults.reduce((sum, result) => sum + (result.excellent_matches || 0), 0),
                good_matches: allResults.reduce((sum, result) => sum + (result.good_matches || 0), 0),
                fair_matches: allResults.reduce((sum, result) => sum + (result.fair_matches || 0), 0),
                poor_matches: allResults.reduce((sum, result) => sum + (result.poor_matches || 0), 0),
                processed: totalProcessed,
                average_score: allResults.length > 0 ? Math.round(allResults.reduce((sum, result) => sum + (result.average_score || 0), 0) / allResults.length) : 0
            };
            
            displayMatchStatistics(overallSummary);
            
            showNotification(`Match calculation completed! 
                Processed ${totalProcessed} applicants across ${jobIds.length} job(s).
                ${totalErrors > 0 ? `${totalErrors} job(s) had errors.` : ''}`, 'success');
            
            // Reload applicants to show updated scores
            setTimeout(() => {
                fetchApplicants(currentFilters);
            }, 1000);
            
        } catch (error) {
            console.error('Match calculation error:', error);
            showError('Failed to calculate matches: ' + error.message);
        } finally {
            hideCalculationProgress();
        }
    }

    function showCalculationProgress() {
        const calculationProgress = document.getElementById('calculation-progress');
        const calculateBtn = document.getElementById('calculate-matches-btn');
        
        if (calculationProgress) {
            calculationProgress.style.display = 'block';
            const progressFill = document.getElementById('progress-fill');
            const progressText = document.getElementById('progress-text');
            
            // Animate progress bar
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                if (progressFill) progressFill.style.width = progress + '%';
                if (progressText) progressText.textContent = `Calculating matches... ${Math.round(progress)}%`;
                
                if (progress >= 90) clearInterval(interval);
            }, 200);
            
            // Store interval for cleanup
            window.matchCalculationInterval = interval;
        }
        
        if (calculateBtn) {
            calculateBtn.disabled = true;
            calculateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
        }
    }

    function hideCalculationProgress() {
        const calculationProgress = document.getElementById('calculation-progress');
        const calculateBtn = document.getElementById('calculate-matches-btn');
        
        if (calculationProgress) {
            calculationProgress.style.display = 'none';
        }
        
        if (window.matchCalculationInterval) {
            clearInterval(window.matchCalculationInterval);
        }
        
        if (calculateBtn) {
            calculateBtn.disabled = false;
            calculateBtn.innerHTML = '<i class="fas fa-sync"></i> <span>Calculate Matches</span>';
        }
        
        // Complete progress bar
        const progressFill = document.getElementById('progress-fill');
        if (progressFill) progressFill.style.width = '100%';
        
        setTimeout(() => {
            if (calculationProgress) calculationProgress.style.display = 'none';
        }, 500);
    }

    function displayMatchStatistics(summary) {
        const matchStatsDiv = document.getElementById('match-stats');
        if (!matchStatsDiv) return;

        // Store summary data for category modal
        window.currentMatchSummary = summary;
        
        matchStatsDiv.style.display = 'block';
        
        // Create enhanced statistics cards HTML
        matchStatsDiv.innerHTML = `
            <div class="stats-grid">
                <div class="match-stat-card excellent-card" data-category="excellent" data-count="${summary.excellent_matches || 0}">
                    <div class="stat-header">
                        <div class="stat-icon">🔥</div>
                        <div class="stat-info">
                            <div class="stat-count">${summary.excellent_matches || 0}</div>
                            <div class="stat-label">Excellent</div>
                            <div class="stat-range">90-100%</div>
                        </div>
                    </div>
                    <div class="stat-preview" id="excellent-preview">
                        <div class="preview-content">
                            <div class="preview-header">Top Candidates</div>
                            <div class="preview-loading">Loading...</div>
                        </div>
                    </div>
                    <div class="stat-action">
                        <span class="view-details">Click to view details</span>
                    </div>
                </div>

                <div class="match-stat-card good-card" data-category="good" data-count="${summary.good_matches || 0}">
                    <div class="stat-header">
                        <div class="stat-icon">🟢</div>
                        <div class="stat-info">
                            <div class="stat-count">${summary.good_matches || 0}</div>
                            <div class="stat-label">Good</div>
                            <div class="stat-range">75-89%</div>
                        </div>
                    </div>
                    <div class="stat-preview" id="good-preview">
                        <div class="preview-content">
                            <div class="preview-header">Top Candidates</div>
                            <div class="preview-loading">Loading...</div>
                        </div>
                    </div>
                    <div class="stat-action">
                        <span class="view-details">Click to view details</span>
                    </div>
                </div>

                <div class="match-stat-card fair-card" data-category="fair" data-count="${summary.fair_matches || 0}">
                    <div class="stat-header">
                        <div class="stat-icon">🟡</div>
                        <div class="stat-info">
                            <div class="stat-count">${summary.fair_matches || 0}</div>
                            <div class="stat-label">Fair</div>
                            <div class="stat-range">60-74%</div>
                        </div>
                    </div>
                    <div class="stat-preview" id="fair-preview">
                        <div class="preview-content">
                            <div class="preview-header">Top Candidates</div>
                            <div class="preview-loading">Loading...</div>
                        </div>
                    </div>
                    <div class="stat-action">
                        <span class="view-details">Click to view details</span>
                    </div>
                </div>

                <div class="match-stat-card needs-review-card" data-category="needs-review" data-count="${summary.poor_matches || 0}">
                    <div class="stat-header">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-info">
                            <div class="stat-count">${summary.poor_matches || 0}</div>
                            <div class="stat-label">Needs Review</div>
                            <div class="stat-range">0-59%</div>
                        </div>
                    </div>
                    <div class="stat-preview" id="needs-review-preview">
                        <div class="preview-content">
                            <div class="preview-header">Top Candidates</div>
                            <div class="preview-loading">Loading...</div>
                        </div>
                    </div>
                    <div class="stat-action">
                        <span class="view-details">Click to view details</span>
                    </div>
                </div>
            </div>

            <div class="stats-summary">
                <div class="summary-item">
                    <span class="summary-label">Total Processed:</span>
                    <span class="summary-value">${summary.processed || 0}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Average Score:</span>
                    <span class="summary-value">${summary.average_score || 0}%</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Highest Score:</span>
                    <span class="summary-value">${summary.highest_score || 0}%</span>
                </div>
            </div>
        `;

        // Add event listeners for enhanced cards
        addStatisticsCardListeners();
    }

    function addStatisticsCardListeners() {
        const cards = document.querySelectorAll('.match-stat-card');
        
        cards.forEach(card => {
            const category = card.dataset.category;
            const count = parseInt(card.dataset.count);
            
            // Only add interactions if there are applicants in this category
            if (count > 0) {
                card.classList.add('interactive');
                
                // Hover event for preview
                card.addEventListener('mouseenter', () => {
                    showCategoryPreview(category, card);
                });
                
                card.addEventListener('mouseleave', () => {
                    hideCategoryPreview(card);
                });
                
                // Click event for detailed modal
                card.addEventListener('click', () => {
                    openCategoryModal(category);
                });
            } else {
                card.classList.add('empty');
            }
        });
    }

    function updateCategoryTabs(activeCategory) {
        const summary = window.currentMatchSummary;
        if (!summary) return;
        
        const counts = {
            'excellent': summary.excellent_matches || 0,
            'good': summary.good_matches || 0,
            'fair': summary.fair_matches || 0,
            'needs-review': summary.poor_matches || 0
        };
        
        // Update tab counts
        Object.keys(counts).forEach(category => {
            const countEl = document.getElementById(`${category}-tab-count`);
            if (countEl) {
                countEl.textContent = counts[category];
            }
        });
        
        // Set active tab
        const tabs = document.querySelectorAll('.category-tab');
        tabs.forEach(tab => {
            const tabCategory = tab.dataset.category;
            if (tabCategory === activeCategory) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
    }

    async function showCategoryPreview(category, card) {
        const preview = card.querySelector('.stat-preview');
        const previewContent = card.querySelector('.preview-content');
        
        if (!preview || !previewContent) return;
        
        // Show preview with loading state
        preview.classList.add('visible');
        
        try {
            // Fetch preview data for this category
            const previewData = await fetchCategoryPreview(category);
            
            if (previewData && previewData.length > 0) {
                const previewHTML = previewData.slice(0, 3).map(applicant => `
                    <div class="preview-applicant">
                        <div class="preview-name">${applicant.full_name}</div>
                        <div class="preview-score">${applicant.match_score}%</div>
                        <div class="preview-job">${applicant.job_title}</div>
                    </div>
                `).join('');
                
                previewContent.innerHTML = `
                    <div class="preview-header">Top Candidates</div>
                    ${previewHTML}
                    ${previewData.length > 3 ? `<div class="preview-more">+${previewData.length - 3} more</div>` : ''}
                `;
            } else {
                previewContent.innerHTML = `
                    <div class="preview-header">No Candidates</div>
                    <div class="preview-empty">No applicants in this category</div>
                `;
            }
        } catch (error) {
            console.error('Failed to load preview:', error);
            previewContent.innerHTML = `
                <div class="preview-header">Preview Error</div>
                <div class="preview-error">Failed to load preview</div>
            `;
        }
    }

    function hideCategoryPreview(card) {
        const preview = card.querySelector('.stat-preview');
        if (preview) {
            preview.classList.remove('visible');
        }
    }

    async function fetchCategoryPreview(category) {
        try {
            // Get score ranges for categories
            const scoreRanges = {
                'excellent': { min: 90, max: 100 },
                'good': { min: 75, max: 89 },
                'fair': { min: 60, max: 74 },
                'needs-review': { min: 0, max: 59 }
            };
            
            const range = scoreRanges[category];
            if (!range) return [];
            
            // Filter current applicants data by score range
            if (window.applicantsData && window.applicantsData.length > 0) {
                return window.applicantsData
                    .filter(applicant => {
                        const score = parseFloat(applicant.match_score) || 0;
                        return score >= range.min && score <= range.max;
                    })
                    .sort((a, b) => (parseFloat(b.match_score) || 0) - (parseFloat(a.match_score) || 0))
                    .slice(0, 5); // Get top 5 for preview
            }
            
            return [];
        } catch (error) {
            console.error('Error fetching category preview:', error);
            return [];
        }
    }

    function openCategoryModal(category) {
        const modal = document.getElementById('categoryModal');
        if (!modal) {
            console.error('Category modal not found');
            return;
        }
        
        // Set active category
        window.currentCategory = category;
        
        // Update modal title
        const titleEl = document.getElementById('categoryModalTitle');
        if (titleEl) {
            const categoryLabels = {
                'excellent': '🔥 Excellent Matches',
                'good': '🟢 Good Matches', 
                'fair': '🟡 Fair Matches',
                'needs-review': '⚠️ Needs Review'
            };
            titleEl.innerHTML = `<i class="fas fa-users"></i> ${categoryLabels[category] || 'Match Results'}`;
        }
        
        // Update tab counts and set active tab
        updateCategoryTabs(category);
        
        // Load category content
        loadCategoryContent(category);
        
        // Show modal
        modal.style.display = 'flex';
    }

    // Helper function to filter main view by category (temporary)
    function filterApplicantsByCategory(category) {
        const scoreRanges = {
            'excellent': { min: 90, max: 100 },
            'good': { min: 75, max: 89 },
            'fair': { min: 60, max: 74 },
            'needs-review': { min: 0, max: 59 }
        };
        
        const range = scoreRanges[category];
        if (!range || !window.applicantsData) return;
        
        const filteredApplicants = window.applicantsData.filter(applicant => {
            const score = parseFloat(applicant.match_score) || 0;
            return score >= range.min && score <= range.max;
        });
        
        // Update the main applicants display with filtered results
        if (window.renderApplicants) {
            window.renderApplicants(filteredApplicants);
        }
        
        showNotification(`Showing ${filteredApplicants.length} applicants in "${category}" category`, 'success');
    }

    async function loadCategoryContent(category) {
        const contentEl = document.getElementById('categoryContent');
        const loadingEl = document.getElementById('categoryLoading');
        const emptyEl = document.getElementById('categoryEmpty');
        const accordionEl = document.getElementById('jobsAccordion');
        
        if (!contentEl) return;
        
        // Show loading state
        loadingEl.style.display = 'block';
        emptyEl.style.display = 'none';
        accordionEl.style.display = 'none';
        
        try {
            // Fetch categorized data
            const response = await fetch(`../../backend/employer/get_categorized_matches.php?category=${category}`);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Failed to load category data');
            }
            
            if (data.success) {
                if (data.job_groups && data.job_groups.length > 0) {
                    renderJobAccordions(data.job_groups);
                    accordionEl.style.display = 'block';
                } else {
                    emptyEl.style.display = 'block';
                }
            } else {
                throw new Error(data.error || 'Failed to load category data');
            }
            
        } catch (error) {
            console.error('Error loading category content:', error);
            showError('Failed to load category data: ' + error.message);
            emptyEl.style.display = 'block';
        } finally {
            loadingEl.style.display = 'none';
        }
    }

    // Render job accordions
    function renderJobAccordions(jobGroups) {
        const accordionEl = document.getElementById('jobsAccordion');
        if (!accordionEl) return;
        
        accordionEl.innerHTML = '';
        
        jobGroups.forEach((group, index) => {
            const jobAccordion = createJobAccordion(group, index);
            accordionEl.appendChild(jobAccordion);
        });
    }

    // FIXED: Create individual job accordion
    function createJobAccordion(group, index) {
        const template = document.getElementById('jobAccordionTemplate');
        if (!template) {
            console.error('❌ Job accordion template not found');
            return document.createElement('div');
        }
        
        // Clone the template content properly
        const accordionFragment = template.content.cloneNode(true);
        
        // Get the main accordion element BEFORE doing any manipulations
        const accordionEl = accordionFragment.querySelector('.job-accordion');
        if (!accordionEl) {
            console.error('❌ Job accordion element not found in template');
            return document.createElement('div');
        }
        
        // Set job info
        const titleEl = accordionFragment.querySelector('.job-title');
        const countEl = accordionFragment.querySelector('.job-applicant-count');
        const scoreEl = accordionFragment.querySelector('.job-average-score');
        
        if (titleEl) titleEl.textContent = group.job_info.job_title;
        if (countEl) countEl.textContent = `${group.stats.count} applicant${group.stats.count !== 1 ? 's' : ''}`;
        if (scoreEl) scoreEl.textContent = `Avg: ${group.stats.average_score}%`;
        
        // Set accordion ID
        const accordionId = `job-accordion-${index}`;
        accordionEl.setAttribute('id', accordionId);
        
        // Add toggle functionality
        const header = accordionFragment.querySelector('.job-accordion-header');
        const content = accordionFragment.querySelector('.job-accordion-content');
        
        if (header && content) {
            header.addEventListener('click', () => {
                const isExpanded = accordionEl.classList.contains('expanded');
                
                // Close all other accordions
                document.querySelectorAll('.job-accordion.expanded').forEach(acc => {
                    if (acc !== accordionEl) {
                        acc.classList.remove('expanded');
                        acc.querySelector('.job-accordion-content').style.display = 'none';
                    }
                });
                
                // Toggle current accordion
                if (isExpanded) {
                    accordionEl.classList.remove('expanded');
                    content.style.display = 'none';
                } else {
                    accordionEl.classList.add('expanded');
                    content.style.display = 'block';
                    
                    // Load applicants if not already loaded
                    if (!content.dataset.loaded) {
                        renderApplicantsInAccordion(group.applicants, content);
                        content.dataset.loaded = 'true';
                    }
                }
            });
            
            // Auto-expand first accordion
            if (index === 0) {
                accordionEl.classList.add('expanded');
                content.style.display = 'block';
                renderApplicantsInAccordion(group.applicants, content);
                content.dataset.loaded = 'true';
            }
        }
        
        // Return the actual DOM element, not the fragment
        return accordionEl;
    }

    // Render applicants within accordion
    function renderApplicantsInAccordion(applicants, contentEl) {
        const listEl = contentEl.querySelector('.applicants-list');
        if (!listEl) return;
        
        listEl.innerHTML = '';
        
        applicants.forEach(applicant => {
            const applicantCard = createCategoryApplicantCard(applicant);
            listEl.appendChild(applicantCard);
        });
    }

    // FIXED: Create individual applicant card for category modal
    function createCategoryApplicantCard(applicant) {
        const template = document.getElementById('categoryApplicantTemplate');
        if (!template) {
            console.error('❌ Category applicant template not found');
            return document.createElement('div');
        }
        
        // Clone the template content properly
        const cardFragment = template.content.cloneNode(true);
        
        // Get the main card element BEFORE doing any manipulations
        const cardEl = cardFragment.querySelector('.category-applicant-card');
        if (!cardEl) {
            console.error('❌ Category applicant card element not found in template');
            return document.createElement('div');
        }
        
        // Set applicant info
        const nameEl = cardFragment.querySelector('.applicant-name');
        const scoreEl = cardFragment.querySelector('.applicant-score');
        const avatarImg = cardFragment.querySelector('.applicant-avatar img');
        const avatarFallback = cardFragment.querySelector('.avatar-fallback');
        
        if (nameEl) nameEl.textContent = applicant.full_name;
        if (scoreEl) scoreEl.textContent = `${applicant.match_score}%`;
        
        // Handle avatar
        if (applicant.profile_picture) {
            if (avatarImg) {
                avatarImg.src = applicant.profile_picture;
                avatarImg.style.display = 'block';
            }
            if (avatarFallback) avatarFallback.style.display = 'none';
        } else {
            if (avatarImg) avatarImg.style.display = 'none';
            if (avatarFallback) avatarFallback.style.display = 'flex';
        }
        
        // Set skills analysis
        const matchedSkillsEl = cardFragment.querySelector('.skills-list.matched');
        const missingSkillsEl = cardFragment.querySelector('.skills-list.missing');
        
        if (applicant.skills_analysis) {
            if (matchedSkillsEl) {
                if (applicant.skills_analysis.matched_skills.length > 0) {
                    matchedSkillsEl.innerHTML = applicant.skills_analysis.matched_skills
                        .map(skill => `<span class="skill-tag">${skill}</span>`)
                        .join('');
                } else {
                    matchedSkillsEl.innerHTML = '<span class="no-skills">No matched skills</span>';
                }
            }
            
            if (missingSkillsEl) {
                if (applicant.skills_analysis.missing_skills.length > 0) {
                    missingSkillsEl.innerHTML = applicant.skills_analysis.missing_skills
                        .map(skill => `<span class="skill-tag">${skill}</span>`)
                        .join('');
                } else {
                    missingSkillsEl.innerHTML = '<span class="no-skills">No missing skills</span>';
                }
            }
        }
        
        // Set resume preview with enhanced design
        const resumePreview = cardFragment.querySelector('.resume-preview');
        if (resumePreview && applicant.resume_content) {
            const resumeContent = applicant.resume_content;
            
            if (resumeContent.includes('Resume file available')) {
                // File-based resume
                resumePreview.innerHTML = `
                    <div class="resume-file-info">
                        <div class="file-icon">📄</div>
                        <div class="file-details">
                            <div class="file-name">${applicant.resume_file || 'Resume File'}</div>
                            <div class="file-type">${applicant.resume_type || 'PDF Document'}</div>
                            <button class="view-full-resume-btn" onclick="viewFullResume('${applicant.application_id}')">
                                <i class="fas fa-external-link-alt"></i> View Full Resume
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Text content preview
                resumePreview.innerHTML = `
                    <div class="resume-text-preview">
                        <div class="preview-text">${resumeContent}</div>
                        <button class="view-full-resume-btn" onclick="viewFullResume('${applicant.application_id}')">
                            <i class="fas fa-expand"></i> View Full Resume
                        </button>
                    </div>
                `;
            }
        } else if (resumePreview) {
            resumePreview.innerHTML = `
                <div class="no-resume">
                    <i class="fas fa-file-slash"></i>
                    <span>No resume available</span>
                </div>
            `;
        }
        
        // Add action button listeners
        addCategoryApplicantActions(cardFragment, applicant);
        
        // Return the actual DOM element, not the fragment
        return cardEl;
    }

    // Add action button listeners for category applicant cards
    function addCategoryApplicantActions(card, applicant) {
        // View resume button - FIXED to use enhanced viewer
        const viewResumeBtn = card.querySelector('.view-full-resume-btn');
        if (viewResumeBtn) {
            viewResumeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔄 View Full Resume clicked for:', applicant.application_id);
                
                if (applicant.resume_file || applicant.application_id) {
                    // Use enhanced viewer instead of opening new tab
                    openEnhancedResumeViewer(applicant.application_id);
                } else {
                    showNotification('No resume file available', 'warning');
                }
            });
        }
        
        // View profile button
        const viewProfileBtn = card.querySelector('.view-profile-btn');
        if (viewProfileBtn) {
            viewProfileBtn.addEventListener('click', () => {
                if (window.openApplicantModal) {
                    window.openApplicantModal(applicant.application_id);
                }
            });
        }
        
        // Status action buttons
        const statusBtns = card.querySelectorAll('.status-btn');
        statusBtns.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const status = btn.dataset.status;
                const success = await updateApplicantStatusInCategory(applicant.application_id, status);
                
                if (success) {
                    showNotification(`Status updated to: ${status.replace('_', ' ')}`, 'success');
                    if (window.currentCategory) {
                        loadCategoryContent(window.currentCategory);
                    }
                }
            });
        });
    }

    // Update applicant status from category modal
    async function updateApplicantStatusInCategory(applicationId, newStatus) {
        try {
            const response = await fetch('../../backend/employer/update_application_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    application_id: applicationId,
                    status: newStatus
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                return true;
            } else {
                showError(data.message || 'Failed to update status');
                return false;
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showError('Failed to update status');
            return false;
        }
    }

    // Enhanced tab switching functionality - Add this to empapplicants.js
    document.addEventListener('DOMContentLoaded', function() {
        // Category tab switching - ENHANCED VERSION
        const categoryTabs = document.querySelectorAll('.category-tab');
        categoryTabs.forEach(tab => {
            tab.addEventListener('click', async function() {
                const category = tab.dataset.category;
                const count = parseInt(tab.querySelector('.tab-count').textContent);
                
                console.log('🔄 Tab clicked:', category, 'Count:', count);
                
                // Always allow tab switching, even if count is 0
                // Update active tab immediately for better UX
                categoryTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Update modal title
                const titleEl = document.getElementById('categoryModalTitle');
                if (titleEl) {
                    const categoryLabels = {
                        'excellent': '🔥 Excellent Matches',
                        'good': '🟢 Good Matches', 
                        'fair': '🟡 Fair Matches',
                        'needs-review': '⚠️ Needs Review'
                    };
                    titleEl.innerHTML = `<i class="fas fa-users"></i> ${categoryLabels[category] || 'Match Results'}`;
                }
                
                // Load new category content
                await loadCategoryContent(category);
                window.currentCategory = category;
            });
        });
        
        // Enhanced modal close handlers
        const categoryModal = document.getElementById('categoryModal');
        if (categoryModal) {
            // Close button handler
            const closeBtn = categoryModal.querySelector('[data-modal="categoryModal"]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    console.log('🔄 Closing modal via X button');
                    categoryModal.style.display = 'none';
                });
            }
            
            // Back button handler
            const backBtn = categoryModal.querySelector('.secondary-btn');
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    console.log('🔄 Closing modal via Back button');
                    categoryModal.style.display = 'none';
                });
            }
            
            // Click outside to close
            categoryModal.addEventListener('click', function(e) {
                if (e.target === categoryModal) {
                    console.log('🔄 Closing modal via outside click');
                    categoryModal.style.display = 'none';
                }
            });
            
            // Escape key to close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && categoryModal.style.display === 'flex') {
                    console.log('🔄 Closing modal via Escape key');
                    categoryModal.style.display = 'none';
                }
            });
        }
    });

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#f44336' : '#2563eb'};
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 10000;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 5000);
    }

    function setupCategoryModalEvents() {
        console.log('🔄 Setting up category modal events with delegation');
        
        // Use event delegation for tab navigation
        document.addEventListener('click', function(e) {
            // Handle category tab clicks
            if (e.target.closest('.category-tab')) {
                const tab = e.target.closest('.category-tab');
                const category = tab.dataset.category;
                
                console.log('🔄 Tab clicked via delegation:', category);
                handleTabClick(e, tab, category);
            }
            
            // Handle modal close buttons
            if (e.target.matches('[data-modal="categoryModal"]') || 
                e.target.closest('[data-modal="categoryModal"]')) {
                console.log('🔄 Closing category modal');
                handleModalClose();
            }
        });
        
        // Enhanced modal setup
        const categoryModal = document.getElementById('categoryModal');
        if (categoryModal) {
            console.log('✅ Category modal found, setting up handlers');
            
            // Click outside to close
            categoryModal.addEventListener('click', function(e) {
                if (e.target === categoryModal) {
                    console.log('🔄 Closing modal via outside click');
                    handleModalClose();
                }
            });
            
            // Escape key to close
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && categoryModal.style.display === 'flex') {
                    console.log('🔄 Closing modal via Escape key');
                    handleModalClose();
                }
            });
        } else {
            console.log('❌ Category modal not found during setup');
        }
    }

    async function handleTabClick(event) {
        const tab = event.currentTarget;
        const category = tab.dataset.category;
        
        console.log('🔄 Tab clicked:', category);
        
        // Update active tab immediately for better UX
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        
        // Update modal title
        const titleEl = document.getElementById('categoryModalTitle');
        if (titleEl) {
            const categoryLabels = {
                'excellent': '🔥 Excellent Matches',
                'good': '🟢 Good Matches', 
                'fair': '🟡 Fair Matches',
                'needs-review': '⚠️ Needs Review'
            };
            titleEl.innerHTML = `<i class="fas fa-users"></i> ${categoryLabels[category] || 'Match Results'}`;
        }
        
        // Load new category content
        try {
            await loadCategoryContent(category);
            window.currentCategory = category;
        } catch (error) {
            console.error('Error loading category:', error);
        }
    }

    function handleModalClose(event) {
        console.log('🔄 Closing modal via button click');
        const categoryModal = document.getElementById('categoryModal');
        if (categoryModal) {
            categoryModal.style.display = 'none';
        }
    }

    function handleOutsideClick(event) {
        if (event.target === event.currentTarget) {
            console.log('🔄 Closing modal via outside click');
            event.currentTarget.style.display = 'none';
        }
    }

    function handleEscapeKey(event) {
        if (event.key === 'Escape') {
            const categoryModal = document.getElementById('categoryModal');
            if (categoryModal && categoryModal.style.display === 'flex') {
                console.log('🔄 Closing modal via Escape key');
                categoryModal.style.display = 'none';
            }
        }
    }

    function viewFullResume(applicationId) {
        console.log('🔄 Opening enhanced resume viewer for application:', applicationId);
        openEnhancedResumeViewer(applicationId);
    }

    // ===================================
// PHASE 3: ENHANCED PDF VIEWER SYSTEM
// ===================================

// Global variables for resume viewer
let currentResumeData = null;
let currentZoomLevel = 100;
let isFullscreen = false;

// Enhanced function to open PDF viewer modal
async function openEnhancedResumeViewer(applicationId) {
    const modal = document.getElementById('resumeViewerModal');
    if (!modal) {
        console.error('❌ Resume viewer modal not found');
        return;
    }
    
    try {
        // Show modal immediately with loading state
        modal.style.display = 'flex';
        showResumeLoading();
        
        // Fetch detailed applicant data
        const applicantData = await fetchApplicantDetailsForResume(applicationId);
        if (!applicantData) {
            throw new Error('Failed to fetch applicant details');
        }
        
        currentResumeData = applicantData;
        
        // Populate modal with applicant data
        populateResumeViewerModal(applicantData);
        
        // Load the resume content
        await loadResumeContent(applicantData);
        
        // Setup modal event listeners
        setupResumeViewerEventListeners();
        
        console.log('✅ Resume viewer opened successfully');
        
    } catch (error) {
        console.error('❌ Error opening resume viewer:', error);
        showResumeError('Failed to load resume: ' + error.message);
    }
}

// Fetch detailed applicant data for resume viewing
async function fetchApplicantDetailsForResume(applicationId) {
    try {
        const response = await fetch(`../../backend/employer/get_applicant_details.php?application_id=${applicationId}`);
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Failed to fetch applicant details');
        }
        
        if (data.success) {
            return data;
        } else {
            throw new Error(data.message || 'Failed to load applicant details');
        }
        
    } catch (error) {
        console.error('Error fetching applicant details:', error);
        throw error;
    }
}

// Populate resume viewer modal with applicant data
function populateResumeViewerModal(data) {
    const applicant = data.applicant;
    
    // Header information
    document.getElementById('resumeApplicantName').textContent = applicant.full_name || 'Unknown Applicant';
    document.getElementById('resumeJobTitle').textContent = applicant.job_title || 'Job Position';
    document.getElementById('resumeMatchScore').textContent = `${applicant.match_score || 0}% Match`;
    
    // Score circle
    const scoreCircle = document.getElementById('resumeScoreCircle');
    const scoreNumber = document.getElementById('resumeScoreNumber');
    if (scoreCircle && scoreNumber) {
        const score = applicant.match_score || 0;
        scoreNumber.textContent = `${score}%`;
        
        // Color based on score
        let scoreColor = '#ef4444'; // Red for low scores
        if (score >= 90) scoreColor = '#10b981'; // Green for excellent
        else if (score >= 75) scoreColor = '#f59e0b'; // Orange for good
        else if (score >= 60) scoreColor = '#3b82f6'; // Blue for fair
        
        scoreCircle.style.borderColor = scoreColor;
        scoreNumber.style.color = scoreColor;
    }
    
    // Skills analysis
    if (data.skills_analysis) {
        populateSkillsAnalysis(data.skills_analysis);
    }
    
    // Applicant details (right panel)
    document.getElementById('resumeApplicantFullName').textContent = applicant.full_name;
    document.getElementById('resumeApplicantHeadline').textContent = applicant.headline || 'Job Seeker';
    document.getElementById('resumeApplicantEmail').textContent = applicant.email || 'No email available';
    document.getElementById('resumeApplicantPhone').textContent = applicant.contact_number || 'No phone available';
    document.getElementById('resumeApplicantLocation').textContent = getLocationText(applicant) || 'Location not specified';
    
    // Application details
    document.getElementById('resumeJobPosition').textContent = applicant.job_title || 'Job Position';
    document.getElementById('resumeApplicationDate').textContent = formatDate(applicant.applied_at);
    
    const statusBadge = document.getElementById('resumeApplicationStatus');
    if (statusBadge) {
        statusBadge.textContent = formatApplicationStatus(applicant.application_status);
        statusBadge.className = `detail-value status-badge status-${applicant.application_status}`;
    }
    
    // Avatar handling
    const avatar = document.getElementById('resumeApplicantAvatar');
    const avatarFallback = document.querySelector('.avatar-fallback-large');
    
    if (applicant.profile_picture) {
        avatar.src = applicant.profile_picture;
        avatar.style.display = 'block';
        avatarFallback.style.display = 'none';
    } else {
        avatar.style.display = 'none';
        avatarFallback.style.display = 'flex';
    }
}

// Populate skills analysis section
function populateSkillsAnalysis(skillsData) {
    const matchedSkills = document.getElementById('resumeMatchedSkills');
    const missingSkills = document.getElementById('resumeMissingSkills');
    const bonusSkills = document.getElementById('resumeBonusSkills');
    
    if (matchedSkills && skillsData.matched_skills) {
        matchedSkills.innerHTML = skillsData.matched_skills
            .map(skill => `<span class="skill-tag matched">${skill}</span>`)
            .join('') || '<span class="no-skills">No matched skills</span>';
    }
    
    if (missingSkills && skillsData.missing_skills) {
        missingSkills.innerHTML = skillsData.missing_skills
            .map(skill => `<span class="skill-tag missing">${skill}</span>`)
            .join('') || '<span class="no-skills">No missing skills</span>';
    }
    
    if (bonusSkills && skillsData.bonus_skills) {
        bonusSkills.innerHTML = skillsData.bonus_skills
            .map(skill => `<span class="skill-tag bonus">${skill}</span>`)
            .join('') || '<span class="no-skills">No bonus skills</span>';
    }
}

// Load resume content (PDF or text)
async function loadResumeContent(applicantData) {
    const applicant = applicantData.applicant;
    const pdfViewer = document.getElementById('resumePdfViewer');
    const textViewer = document.getElementById('resumeTextViewer');
    const controls = document.getElementById('resumeViewerControls');
    
    try {
        // Update file information
        updateResumeFileInfo(applicant);
        
        // Determine file type and load accordingly
        const fileType = applicant.resume_type || '';
        const fileName = applicant.resume_file || '';
        const fileExtension = fileName.split('.').pop()?.toLowerCase();
        
        hideResumeLoading();
        
        if (fileExtension === 'pdf' || fileType.includes('pdf')) {
            // Load PDF
            const resumeUrl = `../../backend/employer/view_resume.php?application_id=${applicant.application_id}`;
            pdfViewer.src = resumeUrl;
            pdfViewer.style.display = 'block';
            textViewer.style.display = 'none';
            controls.style.display = 'flex';
            
            // Handle PDF load events
            pdfViewer.onload = () => {
                console.log('✅ PDF loaded successfully');
            };
            
            pdfViewer.onerror = () => {
                console.error('❌ Failed to load PDF');
                showResumeError('Failed to load PDF file. The file may be corrupted or incompatible.');
            };
            
        } else {
            // Handle non-PDF files (show text content or download option)
            if (applicant.resume_content && !applicant.resume_content.includes('Resume file available')) {
                // Show text content
                textViewer.style.display = 'block';
                pdfViewer.style.display = 'none';
                controls.style.display = 'none';
                
                document.getElementById('resumeTextContent').innerHTML = `
                    <div class="text-resume-content">
                        <h3>Resume Content</h3>
                        <pre>${applicant.resume_content}</pre>
                    </div>
                `;
            } else {
                // Show download option for unsupported files
                showResumeError(
                    `This file type (${fileExtension.toUpperCase()}) cannot be previewed directly. ` +
                    'Please download the file to view it.',
                    true
                );
            }
        }
        
    } catch (error) {
        console.error('Error loading resume content:', error);
        showResumeError('Failed to load resume content: ' + error.message);
    }
}

// Update file information in the details panel
function updateResumeFileInfo(applicant) {
    document.getElementById('resumeFileName').textContent = applicant.resume_file || 'Unknown file';
    document.getElementById('resumeFileType').textContent = (applicant.resume_type || 'Unknown type').toUpperCase();
    
    // Calculate and display file size if available
    const fileSize = applicant.file_size;
    if (fileSize) {
        const sizeInMB = (fileSize / (1024 * 1024)).toFixed(2);
        document.getElementById('resumeFileSize').textContent = `${sizeInMB} MB`;
    } else {
        document.getElementById('resumeFileSize').textContent = 'Unknown size';
    }
}

// Show resume loading state
function showResumeLoading() {
    document.getElementById('resumeLoading').style.display = 'flex';
    document.getElementById('resumeError').style.display = 'none';
    document.getElementById('resumePdfViewer').style.display = 'none';
    document.getElementById('resumeTextViewer').style.display = 'none';
    document.getElementById('resumeViewerControls').style.display = 'none';
}

// Hide resume loading state
function hideResumeLoading() {
    document.getElementById('resumeLoading').style.display = 'none';
}

// Show resume error state
function showResumeError(message, showDownload = false) {
    const errorEl = document.getElementById('resumeError');
    const messageEl = document.getElementById('resumeErrorMessage');
    const downloadBtn = document.getElementById('resumeDownloadAnyway');
    
    hideResumeLoading();
    
    if (errorEl && messageEl) {
        messageEl.textContent = message;
        errorEl.style.display = 'flex';
        
        if (downloadBtn) {
            downloadBtn.style.display = showDownload ? 'block' : 'none';
        }
    }
    
    // Hide other content
    document.getElementById('resumePdfViewer').style.display = 'none';
    document.getElementById('resumeTextViewer').style.display = 'none';
    document.getElementById('resumeViewerControls').style.display = 'none';
}

// Setup resume viewer event listeners
function setupResumeViewerEventListeners() {
    const modal = document.getElementById('resumeViewerModal');
    if (!modal) return;
    
    // Close modal handlers
    const closeBtn = modal.querySelector('[data-modal="resumeViewerModal"]');
    if (closeBtn) {
        closeBtn.onclick = () => closeResumeViewer();
    }
    
    // Download handlers
    const downloadBtn = document.getElementById('resumeDownloadBtn');
    const downloadPrimary = document.getElementById('resumeDownloadPrimary');
    const downloadAnyway = document.getElementById('resumeDownloadAnyway');
    
    [downloadBtn, downloadPrimary, downloadAnyway].forEach(btn => {
        if (btn) {
            btn.onclick = () => downloadCurrentResume();
        }
    });
    
    // Open in new tab
    const openNewBtn = document.getElementById('resumeOpenNew');
    if (openNewBtn) {
        openNewBtn.onclick = () => openResumeInNewTab();
    }
    
    // Search functionality
    const searchBtn = document.getElementById('resumeSearchBtn');
    const searchClose = document.getElementById('resumeSearchClose');
    const searchExecute = document.getElementById('resumeSearchExecute');
    const searchInput = document.getElementById('resumeSearchInput');
    
    if (searchBtn) {
        searchBtn.onclick = () => toggleResumeSearch();
    }
    
    if (searchClose) {
        searchClose.onclick = () => hideResumeSearch();
    }
    
    if (searchExecute) {
        searchExecute.onclick = () => executeResumeSearch();
    }
    
    if (searchInput) {
        searchInput.onkeypress = (e) => {
            if (e.key === 'Enter') {
                executeResumeSearch();
            }
        };
    }
    
    // Zoom controls
    setupZoomControls();
    
    // Quick actions
    setupQuickActions();
    
    // Fullscreen toggle
    const fullscreenBtn = document.getElementById('resumeFullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.onclick = () => toggleResumeFullscreen();
    }
    
    // ESC key to close
    document.addEventListener('keydown', handleResumeViewerKeydown);
}

// Close resume viewer
function closeResumeViewer() {
    const modal = document.getElementById('resumeViewerModal');
    if (modal) {
        modal.style.display = 'none';
        currentResumeData = null;
        currentZoomLevel = 100;
        isFullscreen = false;
        
        // Clean up event listeners
        document.removeEventListener('keydown', handleResumeViewerKeydown);
    }
}

// Handle keyboard shortcuts
function handleResumeViewerKeydown(event) {
    const modal = document.getElementById('resumeViewerModal');
    if (!modal || modal.style.display === 'none') return;
    
    switch (event.key) {
        case 'Escape':
            closeResumeViewer();
            break;
        case 'f':
        case 'F':
            if (event.ctrlKey || event.metaKey) {
                event.preventDefault();
                toggleResumeSearch();
            }
            break;
        case 'F11':
            event.preventDefault();
            toggleResumeFullscreen();
            break;
    }
}

// Download current resume
function downloadCurrentResume() {
    if (!currentResumeData) return;
    
    const applicationId = currentResumeData.applicant.application_id;
    const downloadUrl = `../../backend/employer/download_resume.php?application_id=${applicationId}`;
    
    // Create temporary download link
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = currentResumeData.applicant.resume_file || 'resume.pdf';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Resume download started', 'success');
}

// Open resume in new tab
function openResumeInNewTab() {
    if (!currentResumeData) return;
    
    const applicationId = currentResumeData.applicant.application_id;
    const viewUrl = `../../backend/employer/view_resume.php?application_id=${applicationId}`;
    
    window.open(viewUrl, '_blank');
}

// Utility functions
function formatApplicationStatus(status) {
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

function formatDate(dateString) {
    if (!dateString) return 'Date not available';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// ===================================
// PHASE 3 PART 3: ADVANCED SEARCH & ANALYSIS
// ===================================

// Resume Search Functionality
function toggleResumeSearch() {
    const searchPanel = document.getElementById('resumeSearchPanel');
    const searchInput = document.getElementById('resumeSearchInput');
    
    if (searchPanel.style.display === 'none' || !searchPanel.style.display) {
        searchPanel.style.display = 'block';
        searchInput.focus();
    } else {
        hideResumeSearch();
    }
}

function hideResumeSearch() {
    const searchPanel = document.getElementById('resumeSearchPanel');
    const searchResults = document.getElementById('resumeSearchResults');
    const searchInput = document.getElementById('resumeSearchInput');
    
    searchPanel.style.display = 'none';
    searchResults.innerHTML = '';
    searchInput.value = '';
}

function executeResumeSearch() {
    const searchInput = document.getElementById('resumeSearchInput');
    const searchResults = document.getElementById('resumeSearchResults');
    const query = searchInput.value.trim();
    
    if (!query) {
        searchResults.innerHTML = '<div class="search-error">Please enter a search term</div>';
        return;
    }
    
    if (!currentResumeData) {
        searchResults.innerHTML = '<div class="search-error">No resume data available</div>';
        return;
    }
    
    // Search in resume content
    const resumeContent = currentResumeData.applicant.resume_content || '';
    const matches = searchInText(resumeContent, query);
    
    if (matches.length > 0) {
        displaySearchResults(matches, query);
        highlightSearchResults(query);
    } else {
        searchResults.innerHTML = `
            <div class="search-no-results">
                <i class="fas fa-search"></i>
                No results found for "${query}"
            </div>
        `;
    }
}

function searchInText(text, query) {
    const regex = new RegExp(query, 'gi');
    const matches = [];
    let match;
    
    while ((match = regex.exec(text)) !== null) {
        const start = Math.max(0, match.index - 50);
        const end = Math.min(text.length, match.index + query.length + 50);
        const context = text.substring(start, end);
        
        matches.push({
            index: match.index,
            context: context,
            query: query
        });
    }
    
    return matches;
}

function displaySearchResults(matches, query) {
    const searchResults = document.getElementById('resumeSearchResults');
    
    const resultsHTML = `
        <div class="search-summary">
            Found ${matches.length} occurrence${matches.length !== 1 ? 's' : ''} of "${query}"
        </div>
        <div class="search-matches">
            ${matches.map((match, index) => `
                <div class="search-match" onclick="scrollToMatch(${index})">
                    <div class="match-context">
                        ${highlightQueryInContext(match.context, query)}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    searchResults.innerHTML = resultsHTML;
}

function highlightQueryInContext(context, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return context.replace(regex, '<mark>$1</mark>');
}

function highlightSearchResults(query) {
    // Highlight results in PDF viewer (limited functionality)
    const textViewer = document.getElementById('resumeTextViewer');
    if (textViewer && textViewer.style.display !== 'none') {
        const content = textViewer.innerHTML;
        const regex = new RegExp(`(${query})`, 'gi');
        const highlighted = content.replace(regex, '<mark class="search-highlight">$1</mark>');
        textViewer.innerHTML = highlighted;
    }
}

// Zoom Controls Implementation
function setupZoomControls() {
    const zoomInBtn = document.getElementById('resumeZoomIn');
    const zoomOutBtn = document.getElementById('resumeZoomOut');
    const fitWidthBtn = document.getElementById('resumeFitWidth');
    const fitPageBtn = document.getElementById('resumeFitPage');
    const zoomLevelDisplay = document.getElementById('resumeZoomLevel');
    
    if (zoomInBtn) {
        zoomInBtn.onclick = () => {
            currentZoomLevel = Math.min(200, currentZoomLevel + 25);
            updateZoom();
        };
    }
    
    if (zoomOutBtn) {
        zoomOutBtn.onclick = () => {
            currentZoomLevel = Math.max(25, currentZoomLevel - 25);
            updateZoom();
        };
    }
    
    if (fitWidthBtn) {
        fitWidthBtn.onclick = () => {
            currentZoomLevel = 100;
            updateZoom();
        };
    }
    
    if (fitPageBtn) {
        fitPageBtn.onclick = () => {
            currentZoomLevel = 75;
            updateZoom();
        };
    }
}

function updateZoom() {
    const pdfViewer = document.getElementById('resumePdfViewer');
    const textViewer = document.getElementById('resumeTextViewer');
    const zoomLevelDisplay = document.getElementById('resumeZoomLevel');
    
    if (zoomLevelDisplay) {
        zoomLevelDisplay.textContent = `${currentZoomLevel}%`;
    }
    
    if (pdfViewer && pdfViewer.style.display !== 'none') {
        pdfViewer.style.transform = `scale(${currentZoomLevel / 100})`;
        pdfViewer.style.transformOrigin = 'top left';
    }
    
    if (textViewer && textViewer.style.display !== 'none') {
        textViewer.style.fontSize = `${currentZoomLevel}%`;
    }
}

// Quick Actions Implementation
function setupQuickActions() {
    const scheduleBtn = document.getElementById('resumeScheduleInterview');
    const hireBtn = document.getElementById('resumeHireApplicant');
    const rejectBtn = document.getElementById('resumeRejectApplicant');
    
    if (scheduleBtn) {
        scheduleBtn.onclick = () => {
            if (currentResumeData) {
                scheduleInterviewFromResume(currentResumeData.applicant.application_id);
            }
        };
    }
    
    if (hireBtn) {
        hireBtn.onclick = () => {
            if (currentResumeData) {
                hireApplicantFromResume(currentResumeData.applicant.application_id);
            }
        };
    }
    
    if (rejectBtn) {
        rejectBtn.onclick = () => {
            if (currentResumeData) {
                rejectApplicantFromResume(currentResumeData.applicant.application_id);
            }
        };
    }
}

async function scheduleInterviewFromResume(applicationId) {
    closeResumeViewer();
    
    // Open existing interview scheduling modal
    if (window.openInterviewModal) {
        window.openInterviewModal(applicationId);
    } else {
        showNotification('Interview scheduling feature coming soon!', 'info');
    }
}

async function hireApplicantFromResume(applicationId) {
    const confirmed = confirm('Are you sure you want to hire this applicant?');
    if (!confirmed) return;
    
    try {
        const success = await updateApplicantStatusInCategory(applicationId, 'hired');
        if (success) {
            showNotification('Applicant hired successfully!', 'success');
            closeResumeViewer();
            
            // Refresh the main applicants view
            if (window.fetchApplicants) {
                window.fetchApplicants(window.currentFilters);
            }
        }
    } catch (error) {
        showError('Failed to hire applicant: ' + error.message);
    }
}

async function rejectApplicantFromResume(applicationId) {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason === null) return; // User cancelled
    
    try {
        const success = await updateApplicantStatusInCategory(applicationId, 'rejected');
        if (success) {
            showNotification('Applicant rejected', 'success');
            closeResumeViewer();
            
            // Refresh the main applicants view
            if (window.fetchApplicants) {
                window.fetchApplicants(window.currentFilters);
            }
        }
    } catch (error) {
        showError('Failed to reject applicant: ' + error.message);
    }
}

// Fullscreen Implementation
function toggleResumeFullscreen() {
    const modal = document.getElementById('resumeViewerModal');
    const fullscreenBtn = document.getElementById('resumeFullscreenBtn');
    const fullscreenIcon = fullscreenBtn.querySelector('i');
    
    if (!isFullscreen) {
        // Enter fullscreen
        modal.classList.add('fullscreen');
        fullscreenIcon.className = 'fas fa-compress';
        fullscreenBtn.title = 'Exit Fullscreen';
        isFullscreen = true;
        
        // Try to use browser fullscreen API
        if (modal.requestFullscreen) {
            modal.requestFullscreen().catch(console.error);
        } else if (modal.webkitRequestFullscreen) {
            modal.webkitRequestFullscreen();
        } else if (modal.msRequestFullscreen) {
            modal.msRequestFullscreen();
        }
    } else {
        // Exit fullscreen
        modal.classList.remove('fullscreen');
        fullscreenIcon.className = 'fas fa-expand';
        fullscreenBtn.title = 'Toggle Fullscreen';
        isFullscreen = false;
        
        // Exit browser fullscreen
        if (document.exitFullscreen) {
            document.exitFullscreen().catch(console.error);
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

// Enhanced Resume Analysis
function generateResumeInsights(applicantData) {
    const insights = [];
    const applicant = applicantData.applicant;
    const skills = applicantData.skills_analysis;
    
    // Experience analysis
    if (applicant.resume_content && applicant.resume_content.toLowerCase().includes('years')) {
        insights.push({
            icon: 'fas fa-briefcase',
            text: 'Relevant work experience mentioned in resume'
        });
    }
    
    // Education analysis
    if (applicant.resume_content && 
        (applicant.resume_content.toLowerCase().includes('degree') || 
         applicant.resume_content.toLowerCase().includes('university'))) {
        insights.push({
            icon: 'fas fa-graduation-cap',
            text: 'Educational background present'
        });
    }
    
    // Skills analysis
    if (skills && skills.matched_skills && skills.matched_skills.length > 0) {
        insights.push({
            icon: 'fas fa-cogs',
            text: `${skills.matched_skills.length} required skills matched`
        });
    }
    
    if (skills && skills.bonus_skills && skills.bonus_skills.length > 0) {
        insights.push({
            icon: 'fas fa-star',
            text: `${skills.bonus_skills.length} additional skills found`
        });
    }
    
    // Match score analysis
    const score = applicant.match_score || 0;
    if (score >= 90) {
        insights.push({
            icon: 'fas fa-trophy',
            text: 'Excellent match - highly recommended candidate'
        });
    } else if (score >= 75) {
        insights.push({
            icon: 'fas fa-thumbs-up',
            text: 'Good match - consider for interview'
        });
    } else if (score >= 60) {
        insights.push({
            icon: 'fas fa-info-circle',
            text: 'Fair match - may need additional training'
        });
    }
    
    return insights;
}

// Update populateResumeViewerModal to include generated insights
function populateResumeViewerModalEnhanced(data) {
    // Call the existing function
    populateResumeViewerModal(data);
    
    // Add generated insights
    const insights = generateResumeInsights(data);
    const insightsList = document.getElementById('resumeInsightsList');
    
    if (insightsList && insights.length > 0) {
        insightsList.innerHTML = insights.map(insight => `
            <div class="insight-item">
                <i class="${insight.icon}"></i>
                <span>${insight.text}</span>
            </div>
        `).join('');
    }
}

// Additional CSS for search functionality
const searchStyles = `
<style>
.search-summary {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 10px;
    font-weight: 500;
}

.search-matches {
    max-height: 150px;
    overflow-y: auto;
}

.search-match {
    padding: 8px 12px;
    margin-bottom: 5px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.search-match:hover {
    background: #f8f9fa;
}

.match-context {
    font-size: 0.8rem;
    line-height: 1.4;
}

.search-match mark {
    background: #ffd54f;
    padding: 1px 2px;
    border-radius: 2px;
}

.search-highlight {
    background: #ffd54f !important;
    padding: 2px 4px;
    border-radius: 3px;
    animation: highlight-pulse 2s ease-in-out;
}

@keyframes highlight-pulse {
    0% { background: #ffd54f; }
    50% { background: #ffeb3b; }
    100% { background: #ffd54f; }
}

.search-error,
.search-no-results {
    text-align: center;
    padding: 20px;
    color: #666;
    font-style: italic;
}

.search-no-results i {
    display: block;
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}
</style>
`;

// Inject search styles
document.head.insertAdjacentHTML('beforeend', searchStyles);

console.log('✅ Phase 3: Advanced Search & Analysis Features loaded');
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Fixed ThisAble System');
    
    // Set up category modal events
    setupCategoryModalEvents();
    
    // Global event delegation for resume buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.view-full-resume-btn') || 
            e.target.closest('.view-full-resume-btn')) {
            
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.matches('.view-full-resume-btn') ? 
                          e.target : e.target.closest('.view-full-resume-btn');
            
            const card = button.closest('.category-applicant-card');
            if (card && card.dataset.applicationId) {
                console.log('🔄 Opening resume for application:', card.dataset.applicationId);
                openEnhancedResumeViewer(card.dataset.applicationId);
            }
        }
    });
    
    console.log('✅ Fixed system initialized');
});

/**
 * ===================================================================
 * PHASE 4: COMPLETE ENHANCED APPLICANT VIEW - COPY PASTE THIS
 * Add this entire block to the END of your empapplicants.js file
 * ===================================================================
 */

// ===================================================================
// PHASE 4: MAIN ENHANCED VIEW FUNCTION
// ===================================================================

/**
 * Open enhanced applicant view with documents and requirements
 */
async function openEnhancedApplicantView(applicationId) {
    try {
        showLoading();
        
        // Use your existing fetchApplicantDetails function
        const response = await fetch(`${API_BASE}get_applicant_details.php?application_id=${applicationId}`);
        const data = await response.json();
        
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = 'emplogin.php';
                return;
            }
            throw new Error(data.message || 'Failed to fetch applicant details');
        }
        
        if (data.success) {
            showPhase4Modal(data);
        } else {
            throw new Error(data.message || 'Failed to load applicant details');
        }
        
    } catch (error) {
        console.error('❌ Error opening enhanced view:', error);
        showError('Failed to load enhanced applicant view: ' + error.message);
    } finally {
        hideLoading();
    }
}

/**
 * Create and display the Phase 4 enhanced modal
 */
function showPhase4Modal(data) {
    const { applicant, documents, requirements_analysis } = data;
    
    // Create modal element
    const modal = document.createElement('div');
    modal.className = 'phase4-modal';
    modal.innerHTML = createPhase4ModalHTML(applicant, documents || [], requirements_analysis || {});
    
    // Add to page
    document.body.appendChild(modal);
    
    // Setup event listeners
    setupPhase4Events(modal, data);
    
    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    console.log('✅ Phase 4 enhanced view displayed');
}

/**
 * Create the enhanced modal HTML - Perfect for capstone demo
 */
function createPhase4ModalHTML(applicant, documents, analysis) {
    return `
        <div class="modal-overlay">
            <div class="modal-content enhanced-modal">
                <!-- Header -->
                <div class="modal-header enhanced-header">
                    <div class="applicant-info-header">
                        <div class="applicant-avatar">
                            ${applicant.profile_photo_path ? 
                                `<img src="../../${applicant.profile_photo_path}" alt="Profile">` :
                                `<div class="avatar-placeholder">${(applicant.first_name || 'U').charAt(0)}${(applicant.last_name || 'U').charAt(0)}</div>`
                            }
                        </div>
                        <div class="applicant-details">
                            <h2>${applicant.full_name || 'Unknown Applicant'}</h2>
                            <p class="applicant-title">${applicant.headline || 'Job Seeker'}</p>
                            <p class="application-info">Applied: ${formatDate(applicant.applied_at)}</p>
                        </div>
                    </div>
                    <button class="close-phase4-modal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Quick Assessment Cards -->
                <div class="quick-assessment">
                    <div class="assessment-card overall-score">
                        <div class="card-icon">📊</div>
                        <div class="card-content">
                            <div class="score-value">${analysis.overall_score || 0}%</div>
                            <div class="card-label">Document Score</div>
                        </div>
                    </div>
                    <div class="assessment-card documents-count">
                        <div class="card-icon">📁</div>
                        <div class="card-content">
                            <div class="score-value">${documents.length}</div>
                            <div class="card-label">Documents</div>
                        </div>
                    </div>
                    <div class="assessment-card status-card">
                        <div class="card-icon">⭐</div>
                        <div class="card-content">
                            <div class="score-value">${getDocumentStatus(analysis)}</div>
                            <div class="card-label">Status</div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="enhanced-content">
                    <!-- Documents Section -->
                    <div class="content-section">
                        <h3><i class="fas fa-folder-open"></i> Submitted Documents</h3>
                        ${createDocumentsDisplay(documents)}
                    </div>

                    <!-- Requirements Check -->
                    <div class="content-section">
                        <h3><i class="fas fa-check-circle"></i> Requirements Assessment</h3>
                        ${createRequirementsDisplay(analysis)}
                    </div>

                    <!-- Contact & Bio -->
                    <div class="content-section">
                        <h3><i class="fas fa-user"></i> Candidate Information</h3>
                        <div class="candidate-info">
                            <div class="info-item">
                                <strong>Email:</strong> ${applicant.email || 'Not provided'}
                            </div>
                            <div class="info-item">
                                <strong>Phone:</strong> ${applicant.contact_number || 'Not provided'}
                            </div>
                            <div class="info-item">
                                <strong>Location:</strong> ${applicant.location || 'Not specified'}
                            </div>
                            ${applicant.bio ? `
                                <div class="info-item bio">
                                    <strong>Bio:</strong>
                                    <p>${applicant.bio}</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="modal-footer enhanced-footer">
                    <div class="footer-actions">
                        <button class="btn btn-secondary close-phase4-modal">
                            <i class="fas fa-times"></i> Close
                        </button>
                        <button class="btn btn-outline view-resume-btn" data-application-id="${applicant.application_id}">
                            <i class="fas fa-file-pdf"></i> View Resume
                        </button>
                        <button class="btn btn-success approve-btn" data-application-id="${applicant.application_id}">
                            <i class="fas fa-check"></i> Schedule Interview
                        </button>
                        <button class="btn btn-danger reject-btn" data-application-id="${applicant.application_id}">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Create documents display - FIXED for your database columns
 */
function createDocumentsDisplay(documents) {
    if (!documents || documents.length === 0) {
        return `
            <div class="no-documents">
                <div class="no-docs-icon">📂</div>
                <p>No documents submitted yet</p>
            </div>
        `;
    }

    return `
        <div class="documents-grid">
            ${documents.map(doc => `
                <div class="document-item ${doc.is_verified ? 'verified' : ''}">
                    <div class="doc-icon">${getDocumentIcon(doc.document_type)}</div>
                    <div class="doc-info">
                        <div class="doc-name">${doc.document_name || doc.original_filename || 'Unknown Document'}</div>
                        <div class="doc-meta">
                            ${formatDocumentType(doc.document_type)} • 
                            ${formatFileSize(doc.file_size)} • 
                            ${formatDate(doc.upload_date)}
                        </div>
                        ${doc.verification_notes ? `<div class="doc-description">${doc.verification_notes}</div>` : ''}
                    </div>
                    <div class="doc-actions">
                        <button class="btn btn-sm view-doc-btn" data-path="${doc.file_path}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                    ${doc.is_verified ? '<div class="verified-badge">✓</div>' : ''}
                </div>
            `).join('')}
        </div>
    `;
}

/**
 * Create requirements display - FIXED for your document types
 */
function createRequirementsDisplay(analysis) {
    if (!analysis) {
        return `<div class="no-analysis"><p>Requirements analysis not available</p></div>`;
    }

    return `
        <div class="requirements-assessment">
            <div class="requirement-item ${analysis.has_education_docs ? 'met' : 'not-met'}">
                <div class="req-icon">${analysis.has_education_docs ? '✅' : '❌'}</div>
                <div class="req-content">
                    <strong>Educational Credentials</strong>
                    <p>${analysis.has_education_docs ? 'Diploma submitted' : 'No diploma found'}</p>
                </div>
            </div>
            
            <div class="requirement-item ${analysis.has_certification_docs ? 'met' : 'not-met'}">
                <div class="req-icon">${analysis.has_certification_docs ? '✅' : '❌'}</div>
                <div class="req-content">
                    <strong>Professional Certifications</strong>
                    <p>${analysis.has_certification_docs ? 'Certificates submitted' : 'No certifications found'}</p>
                </div>
            </div>

            ${analysis.missing_documents && analysis.missing_documents.length > 0 ? `
                <div class="missing-docs-alert">
                    <strong>⚠️ Missing Documents:</strong>
                    <ul>
                        ${analysis.missing_documents.map(doc => `<li>${doc}</li>`).join('')}
                    </ul>
                </div>
            ` : ''}
        </div>
    `;
}

/**
 * Setup Phase 4 event listeners
 */
function setupPhase4Events(modal, data) {
    // Close modal events
    modal.querySelectorAll('.close-phase4-modal').forEach(btn => {
        btn.addEventListener('click', () => closePhase4Modal(modal));
    });

    // Document viewing
    modal.querySelectorAll('.view-doc-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const docPath = this.dataset.path;
            viewDocument(docPath);
        });
    });

    // Resume viewing (reuse existing function)
    const resumeBtn = modal.querySelector('.view-resume-btn');
    if (resumeBtn) {
        resumeBtn.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            window.open(`${API_BASE}view_resume.php?application_id=${applicationId}`, '_blank');
        });
    }

    // Action buttons
    const approveBtn = modal.querySelector('.approve-btn');
    const rejectBtn = modal.querySelector('.reject-btn');
    
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            console.log('Approve application:', applicationId);
            // You can connect this to your existing approval logic
            showSuccessMessage('Application approved for interview!');
            closePhase4Modal(modal);
        });
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            console.log('Reject application:', applicationId);
            // You can connect this to your existing rejection logic
            showSuccessMessage('Application rejected');
            closePhase4Modal(modal);
        });
    }
}

// ===================================================================
// PHASE 4: UTILITY FUNCTIONS - FIXED for your database
// ===================================================================

/**
 * Get document icon - FIXED for your enum values
 */
function getDocumentIcon(docType) {
    const icons = {
        'diploma': '🎓',     // Education documents
        'certificate': '📜', // Professional certifications
        'license': '🆔',     // Professional licenses  
        'other': '📁'        // Other documents
    };
    return icons[docType] || icons['other'];
}

/**
 * Format document type - FIXED for your enum values
 */
function formatDocumentType(docType) {
    const types = {
        'diploma': 'Diploma',
        'certificate': 'Certificate', 
        'license': 'License',
        'other': 'Other Document'
    };
    return types[docType] || 'Document';
}

/**
 * Get document status based on analysis
 */
function getDocumentStatus(analysis) {
    if (!analysis) return 'Unknown';
    if (analysis.overall_score >= 80) return 'Complete';
    if (analysis.overall_score >= 50) return 'Partial';
    return 'Incomplete';
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return 'Unknown size';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Format date - FIXED for your upload_date column
 */
function formatDate(dateString) {
    if (!dateString) return 'Unknown date';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short', 
        day: 'numeric'
    });
}

/**
 * View document in new tab
 */
function viewDocument(docPath) {
    if (!docPath) {
        showError('Document path not found');
        return;
    }
    const viewUrl = `../../backend/employer/view_document.php?path=${encodeURIComponent(docPath)}`;
    window.open(viewUrl, '_blank', 'width=800,height=900,scrollbars=yes,resizable=yes');
}

/**
 * Close Phase 4 modal
 */
function closePhase4Modal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
    modal.remove();
}

// ===================================================================
// PHASE 4: AUTO-ADD ENHANCED VIEW BUTTONS
// ===================================================================

/**
 * Add enhanced view buttons to existing applicant cards
 */
function addEnhancedViewButtons() {
    // Find all existing applicant cards
    const applicantCards = document.querySelectorAll('.applicant-card, .category-applicant-card, [data-application-id]');
    
    applicantCards.forEach(card => {
        // Check if enhanced button already exists
        if (card.querySelector('.enhanced-view-btn')) return;
        
        // Get application ID from various possible sources
        const applicationId = card.dataset.applicationId || 
                             card.getAttribute('data-application-id') ||
                             card.querySelector('[data-application-id]')?.dataset.applicationId ||
                             card.querySelector('.view-btn')?.dataset.applicationId;
        
        if (!applicationId) return;
        
        // Find existing action buttons area
        const actionsArea = card.querySelector('.action-buttons, .applicant-actions, .card-actions, .action-buttons-container') ||
                           card.querySelector('.view-btn')?.parentElement;
        
        if (actionsArea) {
            // Create enhanced view button
            const enhancedBtn = document.createElement('button');
            enhancedBtn.className = 'action-btn enhanced-view-btn';
            enhancedBtn.dataset.applicationId = applicationId;
            enhancedBtn.innerHTML = '<i class="fas fa-search-plus"></i> Enhanced View';
            enhancedBtn.title = 'View with documents and requirements';
            
            // Add click handler
            enhancedBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('Opening enhanced view for application:', applicationId);
                openEnhancedApplicantView(applicationId);
            });
            
            // Add to actions area
            actionsArea.appendChild(enhancedBtn);
            
            console.log('✅ Added enhanced view button to card with application ID:', applicationId);
        }
    });
}

// ===================================================================
// PHASE 4: AUTO-INITIALIZATION
// ===================================================================

// Auto-add enhanced view buttons after page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Phase 4: Setting up enhanced view buttons...');
    
    // Add buttons immediately if cards exist
    setTimeout(() => {
        addEnhancedViewButtons();
        console.log('✅ Phase 4: Enhanced view buttons added');
    }, 1000);
    
    // Also add buttons when new content is loaded (for dynamic loading)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Check if new applicant cards were added
                const hasNewCards = Array.from(mutation.addedNodes).some(node => 
                    node.nodeType === 1 && (
                        node.classList?.contains('applicant-card') ||
                        node.querySelector?.('.applicant-card') ||
                        node.dataset?.applicationId
                    )
                );
                
                if (hasNewCards) {
                    setTimeout(addEnhancedViewButtons, 500);
                }
            }
        });
    });
    
    // Observe the applicants container
    const applicantsContainer = document.querySelector('.applicants-grid, .applicants-container, #applicants-grid');
    if (applicantsContainer) {
        observer.observe(applicantsContainer, { childList: true, subtree: true });
    }
});

console.log('✅ Phase 4: Enhanced Applicant View loaded successfully');
console.log('🎯 Ready for capstone demo!');

/**
 * ===================================================================
 * END OF PHASE 4 CODE - COPY PASTE COMPLETE!
 * ===================================================================
 * 
 * USAGE:
 * 1. Copy this entire block
 * 2. Paste at the END of your empapplicants.js file
 * 3. Save the file
 * 4. Refresh your empapplicants.php page
 * 5. Look for "Enhanced View" buttons on applicant cards
 * 6. Click to test the enhanced modal!
 * 
 * Perfect for capstone presentation! 🎓🚀
 */