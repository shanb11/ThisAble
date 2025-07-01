<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');

// Set content type for JSON response
header('Content-Type: application/json');

// Check if form is submitted and Google data exists
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['google_employer_data'])) {
    // Get Google data from session
    $googleData = $_SESSION['google_employer_data'];
    
    // Get form data
    $companyName = trim($_POST['company_name']);
    $industryId = intval($_POST['industry_id']);
    $companyAddress = trim($_POST['company_address']);
    $companySize = trim($_POST['company_size'] ?? '');
    $companyWebsite = trim($_POST['company_website'] ?? '');
    $companyDescription = trim($_POST['company_description'] ?? '');
    $position = trim($_POST['position']);
    $contactNumber = trim($_POST['contact_number']);
    
    // Validate inputs
    if (empty($companyName) || $industryId <= 0 || empty($companyAddress) || 
        empty($position) || empty($contactNumber)) {
        echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled']);
        exit;
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT email FROM employer_accounts WHERE email = :email");
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit;
        }
        
        // 1. Insert into employers table
        $stmt = $conn->prepare("INSERT INTO employers (company_name, industry, industry_id, company_address, 
                                                      company_size, company_website, company_description, 
                                                      verification_status) 
                               VALUES (:company_name, :industry, :industry_id, :company_address, 
                                      :company_size, :company_website, :company_description, 'verified')");
        
        // Get industry name for backward compatibility
        $industryStmt = $conn->prepare("SELECT industry_name FROM industries WHERE industry_id = :industry_id");
        $industryStmt->bindParam(':industry_id', $industryId);
        $industryStmt->execute();
        $industryName = $industryStmt->fetchColumn();
        
        $stmt->bindParam(':company_name', $companyName);
        $stmt->bindParam(':industry', $industryName);
        $stmt->bindParam(':industry_id', $industryId);
        $stmt->bindParam(':company_address', $companyAddress);
        $stmt->bindParam(':company_size', $companySize);
        $stmt->bindParam(':company_website', $companyWebsite);
        $stmt->bindParam(':company_description', $companyDescription);
        $stmt->execute();
        
        $employerId = $conn->lastInsertId();
        
        // 2. Insert into employer_contacts table
        $stmt = $conn->prepare("INSERT INTO employer_contacts (employer_id, first_name, last_name, position, 
                                                              contact_number, email, is_primary) 
                               VALUES (:employer_id, :first_name, :last_name, :position, 
                                      :contact_number, :email, 1)");
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->bindParam(':first_name', $googleData['first_name']);
        $stmt->bindParam(':last_name', $googleData['last_name']);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':contact_number', $contactNumber);
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->execute();
        
        $contactId = $conn->lastInsertId();
        
        // 3. Generate a random password (will not be used since login is via Google)
        $randomPassword = bin2hex(random_bytes(8));
        $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
        
        // 4. Insert into employer_accounts table with google_account flag
        $stmt = $conn->prepare("INSERT INTO employer_accounts (employer_id, contact_id, email, password_hash, 
                                                              google_account, email_verified) 
                               VALUES (:employer_id, :contact_id, :email, :password_hash, 1, 1)");
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->bindParam(':contact_id', $contactId);
        $stmt->bindParam(':email', $googleData['email']);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->execute();
        
        $accountId = $conn->lastInsertId();
        
        // 5. Set up initial employer setup progress (NOT COMPLETE - they need to do setup later)
        $stmt = $conn->prepare("INSERT INTO employer_setup_progress 
                               (employer_id, basic_info_complete, company_description_complete, 
                                hiring_preferences_complete, social_links_complete, logo_uploaded,
                                setup_complete, completion_percentage) 
                               VALUES (:employer_id, 1, 0, 0, 0, 0, 0, 20)");
        $stmt->bindParam(':employer_id', $employerId);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        // DON'T set session variables here - they need to login again
        
        // Clear Google data from session
        unset($_SESSION['google_employer_data']);
        
        // Log the successful registration
        $logDir = '../../logs/verification';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logMessage = date('Y-m-d H:i:s') . " | Google Auth Employer | Employer ID: $employerId | Email: " . 
                     $googleData['email'] . " | Company: $companyName | Registration completed (Setup incomplete)\n";
        file_put_contents("$logDir/employer_registration_log.txt", $logMessage, FILE_APPEND);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Registration completed successfully! Please login to continue.',
            'redirect' => '../../frontend/employer/emplogin.php?registered=1'
        ]);
        
    } catch(PDOException $e) {
        // Rollback the transaction if any error occurs
        $conn->rollBack();
        
        // Log the error
        error_log("Google employer setup error: " . $e->getMessage());
        
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit;
?>