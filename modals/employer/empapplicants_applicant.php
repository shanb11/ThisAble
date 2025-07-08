<!-- Enhanced Applicant Profile Modal -->
<div class="modal" id="applicantModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Applicant Profile</h2>
            <span class="close-modal">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="profile-header">
                <div class="profile-avatar" id="profile-avatar">JD</div>
                <div class="profile-info">
                    <h3 id="profile-name">John Doe</h3>
                    <p id="profile-title">Full Stack Developer</p>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span id="profile-email">johndoe@example.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span id="profile-phone">(555) 123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="profile-location">San Francisco, CA</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-sections">
                <!-- About Section -->
                <div class="profile-section">
                    <h4><i class="fas fa-user"></i> About</h4>
                    <p id="about-text">Experienced developer with expertise in web technologies...</p>
                </div>

                <!-- Skills Section -->
                <div class="profile-section">
                    <h4><i class="fas fa-code"></i> Skills</h4>
                    <div class="skills-list" id="skills-list">
                        <!-- Skills will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Work Preferences -->
                <div class="profile-section">
                    <h4><i class="fas fa-briefcase"></i> Work Preferences</h4>
                    <div class="preferences-grid">
                        <div class="preference-item">
                            <label>Work Setup:</label>
                            <span id="work-setup">Remote</span>
                        </div>
                        <div class="preference-item">
                            <label>Job Type:</label>
                            <span id="job-type">Full-time</span>
                        </div>
                    </div>
                </div>

                <!-- Workplace Accommodations -->
                <div class="profile-section" id="accommodations-section" style="display: none;">
                    <h4><i class="fas fa-accessibility"></i> Workplace Accommodations</h4>
                    <div id="accommodation-list">
                        <!-- Accommodations will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Resume Section -->
                <div class="profile-section" id="resume-section">
                    <h4><i class="fas fa-file-alt"></i> Resume</h4>
                    <div id="resume-actions">
                        <!-- Resume actions will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Application Details -->
                <div class="profile-section">
                    <h4><i class="fas fa-clipboard-list"></i> Application Details</h4>
                    <div class="application-details">
                        <div class="detail-row">
                            <label>Date Applied:</label>
                            <span id="date-applied">Apr 7, 2025</span>
                        </div>
                        <div class="detail-row">
                            <label>Position:</label>
                            <span id="job-applied">Senior Web Developer</span>
                        </div>
                        <div class="detail-row">
                            <label>Current Status:</label>
                            <span class="status-pill" id="status-pill">New</span>
                        </div>
                    </div>
                </div>

                <!-- Education Section (Optional - will be populated if data exists) -->
                <div class="profile-section" id="education-section" style="display: none;">
                    <h4><i class="fas fa-graduation-cap"></i> Education</h4>
                    <div id="education-list">
                        <!-- Education will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Experience Section (Optional - will be populated if data exists) -->
                <div class="profile-section" id="experience-section" style="display: none;">
                    <h4><i class="fas fa-briefcase"></i> Work Experience</h4>
                    <div id="experience-list">
                        <!-- Experience will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
    <button class="footer-btn secondary-btn close-modal">Close</button>
    <button class="footer-btn primary-btn" data-status="reviewed" data-application-id="<?php echo $application_id; ?>">Mark as Reviewed</button>
    <button class="footer-btn interview-btn" data-status="interview" data-application-id="<?php echo $application_id; ?>">Schedule Interview</button>
    <button class="footer-btn success-btn" data-status="hired" data-application-id="<?php echo $application_id; ?>">Hire</button>
    <button class="footer-btn danger-btn" data-status="rejected" data-application-id="<?php echo $application_id; ?>">Reject</button>
</div>
    </div>
</div>