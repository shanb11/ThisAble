<div class="modal-overlay" id="edit-job-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Job Post</h3>
            <button class="modal-close" id="close-edit-job">&times;</button>
        </div>
        <div class="modal-body">
            <form id="edit-job-form">
                <input type="hidden" id="edit-job-id" name="job_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit-job-title" class="form-label">Job Title*</label>
                        <input type="text" id="edit-job-title" class="form-control" required placeholder="e.g., Web Developer">
                    </div>
                    <div class="form-group">
                        <label for="edit-job-department" class="form-label">Department*</label>
                        <select id="edit-job-department" class="form-control" required>
                            <option value="">Select department</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Design">Design</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Sales">Sales</option>
                            <option value="Finance">Finance</option>
                            <option value="HR">Human Resources</option>
                            <option value="Customer Support">Customer Support</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-job-location" class="form-label">Location*</label>
                        <input type="text" id="edit-job-location" class="form-control" required placeholder="e.g., Manila, Remote">
                    </div>
                    <div class="form-group">
                        <label for="edit-job-type" class="form-label">Employment Type*</label>
                        <select id="edit-job-type" class="form-control" required>
                            <option value="">Select type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-job-status" class="form-label">Job Status*</label>
                        <select id="edit-job-status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-job-salary" class="form-label">Salary Range</label>
                        <input type="text" id="edit-job-salary" class="form-control" placeholder="e.g., ₱40,000 - ₱60,000">
                    </div>
                    <div class="form-group form-fullwidth">
                        <label for="edit-job-description" class="form-label">Job Description*</label>
                        <textarea id="edit-job-description" class="form-control" required placeholder="Job description"></textarea>
                    </div>
                    <div class="form-group form-fullwidth">
                        <label for="edit-job-requirements" class="form-label">Requirements*</label>
                        <textarea id="edit-job-requirements" class="form-control" required placeholder="Job requirements"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-job-deadline" class="form-label">Application Deadline</label>
                        <input type="date" id="edit-job-deadline" class="form-control">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-edit-job">Cancel</button>
            <button class="btn btn-primary" id="update-job">Update Job</button>
        </div>
    </div>
</div>