<!-- Interview Scheduling Modal -->
<div class="modal" id="scheduleInterviewModal">
    <div class="modal-content interview-modal">
        <div class="modal-header">
            <h2><i class="fas fa-calendar-alt"></i> Schedule Interview</h2>
            <span class="close-modal" data-modal="scheduleInterviewModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <!-- Applicant Info Header -->
            <div class="interview-applicant-info">
                <div class="applicant-avatar-small" id="interview-avatar">JD</div>
                <div class="applicant-details">
                    <h4 id="interview-applicant-name">John Doe</h4>
                    <p id="interview-job-title">Senior Web Developer</p>
                    <div class="applicant-contact">
                        <span id="interview-applicant-email">john@example.com</span>
                        <span id="interview-applicant-phone">(555) 123-4567</span>
                    </div>
                </div>
                <div class="disability-info" id="interview-disability-info">
                    <i class="fas fa-accessibility"></i>
                    <span id="interview-disability-type">Visual Impairment</span>
                </div>
            </div>

            <!-- Interview Form -->
            <form id="scheduleInterviewForm" class="interview-form">
                <input type="hidden" id="interview-application-id" name="application_id">
                
                <!-- Basic Interview Details -->
                <div class="form-section">
                    <h5><i class="fas fa-calendar"></i> Interview Details</h5>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="interview-type">Interview Type *</label>
                            <select id="interview-type" name="interview_type" required>
                                <option value="online">Online Interview</option>
                                <option value="in_person">In-Person Interview</option>
                                <option value="phone">Phone Interview</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="interview-duration">Duration (minutes)</label>
                            <select id="interview-duration" name="duration_minutes">
                                <option value="30">30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="interview-date">Interview Date *</label>
                            <input type="date" id="interview-date" name="scheduled_date" required min="">
                        </div>
                        
                        <div class="form-group">
                            <label for="interview-time">Interview Time *</label>
                            <input type="time" id="interview-time" name="scheduled_time" required>
                        </div>
                    </div>
                </div>

                <!-- Platform/Location Details -->
                <div class="form-section">
                    <h5 id="platform-section-title"><i class="fas fa-video"></i> Platform Details</h5>
                    
                    <!-- Online Interview Fields -->
                    <div id="online-fields" class="platform-fields">
                        <div class="form-group">
                            <label for="interview-platform">Platform</label>
                            <select id="interview-platform" name="interview_platform">
                                <option value="zoom">Zoom</option>
                                <option value="google-meet">Google Meet</option>
                                <option value="microsoft-teams">Microsoft Teams</option>
                                <option value="skype">Skype</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="meeting-link">Meeting Link</label>
                            <input type="url" id="meeting-link" name="meeting_link" 
                                   placeholder="https://zoom.us/j/1234567890">
                            <small class="form-help">The meeting link will be sent to the candidate</small>
                        </div>
                    </div>
                    
                    <!-- In-Person Interview Fields -->
                    <div id="inperson-fields" class="platform-fields" style="display: none;">
                        <div class="form-group">
                            <label for="interview-location">Interview Location *</label>
                            <textarea id="interview-location" name="location_address" rows="3"
                                      placeholder="Enter the full address where the interview will take place"></textarea>
                        </div>
                    </div>
                    
                    <!-- Phone Interview Fields -->
                    <div id="phone-fields" class="platform-fields" style="display: none;">
                        <div class="form-group">
                            <label for="phone-instructions">Phone Instructions</label>
                            <textarea id="phone-instructions" name="phone_instructions" rows="2"
                                      placeholder="Phone number and any special instructions for the candidate"></textarea>
                        </div>
                    </div>
                </div>

                <!-- PWD Accommodations Section -->
                <div class="form-section accommodations-section">
                    <h5><i class="fas fa-accessibility"></i> Accessibility Accommodations</h5>
                    <p class="section-description">
                        Ensure an inclusive interview experience by selecting appropriate accommodations.
                    </p>
                    
                    <div class="accommodation-options">
                        <div class="accommodation-item">
                            <input type="checkbox" id="sign-language" name="sign_language_interpreter">
                            <label for="sign-language">
                                <i class="fas fa-hands"></i>
                                Sign Language Interpreter
                                <small>Professional interpreter will be provided during the interview</small>
                            </label>
                        </div>
                        
                        <div class="accommodation-item">
                            <input type="checkbox" id="wheelchair-accessible" name="wheelchair_accessible_venue">
                            <label for="wheelchair-accessible">
                                <i class="fas fa-wheelchair"></i>
                                Wheelchair Accessible Venue
                                <small>Ensure the interview location is fully accessible</small>
                            </label>
                        </div>
                        
                        <div class="accommodation-item">
                            <input type="checkbox" id="screen-reader" name="screen_reader_materials">
                            <label for="screen-reader">
                                <i class="fas fa-desktop"></i>
                                Screen Reader Compatible Materials
                                <small>All documents and presentations will be screen reader friendly</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="additional-accommodations">Additional Accommodations</label>
                        <textarea id="additional-accommodations" name="accommodations_needed" rows="3"
                                  placeholder="Describe any other specific accommodations needed for this candidate"></textarea>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="form-section">
                    <h5><i class="fas fa-sticky-note"></i> Interview Notes</h5>
                    
                    <div class="form-group">
                        <label for="interviewer-notes">Preparation Notes (Internal)</label>
                        <textarea id="interviewer-notes" name="interviewer_notes" rows="3"
                                  placeholder="Internal notes for interview preparation (not visible to candidate)"></textarea>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="footer-btn secondary-btn close-modal" data-modal="scheduleInterviewModal">
                Cancel
            </button>
            <button type="button" class="footer-btn primary-btn" id="schedule-interview-btn">
                <i class="fas fa-calendar-plus"></i>
                Schedule Interview
            </button>
        </div>
    </div>
</div>

<!-- Interview Success Modal -->
<div class="modal" id="interviewSuccessModal">
    <div class="modal-content success-modal">
        <div class="modal-header success-header">
            <h2><i class="fas fa-check-circle"></i> Interview Scheduled Successfully</h2>
            <span class="close-modal" data-modal="interviewSuccessModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                
                <div class="success-details">
                    <h4 id="success-applicant-name">John Doe</h4>
                    <p id="success-job-title">Senior Web Developer Interview</p>
                    
                    <div class="interview-summary">
                        <div class="summary-item">
                            <i class="fas fa-calendar"></i>
                            <span id="success-datetime">March 15, 2025 at 2:00 PM</span>
                        </div>
                        
                        <div class="summary-item">
                            <i class="fas fa-clock"></i>
                            <span id="success-duration">60 minutes</span>
                        </div>
                        
                        <div class="summary-item">
                            <i class="fas fa-video"></i>
                            <span id="success-platform">Online via Zoom</span>
                        </div>
                        
                        <div class="summary-item" id="success-accommodations" style="display: none;">
                            <i class="fas fa-accessibility"></i>
                            <span id="success-accommodations-list">Accommodations provided</span>
                        </div>
                    </div>
                    
                    <div class="next-steps">
                        <h5>What happens next:</h5>
                        <ul>
                            <li>The candidate has been automatically notified</li>
                            <li>Calendar invite details have been sent</li>
                            <li>You can manage this interview from your dashboard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="footer-btn secondary-btn close-modal" data-modal="interviewSuccessModal">
                Close
            </button>
            <button type="button" class="footer-btn primary-btn" id="view-interviews-btn">
                <i class="fas fa-calendar"></i>
                View All Interviews
            </button>
        </div>
    </div>
</div>