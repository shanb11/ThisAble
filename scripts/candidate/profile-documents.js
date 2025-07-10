/**
 * Profile Documents Management - COMPLETE WORKING VERSION
 * Save as: scripts/candidate/profile-documents.js
 */

// Global variables
let currentDocuments = {
    diploma: [],
    certificate: [],
    license: [],
    other: []
};

let isUploading = false;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Profile Documents JS loaded');
    initializeDocuments();
});

/**
 * Initialize document functionality
 */
function initializeDocuments() {
    // Add document button handler
    const addDocumentBtn = document.getElementById('add-document-btn');
    if (addDocumentBtn) {
        addDocumentBtn.addEventListener('click', openDocumentUploadForm);
    }

    // Form submission handler
    const uploadForm = document.getElementById('document-upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleDocumentUpload);
    }

    // Load existing documents
    loadUserDocuments();
}

/**
 * Open document upload form
 */
function openDocumentUploadForm() {
    const form = document.getElementById('documents-edit-form');
    if (form) {
        form.style.display = 'block';
        form.classList.add('active');
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Reset form
        const uploadFormEl = document.getElementById('document-upload-form');
        if (uploadFormEl) uploadFormEl.reset();
        removeSelectedFile();
    }
}

/**
 * Load user documents from API
 */
async function loadUserDocuments() {
    const loadingDiv = document.getElementById('documents-loading');
    const containerDiv = document.getElementById('documents-container');
    const errorDiv = document.getElementById('documents-error');

    try {
        console.log('📄 Starting to load documents...');
        
        // Show loading state
        if (loadingDiv) loadingDiv.style.display = 'block';
        if (containerDiv) containerDiv.style.display = 'none';
        if (errorDiv) errorDiv.style.display = 'none';

        const response = await fetch('../../backend/candidate/get_documents.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📄 API Response:', data);

        if (data.success) {
            currentDocuments = data.data.documents;
            console.log('📄 Documents loaded:', currentDocuments);
            
            // Render all document categories
            renderDocumentCategory('diploma');
            renderDocumentCategory('certificate');
            renderDocumentCategory('license');
            renderDocumentCategory('other');
            
            // Update counts
            updateDocumentCounts(data.data.counts);
            
            // Show container
            if (loadingDiv) loadingDiv.style.display = 'none';
            if (containerDiv) containerDiv.style.display = 'block';
            
        } else {
            throw new Error(data.message || 'Failed to load documents');
        }

    } catch (error) {
        console.error('❌ Error loading documents:', error);
        
        // Show error state
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (containerDiv) containerDiv.style.display = 'none';
        if (errorDiv) {
            errorDiv.style.display = 'block';
            const errorMsg = document.getElementById('documents-error-message');
            if (errorMsg) errorMsg.textContent = error.message;
        }
    }
}

/**
 * Render documents for a specific category
 */
function renderDocumentCategory(type) {
    const itemsContainer = document.getElementById(`${type}-items`);
    if (!itemsContainer) {
        console.warn(`Container not found for type: ${type}`);
        return;
    }

    const documents = currentDocuments[type] || [];
    console.log(`📄 Rendering ${type} documents:`, documents);
    
    if (documents.length === 0) {
        // Show empty state
        itemsContainer.innerHTML = `
            <div class="empty-document-state">
                <i class="fas fa-file-upload"></i>
                <p>No ${type}s uploaded yet</p>
                <small>Upload your ${type === 'other' ? 'documents' : type}s</small>
            </div>
        `;
    } else {
        // Render documents in resume style
        itemsContainer.innerHTML = documents.map(doc => renderDocumentItem(doc)).join('');
    }
}

/**
 * Render individual document item (resume style)
 */
function renderDocumentItem(doc) {
    // Get file extension and set icon
    const ext = doc.original_filename.split('.').pop().toLowerCase();
    let fileIcon = 'fa-file-alt';
    let iconColor = '#666';
    
    if (ext === 'pdf') {
        fileIcon = 'fa-file-pdf';
        iconColor = '#f44336';
    } else if (['doc', 'docx'].includes(ext)) {
        fileIcon = 'fa-file-word';
        iconColor = '#2196f3';
    }

    const viewUrl = `../../backend/candidate/view_document.php?action=view&document_id=${doc.document_id}`;
    const downloadUrl = `../../backend/candidate/view_document.php?action=download&document_id=${doc.document_id}`;

    return `
        <div class="current-resume document-item" data-document-id="${doc.document_id}">
            <div class="resume-preview">
                <i class="fas ${fileIcon}" style="color: ${iconColor};"></i>
                <div class="resume-info">
                    <span class="resume-filename">${doc.document_name || doc.original_filename}</span>
                    <span class="resume-meta">
                        <span class="resume-date">Uploaded: ${doc.formatted_date}</span>
                        <span class="resume-size">(${doc.formatted_size})</span>
                    </span>
                </div>
            </div>
            
            <div class="resume-actions">
                <button class="btn view-resume-btn" onclick="viewDocument('${viewUrl}', '${doc.original_filename}')" title="View document">
                    <i class="fas fa-eye"></i> View
                </button>
                
                <button class="btn download-resume-btn" onclick="downloadDocument('${downloadUrl}')" title="Download file">
                    <i class="fas fa-download"></i> Download
                </button>
                        
                <button class="btn delete-resume-btn" onclick="confirmDeleteDocument(${doc.document_id}, '${doc.document_name || doc.original_filename}')" title="Delete Document">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            
            <!-- File type info -->
            <div class="file-type-info">
                ${ext === 'pdf' ? `
                    <div class="info-badge success">
                        <i class="fas fa-check-circle"></i>
                        <span>Can be viewed in browser</span>
                    </div>
                ` : `
                    <div class="info-badge info">
                        <i class="fas fa-info-circle"></i>
                        <span>Download to view</span>
                    </div>
                `}
            </div>
            
            <!-- Document statistics -->
            <div class="resume-stats">
                <div class="stat-item">
                    <i class="fas fa-file-alt stat-icon"></i>
                    <span class="stat-label">Type</span>
                    <span class="stat-value">${ext.toUpperCase()}</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-hdd stat-icon"></i>
                    <span class="stat-label">Size</span>
                    <span class="stat-value">${doc.formatted_size}</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-calendar stat-icon"></i>
                    <span class="stat-label">Updated</span>
                    <span class="stat-value">${doc.formatted_date.split(' ')[0]} ${doc.formatted_date.split(' ')[1]}</span>
                </div>
            </div>
        </div>
    `;
}

/**
 * Update document counts
 */
function updateDocumentCounts(counts) {
    if (!counts) return;
    
    const diplomaCount = document.getElementById('diploma-count');
    const certificateCount = document.getElementById('certificate-count');
    const licenseCount = document.getElementById('license-count');
    const otherCount = document.getElementById('other-count');
    
    if (diplomaCount) diplomaCount.textContent = counts.diploma || 0;
    if (certificateCount) certificateCount.textContent = counts.certificate || 0;
    if (licenseCount) licenseCount.textContent = counts.license || 0;
    if (otherCount) otherCount.textContent = counts.other || 0;
}

/**
 * View document in new tab
 */
function viewDocument(url, filename) {
    console.log('📄 Opening document:', filename);
    const newTab = window.open(url, '_blank');
    if (!newTab) {
        alert('Please allow popups to view documents');
    }
}

/**
 * Download document
 */
function downloadDocument(url) {
    console.log('📄 Downloading document from:', url);
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Confirm document deletion
 */
function confirmDeleteDocument(documentId, documentName) {
    if (confirm(`Are you sure you want to delete "${documentName}"?`)) {
        deleteDocument(documentId);
    }
}

/**
 * Delete document
 */
async function deleteDocument(documentId) {
    try {
        const response = await fetch('../../backend/candidate/delete_document.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ document_id: documentId })
        });

        const data = await response.json();
        
        if (data.success) {
            // Reload documents
            loadUserDocuments();
            alert('Document deleted successfully!');
        } else {
            alert('Error deleting document: ' + data.message);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Error deleting document. Please try again.');
    }
}

/**
 * Handle document upload
 */
async function handleDocumentUpload(event) {
    event.preventDefault();
    
    if (isUploading) {
        return;
    }
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Validate required fields
    const documentType = formData.get('document_type');
    const documentFile = formData.get('document_file');
    
    if (!documentType || !documentFile || documentFile.size === 0) {
        alert('Please select a document type and file');
        return;
    }
    
    try {
        isUploading = true;
        
        // Show upload progress
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            submitBtn.disabled = true;
        }
        
        const response = await fetch('../../backend/candidate/upload_document.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Success - close form and reload documents
            closeDocumentForm();
            loadUserDocuments();
            alert('Document uploaded successfully!');
        } else {
            throw new Error(data.message || 'Upload failed');
        }
        
    } catch (error) {
        console.error('Upload error:', error);
        alert('Error uploading document: ' + error.message);
    } finally {
        isUploading = false;
        
        // Reset submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = originalText || '<i class="fas fa-upload"></i> Upload Document';
            submitBtn.disabled = false;
        }
    }
}

/**
 * Close document upload form
 */
function closeDocumentForm() {
    const form = document.getElementById('documents-edit-form');
    if (form) {
        form.style.display = 'none';
        form.classList.remove('active');
        // Reset form
        const uploadFormEl = document.getElementById('document-upload-form');
        if (uploadFormEl) uploadFormEl.reset();
        removeSelectedFile();
    }
}

/**
 * Remove selected file preview
 */
function removeSelectedFile() {
    const fileInput = document.getElementById('document-file');
    const uploadArea = document.getElementById('file-upload-area');
    const filePreview = document.getElementById('file-preview');

    if (fileInput) fileInput.value = '';
    if (uploadArea) uploadArea.style.display = 'block';
    if (filePreview) filePreview.style.display = 'none';
}