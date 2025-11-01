<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../db.php');

// Google API configuration - Same as candidates
$clientID = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
$clientSecret = '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';
// Detect environment dynamically
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isProduction = (strpos($hostname, 'railway.app') !== false || 
                 strpos($hostname, 'up.railway.app') !== false);

if ($isProduction) {
    // Production Railway URL
    $redirectUri = 'https://thisable-production.up.railway.app/backend/candidate/google_auth.php';
} else {
    // Local development URL
    $redirectUri = 'http://localhost/ThisAble/backend/candidate/google_auth.php';
}
// Check if this is a callback from Google
if (isset($_GET['code'])) {
    // Handle Google OAuth callback
    $code = $_GET['code'];
    
    // Exchange the authorization code for an access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $data = [
        'code' => $code,
        'client_id' => $clientID,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($tokenUrl, false, $context);
    
    if ($result === FALSE) {
        // Handle error
        header('Location: ../../frontend/employer/emplogin.php?error=google_auth_failed');
        exit;
    }
    
    $token = json_decode($result, true);
    
    // Get user information with the access token
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $options = [
        'http' => [
            'header' => "Authorization: Bearer " . $token['access_token'] . "\r\n" .
                        "Accept: application/json\r\n"
        ]
    ];
    
    $context = stream_context_create($options);
    $userInfo = file_get_contents($userInfoUrl, false, $context);
    
    if ($userInfo === FALSE) {
        header('Location: ../../frontend/employer/emplogin.php?error=google_userinfo_failed');
        exit;
    }
    
    $userInfo = json_decode($userInfo, true);
    
    // Check if employer exists in database
    $stmt = $conn->prepare("SELECT ea.account_id, ea.employer_id, ea.contact_id, ec.first_name, e.company_name,
                                   esp.setup_complete
                           FROM employer_accounts ea 
                           JOIN employer_contacts ec ON ea.contact_id = ec.contact_id
                           JOIN employers e ON ea.employer_id = e.employer_id
                           LEFT JOIN employer_setup_progress esp ON ea.employer_id = esp.employer_id
                           WHERE ea.email = :email AND ea.google_account = 1");
    $stmt->bindParam(':email', $userInfo['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Employer exists - Log them in
        $employer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['employer_id'] = $employer['employer_id'];
        $_SESSION['account_id'] = $employer['account_id'];
        $_SESSION['contact_id'] = $employer['contact_id'];
        $_SESSION['employer_email'] = $userInfo['email'];
        $_SESSION['employer_name'] = $employer['first_name'];
        $_SESSION['company_name'] = $employer['company_name'];
        $_SESSION['logged_in'] = true;
        
        // Check setup status and redirect accordingly
        if ($employer['setup_complete']) {
            header('Location: ../../frontend/employer/empdashboard.php');
        } else {
            header('Location: ../../frontend/employer/empaccsetup.php');
        }
        exit;
    } else {
        // New employer - Store in session and redirect to setup
        $_SESSION['google_employer_data'] = [
            'email' => $userInfo['email'],
            'first_name' => $userInfo['given_name'] ?? '',
            'last_name' => $userInfo['family_name'] ?? '',
            'profile_picture' => $userInfo['picture'] ?? ''
        ];
        
        // Redirect to company setup page
        header('Location: ../../frontend/employer/empsignup.php?google_setup=1');
        exit;
    }
} else {
    // Redirect to Google OAuth
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientID,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        'access_type' => 'online',
        'prompt' => 'select_account'
    ]);
    
    header('Location: ' . $authUrl);
    exit;
}
?>