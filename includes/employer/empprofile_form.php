<form id="company-profile-form">
    <!-- Company Identity Section -->
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">Company Identity</h2>
            <button type="button" class="edit-btn" id="edit-identity">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        
        <div class="section-content">
            <div class="infographic-tiles">
                <div class="info-tile">
                    <div class="info-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Company Name</div>
                        <div class="info-value" id="display-company-name">Loading...</div>
                    </div>
                </div>
                
                <div class="info-tile">
                    <div class="info-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Industry</div>
                        <div class="info-value" id="display-industry">Loading...</div>
                    </div>
                </div>
                
                <div class="info-tile">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Location</div>
                        <div class="info-value" id="display-company-address">Loading...</div>
                    </div>
                </div>
                
                <div class="info-tile">
                    <div class="info-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Contact Person</div>
                        <div class="info-value" id="display-contact-person">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact Person Information -->
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">Contact Person Information</h2>
            <button type="button" class="edit-btn" id="edit-contact">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        
        <div class="section-content">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <div class="info-value" id="display-first-name">Loading...</div>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <div class="info-value" id="display-last-name">Loading...</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Position</label>
                    <div class="info-value" id="display-position">Loading...</div>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <div class="info-value" id="display-contact-number">Loading...</div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <div class="info-value" id="display-email">Loading...</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Company Logo and Description -->
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">Company Logo & Description</h2>
            <button type="button" class="edit-btn" id="edit-logo-description">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        
        <div class="section-content">
            <div class="company-logo-section">
                <div class="logo-preview-container">
                    <div class="logo-preview">
                        <img id="logo-img" src="" alt="" style="display: none;">
                        <i class="fas fa-building" id="logo-placeholder" style="font-size: 50px;"></i>
                    </div>
                    <div class="logo-preview-label">Company Logo</div>
                </div>
                
                <div class="form-group">
                    <label>Company Description</label>
                    <div class="info-value" id="display-about-us">Loading...</div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Why Join Us?</label>
                <div class="info-value" id="display-why-join-us">Loading...</div>
            </div>
        </div>
    </div>
    
    <!-- Hiring Preferences -->
    <div class="profile-section">
        <div class="section-header">
            <h2 class="section-title">Hiring Preferences</h2>
            <button type="button" class="edit-btn" id="edit-preferences">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        
        <div class="section-content">
            <div class="form-group">
                <label>Open to hiring Persons with Disabilities (PWDs)</label>
                <div class="info-value" id="display-hire-pwd">Loading...</div>
            </div>
            
            <h3 style="margin: 25px 0 15px; color: var(--text-medium);">Types of Disabilities You Can Accommodate:</h3>
            
            <div class="categories-container" id="display-disability-types">
                <!-- Will be populated by JavaScript -->
            </div>
            
            <h3 style="margin: 25px 0 15px; color: var(--text-medium);">Workplace Accessibility Options:</h3>
            
            <div class="accessibility-icons" id="display-accessibility-options">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Website and Social Media Links -->
<div class="profile-section">
    <div class="section-header">
        <h2 class="section-title">Website & Social Media</h2>
        <button type="button" class="edit-btn" id="edit-social">
            <i class="fas fa-edit"></i> Edit
        </button>
    </div>
    
    <div class="section-content">
        <div class="form-group">
            <label>Company Website</label>
            <div class="social-input">
                <div class="social-icon-wrapper website-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="info-value" id="display-website">Loading...</div>
            </div>
        </div>
        
        <div class="form-group">
            <label>LinkedIn</label>
            <div class="social-input">
                <div class="social-icon-wrapper linkedin-icon">
                    <i class="fab fa-linkedin-in"></i>
                </div>
                <div class="info-value" id="display-linkedin">Loading...</div>
            </div>
        </div>
    </div>
</div>
</form>