  // Wait for the DOM to fully load
  document.addEventListener('DOMContentLoaded', function() {
    // Sample job data (using existing data from the page)
    const jobsData = [
        {
            id: 1,
            title: "Elementary School Teacher",
            company: "Bright Future Academy",
            location: "New York",
            type: "Full-time",
            category: "education",
            salary: "$45,000 - $60,000",
            description: "Looking for a passionate elementary school teacher to educate and inspire young minds. Must have a teaching certificate and experience working with children.",
            requirements: [
                "Bachelor's degree in Education or related field",
                "Valid teaching certificate",
                "2+ years experience working with elementary school children",
                "Strong communication and classroom management skills",
                "Ability to create engaging lesson plans"
            ],
            posted: "3 days ago"
        },
        {
            id: 2,
            title: "Administrative Assistant",
            company: "Global Solutions Inc.",
            location: "Chicago",
            type: "Full-time",
            category: "office",
            salary: "$35,000 - $45,000",
            description: "Seeking an organized administrative assistant to support our executive team. Responsibilities include scheduling, document management, and customer service.",
            requirements: [
                "Associate's degree or equivalent experience",
                "Proficiency in Microsoft Office Suite",
                "Excellent organizational and time management skills",
                "Strong verbal and written communication abilities",
                "Previous administrative experience preferred"
            ],
            posted: "1 week ago"
        },
        {
            id: 3,
            title: "Customer Service Representative",
            company: "Tech Support Central",
            location: "Remote",
            type: "Part-time",
            category: "customer",
            salary: "$18 - $22 per hour",
            description: "Join our remote customer service team providing technical support to customers. Strong communication skills and problem-solving abilities required.",
            requirements: [
                "High school diploma or equivalent",
                "1+ years experience in customer service",
                "Technical troubleshooting experience",
                "Excellent phone and email communication skills",
                "Ability to work independently in a remote environment"
            ],
            posted: "2 days ago"
        },
        {
            id: 4,
            title: "Business Analyst",
            company: "Finance Corp",
            location: "San Francisco",
            type: "Full-time",
            category: "business",
            salary: "$70,000 - $90,000",
            description: "Seeking a business analyst to help improve our operational processes. Must have experience with data analysis and business process optimization.",
            requirements: [
                "Bachelor's degree in Business, Finance, or related field",
                "3+ years experience in business analysis",
                "Proficiency in data analysis tools (Excel, SQL, Tableau)",
                "Strong analytical and problem-solving skills",
                "Experience with process improvement methodologies"
            ],
            posted: "1 month ago"
        },
        {
            id: 5,
            title: "Registered Nurse",
            company: "Community Health Center",
            location: "Miami",
            type: "Full-time",
            category: "healthcare",
            salary: "$65,000 - $85,000",
            description: "Join our healthcare team as a registered nurse. Must have valid nursing license and experience in direct patient care.",
            requirements: [
                "Associate's or Bachelor's degree in Nursing",
                "Current RN license",
                "BLS/ACLS certification",
                "2+ years clinical experience",
                "Strong patient care and communication skills"
            ],
            posted: "2 weeks ago"
        },
        {
            id: 6,
            title: "Bookkeeper",
            company: "Small Business Solutions",
            location: "Atlanta",
            type: "Part-time",
            category: "finance",
            salary: "$25 - $30 per hour",
            description: "Part-time bookkeeper needed for local accounting firm. Responsibilities include maintaining financial records, processing invoices, and reconciling accounts.",
            requirements: [
                "Associate's degree in Accounting or related field",
                "2+ years bookkeeping experience",
                "Proficiency in QuickBooks and Excel",
                "Strong attention to detail",
                "Knowledge of basic accounting principles"
            ],
            posted: "5 days ago"
        },
        {
            id: 7,
            title: "High School Math Tutor",
            company: "Learning Center",
            location: "Dallas",
            type: "Contract",
            category: "education",
            salary: "$30 - $40 per hour",
            description: "Math tutor needed to help high school students with algebra, geometry, and calculus. Flexible hours available.",
            requirements: [
                "Bachelor's degree in Mathematics, Education, or related field",
                "Strong knowledge of high school math subjects",
                "Previous tutoring or teaching experience preferred",
                "Excellent communication and patience",
                "Ability to explain complex concepts in simple terms"
            ],
            posted: "1 week ago"
        },
        {
            id: 8,
            title: "Office Manager",
            company: "Creative Design Studio",
            location: "Seattle",
            type: "Full-time",
            category: "office",
            salary: "$50,000 - $65,000",
            description: "Experienced office manager needed to oversee daily operations of our design studio. Responsibilities include administration, facility management, and team support.",
            requirements: [
                "Bachelor's degree preferred",
                "3+ years experience in office management",
                "Strong organizational and multitasking skills",
                "Experience with budget management",
                "Leadership and team coordination abilities"
            ],
            posted: "2 weeks ago"
        }
    ];

    // Elements
    const jobsGrid = document.getElementById('jobs-grid');
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

    // Initial display of jobs
    displayJobs(currentJobs);
    
    // Implement search functionality
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
    
    // Function to display jobs
    function displayJobs(jobs) {
        jobsGrid.innerHTML = '';
        
        if (jobs.length === 0) {
            jobsGrid.innerHTML = '<p class="no-jobs">No jobs match your search criteria. Try adjusting your filters.</p>';
            jobsCount.textContent = '0';
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
                    <span class="job-detail"><i class="fas fa-dollar-sign"></i> ${job.salary}</span>
                </div>
                <div class="job-description">${job.description}</div>
                <div class="job-action">
                    <button class="apply-btn" data-job-id="${job.id}">View Details</button>
                </div>
            `;
            
            jobsGrid.appendChild(jobCard);
        });
        
        // Update job count
        jobsCount.textContent = jobs.length;
        
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
        const job = jobsData.find(job => job.id === jobId);
        
        if (!job) return;
        
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
                    <div class="job-meta-item"><i class="fas fa-dollar-sign"></i> ${job.salary}</div>
                    <div class="job-meta-item"><i class="fas fa-calendar-alt"></i> Posted ${job.posted}</div>
                </div>
                
                <div class="job-description-full">
                    <h3>Job Description</h3>
                    <p>${job.description}</p>
                </div>
                
                <div class="job-requirements">
                    <h3>Requirements</h3>
                    <ul>
                        ${job.requirements.map(req => `<li>${req}</li>`).join('')}
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
        
        applyNowBtn.addEventListener('click', function() {
            alert(`Thanks for your interest in the ${job.title} position at ${job.company}! Your application has been submitted.`);
        });
        
        saveJobBtn.addEventListener('click', function() {
            alert(`The ${job.title} position has been saved to your favorites.`);
        });
    }
    
    // Category filter functionality
    categoryItems.forEach(item => {
        item.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            categorySelect.value = category;
            
            // Trigger search
            searchButton.click();
            
            // Smooth scroll to jobs grid
            document.querySelector('.jobs-grid').scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    // Close modals
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            jobModal.style.display = 'none';
            postJobModal.style.display = 'none';
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
    
    // Post Job functionality
    if (postJobButton) {
        postJobButton.addEventListener('click', function(e) {
            e.preventDefault();
            postJobModal.style.display = 'block';
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
            postJobModal.style.display = 'none';
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
});