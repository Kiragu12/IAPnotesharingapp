<?php
/**
 * Debug 2FA OTP Verification
 */
session_start();

echo "<h1>🔐 Debug 2FA Verification</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'Global/Database.php';
require_once 'Global/fncs.php';

$ObjFncs = new fncs();

// Check if form is submitted
if (isset($_POST['verify_2fa'])) {
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📝 Step 1: Form Submitted ✅</h2>";
    echo "</div>";
    
    $user_id = $_SESSION['temp_user_id'] ?? 0;
    $otp_code = trim($_POST['otp_code']);
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📊 Step 2: Session & Form Data</h2>";
    echo "<p><strong>Temp User ID:</strong> " . ($user_id ? $user_id : '<span style="color: red;">NOT SET</span>') . "</p>";
    echo "<p><strong>OTP Code Entered:</strong> " . htmlspecialchars($otp_code) . "</p>";
    echo "<p><strong>OTP Length:</strong> " . strlen($otp_code) . " characters</p>";
    echo "<p><strong>Session Data:</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    echo "</div>";
    
    if (empty($user_id)) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>❌ ERROR: No User ID in Session</h2>";
        echo "<p>The temp_user_id is not set in the session. This means:</p>";
        echo "<ul>";
        echo "<li>You didn't sign in first</li>";
        echo "<li>Your session expired</li>";
        echo "<li>The login process didn't complete</li>";
        echo "</ul>";
        echo "<p><a href='signin.php'>Go back to Sign In</a></p>";
        echo "</div>";
    } elseif (empty($otp_code)) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>❌ ERROR: OTP Code is Empty</h2>";
        echo "</div>";
    } elseif (!preg_match('/^\d{6}$/', $otp_code)) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>❌ ERROR: Invalid OTP Format</h2>";
        echo "<p>OTP must be exactly 6 digits. You entered: <strong>" . htmlspecialchars($otp_code) . "</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h2>✅ Step 3: OTP Format Valid</h2>";
        echo "</div>";
        
        try {
            $db = new Database($conf);
            
            echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h2>✅ Step 4: Database Connected</h2>";
            echo "</div>";
            
            // First, let's see ALL OTP codes for this user
            $all_codes_sql = "SELECT id, code, expires_at, used_at, attempts_used, max_attempts, created_at 
                              FROM two_factor_codes 
                              WHERE user_id = :user_id 
                              ORDER BY created_at DESC";
            $all_codes = $db->fetchAll($all_codes_sql, [':user_id' => $user_id]);
            
            echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h2>📋 All OTP Codes for User ID " . $user_id . ":</h2>";
            if (empty($all_codes)) {
                echo "<p style='color: red;'><strong>NO CODES FOUND!</strong> This means no OTP was generated during login.</p>";
            } else {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<tr style='background: #f8f9fa;'>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Code</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Created</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Expires</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Used?</th>";
                echo "<th style='border: 1px solid #ddd; padding: 8px;'>Attempts</th>";
                echo "</tr>";
                foreach ($all_codes as $code) {
                    $is_current = ($code['code'] == $otp_code);
                    $style = $is_current ? 'background: #d4edda; font-weight: bold;' : '';
                    echo "<tr style='$style'>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $code['code'] . ($is_current ? ' 👈 YOU ENTERED THIS' : '') . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $code['created_at'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $code['expires_at'] . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($code['used_at'] ? 'YES (' . $code['used_at'] . ')' : 'NO') . "</td>";
                    echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $code['attempts_used'] . "/" . $code['max_attempts'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            echo "</div>";
            
            // Now check the specific code
            $sql = "SELECT tfc.id as code_id, tfc.attempts_used, tfc.max_attempts, tfc.expires_at, tfc.used_at, u.id, u.email, u.full_name 
                    FROM two_factor_codes tfc
                    JOIN users u ON tfc.user_id = u.id
                    WHERE tfc.user_id = :user_id 
                    AND tfc.code = :code 
                    AND tfc.expires_at > NOW()
                    AND tfc.used_at IS NULL
                    ORDER BY tfc.created_at DESC
                    LIMIT 1";
            
            $result = $db->fetchOne($sql, [
                ':user_id' => $user_id,
                ':code' => $otp_code
            ]);
            
            if (!$result) {
                echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>❌ Step 5: Invalid or Expired Code</h2>";
                echo "<p><strong>The code <code>" . htmlspecialchars($otp_code) . "</code> is either:</strong></p>";
                echo "<ul>";
                echo "<li>Incorrect (doesn't match any code in database)</li>";
                echo "<li>Expired (older than 10 minutes)</li>";
                echo "<li>Already used</li>";
                echo "</ul>";
                echo "<p><strong>Current server time:</strong> " . date('Y-m-d H:i:s') . "</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                echo "<h2>✅ Step 5: Valid Code Found!</h2>";
                echo "<p><strong>Code ID:</strong> " . $result['code_id'] . "</p>";
                echo "<p><strong>User:</strong> " . htmlspecialchars($result['full_name']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($result['email']) . "</p>";
                echo "<p><strong>Attempts Used:</strong> " . $result['attempts_used'] . "/" . $result['max_attempts'] . "</p>";
                echo "<p><strong>Expires At:</strong> " . $result['expires_at'] . "</p>";
                echo "</div>";
                
                // Check attempts
                $max_attempts = $result['max_attempts'] ?? 3;
                if ($result['attempts_used'] >= $max_attempts) {
                    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                    echo "<h2>❌ Too Many Attempts</h2>";
                    echo "<p>You've used all " . $max_attempts . " attempts. Request a new code.</p>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                    echo "<h2>🎉 SUCCESS! Login would complete now!</h2>";
                    echo "<p>In the real page, you would be logged in and redirected to the dashboard.</p>";
                    echo "<p><a href='dashboard.php' style='display: inline-block; background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Go to Dashboard</a></p>";
                    echo "</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
            echo "<h2>❌ Database Error</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>ℹ️ No Form Submission</h2>";
    echo "<p>Fill out the form below to test OTP verification.</p>";
    echo "</div>";
    
    // Show current session info
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>📊 Current Session Status:</h2>";
    $user_id = $_SESSION['temp_user_id'] ?? 0;
    if ($user_id) {
        echo "<p style='color: green;'><strong>✅ Session is active</strong></p>";
        echo "<p><strong>Temp User ID:</strong> " . $user_id . "</p>";
        echo "<p><strong>Temp User Email:</strong> " . ($_SESSION['temp_user_email'] ?? 'Not set') . "</p>";
        echo "<p><strong>Temp User Name:</strong> " . ($_SESSION['temp_user_name'] ?? 'Not set') . "</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ No active session</strong></p>";
        echo "<p>You need to sign in first to get an OTP code.</p>";
        echo "<p><a href='debug_signin.php'>Go to Debug Sign In</a></p>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug 2FA Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding: 20px; background: #f5f5f5;">
    <div class="container">
        <div class="card" style="max-width: 500px; margin: 40px auto;">
            <div class="card-header bg-primary text-white">
                <h3>🔐 Debug 2FA Form</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="otp_code" class="form-label">Enter 6-Digit OTP Code</label>
                        <input type="text" class="form-control" id="otp_code" name="otp_code" 
                               placeholder="000000" maxlength="6" required pattern="\d{6}">
                        <small class="text-muted">Enter the code from your email or from the debug signin page</small>
                    </div>
                    
                    <button type="submit" name="verify_2fa" class="btn btn-primary w-100">
                        🔍 Debug Verify Code
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p><strong>Quick Links:</strong></p>
                    <a href="debug_signin.php" class="btn btn-sm btn-secondary">Debug Sign In</a>
                    <a href="two_factor_auth.php" class="btn btn-sm btn-success">Real 2FA Page</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
