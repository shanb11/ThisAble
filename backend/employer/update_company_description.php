<?php
/**
 * Update Company Description & Logo
 * Handles logo upload, company description, and why join us updates
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    // Extract form data
    $company_description = trim($_POST['company_description'] ?? '');
    $why_join_us = trim($_POST['why_join_us'] ?? '');
    $remove_logo = isset($_POST['remove_logo']) && $_POST['remove_logo'] === 'true';
    
    // Validation array
    $errors = [];
    
    // Validate required fields
    if (empty($company_description)) {
        $errors[] = 'Company description is required';
    } elseif (strlen($company_description) < 50) {
        $errors[] = 'Company description must be at least 50 characters long';
    } elseif (strlen($company_description) > 2000) {
        $errors[] = 'Company description must be less than 2000 characters';
    }
    
    // Why join us is optional but has limits if provided
    if (!empty($why_join_us)) {
        if (strlen($why_join_us) < 30) {
            $errors[] = 'Why Join Us section must be at least 30 characters long if provided';
        } elseif (strlen($why_join_us) > 1000) {
            $errors[] = 'Why Join Us section must be less than 1000 characters';
        }
    }
    
    // Handle logo upload if file is provided
    $logo_path = null;
    $logo_uploaded = false;
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $logo_upload_result = handleLogoUpload($_FILES['logo'], $employer_id);
        
        if ($logo_upload_result['success']) {
            $logo_path = $logo_upload_result['path'];
            $logo_uploaded = true;
        } else {
            $errors[] = $logo_upload_result['message'];
        }
    }
    
    // Return validation errors
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $errors[0],
            'errors' => $errors
        ]);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Get current logo path for removal if needed
        $current_logo_sql = "SELECT company_logo_path FROM employers WHERE employer_id = :employer_id";
        $current_logo_stmt = $conn->prepare($current_logo_sql);
        $current_logo_stmt->execute(['employer_id' => $employer_id]);
        $current_data = $current_logo_stmt->fetch(PDO::FETCH_ASSOC);
        $current_logo_path = $current_data['company_logo_path'] ?? '';
        
        // Prepare update SQL based on what needs to be updated
        $update_fields = [
            'company_description = :company_description',
            'why_join_us = :why_join_us',
            'updated_at = NOW()'
        ];
        
        $update_params = [
            'company_description' => $company_description,
            'why_join_us' => $why_join_us,
            'employer_id' => $employer_id
        ];
        
        // Handle logo updates
        if ($remove_logo) {
            // Remove logo
            $update_fields[] = 'company_logo_path = NULL';
            
            // Delete old logo file if exists
            if (!empty($current_logo_path) && file_exists('../../' . $current_logo_path)) {
                unlink('../../' . $current_logo_path);
            }
            
        } elseif ($logo_uploaded) {
            // Update with new logo
            $update_fields[] = 'company_logo_path = :logo_path';
            $update_params['logo_path'] = $logo_path;
            
            // Delete old logo file if exists
            if (!empty($current_logo_path) && file_exists('../../' . $current_logo_path)) {
                unlink('../../' . $current_logo_path);
            }
        }
        
        // Execute update
        $update_sql = "UPDATE employers SET " . implode(', ', $update_fields) . " WHERE employer_id = :employer_id";
        $update_stmt = $conn->prepare($update_sql);
        $update_result = $update_stmt->execute($update_params);
        
        if (!$update_result) {
            throw new Exception('Failed to update company description');
        }
        
        // Update setup progress for description completion
        $progress_sql = "
            INSERT INTO employer_setup_progress 
            (employer_id, description_complete, updated_at) 
            VALUES (:employer_id, 1, NOW())
            ON DUPLICATE KEY UPDATE 
            description_complete = 1, 
            updated_at = NOW()
        ";
        
        $progress_stmt = $conn->prepare($progress_sql);
        $progress_stmt->execute(['employer_id' => $employer_id]);
        
        // Update logo progress if logo was uploaded or removed
        if ($logo_uploaded || $remove_logo) {
            $logo_complete = $logo_uploaded ? 1 : 0;
            
            $logo_progress_sql = "
                UPDATE employer_setup_progress 
                SET logo_upload_complete = :logo_complete,
                    updated_at = NOW()
                WHERE employer_id = :employer_id
            ";
            
            $logo_progress_stmt = $conn->prepare($logo_progress_sql);
            $logo_progress_stmt->execute([
                'logo_complete' => $logo_complete,
                'employer_id' => $employer_id
            ]);
        }
        
        // Calculate new completion percentage
        $completion_percentage = calculateCompletionPercentage($conn, $employer_id);
        
        // Update completion percentage
        $update_progress_sql = "
            UPDATE employer_setup_progress 
            SET completion_percentage = :percentage,
                setup_complete = :setup_complete,
                updated_at = NOW()
            WHERE employer_id = :employer_id
        ";
        
        $update_progress_stmt = $conn->prepare($update_progress_sql);
        $update_progress_stmt->execute([
            'percentage' => $completion_percentage,
            'setup_complete' => ($completion_percentage >= 100) ? 1 : 0,
            'employer_id' => $employer_id
        ]);
        
        // Commit transaction
        $conn->commit();
        
        // Determine final logo path for response
        $final_logo_path = null;
        if ($remove_logo) {
            $final_logo_path = null;
        } elseif ($logo_uploaded) {
            $final_logo_path = $logo_path;
        } else {
            $final_logo_path = $current_logo_path;
        }
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Company description and logo updated successfully',
            'data' => [
                'company_description' => $company_description,
                'why_join_us' => $why_join_us,
                'company_logo_path' => $final_logo_path,
                'logo_uploaded' => $logo_uploaded,
                'logo_removed' => $remove_logo,
                'completion_percentage' => $completion_percentage
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Clean up uploaded file if transaction failed
        if ($logo_uploaded && $logo_path && file_exists('../../' . $logo_path)) {
            unlink('../../' . $logo_path);
        }
        
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_company_description.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("General error in update_company_description.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating company information. Please try again.'
    ]);
}

/**
 * Handle logo file upload with validation
 */
function handleLogoUpload($file, $employer_id) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'File upload failed. Please try again.'
        ];
    }
    
    // Validate file size (2MB max)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $max_size) {
        return [
            'success' => false,
            'message' => 'File size must be less than 2MB'
        ];
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = $file['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        return [
            'success' => false,
            'message' => 'Only JPG, PNG, and GIF images are allowed'
        ];
    }
    
    // Validate actual image content
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return [
            'success' => false,
            'message' => 'Invalid image file'
        ];
    }
    
    // Validate image dimensions (max 2000x2000)
    $max_width = 2000;
    $max_height = 2000;
    
    if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
        return [
            'success' => false,
            'message' => "Image dimensions must be less than {$max_width}x{$max_height} pixels"
        ];
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../../uploads/company_logos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Failed to create upload directory'
            ];
        }
    }
    
    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = $employer_id . '_logo_' . uniqid() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => false,
            'message' => 'Failed to save uploaded file'
        ];
    }
    
    // Return relative path for database storage
    $relative_path = 'uploads/company_logos/' . $new_filename;
    
    return [
        'success' => true,
        'path' => $relative_path
    ];
}

/**
 * Calculate completion percentage based on profile sections
 */
function calculateCompletionPercentage($conn, $employer_id) {
    try {
        // Get all relevant data for calculation
        $sql = "
            SELECT 
                e.company_name,
                e.industry,
                e.company_address,
                e.company_description,
                e.why_join_us,
                e.company_logo_path,
                ehp.open_to_pwd,
                esl.website_url,
                COUNT(ec.contact_id) as contact_count
            FROM employers e
            LEFT JOIN employer_hiring_preferences ehp ON e.employer_id = ehp.employer_id
            LEFT JOIN employer_social_links esl ON e.employer_id = esl.employer_id
            LEFT JOIN employer_contacts ec ON e.employer_id = ec.employer_id AND ec.is_primary = 1
            WHERE e.employer_id = :employer_id
            GROUP BY e.employer_id
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['employer_id' => $employer_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return 0;
        }
        
        $completed_sections = 0;
        $total_sections = 5; // 5 sections worth 20% each
        
        // 1. Basic Info (20%) - Company name, industry, address
        if (!empty($data['company_name']) && 
            !empty($data['industry']) && 
            !empty($data['company_address'])) {
            $completed_sections++;
        }
        
        // 2. Company Description (20%) - Description and why join us
        if (!empty($data['company_description']) && 
            strlen($data['company_description']) > 50 &&
            !empty($data['why_join_us']) && 
            strlen($data['why_join_us']) > 30) {
            $completed_sections++;
        }
        
        // 3. Hiring Preferences (20%) - PWD settings
        if ($data['open_to_pwd'] !== null) {
            $completed_sections++;
        }
        
        // 4. Social Links (20%) - At least website
        if (!empty($data['website_url'])) {
            $completed_sections++;
        }
        
        // 5. Logo (20%) - Logo uploaded
        if (!empty($data['company_logo_path'])) {
            $completed_sections++;
        }
        
        return round(($completed_sections / $total_sections) * 100);
        
    } catch (Exception $e) {
        error_log("Error calculating completion percentage: " . $e->getMessage());
        return 0;
    }
}
?>