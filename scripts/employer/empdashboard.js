/**
 * ThisAble Employer Dashboard - Final Working Version
 */

// Global variables
let dashboardData = {};
const API_BASE = '../../backend/employer/';

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // STEP 1: Hide problematic auto-showing modals immediately
    hideProblematicModals();
    
    // STEP 2: Setup core functionality
    setupDashboard();
});

/**
 * Hide modals that auto-show and cause overlay issues
 */
function hideProblematicModals() {
    const problemModals = [
        '#scheduleInterviewModal',
        '#interviewSuccessModal'
    ];
    
    problemModals.forEach(selector => {
        const modal = document.querySelector(selector);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show', 'active', 'open');
        }
    });
    
    // Reset any modal overlays
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.classList.remove('show');
        overlay.style.display = 'none';
    });
    
    // Reset body
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
    
    console.log('Problematic modals hidden');
}

/**
 * Setup main dashboard functionality
 */
async function setupDashboard() {
    try {
        // Load dashboard data
        await loadDashboardData();
        
        // Setup event listeners
        setupEventListeners();
        
        // Setup modals
        setupModals();
        
        // Setup dropdowns
        setupDropdowns();
        
        console.log('Dashboard setup complete');
        
    } catch (error) {
        console.error('Dashboard setup error:', error);
        showNotification('Dashboard initialization failed', 'error');
    }
}

/**
 * Load dashboard data from API
 */
async function loadDashboardData() {
    try {
        showLoading();
        
        const response = await fetch(`${API_BASE}dashboard_data.php?action=dashboard_overview`);
        const result = await response.json();
        
        if (result.success) {
            dashboardData = result.data;
            renderDashboard();
        } else {
            console.error('Dashboard data error:', result.message);
            // Load sample data as fallback
            loadSampleData();
        }
    } catch (error) {
        console.error('API error:', error);
        // Load sample data as fallback
        loadSampleData();
    } finally {
        hideLoading();
    }
}

/**
 * Load sample data when API fails
 */
function loadSampleData() {
    console.log('Loading sample dashboard data...');
    
    dashboardData = {
        stats: {
            active_jobs: 1,
            total_applicants: 1,
            pwd_applicants: 1,
            upcoming_interviews: 1
        },
        recent_jobs: [
            {
                job_id: 1,
                job_title: "Dev",
                department: "Engineering",
                location: "Manila",
                status: "active",
                employment_type: "Full-time",
                applicant_count: 1,
                time_ago: "2 days ago"
            }
        ],
        recent_applicants: [
            {
                application_id: 1,
                seeker_id: 1,
                full_name: "John Doe",
                initials: "JD",
                job_title: "Dev",
                disability_name: "Visual Impairment",
                application_status: "submitted",
                time_ago: "1 day ago"
            }
        ],
        upcoming_interviews: [
            {
                interview_id: 1,
                candidate_name: "This Able",
                job_title: "Dev",
                interview_type: "online",
                formatted_date: "June 2, 2025",
                formatted_time: "10:00 AM",
                interview_platform: "Zoom",
                has_accommodations: false
            }
        ],
        recent_notifications: []
    };
    
    renderDashboard();
    showNotification('Loaded sample data - API will be connected in production', 'info');
}

/**
 * Render dashboard with data
 */
function renderDashboard() {
    updateStats();
    updateRecentJobs();
    updateRecentApplicants();
    updateUpcomingInterviews();
    updateRecentNotifications();
}

/**
 * Update stats cards
 */
function updateStats() {
    const stats = dashboardData.stats;
    if (!stats) return;
    
    const statValues = document.querySelectorAll('.stat-value');
    if (statValues[0]) statValues[0].textContent = stats.active_jobs;
    if (statValues[1]) statValues[1].textContent = stats.total_applicants;
    if (statValues[2]) statValues[2].textContent = stats.pwd_applicants;
    if (statValues[3]) statValues[3].textContent = stats.upcoming_interviews;
}

/**
 * Update recent jobs list
 */
function updateRecentJobs() {
    const jobsList = document.querySelector('.job-list');
    const jobs = dashboardData.recent_jobs || [];
    
    if (!jobsList) return;
    
    if (jobs.length === 0) {
        jobsList.innerHTML = '<li class="no-data">No job posts found.</li>';
        return;
    }
    
    jobsList.innerHTML = jobs.map(job => `
        <li class="job-item" data-job-id="${job.job_id}">
            <div class="job-header">
                <div>
                    <div class="job-title">${escapeHtml(job.job_title)}</div>
                    <div class="job-company">${escapeHtml(job.department)}</div>
                </div>
                <span class="job-status ${job.status}">${capitalizeFirst(job.status)}</span>
            </div>
            <div class="job-footer">
                <div class="job-meta">
                    <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(job.location)}</span>
                    <span><i class="fas fa-user"></i> ${job.applicant_count} Applicant${job.applicant_count !== 1 ? 's' : ''}</span>
                </div>
                <div class="job-actions">
                    <button title="View Details" onclick="viewJobDetails(${job.job_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button title="Edit" onclick="editJob(${job.job_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </li>
    `).join('');
}

/**
 * Update recent applicants list
 */
function updateRecentApplicants() {
    const applicantsList = document.querySelector('.applicant-list');
    const applicants = dashboardData.recent_applicants || [];
    
    if (!applicantsList) return;
    
    if (applicants.length === 0) {
        applicantsList.innerHTML = '<li class="no-data">No applications received yet.</li>';
        return;
    }
    
    applicantsList.innerHTML = applicants.map(applicant => `
        <li class="applicant-item" data-application-id="${applicant.application_id}">
            <div class="applicant-avatar">${applicant.initials}</div>
            <div class="applicant-info">
                <div class="applicant-name">
                    ${escapeHtml(applicant.full_name)}
                    <i class="fas fa-universal-access accessibility-icon" 
                       data-tooltip="${escapeHtml(applicant.disability_name)}"></i>
                </div>
                <div class="applicant-position">Applied for ${escapeHtml(applicant.job_title)}</div>
            </div>
            <div class="applicant-date">${applicant.time_ago}</div>
            <div class="applicant-actions">
                <button title="View Profile" onclick="viewApplicantProfile(${applicant.application_id})">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </li>
    `).join('');
}

/**
 * Update upcoming interviews list
 */
function updateUpcomingInterviews() {
    const interviewsList = document.querySelector('.interview-list');
    const interviews = dashboardData.upcoming_interviews || [];
    
    if (!interviewsList) return;
    
    if (interviews.length === 0) {
        interviewsList.innerHTML = '<li class="no-data">No upcoming interviews scheduled.</li>';
        return;
    }
    
    interviewsList.innerHTML = interviews.map(interview => `
        <li class="interview-item" data-interview-id="${interview.interview_id}">
            <div class="interview-header">
                <div class="interview-name">
                    ${escapeHtml(interview.candidate_name)}
                    ${interview.has_accommodations ? 
                        `<i class="fas fa-universal-access accessibility-icon" 
                           data-tooltip="Accommodations needed"></i>` : ''}
                </div>
                <span class="interview-type ${interview.interview_type}">${capitalizeFirst(interview.interview_type)}</span>
            </div>
            <div class="interview-position">${escapeHtml(interview.job_title)}</div>
            <div class="interview-details">
                <div><i class="fas fa-calendar-alt"></i> ${interview.formatted_date}</div>
                <div><i class="fas fa-clock"></i> ${interview.formatted_time}</div>
                <div><i class="fas fa-${interview.interview_type === 'online' ? 'video' : 'map-marker-alt'}"></i> 
                     ${escapeHtml(interview.interview_platform || 'TBD')}</div>
            </div>
            <div class="interview-actions">
                <button class="btn btn-outline" onclick="rescheduleInterview(${interview.interview_id})">Reschedule</button>
                <button class="btn btn-primary" onclick="viewInterviewDetails(${interview.interview_id})">View Details</button>
            </div>
        </li>
    `).join('');
}

/**
 * Update recent notifications list
 */
function updateRecentNotifications() {
    const notificationsList = document.querySelector('.notification-list');
    const notifications = dashboardData.recent_notifications || [];
    
    if (!notificationsList) return;
    
    if (notifications.length === 0) {
        notificationsList.innerHTML = '<li class="no-data">No recent notifications.</li>';
        return;
    }
    
    notificationsList.innerHTML = notifications.slice(0, 5).map(notification => `
        <li class="notification-item ${notification.is_read ? '' : 'unread'}" 
            data-notification-id="${notification.notification_id}">
            <div class="notification-icon">
                <i class="${notification.icon_class}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-message">${escapeHtml(notification.message)}</div>
                <div class="notification-time">${notification.time_ago}</div>
            </div>
        </li>
    `).join('');
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Sidebar toggle
    const toggleBtn = document.getElementById('toggle-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // Search functionality
    const searchInput = document.getElementById('dashboard-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(performSearch, 300));
    }
}

/**
 * Setup dropdown functionality
 */
function setupDropdowns() {
    // Setup all filter dropdown toggles
    const filterToggles = [
        { toggleId: 'job-filter-toggle', dropdownId: 'job-filter-dropdown' },
        { toggleId: 'applicant-filter-toggle', dropdownId: 'applicant-filter-dropdown' },
        { toggleId: 'interview-filter-toggle', dropdownId: 'interview-filter-dropdown' },
        { toggleId: 'notifications-filter-toggle', dropdownId: 'notifications-filter-dropdown' }
    ];
    
    filterToggles.forEach(({ toggleId, dropdownId }) => {
        const toggle = document.getElementById(toggleId);
        const dropdown = document.getElementById(dropdownId);
        
        if (toggle && dropdown) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close all other dropdowns
                filterToggles.forEach(({ dropdownId: otherId }) => {
                    if (otherId !== dropdownId) {
                        const otherDropdown = document.getElementById(otherId);
                        if (otherDropdown) {
                            otherDropdown.classList.remove('show');
                        }
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('show');
                console.log('Dropdown toggled:', dropdownId, dropdown.classList.contains('show'));
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const clickedElement = e.target;
        const isDropdownToggle = clickedElement.closest('[id$="-filter-toggle"]');
        
        if (!isDropdownToggle) {
            // Close all dropdowns
            filterToggles.forEach(({ dropdownId }) => {
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            });
        }
    });
    
    // Setup filter actions
    document.querySelectorAll('.dropdown-menu a[data-filter]').forEach(filterLink => {
        filterLink.addEventListener('click', function(e) {
            e.preventDefault();
            const filterType = this.getAttribute('data-filter');
            const dropdown = this.closest('.dropdown-menu');
            const section = dropdown.id.replace('-filter-dropdown', '');
            
            console.log('Filter applied:', section, filterType);
            applyFilter(section, filterType);
            
            // Close dropdown
            dropdown.classList.remove('show');
        });
    });
    
    console.log('Dropdowns setup complete');
}

/**
 * Apply filter to dashboard sections
 */
function applyFilter(section, filterType) {
    console.log('Applying filter:', section, filterType);
    
    let items = [];
    
    switch(section) {
        case 'job':
            items = document.querySelectorAll('.job-item');
            break;
        case 'applicant':
            items = document.querySelectorAll('.applicant-item');
            break;
        case 'interview':
            items = document.querySelectorAll('.interview-item');
            break;
        case 'notifications':
            items = document.querySelectorAll('.notification-item');
            break;
    }
    
    items.forEach(item => {
        if (filterType === 'all') {
            item.style.display = '';
            return;
        }
        
        let shouldShow = false;
        
        switch(section) {
            case 'job':
                const statusElement = item.querySelector('.job-status');
                if (statusElement) {
                    shouldShow = statusElement.classList.contains(filterType);
                }
                break;
                
            case 'applicant':
                if (filterType === 'pwd') {
                    shouldShow = item.querySelector('.accessibility-icon') !== null;
                } else if (filterType === 'recent') {
                    const dateText = item.querySelector('.applicant-date')?.textContent || '';
                    shouldShow = dateText.includes('hour') || dateText.includes('minute') || dateText.includes('day');
                }
                break;
                
            case 'interview':
                const typeElement = item.querySelector('.interview-type');
                if (typeElement) {
                    shouldShow = typeElement.classList.contains(filterType) || 
                               typeElement.textContent.toLowerCase().includes(filterType);
                }
                break;
                
            case 'notifications':
                if (filterType === 'unread') {
                    shouldShow = item.classList.contains('unread');
                } else if (filterType === 'applicants') {
                    shouldShow = item.textContent.toLowerCase().includes('applicant') || 
                               item.textContent.toLowerCase().includes('application');
                } else if (filterType === 'interviews') {
                    shouldShow = item.textContent.toLowerCase().includes('interview');
                }
                break;
        }
        
        item.style.display = shouldShow ? '' : 'none';
    });
    
    showNotification(`${capitalizeFirst(section)} filtered by: ${capitalizeFirst(filterType)}`, 'info');
}

/**
 * Setup modal functionality
 */
function setupModals() {
    // Post job modal
    setupPostJobModal();
    
    // Other modals
    setupOtherModals();
}

/**
 * Setup post job modal
 */
function setupPostJobModal() {
    const postJobBtn = document.getElementById('post-job-btn');
    const postJobModal = document.getElementById('post-job-modal');
    
    if (postJobBtn && postJobModal) {
        postJobBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showModal('post-job-modal');
        });
        
        // Close buttons
        const closeButtons = postJobModal.querySelectorAll('#close-post-job, #cancel-post-job');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => hideModal('post-job-modal'));
        });
        
        // Submit button
        const submitBtn = document.getElementById('submit-post-job');
        if (submitBtn) {
            submitBtn.addEventListener('click', handlePostJob);
        }
    }
}

/**
 * Setup other modals
 */
function setupOtherModals() {
    // View job modal
    const viewJobModal = document.getElementById('view-job-modal');
    if (viewJobModal) {
        const closeButtons = viewJobModal.querySelectorAll('#close-view-job, #close-job-details');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => hideModal('view-job-modal'));
        });
    }
    
    // View applicant modal
    const viewApplicantModal = document.getElementById('view-applicant-modal');
    if (viewApplicantModal) {
        const closeButtons = viewApplicantModal.querySelectorAll('#close-view-applicant, #close-applicant-profile');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => hideModal('view-applicant-modal'));
        });
    }
    
    // View interview modal
    const viewInterviewModal = document.getElementById('view-interview-modal');
    if (viewInterviewModal) {
        const closeButtons = viewInterviewModal.querySelectorAll('#close-view-interview, #cancel-view-interview');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => hideModal('view-interview-modal'));
        });
    }
    
    // Reschedule interview modal
    const rescheduleInterviewModal = document.getElementById('reschedule-interview-modal');
    if (rescheduleInterviewModal) {
        const closeButtons = rescheduleInterviewModal.querySelectorAll('#close-reschedule-interview, #cancel-reschedule-interview');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => hideModal('reschedule-interview-modal'));
        });
    }
    
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            hideModal(e.target.id);
        }
    });
}

/**
 * Show modal
 */
function showModal(modalId) {
    console.log('Showing modal:', modalId);
    
    // Hide all other modals first
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        if (modal.id !== modalId) {
            modal.classList.remove('show');
            modal.style.display = 'none';
        }
    });
    
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error('Modal not found:', modalId);
        return;
    }
    
    // Show the modal with force
    modal.style.display = 'flex';
    modal.style.position = 'fixed';
    modal.style.top = '0';
    modal.style.left = '0';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.background = 'rgba(0, 0, 0, 0.5)';
    modal.style.zIndex = '9999';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    modal.style.opacity = '1';
    modal.style.visibility = 'visible';
    
    modal.classList.add('show');
    
    // Ensure modal content is visible
    const modalContent = modal.querySelector('.modal');
    if (modalContent) {
        modalContent.style.background = 'white';
        modalContent.style.borderRadius = '10px';
        modalContent.style.width = '90%';
        modalContent.style.maxWidth = '800px';
        modalContent.style.maxHeight = '90vh';
        modalContent.style.overflowY = 'auto';
        modalContent.style.display = 'block';
        modalContent.style.opacity = '1';
        modalContent.style.visibility = 'visible';
    }
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    document.body.classList.add('modal-open');
}

/**
 * Hide modal
 */
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        modal.style.opacity = '0';
        modal.style.visibility = 'hidden';
        
        // Restore body scroll
        document.body.style.overflow = '';
        document.body.classList.remove('modal-open');
    }
}

/**
 * Handle post job form submission
 */
async function handlePostJob() {
    const form = document.getElementById('post-job-form');
    if (!form) {
        showNotification('Job posting form not found', 'error');
        return;
    }
    
    // Get form data
    const formData = {
        job_title: document.getElementById('job-title')?.value?.trim(),
        department: document.getElementById('job-department')?.value,
        location: document.getElementById('job-location')?.value?.trim(),
        employment_type: document.getElementById('job-type')?.value,
        job_description: document.getElementById('job-description')?.value?.trim(),
        job_requirements: document.getElementById('job-requirements')?.value?.trim(),
        salary_range: document.getElementById('job-salary')?.value?.trim(),
        application_deadline: document.getElementById('job-deadline')?.value
    };
    
    // Basic validation
    const required = ['job_title', 'department', 'location', 'employment_type', 'job_description', 'job_requirements'];
    const missing = required.filter(field => !formData[field]);
    
    if (missing.length > 0) {
        showNotification(`Please fill in: ${missing.join(', ')}`, 'error');
        return;
    }
    
    // Get employer ID
    const employerId = getEmployerId();
    if (!employerId) {
        showNotification('Unable to identify employer. Please refresh and try again.', 'error');
        return;
    }
    
    // Prepare job data
    const jobData = {
        employer_id: employerId,
        ...formData
    };
    
    try {
        showLoading();
        
        const response = await fetch(`${API_BASE}../../backend/shared/job_system.php?action=create_job`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(jobData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            hideModal('post-job-modal');
            form.reset();
            showNotification('Job posted successfully! 🎉', 'success');
            
            // Refresh dashboard
            setTimeout(loadDashboardData, 1000);
            
        } else {
            showNotification(result.message || 'Failed to post job', 'error');
        }
    } catch (error) {
        console.error('Post job error:', error);
        showNotification('Error posting job. Please try again.', 'error');
    } finally {
        hideLoading();
    }
}

/**
 * View job details - Uses existing styled modal
 */
window.viewJobDetails = function(jobId) {
    console.log('Viewing job details for ID:', jobId);
    
    // Sample job details
    const sampleData = {
        job_id: jobId,
        job_title: "Sample Job Title",
        job_status: "active",
        department: "Engineering",
        location: "Manila, Philippines",
        employment_type: "Full-time",
        salary_range: "₱40,000 - ₱60,000",
        job_description: "This is a sample job description. In production, this will load actual job data from the database.",
        job_requirements: "• Bachelor's degree in related field\n• 2+ years of experience\n• Strong communication skills\n• Proficiency in relevant technologies",
        total_applications: 3
    };
    
    displayJobDetails(sampleData);
    showModal('view-job-modal');
};

/**
 * Display job details in existing styled modal
 */
function displayJobDetails(job) {
    const jobDetailsContent = document.getElementById('job-details-content');
    if (!jobDetailsContent) return;
    
    jobDetailsContent.innerHTML = `
        <div class="job-details">
            <div class="job-details-header">
                <div class="job-details-title">${escapeHtml(job.job_title)}</div>
                <span class="job-details-status ${job.job_status}">${capitalizeFirst(job.job_status)}</span>
            </div>
            <div class="job-details-info">
                <div class="job-detail-item">
                    <i class="fas fa-building"></i>
                    <span>${escapeHtml(job.department)}</span>
                </div>
                <div class="job-detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>${escapeHtml(job.location)}</span>
                </div>
                <div class="job-detail-item">
                    <i class="fas fa-user"></i>
                    <span>${job.total_applications || 0} Applications</span>
                </div>
                <div class="job-detail-item">
                    <i class="fas fa-clock"></i>
                    <span>${escapeHtml(job.employment_type)}</span>
                </div>
                ${job.salary_range ? `
                <div class="job-detail-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>${escapeHtml(job.salary_range)}</span>
                </div>
                ` : ''}
            </div>
            <div class="job-description">
                <div class="job-description-title">Job Description</div>
                <div class="job-description-content">
                    ${escapeHtml(job.job_description).replace(/\n/g, '<br>')}
                </div>
            </div>
            <div class="job-requirements">
                <div class="job-requirements-title">Requirements</div>
                <div class="job-requirements-content">
                    ${escapeHtml(job.job_requirements).replace(/\n/g, '<br>')}
                </div>
            </div>
        </div>
    `;
}

/**
 * View interview details - Uses existing styled modal
 */
window.viewInterviewDetails = function(interviewId) {
    console.log('Viewing interview details for ID:', interviewId);
    
    // Sample interview data
    const sampleInterview = {
        interview_id: interviewId,
        candidate_name: "This Able",
        job_title: "Dev",
        interview_type: "online",
        scheduled_date: "2025-06-02",
        scheduled_time: "10:00:00",
        duration_minutes: 60,
        interview_platform: "Zoom",
        meeting_link: "https://zoom.us/j/1234567890",
        interview_status: "scheduled",
        disability_name: "Visual Impairment",
        accommodations_needed: "Screen reader compatible materials needed",
        interviewer_notes: "First interview for this position. Candidate seems promising based on application."
    };
    
    displayInterviewDetails(sampleInterview);
    showModal('view-interview-modal');
};

/**
 * Display interview details in existing styled modal
 */
function displayInterviewDetails(interview) {
    const interviewDetailsContent = document.getElementById('interview-details-content');
    if (!interviewDetailsContent) {
        console.error('interview-details-content element not found');
        return;
    }
    
    const formattedDate = new Date(interview.scheduled_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long', 
        day: 'numeric'
    });
    
    const formattedTime = new Date(`1970-01-01T${interview.scheduled_time}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    interviewDetailsContent.innerHTML = `
        <div class="interview-details-content">
            <div class="interview-details-header">
                <div class="interview-details-title">
                    <h3>${escapeHtml(interview.candidate_name)}</h3>
                    <span class="interview-type ${interview.interview_type}">${capitalizeFirst(interview.interview_type)}</span>
                </div>
                <div class="interview-candidate-info">
                    <div class="interview-candidate-avatar">${getInitials(interview.candidate_name)}</div>
                    <div>
                        <div class="interview-candidate-name">${escapeHtml(interview.candidate_name)}</div>
                        <div class="interview-candidate-position">Interview for ${escapeHtml(interview.job_title)}</div>
                        ${interview.disability_name ? `
                        <div class="disability-info">
                            <i class="fas fa-universal-access"></i>
                            <span>${escapeHtml(interview.disability_name)}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="interview-details-info">
                <div class="interview-detail-item">
                    <i class="fas fa-calendar"></i>
                    <div class="interview-detail-label">Date:</div>
                    <div class="interview-detail-value">${formattedDate}</div>
                </div>
                <div class="interview-detail-item">
                    <i class="fas fa-clock"></i>
                    <div class="interview-detail-label">Time:</div>
                    <div class="interview-detail-value">${formattedTime}</div>
                </div>
                <div class="interview-detail-item">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="interview-detail-label">Duration:</div>
                    <div class="interview-detail-value">${interview.duration_minutes} minutes</div>
                </div>
                <div class="interview-detail-item">
                    <i class="fas fa-tasks"></i>
                    <div class="interview-detail-label">Status:</div>
                    <div class="interview-detail-value">${capitalizeFirst(interview.interview_status)}</div>
                </div>
                
                ${interview.interview_type === 'online' && interview.meeting_link ? `
                <div class="interview-detail-item">
                    <i class="fas fa-video"></i>
                    <div class="interview-detail-label">Meeting Link:</div>
                    <div class="interview-detail-value">
                        <a href="${escapeHtml(interview.meeting_link)}" target="_blank" class="meeting-link">
                            Join ${interview.interview_platform || 'Meeting'}
                        </a>
                    </div>
                </div>
                ` : ''}
            </div>
            
            ${interview.accommodations_needed ? `
            <div class="interview-accommodations">
                <h4><i class="fas fa-universal-access"></i> Accessibility Accommodations</h4>
                <p>${escapeHtml(interview.accommodations_needed)}</p>
            </div>
            ` : ''}
            
            ${interview.interviewer_notes ? `
            <div class="interview-notes">
                <h4><i class="fas fa-sticky-note"></i> Interview Notes</h4>
                <div class="interview-notes-content">
                    ${escapeHtml(interview.interviewer_notes).replace(/\n/g, '<br>')}
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

/**
 * Reschedule interview - Uses existing styled modal
 */
window.rescheduleInterview = function(interviewId) {
    console.log('Rescheduling interview for ID:', interviewId);
    
    // Populate reschedule modal with current interview data
    populateRescheduleModal(interviewId);
    showModal('reschedule-interview-modal');
};

/**
 * Populate reschedule modal with interview data
 */
function populateRescheduleModal(interviewId) {
    // For now, just set up the basic reschedule form
    // In production, this would load the actual interview data
    const rescheduleForm = document.getElementById('reschedule-interview-form');
    if (rescheduleForm) {
        // Set today as minimum date
        const dateInput = rescheduleForm.querySelector('input[type="date"]');
        if (dateInput) {
            dateInput.min = new Date().toISOString().split('T')[0];
        }
        
        // Store interview ID for submission
        rescheduleForm.setAttribute('data-interview-id', interviewId);
    }
    
    // Setup submit handler for reschedule
    const confirmBtn = document.getElementById('confirm-reschedule-interview');
    if (confirmBtn) {
        confirmBtn.onclick = function() {
            handleRescheduleSubmit(interviewId);
        };
    }
}

/**
 * Handle reschedule form submission
 */
function handleRescheduleSubmit(interviewId) {
    const form = document.getElementById('reschedule-interview-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const newDate = formData.get('scheduled_date');
    const newTime = formData.get('scheduled_time');
    
    if (!newDate || !newTime) {
        showNotification('Please select both date and time', 'error');
        return;
    }
    
    // Close modal
    hideModal('reschedule-interview-modal');
    
    // Show success message
    const formattedDate = new Date(newDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    const formattedTime = new Date(`1970-01-01T${newTime}`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    showNotification(`Interview rescheduled to ${formattedDate} at ${formattedTime}`, 'success');
    
    console.log('Interview rescheduled:', {
        interviewId,
        newDate,
        newTime
    });
}

/**
 * Other action functions
 */
window.editJob = function(jobId) {
    showNotification('Edit job functionality will be implemented soon', 'info');
};

window.viewApplicantProfile = function(applicationId) {
    showNotification('View applicant profile functionality will be implemented soon', 'info');
};

/**
 * Sidebar toggle
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (sidebar && mainContent && toggleIcon) {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('fa-chevron-left');
            toggleIcon.classList.add('fa-chevron-right');
        } else {
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-chevron-left');
        }
    }
}

/**
 * Search functionality
 */
function performSearch() {
    const searchTerm = document.getElementById('dashboard-search-input').value.trim();
    console.log('Searching for:', searchTerm);
    
    if (searchTerm.length < 2) {
        renderDashboard(); // Reset to show all
        return;
    }
    
    // Filter current data
    const searchRegex = new RegExp(searchTerm, 'i');
    
    // Filter jobs
    const filteredJobs = dashboardData.recent_jobs.filter(job => 
        searchRegex.test(job.job_title) || 
        searchRegex.test(job.department) || 
        searchRegex.test(job.location)
    );
    
    // Filter applicants
    const filteredApplicants = dashboardData.recent_applicants.filter(applicant => 
        searchRegex.test(applicant.full_name) || 
        searchRegex.test(applicant.job_title)
    );
    
    // Update lists with filtered data
    const originalJobs = dashboardData.recent_jobs;
    const originalApplicants = dashboardData.recent_applicants;
    
    dashboardData.recent_jobs = filteredJobs;
    dashboardData.recent_applicants = filteredApplicants;
    
    updateRecentJobs();
    updateRecentApplicants();
    
    // Restore original data
    dashboardData.recent_jobs = originalJobs;
    dashboardData.recent_applicants = originalApplicants;
}

/**
 * Utility functions
 */
function getEmployerId() {
    // Try to get from session helper
    if (window.getCurrentEmployerId) {
        return window.getCurrentEmployerId();
    }
    
    // Fallback - check if session data is available
    if (window.employerSession && window.employerSession.employer_id) {
        return window.employerSession.employer_id;
    }
    
    console.warn('Employer ID not found');
    return null;
}

function getInitials(name) {
    if (!name) return 'NA';
    return name.split(' ')
             .map(word => word.charAt(0))
             .join('')
             .toUpperCase()
             .slice(0, 2);
}

function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'dashboard-loading';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    loading.innerHTML = `
        <div style="text-align: center;">
            <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #257180; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 15px;"></div>
            <p style="color: #666; margin: 0;">Loading...</p>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('dashboard-loading');
    if (loading) loading.remove();
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.dashboard-notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `dashboard-notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 8px;
        padding: 16px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10001;
        min-width: 300px;
        max-width: 400px;
        border-left: 4px solid ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: ${type === 'success' ? '#2e7d32' : type === 'error' ? '#c62828' : '#1565c0'};
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${escapeHtml(message)}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: currentColor; cursor: pointer; opacity: 0.7; padding: 4px; border-radius: 4px; margin-left: auto;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).replace('_', ' ');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Add CSS animation for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

console.log('Complete dashboard script loaded successfully');

// Debug helper
window.debugDashboard = function() {
    console.log('Dashboard debug info:');
    console.log('Dashboard data:', dashboardData);
    console.log('Employer ID:', getEmployerId());
    console.log('Available modals:', Array.from(document.querySelectorAll('[id*="modal"]')).map(m => m.id));
};