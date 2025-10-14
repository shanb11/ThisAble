// Real jobs data - will be populated by AJAX
let jobsData = [];
let categoryStats = {};
let siteStats = {};

// Fetch real jobs from database
async function fetchRealJobs() {
    try {
        console.log('Fetching real jobs from database...');
        
        const response = await fetch('/thisable/backend/landing/get_landing_jobs.php');
        const data = await response.json();
        
        console.log('Response from backend:', data);
        
        if (data.success) {
            // Use real jobs
            jobsData = data.jobs || [];
            categoryStats = data.categories || {};
            siteStats = data.stats || {};
            
            console.log('Loaded ' + jobsData.length + ' real jobs');
            
            // Update category counts on page
            updateCategoryCounts();
            
            // Update site statistics if hero section exists
            updateSiteStats();
            
        } else {
            console.log('No real jobs found or backend error');
            jobsData = [];
            categoryStats = {};
        }
        
    } catch (error) {
        console.error('Error fetching real jobs:', error);
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
        const categoryCard = document.querySelector(`[data-category="${categoryKey}"]`);
        
        if (categoryCard) {
            const countElement = categoryCard.querySelector('p');
            if (countElement) {
                countElement.textContent = `${count} job${count > 1 ? 's' : ''} available`;
            }
        }
    });
}

// Update site statistics in hero section
function updateSiteStats() {
    if (siteStats.total_jobs) {
        console.log('Site stats:', siteStats);
        // You can add live stats display here if needed
    }
}

// Initialize - fetch real jobs when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Fetch real jobs first
    fetchRealJobs().then(() => {
        // Then set up event listeners
        setupEventListeners();
    });
});

// Set up all event listeners
function setupEventListeners() {
    // DOM elements
    const searchBtn = document.getElementById('search-btn');
    const jobSearchInput = document.getElementById('job-search');
    const locationSearchInput = document.getElementById('location-search');
    const browseJobsBtn = document.getElementById('browse-jobs-btn');
    const footerBrowseJobs = document.getElementById('footer-browse-jobs');
    const postJobBtn = document.getElementById('post-job-btn');
    const footerPostJob = document.getElementById('footer-post-job');
    const footerAboutUs = document.getElementById('footer-about-us');
    const categoryCards = document.querySelectorAll('.category-card');
    const jobsModal = document.getElementById('jobs-modal');
    const postJobModal = document.getElementById('post-job-modal');
    const modalCloseButtons = document.querySelectorAll('.close');
    const jobsContainer = document.getElementById('jobs-container');
    const modalTitle = document.getElementById('modal-title');
    const filterLocation = document.getElementById('filter-location');
    const filterType = document.getElementById('filter-type');
    const jobPostForm = document.getElementById('job-post-form');

    // Search button
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const keyword = jobSearchInput.value.trim().toLowerCase();
            const location = locationSearchInput.value.trim().toLowerCase();
            
            // Filter jobs based on search criteria
            let filteredJobs = jobsData;
            
            if (keyword) {
                filteredJobs = filteredJobs.filter(job => 
                    job.title.toLowerCase().includes(keyword) || 
                    job.description.toLowerCase().includes(keyword) ||
                    job.company.toLowerCase().includes(keyword) ||
                    job.category.toLowerCase().includes(keyword)
                );
            }
            
            if (location && location !== 'all locations') {
                filteredJobs = filteredJobs.filter(job => 
                    job.location.toLowerCase().includes(location)
                );
            }
            
            // Display filtered jobs
            displayJobs(filteredJobs, `Search Results for "${keyword}"`);
        });
    }
    
    // Enter key in search inputs
    if (jobSearchInput) {
        jobSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
    
    if (locationSearchInput) {
        locationSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
    
    // Browse Jobs button
    if (browseJobsBtn) {
        browseJobsBtn.addEventListener('click', function() {
            displayJobs(jobsData, 'All Available Jobs');
        });
    }
    
    // Footer Browse Jobs link
    if (footerBrowseJobs) {
        footerBrowseJobs.addEventListener('click', function(e) {
            e.preventDefault();
            displayJobs(jobsData, 'All Available Jobs');
        });
    }
    
    // Category cards
    categoryCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const categoryName = this.querySelector('h3').textContent;
            const categoryJobs = jobsData.filter(job => job.category === category);
            displayJobs(categoryJobs, `${categoryName} Jobs`);
        });
    });
    
    // Post Job button
    if (postJobBtn) {
        postJobBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openPostJobModal();
        });
    }
    
    // Footer Post Job link
    if (footerPostJob) {
        footerPostJob.addEventListener('click', function(e) {
            e.preventDefault();
            openPostJobModal();
        });
    }
    
    // Close buttons for modals
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (jobsModal) jobsModal.style.display = 'none';
            if (postJobModal) postJobModal.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === jobsModal) {
            jobsModal.style.display = 'none';
        }
        if (e.target === postJobModal) {
            postJobModal.style.display = 'none';
        }
    });
    
    // Filter change events
    if (filterLocation) {
        filterLocation.addEventListener('change', applyFilters);
    }
    if (filterType) {
        filterType.addEventListener('change', applyFilters);
    }
    
    // Job post form submission
    if (jobPostForm) {
        jobPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const title = document.getElementById('job-title').value;
            const company = document.getElementById('company-name').value;
            const location = document.getElementById('job-location').value;
            const type = document.getElementById('job-type').value;
            const category = document.getElementById('job-category').value;
            const description = document.getElementById('job-description').value;
            
            // Show success message
            alert('Job posted successfully!');
            
            // Reset form and close modal
            jobPostForm.reset();
            if (postJobModal) {
                postJobModal.style.display = 'none';
            }
            
            // Refresh jobs data
            fetchRealJobs().then(() => {
                displayJobs(jobsData, 'All Available Jobs');
            });
        });
    }
}

// Display jobs in modal
function displayJobs(jobs, title) {
    const jobsModal = document.getElementById('jobs-modal');
    const modalTitle = document.getElementById('modal-title');
    const jobsContainer = document.getElementById('jobs-container');
    const filterLocation = document.getElementById('filter-location');
    const filterType = document.getElementById('filter-type');
    
    if (!jobsModal || !modalTitle || !jobsContainer) return;
    
    // Set modal title
    modalTitle.textContent = title;
    
    // Clear jobs container
    jobsContainer.innerHTML = '';
    
    // Check if there are jobs to display
    if (jobs.length === 0) {
        jobsContainer.innerHTML = `
            <div class="no-jobs">
                <p>No jobs available at the moment.</p>
            </div>
        `;
    } else {
        // Display each job
        jobs.forEach(job => {
            const jobCard = document.createElement('div');
            jobCard.className = 'job-card';
            jobCard.innerHTML = `
                <h3>${job.title}</h3>
                <div class="job-company">${job.company}</div>
                <div class="job-details">
                    <span class="job-detail"><i class="fas fa-map-marker-alt"></i> ${job.location}</span>
                    <span class="job-detail"><i class="fas fa-briefcase"></i> ${job.type}</span>
                    <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary || 'Competitive'}</span>
                </div>
                <div class="job-description">${job.description}</div>
                <a href="#" class="apply-btn">Apply Now</a>
            `;
            jobsContainer.appendChild(jobCard);
        });
    }
    
    // Show modal
    jobsModal.style.display = 'block';
    
    // Reset filters
    if (filterLocation) filterLocation.value = '';
    if (filterType) filterType.value = '';
}

// Apply filters to current jobs
function applyFilters() {
    const modalTitle = document.getElementById('modal-title');
    const filterLocation = document.getElementById('filter-location');
    const filterType = document.getElementById('filter-type');
    const jobSearchInput = document.getElementById('job-search');
    const locationSearchInput = document.getElementById('location-search');
    
    if (!modalTitle) return;
    
    // Get current jobs and title
    const currentTitle = modalTitle.textContent;
    let baseJobs;
    
    // Determine base set of jobs
    if (currentTitle === 'All Available Jobs') {
        baseJobs = jobsData;
    } else if (currentTitle.includes('Search Results')) {
        // Get keyword from title
        const keyword = jobSearchInput ? jobSearchInput.value.trim().toLowerCase() : '';
        const location = locationSearchInput ? locationSearchInput.value.trim().toLowerCase() : '';
        
        baseJobs = jobsData.filter(job => {
            const matchesKeyword = !keyword || 
                job.title.toLowerCase().includes(keyword) || 
                job.description.toLowerCase().includes(keyword) ||
                job.company.toLowerCase().includes(keyword) ||
                job.category.toLowerCase().includes(keyword);
                
            const matchesLocation = !location || location === 'all locations' || 
                job.location.toLowerCase().includes(location);
                
            return matchesKeyword && matchesLocation;
        });
    } else {
        // Category-based jobs
        const category = currentTitle.split(' ')[0].toLowerCase();
        baseJobs = jobsData.filter(job => {
            return job.category.includes(category.toLowerCase()) || 
                   (category === 'education' && job.category === 'education') ||
                   (category === 'office' && job.category === 'office') ||
                   (category === 'customer' && job.category === 'customer') ||
                   (category === 'business' && job.category === 'business') ||
                   (category === 'healthcare' && job.category === 'healthcare') ||
                   (category === 'finance' && job.category === 'finance');
        });
    }
    
    // Apply additional filters
    const locationFilter = filterLocation ? filterLocation.value.toLowerCase() : '';
    const typeFilter = filterType ? filterType.value : '';
    
    let filteredJobs = baseJobs;
    
    if (locationFilter) {
        filteredJobs = filteredJobs.filter(job => 
            job.location.toLowerCase() === locationFilter
        );
    }
    
    if (typeFilter) {
        filteredJobs = filteredJobs.filter(job => 
            job.type === typeFilter
        );
    }
    
    // Update jobs display without changing the title
    updateJobsDisplay(filteredJobs);
}

// Update jobs display without changing title or modal state
function updateJobsDisplay(jobs) {
    const jobsContainer = document.getElementById('jobs-container');
    
    if (!jobsContainer) return;
    
    // Clear jobs container
    jobsContainer.innerHTML = '';
    
    // Check if there are jobs to display
    if (jobs.length === 0) {
        jobsContainer.innerHTML = `
            <div class="no-jobs">
                <p>No jobs found matching your criteria.</p>
            </div>
        `;
    } else {
        // Display each job
        jobs.forEach(job => {
            const jobCard = document.createElement('div');
            jobCard.className = 'job-card';
            jobCard.innerHTML = `
                <h3>${job.title}</h3>
                <div class="job-company">${job.company}</div>
                <div class="job-details">
                    <span class="job-detail"><i class="fas fa-map-marker-alt"></i> ${job.location}</span>
                    <span class="job-detail"><i class="fas fa-briefcase"></i> ${job.type}</span>
                    <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary || 'Competitive'}</span>
                </div>
                <div class="job-description">${job.description}</div>
                <a href="#" class="apply-btn">Apply Now</a>
            `;
            jobsContainer.appendChild(jobCard);
        });
    }
}

// Open post job modal
function openPostJobModal() {
    const postJobModal = document.getElementById('post-job-modal');
    if (postJobModal) {
        postJobModal.style.display = 'block';
    }
}