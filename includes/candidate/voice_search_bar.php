<?php
// includes/candidate/voice_search_bar.php
// Voice-enabled search bar component

// Default options
$voice_options = array_merge([
    'show_voice_button' => true,
    'show_language_toggle' => false, // For future Filipino language support
    'placeholder' => 'Search for jobs...',
    'voice_placeholder' => 'Listening... Speak your search terms',
    'compact' => false
], $voice_options ?? []);
?>

<!-- Enhanced Search Bar with Voice Search -->
<div class="voice-search-container">
    <div class="search-bar voice-enabled" role="search" aria-label="Search and filter jobs">
        <div class="search-input-container">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input type="text" 
                       id="job-search" 
                       class="search-input" 
                       placeholder="<?php echo htmlspecialchars($voice_options['placeholder']); ?>" 
                       aria-label="Search for jobs"
                       autocomplete="off">
                
                <?php if ($voice_options['show_voice_button']): ?>
                <!-- Voice Search Button -->
                <button class="voice-search-btn" 
                        id="voice-search-btn" 
                        type="button"
                        title="Voice search - Click to speak your search"
                        aria-label="Start voice search">
                    <i class="fas fa-microphone"></i>
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Voice Search Status -->
            <div class="voice-search-status" id="voice-search-status" style="display: none;">
                <span class="status-text">Ready</span>
            </div>
        </div>
        
        <!-- Filter Button -->
        <button class="filter-btn" id="filter-btn" aria-haspopup="true">
            <i class="fas fa-sliders-h" aria-hidden="true"></i>
            <span class="filter-text">Filter Options</span>
        </button>
        
        <?php if ($voice_options['show_language_toggle']): ?>
        <!-- Language Toggle for Voice Search -->
        <div class="voice-language-toggle">
            <button class="lang-btn active" data-lang="en-US" title="English voice search">
                <span>EN</span>
            </button>
            <button class="lang-btn" data-lang="fil-PH" title="Filipino voice search">
                <span>FIL</span>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- TTS Quick Access -->
        <div class="search-bar-tts">
            <button class="tts-quick-btn" id="tts-quick-search" title="Read search results aloud">
                <i class="fas fa-volume-up"></i>
                <span>Listen</span>
            </button>
        </div>
    </div>
    
    <!-- Voice Search Help Text -->
    <div class="voice-help-text" id="voice-help-text">
        <div class="voice-help-content">
            <i class="fas fa-info-circle"></i>
            <span>Try saying: "software developer", "part time jobs", "remote work", or "jobs in Manila"</span>
        </div>
    </div>
</div>

<!-- Voice Search Listening Indicator (Global) -->
<div id="voice-listening-indicator" class="voice-listening-indicator">
    <div class="voice-indicator-content">
        <div class="voice-animation">
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
            <div class="voice-wave"></div>
        </div>
        <span class="voice-status-text">Listening for your search...</span>
        <div class="voice-interim" id="voice-interim-result"></div>
        <button class="voice-stop-btn" onclick="window.voiceSearchManager.stopListening()" aria-label="Stop voice search">
            <i class="fas fa-stop"></i>
            Stop
        </button>
    </div>
</div>

<!-- Voice Search Styles -->
<style>
/* Voice Search Container */
.voice-search-container {
    margin-bottom: 20px;
}

/* Enhanced Search Bar */
.search-bar.voice-enabled {
    background-color: var(--card-bg);
    border-radius: 15px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
}

.search-input-container {
    flex: 1;
    margin-right: 20px;
    position: relative;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input {
    width: 100%;
    padding: 12px 15px 12px 40px;
    border-radius: 30px;
    border: 1px solid var(--divider);
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    transition: all 0.3s ease;
    padding-right: 50px; /* Make room for voice button */
}

.search-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(37, 113, 128, 0.2);
    border-color: var(--primary);
}

/* Voice listening state */
.search-input.voice-listening {
    border-color: var(--accent);
    box-shadow: 0 0 0 2px rgba(253, 139, 81, 0.3);
    animation: voicePulse 2s infinite;
}

@keyframes voicePulse {
    0%, 100% { box-shadow: 0 0 0 2px rgba(253, 139, 81, 0.3); }
    50% { box-shadow: 0 0 0 4px rgba(253, 139, 81, 0.5); }
}

.search-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
    z-index: 2;
}

/* Voice Search Button */
.voice-search-btn {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: var(--primary);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.3s ease;
    z-index: 3;
}

.voice-search-btn:hover {
    background: var(--accent);
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(253, 139, 81, 0.4);
}

.voice-search-btn:active {
    transform: translateY(-50%) scale(0.95);
}

/* Voice button states */
.voice-search-btn.listening {
    background: #dc3545;
    animation: voiceListening 1.5s infinite;
}

.voice-search-btn.error {
    background: #dc3545;
    animation: voiceError 0.5s;
}

@keyframes voiceListening {
    0%, 100% { background: #dc3545; }
    50% { background: #ff6b7a; }
}

@keyframes voiceError {
    0%, 100% { background: #dc3545; }
    25%, 75% { background: #ff1744; }
    50% { background: #d50000; }
}

/* Voice Search Status */
.voice-search-status {
    position: absolute;
    bottom: -25px;
    left: 15px;
    font-size: 12px;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 5px;
}

.voice-search-status.listening {
    color: var(--accent);
}

.voice-search-status.error {
    color: #dc3545;
}

/* Language Toggle */
.voice-language-toggle {
    display: flex;
    margin-left: 10px;
    background: var(--bg-color);
    border-radius: 20px;
    padding: 2px;
}

.lang-btn {
    padding: 6px 12px;
    border: none;
    background: transparent;
    border-radius: 18px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    transition: all 0.2s;
}

.lang-btn.active {
    background: var(--primary);
    color: white;
}

.lang-btn:hover:not(.active) {
    background: rgba(0, 0, 0, 0.05);
}

/* Voice Help Text */
.voice-help-text {
    margin-top: 10px;
    padding: 10px 15px;
    background: rgba(37, 113, 128, 0.05);
    border-radius: 10px;
    display: none; /* Show when voice search is first used */
}

.voice-help-text.show {
    display: block;
    animation: slideDown 0.3s ease;
}

.voice-help-content {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--text-secondary);
}

.voice-help-content i {
    color: var(--primary);
}

/* Voice Listening Indicator */
.voice-listening-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1002;
    backdrop-filter: blur(10px);
    border: 2px solid var(--accent);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    min-width: 300px;
    text-align: center;
}

.voice-listening-indicator.show {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, -50%) scale(1);
}

.voice-indicator-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

/* Voice Animation */
.voice-animation {
    display: flex;
    gap: 4px;
    height: 40px;
    align-items: center;
}

.voice-wave {
    width: 4px;
    background: var(--accent);
    border-radius: 2px;
    animation: voiceWave 1.5s infinite;
}

.voice-wave:nth-child(1) { animation-delay: 0s; }
.voice-wave:nth-child(2) { animation-delay: 0.1s; }
.voice-wave:nth-child(3) { animation-delay: 0.2s; }
.voice-wave:nth-child(4) { animation-delay: 0.3s; }

@keyframes voiceWave {
    0%, 100% { height: 20px; }
    50% { height: 40px; }
}

.voice-status-text {
    font-size: 16px;
    font-weight: 500;
    color: var(--text-primary);
}

/* Voice Interim Results */
.voice-interim {
    min-height: 20px;
    padding: 8px 12px;
    background: rgba(37, 113, 128, 0.1);
    border-radius: 10px;
    font-style: italic;
    color: var(--text-secondary);
    font-size: 14px;
    max-width: 250px;
    word-wrap: break-word;
    display: none;
}

.voice-interim:not(:empty) {
    display: block;
}

.voice-stop-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 25px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.voice-stop-btn:hover {
    background: #c82333;
    transform: translateY(-2px);
}

/* Voice Result Feedback */
.voice-result-feedback {
    position: fixed;
    top: 100px;
    right: 20px;
    background: rgba(40, 167, 69, 0.95);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1001;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
    backdrop-filter: blur(10px);
}

.voice-result-feedback.show {
    transform: translateX(0);
}

.voice-result-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.voice-result-content i {
    font-size: 18px;
}

/* Filter Button Updates */
.filter-btn {
    background-color: var(--card-bg);
    border: 1px solid var(--divider);
    padding: 12px 20px;
    border-radius: 30px;
    cursor: pointer;
    display: flex;
    align-items: center;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s;
    white-space: nowrap;
}

.filter-btn:hover, .filter-btn:focus {
    background-color: var(--secondary);
    outline: none;
}

.filter-btn i {
    margin-right: 10px;
}

/* TTS Quick Button */
.search-bar-tts {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}

.tts-quick-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 20px;
    padding: 8px 15px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.tts-quick-btn:hover {
    background: var(--accent);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-bar.voice-enabled {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .search-input-container {
        margin-right: 0;
    }
    
    .filter-btn, .tts-quick-btn {
        width: 100%;
        justify-content: center;
    }
    
    .voice-listening-indicator {
        left: 10px;
        right: 10px;
        transform: translateY(-50%);
        min-width: auto;
    }
    
    .voice-listening-indicator.show {
        transform: translateY(-50%);
    }
    
    .voice-help-text {
        font-size: 12px;
    }
    
    .lang-btn span {
        display: none;
    }
    
    .lang-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 50%;
    }
}

/* High Contrast Mode */
body.high-contrast .voice-search-btn {
    border: 2px solid white;
}

body.high-contrast .voice-listening-indicator {
    border: 3px solid var(--accent);
    background: #000000;
    color: #FFFFFF;
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .voice-search-btn,
    .voice-listening-indicator,
    .voice-result-feedback,
    .voice-wave {
        animation: none !important;
        transition: none !important;
    }
}

/* Focus Management */
.voice-search-btn:focus {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}

.voice-stop-btn:focus {
    outline: 2px solid white;
    outline-offset: 2px;
}

/* Loading State */
.search-input.loading::after {
    content: '';
    position: absolute;
    right: 55px; /* Adjusted for voice button */
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}
</style>

<script>
// Voice Search Integration JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeVoiceSearch();
});

function initializeVoiceSearch() {
    console.log('Initializing voice search integration...');
    
    const voiceBtn = document.getElementById('voice-search-btn');
    const searchInput = document.getElementById('job-search');
    const helpText = document.getElementById('voice-help-text');
    
    if (!voiceBtn || !window.voiceSearchManager) {
        console.warn('Voice search not available');
        return;
    }
    
    // Check if voice search is supported
    if (!window.voiceSearchManager.isSupported) {
        voiceBtn.style.display = 'none';
        console.warn('Voice search not supported in this browser');
        return;
    }
    
    // Set up voice search result callback
    window.voiceSearchManager.setCallbacks(
        handleVoiceSearchResult,
        handleVoiceSearchError,
        handleVoiceSearchStatusChange
    );
    
    // Voice button click handler
    voiceBtn.addEventListener('click', function() {
        if (window.voiceSearchManager.isListening) {
            window.voiceSearchManager.stopListening();
        } else {
            startVoiceSearch();
        }
    });
    
    // Show help text on first use
    let voiceSearchUsed = localStorage.getItem('voice-search-used');
    if (!voiceSearchUsed) {
        voiceBtn.addEventListener('mouseenter', function() {
            showVoiceHelp();
        }, { once: true });
    }
    
    // Language toggle (if enabled)
    const langButtons = document.querySelectorAll('.lang-btn');
    langButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const lang = this.dataset.lang;
            window.voiceSearchManager.setLanguage(lang);
            
            // Update active state
            langButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update button title
            voiceBtn.title = lang === 'fil-PH' ? 'Paghahanap gamit ang boses' : 'Voice search';
        });
    });
    
    // Keyboard shortcuts
    searchInput.addEventListener('keydown', function(e) {
        // Ctrl + Shift + V: Start voice search
        if (e.ctrlKey && e.shiftKey && e.key === 'V') {
            e.preventDefault();
            startVoiceSearch();
        }
    });
}

async function startVoiceSearch() {
    // Request microphone permission first
    const hasPermission = await window.voiceSearchManager.requestMicrophonePermission();
    if (!hasPermission) {
        showError('Microphone permission is required for voice search. Please allow microphone access and try again.');
        return;
    }
    
    // Show help text on first use
    const voiceSearchUsed = localStorage.getItem('voice-search-used');
    if (!voiceSearchUsed) {
        showVoiceHelp();
        localStorage.setItem('voice-search-used', 'true');
    }
    
    // Start listening
    window.voiceSearchManager.startListening();
}

function handleVoiceSearchResult(transcript) {
    console.log('Voice search result:', transcript);
    
    // Update search input
    const searchInput = document.getElementById('job-search');
    if (searchInput) {
        searchInput.value = transcript;
        
        // Trigger search
        const event = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(event);
    }
    
    // Hide help text
    hideVoiceHelp();
}

function handleVoiceSearchError(error, message) {
    console.error('Voice search error:', error, message);
    
    // Show error using existing system
    if (typeof showError === 'function') {
        showError(message);
    }
}

function handleVoiceSearchStatusChange(status) {
    console.log('Voice search status:', status);
    
    // Update status display
    const statusDisplay = document.getElementById('voice-search-status');
    if (statusDisplay) {
        statusDisplay.style.display = status === 'stopped' ? 'none' : 'block';
    }
}

function showVoiceHelp() {
    const helpText = document.getElementById('voice-help-text');
    if (helpText) {
        helpText.classList.add('show');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideVoiceHelp();
        }, 5000);
    }
}

function hideVoiceHelp() {
    const helpText = document.getElementById('voice-help-text');
    if (helpText) {
        helpText.classList.remove('show');
    }
}

// Make functions globally available
window.startVoiceSearch = startVoiceSearch;
window.handleVoiceSearchResult = handleVoiceSearchResult;
window.handleVoiceSearchError = handleVoiceSearchError;
window.handleVoiceSearchStatusChange = handleVoiceSearchStatusChange;
</script>