<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * DOH PWD Verification Integration
 * 
 * This class handles verification of PWD IDs through the DOH website
 * using web scraping techniques.
 */
class DOHPwdVerifier
{
    private $verificationUrl = 'https://pwd.doh.gov.ph/tbl_pwd_id_verificationlist.php';
    private $timeout = 30; // Timeout in seconds
    
    /**
     * Verifies a PWD ID through the DOH website
     * 
     * @param string $pwdIdNumber The PWD ID number to verify
     * @param string $pwdIdIssuedDate The issue date of the PWD ID
     * @param string $pwdIdIssuingLGU The issuing LGU of the PWD ID
     * @return array Result of verification with status and message
     */
    public function verify($pwdIdNumber, $pwdIdIssuedDate, $pwdIdIssuingLGU)
    {
        // Format PWD ID to match DOH required format if needed
        $formattedId = $this->formatPwdId($pwdIdNumber);
        
        // Log the verification attempt
        $this->logVerificationAttempt($formattedId, 'ATTEMPT', 'Verification attempt started');
        
        try {
            // Initialize cURL session
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $this->verificationUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            // Set necessary headers to simulate a browser
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml',
                'Accept-Language: en-US,en;q=0.9',
                'Connection: keep-alive',
                'Origin: https://pwd.doh.gov.ph',
                'Referer: https://pwd.doh.gov.ph/tbl_pwd_id_verificationlist.php'
            ]);
            
            // Prepare post data (based on form structure)
            $postData = [
                'pwd_id' => $formattedId,
                'search' => 'Search'
            ];
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            
            // Execute cURL session
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close cURL session
            curl_close($ch);
            
            // Check for errors
            if ($error) {
                $this->logVerificationAttempt($formattedId, 'ERROR', 'cURL error: ' . $error);
                return $this->handleConnectionError($error);
            }
            
            // Check HTTP status code
            if ($httpCode != 200) {
                $this->logVerificationAttempt($formattedId, 'ERROR', 'HTTP error: ' . $httpCode);
                return $this->handleHttpError($httpCode);
            }
            
            // Process response
            return $this->processVerificationResponse($response, $formattedId);
            
        } catch (Exception $e) {
            $this->logVerificationAttempt($formattedId, 'ERROR', 'Exception: ' . $e->getMessage());
            return [
                'verified' => false,
                'status' => 'error',
                'message' => 'An error occurred during verification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Formats the PWD ID to ensure it matches the DOH required format
     * 
     * @param string $pwdIdNumber The PWD ID number to format
     * @return string Formatted PWD ID
     */
    private function formatPwdId($pwdIdNumber)
    {
        // Strip any spaces
        $pwdIdNumber = trim($pwdIdNumber);
        
        // Check if ID already has the correct format (RR-PPMM-BBB-NNNNNNN)
        if (preg_match('/^\d{2}-\d{4}-\d{3}-\d{7}$/', $pwdIdNumber)) {
            return $pwdIdNumber;
        }
        
        // If only 5 digits in the sequential part, add "00" prefix
        if (preg_match('/^(\d{2}-\d{4}-\d{3}-)(\d{5})$/', $pwdIdNumber, $matches)) {
            return $matches[1] . '00' . $matches[2];
        }
        
        // Return as is if we can't determine the format
        return $pwdIdNumber;
    }
    
    /**
     * Process the verification response from DOH
     * 
     * @param string $response The HTML response from the DOH website
     * @param string $pwdIdNumber The PWD ID number that was verified
     * @return array Result of verification with status and message
     */
    private function processVerificationResponse($response, $pwdIdNumber)
    {
        // Look for success indicators in the response
        if (strpos($response, 'PWD ID Status') !== false) {
            // Check if the ID is valid and not expired
            if (strpos($response, 'VALID') !== false) {
                $this->logVerificationAttempt($pwdIdNumber, 'SUCCESS', 'PWD ID verified successfully');
                return [
                    'verified' => true,
                    'status' => 'success',
                    'message' => 'PWD ID verified successfully through DOH database.'
                ];
            } else if (strpos($response, 'EXPIRED') !== false) {
                $this->logVerificationAttempt($pwdIdNumber, 'WARNING', 'PWD ID is expired');
                return [
                    'verified' => false,
                    'status' => 'warning',
                    'message' => 'PWD ID is expired. Please renew your PWD ID.'
                ];
            }
        }
        
        // Check for "no records found" message
        if (strpos($response, 'No records found') !== false) {
            $this->logVerificationAttempt($pwdIdNumber, 'FAILED', 'No records found');
            return [
                'verified' => false,
                'status' => 'error',
                'message' => 'PWD ID not found in DOH database. Please check your ID number or contact your issuing LGU.'
            ];
        }
        
        // If we can't determine the result, assume the service is unavailable
        $this->logVerificationAttempt($pwdIdNumber, 'WARNING', 'Unable to determine verification result');
        return [
            'verified' => false,
            'status' => 'service_unavailable',
            'message' => 'DOH verification service is currently unavailable or returned an unexpected response.'
        ];
    }
    
    /**
     * Handle connection errors
     * 
     * @param string $error The cURL error message
     * @return array Error result with status and message
     */
    private function handleConnectionError($error)
    {
        return [
            'verified' => false,
            'status' => 'service_unavailable',
            'message' => 'Connection error: Could not connect to DOH verification service.'
        ];
    }
    
    /**
     * Handle HTTP errors
     * 
     * @param int $httpCode The HTTP status code
     * @return array Error result with status and message
     */
    private function handleHttpError($httpCode)
    {
        if ($httpCode >= 500) {
            return [
                'verified' => false,
                'status' => 'service_unavailable',
                'message' => 'DOH verification service is currently unavailable (HTTP '.$httpCode.')'
            ];
        } else {
            return [
                'verified' => false,
                'status' => 'error',
                'message' => 'Error connecting to DOH verification service (HTTP '.$httpCode.')'
            ];
        }
    }
    
    /**
     * Log verification attempts
     * 
     * @param string $pwdIdNumber The PWD ID number
     * @param string $result The result status
     * @param string $details Additional details
     */
    private function logVerificationAttempt($pwdIdNumber, $result, $details = '')
    {
        // Create log directory if it doesn't exist
        $logDir = __DIR__ . '/../../logs/verification';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Get client IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // Create log message
        $logMessage = date('Y-m-d H:i:s') . " | IP: $ip | PWD ID: $pwdIdNumber | Result: $result | $details\n";
        
        // Write to log file
        file_put_contents("$logDir/doh_verification_log.txt", $logMessage, FILE_APPEND);
    }
    
    /**
     * Simple verification for testing purposes
     * This is used when the DOH website is not available for testing
     * 
     * @param string $pwdIdNumber The PWD ID number to verify
     * @return array Result of verification with status and message
     */
    public function testVerify($pwdIdNumber)
    {
        // For testing purposes - simulate verification result
        $pwdIdNumber = trim($pwdIdNumber);
        
        // Simulate specific test cases
        if ($pwdIdNumber === '123456' || $pwdIdNumber === '13-5416-000-0000001') {
            return [
                'verified' => true,
                'status' => 'success',
                'message' => 'PWD ID verified successfully (test mode)'
            ];
        } else if ($pwdIdNumber === '000000') {
            return [
                'verified' => false,
                'status' => 'service_unavailable',
                'message' => 'DOH verification service is currently unavailable (test mode)'
            ];
        } else if ($pwdIdNumber === '999999') {
            return [
                'verified' => false,
                'message' => 'Invalid PWD ID number (test mode)'
            ];
        }
        
        // Default test response - random 70% success rate
        $random = mt_rand(1, 100);
        if ($random <= 70) {
            return [
                'verified' => true,
                'status' => 'success',
                'message' => 'PWD ID verified successfully (test mode)'
            ];
        } else {
            return [
                'verified' => false,
                'message' => 'Could not verify PWD ID (test mode)'
            ];
        }
    }
}