<?php
/**
 * Database Connection - InfinityFree MySQL
 * Auto-detects environment and connects to appropriate database
 * 
 * Localhost (XAMPP): MySQL local database
 * InfinityFree Production: MySQL remote database
 * 
 * Save as: C:\xampp\htdocs\ThisAble\backend\db.php
 * Then upload via FileZilla to: /htdocs/backend/db.php
 */

// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'infinityfree.me') !== false || 
                 strpos($hostname, 'infinityfree.com') !== false ||
                 strpos($hostname, 'infinityfreeapp.com') !== false);

try {
    if ($isProduction) {
        // ===== PRODUCTION: InfinityFree MySQL =====
        error_log("🌐 PRODUCTION ENVIRONMENT (InfinityFree)");
        
        $host = "sql202.infinityfree.com";
        $port = "3306";
        $dbname = "if0_40570875_jobportal"; // ⚠️ REPLACE WITH YOUR ACTUAL DATABASE NAME
        $username = "if0_40570875";
        $password = "i10cRqDoVjtsm";
        
        // MySQL connection for InfinityFree
        $conn = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_PERSISTENT => false,
            ]
        );
        
        error_log("✅ Connected to InfinityFree MySQL (Production)");
        
    } else {
        // ===== DEVELOPMENT: Localhost MySQL (XAMPP) =====
        error_log("🏠 DEVELOPMENT ENVIRONMENT (Localhost)");
        
        $host = "localhost";
        $dbname = "jobportal_db";
        $username = "root";
        $password = "";
        
        // MySQL connection for localhost
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        
        error_log("✅ Connected to MySQL (Development)");
    }
    
} catch(PDOException $e) {
    error_log("❌ Database Connection Error: " . $e->getMessage());
    
    // User-friendly error message
    if ($isProduction) {
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please contact support.'
        ]));
    } else {
        // Show detailed error in development
        echo "Connection failed: " . $e->getMessage();
        die();
    }
}
?>