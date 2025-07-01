<div class="setting-detail-container" id="contact-info-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Contact Info</div>
    </div>
    <form id="contact-info-form">
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input type="text" class="form-control" id="name" value="John Doe">
        </div>
        <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <input type="email" class="form-control" id="email" value="john.doe@example.com">
        </div>
        <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" class="form-control" id="phone" value="+1 234 567 8900">
        </div>
        <div class="form-group">
            <label class="form-label" for="address">Address</label>
            <input type="text" class="form-control" id="address" value="123 Main St, City, Country">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="setting-detail-container" id="password-security-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Password & Security</div>
    </div>
    <form id="password-security-form">
        <div class="form-group">
            <label class="form-label" for="current-password">Current Password</label>
            <input type="password" class="form-control" id="current-password">
        </div>
        <div class="form-group">
            <label class="form-label" for="new-password">New Password</label>
            <input type="password" class="form-control" id="new-password">
        </div>
        <div class="form-group">
            <label class="form-label" for="confirm-password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm-password">
        </div>
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="two-factor" checked>
                <label class="form-check-label" for="two-factor">Enable Two-Factor Authentication</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>