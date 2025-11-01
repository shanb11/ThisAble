<?php
/**
 * Railway Environment Variable Checker
 * Upload to: backend/check-env.php
 * Access: https://thisable-production.up.railway.app/backend/check-env.php?key=check-2025
 */

$secret_key = "check-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=check-2025");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Railway Environment Check</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a2e;
            color: #eee;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 { color: #e94560; }
        .section {
            background: #16213e;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            border: 1px solid #0f3460;
        }
        .var {
            background: #0f3460;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: monospace;
        }
        .key {
            color: #4CAF50;
            font-weight: bold;
        }
        .value {
            color: #FF9800;
            word-break: break-all;
        }
        .success { color: #4CAF50; }
        .error { color: #e74c3c; }
        .warning { color: #f39c12; }
    </style>
</head>
<body>
    <h1>🔍 Railway Environment Variables</h1>

    <div class="section">
        <h2>MySQL-Related Environment Variables:</h2>
        <?php
        $mysql_vars = [
            'MYSQL_URL',
            'MYSQLHOST',
            'MYSQL_HOST',
            'MYSQLPORT',
            'MYSQL_PORT',
            'MYSQLDATABASE',
            'MYSQL_DATABASE',
            'MYSQLUSER',
            'MYSQL_USER',
            'MYSQLPASSWORD',
            'MYSQL_PASSWORD',
            'DATABASE_URL',
            'DB_HOST',
            'DB_PORT',
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD'
        ];
        
        $found = [];
        foreach ($mysql_vars as $var) {
            $value = getenv($var);
            if ($value !== false) {
                // Hide password
                if (stripos($var, 'PASSWORD') !== false || stripos($var, 'PASS') !== false) {
                    $display_value = '***' . substr($value, -4) . ' (length: ' . strlen($value) . ')';
                } else {
                    $display_value = $value;
                }
                
                echo '<div class="var">';
                echo '<span class="key">' . htmlspecialchars($var) . '</span>: ';
                echo '<span class="value">' . htmlspecialchars($display_value) . '</span>';
                echo '</div>';
                
                $found[$var] = $value;
            }
        }
        
        if (empty($found)) {
            echo '<p class="error">❌ No MySQL environment variables found!</p>';
            echo '<p class="warning">⚠️ Make sure MySQL database is added in Railway and linked to this service.</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Connection Test:</h2>
        <?php
        if (!empty($found)) {
            // Try to parse MYSQL_URL if it exists
            if (isset($found['MYSQL_URL'])) {
                $url = $found['MYSQL_URL'];
                echo '<h3>Parsing MYSQL_URL:</h3>';
                
                $parsed = parse_url($url);
                if ($parsed) {
                    echo '<div class="var"><span class="key">Scheme:</span> <span class="value">' . ($parsed['scheme'] ?? 'N/A') . '</span></div>';
                    echo '<div class="var"><span class="key">Host:</span> <span class="value">' . ($parsed['host'] ?? 'N/A') . '</span></div>';
                    echo '<div class="var"><span class="key">Port:</span> <span class="value">' . ($parsed['port'] ?? '3306') . '</span></div>';
                    echo '<div class="var"><span class="key">User:</span> <span class="value">' . ($parsed['user'] ?? 'N/A') . '</span></div>';
                    echo '<div class="var"><span class="key">Database:</span> <span class="value">' . (isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'N/A') . '</span></div>';
                    
                    // Try connection
                    echo '<h3>Testing Connection:</h3>';
                    try {
                        $host = $parsed['host'] ?? 'localhost';
                        $port = $parsed['port'] ?? 3306;
                        $user = $parsed['user'] ?? 'root';
                        $pass = $parsed['pass'] ?? '';
                        $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway';
                        
                        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                        echo '<div class="var"><span class="key">DSN:</span> <span class="value">' . htmlspecialchars($dsn) . '</span></div>';
                        
                        $conn = new PDO($dsn, $user, $pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 5
                        ]);
                        
                        echo '<p class="success">✅ Connection successful!</p>';
                        
                        // Check tables
                        $stmt = $conn->query("SHOW TABLES");
                        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        if (empty($tables)) {
                            echo '<p class="warning">⚠️ Database is empty (no tables found)</p>';
                        } else {
                            echo '<p class="success">✅ Found ' . count($tables) . ' tables: ' . implode(', ', array_slice($tables, 0, 5));
                            if (count($tables) > 5) echo '...';
                            echo '</p>';
                        }
                        
                    } catch (PDOException $e) {
                        echo '<p class="error">❌ Connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
            } else {
                // Try using individual vars
                echo '<h3>Trying Individual Variables:</h3>';
                
                $host = $found['MYSQLHOST'] ?? $found['MYSQL_HOST'] ?? 'localhost';
                $port = $found['MYSQLPORT'] ?? $found['MYSQL_PORT'] ?? '3306';
                $user = $found['MYSQLUSER'] ?? $found['MYSQL_USER'] ?? 'root';
                $pass = $found['MYSQLPASSWORD'] ?? $found['MYSQL_PASSWORD'] ?? '';
                $dbname = $found['MYSQLDATABASE'] ?? $found['MYSQL_DATABASE'] ?? 'railway';
                
                echo '<div class="var"><span class="key">Host:</span> <span class="value">' . htmlspecialchars($host) . '</span></div>';
                echo '<div class="var"><span class="key">Port:</span> <span class="value">' . htmlspecialchars($port) . '</span></div>';
                echo '<div class="var"><span class="key">User:</span> <span class="value">' . htmlspecialchars($user) . '</span></div>';
                echo '<div class="var"><span class="key">Database:</span> <span class="value">' . htmlspecialchars($dbname) . '</span></div>';
                
                try {
                    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                    $conn = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5
                    ]);
                    
                    echo '<p class="success">✅ Connection successful!</p>';
                    
                } catch (PDOException $e) {
                    echo '<p class="error">❌ Connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>Recommendations:</h2>
        <ol>
            <li>Copy the successful connection parameters from above</li>
            <li>I'll create a fixed db.php and import-sql.php for you</li>
            <li>Share this page output with me so I can fix the files</li>
        </ol>
    </div>

    <div class="section">
        <h2>⚠️ Security:</h2>
        <p>Delete this file after checking: <code>backend/check-env.php</code></p>
    </div>
</body>
</html>