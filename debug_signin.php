<?php
/**
 * Debug Signin Process - Step by Step
 */
session_start();

echo "<h1>🔍 Debug Signin Process</h1>";
echo "<hr>";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📝 Step 1: Form Submitted ✅</h2>";
    echo "<p>POST data received successfully</p>";
    echo "</div>";
    
    // Get form data
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📧 Step 2: Form Data Received</h2>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Password:</strong> " . str_repeat('*', strlen($password)) . " (" . strlen($password) . " characters)</p>";
    echo "</div>";
    
    // Load config and database
    require_once 'conf.php';
    require_once 'Global/Database.php';
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>⚙️ Step 3: Configuration Loaded ✅</h2>";
    echo "<p><strong>Database:</strong> " . $conf['db_name'] . "</p>";
    echo "<p><strong>SMTP Host:</strong> " . $conf['smtp_host'] . "</p>";
    echo "<p><strong>SMTP User:</strong> " . $conf['smtp_user'] . "</p>";
    echo "</div>";
    
    try {
        $db = new Database($conf);
        
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>💾 Step 4: Database Connected ✅</h2>";
        echo "</div>";
        
        // Check if user exists
        $sql = "SELECT id, email, password, full_name FROM users WHERE email = :email LIMIT 1";
        $user = $db->fetchOne($sql, [':email' => $email]);
        
        if (!$user) {
            echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h2>❌ Step 5: User NOT Found</h2>";
            echo "<p>No user exists with email: <strong>" . htmlspecialchars($email) . "</strong></p>";
            echo "<p><strong>Solution:</strong> Create an account first or check the email address.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h2>👤 Step 5: User Found ✅</h2>";
            echo "<p><strong>User ID:</strong> " . $user['id'] . "</p>";
            echo "<p><strong>Name:</strong> " . htmlspecialchars($user['full_name']) . "</p>";
            echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
            echo "</div>";
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>🔐 Step 6: Password Verified ✅</h2>";
                echo "<p>Password is correct!</p>";
                echo "</div>";
                
                // Generate OTP
                $otp_code = sprintf("%06d", mt_rand(100000, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>🔢 Step 7: OTP Generated ✅</h2>";
                echo "<p><strong>OTP Code:</strong> <span style='font-size: 24px; letter-spacing: 5px; color: #667eea; font-weight: bold;'>" . $otp_code . "</span></p>";
                echo "<p><strong>Expires At:</strong> " . $expires_at . "</p>";
                echo "</div>";
                
                // Insert OTP into database
                $sql_insert = "INSERT INTO two_factor_codes (user_id, code, expires_at, attempts_used, code_type) 
                               VALUES (:user_id, :code, :expires_at, 0, 'login')";
                $db->execute($sql_insert, [
                    ':user_id' => $user['id'],
                    ':code' => $otp_code,
                    ':expires_at' => $expires_at
                ]);
                
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>💾 Step 8: OTP Saved to Database ✅</h2>";
                echo "</div>";
                
                // Send email
                require_once 'ClassAutoLoad.php';
                
                $email_body = "
                <html>
                <head>
                    <title>Two-Factor Authentication Code</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
                        .header { color: #667eea; text-align: center; }
                        .code-box { text-align: center; margin: 30px 0; }
                        .code { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px; letter-spacing: 5px; font-size: 2em; display: inline-block; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2 class='header'>Two-Factor Authentication</h2>
                        <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                        <p>Your verification code is:</p>
                        <div class='code-box'>
                            <div class='code'>" . $otp_code . "</div>
                        </div>
                        <p><strong>This code will expire in 10 minutes.</strong></p>
                    </div>
                </body>
                </html>";
                
                $mailCnt = [
                    'name_from' => $conf['site_name'],
                    'mail_from' => $conf['admin_email'],
                    'name_to' => $user['full_name'],
                    'mail_to' => $user['email'],
                    'subject' => 'Two-Factor Authentication Code - ' . $conf['site_name'],
                    'body' => $email_body
                ];
                
                $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
                
                if ($email_sent) {
                    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                    echo "<h2>📧 Step 9: Email Sent Successfully ✅</h2>";
                    echo "<p>OTP has been sent to: <strong>" . htmlspecialchars($user['email']) . "</strong></p>";
                    echo "<p>Check your inbox (and spam folder)!</p>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                    echo "<h2>⚠️ Step 9: Email Failed to Send</h2>";
                    echo "<p>OTP code was generated but email could not be sent.</p>";
                    echo "<p><strong>OTP Code (use this to test):</strong> <span style='font-size: 24px; letter-spacing: 5px; color: #667eea; font-weight: bold;'>" . $otp_code . "</span></p>";
                    echo "</div>";
                }
                
                // Set session variables
                $_SESSION['temp_user_id'] = $user['id'];
                $_SESSION['temp_user_email'] = $user['email'];
                $_SESSION['temp_user_name'] = $user['full_name'];
                
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>🎯 Step 10: Session Variables Set ✅</h2>";
                echo "<p>Ready to redirect to 2FA page!</p>";
                echo "</div>";
                
                echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>✅ ALL STEPS COMPLETED SUCCESSFULLY!</h2>";
                echo "<p><strong>Next:</strong> You should be redirected to the 2FA verification page.</p>";
                echo "<p><a href='two_factor_auth.php' style='display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to 2FA Page →</a></p>";
                echo "</div>";
                
            } else {
                echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>❌ Step 6: Password INCORRECT</h2>";
                echo "<p>The password you entered does not match the password in the database.</p>";
                echo "<p><strong>What to do:</strong></p>";
                echo "<ul>";
                echo "<li>Check if you typed the password correctly</li>";
                echo "<li>Password is case-sensitive</li>";
                echo "<li>Run <a href='setup_my_account.php'>setup_my_account.php</a> to reset the password to: <code>MyPassword123!</code></li>";
                echo "</ul>";
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</h2>";
        echo "</div>";
    }
    
} else {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>ℹ️ No Form Submission Yet</h2>";
    echo "<p>Fill out the form below and click 'Sign In' to start the debug process.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Signin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding: 20px; background: #f5f5f5;">
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 40px auto;">
            <div class="card-header bg-primary text-white">
                <h3>🔍 Debug Signin Form</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="macharia.kiragu@strathmore.edu" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               value="MyPassword123!" required>
                    </div>
                    
                    <button type="submit" name="signin" class="btn btn-primary w-100">
                        🔍 Debug Sign In Process
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p><strong>Quick Links:</strong></p>
                    <a href="setup_my_account.php" class="btn btn-sm btn-secondary">Setup Account</a>
                    <a href="signin.php" class="btn btn-sm btn-success">Regular Signin</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
