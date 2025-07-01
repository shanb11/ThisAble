<div class="modal-overlay" id="post-job-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Post a New Job</h3>
            <button class="modal-close" id="close-post-job">&times;</button>
        </div>
        <div class="modal-body">
            <form id="post-job-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="job-title" class="form-label">Job Title*</label>
                        <input type="text" id="job-title" class="form-control" required placeholder="e.g., Web Developer, Graphic Designer">
                    </div>
                    <div class="form-group">
                        <label for="job-department" class="form-label">Department*</label>
                        <select id="job-department" class="form-control" required>
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
                        <label for="job-location" class="form-label">Location*</label>
                        <input type="text" id="job-location" class="form-control" required placeholder="e.g., Manila, Remote">
                    </div>
                    <div class="form-group">
                        <label for="job-type" class="form-label">Employment Type*</label>
                        <select id="job-type" class="form-control" required>
                            <option value="">Select type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                    <div class="form-group form-fullwidth">
                        <label for="job-description" class="form-label">Job Description*</label>
                        <textarea id="job-description" class="form-control" required placeholder="Provide a detailed description of the job role, responsibilities, and expectations."></textarea>
                    </div>
                    <div class="form-group form-fullwidth">
                        <label for="job-requirements" class="form-label">Requirements*</label>
                        <textarea id="job-requirements" class="form-control" required placeholder="List the required skills, qualifications, and experience."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="job-salary" class="form-label">Salary Range</label>
                        <input type="text" id="job-salary" class="form-control" placeholder="e.g., ₱40,000 - ₱60,000">
                    </div>
                    <div class="form-group">
                        <label for="job-deadline" class="form-label">Application Deadline</label>
                        <input type="date" id="job-deadline" class="form-control">
                    </div>
                    <div class="form-group form-fullwidth">
                        <label class="form-label">Accessibility Options</label>
                        <div class="form-hint">Select the accommodations available for this position</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                            <div class="form-check">
                                <input type="checkbox" id="wheelchair-access" class="form-check-input">
                                <label for="wheelchair-access">Wheelchair accessible workplace</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="flexible-schedule" class="form-check-input">
                                <label for="flexible-schedule">Flexible work schedule</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="assistive-tech" class="form-check-input">
                                <label for="assistive-tech">Assistive technology available</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="remote-work" class="form-check-input">
                                <label for="remote-work">Remote work option</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="screen-reader" class="form-check-input">
                                <label for="screen-reader">Screen reader compatibility</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="sign-language" class="form-check-input">
                                <label for="sign-language">Sign language interpreter</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-fullwidth">
                        <label for="additional-accommodations" class="form-label">Additional Accommodations</label>
                        <textarea id="additional-accommodations" class="form-control" placeholder="Describe any other accommodations or accessibility features available for this position."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancel-post-job">Cancel</button>
            <button class="btn btn-primary" id="submit-post-job">Post Job</button>
        </div>
    </div>
</div>