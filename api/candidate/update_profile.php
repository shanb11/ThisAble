<?php
/**
 * Update Profile API for ThisAble Mobile
 * Handles: personal info, bio, profile details with auto-create logic
 */

// Include required files
require_once '../config/cors.php';
require_once '../config/response.php';
require_once '../config/database.php';

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    ApiResponse::error("Method not allowed", 405);
}

try {
    // Require authentication
    $user = requireAuth();
    
    if ($user['user_type'] !== 'candidate') {
        ApiResponse::unauthorized("Invalid user type");
    }
    
    $seekerId = $user['user_id'];
    error_log("Update Profile API: seeker_id=$seekerId");

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::validationError(['input' => 'Invalid JSON input']);
    }

    // Get section to update
    $section = $input['section'] ?? 'personal_info';
    error_log("Update Profile Section: $section");

    // Get database connection
    $conn = ApiDatabase::getConnection();
    
    // Handle different sections
    switch ($section) {
        case 'personal_info':
            updatePersonalInfo($conn, $seekerId, $input);
            break;
            
        case 'bio':
            updateBio($conn, $seekerId, $input);
            break;
            
        case 'skills':
            updateSkills($conn, $seekerId, $input);
            break;
            
        case 'education':
            updateEducation($conn, $seekerId, $input);
            break;
            
        case 'experience':
            updateExperience($conn, $seekerId, $input);
            break;
            
        default:
            ApiResponse::error("Invalid section: $section", 400);
    }
    
} catch(PDOException $e) {
    error_log("Update profile database error: " . $e->getMessage());
    ApiResponse::serverError("Database error occurred");
    
} catch(Exception $e) {
    error_log("Update profile error: " . $e->getMessage());
    ApiResponse::serverError("An error occurred while updating profile");
}

/**
 * Update personal information (job_seekers table + create profile_details if needed)
 */
function updatePersonalInfo($conn, $seekerId, $input) {
    try {
        // Extract and validate data
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $location = trim($input['location'] ?? '');
        $bio = trim($input['bio'] ?? '');
        
        // Validation
        $errors = [];
        if (empty($firstName)) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($lastName)) {
            $errors['last_name'] = 'Last name is required';
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (!empty($phone) && !preg_match('/^09\d{9}$/', $phone)) {
            $errors['phone'] = 'Invalid Philippine phone number format';
        }
        
        if (!empty($errors)) {
            ApiResponse::validationError($errors, "Validation failed");
        }
        
        $conn->beginTransaction();
        
        // Update job_seekers table
        $stmt = $conn->prepare("
            UPDATE job_seekers 
            SET first_name = ?, last_name = ?, contact_number = ?
            WHERE seeker_id = ?
        ");
        $stmt->execute([$firstName, $lastName, $phone, $seekerId]);
        
        // Update email in user_accounts if provided
        if (!empty($email)) {
            $stmt = $conn->prepare("
                UPDATE user_accounts 
                SET email = ?
                WHERE seeker_id = ?
            ");
            $stmt->execute([$email, $seekerId]);
        }
        
        // Check if profile_details record exists
        $stmt = $conn->prepare("SELECT profile_id FROM profile_details WHERE seeker_id = ?");
        $stmt->execute([$seekerId]);
        $profileExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profileExists) {
            // Update existing profile_details
            $stmt = $conn->prepare("
                UPDATE profile_details 
                SET bio = ?, location = ?, updated_at = NOW()
                WHERE seeker_id = ?
            ");
            $stmt->execute([$bio, $location, $seekerId]);
            error_log("Updated existing profile_details for seeker_id=$seekerId");
        } else {
            // Create new profile_details record (Scenario A)
            $stmt = $conn->prepare("
                INSERT INTO profile_details (seeker_id, bio, location, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$seekerId, $bio, $location]);
            error_log("Created new profile_details for seeker_id=$seekerId");
        }
        
        $conn->commit();
        
        // Log successful update
        ApiResponse::logActivity('profile_update', [
            'user_id' => $seekerId,
            'section' => 'personal_info',
            'updated_fields' => ['first_name', 'last_name', 'email', 'phone', 'location', 'bio']
        ]);
        
        ApiResponse::success([
            'updated_fields' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'location' => $location,
                'bio' => $bio
            ]
        ], "Personal information updated successfully");
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Update bio and headline only
 */
function updateBio($conn, $seekerId, $input) {
    try {
        $bio = trim($input['bio'] ?? '');
        $headline = trim($input['headline'] ?? '');
        
        // Check if profile_details exists
        $stmt = $conn->prepare("SELECT profile_id FROM profile_details WHERE seeker_id = ?");
        $stmt->execute([$seekerId]);
        $profileExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($profileExists) {
            // Update existing
            $stmt = $conn->prepare("
                UPDATE profile_details 
                SET bio = ?, headline = ?, updated_at = NOW()
                WHERE seeker_id = ?
            ");
            $stmt->execute([$bio, $headline, $seekerId]);
        } else {
            // Create new
            $stmt = $conn->prepare("
                INSERT INTO profile_details (seeker_id, bio, headline, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$seekerId, $bio, $headline]);
        }
        
        ApiResponse::logActivity('profile_update', [
            'user_id' => $seekerId,
            'section' => 'bio'
        ]);
        
        ApiResponse::success([
            'bio' => $bio,
            'headline' => $headline
        ], "Bio updated successfully");
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Update skills (placeholder for Phase 2)
 */
function updateSkills($conn, $seekerId, $input) {
    ApiResponse::error("Skills update will be implemented in Phase 2", 501);
}

/**
 * Update education (placeholder for Phase 2)
 */
function updateEducation($conn, $seekerId, $input) {
    ApiResponse::error("Education update will be implemented in Phase 2", 501);
}

/**
 * Update experience (placeholder for Phase 2)
 */
function updateExperience($conn, $seekerId, $input) {
    ApiResponse::error("Experience update will be implemented in Phase 2", 501);
}
?>