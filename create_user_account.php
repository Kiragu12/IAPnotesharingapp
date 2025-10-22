<?php
/**
 * Create Test Account for User Login
 */

echo "<h1>👤 Creating Your Test Account</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'Global/Database.php';

try {
    $db = new Database($conf);
    
    // Your test account details
    $test_email = 'testuser@gmail.com';
    $test_password = 'MyPassword123!';
    $test_name = 'Test User Account';
    
    echo "<h2>🔑 Your Login Credentials:</h2>";
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; border-left: 4px solid #2196F3; margin: 20px 0;'>";
    echo "<p><strong>📧 Email:</strong> <code style='background: #f0f0f0; padding: 2px 8px; border-radius: 4px;'>$test_email</code></p>";
    echo "<p><strong>🔒 Password:</strong> <code style='background: #f0f0f0; padding: 2px 8px; border-radius: 4px;'>$test_password</code></p>";
    echo "<p><strong>👤 Name:</strong> $test_name</p>";
    echo "</div>";
    
    // Check if account already exists
    $existing = $db->fetchOne("SELECT id, email FROM users WHERE email = :email", [':email' => $test_email]);
    
    if ($existing) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
        echo "<h3>⚠️ Account Already Exists</h3>";
        echo "<p>An account with email <strong>$test_email</strong> already exists (ID: " . $existing['id'] . ").</p>";
        echo "<p>You can use the credentials above to log in, or I can update the password.</p>";
        echo "</div>";
        
        // Update password for existing account
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = :password, full_name = :full_name WHERE email = :email";
        $db->execute($update_sql, [
            ':password' => $hashed_password,
            ':full_name' => $test_name,
            ':email' => $test_email
        ]);
        
        echo "<p style='color: green;'>✅ Password updated for existing account!</p>";
        
    } else {
        // Create new account
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                VALUES (:email, :password, :full_name, 1, 1, NOW())";
        
        $result = $db->execute($sql, [
            ':email' => $test_email,
            ':password' => $hashed_password,
            ':full_name' => $test_name
        ]);
        
        if ($result > 0) {
            $user_id = $db->getPDO()->lastInsertId();
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
            echo "<h3>✅ Account Created Successfully!</h3>";
            echo "<p>Your test account has been created with ID: <strong>$user_id</strong></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create account!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>🚀 How to Test Your Account:</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
    echo "<ol style='font-size: 16px;'>";
    echo "<li><strong>Go to Sign In Page:</strong> <a href='signin.php' target='_blank' style='color: #007bff; font-weight: bold;'>Click here to open signin.php</a></li>";
    echo "<li><strong>Enter Email:</strong> <code>$test_email</code></li>";
    echo "<li><strong>Enter Password:</strong> <code>$test_password</code></li>";
    echo "<li><strong>Click 'Sign In'</strong></li>";
    echo "<li><strong>Check for 2FA:</strong> You'll be redirected to the 2FA page</li>";
    echo "<li><strong>Get OTP Code:</strong> Check your email OR view the database table</li>";
    echo "<li><strong>Enter OTP:</strong> Complete the 2FA verification</li>";
    echo "<li><strong>Access Dashboard:</strong> You should see the welcome message!</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-top: 20px;'>";
    echo "<h3>📧 Note About Email:</h3>";
    echo "<p>Since <code>$test_email</code> is a test email, you won't receive the actual OTP email. You can:</p>";
    echo "<ul>";
    echo "<li><strong>Check Database:</strong> <a href='show_tables.php' target='_blank'>View two_factor_codes table</a> to see the generated OTP</li>";
    echo "<li><strong>Use Real Email:</strong> Create another account with your real email address for actual email testing</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 15px; border-radius: 8px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h2>🔗 Quick Actions:</h2>";
echo "<p>";
echo "<a href='signin.php' style='display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px; font-weight: bold;'>🔓 Sign In Now</a>";
echo "<a href='show_tables.php' style='display: inline-block; background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;'>📊 View Database</a>";
echo "<a href='signup.php' style='display: inline-block; background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px;'>➕ Create New Account</a>";
echo "</p>";
?>