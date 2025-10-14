<?php
/**
 * BULLETPROOF Database Wrapper - ULTRA SIMPLE VERSION
 * This WILL work - no complex queries, no complex logic
 */

// Include your existing database connection
require_once 'C:/xampp/htdocs/ThisAble/backend/db.php';

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
            $conn = self::getConnection();
            
            // Get token data
            $stmt = $conn->prepare("SELECT user_id, user_type FROM api_tokens 
                                WHERE token = ? 
                                AND is_active = 1 
                                AND expires_at > NOW()");
            $stmt->execute([$token]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tokenData) {
                return null;
            }
            
            $userId = $tokenData['user_id'];
            $userType = $tokenData['user_type'];
            
            // Get user info for candidates
            if ($userType === 'candidate') {
                $userStmt = $conn->prepare("
                    SELECT 
                        js.seeker_id,
                        js.first_name, 
                        js.last_name,
                        ua.email
                    FROM job_seekers js 
                    LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
                    WHERE js.seeker_id = ?
                ");
                $userStmt->execute([$userId]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData) {
                    // CRITICAL FIX: Ensure both user_id and seeker_id are set
                    $userData['user_id'] = $userData['seeker_id'];  // Set user_id = seeker_id
                    $userData['user_type'] = 'candidate';
                }
                
            } else {
                // For employers (future implementation)
                $userData = null;
            }
            
            if (!$userData) {
                return null;
            }
            
            // Update last_used timestamp
            $updateStmt = $conn->prepare("UPDATE api_tokens SET last_used = NOW() WHERE token = ?");
            $updateStmt->execute([$token]);
            
            return $userData;
            
        } catch (Exception $e) {
            error_log("Token validation error: " . $e->getMessage());
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