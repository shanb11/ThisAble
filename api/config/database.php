<?php
/**
 * API Database Configuration - InfinityFree MySQL Compatible
 * UPDATED: Now detects InfinityFree instead of Railway
 * Works on InfinityFree (production) and localhost (XAMPP)
 * 
 * This is for the MOBILE API endpoints in /api folder
 */

// Enhanced environment variable reading (kept for compatibility)
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

// Detect environment based on hostname
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'infinityfree.me') !== false || 
                 strpos($hostname, 'infinityfree.com') !== false ||
                 strpos($hostname, 'infinityfreeapp.com') !== false);

try {
    if ($isProduction) {
        // ===== PRODUCTION: InfinityFree MySQL =====
        error_log("🌐 API: PRODUCTION ENVIRONMENT DETECTED (InfinityFree)");
        
        $host = "sql202.infinityfree.com";
        $port = "3306";
        $dbname = "if0_40570875_jobportal";
        $username = "if0_40570875";
        $password = "i10cRqDoVjtsm";
        
        error_log("🔧 API: Connecting to InfinityFree MySQL");
        error_log("🔧 API: Host=$host, Port=$port, DB=$dbname");
        
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
        
        error_log("✅ API: Connected to InfinityFree MySQL successfully!");
        
    } else {
        // ===== DEVELOPMENT: Localhost MySQL (XAMPP) =====
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
    error_log("❌ API: Environment: " . ($isProduction ? 'PRODUCTION (InfinityFree)' : 'LOCAL (XAMPP)'));
    
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
            
            // MySQL syntax
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                   WHERE token = ? 
                                   AND is_active = 1 
                                   AND expires_at > NOW()");
            $stmt->execute([$token]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("🔐 TOKEN VALID");
                
                // Update last_used timestamp
                $updateStmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = ?");
                $updateStmt->execute([$token]);
                
                return [
                    'valid' => true,
                    'user_id' => $result['user_id'],
                    'user_type' => $result['user_type']
                ];
            }
            
            error_log("🔐 TOKEN INVALID");
            return ['valid' => false];
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN VALIDATION ERROR: " . $e->getMessage());
            return ['valid' => false];
        }
    }
}
?>