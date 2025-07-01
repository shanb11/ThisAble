<div class="setting-detail-container" id="resume-docs-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Resume & Document Settings</div>
    </div>
    <form id="resume-docs-form">
        <div class="form-group">
            <label class="form-label">Uploaded Resumes</label>
            <div class="document-item">
                <div class="document-info">
                    <div class="document-icon"><i class="fas fa-file-pdf"></i></div>
                    <div>
                        <div class="document-title">Professional_Resume_2024.pdf <span class="default-badge">Default</span></div>
                        <div class="document-date">Uploaded: April 15, 2025</div>
                    </div>
                </div>
                <div class="document-actions">
                    <div class="document-action" title="Preview"><i class="fas fa-eye"></i></div>
                    <div class="document-action" title="Download"><i class="fas fa-download"></i></div>
                    <div class="document-action" title="Delete"><i class="fas fa-trash"></i></div>
                </div>
            </div>
            <div class="document-item">
                <div class="document-info">
                    <div class="document-icon"><i class="fas fa-file-word"></i></div>
                    <div>
                        <div class="document-title">Technical_Resume_2024.docx</div>
                        <div class="document-date">Uploaded: March 10, 2025</div>
                    </div>
                </div>
                <div class="document-actions">
                    <div class="document-action" title="Preview"><i class="fas fa-eye"></i></div>
                    <div class="document-action" title="Download"><i class="fas fa-download"></i></div>
                    <div class="document-action" title="Make Default"><i class="fas fa-star"></i></div>
                    <div class="document-action" title="Delete"><i class="fas fa-trash"></i></div>
                </div>
            </div>
            <div class="file-upload">
                <label for="resume-upload" class="file-upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload New Resume
                </label>
                <input type="file" id="resume-upload" accept=".pdf,.doc,.docx">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Uploaded Cover Letters</label>
            <div class="document-item">
                <div class="document-info">
                    <div class="document-icon"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <div class="document-title">General_Cover_Letter.pdf <span class="default-badge">Default</span></div>
                        <div class="document-date">Uploaded: April 20, 2025</div>
                    </div>
                </div>
                <div class="document-actions">
                    <div class="document-action" title="Preview"><i class="fas fa-eye"></i></div>
                    <div class="document-action" title="Download"><i class="fas fa-download"></i></div>
                    <div class="document-action" title="Delete"><i class="fas fa-trash"></i></div>
                </div>
            </div>
            <div class="file-upload">
                <label for="cover-letter-upload" class="file-upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload New Cover Letter
                </label>
                <input type="file" id="cover-letter-upload" accept=".pdf,.doc,.docx">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Document Preferences</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="auto-update-dates" checked>
                <label class="form-check-label" for="auto-update-dates">Automatically update document dates</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-document-views" checked>
                <label class="form-check-label" for="track-document-views">Track when employers view my documents</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>