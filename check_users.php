<?php
/**
 * Check Current Database Users and Test Login
 */

echo "<h1>🔍 Checking Current Database Users</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'Global/Database.php';

try {
    $db = new Database($conf);
    
    // Get all current users
    $users = $db->fetchAll('SELECT id, email, full_name, email_verified, is_2fa_enabled, created_at FROM users ORDER BY created_at DESC');
    
    echo "<h2>👥 Current Users in Database:</h2>";
    
    if (empty($users)) {
        echo "<p style='color: orange;'>⚠️ No users found in database!</p>";
        echo "<p>You'll need to create an account first at: <a href='signup.php'>signup.php</a></p>";
    } else {
        echo "<p><strong>Found " . count($users) . " user(s):</strong></p>";
        
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Email</th><th>Full Name</th><th>Email Verified</th><th>2FA Enabled</th><th>Created At</th>";
        echo "</tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td><strong>" . $user['id'] . "</strong></td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['full_name'] . "</td>";
            echo "<td>" . ($user['email_verified'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>" . ($user['is_2fa_enabled'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<hr>";
        echo "<h2>🔐 Can You Login?</h2>";
        
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
        echo "<h3>⚠️ Important - Password Issue</h3>";
        echo "<p><strong>Problem:</strong> The users in your database were likely created during testing, and we don't know their actual passwords.</p>";
        echo "<p><strong>Why:</strong> Passwords are hashed in the database for security, so we can't see the original passwords.</p>";
        echo "</div>";
        
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; margin-top: 15px;'>";
        echo "<h3>💡 Solutions:</h3>";
        echo "<ol>";
        echo "<li><strong>Create a New Account:</strong> Go to <a href='signup.php'>signup.php</a> and create a fresh account with a password you know</li>";
        echo "<li><strong>Test with Known Credentials:</strong> If any of these emails are yours and you remember the password, try logging in</li>";
        echo "<li><strong>Reset the Database:</strong> Clear all users and start fresh</li>";
        echo "</ol>";
        echo "</div>";
        
        // Check if any test users exist
        $test_users = [];
        foreach ($users as $user) {
            if (strpos($user['email'], 'test') !== false || strpos($user['email'], 'demo') !== false) {
                $test_users[] = $user;
            }
        }
        
        if (!empty($test_users)) {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-top: 15px;'>";
            echo "<h3>🧪 Test Users Found:</h3>";
            echo "<p>These look like test accounts that were created during development:</p>";
            echo "<ul>";
            foreach ($test_users as $test_user) {
                echo "<li><strong>" . $test_user['email'] . "</strong> (Created: " . $test_user['created_at'] . ")</li>";
            }
            echo "</ul>";
            echo "<p><em>These were likely created with random passwords during testing.</em></p>";
            echo "</div>";
        }
        
        echo "<hr>";
        echo "<h2>🎯 Recommended Next Steps:</h2>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
        echo "<ol>";
        echo "<li><strong>Create Your Real Account:</strong> Go to <a href='signup.php' style='font-weight: bold;'>signup.php</a></li>";
        echo "<li><strong>Use Your Real Email:</strong> Use one of the allowed domains:</li>";
        echo "<ul>";
        foreach ($conf['valid_email_domain'] as $domain) {
            echo "<li>your-name@<strong>$domain</strong></li>";
        }
        echo "</ul>";
        echo "<li><strong>Choose a Password You'll Remember:</strong> Minimum " . $conf['min_password_length'] . " characters</li>";
        echo "<li><strong>Complete 2FA:</strong> Check your email for the OTP code</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🔗 Quick Links:</h2>";
echo "<p>";
echo "<a href='signup.php' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Create New Account</a>";
echo "<a href='signin.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try to Sign In</a>";
echo "</p>";
?>