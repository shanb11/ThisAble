<div class="right">
    <a href="../../index.php">
        <img src="../../images/thisablelogo.png" alt="Logo" class="logo">
    </a>
    <div class="login-box">
        <h2>Welcome Back</h2>
        
        <!-- Google Sign In Button -->
        <button class="google-btn" id="googleSignInBtn">
            <i class="fab fa-google google-icon"></i>
            Sign in with Google
        </button>
        
        <!-- Divider between Google and form -->
        <div class="divider">
            <span>OR</span>
        </div>
        
        <div class="input-box">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" placeholder="Email">
        </div>

        <div class="input-box">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" placeholder="Password">
            <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <div class="forgot" id="forgotPasswordLink">Forgot Password?</div>

        <button class="login-btn">Sign In</button>
        
        <div class="signup">New to ThisAble? <a href="#" id="signupLink" onclick="event.preventDefault(); document.getElementById('selectionModal').style.display='flex'; return false;">Sign up</a></div>    
        <div class="signup">Click here to <a href="<?php echo BASE_URL; ?>frontend/employer/emplogin.php" id="signupLink">Log in as employer</a></div>
    </div>
</div>