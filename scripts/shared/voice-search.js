// Voice Search System for ThisAble
// scripts/shared/voice-search.js

class VoiceSearchManager {
    constructor() {
        this.isSupported = 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window;
        this.isListening = false;
        this.recognition = null;
        this.onResultCallback = null;
        this.onErrorCallback = null;
        this.onStatusChangeCallback = null;
        
        // Voice search settings
        this.language = 'en-US'; // Can be changed to 'fil-PH' for Filipino
        this.continuous = false;
        this.interimResults = true;
        this.maxAlternatives = 1;
        
        this.init();
    }

    init() {
        if (!this.isSupported) {
            console.warn('Voice search not supported in this browser');
            return;
        }

        // Initialize speech recognition
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        
        // Configure recognition
        this.recognition.continuous = this.continuous;
        this.recognition.interimResults = this.interimResults;
        this.recognition.maxAlternatives = this.maxAlternatives;
        this.recognition.lang = this.language;

        // Set up event listeners
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (!this.recognition) return;

        // When speech recognition starts
        this.recognition.onstart = () => {
            this.isListening = true;
            console.log('Voice search started');
            this.updateStatus('listening');
            this.showListeningFeedback();
        };

        // When speech recognition ends
        this.recognition.onend = () => {
            this.isListening = false;
            console.log('Voice search ended');
            this.updateStatus('stopped');
            this.hideListeningFeedback();
        };

        // When speech recognition gets results
        this.recognition.onresult = (event) => {
            let finalTranscript = '';
            let interimTranscript = '';

            // Process all results
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                
                if (event.results[i].isFinal) {
                    finalTranscript += transcript;
                } else {
                    interimTranscript += transcript;
                }
            }

            // Show interim results for better UX
            if (interimTranscript) {
                this.showInterimResult(interimTranscript);
            }

            // Process final result
            if (finalTranscript) {
                this.processFinalResult(finalTranscript.trim());
            }
        };

        // Handle errors
        this.recognition.onerror = (event) => {
            console.error('Voice search error:', event.error);
            this.isListening = false;
            this.updateStatus('error');
            this.hideListeningFeedback();
            this.handleError(event.error);
        };

        // Handle no speech detected
        this.recognition.onnomatch = () => {
            console.log('No speech detected');
            this.updateStatus('no-match');
            this.showError('No speech detected. Please try again.');
        };
    }

    // Start voice search
    startListening(callback = null) {
        if (!this.isSupported) {
            this.showError('Voice search is not supported in your browser');
            return false;
        }

        if (this.isListening) {
            console.log('Already listening');
            return false;
        }

        // Set callback for results
        if (callback && typeof callback === 'function') {
            this.onResultCallback = callback;
        }

        try {
            this.recognition.start();
            return true;
        } catch (error) {
            console.error('Error starting voice search:', error);
            this.showError('Failed to start voice search. Please try again.');
            return false;
        }
    }

    // Stop voice search
    stopListening() {
        if (this.recognition && this.isListening) {
            this.recognition.stop();
        }
    }

    // Process final speech result
    processFinalResult(transcript) {
        console.log('Voice search result:', transcript);
        
        // Clean up the transcript
        const cleanedTranscript = this.cleanTranscript(transcript);
        
        // Execute callback if provided
        if (this.onResultCallback && typeof this.onResultCallback === 'function') {
            this.onResultCallback(cleanedTranscript);
        }

        // Show result feedback
        this.showResultFeedback(cleanedTranscript);

        // Announce result with TTS if available
        if (window.ttsManager && window.ttsManager.isSupported) {
            setTimeout(() => {
                window.ttsManager.speak(`Searching for: ${cleanedTranscript}`);
            }, 500);
        }
    }

    // Clean and process transcript
    cleanTranscript(transcript) {
        return transcript
            .toLowerCase()
            .trim()
            // Handle common speech-to-text errors
            .replace(/\bfor\b/g, 'for') // Ensure 'for' is preserved
            .replace(/\bjobs?\b/g, '') // Remove 'job' or 'jobs' as they're redundant
            .replace(/\bsearch\b/g, '') // Remove 'search' command
            .replace(/\bfind\b/g, '') // Remove 'find' command
            .replace(/\blook for\b/g, '') // Remove 'look for' command
            .replace(/\s+/g, ' ') // Replace multiple spaces with single space
            .trim();
    }

    // Show interim results while user is speaking
    showInterimResult(interimText) {
        const interimDisplay = document.getElementById('voice-interim-result');
        if (interimDisplay) {
            interimDisplay.textContent = interimText;
            interimDisplay.style.display = 'block';
        }
    }

    // Show listening feedback
    showListeningFeedback() {
        // Update voice button state
        const voiceButtons = document.querySelectorAll('.voice-search-btn');
        voiceButtons.forEach(btn => {
            btn.classList.add('listening');
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-stop';
            }
            btn.setAttribute('title', 'Stop voice search');
            btn.setAttribute('aria-label', 'Stop voice search');
        });

        // Show listening indicator
        this.showListeningIndicator();

        // Add pulsing animation to search input
        const searchInput = document.getElementById('job-search');
        if (searchInput) {
            searchInput.classList.add('voice-listening');
            searchInput.placeholder = 'Listening... Speak your search terms';
        }
    }

    // Hide listening feedback
    hideListeningFeedback() {
        // Reset voice button state
        const voiceButtons = document.querySelectorAll('.voice-search-btn');
        voiceButtons.forEach(btn => {
            btn.classList.remove('listening', 'error');
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-microphone';
            }
            btn.setAttribute('title', 'Voice search');
            btn.setAttribute('aria-label', 'Start voice search');
        });

        // Hide listening indicator
        this.hideListeningIndicator();

        // Remove pulsing animation from search input
        const searchInput = document.getElementById('job-search');
        if (searchInput) {
            searchInput.classList.remove('voice-listening');
            searchInput.placeholder = 'Search for jobs...';
        }

        // Hide interim results
        const interimDisplay = document.getElementById('voice-interim-result');
        if (interimDisplay) {
            interimDisplay.style.display = 'none';
        }
    }

    // Show listening indicator
    showListeningIndicator() {
        let indicator = document.getElementById('voice-listening-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'voice-listening-indicator';
            indicator.className = 'voice-listening-indicator';
            indicator.innerHTML = `
                <div class="voice-indicator-content">
                    <div class="voice-animation">
                        <div class="voice-wave"></div>
                        <div class="voice-wave"></div>
                        <div class="voice-wave"></div>
                        <div class="voice-wave"></div>
                    </div>
                    <span class="voice-status-text">Listening for your search...</span>
                    <div class="voice-interim" id="voice-interim-result"></div>
                    <button class="voice-stop-btn" onclick="window.voiceSearchManager.stopListening()">
                        <i class="fas fa-stop"></i>
                        Stop
                    </button>
                </div>
            `;
            document.body.appendChild(indicator);
        }
        indicator.classList.add('show');
    }

    // Hide listening indicator
    hideListeningIndicator() {
        const indicator = document.getElementById('voice-listening-indicator');
        if (indicator) {
            indicator.classList.remove('show');
        }
    }

    // Show result feedback
    showResultFeedback(transcript) {
        // Create temporary feedback
        const feedback = document.createElement('div');
        feedback.className = 'voice-result-feedback';
        feedback.innerHTML = `
            <div class="voice-result-content">
                <i class="fas fa-check-circle"></i>
                <span>Heard: "${transcript}"</span>
            </div>
        `;
        document.body.appendChild(feedback);

        // Show and auto-hide
        setTimeout(() => feedback.classList.add('show'), 100);
        setTimeout(() => {
            feedback.classList.remove('show');
            setTimeout(() => feedback.remove(), 300);
        }, 2000);
    }

    // Handle errors
    handleError(error) {
        let errorMessage = 'Voice search error occurred';
        
        switch (error) {
            case 'not-allowed':
                errorMessage = 'Microphone access denied. Please allow microphone access and try again.';
                break;
            case 'no-speech':
                errorMessage = 'No speech detected. Please speak clearly and try again.';
                break;
            case 'audio-capture':
                errorMessage = 'Microphone not found. Please check your microphone and try again.';
                break;
            case 'network':
                errorMessage = 'Network error. Please check your connection and try again.';
                break;
            case 'aborted':
                errorMessage = 'Voice search was cancelled.';
                break;
            case 'language-not-supported':
                errorMessage = 'Language not supported for voice search.';
                break;
            default:
                errorMessage = 'Voice search failed. Please try again.';
        }

        this.showError(errorMessage);

        // Mark voice buttons as error state
        const voiceButtons = document.querySelectorAll('.voice-search-btn');
        voiceButtons.forEach(btn => {
            btn.classList.add('error');
            setTimeout(() => btn.classList.remove('error'), 3000);
        });

        // Call error callback if provided
        if (this.onErrorCallback && typeof this.onErrorCallback === 'function') {
            this.onErrorCallback(error, errorMessage);
        }
    }

    // Update status
    updateStatus(status) {
        if (this.onStatusChangeCallback && typeof this.onStatusChangeCallback === 'function') {
            this.onStatusChangeCallback(status);
        }

        // Update status display if exists
        const statusDisplay = document.getElementById('voice-search-status');
        if (statusDisplay) {
            statusDisplay.textContent = this.getStatusText(status);
            statusDisplay.className = `voice-search-status ${status}`;
        }
    }

    // Get status text
    getStatusText(status) {
        const statusTexts = {
            'listening': 'Listening...',
            'processing': 'Processing...',
            'stopped': 'Ready',
            'error': 'Error',
            'no-match': 'No speech detected'
        };
        return statusTexts[status] || 'Ready';
    }

    // Show error using existing notification system
    showError(message) {
        if (typeof showError === 'function') {
            showError(message);
        } else if (typeof showNotification === 'function') {
            showNotification(message, 'error');
        } else {
            console.error('Voice Search Error:', message);
            alert(message); // Fallback
        }
    }

    // Set language for voice recognition
    setLanguage(language) {
        this.language = language;
        if (this.recognition) {
            this.recognition.lang = language;
        }
    }

    // Set callbacks
    setCallbacks(onResult, onError, onStatusChange) {
        if (onResult) this.onResultCallback = onResult;
        if (onError) this.onErrorCallback = onError;
        if (onStatusChange) this.onStatusChangeCallback = onStatusChange;
    }

    // Get current status
    getStatus() {
        return {
            isSupported: this.isSupported,
            isListening: this.isListening,
            language: this.language
        };
    }

    // Check microphone permissions
    async checkMicrophonePermission() {
        try {
            const permission = await navigator.permissions.query({ name: 'microphone' });
            return permission.state; // 'granted', 'denied', or 'prompt'
        } catch (error) {
            console.warn('Could not check microphone permission:', error);
            return 'unknown';
        }
    }

    // Request microphone permission
    async requestMicrophonePermission() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            stream.getTracks().forEach(track => track.stop()); // Stop the stream immediately
            return true;
        } catch (error) {
            console.error('Microphone permission denied:', error);
            return false;
        }
    }
}

// Initialize Voice Search Manager
window.voiceSearchManager = new VoiceSearchManager();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VoiceSearchManager;
}