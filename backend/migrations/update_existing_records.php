<?php
/**
 * TEMPORARY MIGRATION SCRIPT
 * Purpose: Update existing records to set default account_status = 'active'
 * Run this ONCE on Railway after adding columns
 * Then DELETE this file for security
 */

require_once '../db.php';

// Prevent running multiple times
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            font-size: 18px;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-size: 18px;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #2196F3;
        }
        .warning {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Database Migration</h1>
        <p>Updating existing records with default account_status values...</p>
        
        <?php
        try {
            echo "<div class='info'>📊 Starting migration process...</div>";
            
            // Update job_seekers
            echo "<h3>1. Updating job_seekers table</h3>";
            $stmt = $conn->prepare("
                UPDATE job_seekers 
                SET account_status = 'active' 
                WHERE account_status IS NULL OR account_status = ''
            ");
            $stmt->execute();
            $count1 = $stmt->rowCount();
            
            // Get total job_seekers count
            $stmt = $conn->query("SELECT COUNT(*) as total FROM job_seekers");
            $total1 = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo "<table>";
            echo "<tr><th>Metric</th><th>Value</th></tr>";
            echo "<tr><td>Total job seekers</td><td>{$total1}</td></tr>";
            echo "<tr><td>Records updated</td><td>{$count1}</td></tr>";
            echo "<tr><td>Status</td><td class='success'>✅ Success</td></tr>";
            echo "</table>";
            
            // Update employers
            echo "<h3>2. Updating employers table</h3>";
            $stmt = $conn->prepare("
                UPDATE employers 
                SET account_status = 'active' 
                WHERE account_status IS NULL OR account_status = ''
            ");
            $stmt->execute();
            $count2 = $stmt->rowCount();
            
            // Get total employers count
            $stmt = $conn->query("SELECT COUNT(*) as total FROM employers");
            $total2 = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo "<table>";
            echo "<tr><th>Metric</th><th>Value</th></tr>";
            echo "<tr><td>Total employers</td><td>{$total2}</td></tr>";
            echo "<tr><td>Records updated</td><td>{$count2}</td></tr>";
            echo "<tr><td>Status</td><td class='success'>✅ Success</td></tr>";
            echo "</table>";
            
            // Summary
            echo "<div class='success'>";
            echo "<h2>✅ Migration Completed Successfully!</h2>";
            echo "<p>Updated {$count1} job seekers and {$count2} employers</p>";
            echo "</div>";
            
            echo "<div class='warning'>";
            echo "<h3>⚠️ IMPORTANT: Security Notice</h3>";
            echo "<p><strong>DELETE THIS FILE IMMEDIATELY!</strong></p>";
            echo "<p>For security reasons, please delete this migration script:</p>";
            echo "<p><code>/backend/migrations/update_existing_records.php</code></p>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "<h2>❌ Migration Failed</h2>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<h3>Troubleshooting:</h3>";
            echo "<ul>";
            echo "<li>Check if columns 'account_status' and 'closed_at' exist in both tables</li>";
            echo "<li>Verify database connection is working</li>";
            echo "<li>Check Railway database logs for more details</li>";
            echo "</ul>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h2>❌ Unexpected Error</h2>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>