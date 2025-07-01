// Store selected needs
const selectedNeeds = new Set();
let noNeedsSelected = false;

// Toggle selection of a need card
function toggleSelection(card) {
    // If "no needs" is selected, unselect it first
    if (noNeedsSelected) {
        const noNeedsOption = document.querySelector('.no-needs-option');
        noNeedsOption.classList.remove('selected');
        noNeedsSelected = false;
    }
    
    // Toggle the selected class
    card.classList.toggle('selected');
    
    // Get the need title
    const needTitle = card.querySelector('.need-title').textContent;
    
    // Update the selected needs set
    if (card.classList.contains('selected')) {
        selectedNeeds.add(needTitle);
    } else {
        selectedNeeds.delete(needTitle);
    }
    
    // Store in local storage
    localStorage.setItem('selectedApparentNeeds', JSON.stringify([...selectedNeeds]));
}

// Toggle "No needs" option
function toggleNoNeeds(option) {
    option.classList.toggle('selected');
    noNeedsSelected = option.classList.contains('selected');
    
    // If selecting "no needs", unselect all other needs
    if (noNeedsSelected) {
        document.querySelectorAll('.need-card').forEach(card => {
            card.classList.remove('selected');
        });
        selectedNeeds.clear();
        localStorage.setItem('selectedApparentNeeds', JSON.stringify([]));
        localStorage.setItem('noNeedsSelected', 'true');
    } else {
        localStorage.setItem('noNeedsSelected', 'false');
    }
}

// On page load, restore previously selected options
document.addEventListener('DOMContentLoaded', () => {
    // Restore selected needs
    try {
        const savedNeeds = JSON.parse(localStorage.getItem('selectedApparentNeeds')) || [];
        savedNeeds.forEach(need => {
            const cards = document.querySelectorAll('.need-card');
            cards.forEach(card => {
                const cardTitle = card.querySelector('.need-title').textContent;
                if (cardTitle === need) {
                    card.classList.add('selected');
                    selectedNeeds.add(need);
                }
            });
        });
        
        // Restore "no needs" selection
        if (localStorage.getItem('noNeedsSelected') === 'true') {
            const noNeedsOption = document.querySelector('.no-needs-option');
            noNeedsOption.classList.add('selected');
            noNeedsSelected = true;
        }
    } catch (error) {
        console.error('Error restoring saved needs:', error);
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

// Go to next page
function goToNextPage() {
    // Save needs to localStorage (already done in toggleSelection)
    console.log("Selected apparent needs:", Array.from(selectedNeeds));
    
    // Continue to next page
    window.location.href = "uploadresume.php";
}

// Go back to previous page
function goBack() {
    window.location.href = "disabilitytype.php";
}