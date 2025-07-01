<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Company Description</title>
        <link rel="stylesheet" href="../../styles/employer/empdescription.css">
    </head>

    <body>
        <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">
        
        <div class="header">
            <h1>Company Description</h1>
            <p class="subtitle">Tell candidates about your company's mission, values, and what makes you unique.</p>
            
        </div>
        
        <div class="description-container">
            <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    About Us
                </h3>
                <div class="textarea-container">
                    <textarea id="about-us" placeholder="Tell candidates about your company, its history, size, and culture. Be specific about what your company does and what makes it special." maxlength="500"></textarea>
                    <div class="character-counter"><span id="about-us-count">0</span>/500</div>
                </div>
                <div class="error-message" id="about-us-error">This field is required</div>
                <p class="helper-text">Example: <span class="placeholder-text">TechForward is a leading software development company established in 2010. With over 200 employees globally, we create innovative solutions for healthcare and finance industries.</span></p>
            </div>
            
            <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 8V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Mission & Vision
                </h3>
                <div class="textarea-container">
                    <textarea id="mission-vision" placeholder="Share your company's mission, vision, and core values. What drives your company forward? What changes are you trying to make in the world?" maxlength="300"></textarea>
                    <div class="character-counter"><span id="mission-vision-count">0</span>/300</div>
                </div>
                <div class="error-message" id="mission-vision-error">This field is required</div>
                <p class="helper-text">Example: <span class="placeholder-text">Our mission is to make technology accessible to everyone. We envision a world where digital solutions enhance daily life for people of all abilities.</span></p>
            </div>
            
            <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.2 5.94C18.5839 6.30196 18.9 6.71717 19.1403 7.17156C19.3806 7.62596 19.542 8.1142 19.6182 8.61732C19.6944 9.12045 19.6842 9.63421 19.5882 10.1331C19.4921 10.632 19.3118 11.1088 19.0546 11.5546C20.1633 13.2937 20.4386 15.3669 19.8135 17.3006C19.1884 19.2344 17.715 20.8486 15.8222 21.7282C13.9295 22.6079 11.7683 22.6728 9.82251 21.9083C7.87668 21.1437 6.30668 19.6178 5.47482 17.6717C4.64296 15.7256 4.61754 13.5236 5.407 11.5508C6.19645 9.57805 7.73315 8.01162 9.65906 7.20167C11.585 6.39172 13.774 6.41988 15.6788 7.27728C16.1241 7.0196 16.6005 6.83884 17.099 6.74246C17.5974 6.64608 18.1108 6.63591 18.6139 6.71243C18.4787 6.46002 18.3585 6.19997 18.2539 5.93329L18.2 5.94Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Why Join Us
                </h3>
                <div class="textarea-container">
                    <textarea id="why-join" placeholder="Explain what makes your company a great place to work. Mention benefits, growth opportunities, workplace culture, and what sets you apart from other employers." maxlength="400"></textarea>
                    <div class="character-counter"><span id="why-join-count">0</span>/400</div>
                </div>
                <div class="error-message" id="why-join-error">This field is required</div>
                <p class="helper-text">Example: <span class="placeholder-text">We offer competitive benefits, flexible work arrangements, and continuous learning opportunities. Our inclusive culture encourages innovation and personal growth in a supportive environment.</span></p>
            </div>
        </div>
        
        <div class="nav-buttons">
            <button class="back-btn" onclick="goBack()">Back</button>
            <button class="continue-btn" onclick="continueToNext()">Continue</button>
        </div>

        <script src="../../scripts/employer/empdescription.js"></script>

    </body>
</html>