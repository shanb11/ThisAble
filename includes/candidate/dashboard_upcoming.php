<div class="dashboard-section">
    <div class="section-header">
        <h2 class="section-title">Upcoming Interviews</h2>
        <a href="applications.php#interviews" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
    </div>
    
    <div id="upcoming-interviews-container">
        <!-- Loading state -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading your upcoming interviews...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadUpcomingInterviews();
});

function loadUpcomingInterviews() {
    fetch('../../backend/candidate/get_candidate_interviews.php?status=upcoming&limit=3')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayUpcomingInterviews(data.interviews);
            } else {
                showNoInterviewsMessage();
            }
        })
        .catch(error => {
            console.error('Error fetching interviews:', error);
            showErrorMessage();
        });
}

function displayUpcomingInterviews(interviews) {
    const container = document.getElementById('upcoming-interviews-container');
    
    if (interviews.length === 0) {
        showNoInterviewsMessage();
        return;
    }
    
    const interviewsHTML = `
        <div class="interviews-list">
            ${interviews.map(interview => `
                <div class="interview-card" data-interview-id="${interview.id}">
                    <div class="interview-header">
                        <div class="company-info">
                            <div class="company-logo">${interview.company_logo}</div>
                            <div class="interview-title">
                                <h3 class="interview-company">Interview with ${interview.company_name}</h3>
                                <p class="job-title">${interview.job_title}</p>
                            </div>
                        </div>
                        <span class="interview-type ${interview.interview_type}">${interview.interview_type_display}</span>
                    </div>
                    
                    <div class="interview-details-grid">
                        <div class="interview-detail">
                            <i class="far fa-calendar-alt"></i>
                            <span>${interview.formatted_date}</span>
                        </div>
                        <div class="interview-detail">
                            <i class="far fa-clock"></i>
                            <span>${interview.formatted_time} (${interview.duration_formatted})</span>
                        </div>
                        <div class="interview-detail">
                            <i class="${getInterviewIcon(interview.interview_type)}"></i>
                            <span>${interview.meeting_info}</span>
                        </div>
                        ${interview.time_until ? `
                            <div class="interview-detail countdown">
                                <i class="fas fa-hourglass-half"></i>
                                <span class="time-until">In ${interview.time_until}</span>
                            </div>
                        ` : ''}
                    </div>
                    
                    ${interview.has_accommodations ? `
                        <div class="accommodations-info">
                            <i class="fas fa-universal-access"></i>
                            <span>Accommodations: ${interview.accommodations.join(', ')}</span>
                        </div>
                    ` : ''}
                    
                    <div class="interview-actions">
                        ${interview.meeting_link ? `
                            <button class="btn btn-primary" onclick="openMeetingLink('${interview.meeting_link}')">
                                <i class="fas fa-video"></i> Join Meeting
                            </button>
                        ` : ''}
                        <button class="btn btn-secondary" onclick="viewInterviewDetails(${interview.id})">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = interviewsHTML;
    
    // Add countdown timers for interviews today
    interviews.forEach(interview => {
        if (interview.time_until && interview.time_until.includes('hour')) {
            startCountdownTimer(interview.id, interview.scheduled_date, interview.scheduled_time);
        }
    });
}

function showNoInterviewsMessage() {
    const container = document.getElementById('upcoming-interviews-container');
    container.innerHTML = `
        <div class="no-interviews">
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Upcoming Interviews</h3>
                <p>You don't have any scheduled interviews at the moment.</p>
                <a href="applications.php" class="btn btn-primary">Check Applications</a>
            </div>
        </div>
    `;
}

function showErrorMessage() {
    const container = document.getElementById('upcoming-interviews-container');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Unable to load interviews. Please try again later.</p>
            <button onclick="loadUpcomingInterviews()" class="btn btn-secondary">Retry</button>
        </div>
    `;
}

function getInterviewIcon(type) {
    const iconMap = {
        'online': 'fas fa-video',
        'in_person': 'fas fa-map-marker-alt',
        'phone': 'fas fa-phone'
    };
    return iconMap[type] || 'fas fa-calendar-alt';
}

function openMeetingLink(link) {
    if (link) {
        // Add protocol if missing
        const url = link.startsWith('http') ? link : 'https://' + link;
        window.open(url, '_blank');
    }
}

function viewInterviewDetails(interviewId) {
    // Navigate to applications page with interview filter
    window.location.href = `applications.php?interview=${interviewId}`;
}

function startCountdownTimer(interviewId, date, time) {
    const interviewDateTime = new Date(date + ' ' + time);
    const card = document.querySelector(`[data-interview-id="${interviewId}"]`);
    const timeUntilElement = card?.querySelector('.time-until');
    
    if (!timeUntilElement) return;
    
    const timer = setInterval(() => {
        const now = new Date();
        const timeDiff = interviewDateTime - now;
        
        if (timeDiff <= 0) {
            timeUntilElement.textContent = 'Interview starting now!';
            timeUntilElement.style.color = '#dc3545';
            timeUntilElement.style.fontWeight = 'bold';
            clearInterval(timer);
            return;
        }
        
        const hours = Math.floor(timeDiff / (1000 * 60 * 60));
        const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
        
        if (hours > 0) {
            timeUntilElement.textContent = `In ${hours}h ${minutes}m`;
        } else if (minutes > 0) {
            timeUntilElement.textContent = `In ${minutes} minutes`;
        } else {
            timeUntilElement.textContent = 'Starting soon!';
            timeUntilElement.style.color = '#ffc107';
        }
    }, 60000); // Update every minute
}
</script>

<style>
.loading-state, .no-interviews, .error-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.loading-state i {
    font-size: 24px;
    margin-bottom: 10px;
    color: #2F8A99;
}

.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 15px;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #333;
}

.interviews-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.interview-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.interview-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    border-color: #2F8A99;
    transform: translateY(-2px);
}

.interview-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.company-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.company-logo {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
    flex-shrink: 0;
}

.interview-title h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    color: #333;
    font-weight: 600;
}

.job-title {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.interview-type {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.interview-type.online {
    background: #e3f2fd;
    color: #1976d2;
}

.interview-type.in_person {
    background: #f3e5f5;
    color: #7b1fa2;
}

.interview-type.phone {
    background: #fff3e0;
    color: #f57c00;
}

.interview-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.interview-detail {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #555;
}

.interview-detail i {
    color: #2F8A99;
    width: 16px;
    text-align: center;
}

.interview-detail.countdown {
    color: #2F8A99;
    font-weight: 500;
}

.accommodations-info {
    background: #f8f9fa;
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #555;
}

.accommodations-info i {
    color: #28a745;
}

.interview-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #267A87, #1e6670);
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f8f9fa;
    color: #555;
    border: 1px solid #dee2e6;
}

.btn-secondary:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.error-state {
    color: #d32f2f;
}

/* Responsive */
@media (max-width: 768px) {
    .interview-card {
        padding: 16px;
    }
    
    .interview-header {
        flex-direction: column;
        gap: 12px;
    }
    
    .interview-details-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .interview-actions {
        justify-content: stretch;
    }
    
    .btn {
        flex: 1;
        justify-content: center;
    }
    
    .company-logo {
        width: 40px;
        height: 40px;
        font-size: 14px;
    }
}

/* Animation for upcoming interviews */
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(47, 138, 153, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(47, 138, 153, 0); }
    100% { box-shadow: 0 0 0 0 rgba(47, 138, 153, 0); }
}

.interview-card:has(.time-until) {
    animation: pulse 2s infinite;
}
</style>