<?php
/**
 * List All Database Tables
 * Shows all tables in the database with their structure
 */

require_once 'conf.php';
require_once 'Global/Database.php';

echo "<h1>Database Tables Overview</h1>";
echo "<p><strong>Database:</strong> " . htmlspecialchars($conf['db_name']) . "</p>";
echo "<hr>";

try {
    $db = new Database($conf);
    
    // Get all tables
    $sql = "SHOW TABLES";
    $tables = $db->fetchAll($sql);
    
    echo "<h2>Found " . count($tables) . " Tables:</h2>";
    
    $table_key = 'Tables_in_' . $conf['db_name'];
    
    foreach ($tables as $index => $table) {
        $table_name = $table[$table_key];
        
        echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 10px; border-left: 4px solid #667eea;'>";
        echo "<h3 style='color: #667eea;'>Table " . ($index + 1) . ": " . htmlspecialchars($table_name) . "</h3>";
        
        // Get table structure
        $sql_desc = "DESCRIBE " . $table_name;
        $columns = $db->fetchAll($sql_desc);
        
        echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; border-collapse: collapse;'>";
        echo "<thead style='background: #667eea; color: white;'>";
        echo "<tr>";
        echo "<th>Field</th>";
        echo "<th>Type</th>";
        echo "<th>Null</th>";
        echo "<th>Key</th>";
        echo "<th>Default</th>";
        echo "<th>Extra</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($columns as $col) {
            echo "<tr style='background: white;'>";
            echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        
        // Get row count
        $sql_count = "SELECT COUNT(*) as total FROM " . $table_name;
        $count = $db->fetchOne($sql_count);
        
        echo "<p style='margin-top: 10px;'>";
        echo "<strong>Total Records:</strong> " . $count['total'];
        echo "</p>";
        
        echo "</div>";
    }
    
    // Summary
    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border-left: 4px solid #28a745;'>";
    echo "<h2 style='color: #28a745;'>✅ Database Summary</h2>";
    echo "<ul style='font-size: 16px;'>";
    echo "<li><strong>Total Tables:</strong> " . count($tables) . "</li>";
    
    $total_records = 0;
    foreach ($tables as $table) {
        $table_name = $table[$table_key];
        $sql_count = "SELECT COUNT(*) as total FROM " . $table_name;
        $count = $db->fetchOne($sql_count);
        $total_records += $count['total'];
    }
    
    echo "<li><strong>Total Records (All Tables):</strong> " . $total_records . "</li>";
    echo "<li><strong>Database Engine:</strong> MySQL/MariaDB</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border-left: 4px solid #dc3545;'>";
    echo "<h2 style='color: #dc3545;'>❌ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
