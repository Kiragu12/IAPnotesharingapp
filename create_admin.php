<?php
/**
 * Create First Admin User Script
 * Usage: php create_admin.php email@example.com
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

// Get email from command line
$email = $argv[1] ?? null;

if (!$email) {
    echo "Usage: php create_admin.php email@example.com\n";
    echo "Example: php create_admin.php admin@noteshare.com\n";
    exit(1);
}

try {
    $db = new Database($conf);
    
    // Check if user exists
    $stmt = $db->query("SELECT id, full_name, email, is_admin FROM users WHERE email = ?", [$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "✗ User not found with email: $email\n";
        echo "\nPlease register this user first, then run this script again.\n";
        exit(1);
    }
    
    if ($user['is_admin'] == 1) {
        echo "✓ User '{$user['full_name']}' is already an admin!\n";
        exit(0);
    }
    
    // Make user admin
    $db->query("UPDATE users SET is_admin = 1 WHERE email = ?", [$email]);
    
    echo "=================================\n";
    echo "Admin User Created Successfully!\n";
    echo "=================================\n\n";
    echo "Name: {$user['full_name']}\n";
    echo "Email: {$user['email']}\n";
    echo "User ID: {$user['id']}\n\n";
    echo "You can now access the admin panel at:\n";
    echo "http://localhost/IAPnotesharingapp-1/views/admin/dashboard.php\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
