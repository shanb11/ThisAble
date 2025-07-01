document.addEventListener('DOMContentLoaded', function() {
    // Load existing data when page loads
    loadExistingData();
    
    // Initialize form handlers
    initializeFormHandlers();
});

// Get DOM elements
const companyUrlInput = document.getElementById('company-url');
const facebookUrlInput = document.getElementById('facebook-url');
const linkedinUrlInput = document.getElementById('linkedin-url');

// Error messages
const companyUrlError = document.getElementById('company-url-error');
const facebookUrlError = document.getElementById('facebook-url-error');
const linkedinUrlError = document.getElementById('linkedin-url-error');

function initializeFormHandlers() {
    // Add event listeners for URL validation
    companyUrlInput.addEventListener('input', function() {
        validateUrl(this, companyUrlError);
    });
    
    facebookUrlInput.addEventListener('input', function() {
        validateUsername(this, facebookUrlError);
    });
    
    linkedinUrlInput.addEventListener('input', function() {
        validateUsername(this, linkedinUrlError);
    });
}

function loadExistingData() {
    fetch('../../backend/employer/get_setup_progress.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const socialData = data.social_data;
                
                if (socialData) {
                    // Load company URL
                    if (socialData.website_url) {
                        companyUrlInput.value = socialData.website_url;
                    }
                    
                    // Load Facebook URL (extract username part)
                    if (socialData.facebook_url) {
                        const facebookUsername = socialData.facebook_url.replace('https://facebook.com/', '');
                        facebookUrlInput.value = facebookUsername;
                    }
                    
                    // Load LinkedIn URL (extract company name part)
                    if (socialData.linkedin_url) {
                        const linkedinCompany = socialData.linkedin_url.replace('https://linkedin.com/company/', '');
                        linkedinUrlInput.value = linkedinCompany;
                    }
                }
                
                // Update progress indicator
                updateProgressIndicator(data.progress.completion_percentage);
            }
        })
        .catch(error => {
            console.error('Error loading existing data:', error);
        });
}

// URL validation functions
function validateUrl(input, errorElement) {
    if (input.value && !input.value.match(/^https?:\/\/.+\..+/)) {
        errorElement.style.visibility = 'visible';
        return false;
    } else {
        errorElement.style.visibility = 'hidden';
        return true;
    }
}

function validateUsername(input, errorElement) {
    if (input.value && !input.value.match(/^[a-zA-Z0-9._-]+$/)) {
        errorElement.style.visibility = 'visible';
        return false;
    } else {
        errorElement.style.visibility = 'hidden';
        return true;
    }
}

// Navigation functions
function goBack() {
    window.location.href = './empreferences.php';
}

function continueToNext() {
    // Validate fields
    let isValid = true;
    
    // Company URL is required
    if (!companyUrlInput.value.trim()) {
        companyUrlError.textContent = "Please enter your company website URL";
        companyUrlError.style.visibility = 'visible';
        isValid = false;
    } else {
        isValid = validateUrl(companyUrlInput, companyUrlError) && isValid;
    }
    
    // Social media URLs are optional, but validate if provided
    if (facebookUrlInput.value.trim()) {
        isValid = validateUsername(facebookUrlInput, facebookUrlError) && isValid;
    }
    
    if (linkedinUrlInput.value.trim()) {
        isValid = validateUsername(linkedinUrlInput, linkedinUrlError) && isValid;
    }
    
    if (isValid) {
        // Save data and complete setup
        saveSocialLinksAndComplete();
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.error-message[style="visibility: visible"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

function saveSocialLinksAndComplete() {
    const companyUrl = companyUrlInput.value.trim();
    const facebookUrl = facebookUrlInput.value.trim();
    const linkedinUrl = linkedinUrlInput.value.trim();
    
    // Show loading state
    showLoadingState(true);
    
    const formData = new FormData();
    formData.append('company_url', companyUrl);
    formData.append('facebook_url', facebookUrl);
    formData.append('linkedin_url', linkedinUrl);
    
    fetch('../../backend/employer/save_social_links.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update progress indicator
            updateProgressIndicator(data.progress);
            
            // Complete the setup process
            completeSetup();
        } else {
            showLoadingState(false);
            showErrorMessage(data.message || 'Failed to save social links');
        }
    })
    .catch(error => {
        showLoadingState(false);
        console.error('Save error:', error);
        showErrorMessage('Failed to save data. Please try again.');
    });
}

function completeSetup() {
    fetch('../../backend/employer/complete_setup.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        showLoadingState(false);
        
        if (data.success) {
            // Show completion modal
            showCompletionModal();
        } else {
            showErrorMessage(data.message || 'Failed to complete setup');
        }
    })
    .catch(error => {
        showLoadingState(false);
        console.error('Setup completion error:', error);
        showErrorMessage('Setup saved but completion failed. Please refresh the page.');
    });
}

function showCompletionModal() {
    // Create completion modal
    const modal = document.createElement('div');
    modal.className = 'completion-modal';
    modal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="completion-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Setup Complete!</h2>
            <p>Congratulations! Your employer account has been successfully set up. You can now start posting jobs and connecting with talented candidates.</p>
            <div class="completion-actions">
                <button class="primary-btn" onclick="goToDashboard()">Go to Dashboard</button>
            </div>
        </div>
    `;
    
    // Add CSS for completion modal
    const style = document.createElement('style');
    style.textContent = `
        .completion-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .completion-icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .modal-content h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 28px;
        }
        .modal-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .primary-btn {
            background: linear-gradient(135deg, #ff7b54, #ff9472);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .primary-btn:hover {
            transform: translateY(-2px);
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(modal);
    
    // Update progress to 100%
    updateProgressIndicator(100);
}

function goToDashboard() {
    window.location.href = './empdashboard.php';
}

function showLoadingState(isLoading) {
    const continueBtn = document.querySelector('.continue-btn');
    if (isLoading) {
        continueBtn.textContent = 'Completing Setup...';
        continueBtn.disabled = true;
    } else {
        continueBtn.textContent = 'Complete Setup';
        continueBtn.disabled = false;
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