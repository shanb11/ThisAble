<?php


//session_start();
require_once '../../backend/shared/session_helper.php';


echo "<!-- AFTER SESSION HELPER: ";
echo "Status: " . session_status() . ", ";
echo "ID: " . session_id() . ", ";
echo "employer_id: " . ($_SESSION['employer_id'] ?? 'NOT SET') . ", ";
echo "logged_in: " . ($_SESSION['logged_in'] ?? 'NOT SET');
echo " -->";

// Check if employer is logged in
requireEmployerLogin('emplogin.php');
// Check if employer is logged in
requireEmployerLogin('emplogin.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Applicants | Employer Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../../styles/employer/empapplicants.css">
        <link rel="stylesheet" href="../../styles/employer/empsidebar.css">
    </head>
    <body>
        <!-- Output session data for JavaScript -->
        <?php echoEmployerSessionScript(); ?>
        
        <!-- Sidebar -->
        <?php include('../../includes/employer/empsidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="search-bar">
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search applicants by name, skills..." id="search-input">
                </div>
                <div class="notification-icons">
                    <a href="empnotifications.php">
                        <i class="far fa-bell" title="View Notifications"></i>
                    </a>
                </div>
            </div>

            <div class="page-header">
                <div class="page-title">
                    <h1>Applicants</h1>
                    <p>Manage candidates who applied for your job postings</p>
                </div>
                
                <!-- Quick Stats (Optional - will be populated by JS if elements exist) -->
                <div class="quick-stats" style="display: none;">
                    <div class="stat-item">
                        <span class="stat-number" id="total-applications">0</span>
                        <span class="stat-label">Total Applications</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="new-applications">0</span>
                        <span class="stat-label">New</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="interviews-scheduled">0</span>
                        <span class="stat-label">Interviews</span>
                    </div>
                </div>
            </div>

            <!-- Add this to your empapplicants.php file, after the page-header div -->

            <!-- Match Calculation Control Panel -->
            <div class="match-control-panel" style="margin: 20px 0; padding: 16px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                <div class="control-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div>
                        <h3 style="margin: 0; font-size: 16px; color: #333;">
                            <i class="fas fa-calculator"></i> Job Matching System
                        </h3>
                        <p style="margin: 4px 0 0 0; font-size: 14px; color: #666;">
                            Calculate compatibility scores for all applicants
                        </p>
                    </div>
                    <div class="match-actions">
                        <button class="btn btn-primary" id="calculate-matches-btn" style="display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-sync"></i>
                            <span>Calculate Matches</span>
                        </button>
                    </div>
                </div>
    
                <!-- Match Statistics Display -->
                <div class="match-stats" id="match-stats" style="display: none;">
                    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px;">
                        <div class="stat-card excellent" style="padding: 12px; background: #d1fae5; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="excellent-count" style="font-size: 20px; font-weight: bold; color: #065f46;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #065f46;">Excellent (90%+)</div>
                        </div>
                        <div class="stat-card good" style="padding: 12px; background: #dbeafe; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="good-count" style="font-size: 20px; font-weight: bold; color: #1e40af;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #1e40af;">Good (75-89%)</div>
                        </div>
                        <div class="stat-card fair" style="padding: 12px; background: #fef3c7; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="fair-count" style="font-size: 20px; font-weight: bold; color: #92400e;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #92400e;">Fair (60-74%)</div>
                        </div>
                        <div class="stat-card poor" style="padding: 12px; background: #fee2e2; border-radius: 6px; text-align: center;">
                            <div class="stat-number" id="poor-count" style="font-size: 20px; font-weight: bold; color: #991b1b;">0</div>
                            <div class="stat-label" style="font-size: 12px; color: #991b1b;">Needs Review</div>
                        </div>
                    </div>
                    <div class="overall-stats" style="margin-top: 12px; display: flex; justify-content: space-between; font-size: 14px; color: #666;">
                        <span>Average Score: <strong id="average-score">0%</strong></span>
                        <span>Processed: <strong id="processed-count">0</strong> applicants</span>
                    </div>
                </div>
                
                <!-- Progress Indicator -->
                <div class="calculation-progress" id="calculation-progress" style="display: none; margin-top: 12px;">
                    <div class="progress-bar" style="height: 4px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                        <div class="progress-fill" id="progress-fill" style="height: 100%; background: #3b82f6; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <div class="progress-text" id="progress-text" style="margin-top: 8px; font-size: 14px; color: #666;">
                        Calculating matches...
                    </div>
                </div>
            </div>

            <!-- Bulk Selection Header (Created dynamically by JS) -->
            <!-- Select All Container (Created dynamically by JS) -->
    
            <?php include('../../includes/employer/empapplicants_filter.php'); ?>

            <!-- Active Filters display -->
            <?php include('../../includes/employer/empapplicants_active.php'); ?>

            <!-- Applicants Grid - will be populated by JavaScript -->
            <div class="applicants-grid" id="applicants-grid">
                <!-- Loading placeholder -->
                <div class="loading-placeholder">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading applicants...</p>
                </div>
            </div>

            <!-- No Results State (Hidden by default) -->
            <div class="no-results" id="no-results" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No applicants found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>

            <!-- Success Message -->
            <div class="status-confirmation" id="status-confirmation">
                Status updated successfully!
            </div>

            <!-- Loading overlay -->
            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="spinner"></div>
            </div>
        </div>
        
        <!-- ===================================
             MODALS SECTION
             =================================== -->
        
        <!-- Applicant Profile Modal -->
        <?php include('../../modals/employer/empapplicants_applicant.php'); ?>

        <!-- Status update confirmation dialog -->
        <?php include('../../modals/employer/empapplicants_status_modal.php'); ?>

        <!-- Interview Scheduling Modals -->
        <?php include('../../modals/employer/schedule_interview_modal.php'); ?>

        <!-- Bulk Actions Modals -->
        <?php include('../../modals/employer/bulk_actions_modal.php'); ?>

        <!-- Load the JavaScript -->
        <script src="../../scripts/employer/empapplicants.js"></script>
        
        <script>
        // Additional initialization for new features
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 ThisAble Applicants System Loaded');
            console.log('✅ Features Available:');
            console.log('   - Applicant Management');
            console.log('   - Interview Scheduling');
            console.log('   - Bulk Actions');
            console.log('   - Resume Management');
            console.log('   - PWD Accommodations');
            console.log('   - Notification System');
        });
        </script>
    </body>
</html>