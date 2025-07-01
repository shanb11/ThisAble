<?php
// includes/candidate/tts_controls.php
// Reusable Text-to-Speech Controls Component

// Default options
$tts_options = array_merge([
    'show_main_controls' => true,
    'show_speed_controls' => true,
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left
    'compact' => false
], $tts_options ?? []);
?>

<!-- TTS Main Controls -->
<?php if ($tts_options['show_main_controls']): ?>
<div class="tts-main-controls <?php echo $tts_options['compact'] ? 'compact' : ''; ?>" 
     data-position="<?php echo $tts_options['position']; ?>">
    
    <!-- TTS Toggle Button -->
    <button class="tts-toggle-btn" id="tts-toggle" aria-label="Text-to-speech controls">
        <i class="fas fa-volume-up"></i>
        <span class="tts-toggle-text">Read Aloud</span>
    </button>

    <!-- TTS Control Panel (Hidden by default) -->
    <div class="tts-control-panel" id="tts-control-panel" style="display: none;">
        <div class="tts-panel-header">
            <h4>
                <i class="fas fa-volume-up"></i>
                Text-to-Speech
            </h4>
            <button class="tts-close-panel" id="tts-close-panel" aria-label="Close controls">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="tts-panel-body">
            <!-- Main Controls -->
            <div class="tts-main-buttons">
                <button class="tts-btn primary" id="tts-read-page" title="Read entire page">
                    <i class="fas fa-play"></i>
                    <span>Read Page</span>
                </button>
                
                <button class="tts-btn" id="tts-pause-resume" title="Pause/Resume reading">
                    <i class="fas fa-pause"></i>
                    <span>Pause</span>
                </button>
                
                <button class="tts-btn" id="tts-stop" title="Stop reading">
                    <i class="fas fa-stop"></i>
                    <span>Stop</span>
                </button>
            </div>

            <?php if ($tts_options['show_speed_controls']): ?>
            <!-- Speed Controls -->
            <div class="tts-speed-controls">
                <label for="tts-speed">Reading Speed:</label>
                <div class="tts-speed-options">
                    <button class="tts-speed-btn" data-speed="0.7">
                        <i class="fas fa-backward"></i>
                        Slow
                    </button>
                    <button class="tts-speed-btn active" data-speed="1.0">
                        <i class="fas fa-play"></i>
                        Normal
                    </button>
                    <button class="tts-speed-btn" data-speed="1.3">
                        <i class="fas fa-forward"></i>
                        Fast
                    </button>
                </div>
            </div>

            <!-- Volume Controls -->
            <div class="tts-volume-controls">
                <label for="tts-volume">Volume:</label>
                <div class="tts-volume-slider">
                    <i class="fas fa-volume-down"></i>
                    <input type="range" id="tts-volume" min="0" max="100" value="100" step="10">
                    <i class="fas fa-volume-up"></i>
                </div>
            </div>
            <?php endif; ?>

            <!-- Page-specific Controls -->
            <div class="tts-page-controls">
                <?php if (basename($_SERVER['PHP_SELF']) == 'joblistings.php'): ?>
                <button class="tts-btn secondary" id="tts-read-all-jobs" title="Read all job listings">
                    <i class="fas fa-list"></i>
                    <span>Read All Jobs</span>
                </button>
                <?php elseif (basename($_SERVER['PHP_SELF']) == 'notifications.php'): ?>
                <button class="tts-btn secondary" id="tts-read-notifications" title="Read all notifications">
                    <i class="fas fa-bell"></i>
                    <span>Read Notifications</span>
                </button>
                <?php elseif (basename($_SERVER['PHP_SELF']) == 'applications.php'): ?>
                <button class="tts-btn secondary" id="tts-read-applications" title="Read application status">
                    <i class="fas fa-file-alt"></i>
                    <span>Read Applications</span>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- TTS Status Display -->
        <div class="tts-status" id="tts-status">
            <div class="tts-status-text">
                <i class="fas fa-info-circle"></i>
                <span id="tts-status-message">Ready to read</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Individual TTS Button Template (for job cards, etc.) -->
<template id="tts-button-template">
    <button class="tts-btn individual" 
            data-tts-target="" 
            title="Read this content aloud"
            aria-label="Read aloud">
        <i class="fas fa-volume-up"></i>
    </button>
</template>

<!-- TTS Reading Indicator (Global) -->
<div id="tts-reading-indicator" class="tts-reading-indicator">
    <div class="tts-indicator-content">
        <div class="tts-indicator-icon">
            <i class="fas fa-volume-up"></i>
            <div class="tts-sound-waves">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <span class="tts-indicator-text">Reading...</span>
        <button class="tts-stop-btn" onclick="window.ttsManager.stop()" aria-label="Stop reading">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- TTS Styles -->
<style>
/* TTS Main Controls */
.tts-main-controls {
    position: fixed;
    top: 100px;
    right: 20px;
    z-index: 1000;
    font-family: 'Inter', sans-serif;
}

.tts-main-controls[data-position="top-left"] {
    top: 100px;
    left: 20px;
    right: auto;
}

.tts-main-controls[data-position="bottom-right"] {
    top: auto;
    bottom: 20px;
    right: 20px;
}

.tts-main-controls[data-position="bottom-left"] {
    top: auto;
    bottom: 20px;
    left: 20px;
    right: auto;
}

.tts-toggle-btn {
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(37, 113, 128, 0.3);
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
}

.tts-toggle-btn:hover {
    background: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(253, 139, 81, 0.4);
}

.tts-toggle-btn.compact {
    padding: 10px;
    border-radius: 50%;
}

.tts-toggle-btn.compact .tts-toggle-text {
    display: none;
}

/* TTS Control Panel */
.tts-control-panel {
    position: absolute;
    top: 60px;
    right: 0;
    width: 280px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border: 1px solid var(--divider);
    overflow: hidden;
    animation: slideDown 0.3s ease;
}

.tts-main-controls[data-position*="left"] .tts-control-panel {
    right: auto;
    left: 0;
}

.tts-main-controls[data-position*="bottom"] .tts-control-panel {
    top: auto;
    bottom: 60px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tts-panel-header {
    background: var(--primary);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.tts-panel-header h4 {
    margin: 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tts-close-panel {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: background 0.2s;
}

.tts-close-panel:hover {
    background: rgba(255, 255, 255, 0.2);
}

.tts-panel-body {
    padding: 20px;
}

/* TTS Buttons */
.tts-main-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}

.tts-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    min-height: 60px;
}

.tts-btn.primary {
    background: var(--accent);
    color: white;
}

.tts-btn.secondary {
    background: var(--secondary);
    color: var(--primary);
}

.tts-btn:not(.primary):not(.secondary) {
    background: var(--bg-color);
    color: var(--text-secondary);
    border: 1px solid var(--divider);
}

.tts-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.tts-btn.primary:hover {
    background: var(--accent-secondary);
}

.tts-btn i {
    font-size: 16px;
}

.tts-btn span {
    font-size: 11px;
    font-weight: 500;
}

/* Individual TTS Button */
.tts-btn.individual {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    min-height: 32px;
    border-radius: 50%;
    background: rgba(37, 113, 128, 0.9);
    color: white;
    padding: 0;
    font-size: 14px;
    opacity: 0.8;
    transition: all 0.3s ease;
}

.tts-btn.individual:hover {
    opacity: 1;
    background: var(--accent);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(253, 139, 81, 0.4);
}

/* Speed Controls */
.tts-speed-controls {
    margin-bottom: 15px;
}

.tts-speed-controls label {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-primary);
}

.tts-speed-options {
    display: flex;
    gap: 5px;
}

.tts-speed-btn {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid var(--divider);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 11px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.tts-speed-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.tts-speed-btn:hover:not(.active) {
    background: var(--bg-color);
}

/* Volume Controls */
.tts-volume-controls {
    margin-bottom: 15px;
}

.tts-volume-controls label {
    display: block;
    margin-bottom: 8px;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-primary);
}

.tts-volume-slider {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tts-volume-slider input[type="range"] {
    flex: 1;
    height: 4px;
    border-radius: 2px;
    background: var(--divider);
    outline: none;
    cursor: pointer;
}

.tts-volume-slider input[type="range"]::-webkit-slider-thumb {
    appearance: none;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--primary);
    cursor: pointer;
}

.tts-volume-slider i {
    color: var(--text-secondary);
    font-size: 12px;
}

/* Page Controls */
.tts-page-controls {
    border-top: 1px solid var(--divider);
    padding-top: 15px;
}

.tts-page-controls .tts-btn {
    width: 100%;
    flex-direction: row;
    justify-content: center;
    min-height: 40px;
}

/* Status Display */
.tts-status {
    background: var(--bg-color);
    padding: 10px 15px;
    border-top: 1px solid var(--divider);
    margin: 0 -20px -20px -20px;
}

.tts-status-text {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--text-secondary);
}

.tts-status-text i {
    color: var(--primary);
}

/* Reading Indicator */
.tts-reading-indicator {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: rgba(47, 138, 153, 0.95);
    color: white;
    padding: 15px 25px;
    border-radius: 50px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
    z-index: 1001;
    backdrop-filter: blur(10px);
}

.tts-reading-indicator.show {
    transform: translateX(-50%) translateY(0);
}

.tts-indicator-content {
    display: flex;
    align-items: center;
    gap: 15px;
}

.tts-indicator-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.tts-sound-waves {
    display: flex;
    gap: 2px;
    margin-left: 8px;
}

.tts-sound-waves span {
    width: 3px;
    height: 12px;
    background: white;
    border-radius: 1px;
    animation: soundWave 1.2s infinite;
}

.tts-sound-waves span:nth-child(2) {
    animation-delay: 0.1s;
}

.tts-sound-waves span:nth-child(3) {
    animation-delay: 0.2s;
}

@keyframes soundWave {
    0%, 100% { transform: scaleY(0.5); }
    50% { transform: scaleY(1); }
}

.tts-indicator-text {
    font-size: 14px;
    font-weight: 500;
}

.tts-stop-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s;
}

.tts-stop-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Reading State */
.tts-reading {
    background-color: rgba(253, 139, 81, 0.1) !important;
    border-left: 4px solid var(--accent) !important;
    transform: translateX(5px);
    transition: all 0.3s ease;
}

/* Button States */
.tts-btn.tts-speaking {
    background: var(--accent) !important;
    color: white !important;
}

.tts-btn.tts-paused {
    background: var(--warning) !important;
    color: white !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tts-main-controls {
        right: 10px;
        top: 10px;
    }
    
    .tts-control-panel {
        width: 260px;
        right: -10px;
    }
    
    .tts-main-controls[data-position*="left"] .tts-control-panel {
        left: -10px;
    }
    
    .tts-reading-indicator {
        left: 10px;
        right: 10px;
        transform: translateY(100px);
        border-radius: 15px;
    }
    
    .tts-reading-indicator.show {
        transform: translateY(0);
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .tts-reading-indicator,
    .tts-control-panel,
    .tts-btn,
    .tts-reading {
        transition: none !important;
        animation: none !important;
    }
}
</style>

<script>
// TTS Controls JavaScript (inline for immediate availability)
document.addEventListener('DOMContentLoaded', function() {
    initializeTTSControls();
});

function initializeTTSControls() {
    const ttsToggle = document.getElementById('tts-toggle');
    const ttsPanel = document.getElementById('tts-control-panel');
    const ttsClose = document.getElementById('tts-close-panel');
    
    // Toggle panel visibility
    if (ttsToggle) {
        ttsToggle.addEventListener('click', function() {
            const isVisible = ttsPanel.style.display !== 'none';
            ttsPanel.style.display = isVisible ? 'none' : 'block';
        });
    }
    
    // Close panel
    if (ttsClose) {
        ttsClose.addEventListener('click', function() {
            ttsPanel.style.display = 'none';
        });
    }
    
    // Close panel when clicking outside
    document.addEventListener('click', function(event) {
        const ttsControls = document.querySelector('.tts-main-controls');
        if (ttsControls && !ttsControls.contains(event.target)) {
            if (ttsPanel) ttsPanel.style.display = 'none';
        }
    });
    
    // Initialize control buttons
    initializeTTSButtons();
}

function initializeTTSButtons() {
    // Read page button
    const readPageBtn = document.getElementById('tts-read-page');
    if (readPageBtn) {
        readPageBtn.addEventListener('click', function() {
            readEntirePage();
        });
    }
    
    // Pause/Resume button
    const pauseResumeBtn = document.getElementById('tts-pause-resume');
    if (pauseResumeBtn) {
        pauseResumeBtn.addEventListener('click', function() {
            if (window.ttsManager.isSpeaking && !window.ttsManager.isPaused) {
                window.ttsManager.pause();
            } else if (window.ttsManager.isPaused) {
                window.ttsManager.resume();
            }
        });
    }
    
    // Stop button
    const stopBtn = document.getElementById('tts-stop');
    if (stopBtn) {
        stopBtn.addEventListener('click', function() {
            window.ttsManager.stop();
        });
    }
    
    // Speed buttons
    document.querySelectorAll('.tts-speed-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const speed = parseFloat(this.dataset.speed);
            window.ttsManager.setRate(speed);
            
            // Update active state
            document.querySelectorAll('.tts-speed-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Volume slider
    const volumeSlider = document.getElementById('tts-volume');
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            const volume = this.value / 100;
            window.ttsManager.setVolume(volume);
        });
    }
    
    // Page-specific buttons
    initializePageSpecificButtons();
}

function initializePageSpecificButtons() {
    // Read all jobs button (for job listings page)
    const readJobsBtn = document.getElementById('tts-read-all-jobs');
    if (readJobsBtn) {
        readJobsBtn.addEventListener('click', function() {
            readAllJobs();
        });
    }
    
    // Read notifications button
    const readNotifBtn = document.getElementById('tts-read-notifications');
    if (readNotifBtn) {
        readNotifBtn.addEventListener('click', function() {
            readAllNotifications();
        });
    }
    
    // Read applications button
    const readAppsBtn = document.getElementById('tts-read-applications');
    if (readAppsBtn) {
        readAppsBtn.addEventListener('click', function() {
            readAllApplications();
        });
    }
}

// Page-specific reading functions
function readEntirePage() {
    const mainContent = document.querySelector('.main-content') || document.body;
    const textContent = extractReadableText(mainContent);
    window.ttsManager.speak(textContent);
}

function readAllJobs() {
    const jobCards = document.querySelectorAll('.job-card');
    if (jobCards.length === 0) {
        window.ttsManager.speak('No job listings found on this page.');
        return;
    }
    
    let allJobsText = `Found ${jobCards.length} job opportunities. `;
    jobCards.forEach((card, index) => {
        const jobTitle = card.querySelector('.job-title')?.textContent || '';
        const companyName = card.querySelector('.company-name')?.textContent || '';
        allJobsText += `Job ${index + 1}: ${jobTitle} at ${companyName}. `;
    });
    
    window.ttsManager.speak(allJobsText);
}

function readAllNotifications() {
    const notifications = document.querySelectorAll('.notification-item');
    if (notifications.length === 0) {
        window.ttsManager.speak('No notifications found.');
        return;
    }
    
    let notifText = `You have ${notifications.length} notifications. `;
    notifications.forEach((notif, index) => {
        const title = notif.querySelector('.notification-title')?.textContent || '';
        const message = notif.querySelector('.notification-message')?.textContent || '';
        notifText += `Notification ${index + 1}: ${title}. ${message}. `;
    });
    
    window.ttsManager.speak(notifText);
}

function readAllApplications() {
    const applications = document.querySelectorAll('.application-item');
    if (applications.length === 0) {
        window.ttsManager.speak('No applications found.');
        return;
    }
    
    let appsText = `You have ${applications.length} job applications. `;
    applications.forEach((app, index) => {
        const jobTitle = app.querySelector('.job-title')?.textContent || '';
        const status = app.querySelector('.status')?.textContent || '';
        appsText += `Application ${index + 1}: ${jobTitle}, status: ${status}. `;
    });
    
    window.ttsManager.speak(appsText);
}

function extractReadableText(element) {
    // Extract meaningful text while ignoring navigation, ads, etc.
    const elementsToSkip = ['nav', 'header', 'footer', '.sidebar', '.advertisement'];
    const clone = element.cloneNode(true);
    
    // Remove unwanted elements
    elementsToSkip.forEach(selector => {
        clone.querySelectorAll(selector).forEach(el => el.remove());
    });
    
    return clone.textContent.trim();
}
</script>