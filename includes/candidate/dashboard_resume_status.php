<div class="dashboard-card resume-status">
    <div class="card-header">
        <h3><i class="fas fa-file-alt"></i> Resume</h3>
    </div>
    <div class="card-content">
        <?php
        // Get database connection
        require_once '../../backend/db.php';
        
        // Get the user's current resume - PDO version
        $resumeSql = "SELECT * FROM resumes WHERE seeker_id = ? AND is_current = 1";
        $resumeStmt = $conn->prepare($resumeSql);
        $resumeStmt->bindParam(1, $_SESSION['seeker_id'], PDO::PARAM_INT);
        $resumeStmt->execute();
        $resume = $resumeStmt->fetch(PDO::FETCH_ASSOC);

        if ($resume) {
            // Display resume information
            ?>
            <div class="resume-info">
                <div class="resume-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="resume-details">
                    <p class="resume-filename"><?php echo htmlspecialchars($resume['file_name']); ?></p>
                    <p class="resume-meta">Uploaded: <?php echo date('F j, Y', strtotime($resume['upload_date'])); ?></p>
                </div>
                <div class="resume-actions">
                    <a href="view_resume.php?id=<?php echo $resume['resume_id']; ?>" class="btn btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="update_resume.php" class="btn btn-sm">
                        <i class="fas fa-sync-alt"></i> Update
                    </a>
                </div>
            </div>
            <?php
        } else {
            // No resume uploaded
            ?>
            <div class="no-resume">
                <div class="empty-state">
                    <i class="fas fa-file-upload"></i>
                    <p>You haven't uploaded your resume yet</p>
                    <a href="uploadresume.php" class="btn btn-primary">Upload Resume</a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>