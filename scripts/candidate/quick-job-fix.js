// ===================================================================
// CLEAN LAYOUT FIX - REMOVES DUPLICATES AND FIXES LAYOUT
// ===================================================================

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(cleanUpLayout, 2000);
});

function cleanUpLayout() {
    console.log('Cleaning up job listings layout...');
    
    // Remove all existing enhancement CSS
    removeExistingEnhancements();
    
    // Add clean CSS
    addCleanLayoutCSS();
    
    // Clean up existing cards
    cleanUpJobCards();
    
    // Add single enhancement per card
    addSingleEnhancement();
    
    // Add TTS and Voice Search back
    addTTSFeatures();
    addVoiceSearchFeatures();
}

function removeExistingEnhancements() {
    // Remove all enhancement CSS
    const existingCSS = document.querySelectorAll('#quick-fix-css, #clean-job-css, #final-fix-css, #simple-match-css, #dynamic-match-css, #real-data-fix-css, #safe-fix-css, #zero-backend-css, #safe-enhancement-css');
    existingCSS.forEach(css => css.remove());
    
    // Remove all enhancement buttons
    const duplicateButtons = document.querySelectorAll('.safe-view-details, .view-details-btn, .expand-btn, .enhanced-view-btn, .dynamic-details-btn, .real-details-btn, .safe-details-btn, .zero-details-btn, .safe-view-details-btn');
    duplicateButtons.forEach(btn => btn.remove());
    
    // Remove enhanced classes
    const cards = document.querySelectorAll('.job-card');
    cards.forEach(card => {
        card.classList.remove('enhanced-card', 'enhanced', 'dynamic-fixed', 'safe-fixed', 'zero-fixed', 'real-data-fixed');
        card.style.position = '';
        card.style.minHeight = '';
    });
    
    console.log('Cleaned up existing enhancements');
}

function addCleanLayoutCSS() {
    const css = document.createElement('style');
    css.id = 'clean-layout-css';
    css.textContent = `
        /* CLEAN LAYOUT CSS */
        .jobs-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)) !important;
            gap: 24px !important;
            padding: 20px 0 !important;
        }
        
        .job-card {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            padding: 24px !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 400px !important;
            border: 1px solid #e9ecef !important;
        }
        
        .job-card:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important;
        }
        
        .job-card-header {
            margin-bottom: 16px !important;
        }
        
        .job-title {
            font-size: 20px !important;
            font-weight: 600 !important;
            color: #333333 !important;
            margin-bottom: 8px !important;
            line-height: 1.3 !important;
        }
        
        .company-name {
            color: #666666 !important;
            font-size: 14px !important;
            margin-bottom: 8px !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        
        .location-pill {
            color: #999999 !important;
            font-size: 13px !important;
            margin-bottom: 12px !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        
        .job-tags {
            display: flex !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
            margin-bottom: 16px !important;
        }
        
        .job-tag {
            background: #f5f5f5 !important;
            color: #333333 !important;
            padding: 4px 10px !important;
            border-radius: 12px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        
        .job-tag.salary {
            background: #F2E5BF !important;
            color: #257180 !important;
        }
        
        .job-card-body {
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        .job-description {
            color: #666666 !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            margin-bottom: 16px !important;
            flex: 1 !important;
        }
        
        .accessibility-features {
            margin-bottom: 16px !important;
        }
        
        .feature-title {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #FD8B51 !important;
            margin-bottom: 8px !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        
        .features-list {
            display: flex !important;
            gap: 8px !important;
            flex-wrap: wrap !important;
        }
        
        .feature-badge {
            background: rgba(253, 139, 81, 0.1) !important;
            color: #FD8B51 !important;
            padding: 4px 8px !important;
            border-radius: 8px !important;
            font-size: 11px !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
            border: 1px solid rgba(253, 139, 81, 0.2) !important;
        }
        
        /* SINGLE VIEW DETAILS BUTTON */
        .clean-view-details-btn {
            background: linear-gradient(135deg, #257180, #2F8A99) !important;
            color: white !important;
            border: none !important;
            padding: 10px 16px !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            margin-bottom: 12px !important;
            transition: all 0.2s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            width: 100% !important;
        }
        
        .clean-view-details-btn:hover {
            background: linear-gradient(135deg, #2F8A99, #257180) !important;
            transform: translateY(-1px) !important;
        }
        
        .apply-btn {
            background: linear-gradient(135deg, #FD8B51, #CB6040) !important;
            color: white !important;
            border: none !important;
            padding: 12px 20px !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            margin-bottom: 12px !important;
            transition: all 0.2s ease !important;
            width: 100% !important;
        }
        
        .apply-btn:hover {
            background: linear-gradient(135deg, #CB6040, #FD8B51) !important;
            transform: translateY(-1px) !important;
        }
        
        /* APPLIED BUTTON STATE */
        .apply-btn.applied {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            cursor: default !important;
            position: relative !important;
        }
        
        .apply-btn.applied:hover {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
            transform: none !important;
        }
        
        .apply-btn.applied:before {
            content: "✓" !important;
            margin-right: 8px !important;
            font-weight: bold !important;
        }
        
        .job-card-footer {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-top: 12px !important;
            padding-top: 12px !important;
            border-top: 1px solid #e0e0e0 !important;
        }
        
        .job-posted {
            font-size: 12px !important;
            color: #999999 !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        
        .job-stats {
            display: flex !important;
            gap: 12px !important;
            font-size: 12px !important;
            color: #666666 !important;
        }
        
        .job-stats .stat {
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
        }
        
        .job-actions {
            display: flex !important;
            gap: 8px !important;
        }
        
        .action-btn {
            background: none !important;
            border: none !important;
            cursor: pointer !important;
            color: #666666 !important;
            font-size: 16px !important;
            padding: 6px !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
        }
        
        .action-btn:hover {
            background: #f5f5f5 !important;
            color: #333333 !important;
        }
        
        .action-btn.saved {
            color: #257180 !important;
        }
        
        /* TTS and Voice Search Styles */
        .tts-btn:hover {
            background: rgba(253, 139, 81, 0.9) !important;
            transform: scale(1.1) !important;
        }
        
        .voice-search-btn:hover {
            background: #FD8B51 !important;
            transform: translateY(-50%) scale(1.1) !important;
        }
        
        .voice-search-btn.listening {
            background: #dc3545 !important;
            animation: pulse 1s infinite !important;
        }
        
        #floating-tts-btn:hover {
            background: #FD8B51 !important;
            transform: scale(1.1) !important;
        }
        
        /* CLEAN MODAL */
        .clean-job-modal {
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
        
        .clean-modal-content {
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
        
        .clean-modal-header {
            background: linear-gradient(135deg, #257180, #2F8A99);
            color: white;
            padding: 24px;
            border-radius: 12px 12px 0 0;
            position: relative;
        }
        
        .clean-modal-header h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .clean-modal-header .company-info {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .clean-modal-close {
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
        
        .clean-modal-body {
            padding: 24px;
        }
        
        .clean-section {
            margin-bottom: 24px;
        }
        
        .clean-section h3 {
            color: #333333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
        }
        
        .clean-section .content {
            color: #666666;
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
        }
        
        .clean-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        
        .clean-tag {
            background: #257180;
            color: white;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .clean-accommodations {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        
        .clean-accommodation {
            background: rgba(253, 139, 81, 0.1);
            border: 1px solid rgba(253, 139, 81, 0.3);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            color: #FD8B51;
            font-size: 14px;
            font-weight: 500;
        }
        
        .clean-modal-footer {
            padding: 24px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .clean-modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .clean-modal-btn.save {
            background: transparent;
            color: #257180;
            border: 2px solid #257180;
        }
        
        .clean-modal-btn.save:hover {
            background: #257180;
            color: white;
        }
        
        .clean-modal-btn.apply {
            background: #FD8B51;
            color: white;
        }
        
        .clean-modal-btn.apply:hover {
            background: #CB6040;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .jobs-grid {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
                padding: 16px 0 !important;
            }
            
            .job-card {
                min-height: auto !important;
                padding: 20px !important;
            }
            
            .clean-modal-content {
                margin: 10px !important;
                max-height: 95vh !important;
            }
            
            .clean-accommodations {
                grid-template-columns: 1fr !important;
            }
            
            .clean-modal-footer {
                flex-direction: column !important;
            }
            
            .clean-modal-btn {
                width: 100% !important;
            }
        }
    `;
    
    document.head.appendChild(css);
}

function cleanUpJobCards() {
    const cards = document.querySelectorAll('.job-card');
    
    cards.forEach(card => {
        // Remove duplicate buttons
        const existingButtons = card.querySelectorAll('.safe-view-details, .view-details-btn, .expand-btn, .enhanced-view-btn, .dynamic-details-btn, .real-details-btn, .safe-details-btn, .zero-details-btn, .safe-view-details-btn, .clean-view-details-btn');
        existingButtons.forEach(btn => btn.remove());
        
        // Reset card styles
        card.style.position = '';
        card.style.minHeight = '';
        card.style.transform = '';
        
        // Remove enhanced classes
        card.classList.remove('enhanced-card', 'enhanced', 'dynamic-fixed', 'safe-fixed', 'zero-fixed', 'real-data-fixed');
    });
}

function addSingleEnhancement() {
    const cards = document.querySelectorAll('.job-card');
    
    cards.forEach(card => {
        if (card.classList.contains('clean-enhanced')) return;
        card.classList.add('clean-enhanced');
        
        const jobBody = card.querySelector('.job-card-body');
        const applyBtn = card.querySelector('.apply-btn');
        
        if (!jobBody || !applyBtn) return;
        
        // Check if user has already applied to this job
        const hasApplied = card.dataset.hasApplied === '1' || card.getAttribute('data-has-applied') === '1';
        const applicationStatus = card.dataset.applicationStatus || card.getAttribute('data-application-status');
        
        // Update apply button based on application status
        updateApplyButtonState(applyBtn, hasApplied, applicationStatus);
        
        // Extract job data
        const jobId = card.dataset.jobId || card.getAttribute('data-job-id');
        const jobTitle = card.querySelector('.job-title')?.textContent || 'Job Position';
        const companyName = card.querySelector('.company-name')?.textContent?.replace(/^\s*[^\w]*\s*/, '') || 'Company';
        const location = card.querySelector('.location-pill')?.textContent?.replace(/^\s*[^\w]*\s*/, '') || 'Location';
        
        // Create single view details button
        const viewBtn = document.createElement('button');
        viewBtn.className = 'clean-view-details-btn';
        viewBtn.innerHTML = '<i class="fas fa-info-circle"></i> View Full Details';
        
        viewBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            showCleanModal(card);
        });
        
        // Insert before apply button
        jobBody.insertBefore(viewBtn, applyBtn);
    });
}

function updateApplyButtonState(applyBtn, hasApplied, applicationStatus) {
    if (hasApplied) {
        // User has already applied
        applyBtn.classList.add('applied');
        
        // Update button text based on application status
        let buttonText = 'Applied';
        let statusColor = '#28a745'; // Default green
        
        switch(applicationStatus) {
            case 'submitted':
                buttonText = 'Application Submitted';
                statusColor = '#28a745';
                break;
            case 'under_review':
                buttonText = 'Under Review';
                statusColor = '#FD8B51';
                break;
            case 'shortlisted':
                buttonText = 'Shortlisted';
                statusColor = '#8E44AD';
                break;
            case 'interview_scheduled':
                buttonText = 'Interview Scheduled';
                statusColor = '#8E44AD';
                break;
            case 'interviewed':
                buttonText = 'Interviewed';
                statusColor = '#8E44AD';
                break;
            case 'hired':
                buttonText = 'Hired!';
                statusColor = '#28a745';
                break;
            case 'rejected':
                buttonText = 'Not Selected';
                statusColor = '#dc3545';
                break;
            case 'withdrawn':
                buttonText = 'Withdrawn';
                statusColor = '#6c757d';
                break;
            default:
                buttonText = 'Applied';
        }
        
        applyBtn.innerHTML = buttonText;
        applyBtn.style.background = `linear-gradient(135deg, ${statusColor}, ${statusColor}dd)`;
        
        // Change click behavior for applied jobs
        applyBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (typeof showNotification === 'function') {
                showNotification(`You have already applied to this position. Status: ${buttonText}`, 'info');
            } else {
                alert(`You have already applied to this position.\nCurrent status: ${buttonText}`);
            }
        };
        
        // Add tooltip
        applyBtn.title = `Application status: ${buttonText}`;
        
    } else {
        // User hasn't applied yet - normal apply button
        applyBtn.classList.remove('applied');
        applyBtn.innerHTML = 'Apply Now';
        applyBtn.style.background = '';
        applyBtn.title = 'Click to apply for this position';
        
        // Keep original apply functionality
        // The existing joblistings.js should handle the apply click
    }
}

function showCleanModal(card) {
    // Extract all data from card
    const jobId = card.dataset.jobId || card.getAttribute('data-job-id');
    const jobTitle = card.querySelector('.job-title')?.textContent || 'Job Position';
    const companyName = card.querySelector('.company-name')?.textContent?.replace(/^\s*[^\w]*\s*/, '') || 'Company';
    const location = card.querySelector('.location-pill')?.textContent?.replace(/^\s*[^\w]*\s*/, '') || 'Location';
    const description = card.querySelector('.job-description')?.textContent || '';
    
    // Get tags
    const tags = [];
    card.querySelectorAll('.job-tag').forEach(tag => {
        const tagText = tag.textContent.trim().replace(/^\s*[^\w]*\s*/, '');
        if (tagText) tags.push(tagText);
    });
    
    // Get accommodations
    const accommodations = [];
    card.querySelectorAll('.feature-badge').forEach(badge => {
        const accText = badge.textContent.trim();
        if (accText) accommodations.push(accText);
    });
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'clean-job-modal';
    modal.innerHTML = `
        <div class="clean-modal-content">
            <div class="clean-modal-header">
                <h2>${escapeHtml(jobTitle)}</h2>
                <div class="company-info">${escapeHtml(companyName)} • ${escapeHtml(location)}</div>
                <button class="clean-modal-close">&times;</button>
            </div>
            <div class="clean-modal-body">
                <div class="clean-section">
                    <h3>Job Information</h3>
                    <div class="content">
                        <p><strong>Position:</strong> ${escapeHtml(jobTitle)}</p>
                        <p><strong>Company:</strong> ${escapeHtml(companyName)}</p>
                        <p><strong>Location:</strong> ${escapeHtml(location)}</p>
                    </div>
                    <div class="clean-tags">
                        ${tags.map(tag => `<span class="clean-tag">${escapeHtml(tag)}</span>`).join('')}
                    </div>
                </div>
                
                <div class="clean-section">
                    <h3>Job Description</h3>
                    <div class="content">
                        ${description ? escapeHtml(description) : `Join our team as a ${escapeHtml(jobTitle)} where you'll contribute to an inclusive workplace that values diversity and provides equal opportunities for all employees to grow and succeed.`}
                    </div>
                </div>
                
                <div class="clean-section">
                    <h3>PWD Accommodations</h3>
                    <div class="clean-accommodations">
                        ${accommodations.length > 0 ? 
                            accommodations.map(acc => `<div class="clean-accommodation">${escapeHtml(acc)}</div>`).join('') :
                            `<div class="clean-accommodation">PWD-Friendly Workplace</div>
                             <div class="clean-accommodation">Inclusive Environment</div>
                             <div class="clean-accommodation">Equal Opportunities</div>`
                        }
                    </div>
                </div>
                
                <div class="clean-section">
                    <h3>About ${escapeHtml(companyName)}</h3>
                    <div class="content">
                        ${escapeHtml(companyName)} is committed to creating an inclusive workplace where all employees, including persons with disabilities, can thrive and contribute their best work. We believe in equal opportunities and provide comprehensive support for professional growth.
                    </div>
                </div>
            </div>
            <div class="clean-modal-footer">
                <button class="clean-modal-btn save" onclick="closeCleanModal()">
                    <i class="fas fa-bookmark"></i> Save Job
                </button>
                <button class="clean-modal-btn apply" onclick="applyCleanJob('${jobId}')">
                    <i class="fas fa-paper-plane"></i> Apply Now
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    
    // Add close functionality
    modal.querySelector('.clean-modal-close').addEventListener('click', closeCleanModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeCleanModal();
    });
    
    document.body.style.overflow = 'hidden';
}

function closeCleanModal() {
    const modal = document.querySelector('.clean-job-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function applyCleanJob(jobId) {
    closeCleanModal();
    
    // Use existing apply function if available
    if (typeof showApplicationModal === 'function') {
        const card = document.querySelector(`[data-job-id="${jobId}"]`);
        if (card) {
            const jobData = {
                job_id: jobId,
                job_title: card.querySelector('.job-title')?.textContent || 'Job',
                company_name: card.querySelector('.company-name')?.textContent?.replace(/^\s*[^\w]*\s*/, '') || 'Company'
            };
            showApplicationModal(jobData);
            return;
        }
    }
    
    if (typeof showNotification === 'function') {
        showNotification('Redirecting to application...', 'success');
    } else {
        alert('Application process will begin...');
    }
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

// Make functions global
window.closeCleanModal = closeCleanModal;
window.applyCleanJob = applyCleanJob;

// ===================================================================
// TEXT-TO-SPEECH FUNCTIONALITY
// ===================================================================

function addTTSFeatures() {
    console.log('Adding TTS features...');
    
    // Only add if TTS is supported
    if (!('speechSynthesis' in window)) {
        console.log('TTS not supported');
        return;
    }
    
    // Add TTS button to each job card
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach(addTTSToJobCard);
    
    // Add floating TTS control
    addFloatingTTSControl();
}

function addTTSToJobCard(jobCard) {
    // Don't add if already has TTS button
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
    
    // Add to top-right corner
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
        // Stop any current speech
        speechSynthesis.cancel();
        
        // Extract text safely
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
    // Don't add if already exists
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
    
    floatingBtn.addEventListener('click', function() {
        readPageSummary();
    });
    
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

function addVoiceSearchFeatures() {
    console.log('Adding voice search features...');
    
    // Check if Web Speech API is supported
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.log('Voice search not supported');
        return;
    }
    
    // Find existing search input
    const searchInput = document.getElementById('job-search') || document.querySelector('input[placeholder*="search"]');
    if (!searchInput) {
        console.log('Search input not found');
        return;
    }
    
    // Add voice button to search
    addVoiceButtonToSearch(searchInput);
}

function addVoiceButtonToSearch(searchInput) {
    // Don't add if already has voice button
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
    
    // Make sure search input parent is positioned
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
    
    // Change button to listening state
    voiceBtn.classList.add('listening');
    voiceBtn.style.background = '#dc3545';
    voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
    voiceBtn.title = 'Stop listening';
    
    // Change search placeholder
    const originalPlaceholder = searchInput.placeholder;
    searchInput.placeholder = 'Listening... speak now';
    searchInput.style.borderColor = '#dc3545';
    
    // Add listening animation
    voiceBtn.style.animation = 'pulse 1s infinite';
    
    recognition.onstart = function() {
        console.log('Voice recognition started');
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        console.log('Voice result:', transcript);
        
        // Set the search value
        searchInput.value = transcript;
        
        // Trigger search by dispatching input event
        const inputEvent = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(inputEvent);
        
        // Show success feedback
        if (typeof showNotification === 'function') {
            showNotification(`Searching for: "${transcript}"`, 'success');
        }
    };
    
    recognition.onerror = function(event) {
        console.error('Voice recognition error:', event.error);
        
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
        
        if (typeof showNotification === 'function') {
            showNotification(message, 'error');
        }
    };
    
    recognition.onend = function() {
        console.log('Voice recognition ended');
        
        // Reset button state
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#2F8A99';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceBtn.style.animation = 'none';
        voiceBtn.title = 'Voice Search';
        
        // Reset search input
        searchInput.placeholder = originalPlaceholder;
        searchInput.style.borderColor = '';
    };
    
    // Start recognition
    try {
        recognition.start();
    } catch (error) {
        console.error('Failed to start voice recognition:', error);
        
        // Reset button if start fails
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#2F8A99';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceBtn.style.animation = 'none';
        searchInput.placeholder = originalPlaceholder;
        searchInput.style.borderColor = '';
        
        if (typeof showNotification === 'function') {
            showNotification('Could not start voice search', 'error');
        }
    }
}

// ===================================================================
// KEYBOARD SHORTCUTS FOR ACCESSIBILITY
// ===================================================================

document.addEventListener('keydown', function(e) {
    // Don't activate shortcuts when typing
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    // Ctrl + R: Read page summary
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        readPageSummary();
    }
    
    // Ctrl + S: Stop speech
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        speechSynthesis.cancel();
    }
    
    // Ctrl + V: Voice search
    if (e.ctrlKey && e.key === 'v') {
        e.preventDefault();
        const searchInput = document.getElementById('job-search');
        const voiceBtn = document.querySelector('.voice-search-btn');
        if (searchInput && voiceBtn) {
            voiceBtn.click();
        }
    }
});

// Add pulse animation for voice button
const pulseCSS = document.createElement('style');
pulseCSS.textContent = `
    @keyframes pulse {
        0% { opacity: 1; transform: translateY(-50%) scale(1); }
        50% { opacity: 0.7; transform: translateY(-50%) scale(1.1); }
        100% { opacity: 1; transform: translateY(-50%) scale(1); }
    }
`;
document.head.appendChild(pulseCSS);

console.log('Clean layout applied successfully!');