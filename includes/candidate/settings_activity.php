<div class="setting-detail-container" id="account-activity-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Account Activity</div>
    </div>
    
    <div class="form-group">
        <label class="form-label">Recent Login Sessions</label>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Chrome on Windows 10 <span class="current-session">Current</span></div>
                <div class="session-details">Location: New York, USA • IP: 192.168.1.1 • May 1, 2025 10:15 AM</div>
            </div>
            <div class="document-action" title="This is your current session">
                <i class="fas fa-check-circle" style="color: var(--success);"></i>
            </div>
        </div>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Safari on iPhone</div>
                <div class="session-details">Location: New York, USA • IP: 192.168.1.2 • April 30, 2025 6:30 PM</div>
            </div>
            <div class="document-action" title="End this session">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Firefox on MacOS</div>
                <div class="session-details">Location: New York, USA • IP: 192.168.1.3 • April 29, 2025 2:45 PM</div>
            </div>
            <div class="document-action" title="End this session">
                <i class="fas fa-sign-out-alt"></i>
            </div>
        </div>
        <button class="btn btn-secondary" id="logout-all-devices">Sign Out From All Devices</button>
    </div>
    
    <div class="form-group">
        <label class="form-label">Recent Activity</label>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Applied for "UX Designer" at TechCorp</div>
                <div class="session-details">April 28, 2025 3:30 PM</div>
            </div>
            <div class="document-action" title="View application">
                <i class="fas fa-external-link-alt"></i>
            </div>
        </div>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Updated Resume</div>
                <div class="session-details">April 27, 2025 11:15 AM</div>
            </div>
            <div class="document-action" title="View document">
                <i class="fas fa-external-link-alt"></i>
            </div>
        </div>
        <div class="session-item">
            <div class="session-info">
                <div class="session-device">Viewed "Marketing Specialist" at CreativeCo</div>
                <div class="session-details">April 26, 2025 2:10 PM</div>
            </div>
            <div class="document-action" title="View job">
                <i class="fas fa-external-link-alt"></i>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="form-label">Account Statistics</label>
        <div class="alert alert-success">
            You have completed 75% of your profile information
        </div>
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
            <div>
                <strong>12</strong> Applications Submitted
            </div>
            <div>
                <strong>3</strong> Interviews Scheduled
            </div>
            <div>
                <strong>45</strong> Profile Views
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label class="form-label">Data Export & Privacy</label>
        <button class="btn btn-secondary" style="margin-right: 10px;" id="export-data">Export My Data</button>
        <button class="btn btn-secondary" id="clear-history">Clear Activity History</button>
    </div>
</div>