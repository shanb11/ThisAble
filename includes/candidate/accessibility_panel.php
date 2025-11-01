<!-- Accessibility Button - SUPER SIMPLE -->
<div class="accessibility-floating-button" onclick="toggleAccessibility()">
    <i class="fas fa-universal-access"></i>
</div>

<!-- Accessibility Panel - SUPER SIMPLE -->
<div class="accessibility-floating-panel" id="accessibilityPanel">
    <div class="panel-header">
        <h3>Accessibility</h3>
        <button class="close-panel" onclick="closeAccessibility()">×</button>
    </div>
    
    <div class="panel-content">
        <div class="option-row">
            <span>High Contrast</span>
            <label class="switch">
                <input type="checkbox" onclick="toggleContrast()">
                <span class="switch-slider"></span>
            </label>
        </div>
        
        <div class="option-row">
            <span>Reduce Motion</span>
            <label class="switch">
                <input type="checkbox" onclick="toggleMotion()">
                <span class="switch-slider"></span>
            </label>
        </div>
        
        <div class="option-row">
            <span>Font Size</span>
            <div class="font-controls">
                <button onclick="fontSmaller()">A-</button>
                <span id="fontSize">100%</span>
                <button onclick="fontBigger()">A+</button>
            </div>
        </div>
    </div>
</div>

<script>
// Simple global variable
var panelOpen = false;
var currentSize = 100;

function toggleAccessibility() {
    console.log('Button clicked!');
    var panel = document.getElementById('accessibilityPanel');
    
    if (panelOpen) {
        panel.style.display = 'none';
        panelOpen = false;
    } else {
        panel.style.display = 'block';
        panelOpen = true;
    }
}

function closeAccessibility() {
    document.getElementById('accessibilityPanel').style.display = 'none';
    panelOpen = false;
}

function toggleContrast() {
    document.body.classList.toggle('high-contrast-mode');
}

function toggleMotion() {
    document.body.classList.toggle('no-motion');
}

function fontSmaller() {
    if (currentSize > 80) {
        currentSize -= 10;
        document.documentElement.style.fontSize = currentSize + '%';
        document.getElementById('fontSize').textContent = currentSize + '%';
    }
}

function fontBigger() {
    if (currentSize < 150) {
        currentSize += 10;
        document.documentElement.style.fontSize = currentSize + '%';
        document.getElementById('fontSize').textContent = currentSize + '%';
    }
}
</script>

<style>
/* Floating Button - ALWAYS VISIBLE */
.accessibility-floating-button {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: #FD8B51;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    z-index: 99999;
    font-size: 28px;
    transition: all 0.3s;
}

.accessibility-floating-button:hover {
    transform: scale(1.1);
    background: #CB6040;
}

/* Floating Panel */
.accessibility-floating-panel {
    position: fixed;
    bottom: 100px;
    right: 30px;
    width: 320px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    z-index: 99999;
    display: none;
}

.panel-header {
    padding: 20px;
    background: #257180;
    color: white;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-header h3 {
    margin: 0;
    font-size: 18px;
}

.close-panel {
    background: none;
    border: none;
    color: white;
    font-size: 30px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

.panel-content {
    padding: 20px;
}

.option-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.option-row:last-child {
    border-bottom: none;
}

.option-row span:first-child {
    font-size: 15px;
    font-weight: 500;
    color: #333;
}

/* Simple Toggle Switch */
.switch {
    position: relative;
    width: 50px;
    height: 26px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ccc;
    border-radius: 26px;
    transition: 0.3s;
}

.switch-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}

.switch input:checked + .switch-slider {
    background: #FD8B51;
}

.switch input:checked + .switch-slider:before {
    transform: translateX(24px);
}

/* Font Controls */
.font-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.font-controls button {
    width: 35px;
    height: 35px;
    border: 2px solid #257180;
    background: white;
    border-radius: 50%;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    color: #257180;
}

.font-controls button:hover {
    background: #257180;
    color: white;
}

#fontSize {
    font-weight: bold;
    color: #257180;
    min-width: 50px;
    text-align: center;
}

/* High Contrast Mode */
body.high-contrast-mode {
    background: #000 !important;
    color: #fff !important;
}

body.high-contrast-mode .main-content,
body.high-contrast-mode .welcome-section,
body.high-contrast-mode .stat-card {
    background: #1a1a1a !important;
    color: #fff !important;
}

/* No Motion */
body.no-motion * {
    animation: none !important;
    transition: none !important;
}
</style>