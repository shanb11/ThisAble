<?php
echo "<h1>Supabase Connection Diagnostics</h1><hr>";

$host = "db.jxllnfnzossijeidzhrq.supabase.co";

// Test 1: Internet
echo "<h3>1. Internet Connectivity</h3>";
$google = @file_get_contents('https://www.google.com');
echo $google ? "✅ Internet works<br>" : "❌ No internet<br>";

// Test 2: DNS Resolution
echo "<h3>2. DNS Resolution</h3>";
$ip = gethostbyname($host);
if ($ip !== $host) {
    echo "✅ DNS works: Resolved to $ip<br>";
} else {
    echo "❌ DNS failed: Cannot resolve $host<br>";
    echo "<strong>FIX:</strong> Change DNS to 8.8.8.8<br>";
}

// Test 3: Ping (via socket)
echo "<h3>3. Network Reachability</h3>";
$socket = @fsockopen($host, 5432, $errno, $errstr, 10);
if ($socket) {
    echo "✅ Can reach Supabase server<br>";
    fclose($socket);
} else {
    echo "❌ Cannot reach server: $errstr<br>";
}

// Test 4: Extensions
echo "<h3>4. PHP Extensions</h3>";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? '✅' : '❌') . "<br>";
echo "PostgreSQL: " . (extension_loaded('pgsql') ? '✅' : '❌') . "<br>";
echo "OpenSSL: " . (extension_loaded('openssl') ? '✅' : '❌') . "<br>";

// Test 5: Supabase Dashboard
echo "<h3>5. Supabase Dashboard Access</h3>";
$dashboard = @file_get_contents('https://supabase.com');
echo $dashboard ? "✅ Can reach Supabase<br>" : "❌ Cannot reach Supabase<br>";

echo "<hr><h3>Recommended Actions:</h3><ol>";
if ($ip === $host) {
    echo "<li><strong>Change DNS to 8.8.8.8 (Google DNS)</strong></li>";
    echo "<li>Flush DNS cache: ipconfig /flushdns</li>";
    echo "<li>Restart router</li>";
}
if (!extension_loaded('openssl')) {
    echo "<li><strong>Enable OpenSSL in php.ini</strong></li>";
}
echo "</ol>";
?>
```

**Run:**
```
http://localhost/ThisAble/diagnose.php