<?php
// backend/employer/schedule_interview.php
// API to schedule interviews with PWD accommodations + NOTIFICATIONS

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once '../db.php';
require_once 'session_check.php';
require_once 'notification_system.php'; // ADD NOTIFICATION SYSTEM

try {
    // Validate session and get employer ID
    $employer_data = getValidatedEmployerData();
    $employer_id = $employer_data['employer_id'];

    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $application_id = $input['application_id'] ?? null;
    $interview_type = $input['interview_type'] ?? 'online'; // online, in_person, phone
    $scheduled_date = $input['scheduled_date'] ?? null;
    $scheduled_time = $input['scheduled_time'] ?? null;
    $duration_minutes = $input['duration_minutes'] ?? 60;
    $interview_platform = $input['interview_platform'] ?? null;
    $meeting_link = $input['meeting_link'] ?? null;
    $location_address = $input['location_address'] ?? null;
    $interviewer_notes = $input['interviewer_notes'] ?? '';
    
    // PWD Accommodations
    $accommodations_needed = $input['accommodations_needed'] ?? '';
    $sign_language_interpreter = $input['sign_language_interpreter'] ?? false;
    $wheelchair_accessible_venue = $input['wheelchair_accessible_venue'] ?? false;
    $screen_reader_materials = $input['screen_reader_materials'] ?? false;

    if (!$application_id || !$scheduled_date || !$scheduled_time) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Application ID, date, and time are required'
        ]);
        exit;
    }

    $conn->beginTransaction();
    
    // Verify the application belongs to this employer
    $verify_sql = "SELECT 
                    ja.application_id,
                    ja.seeker_id,
                    ja.application_status,
                    jp.job_title,
                    jp.job_id,
                    js.first_name,
                    js.last_name,
                    js.contact_number,
                    ua.email,
                    dt.disability_name
                  FROM job_applications ja
                  JOIN job_posts jp ON ja.job_id = jp.job_id
                  JOIN job_seekers js ON ja.seeker_id = js.seeker_id
                  LEFT JOIN user_accounts ua ON js.seeker_id = ua.seeker_id
                  LEFT JOIN disability_types dt ON js.disability_id = dt.disability_id
                  WHERE ja.application_id = :application_id 
                  AND jp.employer_id = :employer_id";
    
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bindValue(':application_id', $application_id);
    $verify_stmt->bindValue(':employer_id', $employer_id);
    $verify_stmt->execute();
    
    $application_data = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application_data) {
        throw new Exception('Application not found or access denied');
    }
    
    // Check if interview already exists for this application
    $existing_interview_sql = "SELECT interview_id FROM interviews WHERE application_id = :application_id";
    $existing_stmt = $conn->prepare($existing_interview_sql);
    $existing_stmt->bindValue(':application_id', $application_id);
    $existing_stmt->execute();
    $existing_interview = $existing_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_interview) {
        // Update existing interview
        $update_interview_sql = "UPDATE interviews SET 
                                interview_type = :interview_type,
                                scheduled_date = :scheduled_date,
                                scheduled_time = :scheduled_time,
                                duration_minutes = :duration_minutes,
                                interview_platform = :interview_platform,
                                meeting_link = :meeting_link,
                                location_address = :location_address,
                                interviewer_notes = :interviewer_notes,
                                accommodations_needed = :accommodations_needed,
                                sign_language_interpreter = :sign_language_interpreter,
                                wheelchair_accessible_venue = :wheelchair_accessible_venue,
                                screen_reader_materials = :screen_reader_materials,
                                interview_status = 'scheduled',
                                updated_at = CURRENT_TIMESTAMP
                               WHERE interview_id = :interview_id";
        
        $update_stmt = $conn->prepare($update_interview_sql);
        $update_stmt->bindValue(':interview_type', $interview_type);
        $update_stmt->bindValue(':scheduled_date', $scheduled_date);
        $update_stmt->bindValue(':scheduled_time', $scheduled_time);
        $update_stmt->bindValue(':duration_minutes', $duration_minutes);
        $update_stmt->bindValue(':interview_platform', $interview_platform);
        $update_stmt->bindValue(':meeting_link', $meeting_link);
        $update_stmt->bindValue(':location_address', $location_address);
        $update_stmt->bindValue(':interviewer_notes', $interviewer_notes);
        $update_stmt->bindValue(':accommodations_needed', $accommodations_needed);
        $update_stmt->bindValue(':sign_language_interpreter', $sign_language_interpreter ? 1 : 0);
        $update_stmt->bindValue(':wheelchair_accessible_venue', $wheelchair_accessible_venue ? 1 : 0);
        $update_stmt->bindValue(':screen_reader_materials', $screen_reader_materials ? 1 : 0);
        $update_stmt->bindValue(':interview_id', $existing_interview['interview_id']);
        $update_stmt->execute();
        
        $interview_id = $existing_interview['interview_id'];
        $action = 'updated';
    } else {
        // Create new interview
        $insert_interview_sql = "INSERT INTO interviews 
                               (application_id, interview_type, scheduled_date, scheduled_time, 
                                duration_minutes, interview_platform, meeting_link, location_address,
                                interviewer_notes, accommodations_needed, sign_language_interpreter, 
                                wheelchair_accessible_venue, screen_reader_materials, interview_status,
                                created_by_employer_id, created_at)
                               VALUES 
                               (:application_id, :interview_type, :scheduled_date, :scheduled_time,
                                :duration_minutes, :interview_platform, :meeting_link, :location_address,
                                :interviewer_notes, :accommodations_needed, :sign_language_interpreter,
                                :wheelchair_accessible_venue, :screen_reader_materials, 'scheduled',
                                :employer_id, CURRENT_TIMESTAMP)";
        
        $insert_stmt = $conn->prepare($insert_interview_sql);
        $insert_stmt->bindValue(':application_id', $application_id);
        $insert_stmt->bindValue(':interview_type', $interview_type);
        $insert_stmt->bindValue(':scheduled_date', $scheduled_date);
        $insert_stmt->bindValue(':scheduled_time', $scheduled_time);
        $insert_stmt->bindValue(':duration_minutes', $duration_minutes);
        $insert_stmt->bindValue(':interview_platform', $interview_platform);
        $insert_stmt->bindValue(':meeting_link', $meeting_link);
        $insert_stmt->bindValue(':location_address', $location_address);
        $insert_stmt->bindValue(':interviewer_notes', $interviewer_notes);
        $insert_stmt->bindValue(':accommodations_needed', $accommodations_needed);
        $insert_stmt->bindValue(':sign_language_interpreter', $sign_language_interpreter ? 1 : 0);
        $insert_stmt->bindValue(':wheelchair_accessible_venue', $wheelchair_accessible_venue ? 1 : 0);
        $insert_stmt->bindValue(':screen_reader_materials', $screen_reader_materials ? 1 : 0);
        $insert_stmt->bindValue(':employer_id', $employer_id);
        $insert_stmt->execute();
        
        $interview_id = $conn->lastInsertId();
        $action = 'scheduled';
    }
    
    // Update application status to interview_scheduled if not already
    if ($application_data['application_status'] !== 'interview_scheduled') {
        $update_app_sql = "UPDATE job_applications 
                          SET application_status = 'interview_scheduled',
                              status_updated_at = CURRENT_TIMESTAMP,
                              last_activity = CURRENT_TIMESTAMP
                          WHERE application_id = :application_id";
        
        $update_app_stmt = $conn->prepare($update_app_sql);
        $update_app_stmt->bindValue(':application_id', $application_id);
        $update_app_stmt->execute();
        
        // Add to status history
        $history_sql = "INSERT INTO application_status_history 
                       (application_id, previous_status, new_status, changed_by_employer, notes, changed_at)
                       VALUES (:application_id, :previous_status, 'interview_scheduled', 1, :notes, CURRENT_TIMESTAMP)";
        
        $history_stmt = $conn->prepare($history_sql);
        $history_stmt->bindValue(':application_id', $application_id);
        $history_stmt->bindValue(':previous_status', $application_data['application_status']);
        $history_stmt->bindValue(':notes', 'Interview scheduled for ' . date('M j, Y g:i A', strtotime($scheduled_date . ' ' . $scheduled_time)));
        $history_stmt->execute();
    }
    
    // 🔥 ADD NOTIFICATION FOR EMPLOYER - Interview Scheduled
    try {
        $applicant_name = $application_data['first_name'] . ' ' . $application_data['last_name'];
        notify_interview_scheduled(
            $employer_id,
            $applicant_name,
            $application_data['job_title'],
            $scheduled_date,
            $scheduled_time,
            $application_data['job_id'],
            $application_id,
            $interview_id
        );
    } catch (Exception $e) {
        // Don't fail the main operation if notification fails
        error_log("Notification failed in schedule_interview: " . $e->getMessage());
    }
    
    // Create notification for the job seeker
    $interview_datetime = date('M j, Y \a\t g:i A', strtotime($scheduled_date . ' ' . $scheduled_time));
    $notification_message = "You have an interview scheduled for {$application_data['job_title']} on {$interview_datetime}.";
    
    if ($interview_type === 'online' && $meeting_link) {
        $notification_message .= " Meeting link: {$meeting_link}";
    } elseif ($interview_type === 'in_person' && $location_address) {
        $notification_message .= " Location: {$location_address}";
    }
    
    // Add accommodation notes if any
    $accommodation_notes = [];
    if ($sign_language_interpreter) $accommodation_notes[] = "Sign language interpreter will be provided";
    if ($wheelchair_accessible_venue) $accommodation_notes[] = "Venue is wheelchair accessible";
    if ($screen_reader_materials) $accommodation_notes[] = "Screen reader compatible materials will be provided";
    if ($accommodations_needed) $accommodation_notes[] = $accommodations_needed;
    
    if (!empty($accommodation_notes)) {
        $notification_message .= " Accommodations: " . implode(', ', $accommodation_notes);
    }
    
    $notification_sql = "INSERT INTO notifications 
                        (recipient_type, recipient_id, type_id, title, message, related_application_id, related_interview_id, created_at)
                        VALUES ('candidate', :seeker_id, :type_id, :title, :message, :application_id, :interview_id, CURRENT_TIMESTAMP)";
    
    $notification_stmt = $conn->prepare($notification_sql);
    $notification_stmt->bindValue(':seeker_id', $application_data['seeker_id']);
    $notification_stmt->bindValue(':type_id', 2); // interview_scheduled type
    $notification_stmt->bindValue(':title', 'Interview Scheduled');
    $notification_stmt->bindValue(':message', $notification_message);
    $notification_stmt->bindValue(':application_id', $application_id);
    $notification_stmt->bindValue(':interview_id', $interview_id);
    $notification_stmt->execute();
    
    // Log the activity
    logActivity("INTERVIEW_SCHEDULED", "Interview {$action} for application {$application_id} on {$scheduled_date} {$scheduled_time}");
    
    $conn->commit();
    
    // Return success with interview data
    echo json_encode([
        'success' => true,
        'message' => "Interview {$action} successfully for {$application_data['first_name']} {$application_data['last_name']}",
        'data' => [
            'interview_id' => $interview_id,
            'application_id' => $application_id,
            'applicant_name' => $application_data['first_name'] . ' ' . $application_data['last_name'],
            'job_title' => $application_data['job_title'],
            'interview_type' => $interview_type,
            'scheduled_date' => $scheduled_date,
            'scheduled_time' => $scheduled_time,
            'scheduled_datetime_formatted' => $interview_datetime,
            'duration_minutes' => $duration_minutes,
            'accommodations' => [
                'sign_language_interpreter' => $sign_language_interpreter,
                'wheelchair_accessible_venue' => $wheelchair_accessible_venue,
                'screen_reader_materials' => $screen_reader_materials,
                'additional_notes' => $accommodations_needed
            ],
            'contact_info' => [
                'email' => $application_data['email'],
                'phone' => $application_data['contact_number']
            ],
            'disability_info' => $application_data['disability_name'],
            'action' => $action,
            'notification_created' => true // Indicate notification was created
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to schedule interview: ' . $e->getMessage()
    ]);
}