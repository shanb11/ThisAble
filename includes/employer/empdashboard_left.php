<div class="dashboard-left">
    <!-- Recent Job Posts -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-briefcase"></i> Recent Job Posts
            </div>
            <div class="card-actions">
                <button id="job-filter-toggle">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" id="job-filter-dropdown">
                    <a href="#" data-filter="all">All Jobs</a>
                    <a href="#" data-filter="active">Active</a>
                    <a href="#" data-filter="pending">Pending</a>
                    <a href="#" data-filter="closed">Closed</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <ul class="job-list" id="recent-jobs-list">
                <li class="no-data">Loading job posts...</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="empjoblist.php" class="view-all">View All Job Posts</a>
        </div>
    </div>

    <!-- Recent Applicants -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-users"></i> Recent Applicants
            </div>
            <div class="card-actions">
                <button id="applicant-filter-toggle">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" id="applicant-filter-dropdown">
                    <a href="#" data-filter="all">All Applicants</a>
                    <a href="#" data-filter="pwd">PWD Applicants</a>
                    <a href="#" data-filter="recent">Applied Today</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <ul class="applicant-list" id="recent-applicants-list">
                <li class="no-data">Loading applicants...</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="empapplicants.php" class="view-all">View All Applicants</a>
        </div>
    </div>
</div>