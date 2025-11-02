<?php
/**
 * Fix pwd_ids Table Creation
 * Upload to: backend/fix_pwd_ids.php
 * Access: https://thisable-production.up.railway.app/backend/fix_pwd_ids.php?key=fix-2025
 */

$secret_key = "fix-2025";
$provided_key = $_GET['key'] ?? '';

if ($provided_key !== $secret_key) {
    die("Access denied. Use: ?key=fix-2025");
}

// Get MySQL connection
try {
    $mysql_url = getenv('MYSQL_URL');
    
    if ($mysql_url) {
        $parsed = parse_url($mysql_url);
        $host = $parsed['host'] ?? 'localhost';
        $port = $parsed['port'] ?? '3306';
        $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway';
        $username = $parsed['user'] ?? 'root';
        $password = $parsed['pass'] ?? '';
    } else {
        $host = getenv('MYSQLHOST') ?: 'localhost';
        $port = getenv('MYSQLPORT') ?: '3306';
        $dbname = getenv('MYSQLDATABASE') ?: 'railway';
        $username = getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: '';
    }

    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>🔧 Fixing pwd_ids Table</h2>";

    // Drop if exists
    echo "<p>Dropping old table if exists...</p>";
    $conn->exec("DROP TABLE IF EXISTS pwd_ids");
    echo "<p>✅ Dropped</p>";

    // Create with fixed syntax (no DEFAULT curdate())
    echo "<p>Creating pwd_ids table with fixed syntax...</p>";
    $sql = "
    CREATE TABLE `pwd_ids` (
      `pwd_id` int(11) NOT NULL AUTO_INCREMENT,
      `seeker_id` int(11) NOT NULL,
      `pwd_id_number` varchar(50) NOT NULL,
      `issued_at` date DEFAULT NULL,
      `is_verified` tinyint(1) DEFAULT 0,
      `verification_date` datetime DEFAULT NULL,
      `verification_attempts` int(11) DEFAULT 0,
      `id_image_path` varchar(255) DEFAULT NULL,
      `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
      `verification_notes` text DEFAULT NULL,
      PRIMARY KEY (`pwd_id`),
      UNIQUE KEY `seeker_id` (`seeker_id`),
      UNIQUE KEY `pwd_id_number` (`pwd_id_number`),
      CONSTRAINT `pwd_ids_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `job_seekers` (`seeker_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    $conn->exec($sql);
    echo "<p>✅ Table created successfully!</p>";

    // Insert sample data if you had any
    echo "<p>Inserting sample data...</p>";
    $insertSQL = "
    INSERT INTO `pwd_ids` (`pwd_id`, `seeker_id`, `pwd_id_number`, `issued_at`, `is_verified`, `verification_date`, `verification_attempts`, `id_image_path`, `verification_status`, `verification_notes`) VALUES
    (1, 4, 'PWD-2024-001', '2024-01-15', 1, '2024-01-20 10:30:00', 1, 'uploads/pwd_ids/pwd_001.jpg', 'verified', 'Verified on first attempt'),
    (2, 5, 'PWD-2024-002', '2024-02-10', 1, '2024-02-12 14:45:00', 1, 'uploads/pwd_ids/pwd_002.jpg', 'verified', NULL),
    (3, 6, 'PWD-2024-003', '2024-03-05', 0, NULL, 0, 'uploads/pwd_ids/pwd_003.jpg', 'pending', 'Waiting for verification'),
    (4, 7, 'PWD-2024-004', '2024-03-20', 1, '2024-03-22 09:15:00', 2, 'uploads/pwd_ids/pwd_004.jpg', 'verified', 'Verified after resubmission'),
    (5, 8, 'PWD-2024-005', '2024-04-01', 0, NULL, 1, 'uploads/pwd_ids/pwd_005.jpg', 'rejected', 'ID image unclear, please resubmit');
    ";
    
    try {
        $conn->exec($insertSQL);
        echo "<p>✅ Sample data inserted!</p>";
    } catch (PDOException $e) {
        echo "<p>⚠️ Could not insert sample data (this is OK if seeker_ids don't exist): " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    // Verify
    $stmt = $conn->query("SELECT COUNT(*) as count FROM pwd_ids");
    $count = $stmt->fetch()['count'];
    echo "<h3 style='color: green;'>✅ SUCCESS!</h3>";
    echo "<p>pwd_ids table created with <strong>$count</strong> records</p>";
    
    // Show all tables
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Total tables in database: " . count($tables) . "</strong></p>";
    
    echo "<hr>";
    echo "<h3>🎉 Database is now complete!</h3>";
    echo "<p><a href='../frontend/candidate/login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Now!</a></p>";
    
    echo "<hr>";
    echo "<p style='color: red;'><strong>⚠️ Security:</strong> Delete this file after use: <code>backend/fix_pwd_ids.php</code></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>