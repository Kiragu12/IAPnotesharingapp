<?php
/**
 * Email Configuration Test
 * Tests if SMTP credentials are working by sending a test email
 */

require_once 'conf.php';
require_once 'Global/SendMail.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .test-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            max-width: 600px;
            width: 100%;
        }
        .test-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        .config-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 1rem 0;
            border-left: 4px solid #667eea;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            font-weight: 600;
            color: #666;
        }
        .config-value {
            color: #333;
            font-family: monospace;
        }
        .masked {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="text-center mb-4">
            <div class="test-icon">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h2>Email Configuration Test</h2>
            <p class="text-muted">Testing SMTP connection and sending test email</p>
        </div>

        <div class="config-box">
            <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Current Configuration</h5>
            <div class="config-item">
                <span class="config-label">SMTP Host:</span>
                <span class="config-value"><?php echo htmlspecialchars($conf['smtp_host']); ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">SMTP User:</span>
                <span class="config-value"><?php echo htmlspecialchars($conf['smtp_user']); ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">SMTP Password:</span>
                <span class="config-value masked">••••••••••••</span>
            </div>
            <div class="config-item">
                <span class="config-label">SMTP Port:</span>
                <span class="config-value"><?php echo htmlspecialchars($conf['smtp_port']); ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">SMTP Secure:</span>
                <span class="config-value"><?php echo htmlspecialchars(strtoupper($conf['smtp_secure'])); ?></span>
            </div>
        </div>

        <?php
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
            $test_email = $_POST['test_email'] ?? $conf['smtp_user'];
            
            echo '<div class="mt-4">';
            echo '<h5 class="mb-3"><i class="bi bi-send me-2"></i>Sending Test Email</h5>';
            
            try {
                $mailer = new SendMail();
                
                $subject = "SMTP Configuration Test - 2FA Development";
                $message = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 30px;'>
                            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 80px; height: 80px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;'>
                                <span style='font-size: 40px; color: white;'>✓</span>
                            </div>
                            <h2 style='color: #333; margin: 0; font-size: 24px;'>Email Configuration Successful!</h2>
                        </div>
                        
                        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0;'>
                            <h3 style='color: #333; margin-top: 0; font-size: 18px;'>📧 SMTP Configuration</h3>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-weight: 600;'>Host:</td>
                                    <td style='padding: 8px 0; color: #333; font-family: monospace;'>" . htmlspecialchars($conf['smtp_host']) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-weight: 600;'>From:</td>
                                    <td style='padding: 8px 0; color: #333; font-family: monospace;'>" . htmlspecialchars($conf['smtp_user']) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-weight: 600;'>Port:</td>
                                    <td style='padding: 8px 0; color: #333; font-family: monospace;'>" . htmlspecialchars($conf['smtp_port']) . "</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px 0; color: #666; font-weight: 600;'>Security:</td>
                                    <td style='padding: 8px 0; color: #333; font-family: monospace;'>" . htmlspecialchars(strtoupper($conf['smtp_secure'])) . "</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                            <p style='margin: 0; color: #0066cc; font-size: 14px;'>
                                <strong>✅ Success!</strong> Your email system is configured correctly and ready to send 2FA verification codes.
                            </p>
                        </div>
                        
                        <div style='margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef; text-align: center;'>
                            <p style='color: #999; font-size: 12px; margin: 5px 0;'>This is an automated test message from your development environment</p>
                            <p style='color: #999; font-size: 12px; margin: 5px 0;'>Sent: " . date('F j, Y \a\t g:i A') . "</p>
                            <p style='color: #999; font-size: 12px; margin: 5px 0;'>Project: 2FA Authentication System</p>
                        </div>
                    </div>
                ";
                
                // Prepare email content array as required by SendMail class
                $mailContent = [
                    'mail_from' => $conf['smtp_user'],
                    'name_from' => 'Development Team',
                    'mail_to' => $test_email,
                    'name_to' => 'Developer',
                    'subject' => $subject,
                    'body' => $message
                ];
                
                ob_start(); // Capture output from Send_Mail
                $mailer->Send_Mail($conf, $mailContent);
                $output = ob_get_clean();
                
                if (strpos($output, 'Message has been sent') !== false) {
                    echo '<div class="alert alert-success">';
                    echo '<h5 class="alert-heading"><i class="bi bi-check-circle me-2"></i>Success!</h5>';
                    echo '<p class="mb-0">Test email sent successfully to <strong>' . htmlspecialchars($test_email) . '</strong></p>';
                    echo '<p class="mt-2 mb-0"><small>✉️ Check your inbox (and spam folder) for the test email.</small></p>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<h5 class="alert-heading"><i class="bi bi-x-circle me-2"></i>Error Sending Email</h5>';
                    echo '<p class="mb-0">' . htmlspecialchars($output) . '</p>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">';
                echo '<h5 class="alert-heading"><i class="bi bi-x-circle me-2"></i>Error Sending Email</h5>';
                echo '<p class="mb-2"><strong>Error Message:</strong></p>';
                echo '<p class="mb-0"><code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
                echo '</div>';
                
                echo '<div class="alert alert-info mt-3">';
                echo '<h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Troubleshooting Tips</h6>';
                echo '<ul class="mb-0">';
                echo '<li>Verify Gmail app password is correct: <code>gmoi pwjz azvl qlud</code></li>';
                echo '<li>Make sure 2-Step Verification is enabled on your Google account</li>';
                echo '<li>Check that "Less secure app access" is NOT blocking the connection</li>';
                echo '<li>Verify the email address matches: <code>paulkiragu547@gmail.com</code></li>';
                echo '<li>Ensure OpenSSL extension is enabled in PHP</li>';
                echo '</ul>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="test_email" class="form-label">Send Test Email To:</label>
                <input type="email" 
                       class="form-control" 
                       id="test_email" 
                       name="test_email" 
                       value="<?php echo htmlspecialchars($conf['smtp_user']); ?>"
                       required>
                <small class="text-muted">Default is your SMTP user email</small>
            </div>
            <button type="submit" name="send_test" class="btn btn-primary w-100">
                <i class="bi bi-send me-2"></i>Send Test Email
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house me-2"></i>Back to Home
            </a>
            <a href="test_db_connection.php" class="btn btn-outline-primary ms-2">
                <i class="bi bi-database me-2"></i>Test Database
            </a>
        </div>
    </div>
</body>
</html>
