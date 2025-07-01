<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - ThisAble</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../styles/employer/empsettings.css">
    <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include('../../includes/employer/empsidebar.php'); ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="settings-header">
            <h1>Company Settings</h1>
            <div class="notification-icons">
                <a href="../components/empnotifications.html">
                    <i class="far fa-bell"></i>
                </a>
            </div>
        </div>
        
        <!-- Settings Container -->
        <div class="settings-container" id="settings-main">
            <div class="section-header">Account and profile settings</div>
            <div class="settings-section">
                <div class="setting-item" data-setting="contact-info">
                    <div class="setting-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Contact Info</div>
                        <div class="setting-description">Update your company representative's contact information</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="company-info">
                    <div class="setting-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Company Info</div>
                        <div class="setting-description">Manage your company details and public profile</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="hiring-team">
                    <div class="setting-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Hiring Team Management</div>
                        <div class="setting-description">Add or remove team members and manage their permissions</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="applicant-management">
                    <div class="setting-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Applicant Management Preferences</div>
                        <div class="setting-description">Configure applicant tracking and interview pipeline settings</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="password-security">
                    <div class="setting-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Password & Security</div>
                        <div class="setting-description">Update your password and security settings</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="privacy-preferences">
                    <div class="setting-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Privacy Preferences</div>
                        <div class="setting-description">Control how your company information is shared</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="notification-settings">
                    <div class="setting-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Notification Settings</div>
                        <div class="setting-description">Manage your notification preferences</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="analytics">
                    <div class="setting-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Analytics Settings</div>
                        <div class="setting-description">Configure hiring analytics reports and KPI tracking</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="company-values">
                    <div class="setting-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Company Culture & Values</div>
                        <div class="setting-description">Define your company values for display on job postings</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="feedback-settings">
                    <div class="setting-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Applicant Feedback Settings</div>
                        <div class="setting-description">Configure feedback templates for applicants</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
                
                <div class="setting-item" data-setting="display">
                    <div class="setting-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <div class="setting-content">
                        <div class="setting-title">Display</div>
                        <div class="setting-description">Customize how you view the platform</div>
                    </div>
                    <div class="setting-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
            
            <div class="section-header">Account management and control</div>
            <div class="settings-section">
                <div class="account-action" id="sign-out-btn">
                    <div class="setting-content">
                        <div class="setting-title">Sign Out</div>
                    </div>
                </div>
                
                <div class="account-action" id="close-account-btn">
                    <div class="setting-content">
                        <div class="setting-title danger-text">Close Account</div>
                        <div class="setting-description">This will permanently close your company account</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hiring Team Management Detail -->
        <div class="setting-detail-container" id="hiring-team-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Hiring Team Management</div>
            </div>
            <form id="hiring-team-form">
                <div class="form-group">
                    <label class="form-label">Current Team Members</label>
                    <div class="team-members-list">
                        <div class="team-member">
                            <div class="team-member-avatar">JS</div>
                            <div class="team-member-info">
                                <div class="team-member-name">Jane Smith</div>
                                <div class="team-member-role">HR Manager (Admin)</div>
                            </div>
                            <div class="team-member-actions">
                                <button type="button" class="edit-member" data-id="1"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-member" data-id="1"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="team-member">
                            <div class="team-member-avatar">RM</div>
                            <div class="team-member-info">
                                <div class="team-member-name">Robert Martinez</div>
                                <div class="team-member-role">Recruiter</div>
                            </div>
                            <div class="team-member-actions">
                                <button type="button" class="edit-member" data-id="2"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-member" data-id="2"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="team-member">
                            <div class="team-member-avatar">KL</div>
                            <div class="team-member-info">
                                <div class="team-member-name">Kelly Lee</div>
                                <div class="team-member-role">Hiring Manager (View Only)</div>
                            </div>
                            <div class="team-member-actions">
                                <button type="button" class="edit-member" data-id="3"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-member" data-id="3"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary add-btn" id="add-team-member-btn">
                        <i class="fas fa-plus"></i> Add Team Member
                    </button>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role Permissions</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="admin-full-access" checked>
                        <label class="form-check-label" for="admin-full-access">Admin: Full access to all features</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="recruiter-posting" checked>
                        <label class="form-check-label" for="recruiter-posting">Recruiter: Can post jobs and manage applicants</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="hiring-manager-view" checked>
                        <label class="form-check-label" for="hiring-manager-view">Hiring Manager: View-only access to applications</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Applicant Management Preferences Detail -->
        <div class="setting-detail-container" id="applicant-management-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Applicant Management Preferences</div>
            </div>
            <form id="applicant-management-form">
                <div class="form-group">
                    <label class="form-label">Auto-Tagging</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-tag-skills" checked>
                        <label class="form-check-label" for="auto-tag-skills">Auto-tag applicants based on skills in resume</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-tag-experience" checked>
                        <label class="form-check-label" for="auto-tag-experience">Auto-tag applicants based on experience level</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-tag-education">
                        <label class="form-check-label" for="auto-tag-education">Auto-tag applicants based on education</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-tag-pwd" checked>
                        <label class="form-check-label" for="auto-tag-pwd">Auto-tag PWD applicants when disclosed</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Auto-Response</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-response-received" checked>
                        <label class="form-check-label" for="auto-response-received">Send automatic application received email</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-response-reviewed">
                        <label class="form-check-label" for="auto-response-reviewed">Send automatic email when application is reviewed</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="auto-response-rejected" checked>
                        <label class="form-check-label" for="auto-response-rejected">Send automatic rejection email</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Interview Pipeline</label>
                    <div class="pipeline-stages" id="pipeline-stages-container">
                        <div class="pipeline-stage" data-id="1">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <div class="pipeline-stage-number">1</div>
                            <div class="pipeline-stage-info">
                                <div class="pipeline-stage-name">Application Review</div>
                            </div>
                            <div class="pipeline-stage-actions">
                                <button type="button" class="edit-stage" data-id="1"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-stage" data-id="1"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="pipeline-stage" data-id="2">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <div class="pipeline-stage-number">2</div>
                            <div class="pipeline-stage-info">
                                <div class="pipeline-stage-name">Phone Screening</div>
                            </div>
                            <div class="pipeline-stage-actions">
                                <button type="button" class="edit-stage" data-id="2"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-stage" data-id="2"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="pipeline-stage" data-id="3">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <div class="pipeline-stage-number">3</div>
                            <div class="pipeline-stage-info">
                                <div class="pipeline-stage-name">Technical Assessment</div>
                            </div>
                            <div class="pipeline-stage-actions">
                                <button type="button" class="edit-stage" data-id="3"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-stage" data-id="3"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="pipeline-stage" data-id="4">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <div class="pipeline-stage-number">4</div>
                            <div class="pipeline-stage-info">
                                <div class="pipeline-stage-name">Final Interview</div>
                            </div>
                            <div class="pipeline-stage-actions">
                                <button type="button" class="edit-stage" data-id="4"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-stage" data-id="4"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="pipeline-stage" data-id="5">
                            <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                            <div class="pipeline-stage-number">5</div>
                            <div class="pipeline-stage-info">
                                <div class="pipeline-stage-name">Offer</div>
                            </div>
                            <div class="pipeline-stage-actions">
                                <button type="button" class="edit-stage" data-id="5"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-stage" data-id="5"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary add-btn" id="add-pipeline-stage-btn">
                        <i class="fas fa-plus"></i> Add Pipeline Stage
                    </button>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Company Values Detail -->
        <div class="setting-detail-container" id="company-values-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Company Culture & Values</div>
            </div>
            <form id="company-values-form">
                <div class="form-group">
                    <label class="form-label">Company Values</label>
                    <div class="values-container" id="values-container">
                        <div class="value-item" data-id="1">
                            <div class="value-item-number">1</div>
                            <div class="value-item-content">
                                <div class="value-item-title">Inclusivity</div>
                                <div class="value-item-description">We believe in creating an inclusive workspace where everyone can thrive regardless of their abilities.</div>
                            </div>
                            <div class="value-item-actions">
                                <button type="button" class="edit-value" data-id="1"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-value" data-id="1"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="value-item" data-id="2">
                            <div class="value-item-number">2</div>
                            <div class="value-item-content">
                                <div class="value-item-title">Innovation</div>
                                <div class="value-item-description">We constantly push boundaries and find new ways to solve problems.</div>
                            </div>
                            <div class="value-item-actions">
                                <button type="button" class="edit-value" data-id="2"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-value" data-id="2"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                        <div class="value-item" data-id="3">
                            <div class="value-item-number">3</div>
                            <div class="value-item-content">
                                <div class="value-item-title">Integrity</div>
                                <div class="value-item-description">We act with honesty and transparency in everything we do.</div>
                            </div>
                            <div class="value-item-actions">
                                <button type="button" class="edit-value" data-id="3"><i class="fas fa-pen"></i></button>
                                <button type="button" class="delete-value" data-id="3"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary add-btn" id="add-value-btn">
                        <i class="fas fa-plus"></i> Add Value
                    </button>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Company Culture Statement</label>
                    <textarea class="form-control" rows="5" id="culture-statement">At TechLabs Inc., we cultivate a culture of innovation, inclusivity, and excellence. We believe in providing equal opportunities for all and fostering an environment where people with diverse abilities can thrive. Our workplace is characterized by mutual respect, continuous learning, and a shared commitment to making a positive impact.</textarea>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="display-values" checked>
                    <label class="form-check-label" for="display-values">Display values on job posts</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="display-culture" checked>
                    <label class="form-check-label" for="display-culture">Display culture statement on job posts</label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Feedback Settings Detail -->
        <div class="setting-detail-container" id="feedback-settings-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Applicant Feedback Settings</div>
            </div>
            <form id="feedback-settings-form">
                <div class="form-group">
                    <div class="toggle-container">
                        <label class="toggle-label" for="enable-feedback">Enable feedback for rejected applicants</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="enable-feedback" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Feedback Templates</label>
                    <div class="feedback-templates" id="feedback-templates-container">
                        <div class="feedback-template" data-id="1">
                            <div class="feedback-template-header">
                                <div class="feedback-template-title">Skills Gap</div>
                                <div class="feedback-template-actions">
                                    <button type="button" class="edit-template" data-id="1"><i class="fas fa-pen"></i></button>
                                    <button type="button" class="delete-template" data-id="1"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="feedback-template-content">
                                Thank you for applying. While we were impressed with your background, we found other candidates whose skills better aligned with our current needs. We encourage you to develop more experience in [specific skill] and apply for future positions.
                            </div>
                        </div>
                        <div class="feedback-template" data-id="2">
                            <div class="feedback-template-header">
                                <div class="feedback-template-title">Experience Level</div>
                                <div class="feedback-template-actions">
                                    <button type="button" class="edit-template" data-id="2"><i class="fas fa-pen"></i></button>
                                    <button type="button" class="delete-template" data-id="2"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="feedback-template-content">
                                Thank you for your interest in our company. We've decided to move forward with candidates who have more experience in [specific area]. We encourage you to gain more experience and apply for future roles.
                            </div>
                        </div>
                        <div class="feedback-template" data-id="3">
                            <div class="feedback-template-header">
                                <div class="feedback-template-title">Cultural Fit</div>
                                <div class="feedback-template-actions">
                                    <button type="button" class="edit-template" data-id="3"><i class="fas fa-pen"></i></button>
                                    <button type="button" class="delete-template" data-id="3"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="feedback-template-content">
                                Thank you for applying. After careful consideration, we've decided to pursue candidates whose working styles better align with our team culture. We appreciate your interest and wish you success in your job search.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary add-btn" id="add-template-btn">
                        <i class="fas fa-plus"></i> Add Template
                    </button>
                </div>
                
                <div class="form-group">
                    <div class="toggle-container">
                        <label class="toggle-label" for="require-review">Require manager review before sending feedback</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="require-review" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Customization Options</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="allow-custom" checked>
                        <label class="form-check-label" for="allow-custom">Allow recruiters to customize feedback</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="insert-company-name" checked>
                        <label class="form-check-label" for="insert-company-name">Automatically insert company name</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="insert-job-title" checked>
                        <label class="form-check-label" for="insert-job-title">Automatically insert job title</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Contact Info Detail -->
        <div class="setting-detail-container" id="contact-info-detail">
            <div class="detail-header">
                <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
                <div class="detail-title">Contact Info</div>
            </div>
            <form id="contact-info-form">
                <div class="form-group">
                    <label class="form-label" for="rep-name">Representative Name</label>
                    <input type="text" class="form-control" id="rep-name" value="Jane Smith">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Position</label>
                    <input type="text" class="form-control" id="position" value="HR Manager">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" value="jane.smith@company.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Contact Number</label>
                    <input type="tel" class="form-control" id="phone" value="+1 234 567 8900">
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Company Address</label>
                    <input type="text" class="form-control" id="address" value="123 Corporate Ave, Business District, City, Country">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Other setting detail containers would go here... -->
        
        <!-- Toast Notification -->
        <div class="toast" id="toast">
            <i class="fas fa-check-circle"></i>
            <span id="toast-message">Changes saved successfully!</span>
        </div>
        
        <!-- Modals -->
        
        <!-- Sign Out Modal -->
        <div class="modal-overlay" id="sign-out-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Sign Out</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to sign out?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-sign-out">Cancel</button>
                    <button class="btn btn-primary" id="confirm-sign-out">Sign Out</button>
                </div>
            </div>
        </div>
        
        <!-- Close Account Modal -->
        <div class="modal-overlay" id="close-account-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Close Account</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close your account? This action cannot be undone and all your data will be permanently deleted.</p>
                    <div class="form-group" style="margin-top: 15px;">
                        <label class="form-label" for="confirm-password">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="confirm-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="cancel-close-account">Cancel</button>
                    <button class="btn btn-danger" id="confirm-close-account">Close Account</button>
                </div>
            </div>
        </div>
        
        <!-- Add Team Member Modal -->
        <div class="modal-overlay" id="add-team-member-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Add Team Member</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="member-name">Name</label>
                        <input type="text" class="form-control" id="member-name" placeholder="Enter team member's name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="member-email">Email</label>
                        <input type="email" class="form-control" id="member-email" placeholder="Enter team member's email">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="member-role">Role</label>
                        <select class="form-control" id="member-role">
                            <option value="HR Manager (Admin)">HR Manager (Admin)</option>
                            <option value="Recruiter">Recruiter</option>
                            <option value="Hiring Manager (View Only)">Hiring Manager (View Only)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="add-member-submit">Add Team Member</button>
                </div>
            </div>
        </div>
        
        <!-- Edit Team Member Modal -->
        <div class="modal-overlay" id="edit-team-member-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Team Member</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit-member-name">Name</label>
                        <input type="text" class="form-control" id="edit-member-name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-member-role">Role</label>
                        <select class="form-control" id="edit-member-role">
                            <option value="HR Manager (Admin)">HR Manager (Admin)</option>
                            <option value="Recruiter">Recruiter</option>
                            <option value="Hiring Manager (View Only)">Hiring Manager (View Only)</option>
                        </select>
                    </div>
                    <input type="hidden" id="edit-member-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="edit-member-submit">Save Changes</button>
                </div>
            </div>
        </div>
        
        <!-- Delete Team Member Modal -->
        <div class="modal-overlay" id="delete-team-member-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Remove Team Member</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove <span id="delete-member-name"></span> from your hiring team?</p>
                    <input type="hidden" id="delete-member-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-danger" id="delete-member-submit">Remove</button>
                </div>
            </div>
        </div>
        
        <!-- Add Pipeline Stage Modal -->
        <div class="modal-overlay" id="add-pipeline-stage-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Add Pipeline Stage</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="stage-name">Stage Name</label>
                        <input type="text" class="form-control" id="stage-name" placeholder="Enter stage name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="add-stage-submit">Add Stage</button>
                </div>
            </div>
        </div>
        
        <!-- Edit Pipeline Stage Modal -->
        <div class="modal-overlay" id="edit-pipeline-stage-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Pipeline Stage</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit-stage-name">Stage Name</label>
                        <input type="text" class="form-control" id="edit-stage-name">
                    </div>
                    <input type="hidden" id="edit-stage-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="edit-stage-submit">Save Changes</button>
                </div>
            </div>
        </div>
        
        <!-- Delete Pipeline Stage Modal -->
        <div class="modal-overlay" id="delete-pipeline-stage-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Delete Pipeline Stage</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the "<span id="delete-stage-name"></span>" stage from your pipeline?</p>
                    <input type="hidden" id="delete-stage-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-danger" id="delete-stage-submit">Delete</button>
                </div>
            </div>
        </div>
        
        <!-- Add Company Value Modal -->
        <div class="modal-overlay" id="add-value-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Add Company Value</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="value-title">Value Title</label>
                        <input type="text" class="form-control" id="value-title" placeholder="e.g., Integrity, Innovation">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="value-description">Description</label>
                        <textarea class="form-control" id="value-description" rows="3" placeholder="Describe this company value"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="add-value-submit">Add Value</button>
                </div>
            </div>
        </div>
        
        <!-- Edit Company Value Modal -->
        <div class="modal-overlay" id="edit-value-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Company Value</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit-value-title">Value Title</label>
                        <input type="text" class="form-control" id="edit-value-title">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-value-description">Description</label>
                        <textarea class="form-control" id="edit-value-description" rows="3"></textarea>
                    </div>
                    <input type="hidden" id="edit-value-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="edit-value-submit">Save Changes</button>
                </div>
            </div>
        </div>
        
        <!-- Delete Company Value Modal -->
        <div class="modal-overlay" id="delete-value-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Delete Company Value</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the "<span id="delete-value-title"></span>" value?</p>
                    <input type="hidden" id="delete-value-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-danger" id="delete-value-submit">Delete</button>
                </div>
            </div>
        </div>
        
        <!-- Add Feedback Template Modal -->
        <div class="modal-overlay" id="add-template-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Add Feedback Template</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="template-title">Template Title</label>
                        <input type="text" class="form-control" id="template-title" placeholder="e.g., Skills Gap, Experience Level">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="template-content">Template Content</label>
                        <textarea class="form-control" id="template-content" rows="5" placeholder="Enter the feedback template content..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="add-template-submit">Add Template</button>
                </div>
            </div>
        </div>
        
        <!-- Edit Feedback Template Modal -->
        <div class="modal-overlay" id="edit-template-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Edit Feedback Template</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="edit-template-title">Template Title</label>
                        <input type="text" class="form-control" id="edit-template-title">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="edit-template-content">Template Content</label>
                        <textarea class="form-control" id="edit-template-content" rows="5"></textarea>
                    </div>
                    <input type="hidden" id="edit-template-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-primary" id="edit-template-submit">Save Changes</button>
                </div>
            </div>
        </div>
        
        <!-- Delete Feedback Template Modal -->
        <div class="modal-overlay" id="delete-template-modal">
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Delete Feedback Template</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the "<span id="delete-template-title"></span>" template?</p>
                    <input type="hidden" id="delete-template-id">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary modal-cancel-btn">Cancel</button>
                    <button class="btn btn-danger" id="delete-template-submit">Delete</button>
                </div>
            </div>
        </div>
        <!-- Company Info Settings Detail -->
<div class="setting-detail-container" id="company-info-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Company Info</div>
    </div>
    <form id="company-info-form">
        <div class="form-group">
            <label class="form-label" for="company-name">Company Name</label>
            <input type="text" class="form-control" id="company-name" value="TechLabs Inc.">
        </div>
        <div class="form-group">
            <label class="form-label" for="company-industry">Industry</label>
            <select class="select-control" id="company-industry">
                <option value="">Loading industries...</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="company-description">Company Description</label>
            <textarea class="form-control" id="company-description" rows="4">TechLabs Inc. is a leading technology company specializing in accessibility-focused software solutions. We believe in creating innovative products that are usable by everyone, regardless of their abilities.</textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="company-website">Company Website</label>
            <input type="url" class="form-control" id="company-website" value="https://techlabs-example.com">
        </div>
        <div class="form-group">
            <label class="form-label" for="company-size">Company Size</label>
            <select class="select-control" id="company-size">
                <option value="1-10">1-10 employees</option>
                <option value="11-50" selected>11-50 employees</option>
                <option value="51-200">51-200 employees</option>
                <option value="201-500">201-500 employees</option>
                <option value="501-1000">501-1000 employees</option>
                <option value="1001+">1001+ employees</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Company Logo</label>
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <div style="width: 100px; height: 100px; background-color: var(--light-gray); display: flex; align-items: center; justify-content: center; margin-right: 20px; border-radius: 5px; overflow: hidden;">
                    <img id="logo-preview-img" src="company-logo-placeholder.png" alt="Company Logo" style="max-width: 100%; max-height: 100%;">
                </div>
                <div class="file-upload">
                    <label for="logo-upload" class="file-upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Logo
                    </label>
                    <input type="file" id="logo-upload" accept="image/*">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Social Media Links</label>
            <div style="margin-bottom: 10px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fab fa-linkedin" style="font-size: 20px; min-width: 30px;"></i>
                    <input type="url" class="form-control" placeholder="LinkedIn URL" value="https://linkedin.com/company/techlabs-example">
                </div>
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <i class="fab fa-twitter" style="font-size: 20px; min-width: 30px;"></i>
                    <input type="url" class="form-control" placeholder="Twitter URL" value="https://twitter.com/techlabs_example">
                </div>
                <div style="display: flex; align-items: center;">
                    <i class="fab fa-facebook" style="font-size: 20px; min-width: 30px;"></i>
                    <input type="url" class="form-control" placeholder="Facebook URL" value="https://facebook.com/techlabs_example">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<!-- Password & Security Detail -->
<div class="setting-detail-container" id="password-security-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Password & Security</div>
    </div>
    <form id="password-security-form">
        <div class="form-group">
            <label class="form-label" for="current-password">Current Password</label>
            <input type="password" class="form-control" id="current-password" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="new-password">New Password</label>
            <input type="password" class="form-control" id="new-password" required>
            <small class="form-text text-muted">Password must be at least 8 characters long</small>
        </div>
        <div class="form-group">
            <label class="form-label" for="confirm-password">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm-password" required>
        </div>
        <div class="form-group">
            <label class="form-label">Two-Factor Authentication</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="two-factor" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="two-factor">Enable Two-Factor Authentication</label>
            </div>
            <small class="form-text text-muted">Adds an extra layer of security to your account</small>
        </div>
        <div class="form-group">
            <label class="form-label">Login Session</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="remember-login" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="remember-login">Keep me logged in</label>
            </div>
            <small class="form-text text-muted">Not recommended for shared devices</small>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
        <button type="button" class="btn btn-secondary" id="logout-all-devices" style="margin-left: 10px;">Sign Out From All Devices</button>
    </form>
</div>

<!-- Privacy Preferences Detail -->
<div class="setting-detail-container" id="privacy-preferences-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Privacy Preferences</div>
    </div>
    <form id="privacy-preferences-form">
        <div class="form-group">
            <label class="form-label">Company Profile Visibility</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="profile-visibility" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="profile-visibility">Show my company in public searches</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Company Information Sharing</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="share-company-info" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="share-company-info">Share company information with job seekers</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="share-contact-info">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="share-contact-info">Share company contact information with job seekers</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Job Visibility</label>
            <select class="select-control" id="job-visibility">
                <option value="public" selected>Public - visible to all job seekers</option>
                <option value="limited">Limited - visible only to authenticated users</option>
                <option value="private">Private - visible only through direct links</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Data Collection</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="allow-data-collection" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="allow-data-collection">Allow ThisAble to collect data for platform improvement</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="allow-marketing">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="allow-marketing">Allow ThisAble to send marketing communications</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Third-Party Sharing</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="allow-third-party">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="allow-third-party">Allow sharing data with trusted partners</label>
            </div>
            <small class="form-text text-muted">We only share data with partners who adhere to our strict privacy standards</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>

<!-- Notification Settings Detail -->
<div class="setting-detail-container" id="notification-settings-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Notification Settings</div>
    </div>
    <form id="notification-settings-form">
        <div class="form-group">
            <label class="form-label">Notification Methods</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="email-notifications" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="email-notifications">Email Notifications</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="sms-notifications">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="sms-notifications">SMS Notifications</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="push-notifications" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="push-notifications">In-app Notifications</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Notification Categories</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="new-applications" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="new-applications">New Applications</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="application-status" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="application-status">Application Status Updates</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="message-notifications" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="message-notifications">Messages from Applicants</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="system-updates" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="system-updates">System Updates & Announcements</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="marketing-notifications">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="marketing-notifications">Marketing & Promotions</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Email Frequency</label>
            <select class="select-control" id="email-frequency">
                <option value="immediate" selected>Send emails immediately</option>
                <option value="digest">Send daily digest</option>
                <option value="weekly">Send weekly summary</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Quiet Hours</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="enable-quiet-hours">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="enable-quiet-hours">Enable quiet hours</label>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <div style="flex: 1;">
                    <label class="form-label" for="quiet-from">From</label>
                    <input type="time" class="form-control" id="quiet-from" value="22:00">
                </div>
                <div style="flex: 1;">
                    <label class="form-label" for="quiet-to">To</label>
                    <input type="time" class="form-control" id="quiet-to" value="08:00">
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>

<!-- Display Settings Detail -->
<div class="setting-detail-container" id="display-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Display</div>
    </div>
    <form id="display-form">
        <div class="form-group">
            <label class="form-label">Theme</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="light-theme" name="theme" checked>
                <label class="form-check-label" for="light-theme">Light</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="dark-theme" name="theme">
                <label class="form-check-label" for="dark-theme">Dark</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="system-theme" name="theme">
                <label class="form-check-label" for="system-theme">System Default</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Font Size</label>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="small-font" name="font-size">
                <label class="form-check-label" for="small-font">Small</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="medium-font" name="font-size" checked>
                <label class="form-check-label" for="medium-font">Medium</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="large-font" name="font-size">
                <label class="form-check-label" for="large-font">Large</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Color Scheme</label>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="width: 40px; height: 40px; background-color: var(--primary); border-radius: 5px;"></div>
                <div style="width: 40px; height: 40px; background-color: var(--secondary); border-radius: 5px;"></div>
                <div style="width: 40px; height: 40px; background-color: var(--accent); border-radius: 5px;"></div>
            </div>
            <select class="select-control" id="color-scheme">
                <option value="default" selected>Default (Teal & Orange)</option>
                <option value="blue">Blue & Yellow</option>
                <option value="purple">Purple & Green</option>
                <option value="red">Red & Blue</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">Accessibility Features</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="high-contrast">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="high-contrast">High Contrast Mode</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="reduce-motion">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="reduce-motion">Reduce Motion</label>
            </div>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="screen-reader-support" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="screen-reader-support">Screen Reader Support</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Default View</label>
            <select class="select-control" id="default-view">
                <option value="dashboard" selected>Dashboard</option>
                <option value="job-listings">Job Listings</option>
                <option value="applicants">Applicants</option>
                <option value="company-profile">Company Profile</option>
            </select>
            <small class="form-text text-muted">Select the page that will be shown when you first log in</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>

<!-- Analytics Settings Detail -->
<div class="setting-detail-container" id="analytics-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Analytics Settings</div>
    </div>
    <form id="analytics-form">
        <div class="form-group">
            <label class="form-label">Analytics Collection</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="enable-analytics" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="enable-analytics">Enable hiring analytics collection</label>
            </div>
            <small class="form-text text-muted">Analytics help you understand your hiring performance and make better decisions</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Key Performance Indicators (KPIs)</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-time-to-hire" checked>
                <label class="form-check-label" for="track-time-to-hire">Time to Hire</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-cost-per-hire" checked>
                <label class="form-check-label" for="track-cost-per-hire">Cost per Hire</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-application-completion" checked>
                <label class="form-check-label" for="track-application-completion">Application Completion Rate</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-diversity" checked>
                <label class="form-check-label" for="track-diversity">Diversity & Inclusion Metrics</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="track-source-effectiveness" checked>
                <label class="form-check-label" for="track-source-effectiveness">Source Effectiveness</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Report Delivery</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="weekly-report" checked>
                <label class="form-check-label" for="weekly-report">Receive weekly analytics report</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="monthly-report" checked>
                <label class="form-check-label" for="monthly-report">Receive monthly analytics report</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="quarterly-report">
                <label class="form-check-label" for="quarterly-report">Receive quarterly analytics report</label>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Data Retention</label>
            <select class="select-control" id="data-retention">
                <option value="3-months">3 months</option>
                <option value="6-months">6 months</option>
                <option value="1-year" selected>1 year</option>
                <option value="2-years">2 years</option>
                <option value="indefinite">Indefinite</option>
            </select>
            <small class="form-text text-muted">How long analytics data should be retained</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Integration with Other Tools</label>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="integrate-google-analytics">
                <label class="form-check-label" for="integrate-google-analytics">Integrate with Google Analytics</label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="integrate-hr-system">
                <label class="form-check-label" for="integrate-hr-system">Integrate with HR Management System</label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Settings</button>
        <button type="button" class="btn btn-secondary" style="margin-left: 10px;">Export Historical Data</button>
    </form>
</div>
    </div>
     

    <script src="../../scripts/employer/empsettings.js"> </script>
        </body>
        </html>