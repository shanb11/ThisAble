<div class="edit-form" id="experience-edit-form">
    <form id="experience-form">
        <input type="hidden" id="experience-id" name="experience_id" value="">
        
        <div class="form-grid">
            <div class="form-group">
                <label for="experience-title">Job Title *</label>
                <input type="text" id="experience-title" name="job_title" placeholder="e.g. Web Developer" required>
            </div>
            
            <div class="form-group">
                <label for="experience-company">Company *</label>
                <input type="text" id="experience-company" name="company" placeholder="e.g. Tech Solutions Inc." required>
            </div>
            
            <div class="form-group">
                <label for="experience-location">Location</label>
                <input type="text" id="experience-location" name="location" placeholder="e.g. Makati City, Philippines">
            </div>
            
            <div class="form-group">
                <label for="experience-start">Start Date *</label>
                <input type="month" id="experience-start" name="start_date" required>
            </div>
            
            <div class="form-group">
                <label for="experience-end">End Date</label>
                <input type="month" id="experience-end" name="end_date">
                <div class="checkbox-item">
                    <input type="checkbox" id="experience-current" name="is_current">
                    <label for="experience-current">I currently work here</label>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="experience-description">Description & Responsibilities</label>
                <textarea id="experience-description" name="description" rows="4" placeholder="Describe your role, responsibilities, achievements, and key contributions"></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="experience">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="experience">
                <i class="fas fa-save"></i> Save Experience
            </button>
        </div>
    </form>
</div>