document.addEventListener('DOMContentLoaded', function() {
    // Load progress when page loads
    loadSetupProgress();
});

function loadSetupProgress() {
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
        showErrorMessage('Unable to load setup progress. Please refresh the page.');
        return;
    }
    
    fetch(paths[index])
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
            if (data.success) {
                const progress = data.progress;
                const companyData = data.company_data;
                
                // Update page with company information if available
                if (companyData.company_name) {
                    updateWelcomeMessage(companyData.company_name);
                }
                
                // Show progress indicator
                showProgressIndicator(progress.completion_percentage);
                
                // If setup is complete, redirect to dashboard
                if (progress.setup_complete) {
                    showSuccessMessage('Setup already complete! Redirecting to dashboard...');
                    setTimeout(() => {
                        window.location.href = './empdashboard.php';
                    }, 2000);
                }
            } else {
                console.log('Setup progress response:', data);
                // Not an error, just means no progress yet
            }
        })
        .catch(error => {
            console.log(`Path ${paths[index]} failed:`, error);
            // Try next path
            tryFetchWithPaths(paths, index + 1);
        });
}

function updateWelcomeMessage(companyName) {
    const welcomeHeader = document.querySelector('.header h1');
    if (welcomeHeader) {
        welcomeHeader.textContent = `Welcome, ${companyName}!`;
    }
}

function showProgressIndicator(percentage) {
    // Create progress bar if it doesn't exist
    let progressContainer = document.querySelector('.progress-container');
    if (!progressContainer) {
        progressContainer = document.createElement('div');
        progressContainer.className = 'progress-container';
        progressContainer.innerHTML = `
            <div class="progress-label">Setup Progress: <span id="progress-percentage">${percentage}%</span></div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: ${percentage}%"></div>
            </div>
        `;
        
        // Insert after header
        const header = document.querySelector('.header');
        if (header) {
            header.insertAdjacentElement('afterend', progressContainer);
        }
        
        // Add CSS styles
        if (!document.querySelector('#progress-styles')) {
            const style = document.createElement('style');
            style.id = 'progress-styles';
            style.textContent = `
                .progress-container {
                    margin: 20px auto;
                    max-width: 600px;
                    padding: 0 20px;
                }
                .progress-label {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 8px;
                    font-weight: 500;
                }
                .progress-bar {
                    width: 100%;
                    height: 8px;
                    background-color: #e5e5e5;
                    border-radius: 4px;
                    overflow: hidden;
                }
                .progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #ff7b54, #ff9472);
                    border-radius: 4px;
                    transition: width 0.3s ease;
                }
            `;
            document.head.appendChild(style);
        }
    } else {
        // Update existing progress bar
        const progressPercentage = document.getElementById('progress-percentage');
        const progressFill = document.querySelector('.progress-fill');
        if (progressPercentage) progressPercentage.textContent = `${percentage}%`;
        if (progressFill) progressFill.style.width = `${percentage}%`;
    }
}

function showSuccessMessage(message) {
    showMessage(message, 'success');
}

function showErrorMessage(message) {
    showMessage(message, 'error');
}

function showMessage(message, type) {
    // Create and show message
    const messageDiv = document.createElement('div');
    messageDiv.className = `temp-message ${type}-message`;
    messageDiv.innerHTML = `
        <div class="message-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add CSS for message
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
    
    // Remove after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

function goToCompanyProfile() {
    // Update progress and redirect
    window.location.href = './empuploadlogo.php';
}