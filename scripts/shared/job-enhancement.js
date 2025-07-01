// Simple Enhancement Add-On for Existing Job Listings
// This file ADDS features without breaking existing functionality

// Wait for your existing page to load first
document.addEventListener('DOMContentLoaded', function() {
    // Only enhance if job listings exist
    setTimeout(enhanceExistingJobListings, 2000);
});

function enhanceExistingJobListings() {
    console.log('Starting job listings enhancement...');
    
    // Check if jobs are loaded
    const jobCards = document.querySelectorAll('.job-card');
    if (jobCards.length === 0) {
        console.log('No job cards found yet, will retry...');
        setTimeout(enhanceExistingJobListings, 2000);
        return;
    }
    
    console.log(`Found ${jobCards.length} job cards to enhance`);
    
    // Add TTS features safely
    addTTSFeatures();
    
    // Add Voice Search safely
    addVoiceSearchFeatures();
    
    // Add compatibility scores safely
    addCompatibilityFeatures();
}

// ===================================================================
// TTS ENHANCEMENT (SAFE VERSION)
// ===================================================================

function addTTSFeatures() {
    console.log('Adding TTS features...');
    
    // Only add if TTS is supported
    if (!('speechSynthesis' in window)) {
        console.log('TTS not supported');
        return;
    }
    
    // Add simple TTS button to each job card
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach(addTTSToJobCard);
    
    // Add floating TTS control
    addFloatingTTSControl();
}

function addTTSToJobCard(jobCard) {
    // Don't add if already has TTS button
    if (jobCard.querySelector('.simple-tts-btn')) return;
    
    const ttsBtn = document.createElement('button');
    ttsBtn.className = 'simple-tts-btn';
    ttsBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    ttsBtn.title = 'Read job description aloud';
    ttsBtn.setAttribute('aria-label', 'Read job description');
    
    ttsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        readJobCardSafely(jobCard);
    });
    
    // Add to top-right corner
    jobCard.style.position = 'relative';
    ttsBtn.style.cssText = `
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 50%;
        background: rgba(37, 113, 128, 0.9);
        color: white;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.3s ease;
    `;
    
    ttsBtn.addEventListener('mouseenter', function() {
        this.style.background = 'rgba(253, 139, 81, 0.9)';
        this.style.transform = 'scale(1.1)';
    });
    
    ttsBtn.addEventListener('mouseleave', function() {
        this.style.background = 'rgba(37, 113, 128, 0.9)';
        this.style.transform = 'scale(1)';
    });
    
    jobCard.appendChild(ttsBtn);
}

function readJobCardSafely(jobCard) {
    try {
        // Stop any current speech
        speechSynthesis.cancel();
        
        // Extract text safely
        const title = jobCard.querySelector('.job-title')?.textContent || 'Job opening';
        const company = jobCard.querySelector('.company-name')?.textContent || '';
        const description = jobCard.querySelector('.job-description')?.textContent || '';
        
        let textToRead = `${title}`;
        if (company) textToRead += ` at ${company.replace(/^\s*[^\w]*\s*/, '')}`;
        if (description) textToRead += `. ${description}`;
        
        if (textToRead.length < 10) {
            textToRead = 'Job information is available on screen';
        }
        
        const utterance = new SpeechSynthesisUtterance(textToRead);
        utterance.rate = 0.9;
        utterance.volume = 0.8;
        
        utterance.onstart = function() {
            jobCard.style.backgroundColor = 'rgba(253, 139, 81, 0.1)';
        };
        
        utterance.onend = function() {
            jobCard.style.backgroundColor = '';
        };
        
        speechSynthesis.speak(utterance);
        
    } catch (error) {
        console.error('TTS error:', error);
    }
}

function addFloatingTTSControl() {
    const floatingBtn = document.createElement('button');
    floatingBtn.id = 'floating-tts-btn';
    floatingBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
    floatingBtn.title = 'Read page content';
    
    floatingBtn.style.cssText = `
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 50px;
        height: 50px;
        border: none;
        border-radius: 50%;
        background: #257180;
        color: white;
        cursor: pointer;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease;
    `;
    
    floatingBtn.addEventListener('click', function() {
        readPageSummary();
    });
    
    floatingBtn.addEventListener('mouseenter', function() {
        this.style.background = '#FD8B51';
        this.style.transform = 'scale(1.1)';
    });
    
    floatingBtn.addEventListener('mouseleave', function() {
        this.style.background = '#257180';
        this.style.transform = 'scale(1)';
    });
    
    document.body.appendChild(floatingBtn);
}

function readPageSummary() {
    try {
        speechSynthesis.cancel();
        
        const jobCards = document.querySelectorAll('.job-card');
        const jobCount = jobCards.length;
        
        let summaryText = `Job listings page. Found ${jobCount} job opportunities. `;
        
        if (jobCount > 0) {
            summaryText += 'Click on individual jobs to read their descriptions, or use the speaker button on each job card.';
        } else {
            summaryText += 'No jobs found. Try adjusting your search criteria.';
        }
        
        const utterance = new SpeechSynthesisUtterance(summaryText);
        utterance.rate = 0.9;
        utterance.volume = 0.8;
        
        speechSynthesis.speak(utterance);
        
    } catch (error) {
        console.error('Page summary TTS error:', error);
    }
}

// ===================================================================
// VOICE SEARCH ENHANCEMENT (SAFE VERSION)
// ===================================================================

function addVoiceSearchFeatures() {
    console.log('Adding voice search features...');
    
    // Check if Web Speech API is supported
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.log('Voice search not supported');
        return;
    }
    
    // Find existing search input
    const searchInput = document.getElementById('job-search') || document.querySelector('input[placeholder*="search"]');
    if (!searchInput) {
        console.log('Search input not found');
        return;
    }
    
    // Add voice button to search
    addVoiceButtonToSearch(searchInput);
}

function addVoiceButtonToSearch(searchInput) {
    // Don't add if already has voice button
    if (searchInput.parentElement.querySelector('.voice-btn')) return;
    
    const voiceBtn = document.createElement('button');
    voiceBtn.className = 'voice-btn';
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
    voiceBtn.title = 'Voice search';
    voiceBtn.type = 'button';
    
    voiceBtn.style.cssText = `
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 50%;
        background: #257180;
        color: white;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    `;
    
    // Make sure search input parent is positioned
    searchInput.parentElement.style.position = 'relative';
    searchInput.style.paddingRight = '45px';
    
    voiceBtn.addEventListener('click', function() {
        startVoiceSearch(searchInput);
    });
    
    searchInput.parentElement.appendChild(voiceBtn);
}

function startVoiceSearch(searchInput) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    const voiceBtn = searchInput.parentElement.querySelector('.voice-btn');
    
    recognition.onstart = function() {
        voiceBtn.style.background = '#dc3545';
        voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
        searchInput.placeholder = 'Listening... speak now';
    };
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        searchInput.value = transcript;
        
        // Trigger search
        const searchEvent = new Event('input', { bubbles: true });
        searchInput.dispatchEvent(searchEvent);
    };
    
    recognition.onend = function() {
        voiceBtn.style.background = '#257180';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        searchInput.placeholder = 'Search for jobs...';
    };
    
    recognition.onerror = function(event) {
        console.error('Voice recognition error:', event.error);
        voiceBtn.style.background = '#257180';
        voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        searchInput.placeholder = 'Search for jobs...';
    };
    
    try {
        recognition.start();
    } catch (error) {
        console.error('Could not start voice recognition:', error);
    }
}

// ===================================================================
// COMPATIBILITY ENHANCEMENT (SAFE VERSION)
// ===================================================================

function addCompatibilityFeatures() {
    console.log('Adding compatibility features...');
    
    // Add compatibility scores to existing job cards
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach(addCompatibilityToJobCard);
}

function addCompatibilityToJobCard(jobCard) {
    // Don't add if already has compatibility
    if (jobCard.querySelector('.compatibility-badge')) return;
    
    const jobId = jobCard.dataset.jobId || jobCard.getAttribute('data-job-id');
    if (!jobId) return;
    
    // Fetch compatibility score
    fetchCompatibilityScore(jobId, jobCard);
}

async function fetchCompatibilityScore(jobId, jobCard) {
    try {
        const response = await fetch(`../../backend/candidate/get_compatibility_score.php?job_id=${jobId}`);
        const data = await response.json();
        
        if (data.success) {
            displayCompatibilityBadge(jobCard, data.compatibility);
        }
    } catch (error) {
        console.error('Error fetching compatibility:', error);
        // Show default badge
        displayCompatibilityBadge(jobCard, { percentage: 75, level: 'medium' });
    }
}

function displayCompatibilityBadge(jobCard, compatibility) {
    const badge = document.createElement('div');
    badge.className = `compatibility-badge ${compatibility.level}`;
    badge.innerHTML = `
        <div style="font-size: 12px; font-weight: bold;">${compatibility.percentage}%</div>
        <div style="font-size: 10px;">Match</div>
    `;
    
    // Style based on compatibility level
    let bgColor = '#6c757d';
    if (compatibility.level === 'excellent') bgColor = '#28a745';
    else if (compatibility.level === 'high') bgColor = '#20c997';
    else if (compatibility.level === 'medium') bgColor = '#ffc107';
    else if (compatibility.level === 'low') bgColor = '#dc3545';
    
    badge.style.cssText = `
        position: absolute;
        top: -5px;
        right: -5px;
        background: ${bgColor};
        color: white;
        padding: 5px 8px;
        border-radius: 12px;
        font-size: 11px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 5;
        min-width: 45px;
    `;
    
    badge.title = `${compatibility.percentage}% compatibility match`;
    
    jobCard.style.position = 'relative';
    jobCard.appendChild(badge);
}

// ===================================================================
// KEYBOARD SHORTCUTS
// ===================================================================

document.addEventListener('keydown', function(e) {
    // Don't activate shortcuts when typing
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    
    // Ctrl + R: Read page summary
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        readPageSummary();
    }
    
    // Ctrl + S: Stop speech
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        speechSynthesis.cancel();
    }
});

// ===================================================================
// CSS STYLES FOR ENHANCEMENTS
// ===================================================================

const enhancementStyles = document.createElement('style');
enhancementStyles.textContent = `
    .simple-tts-btn:hover {
        background: rgba(253, 139, 81, 0.9) !important;
        transform: scale(1.1) !important;
    }
    
    .voice-btn:hover {
        background: #FD8B51 !important;
        transform: scale(1.1);
    }
    
    .compatibility-badge.excellent {
        background: linear-gradient(135deg, #28a745, #34ce57) !important;
    }
    
    .compatibility-badge.high {
        background: linear-gradient(135deg, #20c997, #36d1a7) !important;
    }
    
    .compatibility-badge.medium {
        background: linear-gradient(135deg, #ffc107, #ffcd39) !important;
        color: #333 !important;
    }
    
    .compatibility-badge.low {
        background: linear-gradient(135deg, #dc3545, #e4606d) !important;
    }
    
    @media (max-width: 768px) {
        #floating-tts-btn {
            bottom: 60px !important;
            right: 15px !important;
            width: 45px !important;
            height: 45px !important;
        }
        
        .compatibility-badge {
            right: 5px !important;
            top: 5px !important;
            font-size: 10px !important;
            padding: 3px 6px !important;
        }
    }
`;
document.head.appendChild(enhancementStyles);

console.log('Job listings enhancement script loaded!');