<?php
/**
 * Secure File Download Handler
 * Handles downloading of uploaded files with proper security checks
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Access denied');
}

require_once '../../config/conf.php';
require_once '../../app/Services/Global/Database.php';

$db = new Database($conf);
$user_id = $_SESSION['user_id'];

// Get file ID from request
$file_id = $_GET['id'] ?? null;

if (!$file_id || !is_numeric($file_id)) {
    http_response_code(400);
    exit('Invalid file ID');
}

try {
    // Get file information from database
    $note = $db->fetchOne(
        "SELECT id, title, note_type, file_path, file_name, file_type, file_size, user_id, is_public 
         FROM notes 
         WHERE id = ? AND note_type = 'file'",
        [$file_id]
    );
    
    if (!$note) {
        http_response_code(404);
        exit('File not found');
    }
    
    // Check permissions - user must own the file OR it must be public
    if ($note['user_id'] != $user_id && !$note['is_public']) {
        http_response_code(403);
        exit('Access denied');
    }
    
    // Construct file path
    $file_path = '../../' . $note['file_path'];
    
    // Security check - ensure file is within uploads directory
    $uploads_dir = realpath('../../uploads/');
    $file_real_path = realpath($file_path);
    
    if (!$file_real_path || strpos($file_real_path, $uploads_dir) !== 0) {
        http_response_code(403);
        exit('Security violation');
    }
    
    // Check if file exists
    if (!file_exists($file_path)) {
        http_response_code(404);
        exit('File not found on server');
    }
    
    // Set appropriate headers for download
    $file_name = $note['file_name'];
    $file_size = filesize($file_path);
    $file_type = $note['file_type'] ?: 'application/octet-stream';
    
    // Clean output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: ' . $file_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Read and output file
    readfile($file_path);
    exit();
    
} catch (Exception $e) {
    error_log('File download error: ' . $e->getMessage());
    http_response_code(500);
    exit('Download failed');
}
?>
