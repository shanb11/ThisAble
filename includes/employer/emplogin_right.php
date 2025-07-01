<div class="right">
    <a href="../../index.php">
        <img src="../../images/thisablelogo.png" alt="Logo" class="logo">
    </a>
    <div class="login-box">
        <h2>Welcome Back</h2>
        
        <!-- Message Container for alerts -->
        <div id="message-container" style="display: none; margin-bottom: 15px;">
            <div id="alert-message" class="alert">
                <i class="fas fa-info-circle"></i>
                <span id="alert-text"></span>
            </div>
        </div>
        
        <div class="input-box">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" placeholder="Email" required>
        </div>

        <div class="input-box">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" placeholder="Password" required>
            <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <div class="checkbox-container">
            <input type="checkbox" id="remember-me">
            <label for="remember-me">Remember me</label>
        </div>

        <div class="forgot" id="forgotPasswordLink">Forgot Password?</div>

        <button class="login-btn" id="login-btn">
            <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
            Sign In
        </button>
        
        <div class="signup">New to ThisAble? <a href="#" id="signupLink" onclick="event.preventDefault(); document.getElementById('selectionModal').style.display='flex'; return false;">Sign up</a></div>
    </div>
</div>