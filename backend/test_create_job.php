<?php
/**
 * Test script for create job API
 * Save this as: backend/test_create_job.php
 * Access at: http://localhost/ThisAble/backend/test_create_job.php
 */

session_start();

// Include required files
require_once('db.php');
require_once('shared/session_helper.php');

echo "<h2>Create Job API Test</h2>";

// Check if session exists
echo "<h3>1. Session Check:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Check if employer is logged in
echo "<h3>2. Employer Login Check:</h3>";
if (function_exists('isEmployerLoggedIn')) {
    $isLoggedIn = isEmployerLoggedIn();
    echo "isEmployerLoggedIn(): " . ($isLoggedIn ? "YES" : "NO") . "<br>";
    
    if ($isLoggedIn) {
        echo "Employer ID: " . getCurrentEmployerId() . "<br>";
        echo "Company Name: " . getCurrentCompanyName() . "<br>";
    }
} else {
    echo "❌ Function isEmployerLoggedIn() does not exist!<br>";
}

// Check if getEmployerData function exists
echo "<h3>3. Function Check:</h3>";
if (function_exists('getEmployerData')) {
    echo "✅ getEmployerData() function exists<br>";
    
    if (isset($_SESSION['employer_id'])) {
        try {
            $employer_data = getEmployerData($_SESSION['employer_id']);
            echo "Employer data: ";
            var_dump($employer_data);
        } catch (Exception $e) {
            echo "❌ Error calling getEmployerData(): " . $e->getMessage();
        }
    }
} else {
    echo "❌ getEmployerData() function does not exist!<br>";
    echo "You need to add this function to your session_helper.php<br>";
}

// Test database connection
echo "<h3>4. Database Connection Test:</h3>";
try {
    $test_query = $conn->query("SELECT COUNT(*) as count FROM job_posts");
    $result = $test_query->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database connected. Current job posts: " . $result['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test POST to create job API
echo "<h3>5. Test Create Job API:</h3>";
if (isset($_POST['test_api'])) {
    $test_data = [
        'job_title' => 'Test Job',
        'department' => 'Engineering',
        'location' => 'Manila',
        'employment_type' => 'Full-time',
        'job_description' => 'This is a test job description.',
        'job_requirements' => 'Test requirements here.',
        'remote_work_available' => true,
        'flexible_schedule' => false,
        'accommodations' => [
            'wheelchair_accessible' => true,
            'assistive_technology' => false,
            'remote_work_option' => true,
            'screen_reader_compatible' => false,
            'sign_language_interpreter' => false,
            'modified_workspace' => false,
            'transportation_support' => false,
            'additional_accommodations' => 'Test accommodations'
        ]
    ];
    
    // Make POST request to create job API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/ThisAble/backend/employer/create_job.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Cookie: ' . $_SERVER['HTTP_COOKIE'] // Forward session cookie
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: " . $http_code . "<br>";
    echo "Response: <pre>" . $response . "</pre>";
} else {
    // Show test button
    echo '<form method="POST">';
    echo '<button type="submit" name="test_api" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">Test Create Job API</button>';
    echo '</form>';
}

// Check if create_job.php file exists
echo "<h3>6. File Check:</h3>";
$create_job_file = __DIR__ . '/employer/create_job.php';
if (file_exists($create_job_file)) {
    echo "✅ create_job.php exists<br>";
} else {
    echo "❌ create_job.php file not found at: " . $create_job_file . "<br>";
}
?>