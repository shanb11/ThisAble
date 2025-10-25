<?php
/**
 * Comprehensive Connection Tester
 * Tests ALL possible connection methods for Supabase on Railway
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$password = '082220EthanDrake';
$results = [];

// Test configurations
$connections = [
    [
        'name' => 'Direct Connection (IPv6)',
        'host' => 'db.jxllnfnzossijeidzhrq.supabase.co',
        'port' => '5432',
        'username' => 'postgres',
        'note' => 'Expected to fail on Railway (IPv6 only)'
    ],
    [
        'name' => 'Pooler - Format 1: postgres.project',
        'host' => 'aws-1-ap-southeast-1.pooler.supabase.com', // CORRECTED!
        'port' => '5432',
        'username' => 'postgres.jxllnfnzossijeidzhrq',
        'note' => 'Session mode with dot notation - CORRECT FORMAT'
    ],
    [
        'name' => 'Pooler - Format 2: postgres only',
        'host' => 'aws-1-ap-southeast-1.pooler.supabase.com', // CORRECTED!
        'port' => '5432',
        'username' => 'postgres',
        'note' => 'Session mode with simple username'
    ],
    [
        'name' => 'Pooler - Format 3: postgres-project',
        'host' => 'aws-1-ap-southeast-1.pooler.supabase.com', // CORRECTED!
        'port' => '5432',
        'username' => 'postgres-jxllnfnzossijeidzhrq',
        'note' => 'Session mode with dash notation'
    ],
    [
        'name' => 'Pooler Transaction - Format 1',
        'host' => 'aws-1-ap-southeast-1.pooler.supabase.com', // CORRECTED!
        'port' => '6543',
        'username' => 'postgres.jxllnfnzossijeidzhrq',
        'note' => 'Transaction mode (port 6543)'
    ],
    [
        'name' => 'Pooler Transaction - Format 2',
        'host' => 'aws-1-ap-southeast-1.pooler.supabase.com', // CORRECTED!
        'port' => '6543',
        'username' => 'postgres',
        'note' => 'Transaction mode simple username'
    ],
];

foreach ($connections as $config) {
    $result = [
        'name' => $config['name'],
        'config' => [
            'host' => $config['host'],
            'port' => $config['port'],
            'username' => $config['username'],
        ],
        'note' => $config['note'],
    ];
    
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname=postgres;sslmode=require";
        
        $start = microtime(true);
        $conn = new PDO(
            $dsn,
            $config['username'],
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]
        );
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        
        // Test a simple query
        $stmt = $conn->query("SELECT COUNT(*) as count FROM job_seekers");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $result['status'] = '✅ SUCCESS';
        $result['connect_time_ms'] = $elapsed;
        $result['test_query'] = "Found {$data['count']} job seekers";
        
    } catch (Exception $e) {
        $result['status'] = '❌ FAILED';
        $result['error'] = $e->getMessage();
        $result['error_code'] = $e->getCode();
    }
    
    $results[] = $result;
}

// Summary
$successful = array_filter($results, fn($r) => $r['status'] === '✅ SUCCESS');
$summary = [
    'total_tests' => count($results),
    'successful' => count($successful),
    'failed' => count($results) - count($successful),
    'recommendation' => count($successful) > 0 
        ? "✅ Found working connection! Use: " . $successful[array_key_first($successful)]['name']
        : "❌ No working connections found. Check Supabase pooler settings.",
];

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'summary' => $summary,
    'results' => $results,
], JSON_PRETTY_PRINT);
?>