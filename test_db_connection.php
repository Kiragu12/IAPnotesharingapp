<?php
/**
 * Database Connection Test File
 * This file tests the connection to the Railway MySQL database
 */

// Include the configuration file
require_once 'conf.php';

echo "<h2>Database Connection Test</h2>\n";
echo "<p><strong>Testing connection to Railway MySQL database...</strong></p>\n";

try {
    // Create PDO connection string with port
    $dsn = "mysql:host=" . $conf['db_host'] . ";port=" . $conf['db_port'] . ";dbname=" . $conf['db_name'] . ";charset=utf8mb4";
    
    // PDO options for better error handling and security
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    // Create PDO instance
    $pdo = new PDO($dsn, $conf['db_user'], $conf['db_pass'], $options);
    
    echo "<div style='color: green; font-weight: bold;'>✅ SUCCESS: Connected to database successfully!</div>\n";
    
    // Display connection details (without password)
    echo "<h3>Connection Details:</h3>\n";
    echo "<ul>\n";
    echo "<li><strong>Host:</strong> " . $conf['db_host'] . "</li>\n";
    echo "<li><strong>Port:</strong> " . $conf['db_port'] . "</li>\n";
    echo "<li><strong>Database:</strong> " . $conf['db_name'] . "</li>\n";
    echo "<li><strong>Username:</strong> " . $conf['db_user'] . "</li>\n";
    echo "<li><strong>Connection Type:</strong> " . $conf['db_type'] . "</li>\n";
    echo "</ul>\n";
    
    // Test a simple query
    echo "<h3>Database Information:</h3>\n";
    
    // Get MySQL version
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $version['version'] . "</p>\n";
    
    // Get current database
    $stmt = $pdo->query('SELECT DATABASE() as current_db');
    $current_db = $stmt->fetch();
    echo "<p><strong>Current Database:</strong> " . $current_db['current_db'] . "</p>\n";
    
    // List all tables in the database
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll();
    
    echo "<h3>Tables in Database:</h3>\n";
    if (count($tables) > 0) {
        echo "<ul>\n";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            echo "<li>" . $tableName . "</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<p><em>No tables found in the database.</em></p>\n";
    }
    
    // Test current time
    $stmt = $pdo->query('SELECT NOW() as current_time');
    $time = $stmt->fetch();
    echo "<p><strong>Database Server Time:</strong> " . $time['current_time'] . "</p>\n";
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ ERROR: Connection failed!</div>\n";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>\n";
    echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>\n";
    
    echo "<h3>Troubleshooting Tips:</h3>\n";
    echo "<ul>\n";
    echo "<li>Check if the database credentials are correct</li>\n";
    echo "<li>Verify that the database server is running</li>\n";
    echo "<li>Ensure your IP address is whitelisted (if applicable)</li>\n";
    echo "<li>Check if the database name exists</li>\n";
    echo "<li>Verify network connectivity to the host</li>\n";
    echo "</ul>\n";
}

echo "<hr>\n";
echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>