<?php
/**
 * Profile Documents Upload Form
 * Save as: includes/candidate/profile_documents_edit.php
 */
?>

<div class="edit-form" id="documents-edit-form" style="display: none;">
    <div class="form-header">
        <h3><i class="fas fa-upload"></i> Upload Document</h3>
        <button type="button" class="close-form-btn" onclick="closeDocumentForm()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <form id="document-upload-form" enctype="multipart/form-data">
        <div class="form-grid">
            
            <!-- Document Type Selection -->
            <div class="form-group full-width">
                <label for="document-type">
                    <i class="fas fa-file-alt"></i> Document Type *
                </label>
                <select id="document-type" name="document_type" required>
                    <option value="">Select document type...</option>
                    <option value="diploma">🎓 Diploma/Degree</option>
                    <option value="certificate">🏆 Certificate</option>
                    <option value="license">📜 License</option>
                    <option value="other">📁 Other Document</option>
                </select>
            </div>

            <!-- Document Name -->
            <div class="form-group full-width">
                <label for="document-name">
                    <i class="fas fa-tag"></i> Document Name
                </label>
                <input 
                    type="text" 
                    id="document-name" 
                    name="document_name" 
                    placeholder="e.g., Bachelor of Science in Computer Science"
                    maxlength="255"
                >
                <small class="form-hint">Give your document a descriptive name (optional)</small>
            </div>

            <!-- File Upload -->
            <div class="form-group full-width">
                <label for="document-file">
                    <i class="fas fa-file-pdf"></i> Select PDF File *
                </label>
                <div class="file-upload-container">
                    <input 
                        type="file" 
                        id="document-file" 
                        name="document_file" 
                        accept=".pdf,application/pdf" 
                        required
                    >
                    <div class="file-upload-area" id="file-upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <strong>Click to browse</strong> or drag and drop your PDF file here
                        </div>
                        <div class="upload-restrictions">
                            <small>PDF files only • Maximum 10MB</small>
                        </div>
                    </div>
                    <div class="file-preview" id="file-preview" style="display: none;">
                        <div class="preview-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="preview-info">
                            <span class="file-name" id="preview-filename"></span>
                            <span class="file-size" id="preview-filesize"></span>
                        </div>
                        <button type="button" class="remove-file-btn" onclick="removeSelectedFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Progress -->
            <div class="form-group full-width">
                <div class="upload-progress" id="upload-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <div class="progress-text">
                        <span id="progress-percentage">0%</span>
                        <span id="progress-status">Uploading...</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Upload Guidelines -->
        <div class="upload-guidelines">
            <h4><i class="fas fa-info-circle"></i> Upload Guidelines</h4>
            <ul class="guidelines-list">
                <li><i class="fas fa-check"></i> Only PDF files are accepted for security</li>
                <li><i class="fas fa-check"></i> Maximum file size is 10MB</li>
                <li><i class="fas fa-check"></i> Ensure documents are clear and readable</li>
                <li><i class="fas fa-check"></i> For diplomas, only one can be uploaded (replaces existing)</li>
                <li><i class="fas fa-check"></i> Certificates and licenses can be multiple</li>
            </ul>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn secondary-btn" onclick="closeDocumentForm()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="submit" class="btn primary-btn" id="upload-submit-btn">
                <i class="fas fa-upload"></i> Upload Document
            </button>
        </div>

    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-document-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this document?</p>
            <div class="document-to-delete" id="document-to-delete">
                <strong id="delete-document-name"></strong>
                <small id="delete-document-type"></small>
            </div>
            <p class="warning-text">
                <i class="fas fa-warning"></i> 
                This action cannot be undone.
            </p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn secondary-btn" onclick="closeDeleteModal()">
                Cancel
            </button>
            <button type="button" class="btn danger-btn" id="confirm-delete-btn">
                <i class="fas fa-trash"></i> Delete Document
            </button>
        </div>
    </div>
</div>

<script>
// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('document-file');
    const uploadArea = document.getElementById('file-upload-area');
    const filePreview = document.getElementById('file-preview');

    // File input change handler
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    // Drag and drop handlers
    if (uploadArea) {
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleFileDrop);
        uploadArea.addEventListener('click', () => fileInput?.click());
    }
});

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        showFilePreview(file);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.classList.add('drag-over');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
}

function handleFileDrop(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        document.getElementById('document-file').files = files;
        showFilePreview(file);
    }
}

function showFilePreview(file) {
    const uploadArea = document.getElementById('file-upload-area');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('preview-filename');
    const fileSize = document.getElementById('preview-filesize');

    if (uploadArea && filePreview && fileName && fileSize) {
        uploadArea.style.display = 'none';
        filePreview.style.display = 'flex';
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
    }
}

function removeSelectedFile() {
    const fileInput = document.getElementById('document-file');
    const uploadArea = document.getElementById('file-upload-area');
    const filePreview = document.getElementById('file-preview');

    if (fileInput) fileInput.value = '';
    if (uploadArea) uploadArea.style.display = 'block';
    if (filePreview) filePreview.style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function closeDocumentForm() {
    const form = document.getElementById('documents-edit-form');
    if (form) {
        form.style.display = 'none';
        form.classList.remove('active');
        // Reset form
        document.getElementById('document-upload-form')?.reset();
        removeSelectedFile();
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-document-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}
</script>