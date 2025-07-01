<div class="modal" id="identity-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Company Identity</h2>
            <button type="button" class="close-modal" id="close-identity-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="companyName" class="required-field">Company Name</label>
                <input type="text" id="companyName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="industry" class="required-field">Industry</label>
                <select id="industry" class="form-control" required>
                    <option value="" disabled selected>Select an industry</option>
                </select>
            </div>
            <div class="form-group">
                <label for="companyAddress" class="required-field">Company Address</label>
                <input type="text" id="companyAddress" class="form-control" required>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="cancel-btn" id="cancel-identity">Cancel</button>
            <button type="button" class="save-btn" id="save-identity">Save Changes</button>
        </div>
    </div>
</div>