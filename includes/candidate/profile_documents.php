<?php
/**
 * Profile Documents Display Section - Clean Version
 * Save as: includes/candidate/profile_documents.php
 */
?>

<div class="documents-display" id="documents-display">
    <!-- Loading State -->
    <div class="documents-loading" id="documents-loading" style="display: none;">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <p>Loading documents...</p>
    </div>

    <!-- Documents Container -->
    <div class="documents-container" id="documents-container">
        
        <!-- Diploma Section -->
        <div class="document-category" data-type="diploma">
            <div class="category-header">
                <h4><i class="fas fa-graduation-cap"></i> Diploma/Degree</h4>
                <span class="document-count" id="diploma-count">0</span>
            </div>
            <div class="document-items" id="diploma-items">
                <!-- Documents will be inserted here by JavaScript -->
            </div>
        </div>

        <!-- Certificates Section -->
        <div class="document-category" data-type="certificate">
            <div class="category-header">
                <h4><i class="fas fa-certificate"></i> Certifications</h4>
                <span class="document-count" id="certificate-count">0</span>
            </div>
            <div class="document-items" id="certificate-items">
                <!-- Documents will be inserted here by JavaScript -->
            </div>
        </div>

        <!-- Licenses Section -->
        <div class="document-category" data-type="license">
            <div class="category-header">
                <h4><i class="fas fa-id-card"></i> Licenses</h4>
                <span class="document-count" id="license-count">0</span>
            </div>
            <div class="document-items" id="license-items">
                <!-- Documents will be inserted here by JavaScript -->
            </div>
        </div>

        <!-- Other Documents Section -->
        <div class="document-category" data-type="other">
            <div class="category-header">
                <h4><i class="fas fa-folder"></i> Other Documents</h4>
                <span class="document-count" id="other-count">0</span>
            </div>
            <div class="document-items" id="other-items">
                <!-- Documents will be inserted here by JavaScript -->
            </div>
        </div>

    </div>

    <!-- Error State -->
    <div class="documents-error" id="documents-error" style="display: none;">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h4>Unable to Load Documents</h4>
        <p id="documents-error-message">Please try again later.</p>
        <button class="btn secondary-btn" onclick="loadUserDocuments()">
            <i class="fas fa-refresh"></i> Retry
        </button>
    </div>
</div>