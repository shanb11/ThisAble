<?php
/**
 * Feedback Templates Management
 * Handles CRUD operations for feedback templates
 */

require_once '../db.php';
require_once 'session_check.php';

header('Content-Type: application/json');

try {
    // Validate session and get employer ID
    $employer_id = validateEmployerSession();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetTemplates($conn, $employer_id);
            break;
        case 'POST':
            handleCreateTemplate($conn, $employer_id);
            break;
        case 'PUT':
            handleUpdateTemplate($conn, $employer_id);
            break;
        case 'DELETE':
            handleDeleteTemplate($conn, $employer_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Error in feedback_templates.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGetTemplates($conn, $employer_id) {
    $sql = "
        SELECT template_id, template_title, template_content, template_type
        FROM feedback_templates 
        WHERE employer_id = :employer_id AND is_active = 1
        ORDER BY template_type ASC, template_title ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $templates
    ]);
}

function handleCreateTemplate($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');
    $type = $input['type'] ?? 'rejection';
    
    if (empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title and content are required']);
        return;
    }
    
    $valid_types = ['rejection', 'interview_request', 'general'];
    if (!in_array($type, $valid_types)) {
        $type = 'rejection';
    }
    
    $sql = "
        INSERT INTO feedback_templates (employer_id, template_title, template_content, template_type)
        VALUES (:employer_id, :title, :content, :type)
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'employer_id' => $employer_id,
        'title' => $title,
        'content' => $content,
        'type' => $type
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Feedback template added successfully',
            'data' => [
                'template_id' => $conn->lastInsertId(),
                'title' => $title,
                'content' => $content,
                'type' => $type
            ]
        ]);
    } else {
        throw new Exception('Failed to create feedback template');
    }
}

function handleUpdateTemplate($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $template_id = (int)($input['template_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $content = trim($input['content'] ?? '');
    
    if (!$template_id || empty($title) || empty($content)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID, title and content are required']);
        return;
    }
    
    $sql = "
        UPDATE feedback_templates 
        SET template_title = :title, template_content = :content, updated_at = NOW()
        WHERE template_id = :template_id AND employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'title' => $title,
        'content' => $content,
        'template_id' => $template_id,
        'employer_id' => $employer_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Feedback template updated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Feedback template not found']);
    }
}

function handleDeleteTemplate($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $template_id = (int)($input['template_id'] ?? 0);
    
    if (!$template_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Template ID is required']);
        return;
    }
    
    $sql = "
        UPDATE feedback_templates 
        SET is_active = 0, updated_at = NOW()
        WHERE template_id = :template_id AND employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'template_id' => $template_id,
        'employer_id' => $employer_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Feedback template deleted successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Feedback template not found']);
    }
}
?>