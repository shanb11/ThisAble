<?php
/**
 * Remote Database Connection Test
 * File: C:\xampp\htdocs\ThisAble\api\test_remote_db.php
 *
 * Open this in your browser: http://localhost/ThisAble/api/test_remote_db.php
 * It must say "CONNECTION SUCCESSFUL" before Flutter will work.
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host     = "sql202.infinityfree.com";
$port     = "3306";
$dbname   = "if0_40570875_jobportal";
$username = "if0_40570875";
$password = "i10cRqDoVjtsm";

$startTime = microtime(true);

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 30,
        ]
    );

    $elapsed = round((microtime(true) - $startTime) * 1000, 2);

    // Run basic queries to confirm the data is real
    $seekers   = $conn->query("SELECT COUNT(*) as c FROM job_seekers")->fetch()['c'];
    $employers = $conn->query("SELECT COUNT(*) as c FROM employers")->fetch()['c'];
    $jobs      = $conn->query("SELECT COUNT(*) as c FROM job_posts")->fetch()['c'];

    echo json_encode([
        'success' => true,
        'message' => '✅ CONNECTION SUCCESSFUL — Flutter will work!',
        'data' => [
            'host'           => $host,
            'database'       => $dbname,
            'connect_time'   => $elapsed . 'ms',
            'job_seekers'    => (int)$seekers,
            'employers'      => (int)$employers,
            'job_posts'      => (int)$jobs,
            'server_time'    => date('Y-m-d H:i:s'),
        ]
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    $elapsed = round((microtime(true) - $startTime) * 1000, 2);
    echo json_encode([
        'success' => false,
        'message' => '❌ CONNECTION FAILED — check the error below',
        'error'   => $e->getMessage(),
        'elapsed' => $elapsed . 'ms',
        'hint'    => 'This usually means InfinityFree is temporarily blocking outbound connections from your IP. Try again in a few minutes, or check your XAMPP network settings.',
    ], JSON_PRETTY_PRINT);
}
?>