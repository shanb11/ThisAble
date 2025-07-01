<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload Company Logo</title>
        <link rel="stylesheet" href="../../styles/employer/empuploadlogo.css">
    </head>
    <body>

        <img src="../../images/thisablelogo.png" alt="ThisAble Logo" class="logo">
        
        <div class="header">
            <h1>Upload Your Company Logo</h1>
            <p class="subtitle">Add your company's logo to complete your profile and help candidates recognize your brand.</p>

        </div>
        
        <div class="upload-container">
            <label for="file-input" class="upload-area" id="drop-area">
                <svg class="upload-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <h3 class="upload-title">Upload Company Logo <span class="optional-tag">Optional</span></h3>
                <p class="upload-desc">Drag and drop your logo here or click to browse files</p>
                <p class="file-types">Supported formats: PNG, JPG, SVG (max. 5MB)</p>
                <button type="button" class="select-file-btn">Select File</button>
            </label>
            <input type="file" id="file-input" accept=".jpg,.jpeg,.png,.svg">
            
            <div class="preview-container" id="preview-container">
                <img id="logo-preview" src="" alt="Logo Preview">
                <button type="button" class="remove-btn" id="remove-btn">Remove Logo</button>
            </div>
        </div>
        
        <div class="nav-buttons">
            <button class="back-btn" onclick="goBack()">Back</button>
            <button class="continue-btn" onclick="continueToNext()">Continue</button>
        </div>

        <script src="../../scripts/employer/empuploadlogo.js"></script>
            
        </script>
    </body>
</html>