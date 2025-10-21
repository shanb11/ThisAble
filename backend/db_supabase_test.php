<?php
/**
 * Supabase PostgreSQL Test Connection
 * Ready to use with your credentials
 */

// Your Supabase connection details
$host = "db.jxllnfnzossijeidzhrq.supabase.co";
$port = "5432";
$dbname = "postgres";
$username = "postgres";
$password = "082220EthanDrake";

echo "<h2>Testing Supabase Connection...</h2>";
echo "<hr>";

try {
    echo "📡 Connecting to: $host<br>";
    echo "🔑 Using user: $username<br>";
    echo "💾 Database: $dbname<br><br>";
    
    $conn_test = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "<h3 style='color: green;'>✅ SUCCESS! Connected to Supabase PostgreSQL</h3>";
    echo "<hr>";
    
    // Test 1: Count records
    echo "<h4>Test 1: Count Records</h4>";
    $stmt = $conn_test->query("SELECT COUNT(*) as count FROM job_seekers");
    $result = $stmt->fetch();
    echo "📊 Job Seekers in database: <strong>" . $result['count'] . "</strong><br><br>";
    
    // Test 2: Get sample data
    echo "<h4>Test 2: Sample Data</h4>";
    $stmt = $conn_test->query("SELECT seeker_id, first_name, last_name, contact_number FROM job_seekers LIMIT 1");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "👤 Sample User Found:<br>";
        echo "<pre>" . print_r($user, true) . "</pre>";
    } else {
        echo "⚠️ No users found in database<br>";
    }
    
    // Test 3: List all tables
    echo "<h4>Test 3: Database Tables</h4>";
    $stmt = $conn_test->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 Total tables: <strong>" . count($tables) . "</strong><br>";
    echo "<details><summary>Click to see all tables</summary><pre>";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "</pre></details><br>";
    
    echo "<hr>";
    echo "<h3 style='color: green;'>🎉 ALL TESTS PASSED!</h3>";
    echo "<p><strong>Next step:</strong> Update your db.php file to use these credentials.</p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ CONNECTION FAILED</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h4>Troubleshooting Checklist:</h4>";
    echo "<ul>";
    echo "<li>✓ PostgreSQL extension enabled in php.ini?</li>";
    echo "<li>✓ Host is: db.jxllnfnzossijeidzhrq.supabase.co</li>";
    echo "<li>✓ Password is: 082220EthanDrake</li>";
    echo "<li>✓ Internet connection working?</li>";
    echo "<li>✓ Supabase project active?</li>";
    echo "</ul>";
}
?>
