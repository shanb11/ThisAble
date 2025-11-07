<?php
/**
 * ONE-TIME DATABASE MIGRATION SCRIPT FOR RAILWAY
 * 
 * INSTRUCTIONS:
 * 1. Upload this file to: C:\xampp\htdocs\ThisAble\migrate_close_account.php
 * 2. Deploy to Railway
 * 3. Visit: https://thisable-production.up.railway.app/migrate_close_account.php?secret=thisable2025
 * 4. Wait for success message
 * 5. DELETE THIS FILE after successful migration!
 * 
 * SECURITY: Change the secret key below before using!
 */

// Security check - CHANGE THIS SECRET KEY!
$secret = $_GET['secret'] ?? '';
if ($secret !== 'thisable2025') {  // CHANGE THIS!
    die('Unauthorized. Please provide correct secret key in URL: ?secret=your-key');
}

require_once 'backend/db.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - Close Account Feature</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .step {
            margin: 20px 0;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }
        .step-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .icon {
            font-size: 20px;
            margin-right: 8px;
        }
        .final-steps {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .final-steps h2 {
            margin-bottom: 15px;
        }
        .final-steps ol {
            margin-left: 20px;
            line-height: 1.8;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚂 Database Migration - Close Account Feature</h1>
            <p>Adding account_status and closed_at columns to Railway MySQL</p>
        </div>

        <div class="content">
            <?php
            try {
                // Step 1: Add columns to job_seekers
                echo '<div class="step">';
                echo '<div class="step-title"><span class="icon">📋</span>Step 1: Updating job_seekers table</div>';
                
                $stmt = $conn->query("SHOW COLUMNS FROM job_seekers LIKE 'account_status'");
                if ($stmt->rowCount() > 0) {
                    echo '<div class="warning">⚠️ Column "account_status" already exists in job_seekers. Skipping...</div>';
                } else {
                    $sql = "ALTER TABLE `job_seekers` 
                            ADD COLUMN `account_status` ENUM('active', 'closed') DEFAULT 'active' AFTER `province`,
                            ADD COLUMN `closed_at` DATETIME NULL AFTER `account_status`";
                    $conn->exec($sql);
                    echo '<div class="success">✅ Successfully added columns to job_seekers table!</div>';
                    echo '<div class="code">account_status ENUM(\'active\', \'closed\') DEFAULT \'active\'<br>';
                    echo 'closed_at DATETIME NULL</div>';
                }
                echo '</div>';

                // Step 2: Add columns to employers
                echo '<div class="step">';
                echo '<div class="step-title"><span class="icon">🏢</span>Step 2: Updating employers table</div>';
                
                $stmt = $conn->query("SHOW COLUMNS FROM employers LIKE 'account_status'");
                if ($stmt->rowCount() > 0) {
                    echo '<div class="warning">⚠️ Column "account_status" already exists in employers. Skipping...</div>';
                } else {
                    $sql = "ALTER TABLE `employers` 
                            ADD COLUMN `account_status` ENUM('active', 'closed') DEFAULT 'active' AFTER `verification_status`,
                            ADD COLUMN `closed_at` DATETIME NULL AFTER `account_status`";
                    $conn->exec($sql);
                    echo '<div class="success">✅ Successfully added columns to employers table!</div>';
                    echo '<div class="code">account_status ENUM(\'active\', \'closed\') DEFAULT \'active\'<br>';
                    echo 'closed_at DATETIME NULL</div>';
                }
                echo '</div>';

                // Step 3: Create indexes
                echo '<div class="step">';
                echo '<div class="step-title"><span class="icon">⚡</span>Step 3: Creating indexes for performance</div>';
                
                // Index on job_seekers
                try {
                    $conn->exec("CREATE INDEX `idx_seekers_status` ON `job_seekers`(`account_status`, `closed_at`)");
                    echo '<div class="success">✅ Created index on job_seekers table!</div>';
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                        echo '<div class="warning">⚠️ Index already exists on job_seekers. Skipping...</div>';
                    } else {
                        throw $e;
                    }
                }

                // Index on employers
                try {
                    $conn->exec("CREATE INDEX `idx_employers_status` ON `employers`(`account_status`, `closed_at`)");
                    echo '<div class="success">✅ Created index on employers table!</div>';
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                        echo '<div class="warning">⚠️ Index already exists on employers. Skipping...</div>';
                    } else {
                        throw $e;
                    }
                }
                echo '</div>';

                // Step 4: Verification
                echo '<div class="step">';
                echo '<div class="step-title"><span class="icon">🔍</span>Step 4: Verifying changes</div>';
                
                // Verify job_seekers
                $stmt = $conn->query("DESCRIBE job_seekers");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $found_status = false;
                $found_closed = false;
                
                foreach ($columns as $column) {
                    if ($column['Field'] === 'account_status') $found_status = true;
                    if ($column['Field'] === 'closed_at') $found_closed = true;
                }
                
                if ($found_status && $found_closed) {
                    echo '<div class="success">✅ Verified: job_seekers table structure is correct!</div>';
                } else {
                    echo '<div class="error">❌ Error: Required columns not found in job_seekers table!</div>';
                }

                // Verify employers
                $stmt = $conn->query("DESCRIBE employers");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $found_status = false;
                $found_closed = false;
                
                foreach ($columns as $column) {
                    if ($column['Field'] === 'account_status') $found_status = true;
                    if ($column['Field'] === 'closed_at') $found_closed = true;
                }
                
                if ($found_status && $found_closed) {
                    echo '<div class="success">✅ Verified: employers table structure is correct!</div>';
                } else {
                    echo '<div class="error">❌ Error: Required columns not found in employers table!</div>';
                }
                
                // Count existing accounts
                $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
                $candidate_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                $stmt = $conn->query("SELECT COUNT(*) as count FROM employers");
                $employer_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo '<div class="info">ℹ️ Database Statistics:<br>';
                echo "• Total Candidates: <strong>$candidate_count</strong> (all now have status 'active')<br>";
                echo "• Total Employers: <strong>$employer_count</strong> (all now have status 'active')</div>";
                
                echo '</div>';

                // Success message
                echo '<div class="final-steps">';
                echo '<h2>🎉 Migration Completed Successfully!</h2>';
                echo '<p style="margin: 15px 0;">Your Railway database has been updated with the Close Account feature columns.</p>';
                echo '<h3 style="margin-top: 20px;">📋 Next Steps:</h3>';
                echo '<ol>';
                echo '<li><strong>DELETE THIS FILE</strong> (migrate_close_account.php) immediately for security!</li>';
                echo '<li>Upload backend PHP files to your Railway deployment</li>';
                echo '<li>Update frontend JavaScript files</li>';
                echo '<li>Update your Flutter mobile app</li>';
                echo '<li>Test the close account feature with a test account</li>';
                echo '<li>Set up the cron job for automatic deletion (see guide)</li>';
                echo '</ol>';
                echo '</div>';

            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<h3 style="margin-bottom: 10px;">❌ Database Error</h3>';
                echo '<p><strong>Error Message:</strong></p>';
                echo '<div class="code">' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<p style="margin-top: 15px;"><strong>Troubleshooting Tips:</strong></p>';
                echo '<ul style="margin-left: 20px; line-height: 1.8;">';
                echo '<li>Check your database connection in backend/db.php</li>';
                echo '<li>Verify Railway database is running</li>';
                echo '<li>Ensure database user has ALTER TABLE privileges</li>';
                echo '<li>Check Railway dashboard for database status</li>';
                echo '</ul>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<h3 style="margin-bottom: 10px;">❌ An Error Occurred</h3>';
                echo '<div class="code">' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="footer">
            <p>ThisAble Job Portal - Database Migration Tool</p>
            <p style="margin-top: 5px; font-size: 12px;">Remember to delete this file after successful migration!</p>
        </div>
    </div>
</body>
</html>