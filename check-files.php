<?php
/**
 * File System Diagnostic for Railway
 * Upload to: C:\xampp\htdocs\ThisAble\check-files.php
 * Access: https://thisable-production.up.railway.app/check-files.php
 */

header('Content-Type: application/json');

$projectRoot = __DIR__;
$uploadsDir = $projectRoot . '/uploads/resumes';

$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => [
        'hostname' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    ],
    'directories' => [
        'project_root' => $projectRoot,
        'uploads_dir' => $uploadsDir,
        'uploads_exists' => file_exists($uploadsDir),
        'uploads_writable' => is_writable($uploadsDir),
    ],
    'files' => [],
    'database_check' => null
];

// List all files in uploads/resumes if directory exists
if (file_exists($uploadsDir)) {
    $files = scandir($uploadsDir);
    $files = array_diff($files, ['.', '..']); // Remove . and ..
    
    $fileDetails = [];
    foreach ($files as $file) {
        $filePath = $uploadsDir . '/' . $file;
        $fileDetails[] = [
            'name' => $file,
            'size' => filesize($filePath),
            'size_formatted' => number_format(filesize($filePath) / 1024, 2) . ' KB',
            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
            'path' => 'uploads/resumes/' . $file
        ];
    }
    
    $diagnostics['files'] = [
        'count' => count($fileDetails),
        'list' => $fileDetails
    ];
} else {
    $diagnostics['files'] = [
        'error' => 'Uploads directory does not exist',
        'attempted_path' => $uploadsDir
    ];
}

// Check database for resume records
try {
    require_once __DIR__ . '/backend/db.php';
    
    $stmt = $conn->query("SELECT resume_id, seeker_id, file_name, file_path, 
                          file_size, upload_date, is_current 
                          FROM resumes 
                          WHERE is_current = 1 
                          ORDER BY upload_date DESC 
                          LIMIT 10");
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check which files exist on disk
    foreach ($resumes as &$resume) {
        $fullPath = $projectRoot . '/' . $resume['file_path'];
        $resume['file_exists_on_disk'] = file_exists($fullPath);
        $resume['full_path'] = $fullPath;
    }
    
    $diagnostics['database_check'] = [
        'success' => true,
        'recent_resumes' => $resumes,
        'total_count' => count($resumes)
    ];
    
} catch (Exception $e) {
    $diagnostics['database_check'] = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>