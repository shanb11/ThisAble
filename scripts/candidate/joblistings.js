// Global variables
let allJobs = [];
let filteredJobs = [];
let savedJobs = [];
let isLoading = false;
let searchTimeout;

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing job listings...');
    
    // Initialize sidebar toggle
    initializeSidebar();
    
    // Load saved jobs first
    loadSavedJobs();
    
    // Load job listings
    loadJobListings();
    
    // Initialize search
    initializeSearch();
    
    
    
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

// Load job listings from backend
async function loadJobListings(searchTerm = '') {
    if (isLoading) return;
    
    isLoading = true;
    const loadingContainer = document.getElementById('loading-container');
    const jobsContainer = document.getElementById('jobs-container');
    
    // Show loading state
    if (loadingContainer) {
        loadingContainer.style.display = 'flex';
    }
    
    try {
        const url = searchTerm 
            ? `../../backend/candidate/get_job_listings.php?search=${encodeURIComponent(searchTerm)}`
            : '../../backend/candidate/get_job_listings.php';
            
        console.log('Fetching jobs from:', url);
        
        const response = await fetch(url);
        const data = await response.json();
        
        console.log('API Response:', data);
        
        if (data.success) {
            allJobs = data.jobs;
            filteredJobs = [...allJobs];
            renderJobListings();
            updateJobStats(data.total, searchTerm);
        } else {
            showError('Failed to load jobs: ' + (data.error || 'Unknown error'));
            showEmptyState();
        }
    } catch (error) {
        console.error('Error loading jobs:', error);
        showError('Failed to connect to server. Please try again.');
        showEmptyState();
    } finally {
        isLoading = false;
        if (loadingContainer) {
            loadingContainer.style.display = 'none';
        }
    }
}

// Render job listings
function renderJobListings() {
    const container = document.getElementById('jobs-container');
    const loadingContainer = document.getElementById('loading-container');
    
    // Clear existing content except loading
    Array.from(container.children).forEach(child => {
        if (child.id !== 'loading-container') {
            child.remove();
        }
    });
    
    if (filteredJobs.length === 0) {
        showEmptyState();
        return;
    }
    
    filteredJobs.forEach(job => {
        const jobCard = createJobCard(job);
        container.appendChild(jobCard);
    });
}

// Create job card element
function createJobCard(job) {
    const article = document.createElement('article');
    article.className = 'job-card';
    article.setAttribute('role', 'listitem');
    article.setAttribute('tabindex', '0');
    article.setAttribute('data-job-id', job.job_id);
    article.setAttribute('aria-labelledby', `job-title-${job.job_id}`);
    
    // Check if job is saved
    const isSaved = savedJobs.includes(job.job_id.toString());
    const saveIconClass = isSaved ? 'fas' : 'far';
    const saveButtonClass = isSaved ? 'saved' : '';
    
    // Create accommodations HTML
    const accommodationBadges = job.accommodations.map(acc => 
        `<span class="feature-badge">
            <i class="${acc.icon}" aria-hidden="true"></i>
            ${escapeHtml(acc.name)}
        </span>`
    ).join('');
    
    // Determine work mode icon
    const workModeIcon = getWorkModeIcon(job.work_mode);
    
    article.innerHTML = `
        <div class="job-card-header">
            <h2 class="job-title" id="job-title-${job.job_id}">${escapeHtml(job.job_title)}</h2>
            <div class="company-name">
                <i class="far fa-building" aria-hidden="true"></i>
                ${escapeHtml(job.company_name)}
            </div>
            <div class="location-pill">
                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                ${escapeHtml(job.location)}
            </div>
            <div class="job-tags">
                <span class="job-tag">
                    <i class="fas ${workModeIcon}" aria-hidden="true"></i>
                    ${escapeHtml(job.work_mode)}
                </span>
                <span class="job-tag">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    ${escapeHtml(job.employment_type)}
                </span>
                ${job.salary_range ? `<span class="job-tag salary">
                    <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                    ${escapeHtml(job.salary_range)}
                </span>` : ''}
            </div>
        </div>
        <div class="job-card-body">
            <p class="job-description">${escapeHtml(job.job_description)}</p>
            
            <div class="accessibility-features" aria-labelledby="features-title-${job.job_id}">
                <h3 class="feature-title" id="features-title-${job.job_id}">
                    <i class="fas fa-universal-access" aria-hidden="true"></i>
                    PWD Accommodations
                </h3>
                <div class="features-list">
                    ${accommodationBadges}
                </div>
            </div>
            
            <button class="apply-btn" data-job-id="${job.job_id}">
                Apply Now
            </button>
        </div>
        <div class="job-card-footer">
            <div class="job-posted">
                <i class="far fa-clock" aria-hidden="true"></i>
                ${escapeHtml(job.posted_date)}
            </div>
            <div class="job-stats">
                <span class="stat">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                    ${job.views_count} views
                </span>
                <span class="stat">
                    <i class="fas fa-users" aria-hidden="true"></i>
                    ${job.applications_count} applied
                </span>
            </div>
            <div class="job-actions">
                <button class="action-btn save-btn ${saveButtonClass}" aria-label="Save job" data-job-id="${job.job_id}">
                    <i class="${saveIconClass} fa-bookmark" aria-hidden="true"></i>
                </button>
                <button class="action-btn share-btn" aria-label="Share job" data-job-id="${job.job_id}">
                    <i class="far fa-share-square" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    `;
    
    // Add event listeners
    addJobCardEventListeners(article, job);
    
    return article;
}

// Add event listeners to job card
function addJobCardEventListeners(card, job) {
    // Apply button
    const applyBtn = card.querySelector('.apply-btn');
    applyBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showApplicationModal(job);
    });
    
    // Save button
    const saveBtn = card.querySelector('.save-btn');
    saveBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleSaveJob(job.job_id, saveBtn);
    });
    
    // Share button
    const shareBtn = card.querySelector('.share-btn');
    shareBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showShareModal(job);
    });
    
    // Track view when card is clicked
    card.addEventListener('click', () => {
        trackJobView(job.job_id);
    });
}

// Initialize search functionality
function initializeSearch() {
    const searchInput = document.getElementById('job-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            
            // Add loading state to search input
            this.parentElement.classList.add('loading');
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Debounce search
            searchTimeout = setTimeout(() => {
                this.parentElement.classList.remove('loading');
                loadJobListings(searchValue);
            }, 500);
        });
    }
}

// Load saved jobs for current user
async function loadSavedJobs() {
    if (!window.candidateData.isLoggedIn) {
        console.log('User not logged in, skipping saved jobs');
        return;
    }
    
    try {
        const response = await fetch('../../backend/candidate/job_actions.php?action=get_saved_jobs');
        const data = await response.json();
        
        if (data.success) {
            savedJobs = data.saved_jobs.map(id => id.toString());
            console.log('Loaded saved jobs:', savedJobs);
        } else {
            console.error('Failed to load saved jobs:', data.error);
        }
    } catch (error) {
        console.error('Error loading saved jobs:', error);
    }
}

// Toggle save job
async function toggleSaveJob(jobId, buttonElement) {
    if (!window.candidateData.isLoggedIn) {
        showError('Please log in to save jobs');
        return;
    }
    
    const isSaved = savedJobs.includes(jobId.toString());
    const action = isSaved ? 'unsave_job' : 'save_job';
    
    // Optimistic UI update
    const icon = buttonElement.querySelector('i');
    const originalSavedState = isSaved;
    
    try {
        // Update UI immediately
        if (isSaved) {
            savedJobs = savedJobs.filter(id => id !== jobId.toString());
            icon.classList.remove('fas');
            icon.classList.add('far');
            buttonElement.classList.remove('saved');
        } else {
            savedJobs.push(jobId.toString());
            icon.classList.remove('far');
            icon.classList.add('fas');
            buttonElement.classList.add('saved');
        }
        
        // Send request to backend
        const formData = new FormData();
        formData.append('action', action);
        formData.append('job_id', jobId);
        
        const response = await fetch('../../backend/candidate/job_actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(isSaved ? 'Job removed from saved' : 'Job saved successfully');
        } else {
            // Revert UI changes on error
            if (originalSavedState) {
                savedJobs.push(jobId.toString());
                icon.classList.remove('far');
                icon.classList.add('fas');
                buttonElement.classList.add('saved');
            } else {
                savedJobs = savedJobs.filter(id => id !== jobId.toString());
                icon.classList.remove('fas');
                icon.classList.add('far');
                buttonElement.classList.remove('saved');
            }
            showError(data.error || 'Failed to save job');
        }
    } catch (error) {
        // Revert UI changes on error
        if (originalSavedState) {
            savedJobs.push(jobId.toString());
            icon.classList.remove('far');
            icon.classList.add('fas');
            buttonElement.classList.add('saved');
        } else {
            savedJobs = savedJobs.filter(id => id !== jobId.toString());
            icon.classList.remove('fas');
            icon.classList.add('far');
            buttonElement.classList.remove('saved');
        }
        console.error('Error saving job:', error);
        showError('Failed to save job. Please try again.');
    }
}

// Track job view
async function trackJobView(jobId) {
    if (!window.candidateData.isLoggedIn) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'track_view');
        formData.append('job_id', jobId);
        
        await fetch('../../backend/candidate/job_actions.php', {
            method: 'POST',
            body: formData
        });
    } catch (error) {
        console.error('Error tracking view:', error);
    }
}

// Update job statistics
function updateJobStats(total, searchTerm) {
    const jobsCount = document.getElementById('jobs-count');
    const searchInfo = document.getElementById('search-info');
    
    if (jobsCount) {
        const jobText = total === 1 ? 'opportunity' : 'opportunities';
        jobsCount.textContent = `${total} ${jobText} found`;
    }
    
    if (searchInfo && searchTerm) {
        searchInfo.textContent = `for "${searchTerm}"`;
    } else if (searchInfo) {
        searchInfo.textContent = '';
    }
}

// Show empty state
function showEmptyState() {
    const container = document.getElementById('jobs-container');
    const loadingContainer = document.getElementById('loading-container');
    
    // Remove loading container
    if (loadingContainer) {
        loadingContainer.remove();
    }
    
    // Clear container
    container.innerHTML = '';
    
    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'jobs-empty';
    emptyDiv.innerHTML = `
        <i class="fas fa-search"></i>
        <h3>No opportunities found</h3>
        <p>Try adjusting your search criteria or check back later for new postings.</p>
    `;
    
    container.appendChild(emptyDiv);
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.querySelector('.notification');
    if (!notification) {
        // Create notification element if it doesn't exist
        const notificationEl = document.createElement('div');
        notificationEl.className = 'notification';
        document.body.appendChild(notificationEl);
    }
    
    const notificationElement = document.querySelector('.notification');
    notificationElement.textContent = message;
    notificationElement.className = `notification ${type}`;
    notificationElement.classList.add('show');
    
    setTimeout(() => {
        notificationElement.classList.remove('show');
    }, 3000);
}

// Show error notification
function showError(message) {
    showNotification(message, 'error');
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

function getWorkModeIcon(workMode) {
    const icons = {
        'Remote': 'fa-home',
        'Hybrid': 'fa-laptop-house',
        'On-site': 'fa-building'
    };
    return icons[workMode] || 'fa-briefcase';
}

// ===================================================================
// EXISTING MODAL FUNCTIONS FROM YOUR ORIGINAL FILE
// ===================================================================

// Filter modal initialization
function initializeFilterModal() {
    const filterBtn = document.getElementById('filter-btn');
    let filterModal = document.querySelector('.filter-modal');
    
    if (!filterModal) {
        filterModal = document.createElement('div');
        filterModal.className = 'filter-modal';
        filterModal.innerHTML = `
            <div class="filter-modal-content">
                <div class="filter-modal-header">
                    <h3>Filter Jobs</h3>
                    <button class="close-filter-modal" aria-label="Close filter options">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="filter-modal-body">
                    <div class="filter-section">
                        <h4>Job Type</h4>
                        <div class="filter-options">
                            <label>
                                <input type="checkbox" name="job-type" value="full-time"> Full-time
                            </label>
                            <label>
                                <input type="checkbox" name="job-type" value="part-time"> Part-time
                            </label>
                            <label>
                                <input type="checkbox" name="job-type" value="contract"> Contract
                            </label>
                            <label>
                                <input type="checkbox" name="job-type" value="internship"> Internship
                            </label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <h4>Work Mode</h4>
                        <div class="filter-options">
                            <label>
                                <input type="checkbox" name="work-mode" value="remote"> Remote
                            </label>
                            <label>
                                <input type="checkbox" name="work-mode" value="hybrid"> Hybrid
                            </label>
                            <label>
                                <input type="checkbox" name="work-mode" value="on-site"> On-site
                            </label>
                        </div>
                    </div>
                    <div class="filter-section">
                        <h4>Accessibility Features</h4>
                        <div class="filter-options">
                            <label>
                                <input type="checkbox" name="accessibility" value="flexible-schedule"> Flexible Schedule
                            </label>
                            <label>
                                <input type="checkbox" name="accessibility" value="assistive-tech"> Assistive Technology
                            </label>
                            <label>
                                <input type="checkbox" name="accessibility" value="accessible-office"> Accessible Office
                            </label>
                            <label>
                                <input type="checkbox" name="accessibility" value="transportation"> Transportation Assistance
                            </label>
                        </div>
                    </div>
                </div>
                <div class="filter-modal-footer">
                    <button class="clear-filters-btn">Clear All</button>
                    <button class="apply-filters-btn">Apply Filters</button>
                </div>
            </div>
        `;
        document.body.appendChild(filterModal);
    }
    
    const closeFilterModal = document.querySelector('.close-filter-modal');
    
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            filterModal.style.display = 'flex';
        });
    }
    
    if (closeFilterModal) {
        closeFilterModal.addEventListener('click', function() {
            filterModal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === filterModal) {
            filterModal.style.display = 'none';
        }
    });
}

// Share modal initialization
function initializeShareModal() {
    let shareModal = document.querySelector('.share-modal');
    
    if (!shareModal) {
        shareModal = document.createElement('div');
        shareModal.className = 'share-modal';
        shareModal.innerHTML = `
            <div class="share-modal-content">
                <div class="share-modal-header">
                    <h3>Share This Job</h3>
                    <button class="close-share-modal" aria-label="Close share options">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="share-modal-body">
                    <p>Share this opportunity with your network</p>
                    <div class="share-buttons">
                        <button class="social-share-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                        <button class="social-share-btn twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </button>
                        <button class="social-share-btn linkedin">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </button>
                        <button class="social-share-btn email">
                            <i class="fas fa-envelope"></i> Email
                        </button>
                    </div>
                    <div class="share-link-container">
                        <input type="text" class="share-link" value="https://thisable.org/jobs/web-developer-123" readonly>
                        <button class="copy-link-btn">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(shareModal);
    }
    
    const closeShareModal = document.querySelector('.close-share-modal');
    
    if (closeShareModal) {
        closeShareModal.addEventListener('click', function() {
            shareModal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === shareModal) {
            shareModal.style.display = 'none';
        }
    });
    
    // Copy link functionality
    const copyLinkBtn = document.querySelector('.copy-link-btn');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', function() {
            const shareLink = document.querySelector('.share-link');
            shareLink.select();
            document.execCommand('copy');
            showNotification('Link copied to clipboard!');
        });
    }
}

// Application modal initialization
function initializeApplicationModal() {
    let applicationModal = document.querySelector('.application-modal');
    
    if (!applicationModal) {
        applicationModal = document.createElement('div');
        applicationModal.className = 'application-modal';
        applicationModal.innerHTML = `
            <div class="application-modal-content">
                <div class="application-modal-header">
                    <h3>Apply for Job</h3>
                    <button class="close-application-modal" aria-label="Close application form">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="application-modal-body">
                    <div class="job-overview">
                        <h4 id="application-job-title">Job Title</h4>
                        <p id="application-company">Company Name</p>
                    </div>
                    
                    <div class="resume-recommendation">
                        <h4>Resume</h4>
                        <div class="resume-preview">
                            <i class="fas fa-file-alt"></i>
                            <div class="resume-info">
                                <p class="resume-name">MyResume_2024.pdf</p>
                                <p class="resume-match">85% match to job requirements</p>
                            </div>
                            <button class="view-resume-btn">View</button>
                        </div>
                        
                        <div class="resume-tips">
                            <h5>Personalization Tips:</h5>
                            <ul id="resume-tips-list">
                                <li>Highlight your relevant experience</li>
                                <li>Mention experience with accessibility standards</li>
                                <li>Include relevant technical skills for this role</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="additional-materials">
                        <h4>Additional Materials</h4>
                        <div class="materials-options">
                            <label>
                                <input type="checkbox" name="cover-letter" id="include-cover-letter"> Include cover letter
                            </label>
                            <label>
                                <input type="checkbox" name="portfolio" id="include-portfolio"> Include portfolio link
                            </label>
                            <label>
                                <input type="checkbox" name="references" id="include-references"> Include references
                            </label>
                        </div>
                    </div>
                    
                    <div class="accessibility-needs">
                        <h4>Accessibility Needs</h4>
                        <textarea id="accessibility-needs-text" placeholder="Please share any accessibility accommodations you may need during the interview process..."></textarea>
                    </div>
                </div>
                <div class="application-modal-footer">
                    <button class="submit-application-btn" id="submit-application-btn">Submit Application</button>
                    <button class="save-draft-btn">Save Draft</button>
                </div>
            </div>
        `;
        document.body.appendChild(applicationModal);
    }
    
    const closeApplicationModal = document.querySelector('.close-application-modal');
    
    if (closeApplicationModal) {
        closeApplicationModal.addEventListener('click', function() {
            applicationModal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target === applicationModal) {
            applicationModal.style.display = 'none';
        }
    });
    
    // Submit application button
    const submitApplicationBtn = document.querySelector('.submit-application-btn');
    if (submitApplicationBtn) {
        submitApplicationBtn.addEventListener('click', function() {
            submitApplicationFromModal();
        });
    }
}

// Show application modal
async function showApplicationModal(job) {
    if (!window.candidateData.isLoggedIn) {
        showError('Please log in to apply for jobs');
        return;
    }
    
    const applicationModal = document.querySelector('.application-modal');
    
    // Store job_id immediately
    applicationModal.dataset.jobId = job.job_id;
    
    // Show loading state
    applicationModal.innerHTML = `
        <div class="application-modal-content">
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <p>Loading application data...</p>
            </div>
        </div>
    `;
    applicationModal.style.display = 'flex';
    
    try {
        // Fetch application data from backend
        const response = await fetch(`../../backend/candidate/get_application_data.php?job_id=${job.job_id}`);
        const data = await response.json();
        
        if (data.success) {
            renderApplicationModal(data.data, job.job_id); // Pass job_id as backup
        } else {
            showError(data.error || 'Failed to load application data');
            applicationModal.style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading application data:', error);
        showError('Failed to load application data');
        applicationModal.style.display = 'none';
    }
}

// Render application modal with real data
function renderApplicationModal(data, backupJobId = null) {
    const applicationModal = document.querySelector('.application-modal');
    
    // Ensure job_id is set properly
    const jobId = data.job.job_id || backupJobId || applicationModal.dataset.jobId;
    
    console.log('Setting job_id:', jobId); // Debug line
    
    // Check if user already applied
    if (data.already_applied) {
        applicationModal.innerHTML = `
            <div class="application-modal-content">
                <div class="application-modal-header">
                    <h3>Already Applied</h3>
                    <button class="close-application-modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="application-modal-body">
                    <div class="already-applied-message">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 20px;"></i>
                        <h4>You have already applied for this position</h4>
                        <p>Your application for ${data.job.job_title} at ${data.job.company_name} has been submitted and is being reviewed.</p>
                    </div>
                </div>
            </div>
        `;
        
        // Add close event
        applicationModal.querySelector('.close-application-modal').addEventListener('click', () => {
            applicationModal.style.display = 'none';
        });
        return;
    }
    
    // Render normal application modal
    const resumeSection = data.resume ? `
        <div class="resume-recommendation">
            <h4>Resume</h4>
            <div class="resume-preview">
                <i class="fas fa-file-alt"></i>
                <div class="resume-info">
                    <p class="resume-name">${escapeHtml(data.resume.file_name)}</p>
                    <p class="resume-match">${data.resume.match_percentage}% match to job requirements</p>
                </div>
                <button class="view-resume-btn" onclick="viewResume('${data.resume.file_path}')">View</button>
            </div>
            
            <div class="resume-tips">
                <h5>Personalization Tips:</h5>
                <ul id="resume-tips-list">
                    ${data.personalization_tips.map(tip => `<li>${escapeHtml(tip)}</li>`).join('')}
                </ul>
            </div>
        </div>
    ` : `
        <div class="resume-recommendation">
            <h4>Resume</h4>
            <div class="no-resume-message">
                <i class="fas fa-exclamation-triangle" style="color: #ffc107; margin-right: 10px;"></i>
                <p>No resume found. Please upload a resume in your profile before applying.</p>
                <a href="../candidate/profile.php" class="btn-secondary">Go to Profile</a>
            </div>
        </div>
    `;
    
    applicationModal.innerHTML = `
        <div class="application-modal-content">
            <div class="application-modal-header">
                <h3>Apply for Job</h3>
                <button class="close-application-modal" aria-label="Close application form">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="application-modal-body">
                <div class="job-overview">
                    <h4 id="application-job-title">${escapeHtml(data.job.job_title)}</h4>
                    <p id="application-company">${escapeHtml(data.job.company_name)}</p>
                </div>
                
                ${resumeSection}
                
                <div class="additional-materials">
                    <h4>Additional Materials</h4>
                    <div class="materials-options">
                        <label>
                            <input type="checkbox" name="cover-letter" id="include-cover-letter"> Include cover letter
                        </label>
                        <label>
                            <input type="checkbox" name="portfolio" id="include-portfolio"> Include portfolio link
                        </label>
                        <label>
                            <input type="checkbox" name="references" id="include-references"> Include references
                        </label>
                    </div>
                </div>
                
                <div class="accessibility-needs">
                    <h4>Accessibility Needs</h4>
                    <textarea id="accessibility-needs-text" placeholder="Please share any accessibility accommodations you may need during the interview process..."></textarea>
                </div>
            </div>
            <div class="application-modal-footer">
                <button class="submit-application-btn" id="submit-application-btn" ${!data.resume ? 'disabled' : ''}>
                    ${!data.resume ? 'Upload Resume First' : 'Submit Application'}
                </button>
                <button class="save-draft-btn">Cancel</button>
            </div>
        </div>
    `;
    
    // Set job_id in dataset (with fallback)
    applicationModal.dataset.jobId = jobId;
    if (data.resume) {
        applicationModal.dataset.resumeId = data.resume.resume_id;
    }
    
    console.log('Final job_id in dataset:', applicationModal.dataset.jobId); // Debug line
    
    // Add event listeners
    setupApplicationModalEvents();
}

// Setup event listeners for application modal
function setupApplicationModalEvents() {
    const applicationModal = document.querySelector('.application-modal');
    
    // Close button
    const closeBtn = applicationModal.querySelector('.close-application-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            applicationModal.style.display = 'none';
        });
    }
    
    // Cancel button
    const cancelBtn = applicationModal.querySelector('.save-draft-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            applicationModal.style.display = 'none';
        });
    }
    
    // Submit button
    const submitBtn = applicationModal.querySelector('.submit-application-btn');
    if (submitBtn && !submitBtn.disabled) {
        submitBtn.addEventListener('click', () => {
            submitApplicationFromModal();
        });
    }
    
    // Close when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === applicationModal) {
            applicationModal.style.display = 'none';
        }
    });
}

// Submit application from modal
async function submitApplicationFromModal() {
    const applicationModal = document.querySelector('.application-modal');
    const submitBtn = document.getElementById('submit-application-btn');
    const jobId = applicationModal.dataset.jobId;
    const resumeId = applicationModal.dataset.resumeId;
    
    console.log('Submitting application for job_id:', jobId); // Debug line
    console.log('Resume ID:', resumeId); // Debug line
    
    // Get form data
    const accessibilityNeeds = document.getElementById('accessibility-needs-text').value;
    const includeCoverLetter = document.getElementById('include-cover-letter').checked;
    const includePortfolio = document.getElementById('include-portfolio').checked;
    const includeReferences = document.getElementById('include-references').checked;
    
    if (!jobId || jobId === 'undefined' || jobId === '') {
        showError('Invalid job ID. Please try again.');
        console.error('Job ID is invalid:', jobId);
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    try {
        const formData = new FormData();
        formData.append('action', 'apply_job');
        formData.append('job_id', jobId);
        if (resumeId) formData.append('resume_id', resumeId);
        formData.append('accessibility_needs', accessibilityNeeds);
        
        // Add additional materials
        if (includeCoverLetter) formData.append('include_cover_letter', '1');
        if (includePortfolio) formData.append('include_portfolio', '1');
        if (includeReferences) formData.append('include_references', '1');
        
        console.log('Sending form data:', Object.fromEntries(formData)); // Debug line
        
        const response = await fetch('../../backend/candidate/job_actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Backend response:', data); // Debug line
        
        if (data.success) {
            applicationModal.style.display = 'none';
            showSuccessMessage();
            updateApplyButtonState(jobId);
        } else {
            showError(data.error || 'Failed to submit application');
        }
    } catch (error) {
        console.error('Error submitting application:', error);
        showError('Failed to submit application. Please try again.');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
    }
}

// View resume function
function viewResume(filePath) {
    if (filePath) {
        window.open(`../../${filePath}`, '_blank');
    } else {
        showError('Resume file not found');
    }
}

// Initialize all existing modals
    initializeFilterModal();
    initializeShareModal();
    initializeApplicationModal();
    initializeSuccessMessages();

// Update apply button state after application
function updateApplyButtonState(jobId) {
    const jobCard = document.querySelector(`[data-job-id="${jobId}"]`);
    if (jobCard) {
        const applyBtn = jobCard.querySelector('.apply-btn');
        if (applyBtn) {
            applyBtn.textContent = 'Applied';
            applyBtn.disabled = true;
            applyBtn.classList.add('applied');
        }
    }
}

// Update resume tips based on job title
function updateResumeTips(jobTitle) {
    const resumeTips = document.getElementById('resume-tips-list');
    if (!resumeTips) return;
    
    resumeTips.innerHTML = '';
    
    if (jobTitle.toLowerCase().includes('web developer') || jobTitle.toLowerCase().includes('developer')) {
        resumeTips.innerHTML = `
            <li>Highlight your web development projects</li>
            <li>Mention experience with accessibility standards</li>
            <li>Include relevant technical skills like HTML, CSS, JavaScript</li>
        `;
    } else if (jobTitle.toLowerCase().includes('ux') || jobTitle.toLowerCase().includes('designer')) {
        resumeTips.innerHTML = `
            <li>Showcase your design portfolio</li>
            <li>Emphasize experience with accessible design</li>
            <li>Highlight UX research and user testing skills</li>
        `;
    } else if (jobTitle.toLowerCase().includes('customer support') || jobTitle.toLowerCase().includes('support')) {
        resumeTips.innerHTML = `
            <li>Highlight customer service experience</li>
            <li>Mention experience with support tools</li>
            <li>Emphasize communication and problem-solving skills</li>
        `;
    } else {
        resumeTips.innerHTML = `
            <li>Customize your resume to highlight relevant skills</li>
            <li>Mention experience with accessibility standards</li>
            <li>Include achievements relevant to the position</li>
        `;
    }
}

// Show share modal
function showShareModal(job) {
    const shareModal = document.querySelector('.share-modal');
    const shareLink = document.querySelector('.share-link');
    
    if (shareLink) {
        // Create a slug from the job title
        const slug = job.job_title.toLowerCase().replace(/\s+/g, '-');
        shareLink.value = `${window.location.origin}/jobs/${slug}-${job.job_id}`;
    }
    
    if (shareModal) {
        shareModal.style.display = 'flex';
    }
}

// Success and save message initialization
function initializeSuccessMessages() {
    // Success message will be shown using existing notification system
}

// Show success message
function showSuccessMessage() {
    showNotification('Application submitted successfully! You\'ll receive a confirmation email shortly.');
}

function initializeAccessibilityFeatures() {
    const accessibilityToggle = document.querySelector('.accessibility-toggle');
    const accessibilityPanel = document.querySelector('.accessibility-panel');
    const highContrastToggle = document.getElementById('high-contrast');
    const reduceMotionToggle = document.getElementById('reduce-motion');
    const increaseFontBtn = document.getElementById('increase-font');
    const decreaseFontBtn = document.getElementById('decrease-font');
    const fontSizeValue = document.querySelector('.font-size-value');
    
    let currentFontSize = 100;
    
    // Toggle accessibility panel
    if (accessibilityToggle && accessibilityPanel) {
        accessibilityToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            accessibilityPanel.classList.toggle('active');
        });
        
        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!accessibilityPanel.contains(e.target) && !accessibilityToggle.contains(e.target)) {
                accessibilityPanel.classList.remove('active');
            }
        });
    }
    
    // High contrast mode
    if (highContrastToggle) {
        highContrastToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('high-contrast');
                localStorage.setItem('highContrast', 'true');
            } else {
                document.body.classList.remove('high-contrast');
                localStorage.setItem('highContrast', 'false');
            }
        });
        
        // Load saved preference
        if (localStorage.getItem('highContrast') === 'true') {
            highContrastToggle.checked = true;
            document.body.classList.add('high-contrast');
        }
    }
    
    // Reduce motion
    if (reduceMotionToggle) {
        reduceMotionToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('reduce-motion');
                localStorage.setItem('reduceMotion', 'true');
            } else {
                document.body.classList.remove('reduce-motion');
                localStorage.setItem('reduceMotion', 'false');
            }
        });
        
        // Load saved preference
        if (localStorage.getItem('reduceMotion') === 'true') {
            reduceMotionToggle.checked = true;
            document.body.classList.add('reduce-motion');
        }
    }
    
    // Font size controls
    if (increaseFontBtn) {
        increaseFontBtn.addEventListener('click', function() {
            if (currentFontSize < 150) {
                currentFontSize += 10;
                updateFontSize();
            }
        });
    }
    
    if (decreaseFontBtn) {
        decreaseFontBtn.addEventListener('click', function() {
            if (currentFontSize > 80) {
                currentFontSize -= 10;
                updateFontSize();
            }
        });
    }
    
    function updateFontSize() {
        document.documentElement.style.fontSize = currentFontSize + '%';
        if (fontSizeValue) {
            fontSizeValue.textContent = currentFontSize + '%';
        }
        localStorage.setItem('fontSize', currentFontSize);
    }
    
    // Load saved font size
    const savedFontSize = localStorage.getItem('fontSize');
    if (savedFontSize) {
        currentFontSize = parseInt(savedFontSize);
        updateFontSize();
    }
    
    console.log('Accessibility features initialized');
}

// Wait for your existing code to load first
setTimeout(function() {
    console.log('Applied Status Add-On: Starting...');
    addAppliedStatusFeature();
}, 3000);

function addAppliedStatusFeature() {
    // Add CSS for applied buttons
    addAppliedStatusCSS();
    
    // Check all existing job cards and update applied ones
    updateExistingJobCards();
    
    // Override the job card creation to include applied status
    interceptJobCardCreation();
    
    console.log('Applied Status Add-On: Ready!');
}

function addAppliedStatusCSS() {
    // Don't add if already exists
    if (document.getElementById('applied-status-addon-css')) return;
    
    const css = document.createElement('style');
    css.id = 'applied-status-addon-css';
    css.textContent = `
        /* Applied Status Add-On CSS */
        .apply-btn.addon-applied {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            cursor: default !important;
            position: relative !important;
        }
        
        .apply-btn.addon-applied:hover {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            transform: none !important;
        }
        
        .apply-btn.addon-applied i.fa-check {
            margin-right: 8px;
            color: white;
        }
        
        /* Status-specific colors */
        .apply-btn.addon-applied.status-under-review {
            background: linear-gradient(135deg, #FD8B51, #CB6040) !important;
        }
        
        .apply-btn.addon-applied.status-shortlisted,
        .apply-btn.addon-applied.status-interview-scheduled,
        .apply-btn.addon-applied.status-interviewed {
            background: linear-gradient(135deg, #8E44AD, #7B68EE) !important;
        }
        
        .apply-btn.addon-applied.status-rejected {
            background: linear-gradient(135deg, #dc3545, #c82333) !important;
        }
        
        .apply-btn.addon-applied.status-withdrawn {
            background: linear-gradient(135deg, #6c757d, #5a6268) !important;
        }
        
        .applied-date-addon {
            color: #28a745 !important;
            font-weight: 500 !important;
            font-size: 11px !important;
            margin-top: 4px !important;
        }
    `;
    
    document.head.appendChild(css);
}

function updateExistingJobCards() {
    const jobCards = document.querySelectorAll('.job-card');
    
    jobCards.forEach(card => {
        const jobId = card.dataset.jobId || card.getAttribute('data-job-id');
        if (jobId) {
            checkAndUpdateAppliedStatus(card, jobId);
        }
    });
}

async function checkAndUpdateAppliedStatus(card, jobId) {
    try {
        // Check if user has applied to this job
        const response = await fetch(`../../backend/candidate/get_application_data.php?job_id=${jobId}`);
        const data = await response.json();
        
        if (data.success && data.data.already_applied) {
            // User has applied - update the button
            updateApplyButtonToApplied(card, data.data.application_status);
        }
    } catch (error) {
        console.log('Could not check applied status for job', jobId, error);
    }
}

function updateApplyButtonToApplied(card, applicationStatus) {
    const applyBtn = card.querySelector('.apply-btn');
    if (!applyBtn || applyBtn.classList.contains('addon-applied')) return;
    
    // Get status configuration
    const statusConfig = getStatusConfig(applicationStatus);
    
    // Update button appearance
    applyBtn.classList.add('addon-applied');
    applyBtn.classList.add(`status-${applicationStatus || 'submitted'}`);
    
    // Update button content
    applyBtn.innerHTML = `<i class="fas fa-check"></i> ${statusConfig.text}`;
    applyBtn.title = `Application status: ${statusConfig.text}`;
    
    // Remove existing click handlers and add new one
    const newApplyBtn = applyBtn.cloneNode(true);
    applyBtn.parentNode.replaceChild(newApplyBtn, applyBtn);
    
    // Add applied button click handler
    newApplyBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        
        const message = `Application Status: ${statusConfig.text}`;
        
        // Use existing notification system if available
        if (typeof showNotification === 'function') {
            showNotification(message, 'info');
        } else {
            alert(message);
        }
    });
    
    // Add applied date to footer if not exists
    const jobPosted = card.querySelector('.job-posted');
    if (jobPosted && !jobPosted.querySelector('.applied-date-addon')) {
        const appliedDate = document.createElement('div');
        appliedDate.className = 'applied-date-addon';
        appliedDate.innerHTML = `Applied ${getRelativeTime()}`;
        jobPosted.appendChild(appliedDate);
    }
}

function getStatusConfig(status) {
    const configs = {
        'submitted': { text: 'Application Submitted' },
        'under_review': { text: 'Under Review' },
        'shortlisted': { text: 'Shortlisted' },
        'interview_scheduled': { text: 'Interview Scheduled' },
        'interviewed': { text: 'Interviewed' },
        'hired': { text: 'Hired! 🎉' },
        'rejected': { text: 'Not Selected' },
        'withdrawn': { text: 'Withdrawn' }
    };
    
    return configs[status] || { text: 'Applied' };
}

function getRelativeTime() {
    // Simple relative time - you can enhance this
    return 'recently';
}

function interceptJobCardCreation() {
    // Override the original renderJobListings if it exists
    if (typeof window.originalRenderJobListings === 'undefined' && typeof renderJobListings === 'function') {
        window.originalRenderJobListings = renderJobListings;
        
        // Create enhanced version
        window.renderJobListings = function() {
            // Call original function first
            window.originalRenderJobListings();
            
            // Then add applied status after a short delay
            setTimeout(() => {
                updateExistingJobCards();
            }, 500);
        };
        
        console.log('Applied Status Add-On: Enhanced renderJobListings');
    }
}

// Listen for successful application submissions
document.addEventListener('DOMContentLoaded', function() {
    // Monitor for application modal submissions
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('notification')) {
                        const message = node.textContent;
                        if (message.includes('Application submitted successfully') || message.includes('submitted successfully')) {
                            // Application was successful - update UI after a delay
                            setTimeout(() => {
                                updateExistingJobCards();
                            }, 1000);
                        }
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});

// Manual function to update a specific job card after application
window.markJobAsApplied = function(jobId, status = 'submitted') {
    const card = document.querySelector(`[data-job-id="${jobId}"]`);
    if (card) {
        updateApplyButtonToApplied(card, status);
        console.log(`Applied Status Add-On: Marked job ${jobId} as applied`);
    }
};

// Function to refresh all applied statuses
window.refreshAppliedStatuses = function() {
    console.log('Applied Status Add-On: Refreshing all applied statuses...');
    updateExistingJobCards();
};

console.log('Applied Status Add-On: Loaded and ready!');