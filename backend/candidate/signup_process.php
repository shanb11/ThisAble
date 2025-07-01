<?php
ini_set('display_errors', 0); // Disable error display for production
ini_set('log_errors', 1); // Enable error logging
error_log("Starting signup process");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../../backend/db.php');

// Create a debug log function
function debug_log($message) {
    $logDir = '../../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    file_put_contents("$logDir/signup_debug.log", date('Y-m-d H:i:s') . " | " . $message . "\n", FILE_APPEND);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        debug_log("POST request received");
        // Get form data
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $disability = intval($_POST['disability'] ?? 0);
        $password = $_POST['password'] ?? '';
        $pwdIdNumber = trim($_POST['pwdIdNumber'] ?? '');
        $pwdIdIssuedDate = trim($_POST['pwdIdIssuedDate'] ?? '');
        $pwdIdIssuingLGU = trim($_POST['pwdIdIssuingLGU'] ?? '');
        $middleName = isset($_POST['middleName']) ? trim($_POST['middleName']) : null;
        $suffix = isset($_POST['suffix']) ? trim($_POST['suffix']) : null;
        
        debug_log("Form data received: " . json_encode([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'disability' => $disability,
            'pwdIdNumber' => $pwdIdNumber
        ]));
        
        // Check for file upload
        if (isset($_FILES['pwdIdFile'])) {
            debug_log("File upload found: " . json_encode($_FILES['pwdIdFile']));
        } else {
            debug_log("No file upload found in request");
        }
        
        // Begin transaction
        $conn->beginTransaction();
        debug_log("Transaction started");
        
        // Insert into job_seekers table
        $stmt = $conn->prepare("INSERT INTO job_seekers (first_name, middle_name, last_name, suffix, disability_id, contact_number) 
                               VALUES (:first_name, :middle_name, :last_name, :suffix, :disability_id, :contact_number)");
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':middle_name', $middleName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':suffix', $suffix);
        $stmt->bindParam(':disability_id', $disability);
        $stmt->bindParam(':contact_number', $phone);
        $stmt->execute();
        
        // Get the last inserted seeker_id
        $seekerId = $conn->lastInsertId();
        debug_log("New seeker_id created: $seekerId");
        
        // Handle file upload
        $imagePath = null;
        if (isset($_FILES['pwdIdFile']) && $_FILES['pwdIdFile']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/pwd_ids/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = pathinfo($_FILES['pwdIdFile']['name'], PATHINFO_EXTENSION);
            $newFileName = $seekerId . '_' . uniqid() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            debug_log("Attempting to move uploaded file to: $uploadPath");
            if (move_uploaded_file($_FILES['pwdIdFile']['tmp_name'], $uploadPath)) {
                $imagePath = $uploadPath;
                debug_log("File successfully moved to: $imagePath");
            } else {
                debug_log("Failed to move uploaded file. Upload error: " . $_FILES['pwdIdFile']['error']);
                debug_log("PHP last error: " . json_encode(error_get_last()));
            }
        }
        
        // Insert into pwd_ids table
        $stmt = $conn->prepare("INSERT INTO pwd_ids (seeker_id, pwd_id_number, issued_at, is_verified, id_image_path) 
                               VALUES (:seeker_id, :pwd_id_number, :issued_at, 0, :id_image_path)");
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':pwd_id_number', $pwdIdNumber);
        
        $issuedDate = !empty($pwdIdIssuedDate) ? $pwdIdIssuedDate : date('Y-m-d');
        $stmt->bindParam(':issued_at', $issuedDate);
        $stmt->bindParam(':id_image_path', $imagePath);
        $stmt->execute();
        debug_log("PWD ID inserted with image path: " . ($imagePath ? $imagePath : "NULL"));
        
        // Insert into user_accounts table
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user_accounts (seeker_id, email, password_hash) 
                               VALUES (:seeker_id, :email, :password_hash)");
        $stmt->bindParam(':seeker_id', $seekerId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        debug_log("Transaction committed successfully");
        
        // REMOVED: Session setup - User should login after registration
        // This ensures proper security flow and prevents auto-login
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful. Please log in with your credentials.',
            'verified' => false
        ]);
        
    } catch(Exception $e) {
        // Rollback the transaction if any error occurs
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        debug_log("Error occurred: " . $e->getMessage());
        debug_log("Error trace: " . $e->getTraceAsString());
        
        // Return error as JSON
        echo json_encode([
            'status' => 'error',
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
    }
} else {
    // Return error for non-POST requests
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>