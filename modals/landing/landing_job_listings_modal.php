<div id="jobs-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modal-title">Jobs</h2>
        <div class="job-filters">
            <select id="filter-location">
                <option value="">All Locations</option>
                <option value="Remote">Remote</option>
                <option value="New York">New York</option>
                <option value="San Francisco">San Francisco</option>
                <option value="Chicago">Chicago</option>
                <option value="Miami">Miami</option>
            </select>
            <select id="filter-type">
                <option value="">All Types</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
        </div>
        <div id="jobs-container" class="jobs-list">
            <!-- Jobs will be loaded here -->
        </div>
    </div>
</div>