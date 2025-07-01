<?php
/**
 * API Response Helper for ThisAble Mobile API
 * Standardizes all API responses in consistent JSON format
 */

class ApiResponse {
    
    /**
     * Send success response
     * @param mixed $data The data to return
     * @param string $message Success message
     * @param int $code HTTP status code
     */
    public static function success($data = null, $message = "Success", $code = 200) {
        http_response_code($code);
        
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'status_code' => $code
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send error response
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error details
     */
    public static function error($message = "Error occurred", $code = 400, $errors = null) {
        http_response_code($code);
        
        $response = [
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s'),
            'status_code' => $code
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send validation error response
     * @param array $errors Validation errors
     * @param string $message Error message
     */
    public static function validationError($errors, $message = "Validation failed") {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send unauthorized response
     * @param string $message Error message
     */
    public static function unauthorized($message = "Unauthorized access") {
        self::error($message, 401);
    }
    
    /**
     * Send not found response
     * @param string $message Error message
     */
    public static function notFound($message = "Resource not found") {
        self::error($message, 404);
    }
    
    /**
     * Send server error response
     * @param string $message Error message
     */
    public static function serverError($message = "Internal server error") {
        self::error($message, 500);
    }
    
    /**
     * Log API activity
     * @param string $action Action performed
     * @param array $data Additional data to log
     */
    public static function logActivity($action, $data = []) {
        $logDir = '../logs/api';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        $logMessage = json_encode($logData) . "\n";
        file_put_contents("$logDir/api_activity.log", $logMessage, FILE_APPEND);
    }
}
?>