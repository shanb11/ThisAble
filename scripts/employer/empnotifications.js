// DEBUG VERSION - Uses simplified API endpoint
// Replace your current empnotifications.js with this temporarily

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.currentFilter = 'all';
        this.currentSearch = '';
        this.refreshInterval = null;
        this.isLoading = false;
        
        // DOM elements
        this.notificationList = document.getElementById('notification-list');
        this.filterButtons = document.querySelectorAll('.filter-btn');
        this.markAllReadBtn = document.getElementById('mark-all-read');
        this.notificationSearch = document.getElementById('notification-search');
        this.sidebar = document.getElementById('sidebar');
        this.toggleBtn = document.getElementById('toggle-btn');
        this.toggleIcon = document.getElementById('toggle-icon');
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadNotifications();
    }
    
    setupEventListeners() {
        // Sidebar toggle
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => {
                this.sidebar.classList.toggle('collapsed');
                if (this.sidebar.classList.contains('collapsed')) {
                    this.toggleIcon.classList.remove('fa-chevron-left');
                    this.toggleIcon.classList.add('fa-chevron-right');
                } else {
                    this.toggleIcon.classList.remove('fa-chevron-right');
                    this.toggleIcon.classList.add('fa-chevron-left');
                }
            });
        }
        
        // Filter buttons
        this.filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                this.currentFilter = button.dataset.filter;
                this.loadNotifications();
            });
        });
        
        // Mark all as read (simple implementation)
        if (this.markAllReadBtn) {
            this.markAllReadBtn.addEventListener('click', () => {
            });
        }
        
        // Search functionality
        if (this.notificationSearch) {
            this.notificationSearch.addEventListener('input', (e) => {
                this.currentSearch = e.target.value;
                // Debounce search
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.loadNotifications();
                }, 300);
            });
        }
    }
    
    async loadNotifications() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoadingState();
        
        try {
            const params = new URLSearchParams({
                filter: this.currentFilter,
                search: this.currentSearch
            });
            
            // Use working API that's compatible with your session system
            const response = await fetch(`../../backend/employer/working_notifications.php?${params}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Response error:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data);
            
            if (data.success) {
                this.notifications = data.notifications;
                this.renderNotifications();
                this.updateUnreadCount(data.unread_count);
            } else {
                throw new Error(data.message || 'Failed to load notifications');
            }
            
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Failed to load notifications: ' + error.message);
        } finally {
            this.isLoading = false;
        }
    }
    
    renderNotifications() {
        if (!this.notificationList) return;
        
        this.notificationList.innerHTML = '';
        
        if (this.notifications.length === 0) {
            this.showEmptyState();
            return;
        }
        
        this.notifications.forEach(notification => {
            const notificationElement = this.createNotificationElement(notification);
            this.notificationList.appendChild(notificationElement);
        });
    }
    
    createNotificationElement(notification) {
        const notificationItem = document.createElement('div');
        notificationItem.className = `notification-item ${notification.unread ? 'unread' : ''}`;
        notificationItem.dataset.id = notification.id;
        
        // Get icon class based on type
        const iconClass = this.getIconClass(notification.type);
        
        notificationItem.innerHTML = `
            <div class="notification-icon ${notification.type}">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${notification.time}</div>
                <div class="notification-buttons">
                    ${notification.actions.map(action => `
                        <a href="${action.link}">
                            <button class="notification-btn ${action.type}">${action.text}</button>
                        </a>
                    `).join('')}
                </div>
            </div>
            <div class="notification-actions-right">
                ${notification.unread ? `<i class="fas fa-circle-dot mark-read" title="Mark as read"></i>` : ''}
                <div class="notification-options">
                    <i class="fas fa-ellipsis-v"></i>
                    <div class="options-dropdown">
                        <div class="dropdown-item temp-disabled"><i class="fas fa-info-circle"></i> Update features coming soon</div>
                    </div>
                </div>
            </div>
        `;
        
        return notificationItem;
    }
    
    getIconClass(type) {
        const iconMap = {
            'new_application': 'fas fa-user-plus',
            'application_status': 'fas fa-clipboard-check',
            'interview_scheduled': 'fas fa-calendar-alt',
            'interview_reminder': 'fas fa-bell',
            'job_posted': 'fas fa-briefcase',
            'job_expiring': 'fas fa-clock',
            'job_performance': 'fas fa-chart-line',
            'system_update': 'fas fa-cog',
            'subscription_renewal': 'fas fa-credit-card',
            'profile_completion': 'fas fa-user-edit'
        };
        
        return iconMap[type] || 'fas fa-bell';
    }
    
    showLoadingState() {
        if (this.notificationList) {
            this.notificationList.innerHTML = `
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading notifications...</p>
                </div>
            `;
        }
    }
    
    showEmptyState() {
        this.notificationList.innerHTML = `
            <div class="empty-state">
                <i class="far fa-bell"></i>
                <h3>No notifications found</h3>
                <p>No notifications available at this time.</p>
            </div>
        `;
    }
    
    showError(message) {
        if (this.notificationList) {
            this.notificationList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Error Loading Notifications</h3>
                    <p>${message}</p>
                    <button onclick="notificationManager.loadNotifications()" class="retry-btn">
                        <i class="fas fa-refresh"></i> Retry
                    </button>
                </div>
            `;
        }
    }
    
    updateUnreadCount(count) {
        console.log('Updating unread count:', count);
        // Update page title with unread count
        const originalTitle = document.title.replace(/ \(\d+\)$/, '');
        document.title = count > 0 ? `${originalTitle} (${count})` : originalTitle;
    }
    
    showToast(message, type = 'success', duration = 3000) {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const iconMap = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-times-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        };
        
        toast.innerHTML = `
            <i class="${iconMap[type] || iconMap.info}"></i>
            ${message}
        `;
        
        toastContainer.appendChild(toast);
        
        // Show the toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Hide and remove the toast after duration
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toastContainer.contains(toast)) {
                    toastContainer.removeChild(toast);
                }
            }, 300);
        }, duration);
    }
    
    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
}

// Initialize notification manager when DOM is loaded
let notificationManager;
document.addEventListener('DOMContentLoaded', () => {
    notificationManager = new NotificationManager();
});

// Export for global access
window.notificationManager = notificationManager;