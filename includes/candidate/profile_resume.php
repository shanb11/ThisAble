<div class="section-content">
    <div class="resume-container">
        <?php
        // Fetch resume information
        $resume_query = "SELECT * FROM resumes WHERE seeker_id = :seeker_id AND is_current = 1 ORDER BY upload_date DESC LIMIT 1";
        $resume_stmt = $conn->prepare($resume_query);
        $resume_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
        $resume_stmt->execute();
        $resume = $resume_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resume) {
            // Format the file size
            $size_kb = round($resume['file_size'] / 1024);
            $size_mb = $size_kb > 1024 ? round($size_kb / 1024, 1) . ' MB' : $size_kb . ' KB';
            
            // Get file extension for icon and behavior
            $file_ext = strtolower(pathinfo($resume['file_name'], PATHINFO_EXTENSION));
            $file_icon = 'fa-file-alt';
            $icon_color = '#666';
            $can_preview = false;
            
            if ($file_ext == 'pdf') {
                $file_icon = 'fa-file-pdf';
                $icon_color = '#f44336';
                $can_preview = true; // PDFs can be previewed directly
            } elseif (in_array($file_ext, ['doc', 'docx'])) {
                $file_icon = 'fa-file-word';
                $icon_color = '#2196f3';
                $can_preview = false; // Word docs need special handling
            }
            
            // Format date
            $upload_date = date('F j, Y', strtotime($resume['upload_date']));
            
            // Create secure URLs using the new backend handler
            $view_url = '../../backend/candidate/view_resume.php?action=view&resume_id=' . $resume['resume_id'];
            $preview_url = '../../backend/candidate/view_resume.php?action=preview&resume_id=' . $resume['resume_id'];
            $download_url = '../../backend/candidate/view_resume.php?action=download&resume_id=' . $resume['resume_id'];
            
            echo '<div class="current-resume">';
            echo '<div class="resume-preview">';
            echo '<i class="fas ' . $file_icon . '" style="color: ' . $icon_color . ';"></i>';
            echo '<div class="resume-info">';
            echo '<span class="resume-filename">' . htmlspecialchars($resume['file_name']) . '</span>';
            echo '<span class="resume-meta">';
            echo '<span class="resume-date">Uploaded: ' . $upload_date . '</span>';
            echo '<span class="resume-size">(' . $size_mb . ')</span>';
            echo '</span>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="resume-actions">';
            
            if ($can_preview) {
                // For PDFs - direct view button
                echo '<button class="btn view-resume-btn" onclick="viewResumeInTab(\'' . $view_url . '\', \'' . htmlspecialchars($resume['file_name']) . '\')" title="View in browser">';
                echo '<i class="fas fa-eye"></i> View</button>';
            } else {
                // For Word docs - preview page
                echo '<button class="btn view-resume-btn" onclick="viewResumeInTab(\'' . $preview_url . '\', \'' . htmlspecialchars($resume['file_name']) . '\')" title="Preview document">';
                echo '<i class="fas fa-search"></i> Preview</button>';
            }
            
            // Download button
            echo '<button class="btn download-resume-btn" onclick="downloadResume(\'' . htmlspecialchars($download_url) . '\'); return false;" title="Download file">';
            echo '<i class="fas fa-download"></i> Download</button>';
                        
            // Delete button (optional)
            echo '<button class="btn delete-resume-btn" onclick="confirmDeleteResume(' . $resume['resume_id'] . ')" title="Delete Resume">';
            echo '<i class="fas fa-trash-alt"></i></button>';
            echo '</div>';
            
            // File type info
            echo '<div class="file-type-info">';
            if ($can_preview) {
                echo '<div class="info-badge success">';
                echo '<i class="fas fa-check-circle"></i>';
                echo '<span>Can be viewed in browser</span>';
                echo '</div>';
            } else {
                echo '<div class="info-badge info">';
                echo '<i class="fas fa-info-circle"></i>';
                echo '<span>Download to view with Word or compatible app</span>';
                echo '</div>';
            }
            echo '</div>';
            
            echo '</div>';
            
            // Resume statistics
            echo '<div class="resume-stats">';
            echo '<div class="stat-item">';
            echo '<i class="fas fa-file-alt stat-icon"></i>';
            echo '<span class="stat-label">Type</span>';
            echo '<span class="stat-value">' . strtoupper($file_ext) . '</span>';
            echo '</div>';
            echo '<div class="stat-item">';
            echo '<i class="fas fa-hdd stat-icon"></i>';
            echo '<span class="stat-label">Size</span>';
            echo '<span class="stat-value">' . $size_mb . '</span>';
            echo '</div>';
            echo '<div class="stat-item">';
            echo '<i class="fas fa-calendar stat-icon"></i>';
            echo '<span class="stat-label">Updated</span>';
            echo '<span class="stat-value">' . date('M j', strtotime($resume['upload_date'])) . '</span>';
            echo '</div>';
            echo '</div>';
            
        } else {
            echo '<div class="no-resume">';
            echo '<div class="empty-state">';
            echo '<i class="fas fa-file-upload fa-3x empty-icon"></i>';
            echo '<h4>No Resume Uploaded</h4>';
            echo '<p>Upload your resume to help employers understand your qualifications and experience.</p>';
            echo '<div class="upload-tips">';
            echo '<div class="tip-item">';
            echo '<i class="fas fa-file-pdf tip-icon"></i>';
            echo '<span><strong>PDF files:</strong> Can be viewed directly in browser</span>';
            echo '</div>';
            echo '<div class="tip-item">';
            echo '<i class="fas fa-file-word tip-icon"></i>';
            echo '<span><strong>Word files:</strong> Preview available, download to edit</span>';
            echo '</div>';
            echo '<div class="tip-item">';
            echo '<i class="fas fa-weight-hanging tip-icon"></i>';
            echo '<span><strong>File size:</strong> Maximum 5MB allowed</span>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        
        <div class="resume-optimization">
            <h3><i class="fas fa-magic"></i> Resume Optimization</h3>
            <div class="optimization-content">
                <?php if ($resume): ?>
                <div class="optimization-score">
                    <div class="score-circle">
                        <span class="score-number">75</span>
                        <span class="score-percent">%</span>
                    </div>
                    <div class="score-details">
                        <div class="score-text">
                            <h4>Good Resume Score</h4>
                            <p>Your resume is optimized for most job applications. A few improvements could boost your visibility.</p>
                        </div>
                        <button class="btn optimize-btn" onclick="showOptimizationTips()">
                            <i class="fas fa-lightbulb"></i> Get Tips
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="optimization-placeholder">
                    <p><i class="fas fa-info-circle"></i> Upload a resume to get personalized optimization tips and score.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
/**
 * Resume Section JavaScript - FINAL CLEAN VERSION - No Hash Navigation
 */

// Prevent multiple script executions
if (typeof window.resumeScriptLoaded === 'undefined') {
    window.resumeScriptLoaded = true;

    /**
     * View resume in new tab - FINAL CLEAN VERSION
     */
    window.viewResumeInTab = function(url, filename) {
        console.log('🔍 Opening resume:', filename);
        
        // Stop any existing navigation
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Prevent multiple clicks
        if (window.resumeOpening) {
            console.log('❌ Resume already opening');
            return false;
        }
        
        window.resumeOpening = true;
        
        // Show loading toast
        const toast = showLoadingToast('Opening resume...');
        
        try {
            // Clean URL - remove any hash
            const cleanUrl = url.split('#')[0];
            console.log('🔗 Clean URL:', cleanUrl);
            
            // Open in new tab
            const resumeTab = window.open(cleanUrl, '_blank', 'noopener,noreferrer');
            
            if (!resumeTab) {
                hideLoadingToast(toast);
                alert('Popup blocked! Please allow popups and try again.');
            } else {
                console.log('✅ Resume tab opened successfully');
                setTimeout(() => hideLoadingToast(toast), 1000);
            }
            
        } catch (error) {
            console.error('Error opening resume:', error);
            hideLoadingToast(toast);
            alert('Unable to open resume. Please try again.');
        } finally {
            setTimeout(() => {
                window.resumeOpening = false;
            }, 2000);
        }
        
        return false; // Prevent any default action
    };

    /**
     * Confirm resume deletion - CLEAN VERSION
     */
    window.confirmDeleteResume = function(resumeId) {
        // Stop any navigation
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        if (window.deleteConfirmOpen) return false;
        window.deleteConfirmOpen = true;
        
        const modal = document.createElement('div');
        modal.className = 'confirmation-modal';
        modal.innerHTML = `
            <div class="modal-content" style="background: white; border-radius: 8px; padding: 25px; max-width: 450px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <h3 style="color: #257180; margin-bottom: 15px;">Confirm Resume Deletion</h3>
                <p style="margin-bottom: 20px; color: #666;">Are you sure you want to delete this resume? This action cannot be undone.</p>
                <div style="display: flex; justify-content: flex-end; gap: 15px;">
                    <button class="cancel-btn" style="background: #f8f9fa; color: #666; border: 1px solid #ddd; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button class="delete-btn" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Delete</button>
                </div>
            </div>
        `;
        
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; align-items: center;
            justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s;
        `;
        
        document.body.appendChild(modal);
        setTimeout(() => modal.style.opacity = '1', 10);
        
        modal.querySelector('.cancel-btn').onclick = function(e) {
            e.preventDefault();
            closeModal();
        };
        
        modal.querySelector('.delete-btn').onclick = function(e) {
            e.preventDefault();
            closeModal();
            deleteResume(resumeId);
        };
        
        function closeModal() {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    modal.remove();
                }
                window.deleteConfirmOpen = false;
            }, 300);
        }
        
        return false;
    };

    /**
     * Delete resume - CLEAN VERSION
     */
    function deleteResume(resumeId) {
        if (window.deleteInProgress) return;
        window.deleteInProgress = true;
        
        const toast = showLoadingToast('Deleting resume...');
        
        fetch('../../backend/candidate/delete_resume.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ resume_id: resumeId })
        })
        .then(response => {
            if (!response.ok) throw new Error('Server error');
            return response.json();
        })
        .then(data => {
            hideLoadingToast(toast);
            
            if (data.success) {
                showNotification('Resume deleted successfully!', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification(data.message || 'Delete failed', 'error');
            }
        })
        .catch(error => {
            hideLoadingToast(toast);
            showNotification('Error: ' + error.message, 'error');
        })
        .finally(() => {
            window.deleteInProgress = false;
        });
    }

    /**
     * Download resume - CLEAN VERSION
     */
    window.downloadResume = function(url) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        console.log('📥 Downloading resume from:', url);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show download notification
        showNotification('Download started!', 'success');
        
        return false;
    };

    /**
     * Show optimization tips - CLEAN VERSION
     */
    window.showOptimizationTips = function() {
        // Stop any navigation
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        if (window.tipsModalOpen) return false;
        window.tipsModalOpen = true;
        
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div style="background: white; border-radius: 8px; padding: 25px; max-width: 600px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="color: #257180; margin: 0;">Resume Optimization Tips</h2>
                    <button class="close-btn" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #999;">&times;</button>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #257180; margin-bottom: 10px;">Key Recommendations</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; display: flex; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: #FD8B51; margin-top: 2px;"></i>
                            <span>Customize your resume for each job application to match specific requirements and keywords.</span>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #f0f0f0; display: flex; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: #FD8B51; margin-top: 2px;"></i>
                            <span>Include measurable achievements rather than just listing responsibilities.</span>
                        </li>
                        <li style="padding: 10px 0; display: flex; gap: 10px;">
                            <i class="fas fa-check-circle" style="color: #FD8B51; margin-top: 2px;"></i>
                            <span>Use clear formatting and ensure your resume is accessible and ATS-friendly.</span>
                        </li>
                    </ul>
                </div>
                
                <div style="text-align: right;">
                    <button class="got-it-btn" style="background: #257180; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Got it!</button>
                </div>
            </div>
        `;
        
        modal.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; align-items: center;
            justify-content: center; z-index: 1000; opacity: 0; transition: opacity 0.3s;
        `;
        
        document.body.appendChild(modal);
        setTimeout(() => modal.style.opacity = '1', 10);
        
        function closeTips() {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    modal.remove();
                }
                window.tipsModalOpen = false;
            }, 300);
        }
        
        modal.querySelector('.close-btn').onclick = function(e) {
            e.preventDefault();
            closeTips();
        };
        
        modal.querySelector('.got-it-btn').onclick = function(e) {
            e.preventDefault();
            closeTips();
        };
        
        return false;
    };

    /**
     * Utility functions
     */
    function showLoadingToast(message) {
        const existing = document.querySelector('.loading-toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = 'loading-toast';
        toast.innerHTML = `<i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i>${message}`;
        
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px; background: white;
            padding: 15px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000; display: flex; align-items: center;
            border-left: 4px solid #257180; font-family: 'Inter', sans-serif;
            transform: translateX(100%); transition: transform 0.3s; color: #333;
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);
        
        return toast;
    }

    function hideLoadingToast(toast) {
        if (toast && document.body.contains(toast)) {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    toast.remove();
                }
            }, 300);
        }
    }

    function showNotification(message, type = 'info') {
        const existing = document.querySelector('.notification');
        if (existing) existing.remove();
        
        const colors = {
            success: { bg: '#d4edda', border: '#28a745', icon: 'fa-check-circle' },
            error: { bg: '#f8d7da', border: '#dc3545', icon: 'fa-exclamation-circle' },
            info: { bg: '#d1ecf1', border: '#17a2b8', icon: 'fa-info-circle' }
        };
        
        const color = colors[type] || colors.info;
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                <i class="fas ${color.icon}" style="color: ${color.border}; font-size: 16px;"></i>
                <span>${message}</span>
            </div>
            <button style="background: none; border: none; color: #666; cursor: pointer; padding: 5px; opacity: 0.7;" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        notification.style.cssText = `
            position: fixed; bottom: 20px; left: 20px; background: ${color.bg};
            border: 1px solid ${color.border}; border-left: 4px solid ${color.border};
            border-radius: 8px; padding: 15px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: space-between; gap: 15px;
            z-index: 1001; transform: translateY(100px); opacity: 0;
            transition: all 0.3s; max-width: 400px; font-family: 'Inter', sans-serif;
            font-size: 14px; color: #333;
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.style.transform = 'translateY(0)';
            notification.style.opacity = '1';
        }, 10);
        
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.remove();
            }
        }, 5000);
    }

    // Prevent any hash navigation on the entire page
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a[href="#"], button[onclick*="return false"]');
        if (target && target.getAttribute('href') === '#') {
            e.preventDefault();
            return false;
        }
    });

    // Prevent form submissions that might cause hash navigation
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.getAttribute('action') === '' || form.getAttribute('action') === '#') {
            e.preventDefault();
            return false;
        }
    });

    console.log('✅ Resume script loaded - clean version with download function');

} // End of script loading guard
</script>