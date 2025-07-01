<div class="modal" id="contact-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Contact Information</h2>
            <button type="button" class="close-modal" id="close-contact-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="firstName" class="required-field">First Name</label>
                <input type="text" id="firstName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="lastName" class="required-field">Last Name</label>
                <input type="text" id="lastName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="position" class="required-field">Position</label>
                <input type="text" id="position" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="contactNumber" class="required-field">Contact Number</label>
                <input type="tel" id="contactNumber" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email" class="required-field">Email</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password (leave blank to keep current)</label>
                <div class="password-input">
                    <input type="password" id="password" class="form-control">
                    <button type="button" class="password-toggle" id="password-toggle">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-segment segment-1"></div>
                    <div class="strength-segment segment-2"></div>
                    <div class="strength-segment segment-3"></div>
                    <div class="strength-segment segment-4"></div>
                </div>
                <div class="strength-text">
                    <span class="strength-label">Password Strength</span>
                    <span>Use 8+ characters with letters, numbers & symbols</span>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" id="cancel-contact">Cancel</button>
            <button type="button" class="save-btn" id="save-contact">Save Changes</button>
        </div>
    </div>
</div>
