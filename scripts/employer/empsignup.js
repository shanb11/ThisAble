// Updated empsignup.js - Fixed routing for proper setup flow
document.addEventListener('DOMContentLoaded', function() {
    const googleSignupBtn = document.getElementById('googleSignupBtn');
    const companySetupForm = document.getElementById('companySetupForm');
    const completeSetupBtn = document.getElementById('complete-setup-btn');
    const successModal = document.getElementById('success-modal-overlay');
    const continueBtn = document.getElementById('continue-to-dashboard');
    
    // Check if we're on Google setup page
    const urlParams = new URLSearchParams(window.location.search);
    const isGoogleSetup = urlParams.get('google_setup') === '1';
    
    // Google OAuth signup
    if (googleSignupBtn) {
        googleSignupBtn.addEventListener('click', function() {
            // Redirect to Google OAuth
            window.location.href = '../../backend/employer/google_auth.php';
        });
    }
    
    // Load industries for company setup
    if (isGoogleSetup) {
        loadIndustries();
    }
    
    // Company setup form submission
    if (companySetupForm) {
        companySetupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleCompanySetup();
        });
    }
    
    // Continue to login button (changed from dashboard)
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            window.location.href = 'emplogin.php?registered=1';
        });
    }
    
    // Load industries from database
    async function loadIndustries() {
        try {
            const response = await fetch('../../backend/employer/get_industries.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.data) {
                const industrySelect = document.getElementById('industry');
                
                // Clear existing options except the first one
                industrySelect.innerHTML = '<option value="" disabled selected>Select Industry</option>';
                
                // Add industries from database
                result.data.forEach(industry => {
                    const option = document.createElement('option');
                    option.value = industry.industry_id;
                    option.textContent = industry.industry_name;
                    option.setAttribute('data-icon', industry.industry_icon);
                    industrySelect.appendChild(option);
                });
            } else {
                console.error('Failed to load industries:', result.message);
                // Fallback to hardcoded industries
                loadFallbackIndustries();
            }
        } catch (error) {
            console.error('Error loading industries:', error);
            loadFallbackIndustries();
        }
    }
    
    // Fallback industries if API fails
    function loadFallbackIndustries() {
        const industrySelect = document.getElementById('industry');
        const fallbackIndustries = [
            { id: 1, name: 'Technology & IT' },
            { id: 2, name: 'Business Process Outsourcing (BPO)' },
            { id: 3, name: 'Manufacturing' },
            { id: 4, name: 'Healthcare & Medical' },
            { id: 5, name: 'Education & Training' },
            { id: 6, name: 'Financial Services' },
            { id: 7, name: 'Retail & Sales' },
            { id: 8, name: 'Food & Beverage' },
            { id: 9, name: 'Transportation & Logistics' },
            { id: 10, name: 'Government & Public Service' },
            { id: 11, name: 'Non-Profit Organizations' },
            { id: 12, name: 'Creative & Media' }
        ];
        
        fallbackIndustries.forEach(industry => {
            const option = document.createElement('option');
            option.value = industry.id;
            option.textContent = industry.name;
            industrySelect.appendChild(option);
        });
    }
    
    // Handle company setup form submission
    async function handleCompanySetup() {
        try {
            // Validate form
            if (!validateCompanySetupForm()) {
                return;
            }
            
            // Show loading state
            setLoadingState(true);
            
            // Collect form data
            const formData = new FormData(companySetupForm);
            
            // Send company setup request
            const response = await fetch('../../backend/employer/complete_google_setup.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Show success modal (updated message)
                showSuccessModal();
            } else {
                throw new Error(result.message || 'Company setup failed');
            }
            
        } catch (error) {
            console.error('Company setup error:', error);
            showNotification(error.message, 'error');
        } finally {
            setLoadingState(false);
        }
    }
    
    // Validate company setup form
    function validateCompanySetupForm() {
        const requiredFields = [
            { id: 'company-name', name: 'Company Name' },
            { id: 'industry', name: 'Industry' },
            { id: 'company-address', name: 'Company Address' },
            { id: 'position', name: 'Your Position' },
            { id: 'contact-number', name: 'Contact Number' }
        ];
        
        let isValid = true;
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            const value = element.value.trim();
            
            // Remove previous error state
            element.classList.remove('error');
            
            if (!value) {
                element.classList.add('error');
                isValid = false;
            }
        });
        
        // Validate contact number format (Philippine)
        const contactNumber = document.getElementById('contact-number').value.trim();
        const phoneRegex = /^(\+63|0)9\d{9}$/;
        
        if (contactNumber && !phoneRegex.test(contactNumber)) {
            document.getElementById('contact-number').classList.add('error');
            showNotification('Please enter a valid Philippine phone number (e.g., 09123456789)', 'error');
            isValid = false;
        }
        
        // Validate website URL if provided
        const website = document.getElementById('company-website').value.trim();
        if (website) {
            try {
                new URL(website);
            } catch {
                document.getElementById('company-website').classList.add('error');
                showNotification('Please enter a valid website URL', 'error');
                isValid = false;
            }
        }
        
        if (!isValid) {
            showNotification('Please fill in all required fields correctly', 'error');
        }
        
        return isValid;
    }
    
    // Set loading state for form
    function setLoadingState(loading) {
        if (!completeSetupBtn) return;
        
        if (loading) {
            completeSetupBtn.disabled = true;
            completeSetupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        } else {
            completeSetupBtn.disabled = false;
            completeSetupBtn.innerHTML = '<i class="fas fa-check"></i> Complete Company Setup';
        }
    }
    
    // Show success modal (updated message)
    function showSuccessModal() {
        if (successModal) {
            // Update modal content for registration success
            const modalTitle = successModal.querySelector('h3');
            const modalText = successModal.querySelector('p');
            const continueButton = successModal.querySelector('.continue-btn');
            
            if (modalTitle) modalTitle.textContent = 'Registration Successful!';
            if (modalText) modalText.textContent = 'Your employer account has been created. Please login to complete your profile setup.';
            if (continueButton) continueButton.textContent = 'Continue to Login';
            
            successModal.style.display = 'flex';
        }
    }
    
    // Show notification
    function showNotification(message, type = 'info') {
        // Remove any existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                   type === 'error' ? 'exclamation-circle' : 
                                   'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add notification styles if they don't exist
        if (!document.querySelector('#notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    padding: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    max-width: 400px;
                    z-index: 10000;
                    animation: slideIn 0.3s ease;
                }
                .notification.success { border-left: 4px solid #10b981; }
                .notification.error { border-left: 4px solid #ef4444; }
                .notification.info { border-left: 4px solid #3b82f6; }
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                .notification-content i {
                    font-size: 18px;
                }
                .notification.success i { color: #10b981; }
                .notification.error i { color: #ef4444; }
                .notification.info i { color: #3b82f6; }
                .notification-close {
                    background: none;
                    border: none;
                    color: #6b7280;
                    cursor: pointer;
                    padding: 4px;
                }
                .error {
                    border-color: #ef4444 !important;
                    box-shadow: 0 0 0 1px #ef4444 !important;
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styles);
        }
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    // Handle input validation on blur
    document.addEventListener('blur', function(e) {
        if (e.target.classList.contains('error')) {
            if (e.target.value.trim()) {
                e.target.classList.remove('error');
            }
        }
    }, true);
});

// Add CSS for success modal
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('#success-modal-styles')) {
        const styles = document.createElement('style');
        styles.id = 'success-modal-styles';
        styles.textContent = `
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }
            .success-modal {
                background: white;
                padding: 40px;
                border-radius: 12px;
                text-align: center;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            }
            .success-icon {
                font-size: 64px;
                color: #10b981;
                margin-bottom: 20px;
            }
            .success-modal h3 {
                margin: 0 0 16px 0;
                color: #1f2937;
                font-size: 24px;
            }
            .success-modal p {
                margin: 0 0 32px 0;
                color: #6b7280;
                line-height: 1.5;
            }
            .continue-btn {
                background: #CB6040;
                color: white;
                border: none;
                padding: 12px 32px;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 500;
                cursor: pointer;
                transition: background 0.2s;
            }
            .continue-btn:hover {
                background: #b8543a;
            }
        `;
        document.head.appendChild(styles);
    }
});