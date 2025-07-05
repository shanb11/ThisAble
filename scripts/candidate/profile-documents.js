/**
 * Profile Documents Management
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
        document.getElementById('document-upload-form')?.reset();
        removeSelectedFile();
    }
}

/**
 * Load user documents from API
 */
async function loadUserDocuments() {
    const loadingDiv = document.getElementById('documents-loading');
    const gridDiv = document.getElementById('documents-grid');
    const errorDiv = document.getElementById('documents-error');

    try {
        // Show loading state
        if (loadingDiv) loadingDiv.style.display = 'block';
        if (gridDiv) gridDiv.style.display = 'none';
        if (errorDiv) errorDiv.style.display = 'none';

        const response = await fetch('../../backend/candidate/get_documents.php');
        const data = await response.json();

        if (data.success) {
            currentDocuments = data.data.documents;
            renderDocuments(data.data.documents, data.data.counts);
            updateDocumentsSummary(data.data.counts);
        } else {
            throw new Error(data.message || 'Failed to load documents');
        }

    } catch (error) {
        console.error('Error loading documents:', error);
        showDocumentsError(error.message);
    } finally {
        if (loadingDiv) loadingDiv.style.display = 'none';
        if (gridDiv) gridDiv.style.display = 'block';
    }
}

/**
 * Render documents in the UI
 */
function renderDocuments(documents, counts) {
    const types = ['diploma', 'certificate', 'license', 'other'];
    
    types.forEach(type => {
        const container = document.getElementById(`${type}-items`);
        const emptyState = document.getElementById(`${type}-empty`);
        const countBadge = document.getElementById(`${type}-count`);
        
        if (!container) return;

        // Update count badge
        if (countBadge) {
            countBadge.textContent = counts[type] || 0;
        }

        // Clear existing items except empty state
        const existingItems = container.querySelectorAll('.document-item');
        existingItems.forEach(item => item.remove());

        if (documents[type] && documents[type].length > 0) {
            // Hide empty state
            if (emptyState) emptyState.style.display = 'none';
            
            // Render documents
            documents[type].forEach(doc => {
                const docElement = createDocumentElement(doc);
                container.appendChild(docElement);
            });
        } else {
            // Show empty state
            if (emptyState) emptyState.style.display = 'block';
        }
    });
}

/**
 * Create document element from template
 */
function createDocumentElement(document) {
    const template = document.getElementById('document-item-template');
    if (!template) return null;

    const clone = template.content.cloneNode(true);
    const docElement = clone.querySelector('.document-item');
    
    // Set data attributes
    docElement.setAttribute('data-document-id', document.document_id);
    docElement.setAttribute('data-document-type', document.document_type);

    // Fill in document information
    const nameElement = clone.querySelector('.document-name');
    const sizeElement = clone.querySelector('.document-size');
    const dateElement = clone.querySelector('.document-date');
    const statusElement = clone.querySelector('.verification-badge');

    if (nameElement) nameElement.textContent = document.document_name;
    if (sizeElement) sizeElement.textContent = document.formatted_size;
    if (dateElement) dateElement.textContent = document.formatted_date;
    
    // Set verification status
    if (statusElement) {
        if (document.is_verified) {
            statusElement.className = 'verification-badge verified';
            statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Verified';
        } else {
            statusElement.className = 'verification-badge unverified';
            statusElement.innerHTML = '<i class="fas fa-clock"></i> Pending';
        }
    }

    // Add action button handlers
    const viewBtn = clone.querySelector('.view-btn');
    const downloadBtn = clone.querySelector('.download-btn');
    const deleteBtn = clone.querySelector('.delete-btn');

    if (viewBtn) {
        viewBtn.addEventListener('click', () => viewDocument(document));
    }
    if (downloadBtn) {
        downloadBtn.addEventListener('click', () => downloadDocument(document));
    }
    if (deleteBtn) {
        deleteBtn.addEventListener('click', () => showDeleteConfirmation(document));
    }

    return docElement;
}

/**
 * Handle document upload
 */
async function handleDocumentUpload(event) {
    event.preventDefault();
    
    if (isUploading) return;

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('upload-submit-btn');
    const progressContainer = document.getElementById('upload-progress');
    const progressFill = document.getElementById('progress-fill');
    const progressPercentage = document.getElementById('progress-percentage');

    try {
        isUploading = true;
        
        // Disable submit button
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        }

        // Show progress
        if (progressContainer) progressContainer.style.display = 'block';

        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();
        
        // Upload progress handler
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                if (progressFill) progressFill.style.width = percentComplete + '%';
                if (progressPercentage) progressPercentage.textContent = Math.round(percentComplete) + '%';
            }
        });

        // Response handler
        xhr.addEventListener('load', () => {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    showSuccessMessage(response.message);
                    closeDocumentForm();
                    loadUserDocuments(); // Reload documents
                } else {
                    throw new Error(response.message || 'Upload failed');
                }
            } catch (error) {
                showErrorMessage('Upload failed: ' + error.message);
            }
        });

        xhr.addEventListener('error', () => {
            showErrorMessage('Upload failed: Network error');
        });

        // Send request
        xhr.open('POST', '../../backend/candidate/upload_document.php');
        xhr.send(formData);

    } catch (error) {
        console.error('Upload error:', error);
        showErrorMessage('Upload failed: ' + error.message);
    } finally {
        isUploading = false;
        
        // Reset submit button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Document';
        }
        
        // Hide progress
        if (progressContainer) progressContainer.style.display = 'none';
    }
}

/**
 * View document in new tab
 */
function viewDocument(document) {
    const viewUrl = `../../backend/candidate/view_document.php?document_id=${document.document_id}`;
    window.open(viewUrl, '_blank');
}

/**
 * Download document
 */
function downloadDocument(document) {
    const downloadUrl = `../../backend/candidate/view_document.php?document_id=${document.document_id}&download=1`;
    window.open(downloadUrl, '_blank');
}

/**
 * Show delete confirmation modal
 */
function showDeleteConfirmation(document) {
    const modal = document.getElementById('delete-document-modal');
    const nameElement = document.getElementById('delete-document-name');
    const typeElement = document.getElementById('delete-document-type');
    const confirmBtn = document.getElementById('confirm-delete-btn');

    if (modal && nameElement && typeElement && confirmBtn) {
        nameElement.textContent = document.document_name;
        typeElement.textContent = document.document_type.toUpperCase();
        
        // Set up confirm button
        confirmBtn.onclick = () => deleteDocument(document.document_id);
        
        modal.style.display = 'flex';
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
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ document_id: documentId })
        });

        const data = await response.json();

        if (data.success) {
            showSuccessMessage(data.message);
            closeDeleteModal();
            loadUserDocuments(); // Reload documents
        } else {
            throw new Error(data.message || 'Delete failed');
        }

    } catch (error) {
        console.error('Delete error:', error);
        showErrorMessage('Delete failed: ' + error.message);
    }
}

/**
 * Update documents summary
 */
function updateDocumentsSummary(counts) {
    const summaryDiv = document.getElementById('documents-summary');
    const totalElement = document.getElementById('total-documents');
    const verifiedElement = document.getElementById('verified-documents');

    if (summaryDiv && counts.total > 0) {
        summaryDiv.style.display = 'block';
        if (totalElement) totalElement.textContent = counts.total;
        if (verifiedElement) verifiedElement.textContent = counts.verified;
    } else if (summaryDiv) {
        summaryDiv.style.display = 'none';
    }
}

/**
 * Show documents error
 */
function showDocumentsError(message) {
    const errorDiv = document.getElementById('documents-error');
    const errorMessage = document.getElementById('documents-error-message');
    const gridDiv = document.getElementById('documents-grid');

    if (errorDiv) {
        errorDiv.style.display = 'block';
        if (errorMessage) errorMessage.textContent = message;
    }
    if (gridDiv) gridDiv.style.display = 'none';
}

/**
 * Show success message
 */
function showSuccessMessage(message) {
    // You can integrate this with your existing toast/notification system
    console.log('Success:', message);
    
    // Simple alert for now - replace with your toast system
    alert('Success: ' + message);
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    // You can integrate this with your existing toast/notification system
    console.error('Error:', message);
    
    // Simple alert for now - replace with your toast system
    alert('Error: ' + message);
}