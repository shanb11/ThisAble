<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../includes/candidate/session_check.php';
require_login(); // Add this line to enforce login check
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Job Listings</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/candidate/joblistings.css">
        <style>
            /* Loading and notification styles */
            .loading-container {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 300px;
                flex-direction: column;
                gap: 15px;
                color: #666;
            }
            
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .jobs-empty {
                text-align: center;
                padding: 60px 20px;
                color: #666;
                grid-column: 1 / -1;
            }
            
            .jobs-empty i {
                font-size: 64px;
                margin-bottom: 20px;
                color: #ddd;
            }
            
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 12px 20px;
                border-radius: 6px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                z-index: 1000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                font-size: 14px;
                max-width: 300px;
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification.error {
                background: #dc3545;
            }
            
            .notification.info {
                background: #17a2b8;
            }
            
            .section-stats {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
                font-size: 14px;
                color: #666;
                align-items: center;
            }
            
            .section-stats .loading-text {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .mini-spinner {
                width: 16px;
                height: 16px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            /* Save button states */
            .action-btn.saved {
                color: #007bff;
            }
            
            .action-btn.saved i {
                color: #007bff;
            }
            
            /* Apply button states */
            .apply-btn.applied {
                background: #28a745;
                cursor: not-allowed;
                opacity: 0.8;
            }
            
            .apply-btn:disabled {
                background: #6c757d;
                cursor: not-allowed;
            }
            
            /* Job stats */
            .job-stats {
                display: flex;
                gap: 15px;
                font-size: 12px;
                color: #666;
            }
            
            .job-stats .stat {
                display: flex;
                align-items: center;
                gap: 4px;
            }
            
            /* Search loading state */
            .search-input.loading::after {
                content: '';
                position: absolute;
                right: 40px;
                top: 50%;
                transform: translateY(-50%);
                width: 16px;
                height: 16px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            .search-input {
                position: relative;
            }

            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }

            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 0;
                border: none;
                border-radius: 12px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                animation: modalSlideIn 0.3s ease;
            }

            @keyframes modalSlideIn {
                from { opacity: 0; transform: scale(0.9); }
                to { opacity: 1; transform: scale(1); }
            }

            .modal-header {
                background: linear-gradient(135deg, #257180, #2F8A99);
                color: white;
                padding: 20px;
                border-radius: 12px 12px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .modal-header h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }

            .close {
                color: white;
                font-size: 24px;
                font-weight: bold;
                cursor: pointer;
                background: none;
                border: none;
                padding: 0;
                line-height: 1;
            }

            .close:hover {
                opacity: 0.7;
            }

            .modal-body {
                padding: 24px;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #333;
                font-size: 14px;
            }

            .form-group textarea {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-family: inherit;
                font-size: 14px;
                resize: vertical;
                min-height: 80px;
            }

            .form-group textarea:focus {
                outline: none;
                border-color: #2F8A99;
                box-shadow: 0 0 0 2px rgba(47, 138, 153, 0.1);
            }

            .modal-footer {
                padding: 20px 24px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }

            .btn-primary {
                background: #FD8B51;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.2s ease;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .btn-primary:hover {
                background: #CB6040;
            }

            .btn-primary:disabled {
                background: #ccc;
                cursor: not-allowed;
            }

            .btn-secondary {
                background: transparent;
                color: #666;
                border: 1px solid #ddd;
                padding: 12px 24px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.2s ease;
            }

            .btn-secondary:hover {
                background: #f5f5f5;
            }

            /* Mobile responsive */
            @media (max-width: 768px) {
                .modal-content {
                    width: 95%;
                    margin: 10% auto;
                }
                
                .modal-body {
                    padding: 16px;
                }
                
                .modal-footer {
                    flex-direction: column;
                    padding: 16px;
                }
                
                .btn-primary, .btn-secondary {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    </head>
    <body>
        <!-- Sidebar -->
        <?php include('../../includes/candidate/sidebar.php'); ?>
        
        <!-- Main Content -->
        <main class="main-content" id="main-content">
            <!-- Search and Filter Section -->
            <div class="search-bar" role="search" aria-label="Search and filter jobs">
                <div class="search-input">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" id="job-search" placeholder="Search for jobs..." aria-label="Search for jobs">
                </div>
                <button class="filter-btn" id="filter-btn" aria-haspopup="true">
                    <i class="fas fa-sliders-h" aria-hidden="true"></i>
                    Filter Options
                </button>
            </div>
            
            <!-- Section Header -->
            <header class="section-header">
                <h1 class="section-title">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    Opportunities Just For You
                </h1>
            </header>
            <p class="section-subtitle">Discover opportunities tailored to your skills and profile — all jobs are PWD-friendly with accessibility features.</p>
            
            <!-- Section Stats -->
            <div class="section-stats">
                <span id="jobs-count">
                    <span class="loading-text">
                        <div class="mini-spinner"></div>
                        Loading opportunities...
                    </span>
                </span>
                <span id="search-info"></span>
            </div>
            
            <!-- Jobs Grid - Will be populated dynamically -->
            <div class="jobs-grid" role="list" id="jobs-container">
                <!-- Loading State -->
                <div class="loading-container" id="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Finding the best opportunities for you...</p>
                </div>
            </div>
        </main>

        <!-- Notification Toast -->
        <div class="notification" id="notification"></div>

        <!-- Simple Application Modal -->
        <div id="application-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modal-job-title">Apply for Job</h3>
                    <span class="close" id="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p id="modal-company-name"></p>
                    <div class="form-group">
                        <label for="cover-letter">Cover Letter (Optional):</label>
                        <textarea id="cover-letter" placeholder="Write a brief message to the employer..." rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="accessibility-needs">Accessibility Needs (Optional):</label>
                        <textarea id="accessibility-needs" placeholder="Describe any accommodations you may need..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="submit-application" class="btn-primary">Submit Application</button>
                    <button id="cancel-application" class="btn-secondary">Cancel</button>
                </div>
            </div>
        </div>

        <script>
            // Pass PHP session data to JavaScript
            window.candidateData = {
                seekerId: <?php echo json_encode($_SESSION['seeker_id'] ?? null); ?>,
                userName: <?php echo json_encode($_SESSION['user_name'] ?? ''); ?>,
                isLoggedIn: <?php echo json_encode(isset($_SESSION['seeker_id'])); ?>
            };
            
            // Debug info
            console.log('Candidate Data:', window.candidateData);
        </script>
        
        <!-- Load only the unified script -->
        <script src="../../scripts/candidate/joblistings-unified.js"></script>
               
        <?php
            // Add this right before the closing body tag
            if (function_exists('echo_session_sync_script')) {
                echo_session_sync_script();
            }
        ?>


<!-- Accessibility Features -->
<button class="accessibility-toggle" aria-label="Toggle Accessibility Options">
    <i class="fas fa-universal-access"></i>
</button>

<div class="accessibility-panel" style="display: none;">
        <h3>Accessibility Options</h3>
    
    <!-- High Contrast Mode -->
    <div class="accessibility-option">
        <label for="high-contrast">High Contrast</label>
        <label class="toggle-switch">
            <input type="checkbox" id="high-contrast">
            <span class="slider"></span>
        </label>
    </div>
    
    <!-- Reduce Motion -->
    <div class="accessibility-option">
        <label for="reduce-motion">Reduce Motion</label>
        <label class="toggle-switch">
            <input type="checkbox" id="reduce-motion">
            <span class="slider"></span>
        </label>
    </div>
    
    <!-- Font Size Controls -->
    <div class="accessibility-option">
        <label>Font Size</label>
    </div>
    <div class="font-size-controls">
        <button class="font-size-btn" id="decrease-font" aria-label="Decrease font size">
            <i class="fas fa-minus"></i>
        </button>
        <span class="font-size-value">100%</span>
        <button class="font-size-btn" id="increase-font" aria-label="Increase font size">
            <i class="fas fa-plus"></i>
        </button>
    </div>
</div>
    </body>
</html>