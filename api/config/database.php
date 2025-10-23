<?php
/**
 * API Database Configuration - Railway + Local Compatible
 * Works on Railway, Vercel, and localhost (XAMPP)
 */

// Enhanced environment variable reading for Railway
function getEnvVar($key, $default = null) {
    // Try $_ENV first (Railway)
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    
    // Try getenv() (some hosting)
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    
    // Try $_SERVER (alternative)
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    
    // Return default
    return $default;
}

// Database credentials with Railway support
$host = getEnvVar('DB_HOST', 'db.jxllnfnzossijeidzhrq.supabase.co');
$port = getEnvVar('DB_PORT', '5432');
$dbname = getEnvVar('DB_NAME', 'postgres');
$username = getEnvVar('DB_USER', 'postgres');
$password = getEnvVar('DB_PASSWORD', '082220EthanDrake');

// Debug logging (only log in production if needed)
error_log("🔧 Attempting DB connection to: $host:$port/$dbname as $username");

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5, // 5 second timeout
        ]
    );
    
    error_log("✅ Database connected successfully");
    
} catch(PDOException $e) {
    error_log("❌ Database Connection Error: " . $e->getMessage());
    error_log("❌ Connection string: pgsql:host=$host;port=$port;dbname=$dbname");
    
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'host' => $host,
            'port' => $port,
            'database' => $dbname
        ]
    ]));
}

class ApiDatabase {
    
    private static $conn;
    
    /**
     * Get database connection
     */
    public static function getConnection() {
        global $conn;
        self::$conn = $conn;
        return self::$conn;
    }
    
    /**
     * Generate secure API token
     */
    public static function generateApiToken($userId, $userType) {
        try {
            error_log("🔐 GENERATING TOKEN: user=$userId, type=$userType");
            
            $conn = self::getConnection();
            
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
            
            error_log("🔐 GENERATED TOKEN: " . substr($token, 0, 20) . "...");
            
            // Insert token
            $stmt = $conn->prepare("INSERT INTO api_tokens (user_id, user_type, token, expires_at, is_active) VALUES (?, ?, ?, ?, true)");
            $result = $stmt->execute([$userId, $userType, $token, $expiresAt]);
            
            if ($result) {
                error_log("🔐 TOKEN INSERTED SUCCESSFULLY");
                return $token;
            } else {
                error_log("🔐 TOKEN INSERT FAILED: " . json_encode($stmt->errorInfo()));
                return false;
            }
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN GENERATION ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate API token
     */
    public static function validateToken($token) {
        try {
            error_log("🔐 VALIDATING TOKEN: " . substr($token, 0, 20) . "...");
            
            $conn = self::getConnection();
            
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                   WHERE token = ? AND is_active = true 
                                   AND expires_at > NOW()");
            $stmt->execute([$token]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN VALIDATION ERROR: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Require authentication middleware
 */
function requireAuth() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['X-API-Token'])) {
        $token = $headers['X-API-Token'];
    }
    
    if (!$token) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Authentication required']));
    }
    
    $user = ApiDatabase::validateToken($token);
    
    if (!$user) {
        http_response_code(401);
        die(json_encode(['success' => false, 'message' => 'Invalid or expired token']));
    }
    
    return $user;
}
?>