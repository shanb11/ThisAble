// Global variables for applications
let allApplications = [];
let filteredApplications = [];
let currentFilter = 'all';
let searchQuery = '';
let isLoading = false;
let applicationStats = {};

// DOM elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('Applications page initialized');
    
    // Initialize sidebar toggle
    initializeSidebar();
    
    // Load application statistics
    loadApplicationStats();
    
    // Load applications
    loadApplications();
    
    // Initialize filters and search
    initializeFilters();
    
    // Initialize modal
    initializeModal();
    
    // Initialize accessibility features
    initializeAccessibilityFeatures();
});

// Initialize sidebar toggle
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

    if (toggleBtn && toggleIcon) {
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
}

// Load application statistics from backend
async function loadApplicationStats() {
    try {
        console.log('Loading application statistics...');
        
        const response = await fetch('../../backend/candidate/get_application_stats.php');
        const data = await response.json();
        
        if (data.success) {
            applicationStats = data.stats;
            updateStatsDisplay();
            console.log('Stats loaded:', applicationStats);
        } else {
            console.error('Failed to load stats:', data.error);
            showError('Failed to load application statistics');
        }
    } catch (error) {
        console.error('Error loading stats:', error);
        showError('Failed to connect to stats service');
    }
}

// Update stats display
function updateStatsDisplay() {
    const statsCards = document.querySelectorAll('.stat-card');
    
    if (statsCards.length >= 4 && applicationStats.cards) {
        // Remove loading state from all cards
        statsCards.forEach(card => {
            card.classList.remove('loading', 'error');
        });
        
        // Update each stat card
        applicationStats.cards.forEach((card, index) => {
            if (statsCards[index]) {
                const numberEl = statsCards[index].querySelector('.number');
                const labelEl = statsCards[index].querySelector('.label');
                const iconEl = statsCards[index].querySelector('.icon i');
                
                if (numberEl) numberEl.textContent = card.number;
                if (labelEl) labelEl.textContent = card.label;
                if (iconEl) iconEl.className = card.icon;
                
                // Add trend indicator if available
                if (card.trend && card.trend !== '') {
                    let trendEl = statsCards[index].querySelector('.trend');
                    if (!trendEl) {
                        trendEl = document.createElement('div');
                        trendEl.className = 'trend';
                        statsCards[index].appendChild(trendEl);
                    }
                    trendEl.textContent = card.trend;
                    trendEl.className = `trend ${card.trend_positive ? 'positive' : 'negative'}`;
                    trendEl.style.display = 'block';
                }
                
                // Add percentage if available
                if (card.percentage !== undefined) {
                    let percentEl = statsCards[index].querySelector('.percentage');
                    if (!percentEl) {
                        percentEl = document.createElement('div');
                        percentEl.className = 'percentage';
                        statsCards[index].appendChild(percentEl);
                    }
                    percentEl.textContent = `${card.percentage}% response rate`;
                    percentEl.style.display = 'block';
                }
                
                // Add upcoming count for interviews
                if (index === 2 && card.upcoming !== undefined && card.upcoming > 0) {
                    let upcomingEl = statsCards[index].querySelector('.upcoming');
                    if (!upcomingEl) {
                        upcomingEl = document.createElement('div');
                        upcomingEl.className = 'upcoming';
                        statsCards[index].appendChild(upcomingEl);
                    }
                    upcomingEl.textContent = `${card.upcoming} upcoming`;
                    upcomingEl.style.display = 'block';
                }
            }
        });
        
        console.log('Stats display updated successfully');
    }
}

// Load applications from backend
async function loadApplications(filters = {}) {
    if (isLoading) return;
    
    isLoading = true;
    showLoadingState();
    
    try {
        console.log('Loading applications with filters:', filters);
        
        // Build query parameters
        const params = new URLSearchParams({
            status: filters.status || currentFilter,
            search: filters.search || searchQuery,
            limit: 50,
            offset: 0
        });
        
        // Add date filters if provided
        if (filters.date_from) params.append('date_from', filters.date_from);
        if (filters.date_to) params.append('date_to', filters.date_to);
        
        const response = await fetch(`../../backend/candidate/get_applications.php?${params}`);
        const data = await response.json();
        
        console.log('Applications response:', data);
        
        if (data.success) {
            allApplications = data.applications;
            filteredApplications = [...allApplications];
            renderApplications();
            updateApplicationCount(data.total);
        } else {
            console.error('Failed to load applications:', data.error);
            showError('Failed to load applications: ' + data.error);
            showEmptyState();
        }
    } catch (error) {
        console.error('Error loading applications:', error);
        showError('Failed to connect to applications service');
        showEmptyState();
    } finally {
        isLoading = false;
        hideLoadingState();
    }
}

// Show loading state
function showLoadingState() {
    const applicationList = document.getElementById('application-list');
    if (applicationList) {
        applicationList.innerHTML = `
            <div class="loading-state">
                <div class="loading-spinner"></div>
                <p>Loading your applications...</p>
            </div>
        `;
    }
}

// Hide loading state
function hideLoadingState() {
    const loadingState = document.querySelector('.loading-state');
    if (loadingState) {
        loadingState.remove();
    }
}

// Show empty state
function showEmptyState() {
    const applicationList = document.getElementById('application-list');
    if (applicationList) {
        applicationList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No applications found</h3>
                <p>You haven't applied for any jobs yet, or no applications match your current filters.</p>
                <a href="joblistings.php" class="btn-primary">Browse Jobs</a>
            </div>
        `;
    }
}

// Render applications
function renderApplications() {
    const applicationList = document.getElementById('application-list');
    
    if (!applicationList) {
        console.error('Application list container not found');
        return;
    }
    
    if (filteredApplications.length === 0) {
        showEmptyState();
        return;
    }
    
    applicationList.innerHTML = '';
    
    filteredApplications.forEach(application => {
        const applicationItem = createApplicationItem(application);
        applicationList.appendChild(applicationItem);
    });
}

// Create application item element
function createApplicationItem(application) {
    const item = document.createElement('div');
    item.className = 'application-item';
    item.setAttribute('data-application-id', application.id);
    
    // Generate progress steps HTML
    const progressSteps = generateProgressSteps(application.progress);
    
    // Generate progress labels
    const progressLabels = `
        <div class="progress-labels">
            <div class="progress-label">Applied</div>
            <div class="progress-label">Reviewed</div>
            <div class="progress-label">Interview</div>
            <div class="progress-label">Assessment</div>
            <div class="progress-label">Offer</div>
        </div>
    `;
    
    // Format salary display
    const salaryDisplay = application.salary ? 
        `<span><i class="fas fa-money-bill-wave"></i> ${application.salary}</span>` : '';
    
    // Interview badge
    const interviewBadge = application.interview ? 
        `<div class="interview-badge">
            <i class="fas fa-calendar-alt"></i>
            Interview: ${formatInterviewDate(application.interview.date, application.interview.time)}
        </div>` : '';
    
    item.innerHTML = `
        <div class="company-logo">${application.logo}</div>
        <div class="application-content">
            <div class="job-title">${escapeHtml(application.jobTitle)}</div>
            <div class="company-name">${escapeHtml(application.company)}</div>
            <div class="application-details">
                <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(application.location)}</span>
                <span><i class="fas fa-briefcase"></i> ${escapeHtml(application.type)}</span>
                ${salaryDisplay}
            </div>
            ${interviewBadge}
            <div class="progress-container">
                <div class="progress-steps">
                    ${progressSteps}
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${application.progress}%"></div>
                </div>
                ${progressLabels}
            </div>
        </div>
        <div class="application-actions">
            <div class="application-status status-${application.status}">${getStatusLabel(application.status)}</div>
            <div class="application-date">Applied: ${application.dateApplied}</div>
            <button class="view-btn" data-id="${application.id}">View Details</button>
        </div>
    `;
    
    // Add click event to view button
    const viewBtn = item.querySelector('.view-btn');
    viewBtn.addEventListener('click', () => openApplicationDetails(application.id));
    
    return item;
}

// Generate progress steps HTML
function generateProgressSteps(progress) {
    const steps = [20, 40, 60, 80, 100];
    return steps.map(step => 
        `<div class="progress-step ${progress >= step ? 'active' : ''}"></div>`
    ).join('');
}

// Format interview date
function formatInterviewDate(date, time) {
    try {
        const dateTime = new Date(date + ' ' + time);
        return dateTime.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    } catch (error) {
        return 'TBD';
    }
}

// Get status label
function getStatusLabel(status) {
    const statusLabels = {
        'applied': 'Applied',
        'reviewed': 'Reviewed',
        'interview': 'Interview',
        'offered': 'Offered',
        'rejected': 'Rejected',
        'withdrawn': 'Withdrawn'  // ✅ ADD THIS LINE

    };
    return statusLabels[status] || 'Unknown';
}

// Initialize filters
function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.querySelector('.search-applications input');
    
    // Filter button event listeners
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Update current filter
            currentFilter = button.getAttribute('data-filter');
            
            // Reload applications with new filter
            loadApplications({ status: currentFilter, search: searchQuery });
        });
    });
    
    // Search input event listener
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = this.value.trim();
                loadApplications({ status: currentFilter, search: searchQuery });
            }, 500); // Debounce search
        });
    }
}

// Open application details modal
async function openApplicationDetails(applicationId) {
    console.log('Opening application details for ID:', applicationId);
    
    try {
        // Show loading in modal
        showModalLoading();
        
        const response = await fetch(`../../backend/candidate/get_application_details.php?application_id=${applicationId}`);
        const data = await response.json();
        
        console.log('Application details response:', data);
        
        if (data.success) {
            displayApplicationDetails(data.application);
        } else {
            console.error('Failed to load application details:', data.error);
            showError('Failed to load application details: ' + data.error);
        }
    } catch (error) {
        console.error('Error loading application details:', error);
        showError('Failed to connect to application details service');
    }
}

// Show modal loading state
function showModalLoading() {
    const modal = document.getElementById('application-modal');
    const modalBody = document.getElementById('modal-body');
    
    if (modal && modalBody) {
        modalBody.innerHTML = `
            <div class="modal-loading">
                <div class="loading-spinner"></div>
                <p>Loading application details...</p>
            </div>
        `;
        modal.style.display = 'block';
    }
}

// Display application details in modal
function displayApplicationDetails(application) {
    const modalBody = document.getElementById('modal-body');
    
    if (!modalBody) {
        console.error('Modal body not found');
        return;
    }
    
    // Generate timeline HTML
    const timelineHTML = application.details.timeline.map(event => `
        <div class="timeline-item">
            <div class="timeline-point">
                <i class="fas ${event.icon}"></i>
            </div>
            <div class="timeline-date">${event.date}</div>
            <div class="timeline-content">
                <strong>${escapeHtml(event.event)}</strong>
                <p>${escapeHtml(event.description)}</p>
                ${event.interview_details ? generateInterviewDetails(event.interview_details) : ''}
                ${event.feedback ? generateFeedbackDetails(event.feedback) : ''}
            </div>
        </div>
    `).join('');
    
    // Generate action buttons
    const actionButtons = generateActionButtons(application);
    
    modalBody.innerHTML = `
        <div class="application-detail-header">
            <div class="company-logo">${application.logo}</div>
            <div class="application-detail-content">
                <div class="job-title">${escapeHtml(application.jobTitle)}</div>
                <div class="company-name">${escapeHtml(application.company)}</div>
                <div class="application-details">
                    <span><i class="fas fa-map-marker-alt"></i> ${escapeHtml(application.location)}</span>
                    <span><i class="fas fa-briefcase"></i> ${escapeHtml(application.type)}</span>
                    ${application.salary ? `<span><i class="fas fa-money-bill-wave"></i> ${escapeHtml(application.salary)}</span>` : ''}
                </div>
                <div class="application-status status-${application.status}">${getStatusLabel(application.status)}</div>
            </div>
        </div>
        
        <div class="detail-progress">
            <div class="progress-steps">
                ${generateProgressSteps(application.progress)}
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${application.progress}%"></div>
            </div>
            <div class="progress-labels">
                <div class="progress-label">Applied</div>
                <div class="progress-label">Reviewed</div>
                <div class="progress-label">Interview</div>
                <div class="progress-label">Assessment</div>
                <div class="progress-label">Offer</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>Job Description</h3>
            <p>${escapeHtml(application.details.description)}</p>
        </div>
        
        <div class="detail-section">
            <h3>Requirements</h3>
            <p>${escapeHtml(application.details.requirements)}</p>
        </div>
        
        <div class="detail-section">
            <h3>Contact Information</h3>
            <div class="detail-info">
                <div class="info-item">
                    <div class="info-label">Contact Person</div>
                    <div class="info-value">${escapeHtml(application.details.contactPerson)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">${escapeHtml(application.details.contactEmail)}</div>
                </div>
            </div>
        </div>
        
        ${application.application_data.resume_path ? `
        <div class="detail-section">
            <h3>Application Materials</h3>
            <div class="application-materials">
                <div class="material-item">
                    <i class="fas fa-file-pdf"></i>
                    <span>${escapeHtml(application.application_data.resume_filename)}</span>
                    <button class="download-btn" onclick="downloadResume('${application.application_data.resume_path}')">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
        </div>
        ` : ''}
        
        <div class="detail-section">
            <h3>Application Timeline</h3>
            <div class="timeline">
                ${timelineHTML}
            </div>
        </div>
        
        ${application.next_steps ? `
        <div class="detail-section">
            <h3>Next Steps</h3>
            <div class="next-steps">
                <i class="fas fa-lightbulb"></i>
                <p>${escapeHtml(application.next_steps)}</p>
            </div>
        </div>
        ` : ''}
        
        <div class="modal-actions">
            ${actionButtons}
        </div>
    `;
    
    // Store application ID for actions
    const modal = document.getElementById('application-modal');
    modal.dataset.applicationId = application.id;
}

// Generate interview details HTML
function generateInterviewDetails(interview) {
    return `
        <div class="interview-details">
            <div class="interview-info">
                <strong>Interview Details:</strong>
                <p>Type: ${interview.type}</p>
                <p>Date: ${interview.date} at ${interview.time}</p>
                ${interview.meeting_link ? `<p>Meeting Link: <a href="${interview.meeting_link}" target="_blank">Join Interview</a></p>` : ''}
                ${interview.location ? `<p>Location: ${interview.location}</p>` : ''}
            </div>
        </div>
    `;
}

// Generate feedback details HTML
function generateFeedbackDetails(feedback) {
    return `
        <div class="feedback-details">
            <div class="feedback-scores">
                <strong>Interview Feedback:</strong>
                <p>Overall Rating: ${feedback.overall_rating}/5</p>
                ${feedback.strengths ? `<p><strong>Strengths:</strong> ${feedback.strengths}</p>` : ''}
                ${feedback.areas_for_improvement ? `<p><strong>Areas for Improvement:</strong> ${feedback.areas_for_improvement}</p>` : ''}
            </div>
        </div>
    `;
}

// Generate action buttons based on application status
function generateActionButtons(application) {
    let buttons = `<button class="action-btn secondary-action" id="close-modal-btn">Close</button>`;
    
    if (application.can_withdraw) {
        buttons += `<button class="action-btn danger-action" onclick="withdrawApplication(${application.id})">Withdraw Application</button>`;
    }
    
    return buttons;
}

// Initialize modal
function initializeModal() {
    const modal = document.getElementById('application-modal');
    const closeModal = document.querySelector('.close-modal');
    
    // Close modal events
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Event delegation for close button in modal body
    document.addEventListener('click', (event) => {
        if (event.target.id === 'close-modal-btn') {
            modal.style.display = 'none';
        }
    });
}

// Withdraw application
async function withdrawApplication(applicationId) {
    // Create withdrawal modal
    const withdrawalModal = createWithdrawalModal(applicationId);
    document.body.appendChild(withdrawalModal);
    withdrawalModal.style.display = 'flex';
}

// Create withdrawal modal
function createWithdrawalModal(applicationId) {
    const modal = document.createElement('div');
    modal.className = 'withdrawal-modal';
    modal.id = 'withdrawal-modal';
    
    modal.innerHTML = `
        <div class="withdrawal-modal-content">
            <div class="withdrawal-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Withdraw Application</h3>
                <button class="close-withdrawal-modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="withdrawal-modal-body">
                <div class="withdrawal-warning">
                    <i class="fas fa-info-circle"></i>
                    <p>Are you sure you want to withdraw this application? This action cannot be undone.</p>
                </div>
                <div class="form-group">
                    <label for="withdrawal-reason">Reason for withdrawal (optional):</label>
                    <textarea 
                        id="withdrawal-reason" 
                        placeholder="Please provide a reason for withdrawing your application..."
                        rows="4"
                    ></textarea>
                </div>
                <div class="withdrawal-options">
                    <div class="common-reasons">
                        <p>Common reasons:</p>
                        <div class="reason-buttons">
                            <button class="reason-btn" data-reason="Found another opportunity">Found another opportunity</button>
                            <button class="reason-btn" data-reason="Position no longer suitable">Position no longer suitable</button>
                            <button class="reason-btn" data-reason="Personal circumstances">Personal circumstances</button>
                            <button class="reason-btn" data-reason="Company culture mismatch">Company culture mismatch</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="withdrawal-modal-footer">
                <button class="withdrawal-confirm-btn" data-application-id="${applicationId}">
                    <i class="fas fa-check"></i> Confirm Withdrawal
                </button>
                <button class="withdrawal-cancel-btn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    `;
    
    // Add event listeners
    setupWithdrawalModalEvents(modal);
    
    return modal;
}

// Setup withdrawal modal events
function setupWithdrawalModalEvents(modal) {
    // Close button
    const closeBtn = modal.querySelector('.close-withdrawal-modal');
    const cancelBtn = modal.querySelector('.withdrawal-cancel-btn');
    
    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => {
                modal.style.display = 'none';
                modal.remove();
            });
        }
    });
    
    // Reason buttons
    const reasonBtns = modal.querySelectorAll('.reason-btn');
    const textarea = modal.querySelector('#withdrawal-reason');
    
    reasonBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            textarea.value = btn.dataset.reason;
            
            // Visual feedback
            reasonBtns.forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        });
    });
    
    // Confirm button
    const confirmBtn = modal.querySelector('.withdrawal-confirm-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
            const applicationId = confirmBtn.dataset.applicationId;
            const reason = textarea.value.trim() || 'No reason provided';
            
            // Disable button during processing
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            try {
                const formData = new FormData();
                formData.append('action', 'withdraw_application');
                formData.append('application_id', applicationId);
                formData.append('reason', reason);
                
                const response = await fetch('../../backend/candidate/application_actions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Application withdrawn successfully', 'success');
                    
                    // Close modal
                    modal.style.display = 'none';
                    modal.remove();
                    
                    // Close application details modal
                    const appModal = document.getElementById('application-modal');
                    if (appModal) appModal.style.display = 'none';
                    
                    // Reload applications and stats
                    loadApplications();
                    loadApplicationStats();
                } else {
                    showError(data.error || 'Failed to withdraw application');
                    
                    // Re-enable button
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Withdrawal';
                }
            } catch (error) {
                console.error('Error withdrawing application:', error);
                showError('Failed to withdraw application');
                
                // Re-enable button
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Withdrawal';
            }
        });
    }
    
    // Close when clicking outside
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            modal.remove();
        }
    });
}

// Download resume
function downloadResume(resumePath) {
    if (resumePath) {
        const link = document.createElement('a');
        link.href = '../../' + resumePath;
        link.download = '';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else {
        showError('Resume file not found');
    }
}

// Update application count display
function updateApplicationCount(total) {
    // You can add a count display element if needed
    console.log(`Total applications: ${total}`);
}

// Utility functions
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showNotification(message, type = 'success') {
    // Create or update notification
    let notification = document.getElementById('notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'notification';
        notification.className = 'notification';
        document.body.appendChild(notification);
    }
    
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

function showError(message) {
    showNotification(message, 'error');
}

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
        // Check if high contrast mode is already enabled
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
        // Check if reduce motion is already enabled
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
    
    // Font size controls
    const decreaseFontBtn = document.getElementById('decrease-font');
    const increaseFontBtn = document.getElementById('increase-font');
    const fontSizeValue = document.querySelector('.font-size-value');
    
    // Initialize font size
    let currentFontSize = 100;
    if (localStorage.getItem('fontSize')) {
        currentFontSize = parseInt(localStorage.getItem('fontSize'));
        updateFontSize(currentFontSize);
    }
    
    if (decreaseFontBtn) {
        decreaseFontBtn.addEventListener('click', function() {
            if (currentFontSize > 80) {
                currentFontSize -= 10;
                updateFontSize(currentFontSize);
            }
        });
    }
    
    if (increaseFontBtn) {
        increaseFontBtn.addEventListener('click', function() {
            if (currentFontSize < 150) {
                currentFontSize += 10;
                updateFontSize(currentFontSize);
            }
        });
    }
    
    function updateFontSize(size) {
        document.documentElement.style.fontSize = size + '%';
        if (fontSizeValue) fontSizeValue.textContent = size + '%';
        localStorage.setItem('fontSize', size);
        
        // Update classes for specific size ranges
        document.body.classList.remove('large-text', 'larger-text');
        if (size >= 120 && size < 140) {
            document.body.classList.add('large-text');
        } else if (size >= 140) {
            document.body.classList.add('larger-text');
        }
    }
}