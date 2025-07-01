// ThisAble Notifications Phase 3 - Fixed Version
let notificationsData = [];
let currentFilter = 'all';
let currentPage = 1;
let totalPages = 1;
let isLoading = false;
let selectedNotifications = new Set();
let autoRefreshInterval = null;

// DOM elements
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const toggleIcon = document.getElementById('toggle-icon');
const notificationList = document.getElementById('notification-list');
const filterButtons = document.querySelectorAll('.filter-btn');

// Initialize the UI when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
    initializeAccessibilityFeatures();
    setupAutoRefresh();
});

// Toggle sidebar
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

// Initialize notifications system
async function initializeNotifications() {
    showLoadingState();
    await loadNotifications('all');
    setupFilterButtons();
    setupMobileResponsive();
    addAdvancedControls();
}

// Add advanced notification controls
function addAdvancedControls() {
    const header = document.querySelector('.notifications-header');
    if (!header) return;
    
    // Check if controls already exist
    if (document.querySelector('.notification-actions-toolbar')) return;
    
    // Add notification actions
    const actionsHtml = `
        <div class="notification-actions-toolbar">
            <div class="actions-left">
                <button class="action-btn" onclick="refreshNotifications()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
            </div>
            <div class="actions-right">
                <button class="action-btn primary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double"></i> Mark All Read
                </button>
                <button class="action-btn secondary" onclick="enableBulkMode()">
                    <i class="fas fa-check-square"></i> Select Multiple
                </button>
            </div>
        </div>
        
        <div class="bulk-actions-toolbar" id="bulk-actions" style="display: none;">
            <div class="bulk-actions-left">
                <input type="checkbox" id="select-all" class="bulk-checkbox">
                <label for="select-all">Select All</label>
                <span class="selected-count">0 selected</span>
            </div>
            <div class="bulk-actions-right">
                <button class="bulk-btn" onclick="bulkMarkAsRead()">
                    <i class="fas fa-check"></i> Mark as Read
                </button>
                <button class="bulk-btn secondary" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button class="bulk-btn secondary" onclick="clearSelection()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    `;
    
    header.insertAdjacentHTML('afterend', actionsHtml);
    
    // Setup bulk actions after elements are created
    setTimeout(initializeBulkActions, 100);
}

// Load notifications from backend
async function loadNotifications(filter = 'all', page = 1) {
    if (isLoading) return;
    
    isLoading = true;
    currentFilter = filter;
    currentPage = page;
    
    try {
        const url = `../../backend/candidate/get_notifications.php?filter=${filter}&page=${page}&limit=20`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            throw new Error("Invalid JSON response from server");
        }
        
        if (data.success) {
            notificationsData = data.notifications;
            updatePagination(data.pagination);
            renderNotifications(notificationsData);
            updateNotificationBadge();
            clearSelection(); // Clear any selections on new load
        } else {
            showErrorState(data.message || 'Failed to load notifications');
        }
        
    } catch (error) {
        console.error('Error loading notifications:', error);
        showErrorState('Unable to load notifications: ' + error.message);
    } finally {
        isLoading = false;
    }
}

// Enhanced notification rendering with selection checkboxes
function renderNotifications(notifications) {
    if (!notificationList) return;
    
    notificationList.innerHTML = '';
    
    if (notifications.length === 0) {
        showEmptyState();
        return;
    }
    
    notifications.forEach(notification => {
        const notificationItem = createNotificationElement(notification);
        notificationList.appendChild(notificationItem);
    });
}

// Create individual notification element with enhanced features
function createNotificationElement(notification) {
    const notificationItem = document.createElement('div');
    notificationItem.className = `notification-item ${notification.unread ? 'unread' : ''} ${selectedNotifications.has(notification.id) ? 'selected' : ''}`;
    notificationItem.dataset.id = notification.id;
    
    // Safely check if bulk mode is active
    const bulkActionsElement = document.getElementById('bulk-actions');
    const bulkModeActive = bulkActionsElement && bulkActionsElement.style.display !== 'none';
    
    notificationItem.innerHTML = `
        ${bulkModeActive ? `
            <div class="notification-select">
                <input type="checkbox" class="bulk-checkbox" 
                       ${selectedNotifications.has(notification.id) ? 'checked' : ''}
                       onchange="toggleNotificationSelection(${notification.id})">
            </div>
        ` : ''}
        <div class="notification-icon ${notification.type}">
            <i class="${notification.icon_class || getIconClass(notification.type)}"></i>
        </div>
        <div class="notification-content">
            <div class="notification-header">
                <div class="notification-title">${escapeHtml(notification.title)}</div>
                <div class="notification-controls">
                    ${notification.unread ? `
                        <button class="control-btn" onclick="markAsRead(${notification.id})" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
            <div class="notification-body">${escapeHtml(notification.body)}</div>
            <div class="notification-time">${notification.time}</div>
            <div class="notification-actions">
                ${notification.actions.map(action => `
                    <a href="${action.link}" ${action.link === '#' ? 'onclick="return false;"' : ''}>
                        <button class="notification-btn ${action.type}-btn" 
                                ${action.link === '#' ? `onclick="handleNotificationAction('${action.text}', ${notification.id})"` : ''}>
                            ${escapeHtml(action.text)}
                        </button>
                    </a>
                `).join('')}
            </div>
        </div>
        ${notification.unread ? '<div class="unread-indicator"></div>' : ''}
    `;
    
    // Add click listener for row selection in bulk mode
    if (bulkModeActive) {
        notificationItem.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-actions') && !e.target.closest('.notification-controls')) {
                toggleNotificationSelection(notification.id);
            }
        });
    }
    
    return notificationItem;
}

// Bulk actions functionality
function initializeBulkActions() {
    // Setup select all checkbox
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                selectAllNotifications();
            } else {
                clearSelection();
            }
        });
    }
}

function enableBulkMode() {
    const bulkActions = document.getElementById('bulk-actions');
    if (bulkActions) {
        bulkActions.style.display = 'flex';
        renderNotifications(notificationsData); // Re-render with checkboxes
    }
}

function toggleNotificationSelection(notificationId) {
    const checkbox = document.querySelector(`[data-id="${notificationId}"] .bulk-checkbox`);
    const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
    
    if (selectedNotifications.has(notificationId)) {
        selectedNotifications.delete(notificationId);
        if (checkbox) checkbox.checked = false;
        if (notificationElement) notificationElement.classList.remove('selected');
    } else {
        selectedNotifications.add(notificationId);
        if (checkbox) checkbox.checked = true;
        if (notificationElement) notificationElement.classList.add('selected');
    }
    
    updateSelectionCount();
    updateSelectAllState();
}

function selectAllNotifications() {
    notificationsData.forEach(notification => {
        selectedNotifications.add(notification.id);
        const checkbox = document.querySelector(`[data-id="${notification.id}"] .bulk-checkbox`);
        const element = document.querySelector(`[data-id="${notification.id}"]`);
        if (checkbox) checkbox.checked = true;
        if (element) element.classList.add('selected');
    });
    updateSelectionCount();
}

function clearSelection() {
    selectedNotifications.clear();
    document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.notification-item').forEach(item => item.classList.remove('selected'));
    
    const bulkActions = document.getElementById('bulk-actions');
    if (bulkActions) {
        bulkActions.style.display = 'none';
    }
    
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.checked = false;
    }
    
    updateSelectionCount();
    renderNotifications(notificationsData); // Re-render without checkboxes
}

function updateSelectionCount() {
    const countElement = document.querySelector('.selected-count');
    if (countElement) {
        countElement.textContent = `${selectedNotifications.size} selected`;
    }
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        const totalNotifications = notificationsData.length;
        const selectedCount = selectedNotifications.size;
        
        selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalNotifications;
        selectAllCheckbox.checked = selectedCount === totalNotifications && totalNotifications > 0;
    }
}

// Bulk actions
async function bulkMarkAsRead() {
    if (selectedNotifications.size === 0) return;
    
    try {
        const response = await fetch('../../backend/candidate/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'mark_multiple_read',
                notification_ids: Array.from(selectedNotifications)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotificationFeedback(`${data.affected_count} notifications marked as read`, 'success');
            await refreshNotifications();
            clearSelection();
        } else {
            showNotificationFeedback(data.message || 'Failed to mark notifications as read', 'error');
        }
        
    } catch (error) {
        console.error('Error in bulk mark as read:', error);
        showNotificationFeedback('Unable to mark notifications as read', 'error');
    }
}

async function bulkDelete() {
    if (selectedNotifications.size === 0) return;
    
    if (!confirm(`Are you sure you want to delete ${selectedNotifications.size} notification(s)?`)) {
        return;
    }
    
    try {
        // For now, just mark as read since we don't have delete endpoint yet
        await bulkMarkAsRead();
        
    } catch (error) {
        console.error('Error in bulk delete:', error);
        showNotificationFeedback('Unable to delete notifications', 'error');
    }
}

// Mark notification as read
async function markAsRead(id) {
    try {
        const response = await fetch('../../backend/candidate/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_single_read',
                notification_id: id
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the notification in local data
            const notification = notificationsData.find(n => n.id === id);
            if (notification) {
                notification.unread = false;
                
                // Re-render to update UI
                renderNotifications(notificationsData);
                updateNotificationBadge();
                
                // Show success feedback
                showNotificationFeedback('Notification marked as read', 'success');
            }
        } else {
            showNotificationFeedback(data.message || 'Failed to mark as read', 'error');
        }
        
    } catch (error) {
        console.error('Error marking notification as read:', error);
        showNotificationFeedback('Unable to mark as read. Please try again.', 'error');
    }
}

// Mark all notifications as read
async function markAllAsRead() {
    try {
        const response = await fetch('../../backend/candidate/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'mark_all_read'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update all notifications in local data
            notificationsData.forEach(notification => {
                notification.unread = false;
            });
            
            renderNotifications(notificationsData);
            updateNotificationBadge();
            showNotificationFeedback(`${data.affected_count} notifications marked as read`, 'success');
        } else {
            showNotificationFeedback(data.message || 'Failed to mark all as read', 'error');
        }
        
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
        showNotificationFeedback('Unable to mark all as read. Please try again.', 'error');
    }
}

// Setup filter buttons
function setupFilterButtons() {
    filterButtons.forEach(button => {
        button.addEventListener('click', async () => {
            if (isLoading) return;
            
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Load notifications with new filter
            const filter = button.dataset.filter;
            showLoadingState();
            await loadNotifications(filter, 1);
        });
    });
}

// Handle notification actions (like Dismiss, Reschedule, etc.)
async function handleNotificationAction(actionText, notificationId) {
    switch (actionText) {
        case 'Dismiss':
            await markAsRead(notificationId);
            break;
        case 'Not Interested':
            await markAsRead(notificationId);
            showNotificationFeedback('Job removed from recommendations', 'info');
            break;
        case 'It Was Me':
            await markAsRead(notificationId);
            showNotificationFeedback('Security alert dismissed', 'info');
            break;
        case 'Reschedule':
            showNotificationFeedback('Reschedule feature coming soon', 'info');
            break;
        default:
            console.log('Action:', actionText, 'for notification:', notificationId);
    }
}

// Update notification badge (if exists in sidebar)
async function updateNotificationBadge() {
    try {
        const response = await fetch('../../backend/candidate/get_notification_stats.php');
        const data = await response.json();
        
        if (data.success) {
            const badge = document.querySelector('.notification-badge');
            const unreadCount = data.stats.by_category.all.unread;
            
            if (badge) {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error updating notification badge:', error);
    }
}

// Auto-refresh functionality
function setupAutoRefresh() {
    // Refresh notifications every 2 minutes
    autoRefreshInterval = setInterval(() => {
        refreshNotifications(true); // silent refresh
    }, 120000);
    
    // Clear interval when page unloads
    window.addEventListener('beforeunload', () => {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });
}

async function refreshNotifications(silent = false) {
    if (!silent) {
        showNotificationFeedback('Refreshing notifications...', 'info');
    }
    await loadNotifications(currentFilter, currentPage);
    if (!silent) {
        showNotificationFeedback('Notifications refreshed', 'success');
    }
}

// Show loading state
function showLoadingState() {
    if (!notificationList) return;
    
    notificationList.innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h3>Loading notifications...</h3>
            <p>Please wait while we fetch your latest notifications.</p>
        </div>
    `;
}

// Show empty state
function showEmptyState() {
    const filterText = currentFilter !== 'all' ? currentFilter : '';
    if (!notificationList) return;
    
    notificationList.innerHTML = `
        <div class="empty-state">
            <i class="far fa-bell"></i>
            <h3>No notifications</h3>
            <p>You don't have any ${filterText} notifications at the moment.</p>
            ${currentFilter !== 'all' ? `<button onclick="loadNotifications('all')" class="btn-secondary">View all notifications</button>` : ''}
        </div>
    `;
}

// Show error state
function showErrorState(message) {
    if (!notificationList) return;
    
    notificationList.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Unable to load notifications</h3>
            <p>${escapeHtml(message)}</p>
            <button onclick="loadNotifications(currentFilter, currentPage)" class="btn-primary">
                <i class="fas fa-redo"></i> Try Again
            </button>
        </div>
    `;
}

// Show notification feedback
function showNotificationFeedback(message, type = 'info') {
    // Create feedback element if it doesn't exist
    let feedback = document.querySelector('.notification-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'notification-feedback';
        document.body.appendChild(feedback);
    }
    
    feedback.className = `notification-feedback ${type} show`;
    feedback.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${escapeHtml(message)}</span>
    `;
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        feedback.classList.remove('show');
    }, 3000);
}

// Update pagination info
function updatePagination(pagination) {
    totalPages = pagination.total_pages;
    currentPage = pagination.current_page;
    
    // Add pagination controls if needed
    const existingPagination = document.querySelector('.pagination-controls');
    if (existingPagination) {
        existingPagination.remove();
    }
    
    if (totalPages > 1) {
        const paginationControls = createPaginationControls(pagination);
        if (notificationList && notificationList.parentNode) {
            notificationList.parentNode.appendChild(paginationControls);
        }
    }
}

// Create pagination controls
function createPaginationControls(pagination) {
    const controls = document.createElement('div');
    controls.className = 'pagination-controls';
    
    const prevDisabled = pagination.current_page <= 1 ? 'disabled' : '';
    const nextDisabled = pagination.current_page >= pagination.total_pages ? 'disabled' : '';
    
    controls.innerHTML = `
        <button class="pagination-btn ${prevDisabled}" 
                onclick="loadNotifications(currentFilter, currentPage - 1)" 
                ${prevDisabled ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> Previous
        </button>
        <span class="pagination-info">
            Page ${pagination.current_page} of ${pagination.total_pages}
        </span>
        <button class="pagination-btn ${nextDisabled}" 
                onclick="loadNotifications(currentFilter, currentPage + 1)"
                ${nextDisabled ? 'disabled' : ''}>
            Next <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    return controls;
}

// Function to get icon class based on notification type (fallback)
function getIconClass(type) {
    switch (type) {
        case 'application':
            return 'fas fa-file-alt';
        case 'message':
            return 'fas fa-envelope';
        case 'job':
            return 'fas fa-briefcase';
        case 'system':
            return 'fas fa-cog';
        default:
            return 'fas fa-bell';
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Setup mobile responsive functionality
function setupMobileResponsive() {
    if (window.innerWidth <= 768) {
        const mobileMenuBtn = document.createElement('div');
        mobileMenuBtn.className = 'mobile-menu-btn';
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        
        const header = document.querySelector('.notifications-header');
        if (header && !header.querySelector('.mobile-menu-btn')) {
            header.prepend(mobileMenuBtn);
            
            mobileMenuBtn.addEventListener('click', () => {
                if (sidebar) sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (
                    window.innerWidth <= 768 &&
                    sidebar && !sidebar.contains(e.target) &&
                    !mobileMenuBtn.contains(e.target) &&
                    sidebar.classList.contains('show')
                ) {
                    sidebar.classList.remove('show');
                }
            });
        }
    }
}

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

// Add keyboard navigation support
document.addEventListener('keydown', function(e) {
    // Press 'R' to refresh notifications
    if (e.key === 'r' || e.key === 'R') {
        if (!e.ctrlKey && !e.metaKey) { // Avoid interfering with Ctrl+R page refresh
            e.preventDefault();
            refreshNotifications();
        }
    }
    
    // Press 'M' to mark all as read
    if (e.key === 'm' || e.key === 'M') {
        if (!e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            markAllAsRead();
        }
    }
    
    // Press 'B' to enable bulk mode
    if (e.key === 'b' || e.key === 'B') {
        if (!e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            enableBulkMode();
        }
    }
    
    // Press 'Escape' to clear selection/exit bulk mode
    if (e.key === 'Escape') {
        if (selectedNotifications.size > 0) {
            clearSelection();
        }
    }
});