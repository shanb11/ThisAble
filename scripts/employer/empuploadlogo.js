document.addEventListener('DOMContentLoaded', function() {
    // Load existing logo if available
    loadExistingLogo();
});

// File input handling
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('file-input');
const previewContainer = document.getElementById('preview-container');
const logoPreview = document.getElementById('logo-preview');
const removeBtn = document.getElementById('remove-btn');

let currentLogoPath = null;

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

// Highlight drop area when item is dragged over it
['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropArea.style.backgroundColor = '#fff9f2';
}

function unhighlight() {
    dropArea.style.backgroundColor = 'transparent';
}

// Handle dropped files
dropArea.addEventListener('drop', handleDrop, false);
document.body.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length) {
        handleFiles(files);
    }
}

fileInput.addEventListener('change', function() {
    if (this.files.length) {
        handleFiles(this.files);
    }
});

function handleFiles(files) {
    const file = files[0];
    
    // Check file type
    const validTypes = ['image/jpeg', 'image/png', 'image/svg+xml'];
    if (!validTypes.includes(file.type)) {
        showErrorMessage('Please upload a valid image file (PNG, JPG, or SVG)');
        return;
    }
    
    // Check file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showErrorMessage('File size exceeds 5MB limit');
        return;
    }
    
    // Upload file via AJAX
    uploadLogo(file);
}

function uploadLogo(file) {
    const formData = new FormData();
    formData.append('logo', file);
    
    // Show loading state
    showLoadingState(true);
    
    fetch('../../backend/employer/upload_company_logo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showLoadingState(false);
        
        if (data.success) {
            // Show preview
            logoPreview.src = '../../' + data.file_path;
            previewContainer.style.display = 'block';
            dropArea.style.display = 'none';
            currentLogoPath = data.file_path;
            
            showSuccessMessage('Logo uploaded successfully!');
            
            // Update progress indicator if exists
            updateProgressIndicator(data.progress);
        } else {
            showErrorMessage(data.message || 'Failed to upload logo');
        }
    })
    .catch(error => {
        showLoadingState(false);
        console.error('Upload error:', error);
        showErrorMessage('Failed to upload logo. Please try again.');
    });
}

function loadExistingLogo() {
    fetch('../../backend/employer/get_setup_progress.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.company_data.company_logo_path) {
                const logoPath = data.company_data.company_logo_path;
                logoPreview.src = '../../' + logoPath;
                previewContainer.style.display = 'block';
                dropArea.style.display = 'none';
                currentLogoPath = logoPath;
                
                // Update progress indicator
                updateProgressIndicator(data.progress.completion_percentage);
            }
        })
        .catch(error => {
            console.error('Error loading existing logo:', error);
        });
}

// Remove logo
removeBtn.addEventListener('click', function() {
    logoPreview.src = '';
    previewContainer.style.display = 'none';
    dropArea.style.display = 'flex';
    fileInput.value = '';
    currentLogoPath = null;
});

function showLoadingState(isLoading) {
    const selectBtn = document.querySelector('.select-file-btn');
    if (isLoading) {
        selectBtn.textContent = 'Uploading...';
        selectBtn.disabled = true;
    } else {
        selectBtn.textContent = 'Select File';
        selectBtn.disabled = false;
    }
}

function showSuccessMessage(message) {
    showMessage(message, 'success');
}

function showErrorMessage(message) {
    showMessage(message, 'error');
}

function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.temp-message');
    existingMessages.forEach(msg => msg.remove());
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `temp-message ${type}-message`;
    messageDiv.innerHTML = `
        <div class="message-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add CSS for messages
    const style = document.createElement('style');
    style.textContent = `
        .temp-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .success-message {
            background: #4CAF50;
            color: white;
        }
        .error-message {
            background: #f44336;
            color: white;
        }
        .message-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .message-content i {
            font-size: 18px;
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(messageDiv);
    
    // Remove after 4 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 4000);
}

function updateProgressIndicator(percentage) {
    const progressFill = document.querySelector('.progress-fill');
    const progressPercentage = document.querySelector('#progress-percentage');
    
    if (progressFill && progressPercentage) {
        progressFill.style.width = `${percentage}%`;
        progressPercentage.textContent = `${percentage}%`;
    }
}

// Navigation functions
function goBack() {
    window.location.href = './empaccsetup.php';
}

function continueToNext() {
    // If no logo uploaded, skip this step but continue
    if (!currentLogoPath) {
        // Mark step as complete even without logo (optional step)
        fetch('../../backend/employer/save_setup_step.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'step=logo_complete'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = './empdescription.php';
            } else {
                showErrorMessage('Failed to save progress. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error saving progress:', error);
            // Continue anyway
            window.location.href = './empdescription.php';
        });
    } else {
        // Logo already uploaded, just continue
        window.location.href = './empdescription.php';
    }
}

// Make the entire document a drop zone
document.body.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropArea.style.backgroundColor = '#fff9f2';
});