<?php
/**
 * IMPROVED MySQL Database Import Tool for Railway
 * Better error handling and table creation order
 * Upload to: backend/import_mysql_v2.php
 * Access: https://thisable-production.up.railway.app/backend/import_mysql_v2.php?key=import-2025
 */

$secret_key = "import-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=import-2025");
}

session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set time limit for long imports
set_time_limit(600); // 10 minutes
?>
<!DOCTYPE html>
<html>
<head>
    <title>MySQL Database Import Tool v2</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            margin: 15px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin: 15px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
            margin: 15px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #bee5eb;
            margin: 15px 0;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .log-entry {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 5px 10px;
            margin: 2px 0;
            border-left: 3px solid #667eea;
            background: #f8f9fa;
        }
        .log-error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .log-success {
            border-left-color: #28a745;
            background: #f0fff4;
        }
        .progress-bar {
            width: 100%;
            height: 40px;
            background: #e9ecef;
            border-radius: 20px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            margin-top: 5px;
            font-size: 14px;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            max-height: 400px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ MySQL Database Import Tool v2</h1>
        <p style="color: #6c757d;">Improved with better error handling and progress tracking</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'import') {
                importDatabaseV2();
            } elseif ($_POST['action'] === 'clear_all') {
                clearAllTables();
            }
        } else {
            showImportInterface();
        }

        function showImportInterface() {
            ?>
            <div class="success">
                <h3 style="margin-top: 0;">✅ Step 1: Database Connection Check</h3>
                <?php
                $conn = getConnection();
                if ($conn) {
                    echo '<p>✅ Connected to Railway MySQL successfully!</p>';
                    
                    // Show existing tables
                    $stmt = $conn->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    echo '<div class="stats-grid">';
                    echo '<div class="stat-box">';
                    echo '<div class="stat-number">' . count($tables) . '</div>';
                    echo '<div class="stat-label">Existing Tables</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    if (count($tables) > 0) {
                        echo '<div class="warning">';
                        echo '<p><strong>⚠️ Warning:</strong> Database already contains ' . count($tables) . ' tables.</p>';
                        echo '<p>You can either:</p>';
                        echo '<ul>';
                        echo '<li>Import anyway (may cause errors if tables exist)</li>';
                        echo '<li>Clear all tables first (recommended for fresh import)</li>';
                        echo '</ul>';
                        echo '<form method="POST" onsubmit="return confirm(\'Are you sure you want to DELETE ALL TABLES? This cannot be undone!\');">';
                        echo '<input type="hidden" name="action" value="clear_all">';
                        echo '<button type="submit" class="btn" style="background: #dc3545;">🗑️ Clear All Tables First</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="error">❌ Cannot connect to database</p>';
                    return;
                }
                ?>
            </div>

            <div class="info">
                <h3>📤 Step 2: Upload and Import SQL File</h3>
                <p>The SQL file should be located at: <code>backend/jobportal_db.sql</code></p>
                <form method="POST" onsubmit="return confirm('Ready to import? This may take a few minutes.');">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn">🚀 Start Import from jobportal_db.sql</button>
                </form>
            </div>
            <?php
        }

        function getConnection() {
            try {
                $mysql_url = getenv('MYSQL_URL');
                
                if ($mysql_url) {
                    $parsed = parse_url($mysql_url);
                    $host = $parsed['host'] ?? 'localhost';
                    $port = $parsed['port'] ?? '3306';
                    $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway';
                    $username = $parsed['user'] ?? 'root';
                    $password = $parsed['pass'] ?? '';
                } else {
                    $host = getenv('MYSQLHOST') ?: 'localhost';
                    $port = getenv('MYSQLPORT') ?: '3306';
                    $dbname = getenv('MYSQLDATABASE') ?: 'railway';
                    $username = getenv('MYSQLUSER') ?: 'root';
                    $password = getenv('MYSQLPASSWORD') ?: '';
                }

                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                $conn = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 30
                ]);

                return $conn;
            } catch (PDOException $e) {
                echo '<div class="error">Connection failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                return null;
            }
        }

        function clearAllTables() {
            echo '<h2>🗑️ Clearing All Tables...</h2>';
            
            $conn = getConnection();
            if (!$conn) return;

            try {
                $conn->exec('SET FOREIGN_KEY_CHECKS=0');
                
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($tables as $table) {
                    $conn->exec("DROP TABLE IF EXISTS `$table`");
                    echo '<div class="log-entry">🗑️ Dropped table: ' . htmlspecialchars($table) . '</div>';
                    flush();
                    ob_flush();
                }
                
                $conn->exec('SET FOREIGN_KEY_CHECKS=1');
                
                echo '<div class="success">✅ All tables cleared! Ready for fresh import.</div>';
                echo '<a href="?" class="btn">Back to Import</a>';
                
            } catch (PDOException $e) {
                echo '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }

        function importDatabaseV2() {
            echo '<h2>🚀 Starting Database Import...</h2>';
            
            // Find the SQL file
            $sqlFile = __DIR__ . '/jobportal_db.sql';
            
            if (!file_exists($sqlFile)) {
                echo '<div class="error">❌ SQL file not found at: ' . htmlspecialchars($sqlFile) . '</div>';
                echo '<p>Please upload <code>jobportal_db.sql</code> to the <code>backend</code> folder.</p>';
                return;
            }

            $conn = getConnection();
            if (!$conn) return;

            try {
                echo '<div class="log-entry log-success">📖 Reading SQL file...</div>';
                flush();
                ob_flush();

                $sql = file_get_contents($sqlFile);
                $fileSize = strlen($sql);
                
                echo '<div class="log-entry">📊 File size: ' . number_format($fileSize) . ' bytes</div>';
                flush();
                ob_flush();

                // Disable foreign key checks
                $conn->exec('SET FOREIGN_KEY_CHECKS=0');
                $conn->exec('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
                echo '<div class="log-entry">⚙️ Foreign key checks disabled</div>';
                flush();
                ob_flush();

                // Split into statements more carefully
                echo '<div class="log-entry">⚙️ Parsing SQL statements...</div>';
                flush();
                ob_flush();

                // Better SQL splitting that handles multi-line statements
                $statements = [];
                $current = '';
                $lines = explode("\n", $sql);
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    // Skip comments and empty lines
                    if (empty($line) || substr($line, 0, 2) === '--' || substr($line, 0, 2) === '/*') {
                        continue;
                    }
                    
                    $current .= ' ' . $line;
                    
                    // Check if statement is complete (ends with semicolon)
                    if (substr(rtrim($line), -1) === ';') {
                        $statements[] = trim($current);
                        $current = '';
                    }
                }

                $total = count($statements);
                echo '<div class="log-entry log-success">✅ Found ' . $total . ' SQL statements</div>';
                flush();
                ob_flush();

                // Progress tracking
                $executed = 0;
                $errors = 0;
                $creates = 0;
                $inserts = 0;
                $errorMessages = [];

                echo '<div class="progress-bar"><div class="progress-fill" id="progressBar" style="width: 0%">0%</div></div>';
                echo '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 20px 0;" id="logContainer">';

                foreach ($statements as $index => $statement) {
                    try {
                        $conn->exec($statement);
                        $executed++;
                        
                        // Track what type of statement
                        if (stripos($statement, 'CREATE TABLE') !== false) {
                            $creates++;
                            preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches);
                            $tableName = $matches[1] ?? 'unknown';
                            echo '<div class="log-entry log-success">✅ Created table: ' . htmlspecialchars($tableName) . '</div>';
                        } elseif (stripos($statement, 'INSERT INTO') !== false) {
                            $inserts++;
                        }
                        
                        // Update progress every 25 statements
                        if ($index % 25 === 0 || $index === $total - 1) {
                            $progress = round(($index + 1) / $total * 100);
                            echo '<script>
                                document.getElementById("progressBar").style.width = "' . $progress . '%";
                                document.getElementById("progressBar").textContent = "' . $progress . '%";
                                var container = document.getElementById("logContainer");
                                container.scrollTop = container.scrollHeight;
                            </script>';
                            flush();
                            ob_flush();
                        }
                        
                    } catch (PDOException $e) {
                        $errors++;
                        $errorMsg = $e->getMessage();
                        
                        // Only show first 10 errors
                        if ($errors <= 10) {
                            $shortStmt = substr($statement, 0, 100);
                            echo '<div class="log-entry log-error">❌ Error in statement ' . ($index + 1) . ': ' . htmlspecialchars($errorMsg) . '</div>';
                            echo '<div class="log-entry" style="font-size: 11px;">Statement: ' . htmlspecialchars($shortStmt) . '...</div>';
                            flush();
                            ob_flush();
                        }
                        
                        $errorMessages[] = [
                            'index' => $index + 1,
                            'error' => $errorMsg,
                            'statement' => substr($statement, 0, 200)
                        ];
                    }
                }

                echo '</div>'; // Close log container

                // Re-enable foreign key checks
                $conn->exec('SET FOREIGN_KEY_CHECKS=1');

                // Final statistics
                echo '<div class="success"><h3>🎉 Import Complete!</h3></div>';
                
                echo '<div class="stats-grid">';
                echo '<div class="stat-box"><div class="stat-number">' . $total . '</div><div class="stat-label">Total Statements</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $executed . '</div><div class="stat-label">Executed</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $creates . '</div><div class="stat-label">Tables Created</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $inserts . '</div><div class="stat-label">Data Inserts</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $errors . '</div><div class="stat-label">Errors</div></div>';
                echo '</div>';

                // Show final table count
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<div class="success">✅ Database now contains <strong>' . count($tables) . '</strong> tables</div>';

                if ($errors > 0) {
                    echo '<div class="warning">';
                    echo '<h4>⚠️ Import completed with ' . $errors . ' errors</h4>';
                    echo '<p>Some statements failed. This is usually OK for:</p>';
                    echo '<ul>';
                    echo '<li>Duplicate key errors (data already exists)</li>';
                    echo '<li>Foreign key constraint errors (can be ignored if tables were created)</li>';
                    echo '</ul>';
                    
                    if (count($errorMessages) > 0 && count($errorMessages) <= 20) {
                        echo '<details><summary>View Error Details</summary><pre>';
                        foreach ($errorMessages as $err) {
                            echo 'Statement #' . $err['index'] . ":\n";
                            echo '  Error: ' . htmlspecialchars($err['error']) . "\n";
                            echo '  SQL: ' . htmlspecialchars($err['statement']) . "...\n\n";
                        }
                        echo '</pre></details>';
                    }
                    echo '</div>';
                }

                // Test a critical table
                try {
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
                    $count = $stmt->fetch()['count'];
                    echo '<div class="success">✅ Verification: Found ' . $count . ' job seekers in database</div>';
                } catch (PDOException $e) {
                    echo '<div class="warning">⚠️ Could not verify job_seekers table: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }

                echo '<div class="info">';
                echo '<h3>🎯 Next Steps:</h3>';
                echo '<p>Your database is ready! You can now:</p>';
                echo '<ul>';
                echo '<li><a href="../frontend/candidate/login.php">Test Candidate Login</a></li>';
                echo '<li><a href="../frontend/employer/emplogin.php">Test Employer Login</a></li>';
                echo '</ul>';
                echo '</div>';

            } catch (Exception $e) {
                echo '<div class="error">❌ Fatal error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
        }
        ?>

        <div class="warning" style="margin-top: 40px;">
            <h3>⚠️ Security</h3>
            <p><strong>Important:</strong> Delete these files after import:</p>
            <ul>
                <li><code>backend/import_mysql_v2.php</code></li>
                <li><code>backend/jobportal_db.sql</code></li>
                <li><code>backend/check-env.php</code></li>
                <li><code>backend/test_supabase_connection.php</code></li>
            </ul>
        </div>
    </div>
</body>
</html>