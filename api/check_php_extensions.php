<?php
header('Content-Type: application/json');

$results = [
    'php_version' => phpversion(),
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'pgsql' => extension_loaded('pgsql'),
    ],
    'all_loaded_extensions' => get_loaded_extensions(),
];

// Test DNS resolution
$results['dns_test'] = [
    'can_resolve_google' => gethostbyname('google.com') !== 'google.com',
    'can_resolve_supabase' => gethostbyname('db.jxllnfnzossijeidzhrq.supabase.co') !== 'db.jxllnfnzossijeidzhrq.supabase.co',
];

// Test if we can reach Supabase
$supabase_host = 'db.jxllnfnzossijeidzhrq.supabase.co';
$supabase_port = 5432;

$results['connection_test'] = [];

// Test if port is reachable
$fp = @fsockopen($supabase_host, $supabase_port, $errno, $errstr, 5);
if ($fp) {
    $results['connection_test']['port_reachable'] = true;
    fclose($fp);
} else {
    $results['connection_test']['port_reachable'] = false;
    $results['connection_test']['error_number'] = $errno;
    $results['connection_test']['error_message'] = $errstr;
}

// Diagnosis
$diagnosis = [];

if (!$results['extensions']['pdo_pgsql']) {
    $diagnosis[] = '❌ CRITICAL: pdo_pgsql extension is NOT loaded. Enable it in php.ini';
}

if (!$results['dns_test']['can_resolve_google']) {
    $diagnosis[] = '❌ CRITICAL: DNS is not working. Check internet connection.';
}

if (!$results['dns_test']['can_resolve_supabase']) {
    $diagnosis[] = '❌ WARNING: Cannot resolve Supabase hostname. Check firewall or DNS settings.';
}

if (!$results['connection_test']['port_reachable']) {
    $diagnosis[] = '❌ CRITICAL: Cannot connect to Supabase port 5432. Firewall might be blocking it.';
}

if (empty($diagnosis)) {
    $diagnosis[] = '✅ All checks passed! Connection should work.';
}

$results['diagnosis'] = $diagnosis;
$results['success'] = empty(array_filter($diagnosis, fn($d) => str_starts_with($d, '❌ CRITICAL')));

echo json_encode($results, JSON_PRETTY_PRINT);
?>