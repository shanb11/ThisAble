// Fix Voice Button Clickable Issue
// Replace the voice search function with this working version

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(fixVoiceButtonClickable, 4000); // After all other scripts load
});

function fixVoiceButtonClickable() {
    console.log('Fixing voice button clickable issue...');
    
    // Remove any existing voice buttons first
    const existingButtons = document.querySelectorAll('.voice-search-btn-new, .voice-btn, .voice-search-btn');
    existingButtons.forEach(btn => btn.remove());
    
    // Find the search input
    const searchInput = document.querySelector('input[placeholder*="Search"]') || 
                       document.querySelector('#job-search') ||
                       document.querySelector('.search-input input');
    
    if (!searchInput) {
        console.log('Search input not found');
        return;
    }
    
    console.log('Found search input:', searchInput);
    
    // Create new voice button with better positioning
    const voiceBtn = document.createElement('button');
    voiceBtn.type = 'button';
    voiceBtn.className = 'voice-btn-fixed';
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    voiceBtn.title = 'Voice Search';
    
    // Simple, working CSS
    voiceBtn.style.cssText = `
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 50%;
        background: #257180;
        color: white;
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        pointer-events: auto;
    `;
    
    // Ensure parent container allows positioning
    const container = searchInput.parentElement;
    container.style.position = 'relative';
    searchInput.style.paddingRight = '45px';
    
    // Add click event BEFORE adding to DOM
    voiceBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Voice button clicked!');
        startSimpleVoiceSearch(searchInput, voiceBtn);
    });
    
    // Add mouse events
    voiceBtn.addEventListener('mouseenter', function() {
        this.style.background = '#FD8B51';
        this.style.transform = 'translateY(-50%) scale(1.1)';
    });
    
    voiceBtn.addEventListener('mouseleave', function() {
        if (!this.classList.contains('listening')) {
            this.style.background = '#257180';
            this.style.transform = 'translateY(-50%)';
        }
    });
    
    // Add to DOM
    container.appendChild(voiceBtn);
    
    console.log('Voice button added and should be clickable');
    
    // Test click after short delay
    setTimeout(() => {
        console.log('Testing voice button position and visibility...');
        const rect = voiceBtn.getBoundingClientRect();
        console.log('Voice button position:', rect);
        console.log('Voice button visible:', rect.width > 0 && rect.height > 0);
    }, 500);
}

function startSimpleVoiceSearch(searchInput, voiceBtn) {
    console.log('Starting voice search...');
    
    // Check if speech recognition is supported
    if (!window.SpeechRecognition && !window.webkitSpeechRecognition) {
        alert('Voice search is not supported in your browser. Please use Chrome or Edge.');
        return;
    }
    
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    // Configure recognition
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    // Change button to listening state
    voiceBtn.classList.add('listening');
    voiceBtn.style.background = '#dc3545';
    voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
    voiceBtn.title = 'Stop listening';
    
    // Change search placeholder
    const originalPlaceholder = searchInput.placeholder;
    searchInput.placeholder = 'Listening... speak now';
    searchInput.style.borderColor = '#dc3545';
    
    // Add listening animation
    voiceBtn.style.animation = 'pulse 1s infinite';
    const animationStyle = document.createElement('style');
    animationStyle.textContent = `
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    `;
    document.head.appendChild(animationStyle);
    
    recognition.onstart = function() {
        console.log('Voice recognition started');
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        console.log('Voice result:', transcript);
        
        // Set the search value
        searchInput.value = transcript;
        
        // Trigger search by dispatching input event
        const inputEvent = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(inputEvent);
        
        // Show success feedback
        showSimpleNotification(`Searching for: "${transcript}"`, 'success');
    };
    
    recognition.onerror = function(event) {
        console.error('Voice recognition error:', event.error);
        
        let message = 'Voice search failed. ';
        switch(event.error) {
            case 'not-allowed':
                message = 'Please allow microphone access and try again.';
                break;
            case 'no-speech':
                message = 'No speech detected. Please try again.';
                break;
            default:
                message = 'Voice search error. Please try again.';
        }
        
        showSimpleNotification(message, 'error');
    };
    
    recognition.onend = function() {
        console.log('Voice recognition ended');
        
        // Reset button state
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#257180';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        voiceBtn.style.animation = 'none';
        voiceBtn.title = 'Voice Search';
        
        // Reset search input
        searchInput.placeholder = originalPlaceholder;
        searchInput.style.borderColor = '';
    };
    
    // Start recognition
    try {
        recognition.start();
    } catch (error) {
        console.error('Failed to start voice recognition:', error);
        showSimpleNotification('Could not start voice search', 'error');
        
        // Reset button if start fails
        voiceBtn.classList.remove('listening');
        voiceBtn.style.background = '#257180';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        searchInput.placeholder = originalPlaceholder;
    }
}

function showSimpleNotification(message, type) {
    console.log('Notification:', message);
    
    // Try to use existing notification system
    if (typeof showNotification === 'function') {
        showNotification(message, type);
        return;
    }
    
    // Create simple notification
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 12px 16px;
        border-radius: 5px;
        z-index: 10000;
        font-size: 14px;
        max-width: 300px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Force execution
console.log('Voice button fix script loaded');