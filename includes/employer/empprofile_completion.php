<div class="profile-completion-section">
    <div class="profile-header">
        <h2 class="profile-completion-title">📊 Profile Completion</h2>
    </div>
    
    <div class="profile-completion-container">
        <!-- Main Progress Section -->
        <div class="main-progress-section">
            <div class="completion-percentage" id="completion-percentage-display">0%</div>
            <div class="main-progress-bar">
                <div class="main-progress-fill" id="main-progress-fill" style="width: 0%;"></div>
            </div>
        </div>
        
        <!-- Completion Status Message -->
        <div class="completion-status-message" id="completion-status-message">
            <i class="fas fa-info-circle"></i>
            <span>Loading profile data...</span>
        </div>
        
        <!-- Individual Progress Items -->
        <div class="progress-items-list">
            <div class="progress-item" id="progress-company-info" data-section="company_info">
                <div class="progress-item-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="progress-item-content">
                    <div class="progress-item-title">Company Information</div>
                    <div class="progress-item-description">Company name, industry & address</div>
                </div>
                <div class="progress-item-percentage">(20%)</div>
            </div>
            
            <div class="progress-item" id="progress-description" data-section="description">
                <div class="progress-item-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="progress-item-content">
                    <div class="progress-item-title">Company Description</div>
                    <div class="progress-item-description">About us & why join us sections</div>
                </div>
                <div class="progress-item-percentage">(20%)</div>
            </div>
            
            <div class="progress-item" id="progress-preferences" data-section="preferences">
                <div class="progress-item-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="progress-item-content">
                    <div class="progress-item-title">Hiring Preferences</div>
                    <div class="progress-item-description">PWD accommodation settings</div>
                </div>
                <div class="progress-item-percentage">(20%)</div>
            </div>
            
            <div class="progress-item" id="progress-social" data-section="social">
                <div class="progress-item-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="progress-item-content">
                    <div class="progress-item-title">Website & Social Media</div>
                    <div class="progress-item-description">Company website and social links</div>
                </div>
                <div class="progress-item-percentage">(20%)</div>
            </div>
            
            <div class="progress-item" id="progress-logo" data-section="logo">
                <div class="progress-item-icon">
                    <i class="fas fa-image"></i>
                </div>
                <div class="progress-item-content">
                    <div class="progress-item-title">Company Logo</div>
                    <div class="progress-item-description">Upload your company logo</div>
                </div>
                <div class="progress-item-percentage">(20%)</div>
            </div>
        </div>
        
        <!-- Congratulations Message -->
        <div class="congratulations-message" id="congratulations-message" style="display: none;">
            <i class="fas fa-trophy"></i>
            <span>Congratulations! Your profile is 100% complete!</span>
        </div>
    </div>
</div>