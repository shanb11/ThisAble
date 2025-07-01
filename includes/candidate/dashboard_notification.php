<div class="dashboard-section">
    <div class="section-header">
        <h2 class="section-title">Notifications</h2>
        <a href="notifications.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
    </div>
    
    <div id="notifications-container">
        <!-- Loading state -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading notifications...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecentNotifications();
});

function loadRecentNotifications() {
    fetch('../../backend/candidate/get_notifications.php?limit=3')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotifications(data.notifications);
            } else {
                showNoNotificationsMessage();
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            showErrorMessage();
        });
}

function displayNotifications(notifications) {
    const container = document.getElementById('notifications-container');
    
    if (notifications.length === 0) {
        showNoNotificationsMessage();
        return;
    }
    
    const notificationsHTML = `
        <div class="notifications-list">
            ${notifications.map(notification => `
                <div class="notification-item ${notification.unread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="notification-icon">
                        <i class="${notification.icon_class || getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title">${notification.title}</p>
                        <p class="notification-body">${notification.body}</p>
                        <p class="notification-time">${notification.time}</p>
                    </div>
                    ${notification.unread ? '<div class="unread-indicator"></div>' : ''}
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = notificationsHTML;
    
    // Add click handlers for notifications
    container.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            markNotificationAsRead(notificationId);
            
            // Navigate to notifications page or handle notification action
            window.location.href = 'notifications.php';
        });
    });
}

function showNoNotificationsMessage() {
    const container = document.getElementById('notifications-container');
    container.innerHTML = `
        <div class="no-notifications">
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No New Notifications</h3>
                <p>You're all caught up! Check back later for updates.</p>
            </div>
        </div>
    `;
}

function showErrorMessage() {
    const container = document.getElementById('notifications-container');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Unable to load notifications. Please try again later.</p>
            <button onclick="loadRecentNotifications()" class="btn btn-secondary">Retry</button>
        </div>
    `;
}

function getNotificationIcon(type) {
    const iconMap = {
        'application': 'fas fa-file-alt',
        'job': 'fas fa-briefcase',
        'system': 'fas fa-cog',
        'interview': 'fas fa-calendar-alt'
    };
    
    return iconMap[type] || 'fas fa-bell';
}

function markNotificationAsRead(notificationId) {
    fetch('../../backend/candidate/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI to show as read
            const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('unread');
                const indicator = notificationItem.querySelector('.unread-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}
</script>

<style>
.loading-state, .no-notifications, .error-state {
    text-align: center;
    padding: 30px 20px;
    color: #666;
}

.loading-state i {
    font-size: 20px;
    margin-bottom: 10px;
    color: #2F8A99;
}

.empty-state i {
    font-size: 40px;
    color: #ccc;
    margin-bottom: 15px;
}

.empty-state h3 {
    margin-bottom: 8px;
    color: #333;
    font-size: 16px;
}

.empty-state p {
    font-size: 14px;
    margin: 0;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    transition: background-color 0.2s ease;
    cursor: pointer;
    position: relative;
    border: 1px solid transparent;
}

.notification-item:hover {
    background-color: #f8f9fa;
    border-color: #e9ecef;
}

.notification-item.unread {
    background-color: #f0f8ff;
    border-color: #2F8A99;
}

.notification-item.unread .notification-title {
    font-weight: 600;
}

.notification-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #2F8A99;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 14px;
    font-weight: 500;
    margin: 0 0 4px 0;
    line-height: 1.3;
    color: #333;
}

.notification-body {
    font-size: 13px;
    color: #666;
    margin: 0 0 4px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-time {
    font-size: 11px;
    color: #999;
    margin: 0;
}

.unread-indicator {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 8px;
    height: 8px;
    background: #2F8A99;
    border-radius: 50%;
}

.error-state {
    color: #d32f2f;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 12px;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}
</style>