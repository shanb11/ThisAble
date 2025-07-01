// Sample job data
const jobsData = [
    {
        id: 1,
        title: "Elementary School Teacher",
        company: "Bright Future Academy",
        location: "New York",
        type: "Full-time",
        category: "education",
        salary: "$45,000 - $60,000",
        description: "Looking for a passionate elementary school teacher to educate and inspire young minds. Must have a teaching certificate and experience working with children."
    },
    {
        id: 2,
        title: "Administrative Assistant",
        company: "Global Solutions Inc.",
        location: "Chicago",
        type: "Full-time",
        category: "office",
        salary: "$35,000 - $45,000",
        description: "Seeking an organized administrative assistant to support our executive team. Responsibilities include scheduling, document management, and customer service."
    },
    {
        id: 3,
        title: "Customer Service Representative",
        company: "Tech Support Central",
        location: "Remote",
        type: "Part-time",
        category: "customer",
        salary: "$18 - $22 per hour",
        description: "Join our remote customer service team providing technical support to customers. Strong communication skills and problem-solving abilities required."
    },
    {
        id: 4,
        title: "Business Analyst",
        company: "Finance Corp",
        location: "San Francisco",
        type: "Full-time",
        category: "business",
        salary: "$70,000 - $90,000",
        description: "Seeking a business analyst to help improve our operational processes. Must have experience with data analysis and business process optimization."
    },
    {
        id: 5,
        title: "Registered Nurse",
        company: "Community Health Center",
        location: "Miami",
        type: "Full-time",
        category: "healthcare",
        salary: "$65,000 - $85,000",
        description: "Join our healthcare team as a registered nurse. Must have valid nursing license and experience in direct patient care."
    },
    {
        id: 6,
        title: "Accountant",
        company: "Financial Services LLC",
        location: "Chicago",
        type: "Full-time",
        category: "finance",
        salary: "$55,000 - $75,000",
        description: "Seeking an experienced accountant to join our finance team. Responsibilities include financial reporting, budgeting, and tax preparation."
    },
    {
        id: 7,
        title: "Math Tutor",
        company: "Learning Center",
        location: "Remote",
        type: "Part-time",
        category: "education",
        salary: "$25 - $35 per hour",
        description: "Experienced math tutor needed to help students improve their skills. Must have strong knowledge of algebra, geometry, and calculus."
    },
    {
        id: 8,
        title: "Executive Assistant",
        company: "Tech Innovations",
        location: "San Francisco",
        type: "Full-time",
        category: "office",
        salary: "$50,000 - $65,000",
        description: "Support our CEO and executive team with administrative tasks, scheduling, and travel arrangements. Must be highly organized and proactive."
    },
    {
        id: 9,
        title: "Call Center Agent",
        company: "Customer First Services",
        location: "Remote",
        type: "Full-time",
        category: "customer",
        salary: "$35,000 - $42,000",
        description: "Join our remote call center team handling customer inquiries and support requests. Must have excellent communication skills and patience."
    },
    {
        id: 10,
        title: "Marketing Coordinator",
        company: "Brand Builders Inc.",
        location: "New York",
        type: "Full-time",
        category: "business",
        salary: "$45,000 - $55,000",
        description: "Seeking a creative marketing coordinator to help manage our campaigns and social media presence. Experience with digital marketing preferred."
    },
    {
        id: 11,
        title: "Medical Assistant",
        company: "Wellness Medical Group",
        location: "Miami",
        type: "Full-time",
        category: "healthcare",
        salary: "$38,000 - $48,000",
        description: "Join our healthcare team as a medical assistant. Responsibilities include patient intake, vital signs, and assisting physicians."
    },
    {
        id: 12,
        title: "Financial Analyst",
        company: "Investment Partners",
        location: "Chicago",
        type: "Full-time",
        category: "finance",
        salary: "$65,000 - $85,000",
        description: "Seeking a detailed financial analyst to perform market research and investment analysis. Strong Excel and financial modeling skills required."
    },
    {
        id: 13,
        title: "ESL Teacher",
        company: "Language Academy",
        location: "Remote",
        type: "Contract",
        category: "education",
        salary: "$20 - $30 per hour",
        description: "Online ESL teacher needed to teach English to international students. TEFL certification and teaching experience preferred."
    },
    {
        id: 14,
        title: "Data Entry Specialist",
        company: "Information Services",
        location: "Remote",
        type: "Part-time",
        category: "office",
        salary: "$16 - $20 per hour",
        description: "Accurate data entry specialist needed to input and validate information. Must have attention to detail and fast typing skills."
    },
    {
        id: 15,
        title: "Help Desk Support",
        company: "IT Solutions",
        location: "San Francisco",
        type: "Full-time",
        category: "customer",
        salary: "$40,000 - $50,000",
        description: "Provide technical support to employees and customers. Must have knowledge of common software applications and troubleshooting skills."
    },
    {
        id: 16,
        title: "Project Manager",
        company: "Development Enterprises",
        location: "New York",
        type: "Full-time",
        category: "business",
        salary: "$75,000 - $95,000",
        description: "Experienced project manager needed to oversee development projects. PMP certification and 3+ years of experience preferred."
    },
    {
        id: 17,
        title: "Physical Therapist",
        company: "Rehabilitation Center",
        location: "Miami",
        type: "Full-time",
        category: "healthcare",
        salary: "$70,000 - $90,000",
        description: "Licensed physical therapist needed to help patients recover from injuries. Must have experience with rehabilitation techniques."
    },
    {
        id: 18,
        title: "Bookkeeper",
        company: "Small Business Services",
        location: "Remote",
        type: "Part-time",
        category: "finance",
        salary: "$22 - $28 per hour",
        description: "Experienced bookkeeper needed to maintain financial records. Knowledge of QuickBooks and Excel required."
    }
];

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

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners
    setupEventListeners();
});

// Set up all event listeners
function setupEventListeners() {
    // Search button
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
    
    // Enter key in search inputs
    jobSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });
    
    locationSearchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });
    
    // Browse Jobs button
    browseJobsBtn.addEventListener('click', function() {
        displayJobs(jobsData, 'All Available Jobs');
    });
    
    // Footer Browse Jobs link
    footerBrowseJobs.addEventListener('click', function(e) {
        e.preventDefault();
        displayJobs(jobsData, 'All Available Jobs');
    });
    
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
    postJobBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openPostJobModal();
    });
    
    // Footer Post Job link
    footerPostJob.addEventListener('click', function(e) {
        e.preventDefault();
        openPostJobModal();
    });
    
    // Close buttons for modals
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            jobsModal.style.display = 'none';
            postJobModal.style.display = 'none';
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
    filterLocation.addEventListener('change', applyFilters);
    filterType.addEventListener('change', applyFilters);
    
    // Job post form submission
    jobPostForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const title = document.getElementById('job-title').value;
        const company = document.getElementById('company-name').value;
        const location = document.getElementById('job-location').value;
        const type = document.getElementById('job-type').value;
        const category = document.getElementById('job-category').value;
        const description = document.getElementById('job-description').value;
        
        // Create new job object
        const newJob = {
            id: jobsData.length + 1,
            title,
            company,
            location,
            type,
            category,
            salary: "Competitive",
            description
        };
        
        // Add to jobs data
        jobsData.unshift(newJob);
        
        // Reset form and close modal
        jobPostForm.reset();
        postJobModal.style.display = 'none';
        
        // Show success message
        alert('Job posted successfully!');
    });
}

// Display jobs in modal
function displayJobs(jobs, title) {
    // Set modal title
    modalTitle.textContent = title;
    
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
                    <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary}</span>
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
    filterLocation.value = '';
    filterType.value = '';
}

// Apply filters to current jobs
function applyFilters() {
    // Get current jobs and title
    const currentTitle = modalTitle.textContent;
    let baseJobs;
    
    // Determine base set of jobs
    if (currentTitle === 'All Available Jobs') {
        baseJobs = jobsData;
    } else if (currentTitle.includes('Search Results')) {
        // Get keyword from title
        const keyword = jobSearchInput.value.trim().toLowerCase();
        const location = locationSearchInput.value.trim().toLowerCase();
        
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
    const locationFilter = filterLocation.value.toLowerCase();
    const typeFilter = filterType.value;
    
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
                    <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary}</span>
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
    postJobModal.style.display = 'block';
}
