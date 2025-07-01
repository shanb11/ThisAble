<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inclusive Hiring Preferences</title>
    <link rel="stylesheet" href="../../styles/employer/emppreferences.css">

</head>
<body>

    <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">
    
    <div class="header">
        <h1>Inclusive Hiring Preferences</h1>
        <p class="subtitle">Specify the types of disabilities you can accommodate and the accessibility options available at your workplace.</p>

    </div>
    
    <div class="preferences-container">
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 4H8a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V6a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 11c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM16 18v-2a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Accommodation Type
            </h3>
            <div class="disability-type-selector">
                <label class="type-option">
                    <input type="radio" name="disability-type" value="apparent" checked>
                    Apparent Disabilities
                </label>
                <label class="type-option">
                    <input type="radio" name="disability-type" value="non-apparent">
                    Non-Apparent Disabilities
                </label>
                <label class="type-option">
                    <input type="radio" name="disability-type" value="both">
                    Both Types
                </label>
            </div>
            <p class="helper-text">Select the type of disabilities your organization is equipped to accommodate.</p>
        </div>
        
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Types of Disabilities You Can Accommodate
            </h3>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="mobility" name="disabilities">
                    <label for="mobility">Mobility Impairments</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="visual" name="disabilities">
                    <label for="visual">Visual Impairments</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="hearing" name="disabilities">
                    <label for="hearing">Hearing Impairments</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="cognitive" name="disabilities">
                    <label for="cognitive">Cognitive Disabilities</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="speech" name="disabilities">
                    <label for="speech">Speech Impairments</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="chronic" name="disabilities">
                    <label for="chronic">Chronic Illness</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="neurological" name="disabilities">
                    <label for="neurological">Neurological Conditions</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="mental-health" name="disabilities">
                    <label for="mental-health">Mental Health Conditions</label>
                </div>
            </div>
            <div class="error-message" id="disabilities-error">Please select at least one type of disability</div>
        </div>
        
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 11H5a2 2 0 00-2 2v6a2 2 0 002 2h14a2 2 0 002-2v-6a2 2 0 00-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17 7l-5-5-5 5M12 16v-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Workplace Accessibility Options
            </h3>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="ramps" name="accessibility">
                    <label for="ramps">Wheelchair Ramps/Elevators</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="restrooms" name="accessibility">
                    <label for="restrooms">Accessible Restrooms</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="parking" name="accessibility">
                    <label for="parking">Accessible Parking</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="workstations" name="accessibility">
                    <label for="workstations">Adjustable Workstations</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="software" name="accessibility">
                    <label for="software">Assistive Technology/Software</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="signage" name="accessibility">
                    <label for="signage">Braille/Audio Signage</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="interpreters" name="accessibility">
                    <label for="interpreters">Sign Language Interpreters</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="flexible" name="accessibility">
                    <label for="flexible">Flexible Work Arrangements</label>
                </div>
            </div>
            <div class="error-message" id="accessibility-error">Please select at least one accessibility option</div>
        </div>
        
        <div class="section">
            <h3 class="section-title">
                <svg class="section-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Additional Accommodations
            </h3>
            <div class="textarea-container">
                <textarea id="additional-accommodations" placeholder="Describe any additional accommodations, resources, or support your company can offer to employees with disabilities." maxlength="400"></textarea>
                <div class="character-counter"><span id="accommodations-count">0</span>/400</div>
            </div>
            <p class="helper-text">Example: <span style="color: #aaa; font-style: italic;">We offer mentorship programs specifically for employees with disabilities, emergency evacuation plans for individuals with mobility impairments, and accessibility training for all staff members.</span></p>
        </div>
    </div>
    
    <div class="nav-buttons">
        <button class="back-btn" onclick="goBack()">Back</button>
        <button class="continue-btn" onclick="continueToNext()">Continue</button>
    </div>

    <script src="../../scripts/employer/emppreferences.js">

    </script>
</body>
</html>