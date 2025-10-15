

// Toggle sidebar

// Sidebar Toggle - Clean Version
function initializeSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

    if (toggleBtn && sidebar && toggleIcon) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            } else {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
        });
        
        console.log('Sidebar toggle initialized successfully');
    } else {
        console.error('Sidebar toggle elements not found');
    }
}

// Call immediately when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSidebarToggle);
} else {
    initializeSidebarToggle();
}
document.addEventListener('DOMContentLoaded', function() {
    // Add this at the beginning - Check if account setup is complete
    const setupComplete = localStorage.getItem('accountSetupComplete');
    
    // If setup is explicitly marked as incomplete
    if (setupComplete === 'false') {
        // Get the seeker ID
        const seekerId = localStorage.getItem('seekerId');
        
        if (seekerId) {
            // Check the current setup status with the server
            const formData = new FormData();
            formData.append('check_setup', 'true');
            formData.append('seeker_id', seekerId);
            
            fetch('../../backend/candidate/setup_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'incomplete') {
                    // Redirect to the appropriate setup page
                    window.location.href = '../candidate/' + data.redirect;
                } else if (data.status === 'complete') {
                    // Update localStorage to reflect completion
                    localStorage.setItem('accountSetupComplete', 'true');
                }
            })
            .catch(error => {
                console.error('Error checking setup status:', error);
            });
        }
    }
    
    // Populate welcome message with user's name
    const welcomeNameElement = document.getElementById('welcomeUserName');
    if (welcomeNameElement) {
        const userName = localStorage.getItem('userName');
        if (userName) {
            welcomeNameElement.textContent = userName;
        }
    }

    // Original dashboard.js code starts here
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

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

    // Profile actions
    const uploadResumeBtn = document.querySelector('.profile-action-btn:nth-child(1)');
    const addSkillsBtn = document.querySelector('.profile-action-btn:nth-child(2)');
    
    if (uploadResumeBtn) {
        uploadResumeBtn.addEventListener('click', function() {
            window.location.href = 'profile.php';
        });
    }
    
    if (addSkillsBtn) {
        addSkillsBtn.addEventListener('click', function() {
            window.location.href = 'profile.php';
        });
    }

    // Apply Now buttons
    const applyNowButtons = document.querySelectorAll('.apply-now-btn');
    
    applyNowButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobCard = this.closest('.job-card');
            const jobTitle = jobCard.querySelector('.job-title').textContent;
            const companyName = jobCard.querySelector('.company-name').textContent;
            
            // Pass job details to the application page via URL parameters
            window.location.href = 'joblistings.php?job=' + encodeURIComponent(jobTitle) + '&company=' + encodeURIComponent(companyName);
        });
    });

    // Job search functionality
    const searchInput = document.getElementById('job-search');
    const jobCards = document.querySelectorAll('.job-card');
    const searchClearBtn = document.getElementById('search-clear');
    const searchStatus = document.getElementById('search-status');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            // Loop through all job cards
            jobCards.forEach(card => {
                // Get text content to search within
                const jobTitle = card.querySelector('.job-title').textContent.toLowerCase();
                const companyName = card.querySelector('.company-name').textContent.toLowerCase();
                const jobLocation = card.querySelector('.job-location').textContent.toLowerCase();
                
                // Check if the search term matches any of the job card content
                const matchesSearch = 
                    jobTitle.includes(searchTerm) || 
                    companyName.includes(searchTerm) || 
                    jobLocation.includes(searchTerm);
                
                // Show or hide the card based on search results
                if (searchTerm === '' || matchesSearch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show a message if no results found
            const suggestedJobsSection = document.querySelector('.suggested-jobs');
            let noResultsMsg = document.getElementById('no-results-message');
            
            // Check if any job cards are visible
            const anyVisible = Array.from(jobCards).some(card => card.style.display !== 'none');
            
            if (!anyVisible && searchTerm !== '') {
                // Create message if it doesn't exist
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('p');
                    noResultsMsg.id = 'no-results-message';
                    noResultsMsg.style.textAlign = 'center';
                    noResultsMsg.style.padding = '20px';
                    noResultsMsg.style.color = '#666';
                    suggestedJobsSection.appendChild(noResultsMsg);
                }
                noResultsMsg.textContent = `No jobs found matching "${searchTerm}"`;
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }

            // Update status text
            if (searchTerm !== '') {
                // Count visible job cards
                const visibleCount = Array.from(jobCards).filter(card => 
                    card.style.display !== 'none').length;
                
                // Update status text
                searchStatus.textContent = `Found ${visibleCount} job${visibleCount !== 1 ? 's' : ''} matching "${searchTerm}"`;
                searchStatus.style.display = 'block';
            } else {
                searchStatus.style.display = 'none';
            }
        });

        // Add clear search functionality
        searchInput.addEventListener('keydown', function(e) {
            // Clear search on Escape key
            if (e.key === 'Escape') {
                searchInput.value = '';
                // Trigger the input event to update the display
                searchInput.dispatchEvent(new Event('input'));
            }
        });
    }
    
    // Search clear button
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            // Trigger the input event to update the display
            searchInput.dispatchEvent(new Event('input'));
        });
    }

    // Initialize accessibility features
    initializeAccessibilityFeatures();

    // Walkthrough functionality
    initializeWalkthrough();
});

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
}

// Update font size
function updateFontSize(size) {
    document.documentElement.style.fontSize = size + '%';
    const fontSizeValue = document.querySelector('.font-size-value');
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

// Initialize walkthrough functionality
function initializeWalkthrough() {
    // Walkthrough steps
    const steps = [
        {
            title: "Welcome to Your Dashboard",
            content: "This dashboard is designed to help you track your job search progress, applications, and upcoming interviews. We'll guide you through the main features.",
            element: null,
            position: { top: '50%', left: '50%' }
        },
        {
            title: "Navigation Menu",
            content: "Here you can access different sections of the platform: Home, Profile, Jobs, Notifications, Applications, and Settings.",
            element: ".sidebar-menu",
            position: { top: '100px', left: '250px' }
        },
        {
            title: "Search for Jobs",
            content: "Use this search bar to find specific job opportunities by title, company, or skills.",
            element: ".search-input",
            position: { top: '100px', left: '50%' }
        },
        {
            title: "Profile Completion",
            content: "Track your profile completion progress. A complete profile increases your chances of getting noticed by employers.",
            element: ".profile-completion",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Quick Actions",
            content: "Upload your resume and add skills to improve your profile. These actions help employers understand your qualifications better.",
            element: ".profile-actions",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Application Statistics",
            content: "Track your job application statistics at a glance: jobs applied, jobs saved, scheduled interviews, and notifications.",
            element: ".stats-grid",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Recent Applications",
            content: "View and monitor the status of your recent job applications.",
            element: ".applications-table",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Upcoming Interviews",
            content: "Keep track of your scheduled interviews with details about time, location, and format.",
            element: ".interviews-list",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Suggested Jobs",
            content: "Browse through job opportunities that match your profile and skills.",
            element: ".suggested-jobs",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Notifications",
            content: "Stay updated with the latest activities related to your applications and interviews.",
            element: ".notifications-list",
            position: { top: '100%', left: '50%' }
        },
        {
            title: "Need Help Anytime?",
            content: "Click this help button whenever you need assistance navigating the dashboard.",
            element: ".walkthrough-help",
            position: { top: '100%', left: '100%' }
        },
        {
            title: "You're All Set!",
            content: "You've completed the walkthrough. Start exploring to find your next opportunity!",
            element: null,
            position: { top: '50%', left: '50%' }
        }
    ];
    
    // Walkthrough variables
    let currentStep = 0;
    let walkthroughActive = false;
    const walkthroughPreference = 'dashboard_walkthrough_completed';
    
    // Elements
    const overlay = document.getElementById('walkthrough-overlay');
    const container = document.getElementById('walkthrough-container');
    const title = document.getElementById('walkthrough-title');
    const content = document.getElementById('walkthrough-content');
    const prevBtn = document.getElementById('walkthrough-prev');
    const nextBtn = document.getElementById('walkthrough-next');
    const progressContainer = document.getElementById('walkthrough-progress');
    const skipAllBtn = document.getElementById('walkthrough-skip-all');
    
    // Initialize progress dots
    function initProgressDots() {
        progressContainer.innerHTML = '';
        for (let i = 0; i < steps.length; i++) {
            const dot = document.createElement('div');
            dot.classList.add('progress-dot');
            if (i === currentStep) {
                dot.classList.add('active');
            }
            dot.setAttribute('aria-label', `Step ${i+1} of ${steps.length}`);
            progressContainer.appendChild(dot);
        }
    }
    
    // Update progress dots
    function updateProgressDots() {
        const dots = progressContainer.querySelectorAll('.progress-dot');
        dots.forEach((dot, index) => {
            if (index === currentStep) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }
    
    // Start walkthrough
    function startWalkthrough() {
        if (localStorage.getItem(walkthroughPreference) === 'true') {
            return; // Skip if user has completed walkthrough before
        }
        
        walkthroughActive = true;
        currentStep = 0;
        
        // Initialize progress dots
        initProgressDots();
        
        // Show overlay and container
        overlay.style.display = 'block';
        container.style.display = 'block';
        
        // Hide previous button on first step
        prevBtn.style.visibility = currentStep === 0 ? 'hidden' : 'visible';
        
        // Set initial content
        updateStepContent();
        
        // Set focus for accessibility
        nextBtn.focus();
    }
    
    // Handle highlight creation and positioning
    function createHighlight(element) {
        // Remove existing highlight if any
        const existingHighlight = document.querySelector('.walkthrough-highlight');
        if (existingHighlight) {
            existingHighlight.remove();
        }
        
        if (!element) return;
        
        const elementToHighlight = document.querySelector(element);
        if (!elementToHighlight) return;
        
        const rect = elementToHighlight.getBoundingClientRect();
        
        const highlight = document.createElement('div');
        highlight.classList.add('walkthrough-highlight');
        highlight.style.width = `${rect.width + 10}px`;
        highlight.style.height = `${rect.height + 10}px`;
        highlight.style.top = `${rect.top - 5 + window.scrollY}px`;
        highlight.style.left = `${rect.left - 5 + window.scrollX}px`;
        
        document.body.appendChild(highlight);
        
        // Scroll element into view if needed
        elementToHighlight.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
    
    // Update step content
    function updateStepContent() {
        const step = steps[currentStep];
        
        title.textContent = step.title;
        content.textContent = step.content;
        
        // Update buttons
        prevBtn.style.visibility = currentStep === 0 ? 'hidden' : 'visible';
        nextBtn.textContent = currentStep === steps.length - 1 ? 'Finish' : 'Next';
        
        // Update highlight
        createHighlight(step.element);
        
        // Update progress dots
        updateProgressDots();
        
        // Set focus for accessibility
        nextBtn.focus();
        
        // Announce for screen readers
        announceForScreenReaders(`${step.title}. ${step.content}`);
    }
    
    // Next step handler
    function nextStep() {
        if (currentStep < steps.length - 1) {
            currentStep++;
            updateStepContent();
        } else {
            endWalkthrough();
        }
    }
    
    // Previous step handler
    function prevStep() {
        if (currentStep > 0) {
            currentStep--;
            updateStepContent();
        }
    }
    
    // End walkthrough
    function endWalkthrough() {
        walkthroughActive = false;
        
        // Save preference
        localStorage.setItem(walkthroughPreference, 'true');
        
        // Hide overlay and container
        overlay.style.display = 'none';
        container.style.display = 'none';
        
        // Remove highlight
        const highlight = document.querySelector('.walkthrough-highlight');
        if (highlight) {
            highlight.remove();
        }
    }
    
    // Create screen reader announcement element
    function createAnnouncementElement() {
        const announcement = document.createElement('div');
        announcement.setAttribute('id', 'screen-reader-announcement');
        announcement.setAttribute('role', 'status');
        announcement.setAttribute('aria-live', 'polite');
        announcement.classList.add('screen-reader-text');
        document.body.appendChild(announcement);
        return announcement;
    }
    
    // Announce to screen readers
    function announceForScreenReaders(message) {
        let announcement = document.getElementById('screen-reader-announcement');
        if (!announcement) {
            announcement = createAnnouncementElement();
        }
        
        announcement.textContent = '';
        
        // Delay to ensure announcement
        setTimeout(() => {
            announcement.textContent = message;
        }, 100);
    }
    
    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', nextStep);
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', prevStep);
    }
    
    if (skipAllBtn) {
        skipAllBtn.addEventListener('click', endWalkthrough);
        skipAllBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                endWalkthrough();
            }
        });
    }
    
    // Keyboard navigation for walkthrough
    document.addEventListener('keydown', function(e) {
        if (!walkthroughActive) return;
        
        switch(e.key) {
            case 'Escape':
                endWalkthrough();
                break;
            case 'ArrowRight':
            case 'ArrowDown':
                nextStep();
                break;
            case 'ArrowLeft':
            case 'ArrowUp':
                prevStep();
                break;
        }
    });
    
    // Auto-start walkthrough on first visit (after a short delay)
    setTimeout(() => {
        if (!localStorage.getItem(walkthroughPreference)) {
            startWalkthrough();
        }
    }, 1500);


// Job recommendations refresh
function fetchJobRecommendations() {
    // Add loading indicator
    const suggestedJobs = document.querySelector('.suggested-jobs');
    if (suggestedJobs) {
        suggestedJobs.style.opacity = '0.5';
        
        // Simulate API call with setTimeout
        setTimeout(() => {
            suggestedJobs.style.opacity = '1';
            showToast('Job recommendations updated based on your profile', 'success');
        }, 1500);
    }
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast-notification';
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.left = '50%';
        toast.style.transform = 'translateX(-50%)';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '5px';
        toast.style.color = 'white';
        toast.style.zIndex = '1000';
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
        document.body.appendChild(toast);
    }
    
    // Set type-specific styles
    if (type === 'success') {
        toast.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#dc3545';
    } else {
        toast.style.backgroundColor = '#2F8A99';
    }
    
    // Set message
    toast.textContent = message;
    
    // Show and then hide the toast
    toast.style.opacity = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(20px)';
    }, 3000);
}

// Refresh job recommendations every hour if page is open
setInterval(fetchJobRecommendations, 3600000);
}
// Dashboard Welcome Section Enhancements
// Add this to your existing dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboardWelcome();
});

function initializeDashboardWelcome() {
    // Animate progress bar on page load
    animateProgressBar();
    
    // Set up completion tracking
    setupCompletionTracking();
    
    // Add interactive elements
    setupWelcomeInteractions();
    
    // Check for completion updates
    checkCompletionUpdates();
}

/**
 * Animate the progress bar on page load
 */
function animateProgressBar() {
    const progressBar = document.querySelector('.welcome-section .progress');
    if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        
        setTimeout(() => {
            progressBar.style.width = targetWidth;
        }, 800);
    }
}

/**
 * Setup completion tracking
 */
function setupCompletionTracking() {
    // Store current completion percentage
    const completionElement = document.querySelector('.completion-percentage');
    if (completionElement) {
        const completionPercentage = completionElement.textContent.replace('%', '');
        localStorage.setItem('dashboardCompletion', completionPercentage);
        localStorage.setItem('lastDashboardCheck', Date.now().toString());
    }
}

/**
 * Setup welcome section interactions
 */
function setupWelcomeInteractions() {
    // Add hover effects to action buttons
    const actionButtons = document.querySelectorAll('.profile-action-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-2px) scale(1)';
        });
    });
    
    // Add click tracking for analytics (optional)
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const actionText = this.textContent.trim();
            console.log('Dashboard action clicked:', actionText);
            
            // You can add analytics tracking here
            // Example: trackEvent('dashboard_action', actionText);
        });
    });
    
    // View profile button special handling
    const viewProfileBtn = document.querySelector('.view-profile-btn');
    if (viewProfileBtn) {
        viewProfileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add loading state
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            this.disabled = true;
            
            // Navigate after short delay
            setTimeout(() => {
                window.location.href = 'profile.php';
            }, 300);
        });
    }
}

/**
 * Check for completion updates from profile page
 */
function checkCompletionUpdates() {
    // Check if user came from profile page with updates
    const lastProfileUpdate = localStorage.getItem('lastProfileUpdate');
    const lastDashboardCheck = localStorage.getItem('lastDashboardCheck') || '0';
    
    if (lastProfileUpdate && parseInt(lastProfileUpdate) > parseInt(lastDashboardCheck)) {
        // Show update notification
        showCompletionUpdateNotification();
        localStorage.setItem('lastDashboardCheck', Date.now().toString());
    }
    
    // Set up periodic checks
    setInterval(checkForProfileUpdates, 30000); // Check every 30 seconds
}

/**
 * Show completion update notification
 */
function showCompletionUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'completion-update-notification';
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-check-circle"></i>
            <span>Profile updated! Refresh to see latest completion status.</span>
            <button onclick="window.location.reload()" class="refresh-btn">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        z-index: 1000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 300px;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

/**
 * Periodic check for profile updates
 */
function checkForProfileUpdates() {
    const lastProfileUpdate = localStorage.getItem('lastProfileUpdate');
    const lastDashboardCheck = localStorage.getItem('lastDashboardCheck') || '0';
    
    if (lastProfileUpdate && parseInt(lastProfileUpdate) > parseInt(lastDashboardCheck)) {
        // Subtle indicator that refresh is needed
        const completionSection = document.querySelector('.profile-completion-dashboard');
        if (completionSection && !completionSection.classList.contains('needs-refresh')) {
            completionSection.classList.add('needs-refresh');
            completionSection.style.borderColor = 'rgba(253, 139, 81, 0.5)';
            completionSection.style.animation = 'pulse 2s infinite';
        }
    }
}

/**
 * Handle completion section clicks (redirect to profile)
 */
function handleCompletionClick() {
    const completionSection = document.querySelector('.profile-completion-dashboard');
    if (completionSection) {
        completionSection.style.cursor = 'pointer';
        completionSection.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (e.target.classList.contains('view-profile-btn') || 
                e.target.closest('.view-profile-btn')) {
                return;
            }
            
            window.location.href = 'profile.php';
        });
    }
}

/**
 * Update user name dynamically if needed
 */
function updateUserName() {
    const welcomeElement = document.getElementById('welcomeUserName');
    if (welcomeElement) {
        // Try to get updated name from localStorage
        const updatedName = localStorage.getItem('updatedUserName');
        
        if (updatedName && updatedName !== welcomeElement.textContent) {
            // Animate name change
            welcomeElement.style.transition = 'opacity 0.3s ease';
            welcomeElement.style.opacity = '0';
            
            setTimeout(() => {
                welcomeElement.textContent = updatedName;
                welcomeElement.style.opacity = '1';
                
                // Clear the temporary name from storage
                localStorage.removeItem('updatedUserName');
            }, 300);
        }
    }
}

// Call initialization functions
document.addEventListener('DOMContentLoaded', function() {
    handleCompletionClick();
    updateUserName();
});

// Add CSS for animations if not already present
const additionalStyles = `
    .completion-update-notification .notification-content {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }
    
    .completion-update-notification .refresh-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 5px 8px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .completion-update-notification .refresh-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(253, 139, 81, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(253, 139, 81, 0); }
        100% { box-shadow: 0 0 0 0 rgba(253, 139, 81, 0); }
    }
`;

// Add styles to head
if (!document.getElementById('dashboard-welcome-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'dashboard-welcome-styles';
    styleSheet.textContent = additionalStyles;
    document.head.appendChild(styleSheet);
}
// Add this to your dashboard.js
function toggleDarkTheme() {
    const welcomeSection = document.querySelector('.welcome-section');
    welcomeSection.classList.toggle('dark-theme');
    
    // Save preference
    localStorage.setItem('darkTheme', welcomeSection.classList.contains('dark-theme'));
}

// Load saved theme preference
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('darkTheme') === 'true') {
        document.querySelector('.welcome-section').classList.add('dark-theme');
    }
});

// Add these functions to your existing dashboard.js file

// Refresh dashboard data function
function refreshDashboardData() {
    // Refresh stats
    if (typeof loadDashboardStats === 'function') {
        loadDashboardStats();
    }
    
    // Refresh recent applications
    if (typeof loadRecentApplications === 'function') {
        loadRecentApplications();
    }
    
    // Refresh notifications
    if (typeof loadRecentNotifications === 'function') {
        loadRecentNotifications();
    }
    
    console.log('Dashboard data refreshed');
}

// Auto-refresh dashboard every 5 minutes
setInterval(refreshDashboardData, 300000); // 5 minutes

// Enhanced search functionality for jobs
function enhanceJobSearch() {
    const searchInput = document.getElementById('job-search');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // Only search if there's a term
        if (searchTerm.length > 2) {
            searchTimeout = setTimeout(() => {
                performDashboardSearch(searchTerm);
            }, 500); // Debounce for 500ms
        } else {
            clearSearchResults();
        }
    });
}

function performDashboardSearch(searchTerm) {
    // For now, just redirect to job listings with search term
    // Later we can implement a quick search API
    console.log('Searching for:', searchTerm);
    
    // Show search status
    const searchStatus = document.getElementById('search-status');
    if (searchStatus) {
        searchStatus.textContent = `Searching for "${searchTerm}"...`;
        searchStatus.style.display = 'block';
    }
    
    // Simulate search delay
    setTimeout(() => {
        if (searchStatus) {
            searchStatus.textContent = `Press Enter to search for "${searchTerm}" in job listings`;
        }
    }, 1000);
}

function clearSearchResults() {
    const searchStatus = document.getElementById('search-status');
    if (searchStatus) {
        searchStatus.style.display = 'none';
    }
}

// Handle notification clicks from dashboard
function handleNotificationClick(notificationId, notificationType) {
    // Mark as read
    if (typeof markNotificationAsRead === 'function') {
        markNotificationAsRead(notificationId);
    }
    
    // Navigate based on type
    switch (notificationType) {
        case 'application':
            window.location.href = 'applications.php';
            break;
        case 'interview':
            window.location.href = 'applications.php#interviews';
            break;
        case 'job':
            window.location.href = 'joblistings.php';
            break;
        default:
            window.location.href = 'notifications.php';
    }
}

// Error handling for failed API calls
function showTemporaryMessage(message, type = 'info', duration = 3000) {
    // Create or update message element
    let messageEl = document.getElementById('dashboard-message');
    if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.id = 'dashboard-message';
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        document.body.appendChild(messageEl);
    }
    
    // Set message and style
    messageEl.textContent = message;
    messageEl.className = `message-${type}`;
    
    // Set background color based on type
    const colors = {
        info: '#2F8A99',
        success: '#28a745',
        warning: '#ffc107',
        error: '#dc3545'
    };
    messageEl.style.backgroundColor = colors[type] || colors.info;
    
    // Show message
    messageEl.style.opacity = '1';
    
    // Hide after duration
    setTimeout(() => {
        messageEl.style.opacity = '0';
        setTimeout(() => {
            if (messageEl && messageEl.parentNode) {
                messageEl.parentNode.removeChild(messageEl);
            }
        }, 300);
    }, duration);
}

// Update the DOMContentLoaded event listener to include new functionality
document.addEventListener('DOMContentLoaded', function() {
    // Original dashboard functionality
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const toggleIcon = document.getElementById('toggle-icon');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            if (toggleIcon) {
                if (sidebar.classList.contains('collapsed')) {
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                } else {
                    toggleIcon.classList.remove('fa-chevron-right');
                    toggleIcon.classList.add('fa-chevron-left');
                }
            }
        });
    }

    // Initialize enhanced features
    enhanceJobSearch();
    
    // Initialize accessibility features if function exists
    if (typeof initializeAccessibilityFeatures === 'function') {
        initializeAccessibilityFeatures();
    }

    // Initialize walkthrough if function exists
    if (typeof initializeWalkthrough === 'function') {
        initializeWalkthrough();
    }
    
    // Show welcome message
    setTimeout(() => {
        showTemporaryMessage('Dashboard loaded successfully!', 'success', 2000);
    }, 1000);
});

// Handle keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + R for refresh
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        refreshDashboardData();
        showTemporaryMessage('Dashboard refreshed', 'info', 1500);
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        const searchInput = document.getElementById('job-search');
        if (searchInput && document.activeElement === searchInput) {
            searchInput.value = '';
            clearSearchResults();
        }
    }
});

// Utility function to format numbers for display
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Utility function to format relative time
function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = (now - date) / 1000;
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    
    return date.toLocaleDateString();
}
// Add these functions to your existing dashboard.js file

// Enhanced dashboard refresh with all new components
function refreshAllDashboardData() {
    console.log('🔄 Refreshing all dashboard data...');
    
    // Show subtle loading indicator
    showTemporaryMessage('Refreshing dashboard data...', 'info', 1500);
    
    // Refresh all components
    const refreshPromises = [];
    
    // Stats
    if (typeof loadDashboardStats === 'function') {
        refreshPromises.push(loadDashboardStats());
    }
    
    // Recent Applications
    if (typeof loadRecentApplications === 'function') {
        refreshPromises.push(loadRecentApplications());
    }
    
    // Notifications
    if (typeof loadRecentNotifications === 'function') {
        refreshPromises.push(loadRecentNotifications());
    }
    
    // Upcoming Interviews
    if (typeof loadUpcomingInterviews === 'function') {
        refreshPromises.push(loadUpcomingInterviews());
    }
    
    // Suggested Jobs
    if (typeof loadSuggestedJobs === 'function') {
        refreshPromises.push(loadSuggestedJobs());
    }
    
    // Notification count in search bar
    if (typeof loadNotificationCount === 'function') {
        refreshPromises.push(loadNotificationCount());
    }
    
    // Wait for all refreshes to complete
    Promise.allSettled(refreshPromises).then(() => {
        console.log('✅ Dashboard refresh completed');
        showTemporaryMessage('Dashboard updated successfully!', 'success', 2000);
        
        // Update last refresh time
        localStorage.setItem('lastDashboardRefresh', Date.now().toString());
    }).catch(error => {
        console.error('❌ Error during dashboard refresh:', error);
        showTemporaryMessage('Some data failed to refresh', 'warning', 3000);
    });
}

// Auto-refresh dashboard data every 5 minutes
setInterval(() => {
    const lastRefresh = localStorage.getItem('lastDashboardRefresh');
    const now = Date.now();
    
    // Only auto-refresh if page is visible and user is active
    if (!document.hidden && (now - parseInt(lastRefresh || 0)) > 300000) { // 5 minutes
        refreshAllDashboardData();
    }
}, 300000);

// Enhanced job application handler
function handleJobApplication(jobId, jobTitle, companyName) {
    // Track application attempt
    console.log(`🎯 Applying for: ${jobTitle} at ${companyName}`);
    
    // Show confirmation modal or directly navigate
    const confirmed = confirm(`Apply for ${jobTitle} at ${companyName}?\n\nThis will redirect you to the job application page.`);
    
    if (confirmed) {
        // Add loading state
        showTemporaryMessage('Redirecting to application...', 'info', 2000);
        
        // Store application intent for analytics
        const applicationData = {
            jobId: jobId,
            jobTitle: jobTitle,
            companyName: companyName,
            source: 'dashboard_recommendations',
            timestamp: Date.now()
        };
        
        // Store in session storage for the application page
        sessionStorage.setItem('applicationIntent', JSON.stringify(applicationData));
        
        // Navigate to job listings with application intent
        setTimeout(() => {
            window.location.href = `joblistings.php?apply=${jobId}`;
        }, 500);
    }
}

// Enhanced interview management
function handleInterviewAction(action, interviewId, interviewData = null) {
    console.log(`📅 Interview action: ${action} for interview ${interviewId}`);
    
    switch (action) {
        case 'join':
            if (interviewData && interviewData.meeting_link) {
                joinInterviewMeeting(interviewData.meeting_link, interviewData);
            } else {
                showTemporaryMessage('Meeting link not available', 'warning', 3000);
            }
            break;
            
        case 'reschedule':
            requestInterviewReschedule(interviewId);
            break;
            
        case 'prepare':
            showInterviewPreparationTips(interviewData);
            break;
            
        case 'details':
            window.location.href = `applications.php?interview=${interviewId}`;
            break;
            
        default:
            console.log('Unknown interview action:', action);
    }
}

function joinInterviewMeeting(meetingLink, interviewData) {
    // Validate meeting link
    if (!meetingLink || !meetingLink.startsWith('http')) {
        showTemporaryMessage('Invalid meeting link', 'error', 3000);
        return;
    }
    
    // Show joining message
    showTemporaryMessage('Opening interview meeting...', 'info', 2000);
    
    // Track meeting join attempt
    console.log('🔗 Joining meeting:', meetingLink);
    
    // Open meeting in new tab
    const meetingWindow = window.open(meetingLink, '_blank');
    
    if (!meetingWindow) {
        // Handle popup blocked
        showTemporaryMessage('Please allow popups and try again', 'warning', 4000);
        
        // Provide fallback option
        setTimeout(() => {
            if (confirm('Meeting popup was blocked. Would you like to navigate to the meeting link?')) {
                window.location.href = meetingLink;
            }
        }, 2000);
    } else {
        // Track successful meeting join
        trackInterviewJoin(interviewData.interview_id);
    }
}

function requestInterviewReschedule(interviewId) {
    const reason = prompt('Please provide a reason for rescheduling:');
    
    if (reason && reason.trim()) {
        showTemporaryMessage('Sending reschedule request...', 'info', 2000);
        
        // In a real implementation, you would send this to your backend
        fetch('../../backend/candidate/request_interview_reschedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                interview_id: interviewId,
                reason: reason.trim()
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTemporaryMessage('Reschedule request sent successfully', 'success', 3000);
                refreshAllDashboardData();
            } else {
                showTemporaryMessage('Failed to send request. Please try again.', 'error', 3000);
            }
        })
        .catch(error => {
            console.error('Reschedule request error:', error);
            showTemporaryMessage('Request failed. Please contact support.', 'error', 3000);
        });
    }
}

function showInterviewPreparationTips(interviewData) {
    const tips = [
        '📝 Review the job description and requirements',
        '🏢 Research the company and their values',
        '💼 Prepare examples of your work and achievements',
        '❓ Prepare thoughtful questions about the role',
        '🎯 Practice discussing your disability accommodations if needed',
        '💻 Test your technology (camera, microphone, internet)',
        '👔 Choose appropriate interview attire',
        '⏰ Plan to join 5-10 minutes early'
    ];
    
    const tipsHtml = tips.map(tip => `<li>${tip}</li>`).join('');
    
    const modalHtml = `
        <div class="modal-overlay" onclick="closePreparationModal()">
            <div class="preparation-modal" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>🎯 Interview Preparation Tips</h3>
                    <button onclick="closePreparationModal()" class="close-btn">&times;</button>
                </div>
                <div class="modal-content">
                    <p><strong>Interview:</strong> ${interviewData.job_title} at ${interviewData.company_name}</p>
                    <p><strong>Date:</strong> ${interviewData.formatted_datetime}</p>
                    <hr>
                    <ul class="tips-list">${tipsHtml}</ul>
                </div>
                <div class="modal-footer">
                    <button onclick="closePreparationModal()" class="btn btn-primary">Got it!</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closePreparationModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// Enhanced search functionality
function enhancedJobSearch() {
    const searchInput = document.getElementById('job-search');
    if (!searchInput) return;
    
    // Add search history
    const searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
    
    // Add search suggestions based on user profile
    loadPersonalizedSearchSuggestions();
    
    // Enhanced keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        
        // Ctrl/Cmd + Enter to search all jobs
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && document.activeElement === searchInput) {
            e.preventDefault();
            goToJobListings();
        }
    });
    
    // Save search queries
    searchInput.addEventListener('change', function() {
        const query = this.value.trim();
        if (query.length > 2) {
            saveSearchQuery(query);
        }
    });
}

function loadPersonalizedSearchSuggestions() {
    fetch('../../backend/candidate/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.user_info) {
                // Store user preferences for search enhancement
                const preferences = {
                    skills: data.data.user_skills || [],
                    location: data.data.user_info.city || '',
                    workStyle: data.data.settings.work_style || ''
                };
                
                localStorage.setItem('searchPreferences', JSON.stringify(preferences));
            }
        })
        .catch(error => {
            console.error('Error loading search preferences:', error);
        });
}

function saveSearchQuery(query) {
    let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
    
    // Remove if already exists
    searchHistory = searchHistory.filter(item => item.query !== query);
    
    // Add to beginning
    searchHistory.unshift({
        query: query,
        timestamp: Date.now()
    });
    
    // Keep only last 10 searches
    searchHistory = searchHistory.slice(0, 10);
    
    localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
}

// Performance monitoring
function initializePerformanceMonitoring() {
    // Track page load time
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`📊 Dashboard loaded in ${Math.round(loadTime)}ms`);
        
        if (loadTime > 3000) {
            console.warn('⚠️ Slow page load detected');
        }
    });
    
    // Track API response times
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const startTime = performance.now();
        return originalFetch(...args).then(response => {
            const endTime = performance.now();
            const duration = endTime - startTime;
            
            console.log(`🌐 API Call: ${args[0]} - ${Math.round(duration)}ms`);
            
            if (duration > 2000) {
                console.warn(`⚠️ Slow API response: ${args[0]}`);
            }
            
            return response;
        });
    };
}

// Advanced error handling
function setupAdvancedErrorHandling() {
    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('💥 Global error:', e.error);
        
        // Don't show error messages for minor issues
        if (e.error && e.error.name !== 'TypeError') {
            showTemporaryMessage('Something went wrong. Please refresh the page.', 'error', 5000);
        }
    });
    
    // Unhandled promise rejection handler
    window.addEventListener('unhandledrejection', function(e) {
        console.error('💥 Unhandled promise rejection:', e.reason);
        
        // Prevent default browser behavior
        e.preventDefault();
        
        showTemporaryMessage('A network error occurred. Please check your connection.', 'warning', 4000);
    });
}

// Accessibility enhancements
function enhanceAccessibility() {
    // Add skip links
    if (!document.querySelector('.skip-link')) {
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Skip to main content';
        document.body.insertBefore(skipLink, document.body.firstChild);
    }
    
    // Improve focus management
    document.addEventListener('keydown', function(e) {
        // Tab trapping for modals
        if (e.key === 'Tab') {
            const modal = document.querySelector('.modal-overlay');
            if (modal) {
                trapFocusInModal(e, modal);
            }
        }
    });
    
    // Announce dynamic content changes to screen readers
    setupLiveRegions();
}

function trapFocusInModal(e, modal) {
    const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    if (e.shiftKey) {
        if (document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
        }
    } else {
        if (document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
        }
    }
}

function setupLiveRegions() {
    // Create live region for announcements
    if (!document.getElementById('live-region')) {
        const liveRegion = document.createElement('div');
        liveRegion.id = 'live-region';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.style.position = 'absolute';
        liveRegion.style.left = '-10000px';
        liveRegion.style.width = '1px';
        liveRegion.style.height = '1px';
        liveRegion.style.overflow = 'hidden';
        document.body.appendChild(liveRegion);
    }
}

function announceToScreenReader(message) {
    const liveRegion = document.getElementById('live-region');
    if (liveRegion) {
        liveRegion.textContent = message;
        
        // Clear after announcement
        setTimeout(() => {
            liveRegion.textContent = '';
        }, 1000);
    }
}

// Track interview join attempts for analytics
function trackInterviewJoin(interviewId) {
    // This would typically send data to your analytics service
    console.log('📊 Interview join tracked:', interviewId);
    
    // Store locally for now
    const joins = JSON.parse(localStorage.getItem('interviewJoins') || '[]');
    joins.push({
        interviewId: interviewId,
        timestamp: Date.now()
    });
    localStorage.setItem('interviewJoins', JSON.stringify(joins));
}

// Initialize all Phase 2 enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Phase 2 features
    enhancedJobSearch();
    initializePerformanceMonitoring();
    setupAdvancedErrorHandling();
    enhanceAccessibility();
    
    // Set up periodic refresh
    setTimeout(() => {
        if (!localStorage.getItem('lastDashboardRefresh')) {
            refreshAllDashboardData();
        }
    }, 2000);
    
    console.log('🚀 Dashboard Phase 2 features initialized');
});

// Export functions for use in other components
window.dashboardUtils = {
    refreshAllDashboardData,
    handleJobApplication,
    handleInterviewAction,
    announceToScreenReader,
    showTemporaryMessage
};