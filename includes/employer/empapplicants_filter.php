<div class="filter-container">
    <div class="filter-options">
        <div class="filter-group">
            <label for="job-filter">Job Position</label>
            <select id="job-filter">
                <option value="">All Positions</option>
                <option value="senior-web-developer">Senior Web Developer</option>
                <option value="ui-ux-designer">UI/UX Designer</option>
                <option value="marketing-specialist">Marketing Specialist</option>
                <option value="content-writer">Content Writer</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="status-filter">Application Status</label>
            <select id="status-filter">
                <option value="">All Statuses</option>
                <option value="new">New</option>
                <option value="reviewed">Reviewed</option>
                <option value="interview">Interview Scheduled</option>
                <option value="hired">Hired</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="date-filter">Date Applied</label>
            <select id="date-filter">
                <option value="">Any Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="skills-filter">Skills</label>
            <input type="text" id="skills-filter" placeholder="e.g. JavaScript, Python, Design">
        </div>
    </div>
    <div class="filter-actions">
        <button class="filter-btn reset-filter">Reset Filters</button>
        <button class="filter-btn apply-filter">Apply Filters</button>
    </div>
</div>