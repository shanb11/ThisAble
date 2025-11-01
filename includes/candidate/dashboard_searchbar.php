<div class="search-bar">
    <div class="search-container">
        <div class="search-input">
            <input type="text" id="job-search" placeholder="Search for jobs, companies, or skills..." autocomplete="off">
            <i class="fas fa-times search-clear" id="search-clear"></i>
        </div>
        
        <!-- Search Results Dropdown -->
        <div class="search-results-dropdown" id="search-results-dropdown">
            <div class="search-results-content" id="search-results-content">
                <!-- Results will be populated here -->
            </div>
        </div>
        
        <!-- Search Status -->
        <div class="search-status" id="search-status"></div>
    </div>
    
    <div class="notification-icons">
        <button class="search-btn" id="search-btn" title="Search">
            <i class="fas fa-search"></i>
        </button>
        <a href="notifications.php" class="notification-link">
            <i class="far fa-bell"></i>
            <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
        </a>
    </div>
</div>

<script>
let searchTimeout;
let currentSearchQuery = '';
let isSearching = false;

document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    loadNotificationCount();
});

function initializeSearch() {
    const searchInput = document.getElementById('job-search');
    const searchClear = document.getElementById('search-clear');
    const searchBtn = document.getElementById('search-btn');
    const searchResults = document.getElementById('search-results-dropdown');
    const searchStatus = document.getElementById('search-status');
    
    if (!searchInput) return;
    
    // Search button click
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length > 0) {
                goToJobListings();
            } else {
                searchInput.focus();
            }
        });
    }
    
    // Search input event
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        currentSearchQuery = query;
        
        // Show/hide clear button
        if (query.length > 0) {
            searchClear.classList.add('visible');
        } else {
            searchClear.classList.remove('visible');
        }
        
        clearTimeout(searchTimeout);
        
        if (query.length === 0) {
            hideSearchResults();
            updateSearchStatus('');
            return;
        }
        
        if (query.length < 2) {
            updateSearchStatus('Type at least 2 characters to search...');
            return;
        }
        
        // Show loading state
        updateSearchStatus('Searching...');
        showSearchLoading();
        
        // Debounce search
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Clear search
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.focus();
        hideSearchResults();
        updateSearchStatus('');
        currentSearchQuery = '';
        searchClear.classList.remove('visible');
    });
    
    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const results = document.querySelectorAll('.search-result-item');
        const activeResult = document.querySelector('.search-result-item.active');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                navigateResults('down', results, activeResult);
                break;
            case 'ArrowUp':
                e.preventDefault();
                navigateResults('up', results, activeResult);
                break;
            case 'Enter':
                e.preventDefault();
                if (activeResult) {
                    activeResult.click();
                } else if (currentSearchQuery.length > 0) {
                    goToJobListings();
                }
                break;
            case 'Escape':
                hideSearchResults();
                searchInput.blur();
                break;
        }
    });
    
    // Focus events
    searchInput.addEventListener('focus', function() {
        if (currentSearchQuery.length > 0) {
            showSearchResults();
        }
    });
    
    // Click outside to close
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            hideSearchResults();
        }
    });
}

function performSearch(query) {
    if (isSearching) return;
    
    isSearching = true;
    
    fetch(`../../backend/candidate/dashboard_search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            isSearching = false;
            
            if (data.success) {
                displaySearchResults(data);
            } else {
                showSearchError(data.error || 'Search failed');
            }
        })
        .catch(error => {
            isSearching = false;
            console.error('Search error:', error);
            showSearchError('Search failed. Please try again.');
        });
}

function displaySearchResults(data) {
    const resultsContent = document.getElementById('search-results-content');
    const { results, total_jobs_available, suggestions, categories } = data;
    
    if (results.length === 0) {
        showNoResults(data.search_query, suggestions);
        return;
    }
    
    let html = '';
    
    // Group results by type
    const jobResults = results.filter(r => r.result_type === 'job');
    const companyResults = results.filter(r => r.result_type === 'company');
    const skillResults = results.filter(r => r.result_type === 'skill');
    
    // Jobs section
    if (jobResults.length > 0) {
        html += `
            <div class="results-section">
                <div class="results-section-header">
                    <i class="fas fa-briefcase"></i>
                    <span>Jobs (${categories.jobs})</span>
                </div>
                ${jobResults.map(result => createResultItem(result)).join('')}
            </div>
        `;
    }
    
    // Companies section
    if (companyResults.length > 0) {
        html += `
            <div class="results-section">
                <div class="results-section-header">
                    <i class="fas fa-building"></i>
                    <span>Companies (${categories.companies})</span>
                </div>
                ${companyResults.map(result => createResultItem(result)).join('')}
            </div>
        `;
    }
    
    // Skills section
    if (skillResults.length > 0) {
        html += `
            <div class="results-section">
                <div class="results-section-header">
                    <i class="fas fa-cogs"></i>
                    <span>Skills (${categories.skills})</span>
                </div>
                ${skillResults.map(result => createResultItem(result)).join('')}
            </div>
        `;
    }
    
    // View all results footer
    html += `
        <div class="search-footer">
            <button class="view-all-results" onclick="goToJobListings()">
                <i class="fas fa-search"></i>
                View all ${total_jobs_available} jobs for "${data.search_query}"
            </button>
        </div>
    `;
    
    resultsContent.innerHTML = html;
    showSearchResults();
    
    // Update status
    updateSearchStatus(`Found ${results.length} results`);
    
    // Add click handlers
    addResultClickHandlers();
}

function createResultItem(result) {
    const metaInfo = result.meta || {};
    let metaHtml = '';
    
    if (result.result_type === 'job') {
        const metaParts = [];
        if (metaInfo.employment_type) metaParts.push(metaInfo.employment_type);
        if (metaInfo.salary_range) metaParts.push(metaInfo.salary_range);
        if (metaInfo.remote_available) metaParts.push('Remote Available');
        if (metaInfo.posted_ago) metaParts.push(metaInfo.posted_ago);
        
        metaHtml = metaParts.length > 0 ? `<div class="result-meta">${metaParts.join(' • ')}</div>` : '';
    }
    
    return `
        <div class="search-result-item" data-link="${result.link}" data-type="${result.result_type}">
            <div class="result-icon">
                ${result.logo && result.logo.length <= 3 ? 
                    `<div class="company-logo-small">${result.logo}</div>` : 
                    `<i class="${result.icon}"></i>`
                }
            </div>
            <div class="result-content">
                <div class="result-title">${highlightSearchTerm(result.title, currentSearchQuery)}</div>
                <div class="result-subtitle">${result.subtitle}</div>
                ${result.location ? `<div class="result-location">${result.location}</div>` : ''}
                ${metaHtml}
            </div>
            <div class="result-action">
                <span class="action-text">${result.action}</span>
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    `;
}

function showNoResults(query, suggestions) {
    const resultsContent = document.getElementById('search-results-content');
    
    let suggestionsHtml = '';
    if (suggestions && suggestions.length > 0) {
        suggestionsHtml = `
            <div class="search-suggestions">
                <div class="suggestions-title">Try searching for:</div>
                <div class="suggestions-list">
                    ${suggestions.map(suggestion => 
                        `<button class="suggestion-item" onclick="searchFor('${suggestion}')">${suggestion}</button>`
                    ).join('')}
                </div>
            </div>
        `;
    }
    
    resultsContent.innerHTML = `
        <div class="no-results">
            <div class="no-results-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="no-results-title">No results found for "${query}"</div>
            <div class="no-results-text">Try different keywords or browse all jobs</div>
            ${suggestionsHtml}
            <div class="no-results-actions">
                <button class="btn btn-primary" onclick="goToJobListings()">
                    Browse All Jobs
                </button>
            </div>
        </div>
    `;
    
    showSearchResults();
    updateSearchStatus(`No results found for "${query}"`);
}

function showSearchLoading() {
    const resultsContent = document.getElementById('search-results-content');
    resultsContent.innerHTML = `
        <div class="search-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Searching...</span>
        </div>
    `;
    showSearchResults();
}

function showSearchError(error) {
    const resultsContent = document.getElementById('search-results-content');
    resultsContent.innerHTML = `
        <div class="search-error">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${error}</span>
        </div>
    `;
    showSearchResults();
    updateSearchStatus(error);
}

function showSearchResults() {
    const dropdown = document.getElementById('search-results-dropdown');
    dropdown.style.display = 'block';
    dropdown.classList.add('show');
}

function hideSearchResults() {
    const dropdown = document.getElementById('search-results-dropdown');
    dropdown.style.display = 'none';
    dropdown.classList.remove('show');
}

function updateSearchStatus(message) {
    const status = document.getElementById('search-status');
    if (message) {
        status.textContent = message;
        status.style.display = 'block';
    } else {
        status.style.display = 'none';
    }
}

function addResultClickHandlers() {
    document.querySelectorAll('.search-result-item').forEach(item => {
        item.addEventListener('click', function() {
            const link = this.dataset.link;
            if (link) {
                hideSearchResults();
                window.location.href = link;
            }
        });
        
        item.addEventListener('mouseenter', function() {
            // Remove active from others
            document.querySelectorAll('.search-result-item.active').forEach(el => {
                el.classList.remove('active');
            });
            // Add active to this one
            this.classList.add('active');
        });
    });
}

function navigateResults(direction, results, activeResult) {
    if (results.length === 0) return;
    
    let nextIndex = 0;
    
    if (activeResult) {
        const currentIndex = Array.from(results).indexOf(activeResult);
        activeResult.classList.remove('active');
        
        if (direction === 'down') {
            nextIndex = (currentIndex + 1) % results.length;
        } else {
            nextIndex = currentIndex === 0 ? results.length - 1 : currentIndex - 1;
        }
    }
    
    results[nextIndex].classList.add('active');
    results[nextIndex].scrollIntoView({ block: 'nearest' });
}

function highlightSearchTerm(text, term) {
    if (!term || term.length < 2) return text;
    
    const regex = new RegExp(`(${term})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
}

function searchFor(term) {
    const searchInput = document.getElementById('job-search');
    searchInput.value = term;
    searchInput.focus();
    performSearch(term);
}

function goToJobListings() {
    const query = currentSearchQuery || document.getElementById('job-search').value;
    if (query) {
        window.location.href = `joblistings.php?search=${encodeURIComponent(query)}`;
    } else {
        window.location.href = 'joblistings.php';
    }
}

function loadNotificationCount() {
    fetch('../../backend/candidate/get_notification_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stats.overall) {
                const unreadCount = data.stats.overall.unread_count;
                const badge = document.getElementById('notification-badge');
                
                if (unreadCount > 0) {
                    badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading notification count:', error);
        });
}

// Refresh notification count every 30 seconds
setInterval(loadNotificationCount, 30000);
</script>

<style>
.search-bar {
    background: #fff;
    padding: 20px 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 100;
    gap: 20px;
}

.search-container {
    flex: 1;
    max-width: 600px;
    position: relative;
}

.search-input {
    position: relative;
    display: flex;
    align-items: center;
}

/* INPUT FIELD - NO ICON INSIDE */
#job-search {
    width: 100%;
padding: 12px 55px 12px 20px;
    border: 2px solid #e1e5e9;
    border-radius: 25px;
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

#job-search:focus {
    border-color: #2F8A99;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(47, 138, 153, 0.1);
}

/* CLEAR BUTTON (X) - INSIDE INPUT, RIGHT SIDE */
.search-clear {
    position: absolute;
    right: 20px;  /* Changed from 18px to 20px */
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    cursor: pointer;
    z-index: 3;
    transition: all 0.2s ease;
    opacity: 0;
    visibility: hidden;
    font-size: 16px;  /* Made icon slightly bigger */
    width: 22px;  /* Made button area bigger */
    height: 22px;  /* Made button area bigger */
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.search-clear:hover {
    color: #666;
    background: rgba(0,0,0,0.05);
}

.search-clear.visible {
    opacity: 1;
    visibility: visible;
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    margin-top: 8px;
}

.search-results-dropdown.show {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.results-section {
    border-bottom: 1px solid #f0f0f0;
}

.results-section:last-child {
    border-bottom: none;
}

.results-section-header {
    padding: 12px 16px 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #f8f9fa;
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f8f9fa;
}

.search-result-item:hover,
.search-result-item.active {
    background: #f0f8ff;
    border-left: 3px solid #2F8A99;
}

.search-result-item:last-child {
    border-bottom: none;
}

.result-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    flex-shrink: 0;
}

.result-icon i {
    font-size: 18px;
    color: #2F8A99;
}

.company-logo-small {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.result-content {
    flex: 1;
    min-width: 0;
}

.result-title {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
    line-height: 1.3;
}

.result-title mark {
    background: #fff59d;
    padding: 1px 3px;
    border-radius: 2px;
}

.result-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 2px;
}

.result-location {
    font-size: 12px;
    color: #999;
}

.result-meta {
    font-size: 11px;
    color: #777;
    margin-top: 4px;
}

.result-action {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #2F8A99;
    font-size: 12px;
    font-weight: 500;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.search-result-item:hover .result-action {
    opacity: 1;
}

.search-footer {
    padding: 12px 16px;
    border-top: 1px solid #f0f0f0;
    background: #f8f9fa;
}

.view-all-results {
    width: 100%;
    padding: 10px;
    background: transparent;
    border: 1px solid #2F8A99;
    color: #2F8A99;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.view-all-results:hover {
    background: #2F8A99;
    color: white;
}

.search-loading,
.search-error,
.no-results {
    padding: 30px 20px;
    text-align: center;
}

.search-loading {
    color: #2F8A99;
}

.search-loading i {
    font-size: 20px;
    margin-bottom: 8px;
}

.search-error {
    color: #d32f2f;
}

.no-results-icon i {
    font-size: 40px;
    color: #ccc;
    margin-bottom: 12px;
}

.no-results-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.no-results-text {
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
}

.search-suggestions {
    margin: 20px 0;
}

.suggestions-title {
    font-size: 13px;
    color: #666;
    margin-bottom: 10px;
}

.suggestions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}

.suggestion-item {
    padding: 6px 12px;
    background: #f0f8ff;
    border: 1px solid #e0e8f0;
    border-radius: 15px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.suggestion-item:hover {
    background: #2F8A99;
    color: white;
    border-color: #2F8A99;
}

.search-status {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    padding: 8px 16px;
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-top: none;
    border-radius: 0 0 8px 8px;
    font-size: 12px;
    color: #666;
    display: none;
    z-index: 999;
}

/* NOTIFICATION ICONS - NOW WITH SEARCH BUTTON */
.notification-icons {
    display: flex;
    align-items: center;
    gap: 15px;
}

.search-btn {
    background: linear-gradient(135deg, #2F8A99, #267A87);
    color: white;
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(47, 138, 153, 0.3);
}

.search-btn:hover {
    background: linear-gradient(135deg, #267A87, #1e6670);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(47, 138, 153, 0.4);
}

.search-btn i {
    font-size: 16px;
}

.notification-link {
    position: relative;
    color: #666;
    font-size: 20px;
    text-decoration: none;
    transition: color 0.2s ease;
}

.notification-link:hover {
    color: #2F8A99;
}

.notification-badge {
    position: absolute;
    top: -6px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    min-width: 16px;
    text-align: center;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #2F8A99;
    color: white;
}

.btn-primary:hover {
    background: #267A87;
}

/* Responsive */
@media (max-width: 768px) {
    .search-bar {
        padding: 15px 20px;
        flex-direction: column;
        gap: 15px;
    }
    
    .search-container {
        width: 100%;
        max-width: none;
    }
    
    #job-search {
        font-size: 14px;
    }
    
    .notification-icons {
        width: 100%;
        justify-content: flex-end;
    }
    
    .search-results-dropdown {
        max-height: 300px;
    }
    
    .search-result-item {
        padding: 10px 12px;
    }
    
    .result-icon {
        width: 35px;
        height: 35px;
        margin-right: 10px;
    }
    
    .suggestions-list {
        justify-content: flex-start;
    }
}
</style>