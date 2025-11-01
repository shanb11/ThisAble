<?php
/**
 * Supabase PostgreSQL Connection Test for Railway
 * Upload to: backend/test_supabase_connection.php
 * Access: https://thisable-production.up.railway.app/backend/test_supabase_connection.php?key=test-2025
 */

$secret_key = "test-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=test-2025");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Supabase PostgreSQL Connection Test</title>
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
        .success { 
            color: #4CAF50;
            background: #1e4620;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .error { 
            color: #e74c3c;
            background: #4a1a1a;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .warning { 
            color: #f39c12;
            background: #4a3a1a;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .info {
            background: #0f3460;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>🔍 Supabase PostgreSQL Connection Test</h1>

    <div class="section">
        <h2>Step 1: Check PHP PostgreSQL Extension</h2>
        <?php
        if (extension_loaded('pdo_pgsql')) {
            echo '<p class="success">✅ PDO PostgreSQL extension is loaded!</p>';
        } else {
            echo '<p class="error">❌ PDO PostgreSQL extension is NOT loaded!</p>';
            echo '<p class="warning">⚠️ Railway needs to have PHP with pdo_pgsql extension.</p>';
            echo '<p class="info">To fix: Add a nixpacks.toml file or use a Dockerfile with PostgreSQL support.</p>';
        }
        
        if (extension_loaded('pgsql')) {
            echo '<p class="success">✅ Native PostgreSQL extension is loaded!</p>';
        } else {
            echo '<p class="warning">⚠️ Native PostgreSQL extension is not loaded (optional).</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Step 2: Environment Variables</h2>
        <?php
        $db_vars = [
            'DB_HOST',
            'DB_PORT', 
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD',
            'PGHOST',
            'PGPORT',
            'PGDATABASE',
            'PGUSER',
            'PGPASSWORD'
        ];
        
        $found = [];
        foreach ($db_vars as $var) {
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
            echo '<p class="error">❌ No PostgreSQL environment variables found!</p>';
        } else {
            echo '<p class="success">✅ Found ' . count($found) . ' environment variables</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Step 3: Connection Test</h2>
        <?php
        if (!extension_loaded('pdo_pgsql')) {
            echo '<p class="error">❌ Cannot test connection - pdo_pgsql extension not available</p>';
        } else {
            // Get connection parameters
            $host = getenv('PGHOST') ?: getenv('DB_HOST') ?: 'db.jxllnfnzossijeidzhrq.supabase.co';
            $port = getenv('PGPORT') ?: getenv('DB_PORT') ?: '5432';
            $dbname = getenv('PGDATABASE') ?: getenv('DB_NAME') ?: 'postgres';
            $username = getenv('PGUSER') ?: getenv('DB_USER') ?: 'postgres';
            $password = getenv('PGPASSWORD') ?: getenv('DB_PASSWORD') ?: '082220EthanDrake';
            
            echo '<div class="info">';
            echo '<p><strong>Attempting connection with:</strong></p>';
            echo '<div class="var"><span class="key">Host:</span> <span class="value">' . htmlspecialchars($host) . '</span></div>';
            echo '<div class="var"><span class="key">Port:</span> <span class="value">' . htmlspecialchars($port) . '</span></div>';
            echo '<div class="var"><span class="key">Database:</span> <span class="value">' . htmlspecialchars($dbname) . '</span></div>';
            echo '<div class="var"><span class="key">Username:</span> <span class="value">' . htmlspecialchars($username) . '</span></div>';
            echo '<div class="var"><span class="key">Password:</span> <span class="value">***' . substr($password, -4) . '</span></div>';
            echo '</div>';
            
            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
                echo '<div class="var"><span class="key">DSN:</span> <span class="value">' . htmlspecialchars($dsn) . '</span></div>';
                
                $conn = new PDO(
                    $dsn,
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_TIMEOUT => 10
                    ]
                );
                
                echo '<p class="success">✅ CONNECTION SUCCESSFUL!</p>';
                
                // Test query
                echo '<h3>Testing Database Query:</h3>';
                try {
                    // Check if job_seekers table exists
                    $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
                    $result = $stmt->fetch();
                    echo '<p class="success">✅ Query successful! Found ' . $result['count'] . ' job seekers in database.</p>';
                    
                    // List all tables
                    $stmt = $conn->query("
                        SELECT table_name 
                        FROM information_schema.tables 
                        WHERE table_schema = 'public' 
                        ORDER BY table_name
                        LIMIT 10
                    ");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    echo '<div class="info">';
                    echo '<p><strong>Sample Tables:</strong></p>';
                    foreach ($tables as $table) {
                        echo '<div class="var">📋 ' . htmlspecialchars($table) . '</div>';
                    }
                    echo '</div>';
                    
                } catch (PDOException $e) {
                    echo '<p class="warning">⚠️ Connection works but query failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<p class="info">This might mean your database is empty or tables don\'t exist yet.</p>';
                }
                
            } catch (PDOException $e) {
                echo '<p class="error">❌ CONNECTION FAILED!</p>';
                echo '<div class="error">';
                echo '<p><strong>Error Details:</strong></p>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
                
                echo '<div class="info">';
                echo '<h3>Troubleshooting:</h3>';
                echo '<ul>';
                echo '<li>Check if Supabase project is active</li>';
                echo '<li>Verify password is correct: 082220EthanDrake</li>';
                echo '<li>Check if Railway allows outbound connections to Supabase</li>';
                echo '<li>Verify Supabase host: db.jxllnfnzossijeidzhrq.supabase.co</li>';
                echo '</ul>';
                echo '</div>';
            }
        }
        ?>
    </div>

    <div class="section">
        <h2>Step 4: PHP Info</h2>
        <?php
        echo '<div class="var"><span class="key">PHP Version:</span> <span class="value">' . PHP_VERSION . '</span></div>';
        echo '<div class="var"><span class="key">Server Software:</span> <span class="value">' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</span></div>';
        echo '<div class="var"><span class="key">Document Root:</span> <span class="value">' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</span></div>';
        ?>
    </div>

    <div class="section">
        <h2>✅ Next Steps:</h2>
        <div class="info">
            <?php if (!extension_loaded('pdo_pgsql')): ?>
                <h3>🔧 Fix Required: Install PostgreSQL Extension</h3>
                <p>Create a file: <code>nixpacks.toml</code> in your project root with:</p>
                <pre style="background: #0a0a0a; padding: 10px; border-radius: 5px;">
[phases.setup]
nixPkgs = ['...', 'postgresql']

[phases.install]
cmds = ['...']
                </pre>
                <p>Or use a Dockerfile with PHP-FPM that includes PostgreSQL extension.</p>
            <?php else: ?>
                <h3>✅ Everything looks good!</h3>
                <p>Your db.php should work correctly now.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="section">
        <h2>⚠️ Security Warning</h2>
        <p class="warning">DELETE THIS FILE after testing: <code>backend/test_supabase_connection.php</code></p>
    </div>
</body>
</html>