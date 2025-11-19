<?php
require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

$db = new Database($conf);

echo "Creating deleted_notes table...\n";

$sql = "CREATE TABLE IF NOT EXISTS deleted_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_note_id INT,
    user_id INT,
    title VARCHAR(255),
    content TEXT,
    file_path VARCHAR(500),
    note_type ENUM('text','file'),
    deleted_by INT,
    deletion_reason VARCHAR(255),
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP
)";

try {
    $db->execute($sql);
    echo "Table deleted_notes created successfully.\n";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>