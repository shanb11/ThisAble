<div id="post-job-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Post a Job</h2>
        <form id="job-post-form">
            <div class="form-group">
                <label for="job-title">Job Title</label>
                <input type="text" id="job-title" required>
            </div>
            <div class="form-group">
                <label for="company-name">Company Name</label>
                <input type="text" id="company-name" required>
            </div>
            <div class="form-group">
                <label for="job-location">Location</label>
                <input type="text" id="job-location" required>
            </div>
            <div class="form-group">
                <label for="job-type">Job Type</label>
                <select id="job-type" required>
                    <option value="">Select Job Type</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Contract">Contract</option>
                    <option value="Internship">Internship</option>
                </select>
            </div>
            <div class="form-group">
                <label for="job-category">Category</label>
                <select id="job-category" required>
                    <option value="">Select Category</option>
                    <option value="education">Education & Training</option>
                    <option value="office">Office Administration</option>
                    <option value="customer">Customer Service</option>
                    <option value="business">Business Administration</option>
                    <option value="healthcare">Healthcare & Wellness</option>
                    <option value="finance">Finance & Accounting</option>
                </select>
            </div>
            <div class="form-group">
                <label for="job-description">Job Description</label>
                <textarea id="job-description" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Job</button>
        </form>
    </div>
</div>