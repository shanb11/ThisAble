<?php
// backend/debug/direct_api_test.php
// Test batch_calculate_matches.php directly without HTTP call

echo "<h1>🧪 Direct API Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .error{color:red;background:#fee;padding:10px;border-radius:4px;} .success{color:green;} pre{background:#f5f5f5;padding:10px;border-radius:4px;overflow-x:auto;}</style>";

// Start session first (simulate what empapplicants.php does)
session_start();

// Check if session has employer data
echo "<h2>1. Session Check</h2>";
if (isset($_SESSION['employer_id'])) {
    echo "✅ employer_id in session: " . $_SESSION['employer_id'] . "<br>";
} else {
    echo "<div class='error'>❌ No employer_id in session!</div>";
    echo "<p>You need to login first. Go to your employer login page.</p>";
    exit;
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    echo "✅ logged_in: true<br>";
} else {
    echo "<div class='error'>❌ Not logged in!</div>";
    exit;
}

// Test 2: Check if we have applicants
echo "<h2>2. Database Check</h2>";
try {
    require_once '../../backend/db.php';
    
    // Check job applications
    $apps_sql = "SELECT COUNT(*) as count FROM job_applications ja JOIN job_posts jp ON ja.job_id = jp.job_id WHERE jp.employer_id = ?";
    $apps_stmt = $conn->prepare($apps_sql);
    $apps_stmt->execute([$_SESSION['employer_id']]);
    $apps_count = $apps_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "✅ Total applications for your jobs: $apps_count<br>";
    
    if ($apps_count == 0) {
        echo "<div class='error'>❌ No applications found for your employer ID!</div>";
        echo "<p>This explains 'Processed 0 applicants'</p>";
    }
    
    // Check specific job 2
    $job2_sql = "SELECT COUNT(*) as count FROM job_applications WHERE job_id = 2";
    $job2_stmt = $conn->prepare($job2_sql);
    $job2_stmt->execute();
    $job2_count = $job2_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "✅ Applications for job ID 2: $job2_count<br>";
    
    // Check if job 2 belongs to this employer
    $owner_sql = "SELECT employer_id FROM job_posts WHERE job_id = 2";
    $owner_stmt = $conn->prepare($owner_sql);
    $owner_stmt->execute();
    $job_owner = $owner_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($job_owner) {
        echo "✅ Job 2 belongs to employer: " . $job_owner['employer_id'] . "<br>";
        if ($job_owner['employer_id'] != $_SESSION['employer_id']) {
            echo "<div class='error'>❌ Job 2 doesn't belong to your employer ID!</div>";
        }
    } else {
        echo "<div class='error'>❌ Job ID 2 not found!</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . $e->getMessage() . "</div>";
}

// Test 3: Simulate the exact API call
echo "<h2>3. Simulate API Call</h2>";

// Simulate POST data
$_POST = [];
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate the JSON input that empapplicants.js sends
$json_input = json_encode([
    'job_id' => 2,
    'force_recalculate' => true
]);

// Backup php://input simulation
file_put_contents('php://temp', $json_input);

// Capture all output from the API file
ob_start();

try {
    // Include the actual API file
    include '../../backend/employer/batch_calculate_matches.php';
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'PHP Exception: ' . $e->getMessage()
    ]);
}

$api_output = ob_get_clean();

echo "<h3>Raw API Output:</h3>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Test if it's valid JSON
$json_decoded = json_decode($api_output, true);
if ($json_decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "<div class='error'>❌ Output is NOT valid JSON!</div>";
    echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
    
    // Show first few lines to identify the issue
    $lines = explode("\n", $api_output);
    echo "<h4>First 5 lines of output:</h4>";
    echo "<pre>";
    for ($i = 0; $i < min(5, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]) . "\n";
    }
    echo "</pre>";
    
} else {
    echo "<div class='success'>✅ Valid JSON output!</div>";
    echo "<h4>Decoded Response:</h4>";
    echo "<pre>" . print_r($json_decoded, true) . "</pre>";
}

echo "<h2>💡 Diagnosis</h2>";
if ($apps_count == 0) {
    echo "<div class='error'>";
    echo "<h3>FOUND THE ISSUE!</h3>";
    echo "<p>You have 0 job applications for your employer account.</p>";
    echo "<p>This is why you're getting 'Processed 0 applicants'.</p>";
    echo "</div>";
    
    echo "<h3>Solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure you're logged in with the correct employer account</li>";
    echo "<li>Check if you have job applications from candidates</li>";
    echo "<li>Verify your job postings are active and accepting applications</li>";
    echo "</ul>";
} else {
    echo "<p>Database looks good. The issue might be in the API processing.</p>";
}
?>