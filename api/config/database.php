<?php
/**
 * API Database Configuration - Railway MySQL Compatible
 * UPDATED: Now uses MySQL instead of PostgreSQL (Supabase)
 * Works on Railway (production) and localhost (XAMPP)
 */

// Enhanced environment variable reading
function getEnvVar($key, $default = null) {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }
    
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    
    return $default;
}

// Detect environment
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'railway.app') !== false || 
                 strpos($hostname, 'up.railway.app') !== false);

try {
    if ($isProduction) {
        // ===== PRODUCTION: Railway MySQL =====
        error_log("🌐 API: PRODUCTION ENVIRONMENT DETECTED (Railway)");
        
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
        
        error_log("🔧 API: Connecting to Railway MySQL");
        error_log("🔧 API: Host=$host, Port=$port, DB=$dbname");
        
        // MySQL connection for Railway
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
        
        error_log("✅ API: Connected to Railway MySQL successfully!");
        
    } else {
        // ===== LOCAL DEVELOPMENT: Localhost MySQL (XAMPP) =====
        error_log("💻 API: LOCAL ENVIRONMENT DETECTED (XAMPP)");
        
        $host = "localhost";
        $port = "3306";
        $dbname = "jobportal_db";
        $username = "root";
        $password = "";
        
        error_log("🔧 API: Connecting to Local MySQL (XAMPP)");
        
        // MySQL connection for localhost
        $conn = new PDO(
            "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_TIMEOUT => 5,
            ]
        );
        
        error_log("✅ API: Connected to Local MySQL (XAMPP) successfully!");
    }
    
} catch(PDOException $e) {
    error_log("❌ API: DATABASE CONNECTION FAILED");
    error_log("❌ API: Error: " . $e->getMessage());
    error_log("❌ API: Environment: " . ($isProduction ? 'PRODUCTION (Railway)' : 'LOCAL (XAMPP)'));
    
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'environment' => $isProduction ? 'production' : 'local',
        ]
    ]));
}

/**
 * ApiDatabase Class - Helper methods for API operations
 */
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
            
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
            
            error_log("🔐 GENERATED TOKEN: " . substr($token, 0, 20) . "...");
            
            // Insert token (MySQL syntax)
            $stmt = $conn->prepare("INSERT INTO api_tokens (user_id, user_type, token, expires_at, is_active) VALUES (?, ?, ?, ?, 1)");
            $result = $stmt->execute([$userId, $userType, $token, $expiresAt]);
            
            if ($result) {
                error_log("🔐 TOKEN INSERTED SUCCESSFULLY");
                return $token;
            } else {
                error_log("🔐 TOKEN INSERT FAILED");
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
            
            // MySQL syntax - use NOW() instead of PostgreSQL's NOW()
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                   WHERE token = ? AND is_active = 1 
                                   AND expires_at > NOW()");
            $stmt->execute([$token]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("🔐 TOKEN VALID: user={$result['user_id']}, type={$result['user_type']}");
                
                // Update last_used timestamp
                $updateStmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = ?");
                $updateStmt->execute([$token]);
            } else {
                error_log("🔐 TOKEN INVALID OR EXPIRED");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN VALIDATION ERROR: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Extract authentication token from request headers
 */
function getAuthToken() {
    $headers = getallheaders();
    $token = null;
    
    // Try different header formats (case-insensitive)
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
    } elseif (isset($headers['X-API-Token'])) {
        $token = $headers['X-API-Token'];
    } elseif (isset($headers['x-api-token'])) {
        $token = $headers['x-api-token'];
    }
    
    return $token;
}

/**
 * Require authentication middleware
 */
function requireAuth() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['authorization'])) {
        $token = str_replace('Bearer ', '', $headers['authorization']);
    } elseif (isset($headers['X-API-Token'])) {
        $token = $headers['X-API-Token'];
    } elseif (isset($headers['x-api-token'])) {
        $token = $headers['x-api-token'];
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