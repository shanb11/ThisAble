<?php
/**
 * Database Connection - Supabase PostgreSQL
 * Works on both localhost (XAMPP) and Vercel
 */

// Get credentials from environment variables (Vercel) or use defaults (localhost)
$host = getenv('DB_HOST') ?: 'db.jxllnfnzossijeidzhrq.supabase.co';
$port = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'postgres';
$username = getenv('DB_USER') ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: '082220EthanDrake';

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
    
} catch(PDOException $e) {
    error_log("❌ Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.'
    ]));
}
?>
