<div class="stats-grid" id="stats-grid">
    <!-- Loading state -->
    <div class="stat-card loading">
        <div class="stat-icon jobs-applied">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div class="stat-number">--</div>
        <div class="stat-label">Jobs Applied</div>
    </div>
    
    <div class="stat-card loading">
        <div class="stat-icon jobs-saved">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div class="stat-number">--</div>
        <div class="stat-label">Jobs Saved</div>
    </div>
    
    <div class="stat-card loading">
        <div class="stat-icon interviews">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div class="stat-number">--</div>
        <div class="stat-label">Scheduled Interviews</div>
    </div>
    
    <div class="stat-card loading">
        <div class="stat-icon notifications">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div class="stat-number">--</div>
        <div class="stat-label">Notifications</div>
    </div>
</div>

<script>
// Load real stats data
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

function loadDashboardStats() {
    // Load application stats
    fetch('../../backend/candidate/get_application_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatsCards(data.stats);
            } else {
                console.error('Failed to load application stats:', data.error);
                showFallbackStats();
            }
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            showFallbackStats();
        });
    
    // Load notification stats
    fetch('../../backend/candidate/get_notification_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationStats(data.stats);
            }
        })
        .catch(error => {
            console.error('Error fetching notification stats:', error);
        });
}

function updateStatsCards(stats) {
    const statsGrid = document.getElementById('stats-grid');
    
    // Remove loading class
    statsGrid.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('loading');
    });
    
    // Update the cards with real data (your existing code)
    statsGrid.innerHTML = `
        <div class="stat-card clickable-stat" data-stat-type="jobs-applied">
            <div class="stat-icon jobs-applied">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-number">${stats.total_applications}</div>
            <div class="stat-label">Jobs Applied</div>
            ${stats.application_trend ? `<div class="stat-trend ${stats.application_trend > 0 ? 'positive' : 'negative'}">${stats.application_trend > 0 ? '+' : ''}${stats.application_trend}% this month</div>` : ''}
            <div class="stat-action-hint">Click to view details</div>
        </div>
        
        <div class="stat-card clickable-stat" data-stat-type="jobs-saved">
            <div class="stat-icon jobs-saved">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="stat-number" id="saved-jobs-count">--</div>
            <div class="stat-label">Jobs Saved</div>
            <div class="stat-action-hint">Click to view saved jobs</div>
        </div>
        
        <div class="stat-card clickable-stat" data-stat-type="interviews">
            <div class="stat-icon interviews">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number">${stats.upcoming_interviews}</div>
            <div class="stat-label">Scheduled Interviews</div>
            <div class="stat-action-hint">Click to view schedule</div>
        </div>
        
        <div class="stat-card clickable-stat" data-stat-type="notifications">
            <div class="stat-icon notifications">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-number" id="notification-count">--</div>
            <div class="stat-label">Notifications</div>
            <div class="stat-action-hint">Click to view notifications</div>
        </div>
    `;
    
    // Load saved jobs count AFTER the DOM is ready
    setTimeout(() => {
        loadSavedJobsCount();
    }, 100);
    
    // Add click handlers to the new cards
    addStatCardClickHandlers();
}

// NEW: Add click handlers function
function addStatCardClickHandlers() {
    const clickableCards = document.querySelectorAll('.clickable-stat');
    
    clickableCards.forEach(card => {
        card.addEventListener('click', function() {
            const statType = this.dataset.statType;
            handleStatCardClick(statType);
        });
        
        // Add keyboard support
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const statType = this.dataset.statType;
                handleStatCardClick(statType);
            }
        });
        
        // Make focusable
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.style.cursor = 'pointer';
    });
}

// NEW: Handle stat card clicks
function handleStatCardClick(statType) {
    switch(statType) {
        case 'jobs-applied':
            // Simple popup with basic info then redirect
            showQuickStatsPopup('Job Applications', getJobsAppliedInfo(), 'applications.php');
            break;
        case 'jobs-saved':
            showQuickStatsPopup('Saved Jobs', getSavedJobsInfo(), 'joblistings.php?filter=saved');
            break;
        case 'interviews':
            showQuickStatsPopup('Scheduled Interviews', getInterviewsInfo(), 'interviews.php');
            break;
        case 'notifications':
            showQuickStatsPopup('Notifications', getNotificationsInfo(), 'notifications.php');
            break;
    }
}

// NEW: Simple popup function (no complex modal, just info + redirect)
function showQuickStatsPopup(title, content, redirectUrl) {
    const popup = document.createElement('div');
    popup.className = 'quick-stats-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <div class="popup-header">
                <h3>${title}</h3>
                <button class="popup-close" onclick="this.parentElement.parentElement.parentElement.remove()">×</button>
            </div>
            <div class="popup-body">
                ${content}
            </div>
            <div class="popup-footer">
                <button class="btn-secondary" onclick="this.parentElement.parentElement.parentElement.remove()">Close</button>
                <button class="btn-primary" onclick="window.location.href='${redirectUrl}'">View All</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Auto-close after 10 seconds
    setTimeout(() => {
        if (popup.parentElement) {
            popup.remove();
        }
    }, 10000);
}

// NEW: Get content for each stat type
function getJobsAppliedInfo() {
    const totalApps = document.querySelector('[data-stat-type="jobs-applied"] .stat-number').textContent;
    return `
        <p>You have submitted <strong>${totalApps}</strong> job applications.</p>
        <p>Click "View All" to see detailed status of each application, track your progress, and manage your job search.</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Track application status</li>
            <li>View employer responses</li>
            <li>Manage interview schedules</li>
        </ul>
    `;
}

function getSavedJobsInfo() {
    const savedCount = document.getElementById('saved-jobs-count')?.textContent || '0';
    return `
        <p>You have <strong>${savedCount}</strong> jobs saved for later.</p>
        <p>These are job opportunities you bookmarked to apply to when ready.</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Apply to saved jobs</li>
            <li>Remove jobs you're no longer interested in</li>
            <li>See which jobs are expiring soon</li>
        </ul>
    `;
}

function getInterviewsInfo() {
    const interviewCount = document.querySelector('[data-stat-type="interviews"] .stat-number').textContent;
    return `
        <p>You have <strong>${interviewCount}</strong> interviews scheduled.</p>
        <p>Stay prepared and don't miss any opportunities!</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>View interview details and times</li>
            <li>Join video interviews</li>
            <li>Prepare with company information</li>
        </ul>
    `;
}

function getNotificationsInfo() {
    const notifCount = document.getElementById('notification-count')?.textContent || '0';
    return `
        <p>You have <strong>${notifCount}</strong> notifications.</p>
        <p>Stay updated with the latest news about your applications and job matches.</p>
        <ul style="margin: 15px 0; padding-left: 20px;">
            <li>Application status updates</li>
            <li>New job matches</li>
            <li>Interview reminders</li>
        </ul>
    `;
}


function updateNotificationStats(stats) {
    const notificationCountElement = document.getElementById('notification-count');
    if (notificationCountElement && stats.overall) {
        notificationCountElement.textContent = stats.overall.unread_count || 0;
    }
}

function loadSavedJobsCount() {
    fetch('../../backend/candidate/get_saved_jobs_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const savedJobsCountElement = document.getElementById('saved-jobs-count');
                if (savedJobsCountElement) {
                    savedJobsCountElement.textContent = data.count;
                }
            } else {
                const savedJobsCountElement = document.getElementById('saved-jobs-count');
                if (savedJobsCountElement) {
                    savedJobsCountElement.textContent = '0';
                }
            }
        })
        .catch(error => {
            console.error('Error loading saved jobs count:', error);
            const savedJobsCountElement = document.getElementById('saved-jobs-count');
            if (savedJobsCountElement) {
                savedJobsCountElement.textContent = '0';
            }
        });
}

function showFallbackStats() {
    // Show fallback data if API fails
    const statsGrid = document.getElementById('stats-grid');
    statsGrid.innerHTML = `
        <div class="stat-card">
            <div class="stat-icon jobs-applied">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Jobs Applied</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon jobs-saved">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Jobs Saved</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon interviews">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Scheduled Interviews</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon notifications">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-number">0</div>
            <div class="stat-label">Notifications</div>
        </div>
    `;
}
</script>