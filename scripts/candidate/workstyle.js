// Track selected work style
let selectedWorkStyle = '';
        
function selectOption(selectedCard, workStyle) {
    // Remove selected class from all cards
    document.querySelectorAll('.workstyle-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    selectedCard.classList.add('selected');
    
    // Store selected work style
    selectedWorkStyle = workStyle;
    
    // Store selection in local storage for persistence
    localStorage.setItem('preferredWorkStyle', workStyle);
}

// On page load, check if there's a previously selected option
document.addEventListener('DOMContentLoaded', () => {
    const savedWorkStyle = localStorage.getItem('preferredWorkStyle');
    if (savedWorkStyle) {
        const cards = document.querySelectorAll('.workstyle-card');
        if (savedWorkStyle === 'remote') {
            cards[0].classList.add('selected');
        } else if (savedWorkStyle === 'hybrid') {
            cards[1].classList.add('selected');
        } else if (savedWorkStyle === 'onsite') {
            cards[2].classList.add('selected');
        }
        selectedWorkStyle = savedWorkStyle;
    }
});

// Go to the next page and save data if possible
function goToNextPage() {
    if (!selectedWorkStyle) {
        alert('Please select a work style preference before continuing.');
        return;
    }
    
    // Try to get seeker ID from various sources
    const seekerId = (typeof serverSeekerId !== 'undefined' && serverSeekerId) || 
                     localStorage.getItem('seekerId') || 
                     sessionStorage.getItem('seekerId');
    
    if (seekerId) {
        // Show saving indicator
        const saveIndicator = document.createElement('div');
        saveIndicator.classList.add('save-indicator');
        saveIndicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving your preference...';
        document.body.appendChild(saveIndicator);
        
        // Prepare data for submission
        const formData = new FormData();
        formData.append('seeker_id', seekerId);
        formData.append('work_style', selectedWorkStyle);
        
        // Send data to server
        fetch('../../backend/candidate/save_workstyle.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.body.removeChild(saveIndicator);
            
            if (data.success) {
                console.log('Work style preference saved successfully!');
                window.location.href = "jobtype.php";
            } else {
                console.error('Error saving preference:', data.message);
                // Continue anyway, as we saved to localStorage
                window.location.href = "jobtype.php";
            }
        })
        .catch(error => {
            document.body.removeChild(saveIndicator);
            console.error('Error:', error);
            // Continue anyway, as we saved to localStorage
            window.location.href = "jobtype.php";
        });
    } else {
        // If not logged in, just navigate
        console.log('User not logged in, preference saved to localStorage only');
        window.location.href = "jobtype.php";
    }
}

// Go back to the previous page
function goBack() {
    window.location.href = "skillselection.php";
}