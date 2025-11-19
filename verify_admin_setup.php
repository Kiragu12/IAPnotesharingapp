<?php
/**
 * Verify Admin Setup
 * This script checks if admin tables were created successfully
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

echo "=================================\n";
echo "Admin Setup Verification\n";
echo "=================================\n\n";

try {
    $db = new Database($conf);
    
    // Check for admin tables
    $tables = [
        'admin_activity_logs',
        'user_suspensions',
        'flagged_notes',
        'system_statistics',
        'admin_notifications'
    ];
    
    echo "Checking database tables:\n";
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "✓ $table - {$result['count']} records\n";
        } catch (Exception $e) {
            echo "✗ $table - NOT FOUND\n";
        }
    }
    
    // Check users table for is_admin column
    echo "\nChecking users table:\n";
    try {
        $stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
        $result = $stmt->fetch();
        echo "✓ Total users: {$result['total_users']}\n";
        
        $stmt = $db->query("SELECT COUNT(*) as admin_users FROM users WHERE is_admin = 1");
        $result = $stmt->fetch();
        echo "✓ Admin users: {$result['admin_users']}\n";
        
        if ($result['admin_users'] == 0) {
            echo "\n⚠ No admin users found!\n";
            echo "   Run: php create_admin.php your-email@example.com\n";
        } else {
            // Show admin users
            echo "\nAdmin Users:\n";
            $stmt = $db->query("SELECT id, full_name, email FROM users WHERE is_admin = 1");
            while ($user = $stmt->fetch()) {
                echo "  - {$user['full_name']} ({$user['email']})\n";
            }
        }
    } catch (Exception $e) {
        echo "✗ Error checking users: " . $e->getMessage() . "\n";
    }
    
    // Check if required files exist
    echo "\nChecking admin files:\n";
    $files = [
        'app/Controllers/AdminController.php',
        'app/Middleware/AdminMiddleware.php',
        'views/admin/dashboard.php',
        'views/admin/users.php',
        'views/admin/notes.php',
        'views/admin/flagged.php',
        'views/admin/analytics.php',
        'views/admin/categories.php',
        'views/admin/activity.php',
        'views/admin/export.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "✓ $file\n";
        } else {
            echo "✗ $file - MISSING\n";
        }
    }
    
    echo "\n=================================\n";
    echo "Verification Complete!\n";
    echo "=================================\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
