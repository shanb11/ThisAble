<?php
/**
 * API Database Configuration - Standalone for Vercel deployment
 * Works on both localhost and Vercel
 */

// Database credentials (works on both environments)
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
    error_log("❌ API Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.'
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
            
            // Insert token (don't deactivate old ones for now)
            $stmt = $conn->prepare("INSERT INTO api_tokens (user_id, user_type, token, expires_at, is_active) VALUES (?, ?, ?, ?, 1)");
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
     * ULTRA SIMPLE TOKEN VALIDATION - NO COMPLEX QUERIES
     */
    public static function validateToken($token) {
        try {
            error_log("🔐 VALIDATING TOKEN: " . substr($token, 0, 20) . "...");
            
            $conn = self::getConnection();
            
            // Step 1: Check token in api_tokens table
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                   WHERE token = ? 
                                   AND is_active = 1 
                                   AND expires_at > NOW()");
            $stmt->execute([$token]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tokenData) {
                error_log("🔐 TOKEN NOT FOUND OR EXPIRED");
                return null;
            }
            
            $userId = $tokenData['user_id'];
            $userType = $tokenData['user_type'];
            
            error_log("🔐 TOKEN VALID: user_id=$userId, type=$userType");
            
            // Step 2: Get user info with SIMPLE queries (no JOINs)
            if ($userType === 'candidate') {
                $userStmt = $conn->prepare("SELECT seeker_id as user_id, first_name, last_name FROM job_seekers WHERE seeker_id = ?");
                $userStmt->execute([$userId]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData) {
                    $userData['user_type'] = 'candidate';
                    $userData['email'] = ''; // Will get from user_accounts if needed
                }
                
            } else {
                error_log("🔐 EMPLOYER NOT IMPLEMENTED YET");
                return null;
            }
            
            if (!$userData) {
                error_log("🔐 USER NOT FOUND IN job_seekers: user_id=$userId");
                return null;
            }
            
            error_log("🔐 USER FOUND: " . json_encode($userData));
            
            // Step 3: Update last_used
            $updateStmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = ?");
            $updateStmt->execute([$token]);
            
            error_log("🔐 TOKEN VALIDATION SUCCESS");
            return $userData;
            
        } catch (Exception $e) {
            error_log("🔐 TOKEN VALIDATION ERROR: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by token (simple wrapper)
     */
    public static function getUserByToken($token) {
        return self::validateToken($token);
    }
}

/**
 * ULTRA SIMPLE TOKEN EXTRACTION
 */
function getAuthToken() {
    error_log("🔐 EXTRACTING TOKEN...");
    
    // Method 1: Check Authorization header
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        error_log("🔐 FOUND HTTP_AUTHORIZATION: " . substr($authHeader, 0, 30) . "...");
        
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            error_log("🔐 EXTRACTED TOKEN: " . substr($token, 0, 20) . "...");
            return trim($token);
        }
    }
    
    // Method 2: Try getallheaders
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                error_log("🔐 FOUND Authorization in getallheaders: " . substr($value, 0, 30) . "...");
                if (strpos($value, 'Bearer ') === 0) {
                    $token = substr($value, 7);
                    error_log("🔐 EXTRACTED TOKEN: " . substr($token, 0, 20) . "...");
                    return trim($token);
                }
            }
        }
    }
    
    error_log("🔐 NO TOKEN FOUND");
    return null;
}

/**
 * REQUIRE AUTHENTICATION - SIMPLE VERSION
 */
function requireAuth() {
    error_log("🔐 REQUIRE AUTH CALLED");
    
    $token = getAuthToken();
    
    if (!$token) {
        error_log("🔐 NO TOKEN - UNAUTHORIZED");
        ApiResponse::unauthorized("Authentication token required");
    }
    
    $user = ApiDatabase::getUserByToken($token);
    
    if (!$user) {
        error_log("🔐 INVALID TOKEN - UNAUTHORIZED");
        ApiResponse::unauthorized("Invalid or expired token");
    }
    
    error_log("🔐 AUTH SUCCESS: " . json_encode($user));
    return $user;
}
?>