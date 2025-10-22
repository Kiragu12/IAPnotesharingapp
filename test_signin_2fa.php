<?php
/**
 * Test Signin with 2FA Flow
 * Tests login process, OTP generation, and 2FA verification
 */

echo "<h1>🔐 Testing Signin with 2FA Flow</h1>";
echo "<hr>";

try {
    // Load application first
    require_once 'conf.php';
    require_once 'Global/Database.php';
    require_once 'Global/fncs.php';
    require_once 'Global/SendMail.php';
    require_once 'Proc/auth.php';
    
    $lang = array();
    require_once 'Lang/en.php';
    
    // Initialize objects
    $ObjFncs = new fncs();
    $ObjAuth = new auth();
    $ObjSendMail = new SendMail();
    
    echo "<h2>✅ Step 1: Application loaded successfully</h2>";
    
    // First, create a test user for signin
    $db = new Database($conf);
    $test_email = 'signin_test_' . time() . '@icsbacademy.com';
    $test_password = 'TestPassword123!';
    $test_name = 'Signin Test User';
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    
    echo "<h2>👤 Step 2: Creating test user for signin</h2>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> $test_email</li>";
    echo "<li><strong>Password:</strong> $test_password</li>";
    echo "<li><strong>Name:</strong> $test_name</li>";
    echo "</ul>";
    
    // Insert test user
    $insert_sql = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                   VALUES (:email, :password, :full_name, 1, 1, NOW())";
    $db->execute($insert_sql, [
        ':email' => $test_email,
        ':password' => $hashed_password,
        ':full_name' => $test_name
    ]);
    
    $user_id = $db->getPDO()->lastInsertId();
    echo "<p style='color: green;'>✅ Test user created with ID: $user_id</p>";
    
    echo "<h2>🔄 Step 3: Testing Login Process</h2>";
    
    // Simulate signin POST
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'signin' => '1',
        'email' => $test_email,
        'password' => $test_password,
        'remember_me' => false
    ];
    
    // Start output buffering to catch any redirects
    ob_start();
    
    // Simulate session start
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Process login
    $login_result = $ObjAuth->login($conf, $ObjFncs, $ObjSendMail);
    
    // Get any output
    $output = ob_get_clean();
    
    echo "<p><strong>Login processing result:</strong> " . ($login_result === false ? 'false (redirect expected)' : 'true') . "</p>";
    
    if (!empty($output)) {
        echo "<p><strong>Output during login:</strong> " . htmlspecialchars($output) . "</p>";
    }
    
    // Check if temporary session was set
    if (isset($_SESSION['temp_user_id'])) {
        echo "<p style='color: green;'>✅ Temporary session set - user_id: " . $_SESSION['temp_user_id'] . "</p>";
        echo "<p style='color: green;'>✅ Temp email: " . $_SESSION['temp_user_email'] . "</p>";
        echo "<p style='color: green;'>✅ Temp name: " . $_SESSION['temp_user_name'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Temporary session NOT set</p>";
    }
    
    // Check if OTP was generated in database
    echo "<h2>🔍 Step 4: Checking OTP Generation</h2>";
    $otp_check = $db->fetchOne("SELECT id, code, expires_at, attempts_used, code_type FROM two_factor_codes WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1", [
        ':user_id' => $user_id
    ]);
    
    if ($otp_check) {
        echo "<p style='color: green;'>✅ OTP code generated successfully!</p>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><td><strong>Code ID</strong></td><td>" . $otp_check['id'] . "</td></tr>";
        echo "<tr><td><strong>Code</strong></td><td>" . $otp_check['code'] . "</td></tr>";
        echo "<tr><td><strong>Expires At</strong></td><td>" . $otp_check['expires_at'] . "</td></tr>";
        echo "<tr><td><strong>Attempts Used</strong></td><td>" . $otp_check['attempts_used'] . "</td></tr>";
        echo "<tr><td><strong>Code Type</strong></td><td>" . $otp_check['code_type'] . "</td></tr>";
        echo "</table>";
        
        // Test 2FA verification
        echo "<h2>🔐 Step 5: Testing 2FA Verification</h2>";
        
        // Simulate 2FA form submission
        $_POST = [
            'verify_2fa' => '1',
            'otp_code' => $otp_check['code']
        ];
        
        // Mock the 2FA verification logic
        $verify_sql = "SELECT tfc.id as code_id, tfc.attempts_used, tfc.max_attempts, u.id, u.email, u.full_name 
                      FROM two_factor_codes tfc
                      JOIN users u ON tfc.user_id = u.id
                      WHERE tfc.user_id = :user_id 
                      AND tfc.code = :code 
                      AND tfc.expires_at > NOW()
                      AND tfc.used_at IS NULL
                      ORDER BY tfc.created_at DESC
                      LIMIT 1";
        
        $verify_result = $db->fetchOne($verify_sql, [
            ':user_id' => $user_id,
            ':code' => $otp_check['code']
        ]);
        
        if ($verify_result) {
            echo "<p style='color: green;'>✅ OTP verification would succeed!</p>";
            echo "<p>User would be logged in with:</p>";
            echo "<ul>";
            echo "<li>user_id: " . $verify_result['id'] . "</li>";
            echo "<li>user_email: " . $verify_result['email'] . "</li>";
            echo "<li>user_name: " . $verify_result['full_name'] . "</li>";
            echo "</ul>";
            
            // Mark code as used (simulate)
            $mark_used_sql = "UPDATE two_factor_codes SET used_at = NOW() WHERE id = :code_id";
            $db->execute($mark_used_sql, [':code_id' => $verify_result['code_id']]);
            echo "<p style='color: blue;'>✅ OTP code marked as used</p>";
            
        } else {
            echo "<p style='color: red;'>❌ OTP verification would fail!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ OTP code was NOT generated!</p>";
    }
    
    // Check messages
    $msg = $ObjFncs->getMsg('msg');
    if ($msg) {
        echo "<h3>💬 Messages:</h3>";
        echo "<div style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
        echo $msg;
        echo "</div>";
    }
    
    // Clean up test user
    echo "<h2>🧹 Cleanup</h2>";
    $db->execute("DELETE FROM two_factor_codes WHERE user_id = :user_id", [':user_id' => $user_id]);
    $db->execute("DELETE FROM users WHERE id = :user_id", [':user_id' => $user_id]);
    echo "<p style='color: blue;'>✅ Test user and OTP codes cleaned up</p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Error during signin test:</strong><br>";
    echo $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h2>📊 Summary</h2>";
echo "<p><strong>Expected 2FA Flow:</strong></p>";
echo "<ol>";
echo "<li>User enters email/password on signin.php</li>";
echo "<li>System validates credentials</li>";
echo "<li>System generates 6-digit OTP and saves to two_factor_codes</li>";
echo "<li>System sends OTP via email</li>";
echo "<li>System sets temporary session and redirects to two_factor_auth.php</li>";
echo "<li>User enters OTP on 2FA page</li>";
echo "<li>System verifies OTP and completes login</li>";
echo "<li>User is redirected to dashboard.php</li>";
echo "</ol>";
echo "<p><strong>Test your signin at:</strong> <a href='signin.php'>signin.php</a></p>";
?>