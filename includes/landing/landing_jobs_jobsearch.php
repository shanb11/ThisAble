<section class="job-search-section">
    <div class="search-filters">
        <div class="filter">
            <label for="keyword">Keywords</label>
            <input type="text" id="keyword" placeholder="Job title, skills, or company">
        </div>
        <div class="filter">
            <label for="location">Location</label>
            <input type="text" id="location" placeholder="City, state, or 'Remote'">
        </div>
        <div class="filter">
            <label for="category">Category</label>
            <select id="category">
                <option value="">All Categories</option>
                <option value="education">Education & Training</option>
                <option value="office">Office Administration</option>
                <option value="customer">Customer Service</option>
                <option value="business">Business Administration</option>
                <option value="healthcare">Healthcare & Wellness</option>
                <option value="finance">Finance & Accounting</option>
            </select>
        </div>
        <div class="filter">
            <label for="type">Job Type</label>
            <select id="type">
                <option value="">All Types</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Internship">Internship</option>
            </select>
        </div>
    </div>
    <div class="search-actions">
        <button id="search-jobs-btn" class="btn btn-secondary">Search Jobs</button>
        <div class="search-count"><span id="jobs-count">18</span> jobs found</div>
    </div>
</section>