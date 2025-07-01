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
    
    // Update the cards with real data
    statsGrid.innerHTML = `
        <div class="stat-card">
            <div class="stat-icon jobs-applied">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-number">${stats.total_applications}</div>
            <div class="stat-label">Jobs Applied</div>
            ${stats.application_trend ? `<div class="stat-trend ${stats.application_trend > 0 ? 'positive' : 'negative'}">${stats.application_trend > 0 ? '+' : ''}${stats.application_trend}% this month</div>` : ''}
        </div>
        
        <div class="stat-card">
            <div class="stat-icon jobs-saved">
                <i class="fas fa-bookmark"></i>
            </div>
            <div class="stat-number" id="saved-jobs-count">--</div>
            <div class="stat-label">Jobs Saved</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon interviews">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-number">${stats.upcoming_interviews}</div>
            <div class="stat-label">Scheduled Interviews</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon notifications">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-number" id="notification-count">--</div>
            <div class="stat-label">Notifications</div>
        </div>
    `;
    
    // Load saved jobs count (we'll need to create this API later)
    loadSavedJobsCount();
}

function updateNotificationStats(stats) {
    const notificationCountElement = document.getElementById('notification-count');
    if (notificationCountElement && stats.overall) {
        notificationCountElement.textContent = stats.overall.unread_count || 0;
    }
}

function loadSavedJobsCount() {
    // For now, we'll use a placeholder. We can implement saved jobs API later
    fetch('../../backend/candidate/get_saved_jobs_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('saved-jobs-count').textContent = data.count;
            } else {
                document.getElementById('saved-jobs-count').textContent = '0';
            }
        })
        .catch(error => {
            document.getElementById('saved-jobs-count').textContent = '0';
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