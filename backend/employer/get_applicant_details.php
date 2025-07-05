<?php
// backend/employer/get_applicant_details.php
// API to fetch detailed applicant profile for modal view

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];
    
    $application_id = $_GET['application_id'] ?? null;

    if (!$application_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Application ID is required'
        ]);
        exit;
    }

    // Get main applicant data with security check
    $main_sql = "SELECT * FROM applicant_overview 
                WHERE application_id = :application_id 
                AND employer_id = :employer_id";
    
    $stmt = $conn->prepare($main_sql);
    $stmt->bindValue(':application_id', $application_id);
    $stmt->bindValue(':employer_id', $employer_id);
    $stmt->execute();
    
    $applicant = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$applicant) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Applicant not found or access denied'
        ]);
        exit;
    }
    
    $seeker_id = $applicant['seeker_id'];
    
    // Get skills grouped by category
    $skills_sql = "SELECT 
                    s.skill_name,
                    sc.category_name,
                    sc.category_icon
                  FROM seeker_skills ss 
                  JOIN skills s ON ss.skill_id = s.skill_id 
                  JOIN skill_categories sc ON s.category_id = sc.category_id 
                  WHERE ss.seeker_id = :seeker_id
                  ORDER BY sc.category_name, s.skill_name";
    
    $skills_stmt = $conn->prepare($skills_sql);
    $skills_stmt->bindValue(':seeker_id', $seeker_id);
    $skills_stmt->execute();
    $skills_raw = $skills_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group skills by category
    $skills_by_category = [];
    foreach ($skills_raw as $skill) {
        $category = $skill['category_name'];
        if (!isset($skills_by_category[$category])) {
            $skills_by_category[$category] = [
                'category_icon' => $skill['category_icon'],
                'skills' => []
            ];
        }
        $skills_by_category[$category]['skills'][] = $skill['skill_name'];
    }
    
    // Get education history
    $education_sql = "SELECT 
                        degree,
                        institution,
                        location,
                        start_date,
                        end_date,
                        is_current,
                        description
                      FROM education 
                      WHERE seeker_id = :seeker_id 
                      ORDER BY start_date DESC";
    
    $edu_stmt = $conn->prepare($education_sql);
    $edu_stmt->bindValue(':seeker_id', $seeker_id);
    $edu_stmt->execute();
    $education = $edu_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get work experience
    $experience_sql = "SELECT 
                        job_title,
                        company,
                        location,
                        start_date,
                        end_date,
                        is_current,
                        description
                      FROM experience 
                      WHERE seeker_id = :seeker_id 
                      ORDER BY start_date DESC";
    
    $exp_stmt = $conn->prepare($experience_sql);
    $exp_stmt->bindValue(':seeker_id', $seeker_id);
    $exp_stmt->execute();
    $experience = $exp_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get workplace accommodations
    $accommodations_sql = "SELECT 
                            accommodation_list,
                            no_accommodations_needed,
                            disability_type
                          FROM workplace_accommodations 
                          WHERE seeker_id = :seeker_id";
    
    $acc_stmt = $conn->prepare($accommodations_sql);
    $acc_stmt->bindValue(':seeker_id', $seeker_id);
    $acc_stmt->execute();
    $accommodations_raw = $acc_stmt->fetch(PDO::FETCH_ASSOC);
    
    $accommodations = [];
    if ($accommodations_raw) {
        if ($accommodations_raw['no_accommodations_needed']) {
            $accommodations = ['none_needed' => true];
        } else {
            $acc_list = $accommodations_raw['accommodation_list'];
            $parsed = json_decode($acc_list, true);
            if ($parsed) {
                $accommodations = $parsed;
            } else {
                $accommodations = array_filter(array_map('trim', explode(',', $acc_list)));
            }
        }
    }
    
    // Get user preferences
    $preferences_sql = "SELECT 
                         work_style,
                         job_type,
                         salary_range,
                         availability
                       FROM user_preferences 
                       WHERE seeker_id = :seeker_id";
    
    $pref_stmt = $conn->prepare($preferences_sql);
    $pref_stmt->bindValue(':seeker_id', $seeker_id);
    $pref_stmt->execute();
    $preferences = $pref_stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Get application status history
    $history_sql = "SELECT 
                      previous_status,
                      new_status,
                      notes,
                      changed_at,
                      changed_by_employer
                    FROM application_status_history 
                    WHERE application_id = :application_id 
                    ORDER BY changed_at DESC";
    
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bindValue(':application_id', $application_id);
    $history_stmt->execute();
    $status_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get interview information if any
    $interview_sql = "SELECT 
                        interview_id,
                        interview_type,
                        scheduled_date,
                        scheduled_time,
                        duration_minutes,
                        interview_platform,
                        meeting_link,
                        location_address,
                        interview_status,
                        accommodations_needed,
                        sign_language_interpreter,
                        wheelchair_accessible_venue,
                        screen_reader_materials
                      FROM interviews 
                      WHERE application_id = :application_id 
                      ORDER BY scheduled_date DESC";
    
    $interview_stmt = $conn->prepare($interview_sql);
    $interview_stmt->bindValue(':application_id', $application_id);
    $interview_stmt->execute();
    $interviews = $interview_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    $applicant['avatar'] = strtoupper(substr($applicant['first_name'], 0, 1) . substr($applicant['last_name'], 0, 1));
    $applicant['full_name'] = $applicant['first_name'] . ' ' . $applicant['last_name'];
    $applicant['applied_at_formatted'] = date('M j, Y', strtotime($applicant['applied_at']));
    $applicant['last_activity_formatted'] = date('M j, Y g:i A', strtotime($applicant['last_activity']));
    
    // Map status for frontend display
    $status_map = [
        'submitted' => 'New',
        'under_review' => 'Reviewed',
        'shortlisted' => 'Shortlisted',
        'interview_scheduled' => 'Interview',
        'interviewed' => 'Interviewed',
        'hired' => 'Hired',
        'rejected' => 'Rejected'
    ];
    $applicant['status_display'] = $status_map[$applicant['application_status']] ?? $applicant['application_status'];
    
    // Format education dates
    foreach ($education as &$edu) {
        $edu['start_date_formatted'] = date('M Y', strtotime($edu['start_date']));
        if ($edu['is_current']) {
            $edu['end_date_formatted'] = 'Present';
        } else {
            $edu['end_date_formatted'] = $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : '';
        }
    }
    
    // Format experience dates
    foreach ($experience as &$exp) {
        $exp['start_date_formatted'] = date('M Y', strtotime($exp['start_date']));
        if ($exp['is_current']) {
            $exp['end_date_formatted'] = 'Present';
        } else {
            $exp['end_date_formatted'] = $exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : '';
        }
    }
    
    // Format interview dates
    foreach ($interviews as &$interview) {
        $interview['scheduled_date_formatted'] = date('M j, Y', strtotime($interview['scheduled_date']));
        $interview['scheduled_time_formatted'] = date('g:i A', strtotime($interview['scheduled_time']));
    }

    $documentsQuery = "
    SELECT 
        document_id,
        document_type,
        document_name,
        original_filename,
        file_path,
        file_size,
        mime_type,
        upload_date,        -- FIXED: was 'uploaded_at'
        is_verified,
        verification_notes  -- FIXED: was 'description'
    FROM candidate_documents 
    WHERE seeker_id = :seeker_id 
    ORDER BY 
        CASE document_type 
            WHEN 'diploma' THEN 1
            WHEN 'certificate' THEN 2
            WHEN 'license' THEN 3
            WHEN 'other' THEN 4
        END,
        upload_date DESC    -- FIXED: was 'uploaded_at'
";

$docStmt = $conn->prepare($documentsQuery);
$docStmt->bindParam(':seeker_id', $applicant['seeker_id'], PDO::PARAM_INT);
$docStmt->execute();
$documents = $docStmt->fetchAll(PDO::FETCH_ASSOC);

// PHASE 4 ENHANCEMENT: Simple requirements analysis (CORRECTED)
$requirementsAnalysis = [
    'documents_submitted' => count($documents),
    'required_documents' => ['diploma', 'certificate'], // Based on your enum
    'missing_documents' => [],
    'has_education_docs' => false,
    'has_certification_docs' => false,
    'overall_score' => 0
];

// Check for required document types
$documentTypes = array_column($documents, 'document_type');
$requirementsAnalysis['has_education_docs'] = in_array('diploma', $documentTypes);
$requirementsAnalysis['has_certification_docs'] = in_array('certificate', $documentTypes);

// Calculate missing documents
foreach ($requirementsAnalysis['required_documents'] as $reqDoc) {
    if (!in_array($reqDoc, $documentTypes)) {
        $requirementsAnalysis['missing_documents'][] = ucfirst($reqDoc);
    }
}

// Simple scoring based on your actual document types
$score = 0;
if (in_array('diploma', $documentTypes)) $score += 50;     // Education is important
if (in_array('certificate', $documentTypes)) $score += 30; // Certifications add value  
if (in_array('license', $documentTypes)) $score += 20;     // Licenses are bonus
$requirementsAnalysis['overall_score'] = min($score, 100); // Cap at 100%

// ADD to your existing response array:
$response['documents'] = $documents;
$response['requirements_analysis'] = $requirementsAnalysis;
    
    echo json_encode([
        'success' => true,
        'applicant' => $applicant,
        'skills' => $skills_by_category,
        'education' => $education,
        'experience' => $experience,
        'accommodations' => $accommodations,
        'preferences' => $preferences,
        'status_history' => $status_history,
        'interviews' => $interviews
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}