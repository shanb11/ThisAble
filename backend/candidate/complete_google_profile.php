<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../../backend/db.php');

// Check if form is submitted and Google data exists
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['google_data'])) {
    // Get Google data from session
    $googleData = $_SESSION['google_data'];
    
    // Get form data
    $phone = trim($_POST['phone']);
    $disability = intval($_POST['disability']);
    $pwdIdNumber = trim($_POST['pwdIdNumber']);
    $pwdIdIssuedDate = trim($_POST['pwdIdIssuedDate'] ?? '');
    $pwdIdIssuingLGU = trim($_POST['pwdIdIssuingLGU'] ?? '');
    $verificationToken = trim($_POST['verificationToken'] ?? '');
    
    // Validate inputs
    if (empty($phone) || $disability <= 0 || empty($pwdIdNumber)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit;
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM user_accounts WHERE email = :email");
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }
        
        // Check if PWD ID already exists
        $stmt = $conn->prepare("SELECT pwd_id_number FROM pwd_ids WHERE pwd_id_number = :pwd_id_number");
        $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'PWD ID already registered']);
            exit;
        }
        
        // Determine verification status
        $verified = 0;
        // Check if verification token exists and is valid
        if (!empty($verificationToken) && isset($_SESSION['pwd_verification']) && 
            $_SESSION['pwd_verification']['token'] === $verificationToken && 
            $_SESSION['pwd_verification']['pwdIdNumber'] === $pwdIdNumber) {
            
            // Use verification status from token
            $verified = $_SESSION['pwd_verification']['verified'] ? 1 : 0;
        }
        
        // Insert into job_seekers table
        $stmt = $conn->prepare("INSERT INTO job_seekers (first_name, last_name, disability_id, contact_number) 
                               VALUES (:first_name, :last_name, :disability_id, :contact_number)");
        $stmt->bindParam(':first_name', $googleData['first_name']);
        $stmt->bindParam(':last_name', $googleData['last_name']);
        $stmt->bindParam(':disability_id', $disability);
        $stmt->bindParam(':contact_number', $phone);
        $stmt->execute();
        
        // Get the last inserted seeker_id
        $seekerId = $conn->lastInsertId();
        
        // Insert PWD ID with extended information
        $stmt = $conn->prepare("INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, is_verified, verification_status) 
                                VALUES (:seeker_id, :pwd_id_number, :issued_at, :is_verified, :verification_status)");
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
        $stmt->bindParam(':issued_at', $pwdIdIssuedDate);
        $stmt->bindParam(':is_verified', $verified);
        
        $verification_status = $verified ? 'verified' : 'pending';
        $stmt->bindParam(':verification_status', $verification_status);
        $stmt->execute();
        
        // Generate a random password (will not be used since login is via Google)
        $randomPassword = bin2hex(random_bytes(8));
        $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
        
        // Insert into user_accounts table with google_account flag
        $stmt = $conn->prepare("INSERT INTO user_accounts (seeker_id, email, password_hash, google_account) 
                               VALUES (:seeker_id, :email, :password_hash, 1)");
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        // Handle the PWD ID file upload
        if (isset($_FILES['pwdIdFile']) && $_FILES['pwdIdFile']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/pwd_ids/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate a unique filename
            $fileExt = pathinfo($_FILES['pwdIdFile']['name'], PATHINFO_EXTENSION);
            $newFileName = $seekerId . '_' . uniqid() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['pwdIdFile']['tmp_name'], $uploadPath)) {
                // Update the database with the file path
                $stmt = $conn->prepare("UPDATE pwd_ids SET id_image_path = :path WHERE seeker_id = :seeker_id");
                $stmt->bindParam(':path', $uploadPath);
                $stmt->bindParam(':seeker_id', $seekerId);
                $stmt->execute();
            }
        }
        
        // Set up session variables
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['seeker_id'] = $seekerId;
        $_SESSION['user_email'] = $googleData['email'];
        $_SESSION['user_name'] = $googleData['first_name'];
        $_SESSION['logged_in'] = true;
        
        // Clear Google data from session
        unset($_SESSION['google_data']);
        
        // Log the successful registration
        $logDir = '../../logs/verification';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " | Google Auth | Seeker ID: $seekerId | Email: " . 
                     $googleData['email'] . " | Registration completed | Verified: " . ($verified ? 'Yes' : 'No') . "\n";
        file_put_contents("$logDir/registration_log.txt", $logMessage, FILE_APPEND);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Profile completed successfully',
            'verified' => $verified
        ]);
        
    } catch(PDOException $e) {
        // Rollback the transaction if any error occurs
        $conn->rollBack();
        
        // Log the error
        error_log("Google profile completion error: " . $e->getMessage());
        
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit;
?>