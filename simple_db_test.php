<?php
/**
 * Simple Database Connection Test
 * Clean test without problematic queries
 */

require_once 'conf.php';

echo "=== Database Connection Test ===\n";
echo "Testing Railway MySQL Database Connection...\n\n";

try {
    $dsn = "mysql:host=" . $conf['db_host'] . ";port=" . $conf['db_port'] . ";dbname=" . $conf['db_name'] . ";charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $conf['db_user'], $conf['db_pass'], $options);
    
    echo "✅ SUCCESS: Database connection established!\n\n";
    
    echo "Connection Details:\n";
    echo "- Host: " . $conf['db_host'] . "\n";
    echo "- Port: " . $conf['db_port'] . "\n";
    echo "- Database: " . $conf['db_name'] . "\n";
    echo "- Username: " . $conf['db_user'] . "\n\n";
    
    // Get MySQL version
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "MySQL Version: " . $version['version'] . "\n\n";
    
    // List tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll();
    
    echo "Tables in database:\n";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "- " . $tableName . "\n";
    }
    
    echo "\n🎉 Database is ready for use!\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>