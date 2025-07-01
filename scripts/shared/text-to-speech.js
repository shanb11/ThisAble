// Text-to-Speech Core System for ThisAble
// scripts/shared/text-to-speech.js

class TextToSpeechManager {
    constructor() {
        this.isSupported = 'speechSynthesis' in window;
        this.isSpeaking = false;
        this.isPaused = false;
        this.currentUtterance = null;
        this.speechRate = 1; // Normal speed
        this.speechVolume = 1;
        this.currentlyReading = null; // Track what element is being read
        
        this.init();
    }

    init() {
        if (!this.isSupported) {
            console.warn('Text-to-Speech not supported in this browser');
            return;
        }

        // Wait for voices to load
        this.loadVoices();
        
        // Listen for voice changes
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = () => this.loadVoices();
        }
    }

    loadVoices() {
        this.voices = speechSynthesis.getVoices();
        // Prefer Filipino/English voices
        this.defaultVoice = this.voices.find(voice => 
            voice.lang.startsWith('en-') || voice.lang.startsWith('fil-')
        ) || this.voices[0];
    }

    // Main function to read text
    speak(text, options = {}) {
        if (!this.isSupported) {
            this.showError('Text-to-speech is not supported in your browser');
            return;
        }

        if (!text || text.trim() === '') {
            this.showError('No text to read');
            return;
        }

        // Stop any current speech
        this.stop();

        // Clean text for better speech
        const cleanText = this.cleanTextForSpeech(text);
        
        // Create utterance
        this.currentUtterance = new SpeechSynthesisUtterance(cleanText);
        this.currentUtterance.voice = this.defaultVoice;
        this.currentUtterance.rate = this.speechRate;
        this.currentUtterance.volume = this.speechVolume;

        // Set up event listeners
        this.currentUtterance.onstart = () => {
            this.isSpeaking = true;
            this.isPaused = false;
            this.onSpeechStart(options.element);
        };

        this.currentUtterance.onend = () => {
            this.isSpeaking = false;
            this.isPaused = false;
            this.onSpeechEnd();
        };

        this.currentUtterance.onerror = (event) => {
            console.error('Speech synthesis error:', event);
            this.isSpeaking = false;
            this.isPaused = false;
            this.onSpeechEnd();
            this.showError('Error reading text. Please try again.');
        };

        this.currentUtterance.onpause = () => {
            this.isPaused = true;
        };

        this.currentUtterance.onresume = () => {
            this.isPaused = false;
        };

        // Start speaking
        speechSynthesis.speak(this.currentUtterance);
    }

    // Read specific job card
    readJobCard(jobElement) {
        if (!jobElement) return;

        const jobTitle = jobElement.querySelector('.job-title')?.textContent || '';
        const companyName = jobElement.querySelector('.company-name')?.textContent || '';
        const location = jobElement.querySelector('.location-pill')?.textContent || '';
        const description = jobElement.querySelector('.job-description')?.textContent || '';
        const employmentType = jobElement.querySelector('.job-tag')?.textContent || '';
        
        // Build readable text
        let readableText = `Job opening: ${jobTitle}. `;
        if (companyName) readableText += `Company: ${companyName}. `;
        if (location) readableText += `Location: ${location}. `;
        if (employmentType) readableText += `Employment type: ${employmentType}. `;
        if (description) readableText += `Job description: ${description}`;

        this.speak(readableText, { element: jobElement });
    }

    // Read job requirements and accommodations
    readJobDetails(jobElement) {
        if (!jobElement) return;

        const accommodations = Array.from(jobElement.querySelectorAll('.feature-badge'))
            .map(badge => badge.textContent.trim())
            .join(', ');

        let detailsText = '';
        if (accommodations) {
            detailsText = `This job offers the following accessibility accommodations: ${accommodations}`;
        } else {
            detailsText = 'No specific accessibility accommodations listed for this job.';
        }

        this.speak(detailsText, { element: jobElement });
    }

    // Pause current speech
    pause() {
        if (this.isSpeaking && !this.isPaused) {
            speechSynthesis.pause();
        }
    }

    // Resume paused speech
    resume() {
        if (this.isSpeaking && this.isPaused) {
            speechSynthesis.resume();
        }
    }

    // Stop current speech
    stop() {
        speechSynthesis.cancel();
        this.isSpeaking = false;
        this.isPaused = false;
        this.onSpeechEnd();
    }

    // Set speech rate (0.5 - 2.0)
    setRate(rate) {
        this.speechRate = Math.max(0.5, Math.min(2.0, rate));
        if (this.currentUtterance) {
            this.currentUtterance.rate = this.speechRate;
        }
    }

    // Set speech volume (0.0 - 1.0)
    setVolume(volume) {
        this.speechVolume = Math.max(0.0, Math.min(1.0, volume));
        if (this.currentUtterance) {
            this.currentUtterance.volume = this.speechVolume;
        }
    }

    // Clean text for better speech synthesis
    cleanTextForSpeech(text) {
        return text
            .replace(/\s+/g, ' ') // Replace multiple spaces with single space
            .replace(/[^\w\s.,!?;:-]/g, '') // Remove special characters except basic punctuation
            .replace(/([.!?])\s*([A-Z])/g, '$1 $2') // Add space after sentences
            .trim();
    }

    // Visual feedback when speech starts
    onSpeechStart(element) {
        // Remove previous reading indicators
        document.querySelectorAll('.tts-reading').forEach(el => {
            el.classList.remove('tts-reading');
        });

        // Add reading indicator to current element
        if (element) {
            element.classList.add('tts-reading');
            this.currentlyReading = element;
        }

        // Update TTS button states
        this.updateButtonStates();

        // Show reading indicator
        this.showReadingIndicator();
    }

    // Visual feedback when speech ends
    onSpeechEnd() {
        // Remove reading indicators
        document.querySelectorAll('.tts-reading').forEach(el => {
            el.classList.remove('tts-reading');
        });

        this.currentlyReading = null;

        // Update TTS button states
        this.updateButtonStates();

        // Hide reading indicator
        this.hideReadingIndicator();
    }

    // Update button states based on current speech status
    updateButtonStates() {
        const buttons = document.querySelectorAll('.tts-btn');
        buttons.forEach(btn => {
            const icon = btn.querySelector('i');
            if (this.isSpeaking && !this.isPaused) {
                btn.classList.add('tts-speaking');
                if (icon) {
                    icon.className = 'fas fa-pause';
                }
                btn.setAttribute('aria-label', 'Pause reading');
            } else if (this.isPaused) {
                btn.classList.add('tts-paused');
                if (icon) {
                    icon.className = 'fas fa-play';
                }
                btn.setAttribute('aria-label', 'Resume reading');
            } else {
                btn.classList.remove('tts-speaking', 'tts-paused');
                if (icon) {
                    icon.className = 'fas fa-volume-up';
                }
                btn.setAttribute('aria-label', 'Read aloud');
            }
        });
    }

    // Show reading indicator
    showReadingIndicator() {
        let indicator = document.getElementById('tts-reading-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'tts-reading-indicator';
            indicator.className = 'tts-reading-indicator';
            indicator.innerHTML = `
                <div class="tts-indicator-content">
                    <i class="fas fa-volume-up"></i>
                    <span>Reading...</span>
                    <button class="tts-stop-btn" onclick="window.ttsManager.stop()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(indicator);
        }
        indicator.classList.add('show');
    }

    // Hide reading indicator
    hideReadingIndicator() {
        const indicator = document.getElementById('tts-reading-indicator');
        if (indicator) {
            indicator.classList.remove('show');
        }
    }

    // Show error message using existing notification system
    showError(message) {
        if (typeof showError === 'function') {
            showError(message);
        } else if (typeof showNotification === 'function') {
            showNotification(message, 'error');
        } else {
            console.error('TTS Error:', message);
        }
    }

    // Get current status for UI updates
    getStatus() {
        return {
            isSupported: this.isSupported,
            isSpeaking: this.isSpeaking,
            isPaused: this.isPaused,
            currentRate: this.speechRate,
            currentVolume: this.speechVolume
        };
    }
}

// Initialize TTS Manager
window.ttsManager = new TextToSpeechManager();

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TextToSpeechManager;
}