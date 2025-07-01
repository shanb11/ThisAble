<?php
/**
 * Resume File Handler - CLEAN VERSION - No Extra Tabs
 * Backend file: backend/candidate/view_resume.php
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../db.php');

// Security: Check if user is logged in
if (!isset($_SESSION['seeker_id'])) {
    http_response_code(401);
    die('Unauthorized access');
}

$seeker_id = $_SESSION['seeker_id'];

// Get the requested action (view or download)
$action = $_GET['action'] ?? 'view';
$resume_id = $_GET['resume_id'] ?? null;

if (!$resume_id) {
    http_response_code(400);
    die('Resume ID required');
}

try {
    // Fetch resume information with security check
    $stmt = $conn->prepare("
        SELECT r.*, js.seeker_id 
        FROM resumes r 
        JOIN job_seekers js ON r.seeker_id = js.seeker_id 
        WHERE r.resume_id = ? AND r.seeker_id = ? AND r.is_current = 1
    ");
    $stmt->execute([$resume_id, $seeker_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        http_response_code(404);
        die('Resume not found or access denied');
    }
    
    // Construct file path
    $file_path = __DIR__ . '/../../' . $resume['file_path'];
    
    // Security: Validate file path to prevent directory traversal
    $real_path = realpath($file_path);
    $upload_dir = realpath(__DIR__ . '/../../uploads/resumes/');
    
    if (!$real_path || strpos($real_path, $upload_dir) !== 0) {
        http_response_code(403);
        die('Invalid file path');
    }
    
    // Check if file exists
    if (!file_exists($real_path)) {
        http_response_code(404);
        die('File not found on server');
    }
    
    // Get file info
    $file_size = filesize($real_path);
    $file_name = $resume['file_name'];
    $file_type = $resume['file_type'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Handle different actions
    if ($action === 'download') {
        // EXPLICIT DOWNLOAD - Show save dialog
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        ob_clean();
        flush();
        readfile($real_path);
        exit;
        
    } elseif ($action === 'preview' || $action === 'view') {
        // For Word documents, show preview page
        if (in_array($file_extension, ['doc', 'docx'])) {
            showDocumentPreview($resume, $real_path);
            exit;
        }
        
        // For PDFs - serve as HTML page with embedded PDF viewer
        if ($file_extension === 'pdf') {
            showPDFViewer($resume, $real_path);
            exit;
        }
        
        // Fallback for other files - show preview page
        showDocumentPreview($resume, $real_path);
        exit;
    }
    
} catch (Exception $e) {
    error_log('Resume view error: ' . $e->getMessage());
    http_response_code(500);
    die('Server error occurred');
}

/**
 * Show PDF in embedded viewer - NO DOWNLOAD POPUP
 */
function showPDFViewer($resume, $file_path) {
    $file_name = htmlspecialchars($resume['file_name']);
    $resume_id = $resume['resume_id'];
    
    // Create a data URL for the PDF to avoid download prompts
    $pdf_data = base64_encode(file_get_contents($file_path));
    $pdf_data_url = 'data:application/pdf;base64,' . $pdf_data;
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $file_name; ?> - Resume Viewer</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: #f5f5f5;
                height: 100vh;
                display: flex;
                flex-direction: column;
            }
            
            .viewer-header {
                background: white;
                padding: 15px 20px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .file-info {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .file-icon {
                color: #f44336;
                font-size: 20px;
            }
            
            .file-details h1 {
                font-size: 18px;
                color: #257180;
                margin: 0;
            }
            
            .file-details p {
                font-size: 14px;
                color: #666;
                margin: 0;
            }
            
            .viewer-actions {
                display: flex;
                gap: 10px;
            }
            
            .btn {
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }
            
            .btn-secondary {
                background: #FD8B51;
                color: white;
            }
            
            .btn-secondary:hover {
                background: #fc7a3d;
            }
            
            .btn-outline {
                background: transparent;
                color: #666;
                border: 1px solid #ddd;
            }
            
            .btn-outline:hover {
                background: #f8f9fa;
                border-color: #257180;
                color: #257180;
            }
            
            .pdf-viewer {
                flex: 1;
                background: white;
                margin: 10px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            
            .pdf-embed {
                width: 100%;
                height: 100%;
                border: none;
                display: block;
            }
            
            .loading-message {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 200px;
                color: #666;
                font-size: 16px;
            }
            
            .error-message {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 200px;
                color: #666;
                text-align: center;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <div class="viewer-header">
            <div class="file-info">
                <i class="fas fa-file-pdf file-icon"></i>
                <div class="file-details">
                    <h1><?php echo $file_name; ?></h1>
                    <p>PDF Document</p>
                </div>
            </div>
            
            <div class="viewer-actions">
                <button onclick="downloadFile()" class="btn btn-secondary" id="downloadBtn">
                    <i class="fas fa-download"></i> Download
                </button>
                <button onclick="closeTab()" class="btn btn-outline">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
        
        <div class="pdf-viewer">
            <div class="loading-message" id="loadingMessage">
                <i class="fas fa-spinner fa-spin" style="margin-right: 10px;"></i>
                Loading PDF...
            </div>
            
            <object
                data="<?php echo $pdf_data_url; ?>"
                type="application/pdf"
                class="pdf-embed"
                id="pdfEmbed"
                style="display: none;"
                onload="pdfLoaded()"
                onerror="pdfError()">
                
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #FD8B51; margin-bottom: 15px;"></i>
                    <h3>PDF Viewer Not Available</h3>
                    <p>Your browser doesn't support embedded PDF viewing.<br>
                    Please click the Download button to view the file.</p>
                </div>
            </object>
        </div>
        
        <script>
            const downloadUrl = '?action=download&resume_id=<?php echo $resume_id; ?>';
            
            function pdfLoaded() {
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('pdfEmbed').style.display = 'block';
            }
            
            function pdfError() {
                document.getElementById('loadingMessage').style.display = 'none';
                document.getElementById('pdfEmbed').innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #FD8B51; margin-bottom: 15px;"></i>
                        <h3>Cannot Display PDF</h3>
                        <p>Unable to display this PDF in the browser.<br>
                        Please click the Download button to view the file.</p>
                    </div>
                `;
                document.getElementById('pdfEmbed').style.display = 'block';
            }
            
            function downloadFile() {
                const btn = document.getElementById('downloadBtn');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                btn.disabled = true;
                
                // Create download link
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Reset button
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 1500);
                }, 500);
            }
            
            // CLEAN close function - no navigation
            function closeTab() {
                window.close();
            }
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTab();
                }
            });
            
            // Auto-show PDF after short delay
            setTimeout(() => {
                const loadingMsg = document.getElementById('loadingMessage');
                if (loadingMsg.style.display !== 'none') {
                    pdfLoaded();
                }
            }, 3000);
        </script>
    </body>
    </html>
    <?php
}

/**
 * Show document preview page for non-PDF files
 */
function showDocumentPreview($resume, $file_path) {
    $file_name = htmlspecialchars($resume['file_name']);
    $file_size = round($resume['file_size'] / 1024); // KB
    $upload_date = date('F j, Y', strtotime($resume['upload_date']));
    $download_url = '?action=download&resume_id=' . $resume['resume_id'];
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $file_name; ?> - Document Preview</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .preview-container {
                background: white;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                padding: 40px;
                text-align: center;
                max-width: 500px;
                width: 100%;
            }
            
            .file-icon {
                font-size: 4rem;
                color: #2196f3;
                margin-bottom: 20px;
            }
            
            .file-info h1 {
                color: #257180;
                font-size: 24px;
                margin-bottom: 10px;
                word-break: break-word;
            }
            
            .file-meta {
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            
            .preview-message {
                background: #e3f2fd;
                border: 1px solid #2196f3;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 30px;
            }
            
            .preview-message h3 {
                color: #2196f3;
                margin-bottom: 10px;
            }
            
            .preview-message p {
                color: #555;
                line-height: 1.5;
            }
            
            .action-buttons {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .btn {
                padding: 12px 24px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }
            
            .btn-primary {
                background: #257180;
                color: white;
            }
            
            .btn-primary:hover {
                background: #1e5a66;
                transform: translateY(-1px);
            }
            
            .btn-outline {
                background: transparent;
                color: #666;
                border: 1px solid #ddd;
            }
            
            .btn-outline:hover {
                background: #f8f9fa;
                border-color: #257180;
                color: #257180;
            }
        </style>
    </head>
    <body>
        <div class="preview-container">
            <div class="file-icon">
                <i class="fas fa-file-word"></i>
            </div>
            
            <div class="file-info">
                <h1><?php echo $file_name; ?></h1>
                <div class="file-meta">
                    <div><strong>Size:</strong> <?php echo $file_size; ?> KB</div>
                    <div><strong>Uploaded:</strong> <?php echo $upload_date; ?></div>
                </div>
            </div>
            
            <div class="preview-message">
                <h3><i class="fas fa-info-circle"></i> Document Preview</h3>
                <p>Word documents cannot be previewed directly in the browser. Click the download button below to save and view the file.</p>
            </div>
            
            <div class="action-buttons">
                <button onclick="downloadFile()" class="btn btn-primary" id="downloadBtn">
                    <i class="fas fa-download"></i>
                    Download Document
                </button>
                
                <button onclick="closeTab()" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
        
        <script>
            const downloadUrl = '<?php echo $download_url; ?>';
            
            function downloadFile() {
                const btn = document.getElementById('downloadBtn');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                btn.disabled = true;
                
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 1500);
                }, 500);
            }
            
            // CLEAN close function - no navigation
            function closeTab() {
                window.close();
            }
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeTab();
                }
            });
        </script>
    </body>
    </html>
    <?php
}
?>