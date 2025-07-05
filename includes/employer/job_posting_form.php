<?php
/**
 * Enhanced Job Posting Form with Requirements
 * Save as: includes/employer/job_posting_form.php (New file)
 */
?>

<!-- Job Posting Modal -->
<div class="modal-overlay" id="job-posting-modal" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2><i class="fas fa-briefcase"></i> <span id="modal-title">Post New Job</span></h2>
            <button type="button" class="close-modal-btn" onclick="closeJobModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="job-posting-form">
            <input type="hidden" id="job-id" name="job_id" value="">
            
            <div class="form-sections">
                
                <!-- Basic Information Section -->
                <div class="form-section active" id="section-basic">
                    <div class="section-header">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="job-title">
                                <i class="fas fa-briefcase"></i> Job Title *
                            </label>
                            <input type="text" id="job-title" name="job_title" required 
                                   placeholder="e.g., Software Developer">
                        </div>

                        <div class="form-group">
                            <label for="department">
                                <i class="fas fa-building"></i> Department *
                            </label>
                            <input type="text" id="department" name="department" required 
                                   placeholder="e.g., Engineering">
                        </div>

                        <div class="form-group">
                            <label for="location">
                                <i class="fas fa-map-marker-alt"></i> Location *
                            </label>
                            <input type="text" id="location" name="location" required 
                                   placeholder="e.g., Manila, Philippines">
                        </div>

                        <div class="form-group">
                            <label for="employment-type">
                                <i class="fas fa-clock"></i> Employment Type *
                            </label>
                            <select id="employment-type" name="employment_type" required>
                                <option value="">Select employment type...</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Internship">Internship</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="salary-range">
                                <i class="fas fa-dollar-sign"></i> Salary Range
                            </label>
                            <input type="text" id="salary-range" name="salary_range" 
                                   placeholder="e.g., ₱50,000 - ₱80,000">
                        </div>

                        <div class="form-group">
                            <label for="application-deadline">
                                <i class="fas fa-calendar"></i> Application Deadline
                            </label>
                            <input type="date" id="application-deadline" name="application_deadline">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="job-description">
                            <i class="fas fa-align-left"></i> Job Description *
                        </label>
                        <textarea id="job-description" name="job_description" required rows="4" 
                                  placeholder="Describe the role, responsibilities, and what the candidate will be doing..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="job-requirements">
                            <i class="fas fa-list-check"></i> Basic Requirements *
                        </label>
                        <textarea id="job-requirements" name="job_requirements" required rows="3" 
                                  placeholder="List the basic requirements, skills, and qualifications..."></textarea>
                    </div>
                </div>

                <!-- NEW: Enhanced Requirements Section -->
                <div class="form-section" id="section-requirements">
                    <div class="section-header">
                        <h3><i class="fas fa-graduation-cap"></i> Detailed Requirements</h3>
                        <p class="section-description">Specify credentials and experience requirements for better candidate matching</p>
                    </div>
                    
                    <!-- Education Requirements -->
                    <div class="requirement-group">
                        <h4><i class="fas fa-university"></i> Education Requirements</h4>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="requires-degree" name="requires_degree">
                                <span class="checkmark"></span>
                                Requires College Degree
                            </label>
                        </div>
                        
                        <div class="conditional-field" id="degree-field-container" style="display: none;">
                            <label for="degree-field">
                                <i class="fas fa-book"></i> Specific Field of Study
                            </label>
                            <select id="degree-field" name="degree_field">
                                <option value="">Any field (no specific requirement)</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Business Administration">Business Administration</option>
                                <option value="Accounting">Accounting</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Education">Education</option>
                                <option value="Nursing">Nursing</option>
                                <option value="Psychology">Psychology</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Certification Requirements -->
                    <div class="requirement-group">
                        <h4><i class="fas fa-certificate"></i> Certification Requirements</h4>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="requires-certification" name="requires_certification">
                                <span class="checkmark"></span>
                                Requires Professional Certification
                            </label>
                        </div>
                        
                        <div class="conditional-field" id="certification-type-container" style="display: none;">
                            <label for="certification-type">
                                <i class="fas fa-award"></i> Certification Type
                            </label>
                            <select id="certification-type" name="certification_type">
                                <option value="">Any certification</option>
                                <option value="AWS Certification">AWS Certification</option>
                                <option value="Microsoft Certification">Microsoft Certification</option>
                                <option value="Google Certification">Google Certification</option>
                                <option value="Cisco Certification">Cisco Certification</option>
                                <option value="PMP Certification">PMP Certification</option>
                                <option value="Six Sigma">Six Sigma</option>
                                <option value="CompTIA">CompTIA</option>
                                <option value="Adobe Certification">Adobe Certification</option>
                                <option value="Salesforce Certification">Salesforce Certification</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- License Requirements -->
                    <div class="requirement-group">
                        <h4><i class="fas fa-id-card"></i> License Requirements</h4>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="requires-license" name="requires_license">
                                <span class="checkmark"></span>
                                Requires Professional License
                            </label>
                        </div>
                        
                        <div class="conditional-field" id="license-type-container" style="display: none;">
                            <label for="license-type">
                                <i class="fas fa-id-badge"></i> License Type
                            </label>
                            <select id="license-type" name="license_type">
                                <option value="">Any license</option>
                                <option value="Professional Engineer License">Professional Engineer License</option>
                                <option value="CPA License">CPA License</option>
                                <option value="Teaching License">Teaching License</option>
                                <option value="Nursing License">Nursing License</option>
                                <option value="Real Estate License">Real Estate License</option>
                                <option value="Driver's License">Driver's License</option>
                                <option value="Security License">Security License</option>
                                <option value="Trade License">Trade License</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Experience Requirements -->
                    <div class="requirement-group">
                        <h4><i class="fas fa-briefcase"></i> Experience Requirements</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="min-experience-years">
                                    <i class="fas fa-calendar-alt"></i> Minimum Years of Experience
                                </label>
                                <select id="min-experience-years" name="min_experience_years">
                                    <option value="0">No experience required (Entry level)</option>
                                    <option value="1">1 year</option>
                                    <option value="2">2 years</option>
                                    <option value="3">3 years</option>
                                    <option value="4">4 years</option>
                                    <option value="5">5 years</option>
                                    <option value="7">7+ years</option>
                                    <option value="10">10+ years</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="specific-industry-exp" name="specific_industry_exp">
                                <span class="checkmark"></span>
                                Requires Specific Industry Experience
                            </label>
                        </div>
                    </div>

                    <!-- Requirements Preview -->
                    <div class="requirements-preview" id="requirements-preview">
                        <h4><i class="fas fa-eye"></i> Requirements Summary</h4>
                        <div class="preview-content" id="preview-content">
                            <p class="no-requirements">No specific requirements set</p>
                        </div>
                    </div>
                </div>

                <!-- Work Arrangements Section -->
                <div class="form-section" id="section-arrangements">
                    <div class="section-header">
                        <h3><i class="fas fa-home"></i> Work Arrangements</h3>
                    </div>
                    
                    <div class="checkbox-groups">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="remote-work" name="remote_work_available">
                                <span class="checkmark"></span>
                                Remote Work Available
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="flexible-schedule" name="flexible_schedule">
                                <span class="checkmark"></span>
                                Flexible Schedule
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Form Navigation -->
            <div class="form-navigation">
                <div class="nav-buttons">
                    <button type="button" class="btn secondary-btn" id="prev-section-btn" onclick="previousSection()" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn primary-btn" id="next-section-btn" onclick="nextSection()">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
                
                <div class="form-actions" id="form-actions" style="display: none;">
                    <button type="button" class="btn secondary-btn" onclick="closeJobModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn accent-btn" onclick="saveAsDraft()">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" class="btn primary-btn">
                        <i class="fas fa-rocket"></i> Post Job
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- Section Indicators -->
<div class="section-indicators" id="section-indicators" style="display: none;">
    <div class="indicator active" data-section="basic">
        <span class="indicator-number">1</span>
        <span class="indicator-label">Basic Info</span>
    </div>
    <div class="indicator" data-section="requirements">
        <span class="indicator-number">2</span>
        <span class="indicator-label">Requirements</span>
    </div>
    <div class="indicator" data-section="arrangements">
        <span class="indicator-number">3</span>
        <span class="indicator-label">Arrangements</span>
    </div>
</div>

<script>
// Initialize enhanced form functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeRequirementsForm();
});

function initializeRequirementsForm() {
    // Conditional field handlers
    document.getElementById('requires-degree')?.addEventListener('change', function() {
        toggleConditionalField('degree-field-container', this.checked);
        updateRequirementsPreview();
    });
    
    document.getElementById('requires-certification')?.addEventListener('change', function() {
        toggleConditionalField('certification-type-container', this.checked);
        updateRequirementsPreview();
    });
    
    document.getElementById('requires-license')?.addEventListener('change', function() {
        toggleConditionalField('license-type-container', this.checked);
        updateRequirementsPreview();
    });
    
    // Preview update handlers
    const previewTriggers = ['degree-field', 'certification-type', 'license-type', 'min-experience-years', 'specific-industry-exp'];
    previewTriggers.forEach(id => {
        document.getElementById(id)?.addEventListener('change', updateRequirementsPreview);
    });
}

function toggleConditionalField(containerId, show) {
    const container = document.getElementById(containerId);
    if (container) {
        container.style.display = show ? 'block' : 'none';
    }
}

function updateRequirementsPreview() {
    const preview = document.getElementById('preview-content');
    if (!preview) return;
    
    const requirements = [];
    
    // Check degree requirement
    const requiresDegree = document.getElementById('requires-degree')?.checked;
    if (requiresDegree) {
        const degreeField = document.getElementById('degree-field')?.value;
        if (degreeField) {
            requirements.push(`🎓 ${degreeField} degree required`);
        } else {
            requirements.push('🎓 College degree required');
        }
    }
    
    // Check certification requirement
    const requiresCert = document.getElementById('requires-certification')?.checked;
    if (requiresCert) {
        const certType = document.getElementById('certification-type')?.value;
        if (certType) {
            requirements.push(`🏆 ${certType} required`);
        } else {
            requirements.push('🏆 Professional certification required');
        }
    }
    
    // Check license requirement
    const requiresLicense = document.getElementById('requires-license')?.checked;
    if (requiresLicense) {
        const licenseType = document.getElementById('license-type')?.value;
        if (licenseType) {
            requirements.push(`📜 ${licenseType} required`);
        } else {
            requirements.push('📜 Professional license required');
        }
    }
    
    // Check experience requirement
    const minYears = document.getElementById('min-experience-years')?.value;
    if (minYears && minYears > 0) {
        const industryExp = document.getElementById('specific-industry-exp')?.checked;
        const expText = industryExp ? `${minYears} years of industry-specific experience` : `${minYears} years of experience`;
        requirements.push(`💼 Minimum ${expText}`);
    }
    
    // Update preview
    if (requirements.length > 0) {
        preview.innerHTML = '<ul>' + requirements.map(req => `<li>${req}</li>`).join('') + '</ul>';
    } else {
        preview.innerHTML = '<p class="no-requirements">No specific requirements set</p>';
    }
}
</script>