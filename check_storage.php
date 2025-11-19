<?php
require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

$db = new Database($conf);

echo "=================================\n";
echo "STORAGE ANALYSIS\n";
echo "=================================\n\n";

// Get total storage
$result = $db->fetchOne("SELECT SUM(file_size) as total FROM notes WHERE note_type = 'file'");
$totalBytes = $result['total'] ?? 0;
$totalMB = round($totalBytes / (1024 * 1024), 2);
$totalKB = round($totalBytes / 1024, 2);

echo "Total Storage Used:\n";
echo "  Bytes: " . number_format($totalBytes) . " bytes\n";
echo "  KB: " . number_format($totalKB, 2) . " KB\n";
echo "  MB: " . number_format($totalMB, 2) . " MB\n\n";

// Count file notes
$result = $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE note_type = 'file'");
$fileCount = $result['count'];

echo "File Notes:\n";
echo "  Total file-type notes: $fileCount\n\n";

// Get breakdown by user
$files = $db->fetchAll("
    SELECT 
        users.full_name,
        notes.title,
        notes.file_size,
        notes.file_name,
        notes.created_at
    FROM notes
    LEFT JOIN users ON notes.user_id = users.id
    WHERE notes.note_type = 'file' AND notes.file_size > 0
    ORDER BY notes.file_size DESC
    LIMIT 10
");

if (!empty($files)) {
    echo "Top 10 Largest Files:\n";
    echo str_pad("User", 25) . str_pad("Title", 30) . str_pad("Size", 15) . "File Name\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($files as $file) {
        $sizeKB = round($file['file_size'] / 1024, 2);
        echo str_pad(substr($file['full_name'] ?? 'Unknown', 0, 24), 25) . 
             str_pad(substr($file['title'], 0, 29), 30) . 
             str_pad($sizeKB . " KB", 15) . 
             ($file['file_name'] ?? 'N/A') . "\n";
    }
} else {
    echo "No file uploads found.\n";
}

echo "\n=================================\n";
