<?php
// Start session at the beginning of all pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get form data
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $rememberMe = $input['remember_me'] ?? false;
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }
    
    try {
        // Fetch employer from database
        $stmt = $conn->prepare("SELECT ea.account_id, ea.employer_id, ea.contact_id, ea.email, ea.password_hash, 
                                      ea.google_account, ec.first_name, ec.last_name, e.company_name,
                                      esp.setup_complete
                               FROM employer_accounts ea 
                               JOIN employer_contacts ec ON ea.contact_id = ec.contact_id
                               JOIN employers e ON ea.employer_id = e.employer_id
                               LEFT JOIN employer_setup_progress esp ON ea.employer_id = esp.employer_id
                               WHERE ea.email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $employer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if this is a Google account trying to login with password
            if ($employer['google_account']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This account was created with Google. Please use "Continue with Google" to login.'
                ]);
                exit;
            }
            
            // Verify password for regular accounts
            if (password_verify($password, $employer['password_hash'])) {
                // Set session variables
                $_SESSION['employer_id'] = $employer['employer_id'];
                $_SESSION['account_id'] = $employer['account_id'];
                $_SESSION['contact_id'] = $employer['contact_id'];
                $_SESSION['employer_email'] = $employer['email'];
                $_SESSION['employer_name'] = $employer['first_name'];
                $_SESSION['company_name'] = $employer['company_name'];
                $_SESSION['logged_in'] = true;
                
                // Update last login
                $updateStmt = $conn->prepare("UPDATE employer_accounts SET last_login = CURRENT_TIMESTAMP WHERE account_id = :account_id");
                $updateStmt->bindParam(':account_id', $employer['account_id']);
                $updateStmt->execute();
                
                // Set remember me cookie if requested
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(16));
                    setcookie('employer_remember_token', $token, time() + (86400 * 30), '/'); // 30 days
                    
                    // Store token in database (you might want to create a remember_tokens table)
                    // For now, we'll skip this implementation
                }
                
                // Check setup status
                $setupComplete = $employer['setup_complete'] ?? false;
                
                // Prepare response data
                $responseData = [
                    'employer_id' => $employer['employer_id'],
                    'employer_name' => $employer['first_name'],
                    'company_name' => $employer['company_name'],
                    'setup_info' => [
                        'setup_complete' => (bool)$setupComplete
                    ]
                ];
                
                // Determine redirect URL
                $redirectUrl = $setupComplete ? 'empdashboard.php' : 'empaccsetup.php';
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful',
                    'data' => $responseData,
                    'redirect_url' => $redirectUrl
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Employer login error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'A system error occurred. Please try again later.'
        ]);
    }
    
    exit;
}

// If not POST request
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);
?>