<?php
/**
 * Dynamic Database Connection
 * Auto-detects environment and connects to appropriate database
 * 
 * Localhost: MySQL (XAMPP)
 * Railway: Supabase PostgreSQL
 */

// Detect environment
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'railway.app') !== false || 
                 strpos($hostname, 'up.railway.app') !== false);

try {
    if ($isProduction) {
        // PRODUCTION: Railway with Supabase PostgreSQL
        // Railway automatically provides these environment variables if you linked a database
        // Or use your Supabase credentials
        
        $host = getenv('PGHOST') ?: getenv('DB_HOST') ?: 'db.jxllnfnzossijeidzhrq.supabase.co';
        $port = getenv('PGPORT') ?: getenv('DB_PORT') ?: '5432';
        $dbname = getenv('PGDATABASE') ?: getenv('DB_NAME') ?: 'postgres';
        $username = getenv('PGUSER') ?: getenv('DB_USER') ?: 'postgres';
        $password = getenv('PGPASSWORD') ?: getenv('DB_PASSWORD') ?: '082220EthanDrake';
        
        // PostgreSQL connection with SSL for Supabase
        $conn = new PDO(
            "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", 
            $username, 
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        error_log("✅ Connected to Supabase PostgreSQL (Production)");
        
    } else {
        // DEVELOPMENT: Localhost MySQL (XAMPP)
        $host = "localhost";
        $dbname = "jobportal_db";
        $username = "root";
        $password = "";
        
        // MySQL connection
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname", 
            $username, 
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
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