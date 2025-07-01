<div class="dashboard-right">
    <!-- Upcoming Interviews -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-calendar-alt"></i> Upcoming Interviews
            </div>
            <div class="card-actions">
                <button id="interview-filter-toggle">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" id="interview-filter-dropdown">
                    <a href="#" data-filter="all">All Interviews</a>
                    <a href="#" data-filter="online">Online</a>
                    <a href="#" data-filter="in-person">In-Person</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <ul class="interview-list" id="upcoming-interviews-list">
                <li class="no-data">Loading interviews...</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="empapplicants.php" class="view-all">View All Interviews</a>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-bell"></i> Recent Notifications
            </div>
            <div class="card-actions">
                <button id="notifications-filter-toggle">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu" id="notifications-filter-dropdown">
                    <a href="#" data-filter="all">All Notifications</a>
                    <a href="#" data-filter="unread">Unread</a>
                    <a href="#" data-filter="applicants">Applicants</a>
                    <a href="#" data-filter="interviews">Interviews</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <ul class="notification-list" id="recent-notifications-list">
                <li class="no-data">Loading notifications...</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="empnotifications.php" class="view-all">View All Notifications</a>
        </div>
    </div>
</div>