<?php
// backend/candidate/get_job_accommodations.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../db.php';

try {
    // Get job ID from request
    $job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
    
    if (!$job_id) {
        throw new Exception('Job ID is required');
    }
    
    // Get PWD accommodations for this job
    $stmt = $conn->prepare("
        SELECT 
            ja.wheelchair_accessible,
            ja.flexible_schedule,
            ja.assistive_technology,
            ja.remote_work_option,
            ja.screen_reader_compatible,
            ja.sign_language_interpreter,
            ja.modified_workspace,
            ja.transportation_support,
            ja.additional_accommodations
        FROM job_accommodations ja
        WHERE ja.job_id = ?
    ");
    
    $stmt->execute([$job_id]);
    $accommodations_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $accommodations = [];
    
    if ($accommodations_data) {
        // Map database fields to user-friendly names
        $accommodation_map = [
            'wheelchair_accessible' => 'Wheelchair Accessible',
            'flexible_schedule' => 'Flexible Schedule',
            'assistive_technology' => 'Assistive Technology Support',
            'remote_work_option' => 'Remote Work Option',
            'screen_reader_compatible' => 'Screen Reader Compatible',
            'sign_language_interpreter' => 'Sign Language Interpreter',
            'modified_workspace' => 'Modified Workspace',
            'transportation_support' => 'Transportation Support'
        ];
        
        // Add accommodations that are enabled (value = 1)
        foreach ($accommodation_map as $field => $label) {
            if ($accommodations_data[$field] == 1) {
                $accommodations[] = $label;
            }
        }
        
        // Add additional accommodations if any
        if (!empty($accommodations_data['additional_accommodations'])) {
            $additional = explode(',', $accommodations_data['additional_accommodations']);
            foreach ($additional as $acc) {
                $acc = trim($acc);
                if (!empty($acc)) {
                    $accommodations[] = $acc;
                }
            }
        }
    }
    
    // If no accommodations found, provide default PWD-friendly ones
    if (empty($accommodations)) {
        $accommodations = [
            'PWD-Friendly Workplace',
            'Inclusive Environment',
            'Equal Opportunities',
            'Supportive Management'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'accommodations' => $accommodations,
        'job_id' => $job_id
    ]);
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'accommodations' => [
            'PWD-Friendly Workplace',
            'Inclusive Environment', 
            'Equal Opportunities'
        ]
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'accommodations' => [
            'PWD-Friendly Workplace',
            'Inclusive Environment',
            'Equal Opportunities'
        ]
    ]);
}
?>