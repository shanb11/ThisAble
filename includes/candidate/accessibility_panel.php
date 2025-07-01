<button class="accessibility-toggle" aria-label="Accessibility options">
    <i class="fas fa-universal-access"></i>
</button>

<div class="accessibility-panel">
    <h3>Accessibility Options</h3>
    <div class="accessibility-option">
        <label for="high-contrast">High Contrast</label>
        <label class="toggle-switch">
            <input type="checkbox" id="high-contrast">
            <span class="slider"></span>
        </label>
    </div>
    <div class="accessibility-option">
        <label for="reduce-motion">Reduce Motion</label>
        <label class="toggle-switch">
            <input type="checkbox" id="reduce-motion">
            <span class="slider"></span>
        </label>
    </div>
    <div class="accessibility-option">
        <label>Font Size</label>
        <div class="font-size-controls">
            <button class="font-size-btn" id="decrease-font" aria-label="Decrease font size">A-</button>
            <span class="font-size-value">100%</span>
            <button class="font-size-btn" id="increase-font" aria-label="Increase font size">A+</button>
        </div>
    </div>
</div>