<?php
/**
 * Check if favorites table exists and show its structure
 */

require_once __DIR__ . '/../config/conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';

try {
    $db = new Database($conf);
    
    // Check if favorites table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'favorites'", []);
    
    if (empty($tables)) {
        echo "❌ ERROR: 'favorites' table does NOT exist!\n\n";
        echo "Creating favorites table...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            note_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, note_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
            INDEX idx_user_favorites (user_id),
            INDEX idx_note_favorites (note_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->query($sql, []);
        echo "✅ Favorites table created successfully!\n";
    } else {
        echo "✅ 'favorites' table exists!\n\n";
        
        // Show table structure
        echo "Table structure:\n";
        $columns = $db->fetchAll("DESCRIBE favorites", []);
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Extra']}\n";
        }
        
        // Count favorites
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM favorites", []);
        echo "\nTotal favorites: " . $count['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
