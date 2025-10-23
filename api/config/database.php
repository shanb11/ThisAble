<?php
/**
 * API Database Configuration - Railway + Supabase Compatible
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

// Detect environment
$isRailway = !empty($_ENV['RAILWAY_ENVIRONMENT']) || !empty(getenv('RAILWAY_ENVIRONMENT'));
$isVercel = !empty($_ENV['VERCEL']) || !empty(getenv('VERCEL'));
$isCloudEnvironment = $isRailway || $isVercel;

// Database credentials
$dbname = getEnvVar('DB_NAME', 'postgres');
$password = getEnvVar('DB_PASSWORD', '082220EthanDrake');

// Connection configuration based on environment
if ($isCloudEnvironment) {
    // ===== CLOUD DEPLOYMENT: Use Supabase Connection Pooler =====
    $host = 'aws-0-ap-southeast-1.pooler.supabase.com';
    $port = '6543'; // Transaction mode port
    $username = 'postgres.jxllnfnzossijeidzhrq'; // Note: postgres. prefix for pooler
    
    error_log("🌐 Cloud environment detected (Railway/Vercel)");
    error_log("🔧 Using Supabase pooler: $host:$port");
    
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_PERSISTENT => false, // Don't use persistent connections with pooler
    ];
    
} else {
    // ===== LOCAL DEVELOPMENT: Direct connection =====
    $host = getEnvVar('DB_HOST', 'db.jxllnfnzossijeidzhrq.supabase.co');
    $port = getEnvVar('DB_PORT', '5432');
    $username = getEnvVar('DB_USER', 'postgres');
    
    error_log("💻 Local environment detected");
    error_log("🔧 Using direct connection: $host:$port");
    
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
    
    error_log("🔧 Connecting to: $dsn as $username");
    
    $conn = new PDO($dsn, $username, $password, $pdoOptions);
    
    error_log("✅ Database connected successfully!");
    
} catch(PDOException $e) {
    error_log("❌ Database Connection Error: " . $e->getMessage());
    error_log("❌ DSN: $dsn");
    error_log("❌ Username: $username");
    
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'environment' => $isCloudEnvironment ? 'cloud' : 'local',
            'host' => $host,
            'port' => $port,
        ]
    ]));
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
            error_log("🔐 GENERATING TOKEN: user=$userId, type=$userType");
            
            $conn = self::getConnection();
            
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
            
            $stmt = $conn->prepare("INSERT INTO api_tokens (user_id, user_type, token, expires_at, is_active) VALUES (?, ?, ?, ?, true)");
            $result = $stmt->execute([$userId, $userType, $token, $expiresAt]);
            
            if ($result) {
                error_log("🔐 TOKEN INSERTED SUCCESSFULLY");
                return $token;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN GENERATION ERROR: " . $e->getMessage());
            return false;
        }
    }
    
    public static function validateToken($token) {
        try {
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