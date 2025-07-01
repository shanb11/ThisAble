<?php
session_start();
require_once '../../backend/db.php';

// Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    echo "Not logged in";
    exit();
}

$seeker_id = $_SESSION['seeker_id'];

// Fetch user data
$query = "SELECT js.*, dt.disability_name, ua.email 
          FROM job_seekers js 
          JOIN disability_types dt ON js.disability_id = dt.disability_id
          JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
          WHERE js.seeker_id = :seeker_id";
          
$stmt = $conn->prepare($query);
$stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch bio
$bio_query = "SELECT bio FROM profile_details WHERE seeker_id = :seeker_id";
$bio_stmt = $conn->prepare($bio_query);
$bio_stmt->bindParam(':seeker_id', $seeker_id, PDO::PARAM_INT);
$bio_stmt->execute();
$bio_result = $bio_stmt->fetch(PDO::FETCH_ASSOC);

$bio = "";
if ($bio_result && !empty($bio_result['bio'])) {
    $bio = $bio_result['bio'];
} else if (!empty($user_data['bio'])) {
    $bio = $user_data['bio'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $log_file = __DIR__ . '/form_test_log.txt';
    file_put_contents($log_file, "Form submitted: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents($log_file, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
    
    // Process form
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $disability_id = $_POST['disability_id'] ?? '';
    $bio = $_POST['bio'] ?? '';
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update job_seekers
        $update_query = "UPDATE job_seekers SET 
                        first_name = :first_name,
                        middle_name = :middle_name,
                        last_name = :last_name,
                        suffix = :suffix,
                        contact_number = :contact_number,
                        city = :city,
                        province = :province";
                        
        if (!empty($disability_id)) {
            $update_query .= ", disability_id = :disability_id";
        }
        
        $update_query .= " WHERE seeker_id = :seeker_id";
        
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':first_name', $first_name);
        $update_stmt->bindParam(':middle_name', $middle_name);
        $update_stmt->bindParam(':last_name', $last_name);
        $update_stmt->bindParam(':suffix', $suffix);
        $update_stmt->bindParam(':contact_number', $contact_number);
        $update_stmt->bindParam(':city', $city);
        $update_stmt->bindParam(':province', $province);
        
        if (!empty($disability_id)) {
            $update_stmt->bindParam(':disability_id', $disability_id);
        }
        
        $update_stmt->bindParam(':seeker_id', $seeker_id);
        $update_stmt->execute();
        
        // Update bio
        $check_query = "SELECT profile_id FROM profile_details WHERE seeker_id = :seeker_id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(':seeker_id', $seeker_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $bio_query = "UPDATE profile_details SET bio = :bio WHERE seeker_id = :seeker_id";
        } else {
            $bio_query = "INSERT INTO profile_details (seeker_id, bio, created_at) VALUES (:seeker_id, :bio, NOW())";
        }
        
        $bio_stmt = $conn->prepare($bio_query);
        $bio_stmt->bindParam(':bio', $bio);
        $bio_stmt->bindParam(':seeker_id', $seeker_id);
        $bio_stmt->execute();
        
        // Commit
        $conn->commit();
        
        // Success message
        $success = true;
        $message = "Profile updated successfully!";
        
    } catch (PDOException $e) {
        // Rollback
        $conn->rollBack();
        file_put_contents($log_file, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        
        $success = false;
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Form Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: 0 auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; margin: 20px 0; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Profile Form Test</h1>
    <p>This form will directly submit to test if form submission works properly.</p>
    
    <?php if (isset($success)): ?>
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user_data['first_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user_data['middle_name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user_data['last_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="suffix">Suffix</label>
                <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($user_data['suffix'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user_data['contact_number']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user_data['city'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user_data['province'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="disability_id">Type of Disability</label>
                <select id="disability_id" name="disability_id">
                    <?php
                    // Fetch all disability types
                    $disability_query = "SELECT * FROM disability_types ORDER BY disability_name";
                    $disability_stmt = $conn->query($disability_query);
                    
                    while ($disability = $disability_stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($disability['disability_id'] == $user_data['disability_id']) ? 'selected' : '';
                        echo '<option value="' . $disability['disability_id'] . '" ' . $selected . '>' . 
                             htmlspecialchars($disability['disability_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group full-width">
                <label for="bio">Professional Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell employers about yourself, your skills, and career goals..."><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit">Save Changes (Direct Submit)</button>
            <a href="profile.php" style="margin-left: 10px; text-decoration: none;">Back to Profile</a>
        </div>
    </form>
    
    <div style="margin-top: 20px;">
        <h2>Form Field Names and Values (For Debugging)</h2>
        <pre><?php
        echo "Form field names in update_personal_info.php:\n";
        echo "first_name, middle_name, last_name, suffix, contact_number, city, province, disability_id, bio\n\n";
        
        echo "Current user data:\n";
        print_r($user_data);
        
        echo "\nCurrent bio:\n";
        print_r($bio_result);
        ?></pre>
    </div>
</body>
</html>