<div class="dashboard-section">
    <div class="section-header">
        <h2 class="section-title">Recent Applications</h2>
        <a href="applications.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
    </div>
    
    <div id="recent-applications-container">
        <!-- Loading state -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading your recent applications...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecentApplications();
});

function loadRecentApplications() {
    fetch('../../backend/candidate/get_applications.php?limit=5')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRecentApplications(data.applications);
            } else {
                showNoApplicationsMessage();
            }
        })
        .catch(error => {
            console.error('Error fetching applications:', error);
            showErrorMessage();
        });
}

function displayRecentApplications(applications) {
    const container = document.getElementById('recent-applications-container');
    
    if (applications.length === 0) {
        showNoApplicationsMessage();
        return;
    }
    
    const tableHTML = `
        <table class="applications-table">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company Name</th>
                    <th>Status</th>
                    <th>Date Applied</th>
                </tr>
            </thead>
            <tbody>
                ${applications.map(app => `
                    <tr class="application-row" data-application-id="${app.id}">
                        <td class="job-title">${app.jobTitle}</td>
                        <td class="company-name">
                            <div class="company-info">
                                <div class="company-logo">${app.logo}</div>
                                <span>${app.company}</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-${app.status}">
                                ${formatStatusDisplay(app.status)}
                            </span>
                        </td>
                        <td class="date-applied">${app.dateApplied}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
    
    container.innerHTML = tableHTML;
    
    // Add click handlers for application rows
    container.querySelectorAll('.application-row').forEach(row => {
        row.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            window.location.href = `applications.php?id=${applicationId}`;
        });
    });
}

function showNoApplicationsMessage() {
    const container = document.getElementById('recent-applications-container');
    container.innerHTML = `
        <div class="no-applications">
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Applications Yet</h3>
                <p>Start applying to jobs to see your applications here.</p>
                <a href="joblistings.php" class="btn btn-primary">Browse Jobs</a>
            </div>
        </div>
    `;
}

function showErrorMessage() {
    const container = document.getElementById('recent-applications-container');
    container.innerHTML = `
        <div class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Unable to load applications. Please try again later.</p>
            <button onclick="loadRecentApplications()" class="btn btn-secondary">Retry</button>
        </div>
    `;
}

function formatStatusDisplay(status) {
    const statusMap = {
        'applied': 'Applied',
        'reviewed': 'Under Review',
        'interview': 'Interview',
        'offered': 'Job Offer',
        'rejected': 'Rejected'
    };
    
    return statusMap[status] || status;
}
</script>

<style>
.loading-state, .no-applications, .error-state {
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

.company-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.company-logo {
    width: 30px;
    height: 30px;
    background: #2F8A99;
    color: white;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.application-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.application-row:hover {
    background-color: #f8f9fa;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-applied { background: #e3f2fd; color: #1976d2; }
.status-reviewed { background: #fff3e0; color: #f57c00; }
.status-interview { background: #f3e5f5; color: #7b1fa2; }
.status-offered { background: #e8f5e8; color: #388e3c; }
.status-rejected { background: #ffebee; color: #d32f2f; }

.error-state {
    color: #d32f2f;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #2F8A99;
    color: white;
}

.btn-primary:hover {
    background: #267A87;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}
</style>