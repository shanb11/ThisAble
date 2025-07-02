<?php
// backend/debug/test_api_direct.php
// Direct test of batch_calculate_matches.php to see the actual error

echo "<h1>🔍 API Error Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .error{color:red;background:#fee;padding:10px;border-radius:4px;} .success{color:green;} pre{background:#f5f5f5;padding:10px;border-radius:4px;}</style>";

// Test 1: Direct file check
echo "<h2>1. File Existence Check</h2>";
$files_to_check = [
    '../../backend/employer/batch_calculate_matches.php',
    '../../backend/employer/calculate_match_score.php', 
    '../../backend/employer/session_check.php',
    '../../backend/db.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "<span class='error'>❌ $file MISSING!</span><br>";
    }
}

// Test 2: Try to call the API directly
echo "<h2>2. Direct API Call Test</h2>";

// Simulate the API call
$api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/../employer/batch_calculate_matches.php';
echo "<p>Testing: <code>$api_url</code></p>";

$post_data = json_encode([
    'job_id' => 2,
    'force_recalculate' => true
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $post_data
    ]
]);

echo "<h3>Request Data:</h3>";
echo "<pre>" . htmlspecialchars($post_data) . "</pre>";

echo "<h3>Response:</h3>";
$response = @file_get_contents($api_url, false, $context);

if ($response === false) {
    echo "<div class='error'>❌ Failed to call API - check if server is running</div>";
} else {
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Try to decode as JSON
    $json_data = json_decode($response, true);
    if ($json_data === null) {
        echo "<div class='error'>❌ Response is NOT valid JSON</div>";
        echo "<p><strong>This is the problem!</strong> The API is returning HTML/PHP errors instead of JSON.</p>";
        
        // Show first 500 characters to see the error
        echo "<h4>First 500 characters of response:</h4>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        
    } else {
        echo "<div class='success'>✅ Valid JSON response!</div>";
        echo "<pre>" . print_r($json_data, true) . "</pre>";
    }
}

// Test 3: Check if we can include the main files without errors
echo "<h2>3. File Include Test</h2>";

try {
    // Test including db.php
    ob_start();
    include_once '../../backend/db.php';
    $db_output = ob_get_clean();
    
    if (empty($db_output)) {
        echo "✅ db.php includes without errors<br>";
    } else {
        echo "<div class='error'>❌ db.php produced output: " . htmlspecialchars($db_output) . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error including db.php: " . $e->getMessage() . "</div>";
}

try {
    // Test including calculate_match_score.php  
    ob_start();
    include_once '../../backend/employer/calculate_match_score.php';
    $calc_output = ob_get_clean();
    
    if (empty($calc_output)) {
        echo "✅ calculate_match_score.php includes without errors<br>";
    } else {
        echo "<div class='error'>❌ calculate_match_score.php produced output: " . htmlspecialchars($calc_output) . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error including calculate_match_score.php: " . $e->getMessage() . "</div>";
}

// Test 4: Check PHP error log
echo "<h2>4. Recent PHP Errors</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $recent_errors = array_slice(file($error_log), -10);
    if (!empty($recent_errors)) {
        echo "<pre>" . htmlspecialchars(implode('', $recent_errors)) . "</pre>";
    } else {
        echo "<p>No recent errors in log</p>";
    }
} else {
    echo "<p>Error log not found or not configured</p>";
}

echo "<h2>💡 Next Steps</h2>";
echo "<ul>";
echo "<li>If you see PHP errors above, fix those first</li>";
echo "<li>If files are missing, restore them</li>";
echo "<li>If the API response shows HTML/PHP errors, look at the error message</li>";
echo "<li>Common issues: syntax errors, missing files, database connection issues</li>";
echo "</ul>";
?>