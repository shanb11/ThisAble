<?php
/**
 * MySQL Database Import Tool for Railway
 * Upload to: backend/import_mysql.php
 * Access: https://thisable-production.up.railway.app/backend/import_mysql.php?key=import-2025
 * 
 * Instructions:
 * 1. Upload this file to Railway
 * 2. Upload your jobportal_db.sql file to Railway (same directory)
 * 3. Access this page with the secret key
 * 4. Click "Import Database"
 */

$secret_key = "import-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=import-2025");
}

// Start session for progress tracking
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>MySQL Database Import Tool</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
            font-size: 32px;
        }
        .step {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .step h3 {
            margin-top: 0;
            color: #667eea;
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
            display: inline-block;
            text-decoration: none;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
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
        }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        .file-upload {
            border: 3px dashed #667eea;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            margin: 20px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-upload:hover {
            background: #f8f9fa;
            border-color: #5568d3;
        }
        .file-upload input[type="file"] {
            display: none;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ MySQL Database Import Tool</h1>
        
        <?php
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if ($action === 'import') {
                importDatabase();
            } elseif ($action === 'upload') {
                handleFileUpload();
            }
        } else {
            showImportForm();
        }
        
        function showImportForm() {
            ?>
            <div class="step">
                <h3>📋 Step 1: Check MySQL Connection</h3>
                <?php
                $connection = testConnection();
                if ($connection['success']) {
                    echo '<div class="success">✅ ' . $connection['message'] . '</div>';
                    echo '<div class="stats">';
                    foreach ($connection['stats'] as $key => $value) {
                        echo '<div class="stat-box">';
                        echo '<div class="stat-number">' . htmlspecialchars($value) . '</div>';
                        echo '<div class="stat-label">' . htmlspecialchars($key) . '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="error">❌ ' . $connection['message'] . '</div>';
                    return;
                }
                ?>
            </div>
            
            <div class="step">
                <h3>📤 Step 2: Upload SQL File</h3>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="upload">
                    <label for="sqlFile" class="file-upload">
                        <div style="font-size: 48px; margin-bottom: 10px;">📁</div>
                        <div style="font-size: 18px; margin-bottom: 10px;">Click to upload <code>jobportal_db.sql</code></div>
                        <div style="color: #6c757d;">or drag and drop here</div>
                        <input type="file" id="sqlFile" name="sqlFile" accept=".sql" required onchange="this.form.submit()">
                    </label>
                </form>
                
                <?php if (isset($_SESSION['uploaded_file'])): ?>
                    <div class="success">
                        ✅ File uploaded: <strong><?php echo htmlspecialchars($_SESSION['uploaded_file']); ?></strong>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['uploaded_file'])): ?>
            <div class="step">
                <h3>🚀 Step 3: Import Database</h3>
                <div class="warning">
                    ⚠️ <strong>Warning:</strong> This will DROP all existing tables and import the SQL file.
                </div>
                <form method="POST" onsubmit="return confirm('Are you sure you want to import? This will replace all existing data!');">
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="btn">🚀 Start Import</button>
                </form>
            </div>
            <?php endif; ?>
            
            <?php
        }
        
        function testConnection() {
            try {
                // Get MySQL connection details from Railway environment variables
                $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
                $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
                $user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
                $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
                $dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
                
                // Try to parse MYSQL_URL if available
                $mysql_url = getenv('MYSQL_URL');
                if ($mysql_url) {
                    $parsed = parse_url($mysql_url);
                    $host = $parsed['host'] ?? $host;
                    $port = $parsed['port'] ?? $port;
                    $user = $parsed['user'] ?? $user;
                    $password = $parsed['pass'] ?? $password;
                    $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $dbname;
                }
                
                $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                $conn = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10
                ]);
                
                // Create database if it doesn't exist
                $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
                $conn->exec("USE `$dbname`");
                
                // Get database stats
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                return [
                    'success' => true,
                    'message' => 'Connected to MySQL on Railway!',
                    'conn' => $conn,
                    'dbname' => $dbname,
                    'stats' => [
                        'Host' => $host,
                        'Port' => $port,
                        'Database' => $dbname,
                        'Existing Tables' => count($tables)
                    ]
                ];
            } catch (PDOException $e) {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $e->getMessage()
                ];
            }
        }
        
        function handleFileUpload() {
            if (!isset($_FILES['sqlFile']) || $_FILES['sqlFile']['error'] !== UPLOAD_ERR_OK) {
                echo '<div class="error">❌ File upload failed!</div>';
                showImportForm();
                return;
            }
            
            $file = $_FILES['sqlFile'];
            $uploadDir = sys_get_temp_dir() . '/';
            $uploadFile = $uploadDir . 'jobportal_db.sql';
            
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $_SESSION['uploaded_file'] = $file['name'];
                $_SESSION['sql_file_path'] = $uploadFile;
                echo '<div class="success">✅ File uploaded successfully!</div>';
            } else {
                echo '<div class="error">❌ Failed to save uploaded file!</div>';
            }
            
            showImportForm();
        }
        
        function importDatabase() {
            echo '<div class="step"><h3>🚀 Importing Database...</h3>';
            
            $sqlFile = $_SESSION['sql_file_path'] ?? null;
            if (!$sqlFile || !file_exists($sqlFile)) {
                echo '<div class="error">❌ SQL file not found! Please upload it first.</div>';
                echo '</div>';
                showImportForm();
                return;
            }
            
            $connection = testConnection();
            if (!$connection['success']) {
                echo '<div class="error">❌ Database connection failed!</div>';
                echo '</div>';
                showImportForm();
                return;
            }
            
            $conn = $connection['conn'];
            
            try {
                echo '<div class="info">📖 Reading SQL file...</div>';
                
                $sql = file_get_contents($sqlFile);
                if ($sql === false) {
                    throw new Exception('Failed to read SQL file');
                }
                
                echo '<div class="success">✅ File read successfully (' . number_format(strlen($sql)) . ' bytes)</div>';
                
                // Disable foreign key checks
                $conn->exec('SET FOREIGN_KEY_CHECKS=0');
                
                // Split SQL into statements
                echo '<div class="info">⚙️ Parsing SQL statements...</div>';
                $statements = array_filter(
                    array_map('trim', preg_split('/;[\r\n]+/', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^(--|\/\*)/', $stmt);
                    }
                );
                
                $total = count($statements);
                echo '<div class="success">✅ Found ' . $total . ' SQL statements</div>';
                
                echo '<div class="progress-bar"><div class="progress-fill" id="progressBar" style="width: 0%">0%</div></div>';
                echo '<div id="currentAction" class="info">Starting import...</div>';
                
                // Execute statements
                $executed = 0;
                $errors = 0;
                
                foreach ($statements as $index => $statement) {
                    try {
                        $conn->exec($statement);
                        $executed++;
                        
                        // Update progress every 50 statements
                        if ($index % 50 === 0 || $index === $total - 1) {
                            $progress = round(($index + 1) / $total * 100);
                            echo '<script>
                                document.getElementById("progressBar").style.width = "' . $progress . '%";
                                document.getElementById("progressBar").textContent = "' . $progress . '%";
                                document.getElementById("currentAction").textContent = "Executing statement ' . ($index + 1) . ' of ' . $total . '...";
                            </script>';
                            flush();
                            ob_flush();
                        }
                    } catch (PDOException $e) {
                        $errors++;
                        // Only show first 5 errors
                        if ($errors <= 5) {
                            echo '<div class="warning">⚠️ Error in statement ' . ($index + 1) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                    }
                }
                
                // Re-enable foreign key checks
                $conn->exec('SET FOREIGN_KEY_CHECKS=1');
                
                echo '<div class="success">🎉 <strong>Import Complete!</strong></div>';
                echo '<div class="stats">';
                echo '<div class="stat-box"><div class="stat-number">' . $total . '</div><div class="stat-label">Total Statements</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $executed . '</div><div class="stat-label">Executed</div></div>';
                echo '<div class="stat-box"><div class="stat-number">' . $errors . '</div><div class="stat-label">Errors</div></div>';
                echo '</div>';
                
                // Show final table count
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo '<div class="success">✅ Database now contains <strong>' . count($tables) . '</strong> tables</div>';
                
                // Clean up
                unset($_SESSION['uploaded_file']);
                unset($_SESSION['sql_file_path']);
                @unlink($sqlFile);
                
                echo '<div class="info">✨ You can now test your Google Sign-In!</div>';
                echo '<a href="../frontend/candidate/login.php" class="btn" style="margin-right: 10px;">Test Candidate Login</a>';
                echo '<a href="../frontend/employer/emplogin.php" class="btn">Test Employer Login</a>';
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Import failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre style="background: #f4f4f4; padding: 15px; border-radius: 8px; overflow-x: auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            
            echo '</div>';
        }
        ?>
        
        <div class="step">
            <h3>⚠️ Security</h3>
            <p class="warning">
                <strong>Important:</strong> Delete this file after import: <code>backend/import_mysql.php</code>
            </p>
        </div>
    </div>
</body>
</html>