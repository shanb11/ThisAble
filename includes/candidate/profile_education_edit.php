<div class="edit-form" id="education-edit-form">
    <form id="education-form">
        <input type="hidden" id="education-id" name="education_id" value="">
        
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="education-degree">Degree/Certificate *</label>
                <input type="text" id="education-degree" name="degree" placeholder="e.g. Bachelor of Science in Computer Science" required>
            </div>
            
            <div class="form-group">
                <label for="education-institution">Institution *</label>
                <input type="text" id="education-institution" name="institution" placeholder="e.g. Cavite State University" required>
            </div>
            
            <div class="form-group">
                <label for="education-location">Location</label>
                <input type="text" id="education-location" name="location" placeholder="e.g. Indang, Cavite">
            </div>
            
            <div class="form-group">
                <label for="education-start">Start Date *</label>
                <input type="month" id="education-start" name="start_date" required>
            </div>
            
            <div class="form-group">
                <label for="education-end">End Date</label>
                <input type="month" id="education-end" name="end_date">
                <div class="checkbox-item">
                    <input type="checkbox" id="education-current" name="is_current">
                    <label for="education-current">Currently studying here</label>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="education-description">Description</label>
                <textarea id="education-description" name="description" rows="3" placeholder="Describe your studies, achievements, relevant coursework, etc."></textarea>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="education">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="education">
                <i class="fas fa-save"></i> Save Education
            </button>
        </div>
    </form>
</div>