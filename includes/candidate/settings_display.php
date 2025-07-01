<div class="setting-detail-container" id="display-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Display</div>
    </div>
    <form id="display-form">
        <div class="form-group">
            <label class="form-label">Theme</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="light-theme" name="theme" checked>
                <label class="form-check-label" for="light-theme">Light</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="dark-theme" name="theme">
                <label class="form-check-label" for="dark-theme">Dark</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="system-theme" name="theme">
                <label class="form-check-label" for="system-theme">System Default</label>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Font Size</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="small-font" name="font-size">
                <label class="form-check-label" for="small-font">Small</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="medium-font" name="font-size" checked>
                <label class="form-check-label" for="medium-font">Medium</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="large-font" name="font-size">
                <label class="form-check-label" for="large-font">Large</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>

<div class="setting-detail-container" id="job-alert-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Job Alert Preferences</div>
    </div>
    <form id="job-alert-form">
        <div class="form-group">
            <label class="form-label">Alert Frequency</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="daily-alerts" name="alert-frequency" checked>
                <label class="form-check-label" for="daily-alerts">Daily</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="weekly-alerts" name="alert-frequency">
                <label class="form-check-label" for="weekly-alerts">Weekly</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="off-alerts" name="alert-frequency">
                <label class="form-check-label" for="off-alerts">Off</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Alert Method</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="email-alerts" checked>
                <label class="form-check-label" for="email-alerts">Email</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="sms-alerts">
                <label class="form-check-label" for="sms-alerts">SMS</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="app-alerts" checked>
                <label class="form-check-label" for="app-alerts">In-app</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Job Categories</label>
            <div class="category-chips">
                <div class="category-chip selected" data-category="full-time">Full-time</div>
                <div class="category-chip" data-category="part-time">Part-time</div>
                <div class="category-chip selected" data-category="remote">Remote</div>
                <div class="category-chip" data-category="freelance">Freelance</div>
                <div class="category-chip selected" data-category="pwd-friendly">PWD-friendly</div>
                <div class="category-chip" data-category="internship">Internship</div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="job-keywords">Job Keywords</label>
            <input type="text" class="form-control" id="job-keywords" placeholder="e.g. developer, remote, accessibility">
            <small class="form-text text-muted">Separate keywords with commas</small>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="job-location">Preferred Location</label>
            <input type="text" class="form-control" id="job-location" placeholder="e.g. New York, Remote">
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>