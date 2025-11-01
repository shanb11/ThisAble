<?php
/**
 * FIXED Railway MySQL Import Tool
 * Properly handles Railway's MYSQL_URL environment variable
 * 
 * Upload to: backend/import-sql-fixed.php
 * Access: https://thisable-production.up.railway.app/backend/import-sql-fixed.php?key=import-2025
 */

$secret_key = "import-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=import-2025");
}

// Get Railway MySQL credentials from MYSQL_URL
$mysql_url = getenv('MYSQL_URL');
$connectionInfo = [];

if ($mysql_url) {
    $parsed = parse_url($mysql_url);
    $host = $parsed['host'] ?? 'localhost';
    $port = $parsed['port'] ?? '3306';
    $user = $parsed['user'] ?? 'root';
    $password = $parsed['pass'] ?? '';
    $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway';
    
    $connectionInfo['source'] = 'MYSQL_URL';
} else {
    // Fallback
    $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
    $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
    $dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
    $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
    
    $connectionInfo['source'] = 'Individual variables';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Railway MySQL Import Tool (Fixed)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #1a1a2e;
            color: #eee;
        }
        .container {
            background: #16213e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        h1 {
            color: #e94560;
            margin-top: 0;
        }
        .info-box {
            background: #0f3460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 8px 0;
            font-family: 'Courier New', monospace;
        }
        .success {
            background: #27ae60;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .error {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning {
            background: #f39c12;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        input[type="file"] {
            display: block;
            margin: 20px 0;
            padding: 15px;
            background: #0f3460;
            border: 3px dashed #e94560;
            border-radius: 8px;
            width: 100%;
            color: #eee;
            cursor: pointer;
        }
        button {
            background: #e94560;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        button:hover {
            background: #c23649;
        }
        button:disabled {
            background: #555;
            cursor: not-allowed;
        }
        #progress {
            display: none;
            margin: 20px 0;
        }
        .progress-bar {
            width: 100%;
            height: 40px;
            background: #0f3460;
            border-radius: 8px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #e94560, #ff6b9d);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .file-name {
            color: #4CAF50;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Railway MySQL Import Tool</h1>
        <p style="color: #aaa;">Fixed version - Handles MYSQL_URL properly</p>
        
        <div class="info-box">
            <p><strong>📡 Connection Info:</strong></p>
            <p>Source: <?php echo htmlspecialchars($connectionInfo['source'] ?? 'unknown'); ?></p>
            <p>Host: <?php echo htmlspecialchars($host); ?></p>
            <p>Port: <?php echo htmlspecialchars($port); ?></p>
            <p>Database: <?php echo htmlspecialchars($dbname); ?></p>
            <p>User: <?php echo htmlspecialchars($user); ?></p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
            echo '<div id="progress">';
            echo '<div class="progress-bar"><div class="progress-fill" id="progressFill">0%</div></div>';
            echo '</div>';
            
            echo '<script>document.getElementById("progress").style.display = "block";</script>';
            flush();
            
            try {
                // Connect to database
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                $conn = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30
                ]);
                
                echo '<script>
                    document.getElementById("progressFill").style.width = "10%";
                    document.getElementById("progressFill").textContent = "10% - Connected to Railway MySQL";
                </script>';
                flush();
                
                // Read SQL file
                $sql_file = $_FILES['sql_file']['tmp_name'];
                $file_size = filesize($sql_file);
                $sql_content = file_get_contents($sql_file);
                
                if (empty($sql_content)) {
                    throw new Exception('SQL file is empty');
                }
                
                echo '<script>
                    document.getElementById("progressFill").style.width = "20%";
                    document.getElementById("progressFill").textContent = "20% - File loaded (' . number_format($file_size / 1024, 2) . ' KB)";
                </script>';
                flush();
                
                // Clean and split SQL
                $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content); // Remove /* */ comments
                $statements = array_filter(
                    array_map('trim', explode(';', $sql_content)),
                    function($stmt) {
                        $stmt = trim($stmt);
                        return !empty($stmt) && 
                               !preg_match('/^--/', $stmt) && 
                               !preg_match('/^#/', $stmt);
                    }
                );
                
                $total = count($statements);
                $success = 0;
                $errors = [];
                
                echo '<script>
                    document.getElementById("progressFill").style.width = "30%";
                    document.getElementById("progressFill").textContent = "30% - Found ' . $total . ' SQL statements";
                </script>';
                flush();
                
                // Execute statements with progress updates
                foreach ($statements as $index => $statement) {
                    try {
                        $conn->exec($statement . ';');
                        $success++;
                        
                        // Update progress every 10 statements or on last one
                        if ($index % 10 === 0 || $index === $total - 1) {
                            $percent = 30 + (($index + 1) / $total * 60);
                            echo '<script>
                                document.getElementById("progressFill").style.width = "' . $percent . '%";
                                document.getElementById("progressFill").textContent = "' . round($percent) . '% - Executing... (' . ($index + 1) . '/' . $total . ')";
                            </script>';
                            flush();
                        }
                        
                    } catch (PDOException $e) {
                        $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
                    }
                }
                
                echo '<script>
                    document.getElementById("progressFill").style.width = "100%";
                    document.getElementById("progressFill").textContent = "100% - Import Complete!";
                </script>';
                flush();
                
                // Show results
                echo '<div class="success">';
                echo '<h3>✅ Import Successful!</h3>';
                echo '<p><strong>Executed:</strong> ' . $success . ' out of ' . $total . ' statements</p>';
                echo '</div>';
                
                if (!empty($errors)) {
                    echo '<div class="warning">';
                    echo '<h3>⚠️ Some Statements Skipped</h3>';
                    echo '<p>' . count($errors) . ' statements failed (this is often normal for duplicate data)</p>';
                    echo '<details><summary>View first 10 errors</summary><pre style="background:#000;padding:10px;border-radius:5px;overflow-x:auto;">';
                    foreach (array_slice($errors, 0, 10) as $error) {
                        echo htmlspecialchars($error) . "\n";
                    }
                    echo '</pre></details>';
                    echo '</div>';
                }
                
                // Check what tables exist now
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($tables)) {
                    echo '<div class="info-box">';
                    echo '<h3>📋 Database Tables (' . count($tables) . ' tables found)</h3>';
                    echo '<p style="color:#4CAF50;">';
                    echo implode(', ', $tables);
                    echo '</p></div>';
                }
                
                echo '<div class="warning">';
                echo '<h3>🎉 Next Steps:</h3>';
                echo '<ol>';
                echo '<li><strong>Delete this file:</strong> backend/import-sql-fixed.php (security)</li>';
                echo '<li><strong>Update db.php:</strong> Use backend/db-fixed.php</li>';
                echo '<li><strong>Test your website!</strong></li>';
                echo '</ol>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<h3>❌ Import Failed</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        }
        ?>

        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <h3>📁 Select Your SQL File (jobportal_db.sql)</h3>
            <input type="file" name="sql_file" id="sql_file" accept=".sql" required onchange="showFileName(this)">
            <p class="file-name" id="fileName" style="display:none;"></p>
            <button type="submit" id="submitBtn">
                🚀 Import Database to Railway
            </button>
        </form>

        <div class="info-box" style="margin-top: 30px;">
            <h3>ℹ️ Instructions:</h3>
            <ol>
                <li>Click "Choose File" and select your <code>jobportal_db.sql</code></li>
                <li>Click "Import Database to Railway"</li>
                <li>Wait for the import to complete (may take 1-2 minutes)</li>
                <li>Delete this file after successful import</li>
            </ol>
        </div>
    </div>

    <script>
        function showFileName(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileName.textContent = '📄 Selected: ' + input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(2) + ' KB)';
                fileName.style.display = 'block';
            }
        }
        
        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '⏳ Importing... Please wait...';
        });
    </script>
</body>
</html>