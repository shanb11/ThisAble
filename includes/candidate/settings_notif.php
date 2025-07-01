<div class="setting-detail-container" id="notification-settings-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Notification Settings</div>
    </div>
    <form id="notification-settings-form">
        <div class="form-group">
            <label class="form-label">Notification Methods</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="email-notifications" checked>
                <label class="form-check-label" for="email-notifications">Email Notifications</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="sms-notifications" checked>
                <label class="form-check-label" for="sms-notifications">SMS Notifications</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="push-notifications" checked>
                <label class="form-check-label" for="push-notifications">Push Notifications</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Notification Categories</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="job-alerts" checked>
                <label class="form-check-label" for="job-alerts">Job Alerts</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="application-updates" checked>
                <label class="form-check-label" for="application-updates">Application Updates</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="message-notifications" checked>
                <label class="form-check-label" for="message-notifications">Message Notifications</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="marketing-notifications">
                <label class="form-check-label" for="marketing-notifications">Marketing Notifications</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>