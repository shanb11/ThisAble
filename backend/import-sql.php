<?php
/**
 * Railway MySQL Import Tool
 * Upload this file to: backend/import-sql.php
 * Access: https://thisable-production.up.railway.app/backend/import-sql.php
 * 
 * SECURITY: Delete this file after importing!
 */

// Only allow in development or with secret key
$secret_key = "import-database-2025"; // Change this!
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=import-database-2025");
}

// Get Railway MySQL credentials
$host = getenv('MYSQLHOST') ?: 'localhost';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Railway MySQL Import Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #1a1a2e;
            color: #eee;
        }
        .container {
            background: #16213e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h1 {
            color: #0f3460;
            margin-top: 0;
        }
        .info-box {
            background: #0f3460;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box p {
            margin: 5px 0;
        }
        input[type="file"] {
            display: block;
            margin: 20px 0;
            padding: 10px;
            background: #0f3460;
            border: 2px dashed #e94560;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        button {
            background: #e94560;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #c23649;
        }
        button:disabled {
            background: #666;
            cursor: not-allowed;
        }
        .success {
            background: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .error {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning {
            background: #f39c12;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        #progress {
            display: none;
            margin: 20px 0;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #0f3460;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #e94560;
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Railway MySQL Import Tool</h1>
        
        <div class="info-box">
            <p><strong>Connection Info:</strong></p>
            <p>Host: <?php echo htmlspecialchars($host); ?></p>
            <p>Port: <?php echo htmlspecialchars($port); ?></p>
            <p>Database: <?php echo htmlspecialchars($dbname); ?></p>
            <p>User: <?php echo htmlspecialchars($username); ?></p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
            echo '<div id="progress">';
            echo '<div class="progress-bar"><div class="progress-fill" id="progressFill">0%</div></div>';
            echo '</div>';
            
            echo '<script>document.getElementById("progress").style.display = "block";</script>';
            
            try {
                // Connect to database
                $conn = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                
                echo '<script>document.getElementById("progressFill").style.width = "20%"; document.getElementById("progressFill").textContent = "20% - Connected";</script>';
                flush();
                
                // Read SQL file
                $sql_file = $_FILES['sql_file']['tmp_name'];
                $sql_content = file_get_contents($sql_file);
                
                if (empty($sql_content)) {
                    throw new Exception('SQL file is empty');
                }
                
                echo '<script>document.getElementById("progressFill").style.width = "40%"; document.getElementById("progressFill").textContent = "40% - File read";</script>';
                flush();
                
                // Split into statements (basic split by semicolon)
                $statements = array_filter(
                    array_map('trim', explode(';', $sql_content)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^--/', $stmt);
                    }
                );
                
                $total = count($statements);
                $success = 0;
                $errors = [];
                
                echo '<script>document.getElementById("progressFill").style.width = "50%"; document.getElementById("progressFill").textContent = "50% - Processing ' . $total . ' statements";</script>';
                flush();
                
                // Execute statements
                foreach ($statements as $index => $statement) {
                    try {
                        $conn->exec($statement);
                        $success++;
                        
                        $percent = 50 + (($index + 1) / $total * 40);
                        echo '<script>document.getElementById("progressFill").style.width = "' . $percent . '%"; document.getElementById("progressFill").textContent = "' . round($percent) . '% - ' . ($index + 1) . '/' . $total . '";</script>';
                        flush();
                        
                    } catch (PDOException $e) {
                        $errors[] = "Statement " . ($index + 1) . ": " . $e->getMessage();
                    }
                }
                
                echo '<script>document.getElementById("progressFill").style.width = "100%"; document.getElementById("progressFill").textContent = "100% - Complete!";</script>';
                flush();
                
                if ($success > 0) {
                    echo '<div class="success">';
                    echo '<h3>✅ Import Successful!</h3>';
                    echo '<p>Successfully executed ' . $success . ' out of ' . $total . ' statements.</p>';
                    echo '</div>';
                }
                
                if (!empty($errors)) {
                    echo '<div class="warning">';
                    echo '<h3>⚠️ Some Statements Failed</h3>';
                    echo '<p>Total errors: ' . count($errors) . '</p>';
                    echo '<details><summary>View Errors</summary><pre>';
                    foreach (array_slice($errors, 0, 10) as $error) {
                        echo htmlspecialchars($error) . "\n";
                    }
                    if (count($errors) > 10) {
                        echo '... and ' . (count($errors) - 10) . ' more';
                    }
                    echo '</pre></details>';
                    echo '</div>';
                }
                
                // Check tables
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<div class="info-box">';
                echo '<h3>📋 Database Tables (' . count($tables) . ')</h3>';
                echo '<p>' . implode(', ', array_slice($tables, 0, 10));
                if (count($tables) > 10) {
                    echo ' ... and ' . (count($tables) - 10) . ' more';
                }
                echo '</p></div>';
                
                echo '<div class="warning">';
                echo '<h3>🔒 Security Reminder</h3>';
                echo '<p><strong>DELETE THIS FILE NOW!</strong></p>';
                echo '<p>Remove backend/import-sql.php from your project for security.</p>';
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
            <h3>📁 Select Your SQL File</h3>
            <input type="file" name="sql_file" id="sql_file" accept=".sql" required>
            <button type="submit" id="submitBtn">Import Database</button>
        </form>

        <div class="warning" style="margin-top: 30px;">
            <h3>⚠️ Important Notes:</h3>
            <ul>
                <li>This will import your SQL file into Railway MySQL</li>
                <li>Large files may take several minutes</li>
                <li><strong>DELETE THIS FILE after importing</strong></li>
            </ul>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Importing...';
        });
    </script>
</body>
</html>