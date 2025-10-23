<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'environment_vars' => [
        'DB_HOST' => getenv('DB_HOST') ?: 'not set (getenv)',
        'DB_HOST_ENV' => $_ENV['DB_HOST'] ?? 'not set ($_ENV)',
        'DB_HOST_SERVER' => $_SERVER['DB_HOST'] ?? 'not set ($_SERVER)',
    ],
    'pgsql_loaded' => extension_loaded('pgsql'),
    'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql'),
];

// Try connection
try {
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'db.jxllnfnzossijeidzhrq.supabase.co';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
    $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'postgres';
    $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '082220EthanDrake';
    
    $debug['connection_attempt'] = [
        'host' => $host,
        'port' => $port,
        'dbname' => $dbname,
        'username' => $username,
        'password_length' => strlen($password)
    ];
    
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $username,
        $password,
        [PDO::ATTR_TIMEOUT => 5]
    );
    
    $debug['connection_status'] = 'SUCCESS';
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['test_query'] = "Found {$result['count']} job seekers";
    
} catch (Exception $e) {
    $debug['connection_status'] = 'FAILED';
    $debug['error'] = $e->getMessage();
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>