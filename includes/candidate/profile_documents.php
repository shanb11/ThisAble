<?php
/**
 * Profile Documents Display Section
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

    <!-- Documents Grid -->
    <div class="documents-grid" id="documents-grid">
        
        <!-- Diploma Section -->
        <div class="document-category" data-type="diploma">
            <div class="category-header">
                <h4><i class="fas fa-graduation-cap"></i> Diploma/Degree</h4>
                <span class="document-count" id="diploma-count">0</span>
            </div>
            <div class="document-items" id="diploma-items">
                <div class="empty-document-state" id="diploma-empty">
                    <i class="fas fa-file-upload"></i>
                    <p>No diploma uploaded yet</p>
                    <small>Upload your degree certificate</small>
                </div>
            </div>
        </div>

        <!-- Certificates Section -->
        <div class="document-category" data-type="certificate">
            <div class="category-header">
                <h4><i class="fas fa-certificate"></i> Certifications</h4>
                <span class="document-count" id="certificate-count">0</span>
            </div>
            <div class="document-items" id="certificate-items">
                <div class="empty-document-state" id="certificate-empty">
                    <i class="fas fa-file-upload"></i>
                    <p>No certificates uploaded yet</p>
                    <small>Upload your professional certificates</small>
                </div>
            </div>
        </div>

        <!-- Licenses Section -->
        <div class="document-category" data-type="license">
            <div class="category-header">
                <h4><i class="fas fa-id-card"></i> Licenses</h4>
                <span class="document-count" id="license-count">0</span>
            </div>
            <div class="document-items" id="license-items">
                <div class="empty-document-state" id="license-empty">
                    <i class="fas fa-file-upload"></i>
                    <p>No licenses uploaded yet</p>
                    <small>Upload your professional licenses</small>
                </div>
            </div>
        </div>

        <!-- Other Documents Section -->
        <div class="document-category" data-type="other">
            <div class="category-header">
                <h4><i class="fas fa-folder"></i> Other Documents</h4>
                <span class="document-count" id="other-count">0</span>
            </div>
            <div class="document-items" id="other-items">
                <div class="empty-document-state" id="other-empty">
                    <i class="fas fa-file-upload"></i>
                    <p>No other documents uploaded yet</p>
                    <small>Upload additional relevant documents</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Documents Summary -->
    <div class="documents-summary" id="documents-summary" style="display: none;">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="summary-content">
                <h5>Documents Overview</h5>
                <div class="summary-stats">
                    <span class="stat">
                        <strong id="total-documents">0</strong> 
                        <small>Total Documents</small>
                    </span>
                    <span class="stat">
                        <strong id="verified-documents">0</strong> 
                        <small>Verified</small>
                    </span>
                </div>
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

<!-- Document Item Template (Hidden) -->
<template id="document-item-template">
    <div class="document-item" data-document-id="">
        <div class="document-icon">
            <i class="fas fa-file-pdf"></i>
        </div>
        <div class="document-info">
            <h5 class="document-name"></h5>
            <div class="document-meta">
                <span class="document-size"></span>
                <span class="document-date"></span>
            </div>
            <div class="document-status">
                <span class="verification-badge unverified">
                    <i class="fas fa-clock"></i> Pending Verification
                </span>
            </div>
        </div>
        <div class="document-actions">
            <button class="action-btn view-btn" title="View Document">
                <i class="fas fa-eye"></i>
            </button>
            <button class="action-btn download-btn" title="Download">
                <i class="fas fa-download"></i>
            </button>
            <button class="action-btn delete-btn" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
// Initialize documents display when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof loadUserDocuments === 'function') {
        loadUserDocuments();
    }
});
</script>