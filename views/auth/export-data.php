<?php
/**
 * Export User Data - Download all user notes and account information
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

require_once __DIR__ . '/../../config/conf.php';
require_once __DIR__ . '/../../app/Services/Global/Database.php';

$db = new Database($conf);
$user_id = (int) $_SESSION['user_id'];

try {
    // Get user information
    $user_data = $db->fetchOne(
        'SELECT id, full_name, email, created_at, status FROM users WHERE id = ? LIMIT 1', 
        [$user_id]
    );
    
    // Get all user notes
    $notes = $db->fetchAll(
        'SELECT id, title, content, note_type, file_path, file_name, tags, is_public, status, created_at, updated_at 
         FROM notes WHERE user_id = ? ORDER BY created_at DESC', 
        [$user_id]
    );
    
    // Prepare export data
    $export_data = [
        'export_info' => [
            'exported_at' => date('Y-m-d H:i:s'),
            'export_version' => '1.0',
            'application' => 'NotesShare Academy'
        ],
        'user_profile' => $user_data,
        'notes' => $notes,
        'statistics' => [
            'total_notes' => count($notes),
            'public_notes' => count(array_filter($notes, fn($n) => $n['is_public'] == 1)),
            'private_notes' => count(array_filter($notes, fn($n) => $n['is_public'] == 0)),
            'text_notes' => count(array_filter($notes, fn($n) => $n['note_type'] == 'text')),
            'file_notes' => count(array_filter($notes, fn($n) => $n['note_type'] == 'file'))
        ]
    ];
    
    // Set headers for download
    $filename = 'noteshare_data_' . $user_data['full_name'] . '_' . date('Y-m-d_H-i-s') . '.json';
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename); // Sanitize filename
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Output JSON data
    echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
    
} catch (Exception $e) {
    error_log('Export data error: ' . $e->getMessage());
    header('Location: settings.php?error=export_failed');
    exit();
}
?>
