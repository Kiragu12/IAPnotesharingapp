<?php
/**
 * Admin Panel Setup Script
 * This script creates the necessary database tables for the admin panel
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

echo "=================================\n";
echo "Admin Panel Setup Script\n";
echo "=================================\n\n";

try {
    $db = new Database($conf);
    
    // Read SQL file
    $sqlFile = 'sql/create_admin_tables.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: SQL file not found at: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "✓ SQL file loaded successfully\n";
    
    // Split statements by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        // Skip empty statements and comments
        if (empty($statement) || strpos(trim($statement), '--') === 0 || strpos(trim($statement), '/*') === 0) {
            continue;
        }
        
        try {
            $db->query($statement);
            $db->execute();
            $successCount++;
            
            // Extract table name for better feedback
            if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                echo "✓ Created table: {$matches[1]}\n";
            } elseif (preg_match('/CREATE INDEX (\w+)/i', $statement, $matches)) {
                echo "✓ Created index: {$matches[1]}\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "✗ Error executing statement: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n\n";
        }
    }
    
    echo "\n=================================\n";
    echo "Setup Complete!\n";
    echo "=================================\n";
    echo "Successful operations: $successCount\n";
    echo "Errors: $errorCount\n\n";
    
    if ($errorCount === 0) {
        echo "✓ All admin tables created successfully!\n\n";
        echo "Next Steps:\n";
        echo "1. Create your first admin user:\n";
        echo "   UPDATE users SET is_admin = 1 WHERE email = 'your-email@example.com';\n\n";
        echo "2. Access admin panel:\n";
        echo "   http://localhost/IAPnotesharingapp-1/views/admin/dashboard.php\n\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
