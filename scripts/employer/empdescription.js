document.addEventListener('DOMContentLoaded', function() {
    // Load existing data when page loads
    loadExistingData();
    
    // Initialize character counters and validation
    initializeFormHandlers();
});

// Character counters
const aboutUsTextarea = document.getElementById('about-us');
const missionVisionTextarea = document.getElementById('mission-vision');
const whyJoinTextarea = document.getElementById('why-join');

const aboutUsCount = document.getElementById('about-us-count');
const missionVisionCount = document.getElementById('mission-vision-count');
const whyJoinCount = document.getElementById('why-join-count');

// Error messages
const aboutUsError = document.getElementById('about-us-error');
const missionVisionError = document.getElementById('mission-vision-error');
const whyJoinError = document.getElementById('why-join-error');

function initializeFormHandlers() {
    // Update character counters
    aboutUsTextarea.addEventListener('input', function() {
        aboutUsCount.textContent = this.value.length;
        if (this.value.length > 0) {
            aboutUsError.style.visibility = 'hidden';
        }
        autoResize(this);
    });
    
    missionVisionTextarea.addEventListener('input', function() {
        missionVisionCount.textContent = this.value.length;
        if (this.value.length > 0) {
            missionVisionError.style.visibility = 'hidden';
        }
        autoResize(this);
    });
    
    whyJoinTextarea.addEventListener('input', function() {
        whyJoinCount.textContent = this.value.length;
        if (this.value.length > 0) {
            whyJoinError.style.visibility = 'hidden';
        }
        autoResize(this);
    });
    
    // Apply auto-resize to all textareas initially
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        autoResize(textarea);
    });
}

function loadExistingData() {
    // TRY MULTIPLE PATH OPTIONS
    const possiblePaths = [
        '/ThisAble/backend/employer/get_setup_progress.php',
        '../../backend/employer/get_setup_progress.php',
        '../../../backend/employer/get_setup_progress.php'
    ];
    
    tryFetchWithPaths(possiblePaths, 0);
}

function tryFetchWithPaths(paths, index) {
    if (index >= paths.length) {
        console.error('All path attempts failed for get_setup_progress.php');
        return;
    }
    
    fetch(paths[index])
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.company_data) {
                const companyData = data.company_data;
                
                // Populate textareas with existing data
                if (companyData.company_description) {
                    aboutUsTextarea.value = companyData.company_description;
                    aboutUsCount.textContent = companyData.company_description.length;
                    autoResize(aboutUsTextarea);
                }
                
                if (companyData.mission_vision) {
                    missionVisionTextarea.value = companyData.mission_vision;
                    missionVisionCount.textContent = companyData.mission_vision.length;
                    autoResize(missionVisionTextarea);
                }
                
                if (companyData.why_join_us) {
                    whyJoinTextarea.value = companyData.why_join_us;
                    whyJoinCount.textContent = companyData.why_join_us.length;
                    autoResize(whyJoinTextarea);
                }
                
                // Update progress indicator
                updateProgressIndicator(data.progress.completion_percentage);
            }
        })
        .catch(error => {
            console.log(`Path ${paths[index]} failed:`, error);
            // Try next path
            tryFetchWithPaths(paths, index + 1);
        });
}

// Navigation functions
function goBack() {
    window.location.href = './empuploadlogo.php';
}

function continueToNext() {
    // Validate fields
    let isValid = true;
    
    if (aboutUsTextarea.value.trim() === '') {
        aboutUsError.style.visibility = 'visible';
        isValid = false;
    } else {
        aboutUsError.style.visibility = 'hidden';
    }
    
    if (missionVisionTextarea.value.trim() === '') {
        missionVisionError.style.visibility = 'visible';
        isValid = false;
    } else {
        missionVisionError.style.visibility = 'hidden';
    }
    
    if (whyJoinTextarea.value.trim() === '') {
        whyJoinError.style.visibility = 'visible';
        isValid = false;
    } else {
        whyJoinError.style.visibility = 'hidden';
    }
    
    if (isValid) {
        // Save data via AJAX
        saveCompanyDescription();
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.error-message[style*="visible"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

function saveCompanyDescription() {
    const aboutUs = aboutUsTextarea.value.trim();
    const missionVision = missionVisionTextarea.value.trim();
    const whyJoin = whyJoinTextarea.value.trim();
    
    // Show loading state
    showLoadingState(true);
    
    const formData = new FormData();
    formData.append('about_us', aboutUs);
    formData.append('mission_vision', missionVision);
    formData.append('why_join', whyJoin);
    
    // TRY MULTIPLE PATHS FOR SAVE
    const savePaths = [
        '/ThisAble/backend/employer/save_company_description.php',
        '../../backend/employer/save_company_description.php',
        '../../../backend/employer/save_company_description.php'
    ];
    
    trySaveWithPaths(savePaths, 0, formData);
}

function trySaveWithPaths(paths, index, formData) {
    if (index >= paths.length) {
        showLoadingState(false);
        showErrorMessage('Unable to save data. Please check your connection.');
        return;
    }
    
    fetch(paths[index], {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON - likely a PHP error');
        }
        
        return response.json();
    })
    .then(data => {
        showLoadingState(false);
        
        if (data.success) {
            showSuccessMessage('Company description saved successfully!');
            
            // Update progress indicator
            updateProgressIndicator(data.progress);
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = './empreferences.php';
            }, 1500);
        } else {
            showErrorMessage(data.message || 'Failed to save company description');
        }
    })
    .catch(error => {
        console.log(`Save path ${paths[index]} failed:`, error);
        // Try next path
        trySaveWithPaths(paths, index + 1, formData);
    });
}

function showLoadingState(isLoading) {
    const continueBtn = document.querySelector('.continue-btn');
    if (continueBtn) {
        if (isLoading) {
            continueBtn.textContent = 'Saving...';
            continueBtn.disabled = true;
        } else {
            continueBtn.textContent = 'Continue';
            continueBtn.disabled = false;
        }
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
    if (!document.querySelector('#message-styles')) {
        const style = document.createElement('style');
        style.id = 'message-styles';
        style.textContent = `
            .temp-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 1000;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                font-family: Arial, sans-serif;
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
    }
    
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

// Auto-resize textareas
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
}