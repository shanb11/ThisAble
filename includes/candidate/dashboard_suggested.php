<div class="dashboard-section">
    <div class="section-header">
        <h2 class="section-title">Suggested Jobs</h2>
        <a href="joblistings.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
    </div>
    
    <div id="suggested-jobs-container">
        <!-- Loading state -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Finding jobs that match your profile...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSuggestedJobs();
});

function loadSuggestedJobs() {
    fetch('../../backend/candidate/get_job_recommendations.php?limit=6')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySuggestedJobs(data.recommendations);
            } else {
                showNoJobsMessage();
            }
        })
        .catch(error => {
            console.error('Error fetching job recommendations:', error);
            showErrorMessage();
        });
}

function displaySuggestedJobs(jobs) {
    const container = document.getElementById('suggested-jobs-container');
    
    if (jobs.length === 0) {
        showNoJobsMessage();
        return;
    }
    
    const jobsHTML = `
        <div class="suggested-jobs">
            ${jobs.map(job => `
                <div class="job-card" data-job-id="${job.job_id}">
                    <div class="job-header">
                        <div class="company-logo">${job.company_logo}</div>
                        <div class="match-badge" data-match="${job.match_percentage}">
                            ${job.match_percentage}% match
                        </div>
                    </div>
                    
                    <div class="job-content">
                        <h3 class="job-title">${job.job_title}</h3>
                        <p class="company-name">${job.company_name}</p>
                        <p class="job-location">
                            <i class="fas fa-map-marker-alt"></i>
                            ${job.location}
                            ${job.remote_work_available ? '<span class="remote-badge">Remote Available</span>' : ''}
                        </p>
                        
                        ${job.salary_range ? `
                            <p class="job-salary">
                                <i class="fas fa-money-bill-wave"></i>
                                ${job.salary_range}
                            </p>
                        ` : ''}
                        
                        <div class="job-type">
                            <span class="type-badge ${job.employment_type.toLowerCase().replace('-', '')}">${job.employment_type}</span>
                            ${job.flexible_schedule ? '<span class="flex-badge">Flexible</span>' : ''}
                        </div>
                        
                        ${job.reasons.length > 0 ? `
                            <div class="match-reasons">
                                <div class="reasons-list">
                                    ${job.reasons.slice(0, 2).map(reason => `
                                        <span class="reason-tag">
                                            <i class="fas fa-check"></i>
                                            ${reason}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                        
                        ${job.matching_skills.length > 0 ? `
                            <div class="matching-skills">
                                <small>Matching skills:</small>
                                <div class="skills-list">
                                    ${job.matching_skills.slice(0, 3).map(skill => `
                                        <span class="skill-tag">${skill}</span>
                                    `).join('')}
                                    ${job.matching_skills.length > 3 ? `<span class="more-skills">+${job.matching_skills.length - 3} more</span>` : ''}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="job-footer">
                        <div class="job-meta">
                            <span class="posted-time">
                                <i class="fas fa-clock"></i>
                                ${job.posted_ago}
                            </span>
                            ${job.applications_count > 0 ? `
                                <span class="applications-count">
                                    <i class="fas fa-users"></i>
                                    ${job.applications_count} applied
                                </span>
                            ` : ''}
                        </div>
                        <button class="apply-now-btn" onclick="applyToJob(${job.job_id}, '${job.job_title}', '${job.company_name}')">
                            <i class="fas fa-paper-plane"></i>
                            Apply Now
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = jobsHTML;
    
    // Add click handlers for job cards
    container.querySelectorAll('.job-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on apply button
            if (e.target.closest('.apply-now-btn')) return;
            
            const jobId = this.dataset.jobId;
            viewJobDetails(jobId);
        });
    });
    
    // Set match badge colors
    container.querySelectorAll('.match-badge').forEach(badge => {
        const match = parseInt(badge.dataset.match);
        if (match >= 80) {
            badge.classList.add('high-match');
        } else if (match >= 60) {
            badge.classList.add('medium-match');
        } else {
            badge.classList.add('low-match');
        }
    });
}

function showNoJobsMessage() {
    const container = document.getElementById('suggested-jobs-container');
    container.innerHTML = `
        <div class="no-jobs">
            <div class="empty-state">
                <i class="fas fa-briefcase"></i>
                <h3>No Job Recommendations</h3>
                <p>Complete your profile to get personalized job recommendations.</p>
                <div class="empty-actions">
                    <a href="profile.php" class="btn btn-primary">Complete Profile</a>
                    <a href="joblistings.php" class="btn btn-secondary">Browse All Jobs</a>
                </div>
            </div>
        </div>
    `;
}

function showErrorMessage() {
    const container = document.getElementById('suggested-jobs-container');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Unable to load job recommendations. Please try again later.</p>
            <button onclick="loadSuggestedJobs()" class="btn btn-secondary">Retry</button>
        </div>
    `;
}

function applyToJob(jobId, jobTitle, companyName) {
    // Show confirmation or redirect to application
    if (confirm(`Apply for ${jobTitle} at ${companyName}?`)) {
        window.location.href = `joblistings.php?apply=${jobId}`;
    }
}

function viewJobDetails(jobId) {
    window.location.href = `joblistings.php?job=${jobId}`;
}

// Refresh recommendations periodically
function refreshRecommendations() {
    const container = document.getElementById('suggested-jobs-container');
    const currentJobs = container.querySelectorAll('.job-card');
    
    // Add subtle loading indicator
    currentJobs.forEach(card => {
        card.style.opacity = '0.7';
    });
    
    loadSuggestedJobs();
}

// Auto-refresh every 30 minutes
setInterval(refreshRecommendations, 1800000);
</script>

<style>
.loading-state, .no-jobs, .error-state {
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

.empty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 20px;
}

.suggested-jobs {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

.job-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.job-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #2F8A99;
    transform: translateY(-5px);
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.company-logo {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    flex-shrink: 0;
}

.match-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.match-badge.high-match {
    background: #e8f5e8;
    color: #2e7d32;
}

.match-badge.medium-match {
    background: #fff3e0;
    color: #ef6c00;
}

.match-badge.low-match {
    background: #f3e5f5;
    color: #7b1fa2;
}

.job-content {
    margin-bottom: 20px;
}

.job-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.company-name {
    font-size: 15px;
    color: #666;
    margin: 0 0 12px 0;
    font-weight: 500;
}

.job-location, .job-salary {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #555;
    margin: 8px 0;
}

.job-location i, .job-salary i {
    color: #2F8A99;
    width: 14px;
}

.remote-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 8px;
}

.job-type {
    display: flex;
    gap: 8px;
    margin: 12px 0;
}

.type-badge {
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.type-badge.fulltime {
    background: #e8f5e8;
    color: #2e7d32;
}

.type-badge.parttime {
    background: #fff3e0;
    color: #ef6c00;
}

.type-badge.contract {
    background: #f3e5f5;
    color: #7b1fa2;
}

.type-badge.internship {
    background: #e3f2fd;
    color: #1976d2;
}

.flex-badge {
    background: #f0f8ff;
    color: #2F8A99;
    padding: 4px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
}

.match-reasons {
    margin: 12px 0;
}

.reasons-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.reason-tag {
    background: #f8f9fa;
    color: #495057;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.reason-tag i {
    color: #28a745;
    font-size: 10px;
}

.matching-skills {
    margin: 12px 0;
}

.matching-skills small {
    color: #666;
    font-size: 12px;
    display: block;
    margin-bottom: 6px;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.skill-tag {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
}

.more-skills {
    color: #666;
    font-size: 11px;
    font-style: italic;
}

.job-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

.job-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.posted-time, .applications-count {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 4px;
}

.posted-time i, .applications-count i {
    color: #999;
}

.apply-now-btn {
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.apply-now-btn:hover {
    background: linear-gradient(135deg, #267A87, #1e6670);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(47, 138, 153, 0.3);
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #267A87, #1e6670);
}

.btn-secondary {
    background: #f8f9fa;
    color: #555;
    border: 1px solid #dee2e6;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.error-state {
    color: #d32f2f;
}

/* Responsive */
@media (max-width: 768px) {
    .suggested-jobs {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .job-card {
        padding: 16px;
    }
    
    .job-footer {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .apply-now-btn {
        width: 100%;
        justify-content: center;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .reasons-list {
        justify-content: center;
    }
}

/* Animation for new job cards */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.job-card {
    animation: slideInUp 0.5s ease-out;
}

/* Hover effects */
.job-card:hover .apply-now-btn {
    background: linear-gradient(135deg, #267A87, #1e6670);
}

.job-card:hover .company-logo {
    transform: scale(1.05);
}
</style>