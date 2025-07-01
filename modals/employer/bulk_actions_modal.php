<!-- Bulk Actions Modal -->
<div class="modal" id="bulkActionsModal">
    <div class="modal-content bulk-modal">
        <div class="modal-header">
            <h2><i class="fas fa-tasks"></i> Bulk Actions</h2>
            <span class="close-modal" data-modal="bulkActionsModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <!-- Selected Applicants Summary -->
            <div class="selected-summary">
                <div class="summary-info">
                    <i class="fas fa-users"></i>
                    <span class="selected-count" id="bulk-selected-count">0</span>
                    <span>applicants selected</span>
                </div>
                <button type="button" class="clear-selection-btn" id="clear-selection-btn">
                    <i class="fas fa-times"></i> Clear Selection
                </button>
            </div>

            <!-- Selected Applicants List -->
            <div class="selected-applicants-list" id="selected-applicants-list">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Bulk Action Options -->
            <div class="bulk-actions-container">
                
                <!-- Status Update Action -->
                <div class="bulk-action-card" data-action="update_status">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="action-info">
                            <h4>Update Status</h4>
                            <p>Change the application status for all selected applicants</p>
                        </div>
                        <div class="action-toggle">
                            <input type="radio" name="bulk_action" id="action_status" value="update_status">
                            <label for="action_status"></label>
                        </div>
                    </div>
                    
                    <div class="action-details" id="status_details" style="display: none;">
                        <div class="form-group">
                            <label for="bulk-status">New Status</label>
                            <select id="bulk-status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="reviewed">Mark as Reviewed</option>
                                <option value="interview">Schedule for Interview</option>
                                <option value="hired">Mark as Hired</option>
                                <option value="rejected">Mark as Rejected</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="bulk-status-notes">Notes (Optional)</label>
                            <textarea id="bulk-status-notes" class="form-control" rows="3" 
                                      placeholder="Add notes for this status update..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="notify-status-change">
                                <label for="notify-status-change">Send notification to applicants about status change</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Send Notification Action -->
                <div class="bulk-action-card" data-action="send_notification">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="action-info">
                            <h4>Send Notification</h4>
                            <p>Send a custom message to all selected applicants</p>
                        </div>
                        <div class="action-toggle">
                            <input type="radio" name="bulk_action" id="action_notification" value="send_notification">
                            <label for="action_notification"></label>
                        </div>
                    </div>
                    
                    <div class="action-details" id="notification_details" style="display: none;">
                        <div class="form-group">
                            <label for="notification-title">Notification Title</label>
                            <input type="text" id="notification-title" class="form-control" 
                                   placeholder="Enter notification title..." maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="notification-message">Message</label>
                            <textarea id="notification-message" class="form-control" rows="4" 
                                      placeholder="Enter your message to the applicants..." maxlength="500"></textarea>
                            <small class="char-count">0/500 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="send-email-notification">
                                <label for="send-email-notification">Also send email notifications</label>
                                <small>Applicants will receive both in-app and email notifications</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Data Action -->
                <div class="bulk-action-card" data-action="export">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="action-info">
                            <h4>Export Data</h4>
                            <p>Download applicant information in various formats</p>
                        </div>
                        <div class="action-toggle">
                            <input type="radio" name="bulk_action" id="action_export" value="export">
                            <label for="action_export"></label>
                        </div>
                    </div>
                    
                    <div class="action-details" id="export_details" style="display: none;">
                        <div class="form-group">
                            <label for="export-format">Export Format</label>
                            <select id="export-format" class="form-control">
                                <option value="csv">CSV (Comma Separated Values)</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="pdf">PDF Report</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Include Fields</label>
                            <div class="checkbox-group">
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-name" value="name" checked>
                                    <label for="field-name">Full Name</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-email" value="email" checked>
                                    <label for="field-email">Email Address</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-phone" value="phone">
                                    <label for="field-phone">Phone Number</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-job" value="job_title" checked>
                                    <label for="field-job">Position Applied</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-status" value="status" checked>
                                    <label for="field-status">Application Status</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-date" value="applied_date" checked>
                                    <label for="field-date">Application Date</label>
                                </div>
                                <div class="checkbox-wrapper">
                                    <input type="checkbox" id="field-disability" value="disability">
                                    <label for="field-disability">Disability Information</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Archive Action -->
                <div class="bulk-action-card" data-action="archive">
                    <div class="action-header">
                        <div class="action-icon">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="action-info">
                            <h4>Archive Applications</h4>
                            <p>Move selected applications to archive (can be restored later)</p>
                        </div>
                        <div class="action-toggle">
                            <input type="radio" name="bulk_action" id="action_archive" value="archive">
                            <label for="action_archive"></label>
                        </div>
                    </div>
                    
                    <div class="action-details" id="archive_details" style="display: none;">
                        <div class="form-group">
                            <label for="archive-reason">Archive Reason</label>
                            <select id="archive-reason" class="form-control">
                                <option value="Position filled">Position filled</option>
                                <option value="Requirements not met">Requirements not met</option>
                                <option value="Duplicate applications">Duplicate applications</option>
                                <option value="Applicant withdrew">Applicant withdrew</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <strong>Important:</strong> Archived applications will be hidden from the main view 
                                but can be restored from the archive section if needed.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="footer-btn secondary-btn close-modal" data-modal="bulkActionsModal">
                Cancel
            </button>
            <button type="button" class="footer-btn primary-btn" id="execute-bulk-action" disabled>
                <i class="fas fa-play"></i>
                Execute Action
            </button>
        </div>
    </div>
</div>

<!-- Bulk Action Confirmation Modal -->
<div class="modal" id="bulkConfirmationModal">
    <div class="modal-content confirmation-modal">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Confirm Bulk Action</h2>
            <span class="close-modal" data-modal="bulkConfirmationModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                
                <div class="confirmation-details">
                    <h4 id="confirmation-title">Confirm Action</h4>
                    <p id="confirmation-message">Are you sure you want to perform this action?</p>
                    
                    <div class="action-summary" id="action-summary">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="footer-btn secondary-btn close-modal" data-modal="bulkConfirmationModal">
                Cancel
            </button>
            <button type="button" class="footer-btn danger-btn" id="confirm-bulk-action">
                <i class="fas fa-check"></i>
                Yes, Continue
            </button>
        </div>
    </div>
</div>

<!-- Bulk Action Results Modal -->
<div class="modal" id="bulkResultsModal">
    <div class="modal-content results-modal">
        <div class="modal-header success-header">
            <h2><i class="fas fa-check-circle"></i> Bulk Action Results</h2>
            <span class="close-modal" data-modal="bulkResultsModal">&times;</span>
        </div>
        
        <div class="modal-body">
            <div class="results-summary">
                <div class="summary-stats">
                    <div class="stat-item success">
                        <div class="stat-number" id="successful-count">0</div>
                        <div class="stat-label">Successful</div>
                    </div>
                    <div class="stat-item failed">
                        <div class="stat-number" id="failed-count">0</div>
                        <div class="stat-label">Failed</div>
                    </div>
                    <div class="stat-item total">
                        <div class="stat-number" id="total-processed">0</div>
                        <div class="stat-label">Total Processed</div>
                    </div>
                </div>
                
                <div class="results-details" id="results-details">
                    <!-- Will be populated by JavaScript -->
                </div>
                
                <div class="export-section" id="export-section" style="display: none;">
                    <h4>Export Ready</h4>
                    <p>Your export file has been prepared and is ready for download.</p>
                    <button type="button" class="footer-btn primary-btn" id="download-export-btn">
                        <i class="fas fa-download"></i>
                        Download File
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="footer-btn secondary-btn close-modal" data-modal="bulkResultsModal">
                Close
            </button>
            <button type="button" class="footer-btn primary-btn" id="refresh-applicants-btn">
                <i class="fas fa-sync"></i>
                Refresh List
            </button>
        </div>
    </div>
</div>