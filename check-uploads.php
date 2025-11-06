<?php
header('Content-Type: application/json');

$uploadsDir = __DIR__ . '/uploads/resumes/';

$info = [
    'uploads_dir' => $uploadsDir,
    'exists' => file_exists($uploadsDir),
    'is_writable' => is_writable($uploadsDir),
    'files' => []
];

if (file_exists($uploadsDir)) {
    $files = scandir($uploadsDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $uploadsDir . $file;
            $info['files'][] = [
                'name' => $file,
                'size' => filesize($filePath),
                'exists' => file_exists($filePath),
                'readable' => is_readable($filePath)
            ];
        }
    }
}

echo json_encode($info, JSON_PRETTY_PRINT);
?>
