<?php
/**
 * Get Document Categories API
 * Save as: backend/candidate/get_document_categories.php
 */

require_once '../db.php';

header('Content-Type: application/json');

try {
    // Get all document categories
    $sql = "
        SELECT 
            category_id,
            category_name,
            category_type,
            display_order
        FROM document_categories 
        WHERE is_active = 1 
        ORDER BY category_type, display_order, category_name
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group categories by type
    $grouped_categories = [
        'degree_field' => [],
        'certification_type' => [],
        'license_type' => []
    ];

    foreach ($categories as $category) {
        $grouped_categories[$category['category_type']][] = [
            'id' => $category['category_id'],
            'name' => $category['category_name'],
            'value' => strtolower(str_replace(' ', '_', $category['category_name']))
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'categories' => $grouped_categories,
            'document_types' => [
                ['value' => 'diploma', 'label' => 'Diploma/Degree'],
                ['value' => 'certificate', 'label' => 'Certificate'],
                ['value' => 'license', 'label' => 'License'],
                ['value' => 'other', 'label' => 'Other Document']
            ]
        ],
        'message' => 'Categories retrieved successfully'
    ]);

} catch (Exception $e) {
    error_log("Get document categories error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve document categories'
    ]);
}
?>