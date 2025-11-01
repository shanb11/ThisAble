<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - ThisAble</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/employer/empsettings.css">
    <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include('../../includes/employer/empsidebar.php'); ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="settings-header">
            <h1>Company Settings</h1>
        </div>
        
        <!-- Settings Container -->
        <div class="settings-container" id="settings-main">
            <div class="section-header">Account and profile settings</div>
            <div class="settings-section">
                <div class="setting-item" data-setting="password-security">
                    <div class="setting-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Password & Security</div>
                        <div class="setting-description">Update your password and security settings</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
            
            <div class="section-header">Account management and control</div>
            <div class="settings-section">
                <div class="account-action" id="sign-out-btn">
                    <div class="setting-content">
                        <div class="setting-title">Sign Out</div>
                    </div>
                </div>
                
                <div class="account-action" id="close-account-btn">
                    <div class="setting-content">
                        <div class="setting-title danger-text">Close Account</div>
                        <div class="setting-description">This will permanently close your company account</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Password & Security Detail -->
        <div class="setting-detail-container" id="password-security-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Password & Security</div>
            </div>
            <form id="password-security-form">
                <div class="form-group">
                    <label class="form-label" for="current-password">Current Password</label>
                    <input type="password" class="form-control" id="current-password" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="new-password">New Password</label>
                    <input type="password" class="form-control" id="new-password" required>
                    <small class="form-text text-muted">Password must be at least 8 characters long</small>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm-password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm-password" required>
                </div>
                <!-- <div class="form-group">
                    <label class="form-label">Two-Factor Authentication</label>
                    <div class="form-check">
                        <label class="toggle-switch">
                            <input type="checkbox" id="two-factor" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <label class="form-check-label" for="two-factor">Enable Two-Factor Authentication</label>
                    </div>
                    <small class="form-text text-muted">Adds an extra layer of security to your account</small>
                </div> -->
                <!-- <div class="form-group">
                    <label class="form-label">Login Session</label>
                    <div class="form-check">
                        <label class="toggle-switch">
                            <input type="checkbox" id="remember-login" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <label class="form-check-label" for="remember-login">Keep me logged in</label>
                    </div>
                    <small class="form-text text-muted">Not recommended for shared devices</small>
                </div> -->
                <button type="submit" class="btn btn-primary">Update Password</button>
                <button type="button" class="btn btn-secondary" id="logout-all-devices" style="margin-left: 10px;">Sign Out From All Devices</button>
            </form>
        </div>
        
        <!-- Toast Notification -->
        <div class="toast" id="toast">
            <i class="fas fa-check-circle"></i>
            <span id="toast-message">Changes saved successfully!</span>
        </div>
        
        <!-- Modals -->
        
        <!-- Sign Out Modal -->
        <div class="modal-overlay" id="sign-out-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Sign Out</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to sign out?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-sign-out">Cancel</button>
                    <button class="btn btn-primary" id="confirm-sign-out">Sign Out</button>
                </div>
            </div>
        </div>
        
        <!-- Close Account Modal -->
        <div class="modal-overlay" id="close-account-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Close Account</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close your account? This action cannot be undone and all your data will be permanently deleted.</p>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label" for="confirm-password-close">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="confirm-password-close">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-close-account">Cancel</button>
                    <button class="btn btn-danger" id="confirm-close-account">Close Account</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../scripts/employer/empsettings.js"></script>
</body>
</html>