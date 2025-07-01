<div class="setting-detail-container" id="privacy-preferences-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Privacy Preferences</div>
    </div>
    <form id="privacy-preferences-form">
        <div class="form-group">
            <label class="form-label">Profile Visibility</label>
            <select class="select-control" id="profile-visibility">
                <option value="all">Visible to all employers</option>
                <option value="verified">Visible only to verified employers</option>
                <option value="none">Not visible in search (private profile)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Visibility to Other Applicants</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="peer-visibility-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="peer-visibility-toggle">Show my profile on applicant leaderboards</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="search-listing" checked>
                <label class="form-check-label" for="search-listing">Show in Search Results</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="data-collection" checked>
                <label class="form-check-label" for="data-collection">Allow Data Collection for Personalization</label>
            </div>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="third-party-sharing">
                <label class="form-check-label" for="third-party-sharing">Share Data with Third-party Partners</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>