<?php
/**
 * Set Admin Password Script
 * This script sets/resets the password for the admin user
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

$admin_email = 'admin@noteshareacademy.com';
$admin_password = 'admin123';

try {
    $db = new Database($conf);
    
    // Check if admin user exists
    $stmt = $db->query("SELECT id, full_name, email, is_admin FROM users WHERE email = ?", [$admin_email]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "✗ Admin user not found with email: $admin_email\n";
        echo "Please create the admin user first.\n";
        exit(1);
    }
    
    if ($admin['is_admin'] != 1) {
        echo "✗ User exists but is not an admin. Promoting to admin...\n";
        $db->query("UPDATE users SET is_admin = 1 WHERE email = ?", [$admin_email]);
        echo "✓ User promoted to admin\n";
    }
    
    // Hash and set the password
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = ? WHERE email = ?", [$hashed_password, $admin_email]);
    
    echo "\n=================================\n";
    echo "Admin Password Set Successfully!\n";
    echo "=================================\n\n";
    echo "Email: $admin_email\n";
    echo "Password: $admin_password\n";
    echo "Name: {$admin['full_name']}\n\n";
    echo "You can now login with:\n";
    echo "1. Click 'Use Admin' button on signin page\n";
    echo "2. Check 'Admin Direct Login' toggle\n";
    echo "3. Click 'Sign In to Dashboard'\n";
    echo "4. You'll be redirected directly to admin panel!\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
