<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Website & Social Links</title>
    <link rel="stylesheet" href="../../styles/employer/empsocmedlinks.css">

</head>
<body>

    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">
    
    <div class="header">
        <h1>Company Website & Social Links</h1>
        <p class="subtitle">Add your company's website and social media profiles to enhance your presence and help candidates learn more about your organization.</p>

    </div>
    
    <div class="links-container">
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12H22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 2C14.5013 4.73835 15.9228 8.29203 16 12C15.9228 15.708 14.5013 19.2616 12 22C9.49872 19.2616 8.07725 15.708 8 12C8.07725 8.29203 9.49872 4.73835 12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Company Website
            </h3>
            <div class="input-group">
                <label class="input-label" for="company-url">Company Website URL</label>
                <input type="url" id="company-url" class="input-field" placeholder="https://example.com">
                <div class="error-message" id="company-url-error">Please enter a valid URL (include https://)</div>
                <p class="helper-text">Enter the full URL including 'https://'</p>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8C19.6569 8 21 6.65685 21 5C21 3.34315 19.6569 2 18 2C16.3431 2 15 3.34315 15 5C15 6.65685 16.3431 8 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 15C7.65685 15 9 13.6569 9 12C9 10.3431 7.65685 9 6 9C4.34315 9 3 10.3431 3 12C3 13.6569 4.34315 15 6 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 22C19.6569 22 21 20.6569 21 19C21 17.3431 19.6569 16 18 16C16.3431 16 15 17.3431 15 19C15 20.6569 16.3431 22 18 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.59 13.51L15.42 17.49" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.41 6.51L8.59 10.49" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Social Media Profiles <span class="optional-badge">Optional</span>
            </h3>
            <div class="social-links">
                <div class="social-input">
                    <label class="input-label" for="facebook-url">Facebook</label>
                    <div class="prefix">
                        <span class="prefix-text">facebook.com/</span>
                        <input type="text" id="facebook-url" class="prefix-input" placeholder="company.name">
                    </div>
                    <div class="error-message" id="facebook-url-error">Please enter a valid username</div>
                </div>
                
                <div class="social-input">
                    <label class="input-label" for="linkedin-url">LinkedIn</label>
                    <div class="prefix">
                        <span class="prefix-text">linkedin.com/company/</span>
                        <input type="text" id="linkedin-url" class="prefix-input" placeholder="company-name">
                    </div>
                    <div class="error-message" id="linkedin-url-error">Please enter a valid company name</div>
                </div>
            </div>
            <p class="helper-text">Add your social media profiles to help candidates learn more about your company culture and latest updates.</p>
        </div>
    </div>
    
    <div class="nav-buttons">
        <button class="back-btn" onclick="goBack()">Back</button>
        <button class="continue-btn" onclick="continueToNext()">Continue</button>
    </div>

    <script src="../../scripts/employer/empsocmedlinks.js"> </script>
</body>
</html>