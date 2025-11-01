<?php
/**
 * Dynamic Database Connection - Updated for Railway MySQL
 * Auto-detects environment and connects to appropriate database
 * 
 * Localhost: MySQL (XAMPP)
 * Railway: MySQL (Railway Database)
 */

// Detect environment
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'railway.app') !== false || 
                 strpos($hostname, 'up.railway.app') !== false);

try {
    if ($isProduction) {
        // PRODUCTION: Railway MySQL
        
        // Try to get from MYSQL_URL first (Railway's standard variable)
        $mysql_url = getenv('MYSQL_URL');
        
        if ($mysql_url) {
            // Parse the MYSQL_URL
            $parsed = parse_url($mysql_url);
            $host = $parsed['host'] ?? 'localhost';
            $port = $parsed['port'] ?? '3306';
            $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'railway';
            $username = $parsed['user'] ?? 'root';
            $password = $parsed['pass'] ?? '';
        } else {
            // Fallback to individual environment variables
            $host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: 'localhost';
            $port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
            $dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
            $username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
            $password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
        }
        
        // MySQL connection for Railway
        $conn = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        );
        
        error_log("✅ Connected to Railway MySQL (Production)");
        
    } else {
        // DEVELOPMENT: Localhost MySQL (XAMPP)
        $host = "localhost";
        $dbname = "jobportal_db";
        $username = "root";
        $password = "";
        
        // MySQL connection
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
            'message' => 'Database connection failed. Please try again later.'
        ]));
    } else {
        // Show detailed error in development
        echo "Connection failed: " . $e->getMessage();
        die();
    }
}
?>