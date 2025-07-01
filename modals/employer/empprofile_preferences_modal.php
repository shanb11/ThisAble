<div class="modal" id="preferences-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Hiring Preferences</h2>
            <button type="button" class="close-modal" id="close-preferences-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="toggle-switch">
                    <input type="checkbox" id="hirePwd" checked>
                    <span class="toggle-slider"></span>
                    <span class="toggle-label">We are open to hiring Persons with Disabilities (PWDs)</span>
                </label>
            </div>
            <h3 style="margin: 25px 0 15px; color: var(--text-medium);">Types of Disabilities You Can Accommodate:</h3>
            <div class="categories-container">
                <div class="category-card" data-category="visual">
                    <div class="category-icon visual-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="category-text">
                        Visual Impairment
                    </div>
                </div>
                <div class="category-card" data-category="hearing">
                    <div class="category-icon hearing-icon">
                        <i class="fas fa-deaf"></i>
                    </div>
                    <div class="category-text">
                        Hearing Impairment
                    </div>
                </div>
                <div class="category-card" data-category="physical">
                    <div class="category-icon physical-icon">
                        <i class="fas fa-wheelchair"></i>
                    </div>
                    <div class="category-text">
                        Physical/Mobility
                    </div>
                </div>
                <div class="category-card" data-category="cognitive">
                    <div class="category-icon cognitive-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="category-text">
                        Neurodiverse/Cognitive
                    </div>
                </div>
            </div>
            <h3 style="margin: 25px 0 15px; color: var(--text-medium);">Workplace Accessibility Options:</h3>
            <div class="accessibility-icons">
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="wheelchair">
                        <i class="fas fa-wheelchair"></i>
                    </div>
                    <div class="icon-label">Wheelchair-accessible</div>
                </div>
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="remote">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="icon-label">Remote work</div>
                </div>
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="flexible">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="icon-label">Flexible hours</div>
                </div>
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="sign">
                        <i class="fas fa-american-sign-language-interpreting"></i>
                    </div>
                    <div class="icon-label">Sign language</div>
                </div>
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="assistive">
                        <i class="fas fa-assistive-listening-systems"></i>
                    </div>
                    <div class="icon-label">Assistive tech</div>
                </div>
                <div class="accessibility-icon">
                    <div class="icon-circle" data-option="assistant">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <div class="icon-label">Personal assistant</div>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" id="cancel-preferences">Cancel</button>
            <button type="button" class="save-btn" id="save-preferences">Save Changes</button>
        </div>
    </div>
</div>