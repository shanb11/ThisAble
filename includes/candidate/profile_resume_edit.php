<div class="edit-form" id="resume-edit-form">
    <form id="resume-form" enctype="multipart/form-data">
        <div class="upload-area" id="upload-area">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Drag & drop your resume file here, or click to browse</p>
            <p class="file-types">Supported formats: PDF, DOCX, DOC</p>
            <input type="file" id="resume-file" name="resume_file" accept=".pdf,.docx,.doc" class="file-input">
        </div>
        
        <div class="upload-preview" id="upload-preview" style="display: none;">
            <div class="preview-file">
                <i class="fas fa-file-alt preview-icon"></i>
                <span class="preview-filename">No file selected</span>
            </div>
            <button type="button" class="btn cancel-upload-btn">
                <i class="fas fa-times"></i> Remove
            </button>
        </div>
        
        <div class="form-actions">
            <button type="button" class="btn cancel-btn" data-section="resume">Cancel</button>
            <button type="submit" class="btn save-btn" data-section="resume">Upload Resume</button>
        </div>
    </form>
</div>