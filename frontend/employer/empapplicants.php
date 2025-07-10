<?php
//session_start();
require_once '../../backend/shared/session_helper.php';


echo "<!-- AFTER SESSION HELPER: ";
echo "Status: " . session_status() . ", ";
echo "ID: " . session_id() . ", ";
echo "employer_id: " . ($_SESSION['employer_id'] ?? 'NOT SET') . ", ";
echo "logged_in: " . ($_SESSION['logged_in'] ?? 'NOT SET');
echo " -->";

// Check if employer is logged in
requireEmployerLogin('emplogin.php');
// Check if employer is logged in
requireEmployerLogin('emplogin.php');

// ADD THIS LINE TO DEFINE THE VARIABLE
$application_id = ''; // Will be set via JavaScript
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Applicants | Employer Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/employer/empapplicants.css">
        <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
    </head>
    <body>
        <!-- Output session data for JavaScript -->
        <?php echoEmployerSessionScript(); ?>
        
        <!-- Sidebar -->
        <?php include('../../includes/employer/empsidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="search-bar">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search applicants by name, skills..." id="search-input">
                </div>
                <div class="notification-icons">
                    <a href="empnotifications.php">
                        <i class="far fa-bell" title="View Notifications"></i>
                    </a>
                </div>
            </div>

            <div class="page-header">
                <div class="page-title">
                    <h1>Applicants</h1>
                    <p>Manage candidates who applied for your job postings</p>
                </div>
                
                <!-- Quick Stats (Optional - will be populated by JS if elements exist) -->
                <div class="quick-stats" style="display: none;">
                    <div class="stat-item">
                        <span class="stat-number" id="total-applications">0</span>
                        <span class="stat-label">Total Applications</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="new-applications">0</span>
                        <span class="stat-label">New</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="interviews-scheduled">0</span>
                        <span class="stat-label">Interviews</span>
                    </div>
                </div>
            </div>

            <!-- Add this to your empapplicants.php file, after the page-header div -->

            <!-- Match Calculation Control Panel -->
            <div class="match-control-panel" style="margin: 20px 0; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <div class="control-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                        <h3 style="margin: 0; font-size: 16px; color: #333;">
                            <i class="fas fa-calculator"></i> Job Matching System
                        </h3>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: #666;">
                            Calculate compatibility scores for all applicants
                        </p>
                    </div>
                    <div class="match-actions">
                        <button class="calc-btn" id="calculate-matches-btn" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-sync"></i>
                            <span>Calculate Matches</span>
                        </button>
                    </div>
                </div>
    
                <!-- Match Statistics Display -->
                <div class="match-stats" id="match-stats" style="display: none;">
                    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px;">
                        <div class="stat-card excellent" style="padding: 12px; background: #d1fae5; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="excellent-count" style="font-size: 20px; font-weight: bold; color: #065f46;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #065f46;">Excellent (90%+)</div>
                        </div>
                        <div class="stat-card good" style="padding: 12px; background: #dbeafe; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="good-count" style="font-size: 20px; font-weight: bold; color: #1e40af;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #1e40af;">Good (75-89%)</div>
                        </div>
                        <div class="stat-card fair" style="padding: 12px; background: #fef3c7; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="fair-count" style="font-size: 20px; font-weight: bold; color: #92400e;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #92400e;">Fair (60-74%)</div>
                        </div>
                        <div class="stat-card poor" style="padding: 12px; background: #fee2e2; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="poor-count" style="font-size: 20px; font-weight: bold; color: #991b1b;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #991b1b;">Needs Review</div>
                        </div>
                    </div>
                    <div class="overall-stats" style="margin-top: 12px; display: flex; justify-content: space-between; font-size: 14px; color: #666;">
                        <span>Average Score: <strong id="average-score">0%</strong></span>
                        <span>Processed: <strong id="processed-count">0</strong> applicants</span>
                    </div>
                </div>
                
                <!-- Progress Indicator -->
                <div class="calculation-progress" id="calculation-progress" style="display: none; margin-top: 12px;">
                    <div class="progress-bar" style="height: 4px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                        <div class="progress-fill" id="progress-fill" style="height: 100%; background: #3b82f6; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <div class="progress-text" id="progress-text" style="margin-top: 8px; font-size: 14px; color: #666;">
                        Calculating matches...
                    </div>
                </div>
            </div>

            <!-- Bulk Selection Header (Created dynamically by JS) -->
            <!-- Select All Container (Created dynamically by JS) -->
    
            <?php include('../../includes/employer/empapplicants_filter.php'); ?>

            <!-- Active Filters display -->
            <?php include('../../includes/employer/empapplicants_active.php'); ?>

            <!-- Applicants Grid - will be populated by JavaScript -->
            <div class="applicants-grid" id="applicants-grid">
                <!-- Loading placeholder -->
                <div class="loading-placeholder">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading applicants...</p>
                </div>
            </div>

            <!-- No Results State (Hidden by default) -->
            <div class="no-results" id="no-results" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No applicants found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>

            <!-- Success Message -->
            <div class="status-confirmation" id="status-confirmation">
                Status updated successfully!
            </div>

            <!-- Loading overlay -->
            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="spinner"></div>
            </div>
        </div>
        
        <!-- ===================================
             MODALS SECTION
             =================================== -->
        
        <!-- Applicant Profile Modal -->
        <?php include('../../modals/employer/empapplicants_applicant.php'); ?>

        <!-- Status update confirmation dialog -->
        <?php include('../../modals/employer/empapplicants_status_modal.php'); ?>

        <!-- Interview Scheduling Modals -->
        <?php include('../../modals/employer/schedule_interview_modal.php'); ?>

        <!-- Bulk Actions Modals -->
        <?php include('../../modals/employer/bulk_actions_modal.php'); ?>

        <!-- Category Results Modal - Add after existing modals -->
        <div class="modal" id="categoryModal" style="display: none;">
            <div class="modal-content category-modal">
                <div class="modal-header">
                    <h2 class="modal-title" id="categoryModalTitle">
                        <i class="fas fa-users"></i>
                        Match Results
                    </h2>
                    <button class="close-modal" data-modal="categoryModal" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="modal-body">
                    <!-- Category Navigation Tabs -->
                    <div class="category-tabs" id="categoryTabs">
                        <button class="category-tab active" data-category="excellent">
                            <span class="tab-icon">🔥</span>
                            <span class="tab-label">Excellent</span>
                            <span class="tab-count" id="excellent-tab-count">0</span>
                        </button>
                        <button class="category-tab" data-category="good">
                            <span class="tab-icon">🟢</span>
                            <span class="tab-label">Good</span>
                            <span class="tab-count" id="good-tab-count">0</span>
                        </button>
                        <button class="category-tab" data-category="fair">
                            <span class="tab-icon">🟡</span>
                            <span class="tab-label">Fair</span>
                            <span class="tab-count" id="fair-tab-count">0</span>
                        </button>
                        <button class="category-tab" data-category="needs-review">
                            <span class="tab-icon">⚠️</span>
                            <span class="tab-label">Needs Review</span>
                            <span class="tab-count" id="needs-review-tab-count">0</span>
                        </button>
                    </div>
                    
                    <!-- Category Content Area -->
                    <div class="category-content" id="categoryContent">
                        <div class="category-loading" id="categoryLoading">
                            <div class="loading-spinner"></div>
                            <p>Loading applicants...</p>
                        </div>
                        
                        <div class="category-empty" id="categoryEmpty" style="display: none;">
                            <div class="empty-icon">📭</div>
                            <h3>No Applicants Found</h3>
                            <p>There are no applicants in this category yet.</p>
                        </div>
                        
                        <div class="jobs-accordion" id="jobsAccordion">
                            <!-- Job accordions will be dynamically generated here -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button class="footer-btn secondary-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to Overview
                    </button>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <template id="jobAccordionTemplate">
            <div class="job-accordion">
                <div class="job-accordion-header">
                    <div class="job-info">
                        <h4 class="job-title">Job Title</h4>
                        <span class="job-applicant-count">0 applicants</span>
                    </div>
                    <div class="job-actions">
                        <span class="job-average-score">Avg: 0%</span>
                        <button class="accordion-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>
                <div class="job-accordion-content">
                    <div class="applicants-list"></div>
                </div>
            </div>
        </template>

        <template id="categoryApplicantTemplate">
            <div class="category-applicant-card">
                <div class="applicant-header">
                    <div class="applicant-avatar">
                        <img src="" alt="Avatar">
                        <div class="avatar-fallback">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <div class="applicant-info">
                        <h5 class="applicant-name">Applicant Name</h5>
                        <div class="applicant-score-container">
                            <span class="applicant-score">0%</span>
                            <span class="score-label">match</span>
                        </div>
                    </div>
                    <div class="applicant-actions">
                        <button class="action-btn view-resume-btn" title="View Resume">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="action-btn view-profile-btn" title="View Profile">
                            <i class="fas fa-user"></i>
                        </button>
                    </div>
                </div>
                
                <div class="applicant-details">
                    <div class="skills-section">
                        <h6>Skills Analysis</h6>
                        <div class="skills-match">
                            <div class="matched-skills">
                                <span class="skills-label">✅ Matched:</span>
                                <div class="skills-list matched"></div>
                            </div>
                            <div class="missing-skills">
                                <span class="skills-label">❌ Missing:</span>
                                <div class="skills-list missing"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resume-section">
                        <h6>Resume Preview</h6>
                        <div class="resume-preview">
                            <div class="resume-loading">Loading preview...</div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button class="footer-btn secondary-btn close-modal">Close</button>
                    <button class="footer-btn primary-btn" data-status="reviewed" data-application-id="">Mark as Reviewed</button>
                    <button class="footer-btn interview-btn" data-status="interview" data-application-id="">Schedule Interview</button>
                    <button class="footer-btn success-btn" data-status="hired" data-application-id="">Hire</button>
                    <button class="footer-btn danger-btn" data-status="rejected" data-application-id="">Reject</button>
                </div>
            </div>
        </template>

        <!-- Enhanced PDF/Resume Viewer Modal -->
        <div class="modal" id="resumeViewerModal" style="display: none;">
            <div class="modal-content resume-viewer-modal">
                <div class="modal-header resume-viewer-header">
                    <div class="resume-info">
                        <h2 class="modal-title" id="resumeViewerTitle">
                            <i class="fas fa-file-pdf"></i>
                            <span id="resumeApplicantName">Resume Viewer</span>
                        </h2>
                        <div class="resume-meta">
                            <span id="resumeJobTitle">Job Position</span>
                            <span class="separator">•</span>
                            <span id="resumeMatchScore">95% Match</span>
                        </div>
                    </div>
                    
                    <div class="resume-actions">
                        <button class="action-btn search-btn" id="resumeSearchBtn" title="Search in Resume">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="action-btn download-btn" id="resumeDownloadBtn" title="Download Resume">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="action-btn fullscreen-btn" id="resumeFullscreenBtn" title="Toggle Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button class="close-modal" data-modal="resumeViewerModal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="modal-body resume-viewer-body">
                    <!-- Search Panel (Hidden by default) -->
                    <div class="resume-search-panel" id="resumeSearchPanel" style="display: none;">
                        <div class="search-container">
                            <input type="text" id="resumeSearchInput" placeholder="Search in resume content..." />
                            <button class="search-execute-btn" id="resumeSearchExecute">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="search-close-btn" id="resumeSearchClose">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="search-results" id="resumeSearchResults"></div>
                    </div>
                    
                    <!-- Resume Content Area -->
                    <div class="resume-content-wrapper">
                        <!-- Left Panel: Resume Analysis -->
                        <div class="resume-analysis-panel" id="resumeAnalysisPanel">
                            <div class="analysis-section">
                                <h3><i class="fas fa-chart-line"></i> Resume Analysis</h3>
                                
                                <div class="match-overview">
                                    <div class="match-score-display">
                                        <div class="score-circle" id="resumeScoreCircle">
                                            <span class="score-number" id="resumeScoreNumber">95%</span>
                                            <span class="score-label">Match</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="skills-analysis">
                                    <h4><i class="fas fa-cogs"></i> Skills Analysis</h4>
                                    <div class="skills-section">
                                        <div class="skills-matched">
                                            <h5 class="skills-title">✅ Matched Skills</h5>
                                            <div class="skills-list" id="resumeMatchedSkills"></div>
                                        </div>
                                        <div class="skills-missing">
                                            <h5 class="skills-title">❌ Missing Skills</h5>
                                            <div class="skills-list" id="resumeMissingSkills"></div>
                                        </div>
                                        <div class="skills-bonus">
                                            <h5 class="skills-title">🎯 Bonus Skills</h5>
                                            <div class="skills-list" id="resumeBonusSkills"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="resume-insights">
                                    <h4><i class="fas fa-lightbulb"></i> Key Insights</h4>
                                    <div class="insights-list" id="resumeInsightsList">
                                        <div class="insight-item">
                                            <i class="fas fa-graduation-cap"></i>
                                            <span>Education background matches job requirements</span>
                                        </div>
                                        <div class="insight-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span>5+ years relevant work experience</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="quick-actions">
                                <button class="quick-action-btn interview-action" id="resumeScheduleInterview">
                                    <i class="fas fa-calendar"></i>
                                    Schedule Interview
                                </button>
                                <button class="quick-action-btn hire-action" id="resumeHireApplicant">
                                    <i class="fas fa-check"></i>
                                    Hire Applicant
                                </button>
                                <button class="quick-action-btn reject-action" id="resumeRejectApplicant">
                                    <i class="fas fa-times"></i>
                                    Reject
                                </button>
                            </div>
                        </div>
                        
                        <!-- Center Panel: PDF Viewer -->
                        <div class="resume-viewer-container">
                            <div class="resume-loading" id="resumeLoading">
                                <div class="loading-spinner"></div>
                                <p>Loading resume...</p>
                            </div>
                            
                            <div class="resume-error" id="resumeError" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Unable to Load Resume</h3>
                                <p id="resumeErrorMessage">The resume file could not be displayed.</p>
                                <button class="download-anyway-btn" id="resumeDownloadAnyway">
                                    <i class="fas fa-download"></i>
                                    Download File Instead
                                </button>
                            </div>
                            
                            <!-- PDF Viewer -->
                            <iframe 
                                id="resumePdfViewer" 
                                class="resume-pdf-frame"
                                style="display: none;"
                                title="Resume PDF Viewer">
                            </iframe>
                            
                            <!-- Text Content Viewer (for non-PDF resumes) -->
                            <div class="resume-text-viewer" id="resumeTextViewer" style="display: none;">
                                <div class="text-content" id="resumeTextContent"></div>
                            </div>
                            
                            <!-- Viewer Controls -->
                            <div class="resume-viewer-controls" id="resumeViewerControls" style="display: none;">
                                <button class="control-btn zoom-out" id="resumeZoomOut">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <span class="zoom-level" id="resumeZoomLevel">100%</span>
                                <button class="control-btn zoom-in" id="resumeZoomIn">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <div class="control-separator"></div>
                                <button class="control-btn fit-width" id="resumeFitWidth">
                                    <i class="fas fa-arrows-alt-h"></i>
                                    Fit Width
                                </button>
                                <button class="control-btn fit-page" id="resumeFitPage">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                    Fit Page
                                </button>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Resume Details -->
                        <div class="resume-details-panel" id="resumeDetailsPanel">
                            <div class="applicant-summary">
                                <div class="applicant-avatar-large">
                                    <img id="resumeApplicantAvatar" src="" alt="Applicant">
                                    <div class="avatar-fallback-large">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="applicant-info-detailed">
                                    <h3 id="resumeApplicantFullName">Applicant Name</h3>
                                    <p class="applicant-headline" id="resumeApplicantHeadline">Professional Title</p>
                                    <div class="contact-info">
                                        <div class="contact-item">
                                            <i class="fas fa-envelope"></i>
                                            <span id="resumeApplicantEmail">email@example.com</span>
                                        </div>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <span id="resumeApplicantPhone">+1234567890</span>
                                        </div>
                                        <div class="contact-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span id="resumeApplicantLocation">Location</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="application-details">
                                <h4><i class="fas fa-briefcase"></i> Application Details</h4>
                                <div class="detail-item">
                                    <span class="detail-label">Applied For:</span>
                                    <span class="detail-value" id="resumeJobPosition">Job Title</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Application Date:</span>
                                    <span class="detail-value" id="resumeApplicationDate">Date</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Current Status:</span>
                                    <span class="detail-value status-badge" id="resumeApplicationStatus">Submitted</span>
                                </div>
                            </div>
                            
                            <div class="resume-file-info">
                                <h4><i class="fas fa-file"></i> File Information</h4>
                                <div class="file-details">
                                    <div class="detail-item">
                                        <span class="detail-label">File Name:</span>
                                        <span class="detail-value" id="resumeFileName">resume.pdf</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">File Size:</span>
                                        <span class="detail-value" id="resumeFileSize">2.5 MB</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">File Type:</span>
                                        <span class="detail-value" id="resumeFileType">PDF</span>
                                    </div>
                                </div>
                                
                                <div class="file-actions">
                                    <button class="file-action-btn primary" id="resumeDownloadPrimary">
                                        <i class="fas fa-download"></i>
                                        Download Resume
                                    </button>
                                    <button class="file-action-btn secondary" id="resumeOpenNew">
                                        <i class="fas fa-external-link-alt"></i>
                                        Open in New Tab
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Load the JavaScript -->
        <script src="../../scripts/employer/empapplicants.js"></script>
        
        <script>
        // Additional initialization for new features
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 ThisAble Applicants System Loaded');
            console.log('✅ Features Available:');
            console.log('   - Applicant Management');
            console.log('   - Interview Scheduling');
            console.log('   - Bulk Actions');
            console.log('   - Resume Management');
            console.log('   - PWD Accommodations');
            console.log('   - Notification System');
        });
        </script>

        <script>
// Debug version - add to empapplicant.php
document.addEventListener('click', function(e) {
    if (e.target.hasAttribute('data-status')) {
        const status = e.target.getAttribute('data-status');
        const applicationId = e.target.getAttribute('data-application-id');
        
        // DEBUG: Log what we're getting
        console.log('=== BUTTON CLICK DEBUG ===');
        console.log('Button element:', e.target);
        console.log('Status attribute:', status);
        console.log('Application ID attribute:', applicationId);
        console.log('Button HTML:', e.target.outerHTML);
        
        // Check if values are actually there
        if (!applicationId || !status) {
            alert('DEBUG: Missing data!\nApplication ID: ' + applicationId + '\nStatus: ' + status);
            return;
        }
        
        // If we have both values, proceed
        updateStatusWithDebug(status, applicationId);
    }
});

async function updateStatusWithDebug(status, applicationId) {
    const requestData = {
        application_id: applicationId,
        status: status
    };
    
    console.log('=== SENDING TO BACKEND ===');
    console.log('Request data:', requestData);
    
    try {
        const response = await fetch('../../backend/employer/update_application_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            alert('Status updated successfully!');
            location.reload();
        } else {
            alert('Backend Error: ' + data.message);
        }
    } catch (error) {
        console.error('Network error:', error);
        alert('Network error: ' + error.message);
    }
}
</script>
    </body>
</html>