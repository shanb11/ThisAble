<div class="setting-detail-container" id="application-prefs-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Application Preferences</div>
    </div>
    <form id="application-prefs-form">
        <div class="form-group">
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="auto-fill-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="auto-fill-toggle">Enable auto-fill on applications</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Default Application Settings</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="include-cover-letter" checked>
                <label class="form-check-label" for="include-cover-letter">Include default cover letter when available</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="follow-companies" checked>
                <label class="form-check-label" for="follow-companies">Automatically follow companies when applying</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="default-cover-letter-text">Default Cover Letter Text</label>
            <textarea class="form-control" id="default-cover-letter-text" rows="5" placeholder="Enter your default cover letter text here...">Dear Hiring Manager,

I am writing to express my interest in the position at your company. With my background and skills, I believe I would be a valuable addition to your team.

Thank you for considering my application. I look forward to the opportunity to discuss how my experience aligns with your needs.

Sincerely,
John Doe</textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Application History Preferences</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="save-application-history" checked>
                <label class="form-check-label" for="save-application-history">Save application history</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="receive-application-feedback" checked>
                <label class="form-check-label" for="receive-application-feedback">Request feedback on rejected applications</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>