document.addEventListener('DOMContentLoaded', function() {
    // Load existing data when page loads
    loadExistingData();
    
    // Initialize form handlers
    initializeFormHandlers();
});

// Get DOM elements
const disabilityTypeOptions = document.querySelectorAll('.type-option');
const additionalAccommodationsTextarea = document.getElementById('additional-accommodations');
const accommodationsCount = document.getElementById('accommodations-count');
const disabilitiesCheckboxes = document.querySelectorAll('input[name="disabilities"]');
const accessibilityCheckboxes = document.querySelectorAll('input[name="accessibility"]');

// Error messages
const disabilitiesError = document.getElementById('disabilities-error');
const accessibilityError = document.getElementById('accessibility-error');

function initializeFormHandlers() {
    // Handle disability type selection
    disabilityTypeOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        
        option.addEventListener('click', function() {
            radio.checked = true;
            disabilityTypeOptions.forEach(opt => {
                opt.classList.remove('selected');
            });
            option.classList.add('selected');
        });
        
        // Check if already selected
        if (radio.checked) {
            option.classList.add('selected');
        }
    });
    
    // Character counter for textarea
    additionalAccommodationsTextarea.addEventListener('input', function() {
        accommodationsCount.textContent = this.value.length;
        autoResize(this);
    });
    
    // Checkbox validation
    disabilitiesCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const anyChecked = Array.from(disabilitiesCheckboxes).some(cb => cb.checked);
            if (anyChecked) {
                disabilitiesError.style.visibility = 'hidden';
            }
        });
    });
    
    accessibilityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const anyChecked = Array.from(accessibilityCheckboxes).some(cb => cb.checked);
            if (anyChecked) {
                accessibilityError.style.visibility = 'hidden';
            }
        });
    });
    
    // Initial resize
    autoResize(additionalAccommodationsTextarea);
}

function loadExistingData() {
    fetch('../../backend/employer/get_setup_progress.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const preferencesData = data.preferences_data;
                
                if (preferencesData) {
                    // Load disability types checkboxes
                    if (preferencesData.disability_types) {
                        try {
                            const disabilityTypes = JSON.parse(preferencesData.disability_types);
                            disabilityTypes.forEach(type => {
                                const checkbox = document.getElementById(type);
                                if (checkbox) checkbox.checked = true;
                            });
                        } catch (e) {
                            console.error('Error parsing disability types:', e);
                        }
                    }
                    
                    // Load accessibility options checkboxes
                    if (preferencesData.workplace_accommodations) {
                        try {
                            const accommodations = JSON.parse(preferencesData.workplace_accommodations);
                            accommodations.forEach(accommodation => {
                                const checkbox = document.getElementById(accommodation);
                                if (checkbox) checkbox.checked = true;
                            });
                        } catch (e) {
                            console.error('Error parsing workplace accommodations:', e);
                        }
                    }
                    
                    // Load additional accommodations
                    if (preferencesData.additional_accommodations) {
                        additionalAccommodationsTextarea.value = preferencesData.additional_accommodations;
                        accommodationsCount.textContent = preferencesData.additional_accommodations.length;
                        autoResize(additionalAccommodationsTextarea);
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

// Navigation functions
function goBack() {
    window.location.href = './empdescription.php';
}

function continueToNext() {
    // Validate fields
    let isValid = true;
    
    // Check if at least one disability type is selected
    const disabilitiesChecked = Array.from(disabilitiesCheckboxes).some(cb => cb.checked);
    if (!disabilitiesChecked) {
        disabilitiesError.style.visibility = 'visible';
        isValid = false;
    }
    
    // Check if at least one accessibility option is selected
    const accessibilityChecked = Array.from(accessibilityCheckboxes).some(cb => cb.checked);
    if (!accessibilityChecked) {
        accessibilityError.style.visibility = 'visible';
        isValid = false;
    }
    
    if (isValid) {
        // Save data via AJAX
        saveHiringPreferences();
    } else {
        // Scroll to first error
        const firstError = document.querySelector('.error-message[style="visibility: visible"]');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

function saveHiringPreferences() {
    // Get selected disability type
    const selectedType = document.querySelector('input[name="disability-type"]:checked').value;
    
    // Get selected disabilities
    const selectedDisabilities = Array.from(disabilitiesCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.id);
    
    // Get selected accessibility options
    const selectedAccessibility = Array.from(accessibilityCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.id);
    
    // Get additional accommodations
    const additionalAccommodations = additionalAccommodationsTextarea.value.trim();
    
    // Show loading state
    showLoadingState(true);
    
    const formData = new FormData();
    formData.append('disability_type', selectedType);
    formData.append('disabilities', JSON.stringify(selectedDisabilities));
    formData.append('accessibility', JSON.stringify(selectedAccessibility));
    formData.append('additional_accommodations', additionalAccommodations);
    
    fetch('../../backend/employer/save_hiring_preferences.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showLoadingState(false);
        
        if (data.success) {
            showSuccessMessage('Hiring preferences saved successfully!');
            
            // Update progress indicator
            updateProgressIndicator(data.progress);
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = './empsocmedlinks.php';
            }, 1500);
        } else {
            showErrorMessage(data.message || 'Failed to save hiring preferences');
        }
    })
    .catch(error => {
        showLoadingState(false);
        console.error('Save error:', error);
        showErrorMessage('Failed to save data. Please try again.');
    });
}

function showLoadingState(isLoading) {
    const continueBtn = document.querySelector('.continue-btn');
    if (isLoading) {
        continueBtn.textContent = 'Saving...';
        continueBtn.disabled = true;
    } else {
        continueBtn.textContent = 'Continue';
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

// Auto-resize textarea
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
}