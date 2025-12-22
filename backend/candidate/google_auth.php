<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../../backend/db.php');

// Google API configuration
$clientID = '83628564105-ebo9ng5modqfhkgepbm55rkv92d669l9.apps.googleusercontent.com';
$clientSecret = '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';

// ✅ FIXED: Detect environment and set BASE_URL
$hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (strpos($hostname, 'localhost') !== false) {
    $BASE_URL = '/ThisAble/';
} elseif (strpos($hostname, 'infinityfree.me') !== false) {
    $BASE_URL = '/';
} elseif (strpos($hostname, 'railway.app') !== false) {
    $BASE_URL = '/';
} else {
    $BASE_URL = '/';
}

// Set redirect URI based on environment
$isProduction = (strpos($hostname, 'railway.app') !== false || 
                 strpos($hostname, 'up.railway.app') !== false ||
                 strpos($hostname, 'infinityfree.me') !== false);

if ($isProduction) {
    // Production URLs
    if (strpos($hostname, 'infinityfree.me') !== false) {
        $redirectUri = 'https://' . $hostname . '/backend/candidate/google_auth.php';
    } else {
        $redirectUri = 'https://thisable-production.up.railway.app/backend/candidate/google_auth.php';
    }
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
        // ✅ FIXED: Use BASE_URL
        header('Location: ' . $BASE_URL . 'frontend/candidate/login.php?error=google_auth_failed');
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
        // ✅ FIXED: Use BASE_URL
        header('Location: ' . $BASE_URL . 'frontend/candidate/login.php?error=google_userinfo_failed');
        exit;
    }
    
    $userInfo = json_decode($userInfo, true);
    
    // Check if user exists in database
    $stmt = $conn->prepare("SELECT ua.account_id, ua.seeker_id, js.first_name 
                           FROM user_accounts ua 
                           JOIN job_seekers js ON ua.seeker_id = js.seeker_id 
                           WHERE ua.email = :email AND ua.google_account = 1");
    $stmt->bindParam(':email', $userInfo['email']);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // User exists - Log them in
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['user_id'] = $user['account_id'];
        $_SESSION['seeker_id'] = $user['seeker_id'];
        $_SESSION['user_email'] = $userInfo['email'];
        $_SESSION['user_name'] = $user['first_name'];
        $_SESSION['logged_in'] = true;
        
        // ✅ FIXED: Redirect to dashboard using BASE_URL
        header('Location: ' . $BASE_URL . 'frontend/candidate/dashboard.php');
        exit;
    } else {
        // New user - Store in session and redirect to complete profile form
        $_SESSION['google_data'] = [
            'email' => $userInfo['email'],
            'first_name' => $userInfo['given_name'] ?? '',
            'last_name' => $userInfo['family_name'] ?? '',
            'profile_picture' => $userInfo['picture'] ?? ''
        ];
        
        // ✅ FIXED: Redirect back to login page using BASE_URL
        header('Location: ' . $BASE_URL . 'frontend/candidate/login.php?show_pwd_details=1');
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