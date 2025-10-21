<?php
/**
 * Database Connection - Now using Supabase PostgreSQL
 * Backup of original MySQL connection: db_mysql_backup.php
 */

// Supabase PostgreSQL credentials
$host = "db.jxllnfnzossijeidzhrq.supabase.co";
$port = "5432";
$dbname = "postgres";
$username = "postgres";
$password = "082220EthanDrake";

try {
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
    
    // Optional: Log successful connection (remove in production)
    // error_log("✅ Connected to Supabase PostgreSQL at " . date('Y-m-d H:i:s'));
    
} catch(PDOException $e) {
    error_log("❌ Supabase Connection Error: " . $e->getMessage());
    die("Database connection failed. Check error logs.");
}
?>
