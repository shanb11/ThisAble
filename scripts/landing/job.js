console.log("=== JOB.JS LOADED - CLEAN VERSION ===");

// Real jobs data - will be populated by AJAX
let jobsData = [];
let categoryStats = {};

// Fetch real jobs from database
async function fetchRealJobs() {
    try {
        console.log('Fetching jobs from database...');
        
        const response = await fetch('/thisable/backend/landing/get_landing_jobs.php');
        const data = await response.json();
        
        console.log('Response from backend:', data);
        
        if (data.success) {
            jobsData = data.jobs || [];
            categoryStats = data.categories || {};
            
            console.log('Loaded ' + jobsData.length + ' jobs');
            
            // Update category counts on page
            updateCategoryCounts();
        } else {
            console.error('Backend returned error:', data.message);
            jobsData = [];
            categoryStats = {};
        }
        
    } catch (error) {
        console.error('Error fetching jobs:', error);
        jobsData = [];
        categoryStats = {};
    }
}

// Update category counts in the UI
function updateCategoryCounts() {
    const categories = {
        'education': 'Education & Training',
        'office': 'Office Administration', 
        'customer': 'Customer Service',
        'business': 'Business Administration',
        'healthcare': 'Healthcare & Wellness',
        'finance': 'Finance & Accounting'
    };
    
    Object.keys(categories).forEach(categoryKey => {
        const count = categoryStats[categoryKey] || 0;
        const categoryItem = document.querySelector(`[data-category="${categoryKey}"]`);
        
        if (categoryItem) {
            const countElement = categoryItem.querySelector('p');
            if (countElement) {
                countElement.textContent = `${count} job${count > 1 ? 's' : ''} available`;
            }
        }
    });
}

// Wait for the DOM to fully load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, fetching jobs...');
    
    // Fetch real jobs first, then setup the page
    fetchRealJobs().then(() => {
        // Initial display of jobs
        displayJobs(jobsData);
        
        // Setup all event listeners
        setupEventListeners();
    });
});

// Setup all event listeners
function setupEventListeners() {
    console.log('Setting up event listeners...');
    
    // Elements
    const searchButton = document.getElementById('search-jobs-btn');
    const jobsCount = document.getElementById('jobs-count');
    const keywordInput = document.getElementById('keyword');
    const locationInput = document.getElementById('location');
    const categorySelect = document.getElementById('category');
    const typeSelect = document.getElementById('type');
    const jobModal = document.getElementById('job-modal');
    const jobDetailsContainer = document.getElementById('job-details-container');
    const closeButtons = document.querySelectorAll('.close');
    const categoryItems = document.querySelectorAll('.category-item');
    const pagination = document.querySelector('.pagination');
    const postJobButton = document.getElementById('footer-post-job');
    const postJobModal = document.getElementById('post-job-modal');
    const jobPostForm = document.getElementById('job-post-form');
    
    let currentJobs = [...jobsData]; // Clone the jobs array

    // Implement search functionality
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            const keyword = keywordInput.value.toLowerCase();
            const location = locationInput.value.toLowerCase();
            const category = categorySelect.value;
            const type = typeSelect.value;
            
            // Filter jobs based on search criteria
            const filteredJobs = jobsData.filter(job => {
                const matchKeyword = keyword === '' || 
                    job.title.toLowerCase().includes(keyword) || 
                    job.company.toLowerCase().includes(keyword) || 
                    job.description.toLowerCase().includes(keyword);
                    
                const matchLocation = location === '' || 
                    job.location.toLowerCase().includes(location);
                    
                const matchCategory = category === '' || 
                    job.category === category;
                    
                const matchType = type === '' || 
                    job.type === type;
                    
                return matchKeyword && matchLocation && matchCategory && matchType;
            });
            
            currentJobs = filteredJobs;
            displayJobs(filteredJobs);
        });
    }
    
    // Category filter functionality
    categoryItems.forEach(item => {
        item.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            if (categorySelect) {
                categorySelect.value = category;
                
                // Trigger search
                if (searchButton) {
                    searchButton.click();
                }
                
                // Smooth scroll to jobs grid
                const jobsGrid = document.querySelector('.jobs-grid');
                if (jobsGrid) {
                    jobsGrid.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (jobModal) jobModal.style.display = 'none';
            if (postJobModal) postJobModal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === jobModal) {
            jobModal.style.display = 'none';
        }
        if (event.target === postJobModal) {
            postJobModal.style.display = 'none';
        }
    });
    
    // Pagination functionality
    if (pagination) {
        pagination.addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                // Remove active class from all links
                const links = pagination.querySelectorAll('a');
                links.forEach(link => link.classList.remove('active'));
                
                // Add active class to clicked link
                e.target.classList.add('active');
                
                // In a real app, this would load different page of results
                // For now, just display an alert
                if (e.target.textContent.includes('Next')) {
                    alert('Loading next page of results...');
                } else {
                    alert(`Loading page ${e.target.textContent}...`);
                }
            }
        });
    }
    
    // Post Job functionality
    if (postJobButton) {
        postJobButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (postJobModal) {
                postJobModal.style.display = 'block';
            }
        });
    }
    
    // Handle job post form submission
    if (jobPostForm) {
        jobPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const title = document.getElementById('job-title').value;
            const company = document.getElementById('company-name').value;
            
            // Show success message
            alert(`Thank you! Your job listing for "${title}" at "${company}" has been submitted for review and will appear on our site soon.`);
            
            // Close modal and reset form
            if (postJobModal) {
                postJobModal.style.display = 'none';
            }
            jobPostForm.reset();
        });
    }
    
    // Handle job alerts subscription
    const emailForm = document.querySelector('.email-form');
    if (emailForm) {
        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email"]');
            
            if (emailInput.value) {
                alert(`Thank you! Job alerts will be sent to ${emailInput.value}`);
                emailInput.value = '';
            }
        });
    }
}

// Function to display jobs
function displayJobs(jobs) {
    const jobsGrid = document.getElementById('jobs-grid');
    const jobsCount = document.getElementById('jobs-count');
    
    if (!jobsGrid) return;
    
    jobsGrid.innerHTML = '';
    
    if (jobs.length === 0) {
        jobsGrid.innerHTML = '<p class="no-jobs">No jobs available at the moment. Please check back later.</p>';
        if (jobsCount) {
            jobsCount.textContent = '0';
        }
        return;
    }
    
    jobs.forEach(job => {
        const jobCard = document.createElement('div');
        jobCard.className = 'job-card';
        jobCard.innerHTML = `
            <h3>${job.title}</h3>
            <div class="job-company">${job.company}</div>
            <div class="job-details">
                <span class="job-detail"><i class="fas fa-map-marker-alt"></i> ${job.location}</span>
                <span class="job-detail"><i class="fas fa-clock"></i> ${job.type}</span>
                <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary || 'Competitive'}</span>
            </div>
            <div class="job-description">${job.description}</div>
            <div class="job-action">
                <button class="apply-btn" data-job-id="${job.id}">View Details</button>
            </div>
        `;
        
        jobsGrid.appendChild(jobCard);
    });
    
    // Update job count
    if (jobsCount) {
        jobsCount.textContent = jobs.length;
    }
    
    // Add event listeners to the "View Details" buttons
    const viewDetailsButtons = document.querySelectorAll('.apply-btn');
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const jobId = parseInt(this.getAttribute('data-job-id'));
            openJobDetails(jobId);
        });
    });
}

// Function to open job details modal
function openJobDetails(jobId) {
    const jobModal = document.getElementById('job-modal');
    const jobDetailsContainer = document.getElementById('job-details-container');
    
    if (!jobModal || !jobDetailsContainer) return;
    
    const job = jobsData.find(job => job.id === jobId);
    
    if (!job) return;
    
    // Ensure requirements is an array
    const requirements = Array.isArray(job.requirements) ? job.requirements : 
                        (job.requirements ? [job.requirements] : ['Requirements to be updated by employer']);
    
    // Populate modal with job details
    jobDetailsContainer.innerHTML = `
        <div class="job-header">
            <h2>${job.title}</h2>
            <div class="job-company">${job.company}</div>
        </div>
        <div class="job-body">
            <div class="job-meta">
                <div class="job-meta-item"><i class="fas fa-map-marker-alt"></i> ${job.location}</div>
                <div class="job-meta-item"><i class="fas fa-clock"></i> ${job.type}</div>
                <div class="job-meta-item"><i class="fas fa-dollar-sign"></i> ${job.salary || 'Competitive'}</div>
                <div class="job-meta-item"><i class="fas fa-calendar-alt"></i> Posted ${job.posted || 'recently'}</div>
            </div>
            
            <div class="job-description-full">
                <h3>Job Description</h3>
                <p>${job.description}</p>
            </div>
            
            <div class="job-requirements">
                <h3>Requirements</h3>
                <ul>
                    ${requirements.map(req => `<li>${req}</li>`).join('')}
                </ul>
            </div>
            
            <div class="job-actions">
                <button class="btn btn-primary">Apply Now</button>
                <button class="btn btn-secondary save-job-btn">Save Job</button>
            </div>
        </div>
    `;
    
    // Show the modal
    jobModal.style.display = 'block';
    
    // Add event listeners for Apply Now and Save Job buttons
    const applyNowBtn = jobDetailsContainer.querySelector('.btn-primary');
    const saveJobBtn = jobDetailsContainer.querySelector('.save-job-btn');
    
    if (applyNowBtn) {
        applyNowBtn.addEventListener('click', function() {
            alert(`Thanks for your interest in the ${job.title} position at ${job.company}! Your application has been submitted.`);
        });
    }
    
    if (saveJobBtn) {
        saveJobBtn.addEventListener('click', function() {
            alert(`The ${job.title} position has been saved to your favorites.`);
        });
    }
}