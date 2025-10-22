<?php
/**
 * Test Email Sending with Current SMTP Configuration
 */

echo "<h1>📧 Testing Email Delivery</h1>";
echo "<hr>";

require_once 'conf.php';
require_once 'ClassAutoLoad.php';

echo "<h2>🔧 Current SMTP Configuration:</h2>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
echo "<p><strong>SMTP Host:</strong> " . $conf['smtp_host'] . "</p>";
echo "<p><strong>SMTP Port:</strong> " . $conf['smtp_port'] . "</p>";
echo "<p><strong>SMTP User:</strong> " . $conf['smtp_user'] . "</p>";
echo "<p><strong>SMTP Pass:</strong> " . str_repeat('*', strlen($conf['smtp_pass'])) . " (hidden)</p>";
echo "<p><strong>Admin Email:</strong> " . $conf['admin_email'] . "</p>";
echo "</div>";

// Test 1: Send test email to your Strathmore email
echo "<h2>📨 Test 1: Sending Test Email...</h2>";

$mailCnt = [
    'name_from' => $conf['site_name'],
    'mail_from' => $conf['admin_email'],
    'name_to' => 'Macharia Kiragu',
    'mail_to' => 'macharia.kiragu@strathmore.edu',
    'subject' => 'Test Email from NotesShare - ' . date('Y-m-d H:i:s'),
    'body' => '
    <html>
    <head>
        <title>Test Email</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
            .header { color: #667eea; text-align: center; }
            .success-box { background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2 class="header">✅ Email Test Successful!</h2>
            <div class="success-box">
                <p><strong>If you received this email, your SMTP configuration is working correctly!</strong></p>
                <p>Email sent at: ' . date('Y-m-d H:i:s') . '</p>
                <p>From: ' . $conf['admin_email'] . '</p>
            </div>
            <p>Your application can now send:</p>
            <ul>
                <li>✅ Welcome emails after signup</li>
                <li>✅ 2FA OTP codes for login</li>
                <li>✅ Password reset emails</li>
            </ul>
        </div>
    </body>
    </html>'
];

$ObjSendMail = new SendMail();
$email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);

if ($email_sent) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;'>";
    echo "<h3>✅ Email Sent Successfully!</h3>";
    echo "<p>A test email has been sent to <strong>macharia.kiragu@strathmore.edu</strong></p>";
    echo "<p><strong>Check your Strathmore inbox!</strong> (Also check spam folder)</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Email Failed to Send</h3>";
    echo "<p>There was an error sending the email. Check the error log for details.</p>";
    echo "<p><strong>Common Issues:</strong></p>";
    echo "<ul>";
    echo "<li>Incorrect Google App Password</li>";
    echo "<li>2-Step Verification not enabled on Gmail</li>";
    echo "<li>SMTP port blocked by firewall</li>";
    echo "<li>Incorrect email address in smtp_user</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";

// Test 2: Generate and display a sample OTP code
echo "<h2>🔐 Test 2: 2FA OTP Code Generation</h2>";

$otp_code = sprintf("%06d", mt_rand(100000, 999999));
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Sample OTP Code:</h3>";
echo "<div style='text-align: center; font-size: 36px; letter-spacing: 10px; color: #667eea; font-weight: bold; padding: 20px; background: white; border-radius: 8px;'>";
echo $otp_code;
echo "</div>";
echo "<p style='text-align: center; margin-top: 15px; color: #666;'>This is what your users will receive via email</p>";
echo "</div>";

echo "<hr>";
echo "<h2>🧪 Next Steps:</h2>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<ol style='font-size: 16px;'>";
echo "<li><strong>If email was sent successfully:</strong> Check your Strathmore inbox</li>";
echo "<li><strong>Try signing in:</strong> <a href='signin.php' style='color: #007bff; font-weight: bold;'>Go to Sign In</a></li>";
echo "<li><strong>Use test account:</strong> macharia.kiragu@strathmore.edu / MyPassword123!</li>";
echo "<li><strong>You should receive:</strong> Real OTP code via email</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; margin-top: 20px;'>";
echo "<h3>✨ What's Fixed:</h3>";
echo "<ul>";
echo "<li>✅ Signin page now processes form BEFORE HTML output</li>";
echo "<li>✅ Header redirects will work properly</li>";
echo "<li>✅ No more page reload/stuck issues</li>";
echo "<li>✅ Email sending configured with your Strathmore account</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<h2>🔗 Quick Links:</h2>";
echo "<p>";
echo "<a href='signin.php' style='display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px; font-weight: bold;'>🔓 Sign In</a>";
echo "<a href='signup.php' style='display: inline-block; background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px; margin-right: 10px;'>➕ Sign Up</a>";
echo "<a href='setup_my_account.php' style='display: inline-block; background: #6c757d; color: white; padding: 12px 25px; text-decoration: none; border-radius: 8px;'>👤 Setup Account</a>";
echo "</p>";
?>
