<?php
/**
 * API Database Configuration - WORKS WITH LOCAL MYSQL
 * File: C:\xampp\htdocs\ThisAble\api\config\database.php
 */

$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'infinityfree.me') !== false);

try {
    if ($isProduction) {
        // Production - InfinityFree MySQL
        $host = "sql202.infinityfree.com";
        $port = "3306";
        $dbname = "if0_40570875_jobportal";
        $username = "if0_40570875";
        $password = "i10cRqDoVjtsm";
        
        $conn = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );
        
        error_log("✅ Connected to InfinityFree MySQL");
        
    } else {
        // Local - XAMPP MySQL
        $host = "localhost";
        $dbname = "jobportal_db";
        $username = "root";
        $password = "";
        
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );
        
        error_log("✅ Connected to Local MySQL (XAMPP)");
    }
    
} catch(PDOException $e) {
    error_log("❌ Database Error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

class ApiDatabase {
    private static $conn;
    
    public static function getConnection() {
        global $conn;
        self::$conn = $conn;
        return self::$conn;
    }
    
    public static function generateApiToken($userId, $userType) {
        try {
            $conn = self::getConnection();
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
            
            $stmt = $conn->prepare(
                "INSERT INTO api_tokens (user_id, user_type, token, expires_at, is_active)
                 VALUES (?, ?, ?, ?, 1)"
            );
            $stmt->execute([$userId, $userType, $token, $expiresAt]);
            return $token;
        } catch (Exception $e) {
            error_log("Token generation error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function validateToken($token) {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare(
                "SELECT user_id, user_type FROM api_tokens
                 WHERE token = ? AND is_active = 1 AND expires_at > NOW()"
            );
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function getUserByToken($token) {
        return self::validateToken($token);
    }
    
    public static function logActivity($action, $data = []) {
        try {
            $conn = self::getConnection();
            $userId = $data['user_id'] ?? null;
            $email = $data['email'] ?? null;
            $logData = json_encode($data);
            
            $stmt = $conn->prepare(
                "INSERT INTO api_activity_logs (action, user_id, email, data, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$action, $userId, $email, $logData]);
        } catch (Exception $e) {
            // Non-fatal
        }
    }
}

function getAuthToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) return str_replace('Bearer ', '', $headers['Authorization']);
    if (isset($headers['authorization'])) return str_replace('Bearer ', '', $headers['authorization']);
    if (isset($headers['X-API-Token'])) return $headers['X-API-Token'];
    if (isset($headers['x-api-token'])) return $headers['x-api-token'];
    return null;
}

function requireAuth() {
    $token = getAuthToken();
    if (!$token) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Authentication required']));
    }
    $user = ApiDatabase::validateToken($token);
    if (!$user) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Invalid token']));
    }
    return $user;
}
?>