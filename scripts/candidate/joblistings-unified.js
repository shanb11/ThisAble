// ===================================================================
// THISABLE JOB LISTINGS - UNIFIED VERSION
// Combines working apply functionality with TTS and enhanced features
// ===================================================================

// Global variables
let allJobs = [];
let filteredJobs = [];
let savedJobs = [];
let isLoading = false;
let searchTimeout;

// ===================================================================
// MAIN INITIALIZATION
// ===================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing ThisAble Job Listings - Unified Version...');
    
    // Initialize core functionality
    initializeSidebar();
    loadSavedJobs();
    loadJobListings();
    initializeSearch();
    initializeAccessibilityFeatures();
    
    // Initialize enhanced features
    initializeTTSFeatures();
    initializeVoiceSearch();
    
    // Initialize modals (but not application modal - we'll use the existing one)
    initializeFilterModal();
    initializeShareModal();
    
    // Add enhanced CSS
    addEnhancedCSS();
});

// ===================================================================
// CORE JOB LISTINGS FUNCTIONALITY (From working version)
// ===================================================================

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

async function loadJobListings(searchTerm = '') {
    if (isLoading) return;
    
    isLoading = true;
    const loadingContainer = document.getElementById('loading-container');
    
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

function renderJobListings() {
    const container = document.getElementById('jobs-container');
    
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

// Enhanced job card creation with TTS support
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
            
            <button class="view-details-btn" data-job-id="${job.job_id}">
                <i class="fas fa-info-circle"></i> View Full Details
            </button>
            
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
    
    // Add TTS button
    addTTSButton(article);
    
    return article;
}

function addJobCardEventListeners(card, job) {
    // Apply button - USE SOPHISTICATED MODAL WITH RESUME
    const applyBtn = card.querySelector('.apply-btn');
    applyBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showApplicationModal(job); // Use the full-featured modal
    });
    
    // View details button - NEW ENHANCED MODAL
    const detailsBtn = card.querySelector('.view-details-btn');
    detailsBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        showJobDetailsModal(job);
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

// ===================================================================
// APPLICATION FUNCTIONALITY - Full Featured Modal with Resume
// ===================================================================

async function showApplicationModal(job) {
    if (!window.candidateData.isLoggedIn) {
        showError('Please log in to apply for jobs');
        return;
    }
    
    // Create or get application modal
    let applicationModal = document.querySelector('.application-modal');
    
    if (!applicationModal) {
        applicationModal = document.createElement('div');
        applicationModal.className = 'application-modal';
        document.body.appendChild(applicationModal);
    }
    
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
            renderApplicationModal(data.data, job.job_id);
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
    
    console.log('Setting job_id:', jobId);
    
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
        
        applicationModal.querySelector('.close-application-modal').addEventListener('click', () => {
            applicationModal.style.display = 'none';
        });
        return;
    }
    
    // Render normal application modal with resume section
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
    
    console.log('Final job_id in dataset:', applicationModal.dataset.jobId);
    
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
    
    console.log('Submitting application for job_id:', jobId);
    console.log('Resume ID:', resumeId);
    
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
        
        console.log('Sending form data:', Object.fromEntries(formData));
        
        const response = await fetch('../../backend/candidate/job_actions.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Backend response:', data);
        
        if (data.success) {
            applicationModal.style.display = 'none';
            showNotification('Application submitted successfully! You\'ll receive a confirmation email shortly.', 'success');
            updateJobCardAfterApplication(jobId);
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

function updateJobCardAfterApplication(jobId) {
    const jobCard = document.querySelector(`[data-job-id="${jobId}"]`);
    if (!jobCard) return;
    
    // Update apply button
    const applyBtn = jobCard.querySelector('.apply-btn');
    if (applyBtn) {
        applyBtn.innerHTML = '<i class="fas fa-check"></i> Application Submitted';
        applyBtn.classList.add('applied');
        applyBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            showNotification('You have already applied to this position. Status: Application Submitted', 'info');
        };
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

// ===================================================================
// TTS (TEXT-TO-SPEECH) FUNCTIONALITY
// ===================================================================

function initializeTTSFeatures() {
    console.log('Initializing TTS features...');
    
    if (!('speechSynthesis' in window)) {
        console.log('TTS not supported');
        return;
    }
    
    addFloatingTTSControl();
}

function addTTSButton(jobCard) {
    if (jobCard.querySelector('.tts-btn')) return;
    
    const ttsBtn = document.createElement('button');
    ttsBtn.className = 'tts-btn';
    ttsBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    ttsBtn.title = 'Read job description aloud';
    ttsBtn.setAttribute('aria-label', 'Read job description');
    
    ttsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        readJobCard(jobCard);
    });
    
    // Style the TTS button
    jobCard.style.position = 'relative';
    ttsBtn.style.cssText = `
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: rgba(47, 138, 153, 0.9);
        color: white;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    `;
    
    ttsBtn.addEventListener('mouseenter', function() {
        this.style.background = 'rgba(253, 139, 81, 0.9)';
        this.style.transform = 'scale(1.1)';
    });
    
    ttsBtn.addEventListener('mouseleave', function() {
        this.style.background = 'rgba(47, 138, 153, 0.9)';
        this.style.transform = 'scale(1)';
    });
    
    jobCard.appendChild(ttsBtn);
}

function readJobCard(jobCard) {
    try {
        speechSynthesis.cancel();
        
        const title = jobCard.querySelector('.job-title')?.textContent || 'Job opening';
        const company = jobCard.querySelector('.company-name')?.textContent || '';
        const description = jobCard.querySelector('.job-description')?.textContent || '';
        const location = jobCard.querySelector('.location-pill')?.textContent || '';
        
        let textToRead = `${title}`;
        if (company) textToRead += ` at ${company.replace(/^\s*[^\w]*\s*/, '')}`;
        if (location) textToRead += ` in ${location.replace(/^\s*[^\w]*\s*/, '')}`;
        if (description) textToRead += `. ${description}`;
        
        if (textToRead.length < 10) {
            textToRead = 'Job information is available on screen';
        }
        
        const utterance = new SpeechSynthesisUtterance(textToRead);
        utterance.rate = 0.9;
        utterance.volume = 0.8;
        
        utterance.onstart = function() {
            jobCard.style.backgroundColor = 'rgba(47, 138, 153, 0.1)';
            jobCard.style.borderColor = '#2F8A99';
        };
        
        utterance.onend = function() {
            jobCard.style.backgroundColor = '';
            jobCard.style.borderColor = '';
        };
        
        speechSynthesis.speak(utterance);
        
    } catch (error) {
        console.error('TTS error:', error);
    }
}

function addFloatingTTSControl() {
    if (document.getElementById('floating-tts-btn')) return;
    
    const floatingBtn = document.createElement('button');
    floatingBtn.id = 'floating-tts-btn';
    floatingBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    floatingBtn.title = 'Read page content';
    
    floatingBtn.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 56px;
        height: 56px;
        border: none;
        border-radius: 50%;
        background: #2F8A99;
        color: white;
        cursor: pointer;
        z-index: 1000;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        transition: all 0.3s ease;
    `;
    
    floatingBtn.addEventListener('click', readPageSummary);
    
    floatingBtn.addEventListener('mouseenter', function() {
        this.style.background = '#FD8B51';
        this.style.transform = 'scale(1.1)';
    });
    
    floatingBtn.addEventListener('mouseleave', function() {
        this.style.background = '#2F8A99';
        this.style.transform = 'scale(1)';
    });
    
    document.body.appendChild(floatingBtn);
}

function readPageSummary() {
    try {
        speechSynthesis.cancel();
        
        const jobCards = document.querySelectorAll('.job-card');
        const jobCount = jobCards.length;
        
        let summaryText = `Job listings page. Found ${jobCount} job opportunities. `;
        
        if (jobCount > 0) {
            summaryText += 'Click on individual speaker buttons to read job descriptions, or use the View Details button for more information.';
        } else {
            summaryText += 'No jobs found. Try adjusting your search criteria.';
        }
        
        const utterance = new SpeechSynthesisUtterance(summaryText);
        utterance.rate = 0.9;
        utterance.volume = 0.8;
        
        speechSynthesis.speak(utterance);
        
    } catch (error) {
        console.error('Page summary TTS error:', error);
    }
}

// ===================================================================
// VOICE SEARCH FUNCTIONALITY
// ===================================================================

function initializeVoiceSearch() {
    console.log('Initializing voice search...');
    
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.log('Voice search not supported');
        return;
    }
    
    const searchInput = document.getElementById('job-search') || document.querySelector('input[placeholder*="search"]');
    if (!searchInput) {
        console.log('Search input not found');
        return;
    }
    
    addVoiceButtonToSearch(searchInput);
}

function addVoiceButtonToSearch(searchInput) {
    if (searchInput.parentElement.querySelector('.voice-search-btn')) return;
    
    const voiceBtn = document.createElement('button');
    voiceBtn.className = 'voice-search-btn';
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    voiceBtn.title = 'Voice search';
    voiceBtn.type = 'button';
    
    voiceBtn.style.cssText = `
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: #2F8A99;
        color: white;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    `;
    
    searchInput.parentElement.style.position = 'relative';
    searchInput.style.paddingRight = '50px';
    
    voiceBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        startVoiceSearch(searchInput, voiceBtn);
    });
    
    voiceBtn.addEventListener('mouseenter', function() {
        this.style.background = '#FD8B51';
        this.style.transform = 'translateY(-50%) scale(1.1)';
    });
    
    voiceBtn.addEventListener('mouseleave', function() {
        if (!this.classList.contains('listening')) {
            this.style.background = '#2F8A99';
            this.style.transform = 'translateY(-50%)';
        }
    });
    
    searchInput.parentElement.appendChild(voiceBtn);
}

function startVoiceSearch(searchInput, voiceBtn) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    voiceBtn.classList.add('listening');
    voiceBtn.style.background = '#dc3545';
    voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
    voiceBtn.style.animation = 'pulse 1s infinite';
    
    const originalPlaceholder = searchInput.placeholder;
    searchInput.placeholder = 'Listening... speak now';
    searchInput.style.borderColor = '#dc3545';
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        searchInput.value = transcript;
        
        const inputEvent = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(inputEvent);
        
        showNotification(`Searching for: "${transcript}"`, 'success');
    };
    
    recognition.onerror = function(event) {
        let message = 'Voice search failed. ';
        switch(event.error) {
            case 'not-allowed':
                message = 'Please allow microphone access and try again.';
                break;
            case 'no-speech':
                message = 'No speech detected. Please try again.';
                break;
            default:
                message = 'Voice search error. Please try again.';
        }
        showNotification(message, 'error');
    };
    
    recognition.onend = function() {
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#2F8A99';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceBtn.style.animation = 'none';
        searchInput.placeholder = originalPlaceholder;
        searchInput.style.borderColor = '';
    };
    
    try {
        recognition.start();
    } catch (error) {
        console.error('Failed to start voice recognition:', error);
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#2F8A99';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceBtn.style.animation = 'none';
        searchInput.placeholder = originalPlaceholder;
        searchInput.style.borderColor = '';
        showNotification('Could not start voice search', 'error');
    }
}

// ===================================================================
// JOB DETAILS MODAL (Enhanced Feature)
// ===================================================================

function showJobDetailsModal(job) {
    const modal = document.createElement('div');
    modal.className = 'job-details-modal';
    modal.innerHTML = `
        <div class="job-details-modal-content">
            <div class="job-details-header">
                <h2>${escapeHtml(job.job_title)}</h2>
                <div class="company-info">${escapeHtml(job.company_name)} • ${escapeHtml(job.location)}</div>
                <button class="close-job-details">&times;</button>
            </div>
            <div class="job-details-body">
                <div class="job-details-section">
                    <h3><i class="fas fa-briefcase"></i> Job Information</h3>
                    <div class="job-details-content">
                        <p><strong>Position:</strong> ${escapeHtml(job.job_title)}</p>
                        <p><strong>Employment Type:</strong> ${escapeHtml(job.employment_type)}</p>
                        <p><strong>Work Mode:</strong> ${escapeHtml(job.work_mode)}</p>
                        <p><strong>Location:</strong> ${escapeHtml(job.location)}</p>
                        ${job.salary_range ? `<p><strong>Salary Range:</strong> ${escapeHtml(job.salary_range)}</p>` : ''}
                        <p><strong>Posted:</strong> ${escapeHtml(job.posted_date)}</p>
                    </div>
                </div>

                <div class="job-details-section">
                    <h3><i class="fas fa-list"></i> Requirements</h3>
                    <div class="job-details-content">
                        <p>${job.job_requirements || 'No specific requirements listed'}</p>
                    </div>
                </div>
                
                <div class="job-details-section">
                    <h3><i class="fas fa-align-left"></i> Job Description</h3>
                    <div class="job-details-content">
                        <p>${escapeHtml(job.job_description)}</p>
                    </div>
                </div>
                
                <div class="job-details-section">
                    <h3><i class="fas fa-universal-access"></i> PWD Accommodations & Support</h3>
                    <div class="accommodations-grid">
                        ${job.accommodations.map(acc => 
                            `<div class="accommodation-item">
                                <i class="${acc.icon}"></i>
                                <span>${escapeHtml(acc.name)}</span>
                            </div>`
                        ).join('')}
                    </div>
                </div>

                <div class="job-details-section">
                    <h3><i class="fas fa-chart-line"></i> Job Statistics</h3>
                    <div class="job-stats-detailed">
                        <div class="stat-item">
                            <i class="fas fa-eye"></i>
                            <span><strong>${job.views_count}</strong> views</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span><strong>${job.applications_count}</strong> applications</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span>Posted <strong>${escapeHtml(job.posted_date)}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="job-details-footer">
                <button class="save-job-btn" onclick="toggleSaveJob(${job.job_id}, this)">
                    <i class="fas fa-bookmark"></i> ${savedJobs.includes(job.job_id.toString()) ? 'Saved' : 'Save Job'}
                </button>
                <button class="apply-job-btn" onclick="closeJobDetailsModal(); showApplicationModal(${JSON.stringify(job).replace(/"/g, '&quot;')})">
                    <i class="fas fa-paper-plane"></i> Apply Now
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    
    modal.querySelector('.close-job-details').addEventListener('click', () => {
        modal.remove();
        document.body.style.overflow = '';
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    });
    
    document.body.style.overflow = 'hidden';
}

function closeJobDetailsModal() {
    const modal = document.querySelector('.job-details-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

// ===================================================================
// REMAINING FUNCTIONS (Search, Save, Share, etc.)
// ===================================================================

function initializeSearch() {
    const searchInput = document.getElementById('job-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();
            
            this.parentElement.classList.add('loading');
            
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                this.parentElement.classList.remove('loading');
                loadJobListings(searchValue);
            }, 500);
        });
    }
}

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

async function toggleSaveJob(jobId, buttonElement) {
    if (!window.candidateData.isLoggedIn) {
        showError('Please log in to save jobs');
        return;
    }
    
    const isSaved = savedJobs.includes(jobId.toString());
    const action = isSaved ? 'unsave_job' : 'save_job';
    
    const icon = buttonElement.querySelector('i');
    const originalSavedState = isSaved;
    
    try {
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
            // Revert changes on error
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
        // Revert changes on error
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
    
    const closeFilterModal = filterModal.querySelector('.close-filter-modal');
    const clearFiltersBtn = filterModal.querySelector('.clear-filters-btn');
    const applyFiltersBtn = filterModal.querySelector('.apply-filters-btn');
    
    // Open modal
    if (filterBtn) {
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            filterModal.style.display = 'flex';
        });
    }
    
    // Close modal
    if (closeFilterModal) {
        closeFilterModal.addEventListener('click', function() {
            filterModal.style.display = 'none';
        });
    }
    
    if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', function() {
        const checkboxes = filterModal.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        
        // Reset all job cards to visible
        const allCards = document.querySelectorAll('.job-card');
        allCards.forEach(card => {
            card.style.display = 'block';
        });
        
        // Update job count
        updateJobStats(allCards.length, '');
        
        showNotification('Filters cleared!', 'info');
    });
}
    
    // Apply filters
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            const selectedFilters = {};
            const checkboxes = filterModal.querySelectorAll('input[type="checkbox"]:checked');
            
            checkboxes.forEach(cb => {
                const category = cb.getAttribute('name');
                if (!selectedFilters[category]) {
                    selectedFilters[category] = [];
                }
                selectedFilters[category].push(cb.value);
            });
            
            // Apply filters to job listings
            applyJobFilters(selectedFilters);
            filterModal.style.display = 'none';
            
            // Show notification
            showNotification('Filters applied successfully!', 'success');
        });
    }
    
    // Close when clicking outside
    filterModal.addEventListener('click', function(event) {
        if (event.target === filterModal) {
            filterModal.style.display = 'none';
        }
    });
}

function applyJobFilters(filters) {
    const allCards = document.querySelectorAll('.job-card');
    let visibleCount = 0;
    
    allCards.forEach(card => {
        let shouldShow = true;
        
        // Get job details from the card
        const jobTags = card.querySelectorAll('.job-tag');
        const jobTagsText = Array.from(jobTags).map(tag => tag.textContent.toLowerCase().trim()).join(' ');
        const companyName = card.querySelector('.company-name')?.textContent.toLowerCase() || '';
        const jobTitle = card.querySelector('.job-title')?.textContent.toLowerCase() || '';
        const features = card.querySelectorAll('.feature-badge');
        const featuresText = Array.from(features).map(feature => feature.textContent.toLowerCase().trim()).join(' ');
        
        // Check each filter category
        Object.keys(filters).forEach(category => {
            if (filters[category].length > 0 && shouldShow) {
                let categoryMatch = false;
                
                if (category === 'job-type') {
                    // Check employment type in job tags
                    categoryMatch = filters[category].some(value => {
                        const searchTerm = value.replace('-', ' ').toLowerCase();
                        return jobTagsText.includes(searchTerm) || 
                               jobTagsText.includes(value.toLowerCase()) ||
                               (value === 'full-time' && jobTagsText.includes('full time')) ||
                               (value === 'part-time' && jobTagsText.includes('part time'));
                    });
                }
                
                else if (category === 'work-mode') {
                    // Check work mode in job tags  
                    categoryMatch = filters[category].some(value => {
                        const searchTerm = value.replace('-', ' ').toLowerCase();
                        return jobTagsText.includes(searchTerm) ||
                               jobTagsText.includes(value.toLowerCase()) ||
                               (value === 'on-site' && (jobTagsText.includes('onsite') || jobTagsText.includes('on site')));
                    });
                }
                
                else if (category === 'accessibility') {
                    // Check accessibility features
                    categoryMatch = filters[category].some(value => {
                        const searchTerm = value.replace('-', ' ').toLowerCase();
                        return featuresText.includes(searchTerm) ||
                               featuresText.includes(value.toLowerCase()) ||
                               (value === 'flexible-schedule' && featuresText.includes('flexible')) ||
                               (value === 'assistive-tech' && (featuresText.includes('assistive') || featuresText.includes('technology'))) ||
                               (value === 'accessible-office' && featuresText.includes('accessible')) ||
                               (value === 'transportation' && featuresText.includes('transport'));
                    });
                }
                
                if (!categoryMatch) {
                    shouldShow = false;
                }
            }
        });
        
        if (shouldShow) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show empty state if no results
    if (visibleCount === 0) {
        showEmptyStateWithFilters();
    } else {
        // Remove empty state if it exists
        const emptyState = document.querySelector('.jobs-empty');
        if (emptyState) {
            emptyState.remove();
        }
    }
    
    // Update job count
    updateJobStats(visibleCount, '');
}

// ADD this new function for empty state with filters
function showEmptyStateWithFilters() {
    const container = document.getElementById('jobs-container');
    
    // Remove existing empty state
    const existingEmpty = container.querySelector('.jobs-empty');
    if (existingEmpty) {
        existingEmpty.remove();
    }
    
    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'jobs-empty';
    emptyDiv.innerHTML = `
        <i class="fas fa-filter"></i>
        <h3>No jobs match your filters</h3>
        <p>Try adjusting your filter criteria or clear all filters to see more opportunities.</p>
        <button class="clear-all-filters-btn" onclick="clearAllFiltersFromEmpty()">
    Clear All Filters
</button>
    `;
    
    container.appendChild(emptyDiv);
}

// ADD this global function for clearing filters from empty state
window.clearAllFiltersFromEmpty = function() {
    // Clear all checkboxes
    const checkboxes = document.querySelectorAll('.filter-modal input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
    
    // Show all cards
    const allCards = document.querySelectorAll('.job-card');
    allCards.forEach(card => {
        card.style.display = 'block';
    });
    
    // Remove empty state
    const emptyState = document.querySelector('.jobs-empty');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Update job count
    updateJobStats(allCards.length, '');
    
    showNotification('All filters cleared!', 'success');
};

function initializeShareModal() {
    // Basic share modal - can be expanded later
    console.log('Share modal initialized');
}

function showShareModal(job) {
    const shareModal = document.createElement('div');
    shareModal.className = 'share-modal';
    shareModal.innerHTML = `
        <div class="share-modal-content">
            <div class="share-modal-header">
                <h3>Share This Job</h3>
                <button class="close-share-modal">&times;</button>
            </div>
            <div class="share-modal-body">
                <p>Share this opportunity with your network</p>
                <div class="share-link-container">
                    <input type="text" class="share-link" value="https://thisable.org/jobs/${job.job_title.toLowerCase().replace(/\s+/g, '-')}-${job.job_id}" readonly>
                    <button class="copy-link-btn">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(shareModal);
    shareModal.style.display = 'flex';
    
    shareModal.querySelector('.close-share-modal').addEventListener('click', () => {
        shareModal.remove();
    });
    
    shareModal.addEventListener('click', (e) => {
        if (e.target === shareModal) {
            shareModal.remove();
        }
    });
    
    const copyBtn = shareModal.querySelector('.copy-link-btn');
    copyBtn.addEventListener('click', function() {
        const shareLink = shareModal.querySelector('.share-link');
        shareLink.select();
        document.execCommand('copy');
        showNotification('Link copied to clipboard!');
    });
}

function initializeAccessibilityFeatures() {
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            readPageSummary();
        }
        
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            speechSynthesis.cancel();
        }
        
        if (e.ctrlKey && e.key === 'v') {
            e.preventDefault();
            const searchInput = document.getElementById('job-search');
            const voiceBtn = document.querySelector('.voice-search-btn');
            if (searchInput && voiceBtn) {
                voiceBtn.click();
            }
        }
    });
}

// ===================================================================
// UTILITY FUNCTIONS
// ===================================================================

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

function showEmptyState() {
    const container = document.getElementById('jobs-container');
    const loadingContainer = document.getElementById('loading-container');
    
    if (loadingContainer) {
        loadingContainer.remove();
    }
    
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

function showNotification(message, type = 'success') {
    let notification = document.querySelector('.notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification';
        document.body.appendChild(notification);
    }
    
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

function showError(message) {
    showNotification(message, 'error');
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
// ENHANCED CSS STYLES
// ===================================================================

function addEnhancedCSS() {
    const css = document.createElement('style');
    css.id = 'unified-joblistings-css';
    css.textContent = `
        /* TTS Button Styles */
        .tts-btn:hover {
            background: rgba(253, 139, 81, 0.9) !important;
            transform: scale(1.1) !important;
        }
        
        /* Voice Search Styles */
        .voice-search-btn.listening {
            background: #dc3545 !important;
            animation: pulse 1s infinite !important;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: translateY(-50%) scale(1); }
            50% { opacity: 0.7; transform: translateY(-50%) scale(1.1); }
            100% { opacity: 1; transform: translateY(-50%) scale(1); }
        }
        
        /* View Details Button */
        .view-details-btn {
            background: linear-gradient(135deg, #257180, #2F8A99);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }
        
        .view-details-btn:hover {
            background: linear-gradient(135deg, #2F8A99, #257180);
            transform: translateY(-1px);
        }
        
        /* Job Details Modal */
        .job-details-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }
        
        .job-details-modal-content {
            background: white;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .job-details-header {
            background: linear-gradient(135deg, #257180, #2F8A99);
            color: white;
            padding: 24px;
            border-radius: 12px 12px 0 0;
            position: relative;
        }
        
        .job-details-header h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .company-info {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .close-job-details {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 4px;
        }
        
        .job-details-body {
            padding: 24px;
        }
        
        .job-details-section {
            margin-bottom: 24px;
        }
        
        .job-details-section h3 {
            color: #333333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .job-details-section h3 i {
            color: #FD8B51;
            font-size: 16px;
        }
        
        .job-details-content {
            color: #666666;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .accommodations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        
        .accommodation-item {
            background: rgba(253, 139, 81, 0.1);
            border: 1px solid rgba(253, 139, 81, 0.3);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            color: #FD8B51;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .job-stats-detailed {
            display: flex;
            gap: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }
        
        .stat-item i {
            color: #257180;
            font-size: 16px;
        }
        
        .job-details-footer {
            padding: 24px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .save-job-btn, .apply-job-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .save-job-btn {
            background: transparent;
            color: #257180;
            border: 2px solid #257180;
        }
        
        .save-job-btn:hover {
            background: #257180;
            color: white;
        }
        
        .apply-job-btn {
            background: #FD8B51;
            color: white;
        }
        
        .apply-job-btn:hover {
            background: #CB6040;
        }
        
        /* Share Modal Styles */
        .share-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }
        
        .share-modal-content {
            background: white;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            animation: modalSlideIn 0.3s ease;
        }
        
        .share-modal-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .share-modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        
        .close-share-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
            padding: 4px;
        }
        
        .share-modal-body {
            padding: 20px;
        }
        
        .share-link-container {
            display: flex;
            margin-top: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .share-link {
            flex: 1;
            padding: 10px 12px;
            border: none;
            font-size: 14px;
            background: #f8f9fa;
        }
        
        .copy-link-btn {
            padding: 10px 15px;
            background: #257180;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .copy-link-btn:hover {
            background: #FD8B51;
        }
        
        /* Applied Status */
        .apply-btn.applied {
            background: #28a745 !important;
            cursor: default !important;
        }
        
        .apply-btn.applied:hover {
            background: #28a745 !important;
            transform: none !important;
        }
        
        /* Application Modal Styles */
        .application-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }
        
        .application-modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }
        
        .application-modal-header {
            background: linear-gradient(135deg, #257180, #2F8A99);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .application-modal-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .close-application-modal {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
        }
        
        .application-modal-body {
            padding: 24px;
        }
        
        .job-overview {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 16px;
        }
        
        .job-overview h4 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 18px;
        }
        
        .job-overview p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .resume-recommendation {
            margin-bottom: 24px;
        }
        
        .resume-recommendation h4 {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 16px;
        }
        
        .resume-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .resume-preview i {
            font-size: 24px;
            color: #257180;
        }
        
        .resume-info {
            flex: 1;
        }
        
        .resume-name {
            margin: 0 0 4px 0;
            font-weight: 500;
            color: #333;
        }
        
        .resume-match {
            margin: 0;
            font-size: 12px;
            color: #28a745;
            font-weight: 500;
        }
        
        .view-resume-btn {
            background: #257180;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .resume-tips h5 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 14px;
        }
        
        .resume-tips ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
            font-size: 13px;
        }
        
        .resume-tips li {
            margin-bottom: 4px;
        }
        
        .additional-materials {
            margin-bottom: 24px;
        }
        
        .additional-materials h4 {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 16px;
        }
        
        .materials-options {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .materials-options label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-size: 14px;
            cursor: pointer;
        }
        
        .accessibility-needs {
            margin-bottom: 24px;
        }
        
        .accessibility-needs h4 {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 16px;
        }
        
        .accessibility-needs textarea {
            width: 100%;
            min-height: 80px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        
        .application-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .submit-application-btn {
            background: #FD8B51;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .submit-application-btn:hover {
            background: #CB6040;
        }
        
        .submit-application-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .save-draft-btn {
            background: transparent;
            color: #666;
            border: 1px solid #ddd;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .save-draft-btn:hover {
            background: #f5f5f5;
        }
        
        .no-resume-message {
            text-align: center;
            padding: 20px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            color: #856404;
        }
        
        .already-applied-message {
            text-align: center;
            padding: 40px 20px;
        }
        
        .already-applied-message h4 {
            margin: 0 0 12px 0;
            color: #333;
            font-size: 18px;
        }
        
        .already-applied-message p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .job-details-modal-content {
                margin: 10px;
                max-height: 95vh;
            }
            
            .accommodations-grid {
                grid-template-columns: 1fr;
            }
            
            .job-details-footer {
                flex-direction: column;
            }
            
            .save-job-btn, .apply-job-btn {
                width: 100%;
                justify-content: center;
            }
            
            .job-stats-detailed {
                flex-direction: column;
                gap: 10px;
            }
            
            /* Application Modal Mobile */
            .application-modal {
                padding: 10px;
            }
            
            .application-modal-content {
                max-height: 95vh;
            }
            
            .application-modal-body {
                padding: 16px;
            }
            
            .application-modal-footer {
                flex-direction: column;
                padding: 16px;
            }
            
            .submit-application-btn,
            .save-draft-btn {
                width: 100%;
                justify-content: center;
            }
            
            .materials-options {
                gap: 12px;
            }
        }
    `;
    
    document.head.appendChild(css);
}

// ===================================================================
// GLOBAL FUNCTIONS FOR ONCLICK HANDLERS
// ===================================================================

window.closeJobDetailsModal = closeJobDetailsModal;
window.showApplicationModal = showApplicationModal;
window.toggleSaveJob = toggleSaveJob;
window.viewResume = viewResume;



console.log('ThisAble Job Listings - Unified Version Loaded! 🚀');

