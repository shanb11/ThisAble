<?php
/**
 * Railway Deployment Diagnostic
 * Place this at: C:\xampp\htdocs\ThisAble\test-railway.php
 * Access: https://thisable-production.up.railway.app/test-railway.php
 */

header('Content-Type: application/json');

$diagnostics = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server_info' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    ],
    'file_structure' => [
        'backend_exists' => file_exists(__DIR__ . '/backend'),
        'backend_candidate_exists' => file_exists(__DIR__ . '/backend/candidate'),
        'upload_resume_exists' => file_exists(__DIR__ . '/backend/candidate/upload_resume_process.php'),
        'config_exists' => file_exists(__DIR__ . '/config/config.php'),
        'db_exists' => file_exists(__DIR . '/backend/db.php'),
    ],
    'environment' => [
        'RAILWAY_ENVIRONMENT' => getenv('RAILWAY_ENVIRONMENT') ?: 'not set',
        'has_mysql_url' => getenv('MYSQL_URL') !== false,   
    ],
    'uploads_directory' => [
        'path' => __DIR__ . '/uploads/resumes',
        'exists' => file_exists(__DIR__ . '/uploads/resumes'),
        'is_writable' => is_writable(__DIR__ . '/uploads/resumes'),
    ]
];

// Try to create uploads directory if it doesn't exist
if (!$diagnostics['uploads_directory']['exists']) {
    $created = mkdir(__DIR__ . '/uploads/resumes', 0755, true);
    $diagnostics['uploads_directory']['created_now'] = $created;
    $diagnostics['uploads_directory']['exists'] = file_exists(__DIR__ . '/uploads/resumes');
    $diagnostics['uploads_directory']['is_writable'] = is_writable(__DIR__ . '/uploads/resumes');
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>