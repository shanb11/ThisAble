<div class="modal" id="logo-description-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Logo & Description</h2>
            <button type="button" class="close-modal" id="close-logo-description-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Company Logo</label>
                <div class="company-logo-section">
                    <div class="logo-preview-container">
                        <div class="logo-preview">
                            <img id="modal-logo-img" src="" alt="" style="display: none;">
                            <i class="fas fa-building" id="modal-logo-placeholder" style="font-size: 50px;"></i>
                        </div>
                    </div>
                    <div class="logo-upload-container">
                        <input type="file" id="logo-input" accept="image/*" hidden>
                        <button type="button" class="btn primary-btn" id="upload-logo-btn">
                            <i class="fas fa-upload"></i> Upload Logo
                        </button>
                        <button type="button" class="btn action-btn" id="remove-logo-btn">
                            <i class="fas fa-trash-alt"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="aboutUs" class="required-field">Company Description</label>
                <textarea id="aboutUs" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="whyJoinUs">Why Join Us?</label>
                <textarea id="whyJoinUs" class="form-control"></textarea>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" id="cancel-logo-description">Cancel</button>
            <button type="button" class="save-btn" id="save-logo-description">Save Changes</button>
        </div>
    </div>
</div>