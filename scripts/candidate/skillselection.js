// Store skills from database
let skillsData = [];

const skillsContainer = document.getElementById("skillsContainer");
const selectedSkills = document.getElementById("selectedSkills");
const searchSkill = document.getElementById("searchSkill");
const categoryTabs = document.getElementById("categoryTabs");
const skillCount = document.getElementById("skillCount");

let currentCategory = "all";
let selectedSkillsArray = [];

// Initialize page by fetching skills from database
document.addEventListener('DOMContentLoaded', () => {
    fetchSkills();
    
    // Log session and storage state for debugging
    console.log('Server Seeker ID:', typeof serverSeekerId, serverSeekerId);
    console.log('User Logged In:', userLoggedIn);
    console.log('localStorage seekerId:', localStorage.getItem('seekerId'));
    console.log('sessionStorage seekerId:', sessionStorage.getItem('seekerId'));
});

// Function to fetch skills from the database
function fetchSkills() {
    // Show loading indicator
    skillsContainer.innerHTML = `
        <div class="loading-indicator">
            <i class="fas fa-spinner fa-spin"></i> Loading skills...
        </div>
    `;
    
    fetch('../../backend/candidate/get_skills.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                skillsContainer.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        Error: ${data.error}
                    </div>
                `;
                console.error('API error:', data.error);
                return;
            }
            
            if (data.length === 0) {
                skillsContainer.innerHTML = `
                    <div class="no-skills-message">
                        No skills found in the database. Please add skills first.
                    </div>
                `;
                return;
            }
            
            skillsData = data;
            renderSkills();
            
            // Restore previously selected skills
            try {
                const savedSkills = JSON.parse(localStorage.getItem('selectedSkillsArray') || '[]');
                selectedSkillsArray = savedSkills;
                
                savedSkills.forEach(skill => {
                    const skillData = skillsData.find(s => s.name === skill);
                    if (skillData) {
                        addSkillTag(skillData);
                    }
                });
                
                updateSkillCount();
            } catch (error) {
                console.error('Error restoring saved skills:', error);
            }
            
            // Set up event listeners
            setupEventListeners();
        })
        .catch(error => {
            console.error('Error fetching skills:', error);
            skillsContainer.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    Error connecting to the server. Please try again later.
                </div>
                <div class="error-details">
                    ${error.message}
                </div>
            `;
        });
}

// Set up event listeners for category tabs and search
function setupEventListeners() {
    // Handle category tab clicks
    categoryTabs.addEventListener("click", (e) => {
        const tab = e.target.closest(".category-tab");
        if (!tab) return;
        
        // Update active tab
        document.querySelectorAll(".category-tab").forEach(t => t.classList.remove("active"));
        tab.classList.add("active");
        
        // Set current category and re-render skills
        currentCategory = tab.dataset.category;
        renderSkills(searchSkill.value);
    });

    // Handle search input
    searchSkill.addEventListener("input", (e) => {
        renderSkills(e.target.value);
    });
}

// Render skills based on current category and search term
function renderSkills(searchTerm = "") {
    skillsContainer.innerHTML = "";
    
    skillsData.forEach(skill => {
        // Filter by category and search term
        if ((currentCategory === "all" || skill.category === currentCategory) && 
            (searchTerm === "" || skill.name.toLowerCase().includes(searchTerm.toLowerCase()))) {
            
            const isSelected = selectedSkillsArray.includes(skill.name);
            
            // Create skill card
            const skillCard = document.createElement("div");
            skillCard.classList.add("skill-card");
            if (isSelected) skillCard.classList.add("selected");
            
            skillCard.innerHTML = `
                <i class="fas ${skill.icon} skill-icon"></i>
                <div class="skill-name">${skill.name}</div>
                <div class="skill-tooltip">${skill.tooltip}</div>
            `;
            
            skillCard.addEventListener("click", () => toggleSkill(skill));
            skillsContainer.appendChild(skillCard);
        }
    });
}

// Toggle selection of a skill
function toggleSkill(skill) {
    const index = selectedSkillsArray.indexOf(skill.name);
    
    if (index === -1) {
        // Add skill
        selectedSkillsArray.push(skill.name);
        addSkillTag(skill);
    } else {
        // Remove skill
        selectedSkillsArray.splice(index, 1);
        removeSkillTag(skill.name);
    }
    
    renderSkills(searchSkill.value);
    updateSkillCount();
    
    // Save to localStorage
    localStorage.setItem('selectedSkillsArray', JSON.stringify(selectedSkillsArray));
}

// Add a selected skill tag
function addSkillTag(skill) {
    const skillTag = document.createElement("div");
    skillTag.classList.add("selected-skill");
    skillTag.innerHTML = `
        ${skill.name}
        <div class="remove-skill" onclick="removeSkill('${skill.name}')">×</div>
    `;
    selectedSkills.appendChild(skillTag);
}

// Remove a skill tag from the UI
function removeSkillTag(skillName) {
    const skillTags = selectedSkills.querySelectorAll(".selected-skill");
    skillTags.forEach(tag => {
        if (tag.textContent.trim().includes(skillName)) {
            tag.remove();
        }
    });
}

// Remove a skill when clicking the X
function removeSkill(skillName) {
    const index = selectedSkillsArray.indexOf(skillName);
    if (index !== -1) {
        selectedSkillsArray.splice(index, 1);
        removeSkillTag(skillName);
        renderSkills(searchSkill.value);
        updateSkillCount();
        
        // Update localStorage
        localStorage.setItem('selectedSkillsArray', JSON.stringify(selectedSkillsArray));
    }
}

// Clear all selected skills
function clearSelection() {
    selectedSkillsArray = [];
    selectedSkills.innerHTML = "";
    renderSkills(searchSkill.value);
    updateSkillCount();
    
    // Update localStorage
    localStorage.setItem('selectedSkillsArray', JSON.stringify([]));
}

// Update skill count display
function updateSkillCount() {
    skillCount.textContent = `(${selectedSkillsArray.length})`;
}

// Get seeker ID from all possible sources, with better logging
function getSeekerId() {
    // Try all possible sources with detailed logging
    let seekerId = null;
    
    if (typeof serverSeekerId !== 'undefined' && serverSeekerId) {
        console.log('Using serverSeekerId:', serverSeekerId);
        seekerId = serverSeekerId;
    } else if (localStorage.getItem('seekerId')) {
        console.log('Using localStorage seekerId:', localStorage.getItem('seekerId'));
        seekerId = localStorage.getItem('seekerId');
    } else if (sessionStorage.getItem('seekerId')) {
        console.log('Using sessionStorage seekerId:', sessionStorage.getItem('seekerId'));
        seekerId = sessionStorage.getItem('seekerId');
    } else {
        console.warn('No seeker ID found in any storage location');
    }
    
    return seekerId;
}

// Save skills to database if user is logged in, then navigate
function saveSelectedSkillsToDatabase() {
    // Always save to localStorage first
    localStorage.setItem('selectedSkillsArray', JSON.stringify(selectedSkillsArray));
    
    // Get seeker ID with better error handling
    const seekerId = getSeekerId();
    
    if (!seekerId) {
        console.warn('No seeker ID available, skills saved to localStorage only');
        window.location.href = "workstyle.php";
        return;
    }
    
    // Show saving indicator
    const saveIndicator = document.createElement('div');
    saveIndicator.classList.add('save-indicator');
    saveIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving your skills...';
    document.body.appendChild(saveIndicator);
    
    // Prepare data for submission
    const formData = new FormData();
    formData.append('seeker_id', seekerId);
    formData.append('skills', JSON.stringify(selectedSkillsArray));
    
    // Log the data being sent
    console.log('Sending seeker_id:', seekerId);
    console.log('Sending skills:', selectedSkillsArray);
    
    // Send data to server
    fetch('../../backend/candidate/save_skills.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        document.body.removeChild(saveIndicator);
        
        if (data.success) {
            console.log('Skills saved successfully!');
            window.location.href = "workstyle.php";
        } else {
            console.error('Error saving skills:', data.message);
            alert('There was an error saving your skills: ' + (data.message || 'Unknown error') + '\n\nYour progress has been saved locally and we\'ll try again later.');
            window.location.href = "workstyle.php";
        }
    })
    .catch(error => {
        document.body.removeChild(saveIndicator);
        console.error('Fetch Error:', error);
        alert('There was an error connecting to the server: ' + error.message + '\n\nYour progress has been saved locally and we\'ll try again later.');
        window.location.href = "workstyle.php";
    });
}

// Update goToNextPage() function
function goToNextPage() {
    // Always save to localStorage
    localStorage.setItem('selectedSkillsArray', JSON.stringify(selectedSkillsArray));
    
    // Try to save to database if possible
    const seekerId = getSeekerId();
    
    if (seekerId) {
        saveSelectedSkillsToDatabase();
    } else {
        // Just navigate if no seeker ID available
        console.log('No seeker ID available, skills saved to localStorage only');
        window.location.href = "workstyle.php";
    }
}

// Navigate to account setup
function goToAccountSetup() {
    window.location.href = "accountsetup.php";
}