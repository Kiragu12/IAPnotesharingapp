<?php
/**
 * Create missing remember_tokens table
 * This table is referenced in auth.php but doesn't exist in the database
 */

require_once 'conf.php';
require_once 'Global/Database.php';

echo "<h1>🔧 Creating Missing remember_tokens Table</h1>";
echo "<hr>";

try {
    $db = new Database($conf);
    
    // Check if table already exists
    $check_sql = "SHOW TABLES LIKE 'remember_tokens'";
    $exists = $db->fetchOne($check_sql);
    
    if ($exists) {
        echo "<p style='color: orange;'>⚠️ Table 'remember_tokens' already exists!</p>";
    } else {
        // Create the table
        $create_sql = "
        CREATE TABLE remember_tokens (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            device_info TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token (token),
            INDEX idx_expires_at (expires_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->query($create_sql);
        
        echo "<p style='color: green;'>✅ Table 'remember_tokens' created successfully!</p>";
        echo "<h3>Table Structure:</h3>";
        echo "<ul>";
        echo "<li><strong>id:</strong> Primary key</li>";
        echo "<li><strong>user_id:</strong> Foreign key to users table</li>";
        echo "<li><strong>token:</strong> Unique remember-me token (64 chars)</li>";
        echo "<li><strong>expires_at:</strong> When token expires (30 days)</li>";
        echo "<li><strong>device_info:</strong> User agent string</li>";
        echo "<li><strong>ip_address:</strong> IP address when token created</li>";
        echo "<li><strong>created_at:</strong> When token was created</li>";
        echo "<li><strong>last_used_at:</strong> When token was last used</li>";
        echo "</ul>";
    }
    
    // Verify the table structure
    echo "<h3>Verifying Table Structure:</h3>";
    $describe_sql = "DESCRIBE remember_tokens";
    $columns = $db->fetchAll($describe_sql);
    
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<p style='color: green; font-size: 18px;'>✅ <strong>remember_tokens table is now ready!</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}