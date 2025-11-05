<?php
/**
 * Railway Deployment Test
 * Access: https://thisable-production.up.railway.app/test-deployment.php
 */

header('Content-Type: application/json');

$result = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Railway deployment is working',
    'php_version' => phpversion(),
    'file_checks' => [
        'upload_resume_process' => file_exists(__DIR__ . '/backend/candidate/upload_resume_process.php'),
        'save_setup_data' => file_exists(__DIR__ . '/backend/candidate/save_setup_data.php'),
        'db_connection' => file_exists(__DIR__ . '/backend/db.php'),
    ],
    'directory_structure' => [
        'current_dir' => __DIR__,
        'files' => scandir(__DIR__)
    ]
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>