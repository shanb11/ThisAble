<?php
/**
 * Railway Deployment Diagnostic Tool
 * Tests database connectivity using the same logic as database.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
];

// Check environment detection
$isRailway = !empty($_ENV['RAILWAY_ENVIRONMENT']) || 
             !empty(getenv('RAILWAY_ENVIRONMENT')) ||
             !empty($_SERVER['RAILWAY_ENVIRONMENT'] ?? '');

$isVercel = !empty($_ENV['VERCEL']) || !empty(getenv('VERCEL'));
$isCloudEnvironment = $isRailway || $isVercel;

$debug['environment_detection'] = [
    'is_railway' => $isRailway,
    'is_vercel' => $isVercel,
    'is_cloud' => $isCloudEnvironment,
    'railway_env_var' => $_ENV['RAILWAY_ENVIRONMENT'] ?? 'not set',
];

// Check environment variables
$debug['environment_vars'] = [
    'DB_HOST' => getenv('DB_HOST') ?: 'not set (getenv)',
    'DB_HOST_ENV' => $_ENV['DB_HOST'] ?? 'not set ($_ENV)',
    'DB_HOST_SERVER' => $_SERVER['DB_HOST'] ?? 'not set ($_SERVER)',
];

// Check PHP extensions
$debug['php_extensions'] = [
    'pgsql_loaded' => extension_loaded('pgsql'),
    'pdo_pgsql_loaded' => extension_loaded('pdo_pgsql'),
];

// Determine connection parameters based on environment
$dbname = 'postgres';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '082220EthanDrake';

if ($isCloudEnvironment) {
    // Cloud: Try both session mode and transaction mode
    // Session mode (port 5432) - more compatible, may work better
    $useSessionMode = getEnvVar('USE_SESSION_MODE', 'true') === 'true';
    
    if ($useSessionMode) {
        $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
        $port = '5432'; // Session mode
        $username = 'postgres.jxllnfnzossijeidzhrq';
        $connectionType = 'SUPABASE POOLER - SESSION MODE (Cloud) - IPv4';
    } else {
        $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
        $port = '6543'; // Transaction mode
        $username = 'postgres.jxllnfnzossijeidzhrq';
        $connectionType = 'SUPABASE POOLER - TRANSACTION MODE (Cloud) - IPv4';
    }
} else {
    // Local: Direct connection
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'db.jxllnfnzossijeidzhrq.supabase.co';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
    $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'postgres';
    $connectionType = 'DIRECT CONNECTION (Local)';
}

$debug['connection_attempt'] = [
    'connection_type' => $connectionType,
    'host' => $host,
    'port' => $port,
    'dbname' => $dbname,
    'username' => $username,
    'password_length' => strlen($password),
    'using_pooler' => $isCloudEnvironment
];

// Try database connection
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $debug['connection_string'] = $dsn;
    
    $conn = new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]
    );
    
    $debug['connection_status'] = '✅ SUCCESS';
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['test_query'] = "✅ Found {$result['count']} job seekers in database";
    
    // Test another table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM api_tokens");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $debug['api_tokens'] = "✅ Found {$result['count']} API tokens";
    
    $debug['conclusion'] = '🎉 ALL TESTS PASSED - DATABASE FULLY OPERATIONAL';
    
} catch (Exception $e) {
    $debug['connection_status'] = '❌ FAILED';
    $debug['error'] = $e->getMessage();
    $debug['error_code'] = $e->getCode();
    
    // Troubleshooting suggestions
    $debug['troubleshooting'] = [
        'check_1' => 'Verify Railway environment variables are set',
        'check_2' => 'Verify Supabase pooler is accessible: aws-0-ap-southeast-1.pooler.supabase.com:6543',
        'check_3' => 'Check if IPv6 is causing issues',
        'check_4' => 'Verify password is correct',
        'suggestion' => $isCloudEnvironment 
            ? 'Using pooler - check if pooler is enabled in Supabase' 
            : 'Using direct connection - may need to switch to pooler'
    ];
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>