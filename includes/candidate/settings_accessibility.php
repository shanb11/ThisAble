<div class="setting-detail-container" id="accessibility-detail">
    <div class="detail-header">
        <div class="back-btn" data-target="settings-main"><i class="fas fa-arrow-left"></i></div>
        <div class="detail-title">Accessibility Preferences</div>
    </div>
    <form id="accessibility-form">
        <div class="form-group">
            <label class="form-label">High Contrast Mode</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="high-contrast-toggle">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="high-contrast-toggle">Enable high contrast for better visibility</label>
            </div>
            <div class="color-mode-preview">
                <div class="color-preview standard"></div>
                <div class="color-preview high-contrast"></div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Text Size</label>
            <input type="range" min="1" max="3" value="2" class="font-size-slider" id="font-size-slider">
            <div style="display: flex; justify-content: space-between;">
                <span>Small</span>
                <span>Medium</span>
                <span>Large</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Screen Reader Support</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="screen-reader-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="screen-reader-toggle">Optimize content for screen readers</label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Keyboard Navigation</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="keyboard-nav-toggle" checked>
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="keyboard-nav-toggle">Enable enhanced keyboard navigation</label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Motion Reduction</label>
            <div class="form-check">
                <label class="toggle-switch">
                    <input type="checkbox" id="motion-reduction-toggle">
                    <span class="toggle-slider"></span>
                </label>
                <label class="form-check-label" for="motion-reduction-toggle">Reduce animations and motion effects</label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="assistive-tools">Custom Assistive Tools</label>
            <select class="select-control" id="assistive-tools" multiple>
                <option value="magnifier">Screen Magnifier</option>
                <option value="caption">Automatic Captions</option>
                <option value="dictation">Voice Dictation</option>
                <option value="alternative-input">Alternative Input Devices</option>
            </select>
            <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple options</small>
        </div>

        <button type="submit" class="btn btn-primary">Save Preferences</button>
    </form>
</div>