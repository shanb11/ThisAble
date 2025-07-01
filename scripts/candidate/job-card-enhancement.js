// ===================================================================
// ULTRA SAFE JOB CARD ENHANCEMENT - NON-BREAKING VERSION
// ===================================================================

// Only enhance if everything is loaded properly
document.addEventListener('DOMContentLoaded', function() {
    // Wait for all existing scripts to complete
    setTimeout(function() {
        try {
            initSafeEnhancements();
        } catch (error) {
            console.error('Enhancement error:', error);
            // Continue normally even if enhancement fails
        }
    }, 5000); // Longer delay to ensure everything loads
});

function initSafeEnhancements() {
    console.log('Initializing safe job card enhancements...');
    
    // Check if job cards exist first
    const existingCards = document.querySelectorAll('.job-card');
    if (existingCards.length === 0) {
        console.log('No job cards found yet, will retry...');
        setTimeout(initSafeEnhancements, 2000);
        return;
    }
    
    console.log(`Found ${existingCards.length} job cards to enhance`);
    
    // Add CSS enhancements
    addSafeCSS();
    
    // Enhance existing cards one by one safely
    existingCards.forEach((card, index) => {
        setTimeout(() => {
            try {
                enhanceCardSafely(card);
            } catch (error) {
                console.error(`Error enhancing card ${index}:`, error);
                // Continue with next card even if one fails
            }
        }, index * 100); // Stagger enhancements
    });
}

// ===================================================================
// SAFE CSS ADDITIONS - NO OVERRIDES
// ===================================================================

function addSafeCSS() {
    // Don't add CSS if already exists
    if (document.getElementById('safe-job-enhancement-css')) return;
    
    const css = document.createElement('style');
    css.id = 'safe-job-enhancement-css';
    css.textContent = `
        /* Safe CSS that doesn't override existing styles */
        
        /* Fixed card heights - only if not already set */
        .jobs-grid:not([style*="grid-template-columns"]) {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            align-items: stretch;
        }
        
        /* Enhanced job cards */
        .job-card.enhanced-card {
            min-height: 400px;
            transition: all 0.3s ease;
        }
        
        .job-card.enhanced-card.expanded {
            min-height: auto;
            max-height: none;
            transform: scale(1.01);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            z-index: 50;
            position: relative;
        }
        
        /* View Details Button */
        .safe-view-details {
            background: linear-gradient(135deg, #257180, #2F8A99);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            margin: 8px 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }
        
        .safe-view-details:hover {
            background: linear-gradient(135deg, #FD8B51, #CB6040);
            transform: translateY(-1px);
        }
        
        .safe-view-details .detail-icon {
            transition: transform 0.2s ease;
        }
        
        .expanded .safe-view-details .detail-icon {
            transform: rotate(180deg);
        }
        
        /* Safe expanded content */
        .safe-expanded-content {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            animation: safeSlideDown 0.3s ease;
        }
        
        .expanded .safe-expanded-content {
            display: block;
        }
        
        @keyframes safeSlideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 500px;
            }
        }
        
        .safe-expanded-section {
            margin-bottom: 15px;
        }
        
        .safe-expanded-section h4 {
            color: #257180;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .safe-expanded-section h4 i {
            color: #FD8B51;
            font-size: 12px;
        }
        
        .safe-full-description {
            line-height: 1.5;
            color: #555;
            font-size: 13px;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .safe-company-preview {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        
        .safe-company-preview h5 {
            margin: 0 0 6px 0;
            color: #257180;
            font-size: 13px;
            font-weight: 600;
        }
        
        .safe-company-preview p {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #666;
        }
        
        .safe-company-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.2s ease;
        }
        
        .safe-company-btn:hover {
            background: #0056b3;
        }
        
        /* Safe modal styles */
        .safe-company-modal {
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
            box-sizing: border-box;
        }
        
        .safe-modal-content {
            background: white;
            border-radius: 8px;
            max-width: 500px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }
        
        .safe-modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
        }
        
        .safe-modal-header h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        
        .safe-modal-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            padding: 4px;
        }
        
        .safe-modal-body {
            padding: 20px;
        }
        
        .safe-company-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .safe-company-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        
        .safe-logo-placeholder {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 16px;
        }
        
        .safe-company-info h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
            color: #333;
        }
        
        .safe-company-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        
        .safe-company-section {
            margin-bottom: 15px;
        }
        
        .safe-company-section h5 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #257180;
            font-weight: 600;
        }
        
        .safe-company-section p {
            margin: 0;
            line-height: 1.4;
            color: #555;
            font-size: 13px;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .jobs-grid:not([style*="grid-template-columns"]) {
                grid-template-columns: 1fr;
            }
            
            .job-card.enhanced-card {
                min-height: auto;
            }
            
            .safe-company-modal {
                padding: 10px;
            }
            
            .safe-modal-content {
                max-height: 95vh;
            }
        }
    `;
    
    document.head.appendChild(css);
}

// ===================================================================
// SAFE CARD ENHANCEMENT
// ===================================================================

function enhanceCardSafely(card) {
    // Don't enhance if already enhanced
    if (card.classList.contains('enhanced-card')) return;
    
    // Mark as enhanced
    card.classList.add('enhanced-card');
    
    // Find job card body
    const jobBody = card.querySelector('.job-card-body');
    if (!jobBody) return;
    
    // Find the apply button to insert before it
    const applyBtn = jobBody.querySelector('.apply-btn');
    if (!applyBtn) return;
    
    // Get job data
    const jobId = card.dataset.jobId || card.getAttribute('data-job-id');
    const jobTitle = card.querySelector('.job-title')?.textContent || 'Job';
    const companyName = card.querySelector('.company-name')?.textContent || 'Company';
    const description = card.querySelector('.job-description')?.textContent || '';
    
    // Create view details button
    const viewDetailsBtn = document.createElement('button');
    viewDetailsBtn.className = 'safe-view-details';
    viewDetailsBtn.innerHTML = `
        <span>View Details</span>
        <i class="fas fa-chevron-down detail-icon"></i>
    `;
    
    // Create expanded content
    const expandedContent = document.createElement('div');
    expandedContent.className = 'safe-expanded-content';
    expandedContent.innerHTML = `
        <div class="safe-expanded-section">
            <h4><i class="fas fa-align-left"></i> Full Description</h4>
            <div class="safe-full-description">${escapeHtmlSafe(description)}</div>
        </div>
        
        <div class="safe-expanded-section">
            <h4><i class="fas fa-building"></i> About ${escapeHtmlSafe(companyName)}</h4>
            <div class="safe-company-preview">
                <h5>Company Information</h5>
                <p>Learn more about ${escapeHtmlSafe(companyName)} and their workplace culture.</p>
                <button class="safe-company-btn" onclick="showSafeCompanyModal('${jobId}', '${escapeHtmlSafe(companyName)}')">
                    <i class="fas fa-external-link-alt"></i> View Company Profile
                </button>
            </div>
        </div>
    `;
    
    // Add click handler for view details button
    viewDetailsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        toggleSafeExpansion(card, viewDetailsBtn);
    });
    
    // Insert elements before apply button
    jobBody.insertBefore(viewDetailsBtn, applyBtn);
    jobBody.insertBefore(expandedContent, applyBtn);
}

function toggleSafeExpansion(card, button) {
    const isExpanded = card.classList.contains('expanded');
    const buttonText = button.querySelector('span');
    const icon = button.querySelector('.detail-icon');
    
    if (isExpanded) {
        // Collapse
        card.classList.remove('expanded');
        buttonText.textContent = 'View Details';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    } else {
        // Expand
        card.classList.add('expanded');
        buttonText.textContent = 'Hide Details';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        
        // Scroll to card after expansion
        setTimeout(() => {
            card.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start',
                inline: 'nearest'
            });
        }, 300);
    }
}

// ===================================================================
// SAFE COMPANY MODAL
// ===================================================================

function showSafeCompanyModal(jobId, companyName) {
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'safe-company-modal';
    modal.innerHTML = `
        <div class="safe-modal-content">
            <div class="safe-modal-header">
                <h3>Company Profile</h3>
                <button class="safe-modal-close">&times;</button>
            </div>
            <div class="safe-modal-body">
                <div class="safe-company-header">
                    <div class="safe-logo-placeholder">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="safe-company-info">
                        <h4>${escapeHtmlSafe(companyName)}</h4>
                        <p>Technology Company</p>
                    </div>
                </div>
                
                <div class="safe-company-section">
                    <h5>About Us</h5>
                    <p>We are an innovative company committed to creating an inclusive workplace where all employees can thrive. Our team values diversity and provides comprehensive support for employees with disabilities.</p>
                </div>
                
                <div class="safe-company-section">
                    <h5>Our Commitment</h5>
                    <p>We believe in equal opportunities and maintain accessible facilities, flexible work arrangements, and supportive management practices.</p>
                </div>
                
                <div class="safe-company-section">
                    <h5>Workplace Accommodations</h5>
                    <p>• Accessible office facilities<br>
                    • Flexible working hours<br>
                    • Assistive technology support<br>
                    • Remote work options</p>
                </div>
            </div>
        </div>
    `;
    
    // Add close functionality
    const closeBtn = modal.querySelector('.safe-modal-close');
    closeBtn.addEventListener('click', () => {
        modal.remove();
    });
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    // Show modal
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    
    // Try to load real company data
    if (jobId) {
        loadRealCompanyData(modal, jobId);
    }
}

async function loadRealCompanyData(modal, jobId) {
    try {
        // Try to get real company data
        const response = await fetch(`../../backend/candidate/get_application_data.php?job_id=${jobId}`);
        const data = await response.json();
        
        if (data.success && data.data && data.data.job) {
            const job = data.data.job;
            updateModalWithRealData(modal, job);
        }
    } catch (error) {
        console.log('Could not load real company data, using fallback');
    }
}

function updateModalWithRealData(modal, job) {
    const companyInfo = modal.querySelector('.safe-company-info');
    if (companyInfo && job.company_name) {
        companyInfo.querySelector('h4').textContent = job.company_name;
        if (job.industry) {
            companyInfo.querySelector('p').textContent = job.industry;
        }
    }
}

// ===================================================================
// SAFE UTILITY FUNCTIONS
// ===================================================================

function escapeHtmlSafe(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make function globally available
window.showSafeCompanyModal = showSafeCompanyModal;

console.log('Ultra safe job card enhancement initialized!');