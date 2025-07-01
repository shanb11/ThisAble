// Track selected job type
let selectedJobType = '';
        
function selectOption(selectedCard, jobType) {
    // Remove selected class from all cards
    document.querySelectorAll('.jobtype-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    selectedCard.classList.add('selected');
    
    // Store selected job type
    selectedJobType = jobType;
    
    // Store selection in local storage for persistence
    localStorage.setItem('preferredJobType', jobType);
}

// On page load, check if there's a previously selected option
document.addEventListener('DOMContentLoaded', () => {
    const savedJobType = localStorage.getItem('preferredJobType');
    if (savedJobType) {
        const cards = document.querySelectorAll('.jobtype-card');
        if (savedJobType === 'freelance') {
            cards[0].classList.add('selected');
        } else if (savedJobType === 'parttime') {
            cards[1].classList.add('selected');
        } else if (savedJobType === 'fulltime') {
            cards[2].classList.add('selected');
        }
        selectedJobType = savedJobType;
    }

    // Add this to each page's DOMContentLoaded event
    console.log("Current localStorage values:");
    console.log("Skills:", localStorage.getItem('selectedSkillsArray'));
    console.log("Work style:", localStorage.getItem('preferredWorkStyle'));
    console.log("Job type:", localStorage.getItem('preferredJobType'));
    console.log("Disability type:", localStorage.getItem('disabilityType'));
    console.log("Apparent needs:", localStorage.getItem('selectedApparentNeeds'));
    console.log("Non-apparent needs:", localStorage.getItem('selectedNonApparentNeeds'));
});


// Go to the next page and save data if possible
function goToNextPage() {
    if (!selectedJobType) {
        alert('Please select a job type preference before continuing.');
        return;
    }
    
    // Try to get seeker ID from various sources
    const seekerId = (typeof serverSeekerId !== 'undefined' && serverSeekerId) || 
                     localStorage.getItem('seekerId') || 
                     sessionStorage.getItem('seekerId');
    
    // Save selected job type to localStorage
    localStorage.setItem('preferredJobType', selectedJobType);
    
    // Log to verify
    console.log("Job type saved to localStorage:", selectedJobType);
    
    // Continue to next page
    window.location.href = "disabilitytype.php";
}

// Go back to the previous page
function goBack() {
    window.location.href = "workstyle.php";
}