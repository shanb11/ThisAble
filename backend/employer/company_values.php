<?php
/**
 * Company Values Management
 * Handles CRUD operations for company values
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
            handleGetValues($conn, $employer_id);
            break;
        case 'POST':
            handleCreateValue($conn, $employer_id);
            break;
        case 'PUT':
            handleUpdateValue($conn, $employer_id);
            break;
        case 'DELETE':
            handleDeleteValue($conn, $employer_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Error in company_values.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleGetValues($conn, $employer_id) {
    $sql = "
        SELECT value_id, value_title, value_description, display_order, is_active
        FROM company_values 
        WHERE employer_id = :employer_id AND is_active = 1
        ORDER BY display_order ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['employer_id' => $employer_id]);
    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $values
    ]);
}

function handleCreateValue($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    
    if (empty($title) || empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        return;
    }
    
    // Get next display order
    $order_sql = "SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM company_values WHERE employer_id = :employer_id";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->execute(['employer_id' => $employer_id]);
    $next_order = $order_stmt->fetch(PDO::FETCH_ASSOC)['next_order'];
    
    $sql = "
        INSERT INTO company_values (employer_id, value_title, value_description, display_order)
        VALUES (:employer_id, :title, :description, :display_order)
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'employer_id' => $employer_id,
        'title' => $title,
        'description' => $description,
        'display_order' => $next_order
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Company value added successfully',
            'data' => [
                'value_id' => $conn->lastInsertId(),
                'title' => $title,
                'description' => $description,
                'display_order' => $next_order
            ]
        ]);
    } else {
        throw new Exception('Failed to create company value');
    }
}

function handleUpdateValue($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $value_id = (int)($input['value_id'] ?? 0);
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    
    if (!$value_id || empty($title) || empty($description)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Value ID, title and description are required']);
        return;
    }
    
    $sql = "
        UPDATE company_values 
        SET value_title = :title, value_description = :description, updated_at = NOW()
        WHERE value_id = :value_id AND employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'title' => $title,
        'description' => $description,
        'value_id' => $value_id,
        'employer_id' => $employer_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Company value updated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Company value not found']);
    }
}

function handleDeleteValue($conn, $employer_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $value_id = (int)($input['value_id'] ?? 0);
    
    if (!$value_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Value ID is required']);
        return;
    }
    
    $sql = "
        UPDATE company_values 
        SET is_active = 0, updated_at = NOW()
        WHERE value_id = :value_id AND employer_id = :employer_id
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        'value_id' => $value_id,
        'employer_id' => $employer_id
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Company value deleted successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Company value not found']);
    }
}
?>