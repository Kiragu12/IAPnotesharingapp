<?php
/**
 * Create Test Account with Known Password
 */

echo "<h1>🧪 Creating Test Account with Known Password</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'Global/Database.php';

try {
    $db = new Database($conf);
    
    // Test account details
    $test_email = 'testlogin@gmail.com';
    $test_password = 'TestLogin123!';
    $test_name = 'Test Login User';
    
    echo "<h2>👤 Test Account Details:</h2>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #2196F3;'>";
    echo "<p><strong>Email:</strong> $test_email</p>";
    echo "<p><strong>Password:</strong> $test_password</p>";
    echo "<p><strong>Name:</strong> $test_name</p>";
    echo "</div>";
    
    // Check if account already exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = :email", [':email' => $test_email]);
    
    if ($existing) {
        echo "<p style='color: orange;'>⚠️ Test account already exists! You can try logging in with the credentials above.</p>";
    } else {
        // Create the account
        $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                VALUES (:email, :password, :full_name, 1, 1, NOW())";
        
        $result = $db->execute($sql, [
            ':email' => $test_email,
            ':password' => $hashed_password,
            ':full_name' => $test_name
        ]);
        
        if ($result > 0) {
            echo "<p style='color: green;'>✅ Test account created successfully!</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to create test account!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>🔑 How to Test Login:</h2>";
    echo "<ol>";
    echo "<li>Go to: <a href='signin.php'><strong>signin.php</strong></a></li>";
    echo "<li>Enter email: <strong>$test_email</strong></li>";
    echo "<li>Enter password: <strong>$test_password</strong></li>";
    echo "<li>Click Sign In</li>";
    echo "<li>You should be redirected to 2FA page</li>";
    echo "<li>Check your email for the OTP code</li>";
    echo "<li>Enter the OTP and complete login</li>";
    echo "</ol>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-top: 15px;'>";
    echo "<h3>📧 Important Note:</h3>";
    echo "<p>Since this is a test Gmail address, you won't actually receive the OTP email. But you can:</p>";
    echo "<ul>";
    echo "<li>See the OTP in your database: <a href='show_tables.php'>Check two_factor_codes table</a></li>";
    echo "<li>Use your real email address for actual testing</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='signin.php' style='display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Try Sign In Now</a></p>";
?>