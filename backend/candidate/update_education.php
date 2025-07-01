<?php
/**
 * Education Update Handler
 * Backend file: backend/candidate/update_education.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Set content type
header('Content-Type: application/json');

// Security: Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$seeker_id = $_SESSION['seeker_id'];

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$education_id = $_POST['education_id'] ?? null;
$degree = trim($_POST['degree'] ?? '');
$institution = trim($_POST['institution'] ?? '');
$location = trim($_POST['location'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$is_current = isset($_POST['is_current']) ? 1 : 0;
$description = trim($_POST['description'] ?? '');

// Validation
$errors = [];

if (empty($degree)) {
    $errors[] = 'Degree/Certificate is required';
}

if (empty($institution)) {
    $errors[] = 'Institution is required';
}

if (empty($start_date)) {
    $errors[] = 'Start date is required';
}

// Validate date format
if (!empty($start_date) && !DateTime::createFromFormat('Y-m', $start_date)) {
    $errors[] = 'Invalid start date format';
}

if (!empty($end_date) && !$is_current && !DateTime::createFromFormat('Y-m', $end_date)) {
    $errors[] = 'Invalid end date format';
}

// If current, set end_date to null
if ($is_current) {
    $end_date = null;
}

// Convert date format for database (Y-m to Y-m-01)
$start_date_db = $start_date . '-01';
$end_date_db = $end_date ? $end_date . '-01' : null;

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    if ($education_id) {
        // Update existing education
        
        // First, verify ownership
        $check_stmt = $conn->prepare("SELECT seeker_id FROM education WHERE education_id = ?");
        $check_stmt->execute([$education_id]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing || $existing['seeker_id'] != $seeker_id) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Education record not found or access denied']);
            exit;
        }
        
        $stmt = $conn->prepare("
            UPDATE education 
            SET degree = ?, institution = ?, location = ?, start_date = ?, end_date = ?, 
                is_current = ?, description = ?
            WHERE education_id = ? AND seeker_id = ?
        ");
        
        $result = $stmt->execute([
            $degree, $institution, $location, $start_date_db, $end_date_db, 
            $is_current, $description, $education_id, $seeker_id
        ]);
        
        $message = 'Education updated successfully!';
        
    } else {
        // Insert new education
        $stmt = $conn->prepare("
            INSERT INTO education (seeker_id, degree, institution, location, start_date, end_date, is_current, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $seeker_id, $degree, $institution, $location, $start_date_db, $end_date_db, $is_current, $description
        ]);
        
        $message = 'Education added successfully!';
    }
    
    if ($result) {
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => $message
        ]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to save education']);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log('Education update error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>