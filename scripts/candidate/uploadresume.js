const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');
let currentFile = null;

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
    document.body.addEventListener(eventName, preventDefaults, false);
});

// Highlight drop zone when dragging over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

// Handle dropped files
dropZone.addEventListener('drop', handleDrop, false);

// Handle file input change
fileInput.addEventListener('change', handleFiles);

function preventDefaults (e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight(e) {
    dropZone.classList.add('dragover');
}

function unhighlight(e) {
    dropZone.classList.remove('dragover');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    handleFiles({ target: { files: files } });
}

function handleFiles(e) {
    const file = e.target.files[0];
    if (file) {
        const fileType = file.type;
        const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!validTypes.includes(fileType)) {
            alert('Please upload a PDF, DOC, or DOCX file');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }

        currentFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.classList.add('show');
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removeFile() {
    currentFile = null;
    fileInput.value = '';
    fileInfo.classList.remove('show');
}

// FIXED: Click anywhere in drop zone to trigger file input - but prevent double triggers
dropZone.addEventListener('click', (e) => {
    // Only trigger if clicking directly on drop zone, not on child elements like buttons
    if (e.target === dropZone || e.target.closest('.upload-text') || e.target.closest('.upload-icon')) {
        fileInput.click();
    }
});

// Determine the previous page based on disability type
document.addEventListener('DOMContentLoaded', () => {
    // Set the previous page based on disability type in localStorage
    const disabilityType = localStorage.getItem('disabilityType');
    if (disabilityType === 'apparent') {
        window.previousPage = 'apparent-workplaceneeds.php';
    } else if (disabilityType === 'non-apparent') {
        window.previousPage = 'non-apparent-workplaceneeds.php';
    } else {
        // Default fallback
        window.previousPage = 'disabilitytype.php';
    }
    
    // Check for previously saved file info
    const savedFileName = localStorage.getItem('uploadedFileName');
    const savedFileSize = localStorage.getItem('uploadedFileSize');
    
    if (savedFileName && savedFileSize) {
        fileName.textContent = savedFileName;
        fileSize.textContent = savedFileSize;
        fileInfo.classList.add('show');
    }

    // Debug localStorage values
    console.log("Current localStorage values:");
    console.log("Skills:", localStorage.getItem('selectedSkillsArray'));
    console.log("Work style:", localStorage.getItem('preferredWorkStyle'));
    console.log("Job type:", localStorage.getItem('preferredJobType'));
    console.log("Disability type:", localStorage.getItem('disabilityType'));
    console.log("Apparent needs:", localStorage.getItem('selectedApparentNeeds'));
    console.log("Non-apparent needs:", localStorage.getItem('selectedNonApparentNeeds'));
});

// Navigate back to the appropriate previous page
function goBack() {
    window.location.href = window.previousPage || 'disabilitytype.php';
}

// Simple function for testing server connection
function testServerConnection() {
    fetch('../../backend/candidate/test_connection.php')
        .then(response => response.json())
        .then(data => {
            alert('Server connection test successful!');
            console.log('Response:', data);
        })
        .catch(error => {
            alert('Error connecting to server: ' + error);
            console.error('Error details:', error);
        });
}

// FIXED: Main function to proceed to next page
function goToNextPage() {
    console.log('=== UPLOAD RESUME DEBUG START ===');
    
    // Get seeker ID from multiple sources
    const seekerId = (typeof serverSeekerId !== 'undefined' && serverSeekerId) || 
                     localStorage.getItem('seekerId') || 
                     sessionStorage.getItem('seekerId');
    
    console.log('Seeker ID sources:');
    console.log('- serverSeekerId:', typeof serverSeekerId !== 'undefined' ? serverSeekerId : 'undefined');
    console.log('- localStorage:', localStorage.getItem('seekerId'));
    console.log('- sessionStorage:', sessionStorage.getItem('seekerId'));
    console.log('- Final seekerId:', seekerId);
    
    if (!seekerId) {
        alert("Session information is missing. Please login again.");
        window.location.href = "login.php";
        return;
    }
    
    // Create and show saving indicator
    const saveIndicator = document.createElement('div');
    saveIndicator.style.position = 'fixed';
    saveIndicator.style.top = '0';
    saveIndicator.style.left = '0';
    saveIndicator.style.width = '100%';
    saveIndicator.style.height = '100%';
    saveIndicator.style.backgroundColor = 'rgba(0,0,0,0.8)';
    saveIndicator.style.display = 'flex';
    saveIndicator.style.flexDirection = 'column';
    saveIndicator.style.justifyContent = 'center';
    saveIndicator.style.alignItems = 'center';
    saveIndicator.style.zIndex = '9999';
    saveIndicator.style.color = 'white';
    saveIndicator.innerHTML = `
        <div style="text-align: center;">
            <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 20px;"></i>
            <h2>Setting up your account...</h2>
            <p>Please wait while we finalize your profile</p>
        </div>
    `;
    document.body.appendChild(saveIndicator);
    
    // Handle resume upload if a file is selected
    if (currentFile) {
        console.log('File selected for upload:', currentFile.name);
        saveIndicator.querySelector('p').textContent = 'Uploading resume...';
        
        const resumeFormData = new FormData();
        resumeFormData.append('seeker_id', seekerId);
        resumeFormData.append('resume_file', currentFile);
        
        console.log('FormData contents:');
        for (let pair of resumeFormData.entries()) {
            console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
        }
        
        // FIXED: Use absolute path and better error handling
        const uploadUrl = apiPath('backend/candidate/upload_resume_process.php');
        console.log('Upload URL:', uploadUrl);
        
        fetch(uploadUrl, {
            method: 'POST',
            body: resumeFormData
        })
        .then(response => {
            console.log('Upload response status:', response.status);
            console.log('Upload response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
            }
            
            return response.text(); // Get as text first for debugging
        })
        .then(text => {
            console.log('Raw upload response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed upload response:', data);
                
                if (!data.success) {
                    console.error("Resume upload failed:", data.message);
                    // Continue anyway - don't stop the process for resume upload failure
                } else {
                    console.log("Resume upload successful");
                    // Save resume info to localStorage
                    localStorage.setItem('uploadedFileName', currentFile.name);
                    localStorage.setItem('uploadedFileSize', formatFileSize(currentFile.size));
                }
                
                // Continue with profile data saving regardless of resume upload result
                saveProfileData(seekerId, saveIndicator);
                
            } catch (e) {
                console.error('JSON parse error for upload response:', e);
                console.error('Response text was:', text);
                // Continue with profile data saving even if resume upload fails
                saveProfileData(seekerId, saveIndicator);
            }
        })
        .catch(error => {
            console.error("Resume upload error:", error);
            // Continue with profile data saving even if resume upload fails
            console.log("Continuing with profile data saving despite resume upload error");
            saveProfileData(seekerId, saveIndicator);
        });
    } else {
        // No resume file selected - proceed directly to save profile data
        console.log("No resume file selected, proceeding with profile data only");
        saveProfileData(seekerId, saveIndicator);
    }
}

// FIXED: Updated saveProfileData function with better error handling
function saveProfileData(seekerId, saveIndicator) {
    saveIndicator.querySelector('p').textContent = 'Saving profile data...';
    
    // Create form data with all setup information from localStorage
    const formData = new FormData();
    formData.append('seeker_id', seekerId);
    
    // Add skills
    try {
        const skillsStr = localStorage.getItem('selectedSkillsArray');
        if (skillsStr) {
            formData.append('skills', skillsStr);
            console.log("Skills added to form:", skillsStr);
        }
    } catch (e) {
        console.error("Error with skills:", e);
    }
    
    // Add work style
    const workStyle = localStorage.getItem('preferredWorkStyle');
    if (workStyle) {
        formData.append('work_style', workStyle);
        console.log("Work style added:", workStyle);
    }
    
    // Add job type
    const jobType = localStorage.getItem('preferredJobType');
    if (jobType) {
        formData.append('job_type', jobType);
        console.log("Job type added:", jobType);
    }
    
    // Add disability type and accommodations
    const disabilityType = localStorage.getItem('disabilityType');
    if (disabilityType) {
        formData.append('disability_type', disabilityType);
        console.log("Disability type added:", disabilityType);
        
        // Determine which accommodation list to use
        let needsList = '';
        let noNeedsSelected = 'false';
        
        if (disabilityType === 'apparent') {
            needsList = localStorage.getItem('selectedApparentNeeds') || '[]';
            noNeedsSelected = localStorage.getItem('noNeedsSelected') || 'false';
        } else if (disabilityType === 'non-apparent') {
            needsList = localStorage.getItem('selectedNonApparentNeeds') || '[]';
            noNeedsSelected = localStorage.getItem('noNeedsSelectedNonApparent') || 'false';
        }
        
        formData.append('accommodation_list', needsList);
        formData.append('no_accommodations_needed', noNeedsSelected === 'true' ? '1' : '0');
        console.log("Accommodations added:", needsList);
        console.log("No needs:", noNeedsSelected);
    }
    
    // FIXED: Use absolute path for save_setup_data
    const saveUrl = apiPath('backend/candidate/save_setup_data.php');
    console.log('Save data URL:', saveUrl);
    
    // Send the data to the server
    fetch(saveUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Save response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        
        return response.text(); // Get as text first for debugging
    })
    .then(text => {
        console.log("Raw save response:", text);
        
        try {
            const data = JSON.parse(text);
            console.log("Parsed save response:", data);
            
            // Remove the saving indicator
            document.body.removeChild(saveIndicator);
            
            if (data && data.success) {
                // Mark setup as complete in localStorage
                localStorage.setItem('setupComplete', 'true');
                localStorage.setItem('accountSetupComplete', 'true');
                
                console.log("Setup completed successfully, navigating to dashboard");
                // Navigate to dashboard
                window.location.href = 'dashboard.php';
            } else {
                console.error("Profile data save failed:", data);
                alert("Error saving profile data: " + (data ? data.message : "Unknown error"));
            }
        } catch (e) {
            console.error("JSON parse error for save response:", e);
            console.error("Response text was:", text);
            document.body.removeChild(saveIndicator);
            alert("Server returned an invalid response. Please try again.");
        }
    })
    .catch(error => {
        console.error("Error during profile data save:", error);
        document.body.removeChild(saveIndicator);
        alert("An error occurred while saving your profile: " + error.message);
    });
    
    console.log('=== UPLOAD RESUME DEBUG END ===');
}

// test
// Add this function to your uploadresume.js for testing
// You can call this from browser console: testSetupConnection()

function testSetupConnection() {
    console.log('=== TESTING SETUP CONNECTION ===');
    
    const testUrl = window.location.origin + '/ThisAble/backend/candidate/test_setup_connection.php';
    console.log('Testing URL:', testUrl);
    
    fetch(testUrl, {
        method: 'GET'
    })
    .then(response => {
        console.log('Test response status:', response.status);
        console.log('Test response headers:', response.headers);
        return response.text();
    })
    .then(text => {
        console.log('Test raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Test parsed response:', data);
            
            if (data.success) {
                alert('✅ Connection test PASSED!\nDatabase connected: ' + data.database_connected + '\nJob seekers count: ' + data.job_seekers_count);
            } else {
                alert('❌ Connection test FAILED!\nError: ' + data.message);
            }
        } catch (e) {
            console.error('Test JSON parse error:', e);
            alert('❌ Server returned invalid response: ' + text.substring(0, 200));
        }
    })
    .catch(error => {
        console.error('Test connection error:', error);
        alert('❌ Connection test error: ' + error.message);
    });
}

// Add this to test specific seeker data
function testSaveSetupData() {
    console.log('=== TESTING SAVE SETUP DATA ===');
    
    const seekerId = (typeof serverSeekerId !== 'undefined' && serverSeekerId) || 
                     localStorage.getItem('seekerId') || 
                     sessionStorage.getItem('seekerId') ||
                     prompt('Enter seeker ID for testing:');
    
    if (!seekerId) {
        alert('No seeker ID found for testing');
        return;
    }
    
    const testFormData = new FormData();
    testFormData.append('seeker_id', seekerId);
    testFormData.append('skills', JSON.stringify(['Digital Literacy', 'Data Entry']));
    testFormData.append('work_style', 'remote');
    testFormData.append('job_type', 'fulltime');
    testFormData.append('disability_type', 'apparent');
    testFormData.append('accommodation_list', JSON.stringify(['Wheelchair Accessible']));
    testFormData.append('no_accommodations_needed', '0');
    
    const saveUrl = window.location.origin + '/ThisAble/backend/candidate/save_setup_data.php';
    console.log('Save test URL:', saveUrl);
    
    fetch(saveUrl, {
        method: 'POST',
        body: testFormData
    })
    .then(response => {
        console.log('Save test response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Save test raw response:', text);
        try {
            const data = JSON.parse(text);
            console.log('Save test parsed response:', data);
            
            if (data.success) {
                alert('✅ Save setup test PASSED!\nMessage: ' + data.message);
            } else {
                alert('❌ Save setup test FAILED!\nError: ' + data.message);
            }
        } catch (e) {
            console.error('Save test JSON parse error:', e);
            alert('❌ Save test returned invalid response: ' + text.substring(0, 200));
        }
    })
    .catch(error => {
        console.error('Save test error:', error);
        alert('❌ Save test error: ' + error.message);
    });
}