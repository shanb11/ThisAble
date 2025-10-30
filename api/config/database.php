<?php
/**
 * API Database Configuration - Railway + Supabase Compatible
 * Uses Supabase Connection Pooler for cloud deployments
 * Works on Railway, Vercel, and localhost (XAMPP)
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

// Detect environment - Railway sets RAILWAY_ENVIRONMENT
$isRailway = !empty($_ENV['RAILWAY_ENVIRONMENT']) || 
             !empty(getenv('RAILWAY_ENVIRONMENT')) ||
             !empty($_SERVER['RAILWAY_ENVIRONMENT'] ?? '');

$isVercel = !empty($_ENV['VERCEL']) || !empty(getenv('VERCEL'));
$isCloudEnvironment = $isRailway || $isVercel;

// Database credentials
$dbname = getEnvVar('DB_NAME', 'postgres');
$password = getEnvVar('DB_PASSWORD', '082220EthanDrake');

// Connection configuration based on environment
if ($isCloudEnvironment) {
    // ===== CLOUD DEPLOYMENT: Try pooler first, fallback to direct =====
    
    // OPTION 1: Supabase Connection Pooler (Supavisor) - IPv4 Compatible
    // CORRECT configuration verified from Supabase dashboard
    $poolerHost = 'aws-1-ap-southeast-1.pooler.supabase.com'; // CORRECTED: aws-1 not aws-0!
    $poolerPortSession = '5432'; // Session mode - behaves like direct connection
    $poolerPortTransaction = '6543'; // Transaction mode - for serverless
    $poolerUsername = 'postgres.jxllnfnzossijeidzhrq'; // Verified from dashboard
    
    // Try session mode by default (more compatible)
    $useSessionMode = getEnvVar('USE_SESSION_MODE', 'true') === 'true';
    $poolerPort = $useSessionMode ? $poolerPortSession : $poolerPortTransaction;
    
    // OPTION 2: Direct connection with IPv4 forced (temporary fallback)
    $directHost = 'db.jxllnfnzossijeidzhrq.supabase.co';
    $directPort = '5432';
    $directUsername = 'postgres';
    
    // Use pooler (NOW CORRECT - verified from Supabase dashboard)
    $usePooler = getEnvVar('USE_POOLER', 'true') === 'true'; // Re-enabled with correct host!
    
    if ($usePooler) {
        $host = $poolerHost;
        $port = $poolerPort;
        $username = $poolerUsername;
        $mode = $useSessionMode ? 'SESSION' : 'TRANSACTION';
        error_log("🌐 CLOUD: Attempting Supabase Connection Pooler ($mode mode)");
    } else {
        $host = $directHost;
        $port = $directPort;
        $username = $directUsername;
        error_log("🌐 CLOUD: Attempting Direct Connection (IPv4)");
    }
    
    error_log("🔧 Host: $host:$port");
    error_log("🔧 Username: $username");
    
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_PERSISTENT => false,
    ];
    
} else {
    // ===== LOCAL DEVELOPMENT: Direct connection =====
    $host = getEnvVar('DB_HOST', 'db.jxllnfnzossijeidzhrq.supabase.co');
    $port = getEnvVar('DB_PORT', '5432');
    $username = getEnvVar('DB_USER', 'postgres');
    
    error_log("💻 LOCAL ENVIRONMENT DETECTED");
    error_log("🔧 Using Direct Connection");
    error_log("🔧 Host: $host:$port");
    
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];
}

// Attempt connection
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    error_log("🔧 DSN: $dsn");
    error_log("🔧 Username: $username");
    
    $conn = new PDO($dsn, $username, $password, $pdoOptions);
    
    error_log("✅ DATABASE CONNECTED SUCCESSFULLY!");
    
} catch(PDOException $e) {
    error_log("❌ DATABASE CONNECTION FAILED");
    error_log("❌ Error: " . $e->getMessage());
    error_log("❌ DSN: $dsn");
    error_log("❌ Username: $username");
    error_log("❌ Environment: " . ($isCloudEnvironment ? 'CLOUD' : 'LOCAL'));
    
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'environment' => $isCloudEnvironment ? 'cloud' : 'local',
            'host' => $host,
            'port' => $port,
            'using_pooler' => $isCloudEnvironment
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
            
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                   WHERE token = ? AND is_active = true 
                                   AND expires_at > NOW()");
            $stmt->execute([$token]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("🔐 TOKEN VALID: user={$result['user_id']}, type={$result['user_type']}");
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
 * This is a helper function used for debugging and logging
 */
function getAuthToken() {
    $headers = getallheaders();
    $token = null;
    
    // Try different header formats (case-insensitive for web compatibility)
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
    } elseif (isset($headers['authorization'])) { // lowercase variant for some web servers
        $token = str_replace('Bearer ', '', $headers['authorization']);
    } elseif (isset($headers['X-API-Token'])) {
        $token = $headers['X-API-Token'];
    } elseif (isset($headers['x-api-token'])) { // lowercase variant
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