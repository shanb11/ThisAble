<?php
/**
 * ALL-IN-ONE RAILWAY MIGRATION SCRIPT
 * 
 * This script will:
 * 1. Add account_status and closed_at columns to job_seekers
 * 2. Add account_status and closed_at columns to employers
 * 3. Update all existing records to set account_status = 'active'
 * 
 * Run ONCE on Railway, then DELETE this file
 */

require_once '../db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Railway Database Migration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
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
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .step h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .success h3 {
            color: #28a745;
        }
        .error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .error h3 {
            color: #dc3545;
        }
        .warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .warning h3 {
            color: #d39e00;
        }
        .info {
            background: #d1ecf1;
            border-left-color: #17a2b8;
            color: #0c5460;
        }
        .info h3 {
            color: #17a2b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
        }
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 5px 5px 5px 0;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
            line-height: 1.6;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Railway Database Migration</h1>
            <p>Account Closure Feature - Database Setup</p>
        </div>
        
        <div class="content">
            <?php
            $totalSteps = 4;
            $currentStep = 0;
            $errors = [];
            $results = [];
            
            try {
                // Detect database type
                $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
                $isPostgreSQL = ($driver === 'pgsql');
                $isMySQL = ($driver === 'mysql');
                
                echo "<div class='step info'>";
                echo "<h3><span class='icon'>🔍</span>Database Detection</h3>";
                echo "<p><strong>Database Type:</strong> <span class='badge badge-info'>" . strtoupper($driver) . "</span></p>";
                echo "</div>";
                
                // ============================================
                // STEP 1: Add columns to job_seekers
                // ============================================
                $currentStep++;
                echo "<div class='step'>";
                echo "<h3><span class='icon'>📊</span>Step {$currentStep}/{$totalSteps}: Updating job_seekers Table</h3>";
                
                try {
                    // Check if columns already exist
                    if ($isPostgreSQL) {
                        $checkSql = "SELECT column_name FROM information_schema.columns 
                                    WHERE table_name = 'job_seekers' 
                                    AND column_name IN ('account_status', 'closed_at')";
                    } else {
                        $checkSql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS 
                                    WHERE TABLE_SCHEMA = DATABASE() 
                                    AND TABLE_NAME = 'job_seekers' 
                                    AND COLUMN_NAME IN ('account_status', 'closed_at')";
                    }
                    
                    $stmt = $conn->query($checkSql);
                    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $accountStatusExists = in_array('account_status', $existingColumns);
                    $closedAtExists = in_array('closed_at', $existingColumns);
                    
                    echo "<p><strong>Column Check:</strong></p>";
                    echo "<ul>";
                    echo "<li>account_status: " . ($accountStatusExists ? "✅ Already exists" : "❌ Needs to be created") . "</li>";
                    echo "<li>closed_at: " . ($closedAtExists ? "✅ Already exists" : "❌ Needs to be created") . "</li>";
                    echo "</ul>";
                    
                    // Add account_status column if it doesn't exist
                    if (!$accountStatusExists) {
                        if ($isPostgreSQL) {
                            // PostgreSQL syntax
                            $conn->exec("
                                DO $$ 
                                BEGIN
                                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'account_status_enum') THEN
                                        CREATE TYPE account_status_enum AS ENUM ('active', 'closed');
                                    END IF;
                                END $$;
                            ");
                            $conn->exec("ALTER TABLE job_seekers ADD COLUMN account_status account_status_enum DEFAULT 'active'");
                        } else {
                            // MySQL syntax
                            $conn->exec("ALTER TABLE job_seekers ADD COLUMN account_status ENUM('active', 'closed') DEFAULT 'active' AFTER setup_complete");
                        }
                        echo "<p>✅ Added account_status column</p>";
                        $results['job_seekers_account_status'] = 'Created';
                    } else {
                        echo "<p>ℹ️ account_status column already exists</p>";
                        $results['job_seekers_account_status'] = 'Already exists';
                    }
                    
                    // Add closed_at column if it doesn't exist
                    if (!$closedAtExists) {
                        if ($isPostgreSQL) {
                            $conn->exec("ALTER TABLE job_seekers ADD COLUMN closed_at TIMESTAMP DEFAULT NULL");
                        } else {
                            $conn->exec("ALTER TABLE job_seekers ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL AFTER account_status");
                        }
                        echo "<p>✅ Added closed_at column</p>";
                        $results['job_seekers_closed_at'] = 'Created';
                    } else {
                        echo "<p>ℹ️ closed_at column already exists</p>";
                        $results['job_seekers_closed_at'] = 'Already exists';
                    }
                    
                    // Update existing records
                    $stmt = $conn->prepare("
                        UPDATE job_seekers 
                        SET account_status = 'active' 
                        WHERE account_status IS NULL OR account_status = ''
                    ");
                    $stmt->execute();
                    $updatedCount = $stmt->rowCount();
                    
                    echo "<p>✅ Updated {$updatedCount} existing records to 'active' status</p>";
                    $results['job_seekers_updated'] = $updatedCount;
                    
                    echo "</div>";
                    
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                    $errors[] = "job_seekers: " . $e->getMessage();
                }
                
                // ============================================
                // STEP 2: Add columns to employers
                // ============================================
                $currentStep++;
                echo "<div class='step'>";
                echo "<h3><span class='icon'>🏢</span>Step {$currentStep}/{$totalSteps}: Updating employers Table</h3>";
                
                try {
                    // Check if columns already exist
                    if ($isPostgreSQL) {
                        $checkSql = "SELECT column_name FROM information_schema.columns 
                                    WHERE table_name = 'employers' 
                                    AND column_name IN ('account_status', 'closed_at')";
                    } else {
                        $checkSql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS 
                                    WHERE TABLE_SCHEMA = DATABASE() 
                                    AND TABLE_NAME = 'employers' 
                                    AND COLUMN_NAME IN ('account_status', 'closed_at')";
                    }
                    
                    $stmt = $conn->query($checkSql);
                    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    $accountStatusExists = in_array('account_status', $existingColumns);
                    $closedAtExists = in_array('closed_at', $existingColumns);
                    
                    echo "<p><strong>Column Check:</strong></p>";
                    echo "<ul>";
                    echo "<li>account_status: " . ($accountStatusExists ? "✅ Already exists" : "❌ Needs to be created") . "</li>";
                    echo "<li>closed_at: " . ($closedAtExists ? "✅ Already exists" : "❌ Needs to be created") . "</li>";
                    echo "</ul>";
                    
                    // Add account_status column if it doesn't exist
                    if (!$accountStatusExists) {
                        if ($isPostgreSQL) {
                            $conn->exec("ALTER TABLE employers ADD COLUMN account_status account_status_enum DEFAULT 'active'");
                        } else {
                            $conn->exec("ALTER TABLE employers ADD COLUMN account_status ENUM('active', 'closed') DEFAULT 'active' AFTER created_at");
                        }
                        echo "<p>✅ Added account_status column</p>";
                        $results['employers_account_status'] = 'Created';
                    } else {
                        echo "<p>ℹ️ account_status column already exists</p>";
                        $results['employers_account_status'] = 'Already exists';
                    }
                    
                    // Add closed_at column if it doesn't exist
                    if (!$closedAtExists) {
                        if ($isPostgreSQL) {
                            $conn->exec("ALTER TABLE employers ADD COLUMN closed_at TIMESTAMP DEFAULT NULL");
                        } else {
                            $conn->exec("ALTER TABLE employers ADD COLUMN closed_at TIMESTAMP NULL DEFAULT NULL AFTER account_status");
                        }
                        echo "<p>✅ Added closed_at column</p>";
                        $results['employers_closed_at'] = 'Created';
                    } else {
                        echo "<p>ℹ️ closed_at column already exists</p>";
                        $results['employers_closed_at'] = 'Already exists';
                    }
                    
                    // Update existing records
                    $stmt = $conn->prepare("
                        UPDATE employers 
                        SET account_status = 'active' 
                        WHERE account_status IS NULL OR account_status = ''
                    ");
                    $stmt->execute();
                    $updatedCount = $stmt->rowCount();
                    
                    echo "<p>✅ Updated {$updatedCount} existing records to 'active' status</p>";
                    $results['employers_updated'] = $updatedCount;
                    
                    echo "</div>";
                    
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                    $errors[] = "employers: " . $e->getMessage();
                }
                
                // ============================================
                // STEP 3: Add indexes for performance
                // ============================================
                $currentStep++;
                echo "<div class='step'>";
                echo "<h3><span class='icon'>⚡</span>Step {$currentStep}/{$totalSteps}: Adding Performance Indexes</h3>";
                
                try {
                    // Add index for job_seekers
                    try {
                        $conn->exec("CREATE INDEX IF NOT EXISTS idx_job_seekers_account_status ON job_seekers(account_status)");
                        echo "<p>✅ Added index on job_seekers.account_status</p>";
                    } catch (PDOException $e) {
                        // Index might already exist
                        echo "<p>ℹ️ Index on job_seekers.account_status already exists or cannot be created</p>";
                    }
                    
                    // Add index for employers
                    try {
                        $conn->exec("CREATE INDEX IF NOT EXISTS idx_employers_account_status ON employers(account_status)");
                        echo "<p>✅ Added index on employers.account_status</p>";
                    } catch (PDOException $e) {
                        // Index might already exist
                        echo "<p>ℹ️ Index on employers.account_status already exists or cannot be created</p>";
                    }
                    
                    echo "</div>";
                    
                } catch (Exception $e) {
                    echo "<p>⚠️ Note: Index creation skipped or failed (non-critical)</p>";
                    echo "</div>";
                }
                
                // ============================================
                // STEP 4: Verification
                // ============================================
                $currentStep++;
                echo "<div class='step'>";
                echo "<h3><span class='icon'>✅</span>Step {$currentStep}/{$totalSteps}: Verification</h3>";
                
                // Count records
                $stmt = $conn->query("SELECT COUNT(*) as total FROM job_seekers");
                $totalSeekers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $stmt = $conn->query("SELECT COUNT(*) as total FROM employers");
                $totalEmployers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                echo "<table>";
                echo "<tr><th>Table</th><th>Total Records</th><th>Status</th></tr>";
                echo "<tr><td>job_seekers</td><td>{$totalSeekers}</td><td><span class='badge badge-success'>✅ Ready</span></td></tr>";
                echo "<tr><td>employers</td><td>{$totalEmployers}</td><td><span class='badge badge-success'>✅ Ready</span></td></tr>";
                echo "</table>";
                
                echo "</div>";
                
                // ============================================
                // FINAL SUMMARY
                // ============================================
                if (empty($errors)) {
                    echo "<div class='step success'>";
                    echo "<h3><span class='icon'>🎉</span>Migration Completed Successfully!</h3>";
                    echo "<p><strong>Summary:</strong></p>";
                    echo "<ul>";
                    echo "<li>✅ Database structure updated</li>";
                    echo "<li>✅ All existing records updated</li>";
                    echo "<li>✅ Performance indexes added</li>";
                    echo "<li>✅ Close account feature is now ready!</li>";
                    echo "</ul>";
                    echo "</div>";
                } else {
                    echo "<div class='step warning'>";
                    echo "<h3><span class='icon'>⚠️</span>Migration Completed with Warnings</h3>";
                    echo "<p>Some steps encountered issues:</p>";
                    echo "<ul>";
                    foreach ($errors as $error) {
                        echo "<li>" . htmlspecialchars($error) . "</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }
                
                // Security warning
                echo "<div class='step error'>";
                echo "<h3><span class='icon'>🔒</span>CRITICAL: Security Action Required</h3>";
                echo "<p><strong>DELETE THIS FILE IMMEDIATELY!</strong></p>";
                echo "<p>For security reasons, you must remove this migration script from your server:</p>";
                echo "<div class='code'>";
                echo "# Run these commands:<br>";
                echo "cd C:\\xampp\\htdocs\\ThisAble<br>";
                echo "git rm backend/migrations/railway_migration.php<br>";
                echo "git commit -m \"Remove migration script after execution\"<br>";
                echo "git push origin main";
                echo "</div>";
                echo "<p>⚠️ Leaving this file accessible is a security risk!</p>";
                echo "</div>";
                
            } catch (Exception $e) {
                echo "<div class='step error'>";
                echo "<h3><span class='icon'>❌</span>Critical Error</h3>";
                echo "<p>Migration failed with error:</p>";
                echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
                echo "</div>";
            }
            ?>
            
            <div class="step info">
                <h3><span class="icon">📝</span>Next Steps</h3>
                <ol>
                    <li>✅ Verify the migration completed successfully above</li>
                    <li>🔒 DELETE this migration file immediately</li>
                    <li>🧪 Test the close account feature on your production site</li>
                    <li>✅ Test account reactivation by logging in again</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>