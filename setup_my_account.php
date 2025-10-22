<?php
/**
 * Setup Your Personal Account
 */

echo "<h1>👤 Setting Up Your Account</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'Global/Database.php';

try {
    $db = new Database($conf);
    
    // Your account details
    $your_email = 'macharia.kiragu@strathmore.edu';
    $your_password = 'MyPassword123!';
    $your_name = 'Macharia Kiragu';
    
    echo "<h2>🔑 Your Login Credentials:</h2>";
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; border-left: 4px solid #2196F3; margin: 20px 0;'>";
    echo "<p><strong>📧 Email:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 4px; font-size: 16px;'>$your_email</code></p>";
    echo "<p><strong>🔒 Password:</strong> <code style='background: #f0f0f0; padding: 5px 10px; border-radius: 4px; font-size: 16px;'>$your_password</code></p>";
    echo "<p><strong>👤 Name:</strong> $your_name</p>";
    echo "</div>";
    
    // Check if account already exists
    $existing = $db->fetchOne("SELECT id, email FROM users WHERE email = :email", [':email' => $your_email]);
    
    if ($existing) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
        echo "<h3>⚠️ Account Already Exists</h3>";
        echo "<p>An account with email <strong>$your_email</strong> already exists (ID: " . $existing['id'] . ").</p>";
        echo "<p>Updating password and details...</p>";
        echo "</div>";
        
        // Update password and details for existing account
        $hashed_password = password_hash($your_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = :password, full_name = :full_name, email_verified = 1, is_2fa_enabled = 1 WHERE email = :email";
        $db->execute($update_sql, [
            ':password' => $hashed_password,
            ':full_name' => $your_name,
            ':email' => $your_email
        ]);
        
        echo "<p style='color: green;'>✅ Account updated successfully!</p>";
        
    } else {
        // Create new account
        $hashed_password = password_hash($your_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                VALUES (:email, :password, :full_name, 1, 1, NOW())";
        
        $result = $db->execute($sql, [
            ':email' => $your_email,
            ':password' => $hashed_password,
            ':full_name' => $your_name
        ]);
        
        if ($result > 0) {
            $user_id = $db->getPDO()->lastInsertId();
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
            echo "<h3>✅ Account Created Successfully!</h3>";
            echo "<p>Your account has been created with ID: <strong>$user_id</strong></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create account!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>📧 Email Configuration Updated:</h2>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>";
    echo "<p>✅ SMTP configured with your Strathmore email</p>";
    echo "<p>✅ App password configured: <code>efhx vpxm hgij ncog</code></p>";
    echo "<p>✅ Admin email set to: <code>$your_email</code></p>";
    echo "<p><strong>You will now receive real OTP emails when you sign in!</strong></p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h2>🚀 Next Steps:</h2>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>";
    echo "<ol style='font-size: 16px;'>";
    echo "<li><strong>Go to Sign In:</strong> <a href='signin.php' style='color: #007bff; font-weight: bold;'>Click here</a></li>";
    echo "<li><strong>Enter your email:</strong> <code>$your_email</code></li>";
    echo "<li><strong>Enter your password:</strong> <code>$your_password</code></li>";
    echo "<li><strong>Click Sign In</strong></li>";
    echo "<li><strong>Check your Strathmore email</strong> for the OTP code (check spam folder too!)</li>";
    echo "<li><strong>Enter the OTP</strong> on the 2FA verification page</li>";
    echo "<li><strong>Access Dashboard</strong> and see the welcome message!</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8; margin-top: 20px;'>";
    echo "<h3>💡 Important Notes:</h3>";
    echo "<ul>";
    echo "<li>OTP emails will be sent from: <strong>$your_email</strong></li>";
    echo "<li>OTP codes expire after <strong>10 minutes</strong></li>";
    echo "<li>You get <strong>3 attempts</strong> to enter the correct OTP</li>";
    echo "<li>If email doesn't arrive, click 'Resend Code' on the 2FA page</li>";
    echo "<li>Welcome emails will also be sent from your Strathmore address</li>";
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
echo "<a href='signup.php' style='display: inline-block; background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;'>➕ Create New Account</a>";
echo "<a href='show_tables.php' style='display: inline-block; background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px;'>📊 View Database</a>";
echo "</p>";
?>
