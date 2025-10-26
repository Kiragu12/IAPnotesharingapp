<?php
// Start session
session_start();

// Redirect if no temp user data (must come from login page)
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: signin.php');
    exit();
}

// Load required files
require_once '../../config/ClassAutoLoad.php';

// Debug logging for 2FA page
$debug_log = __DIR__ . '/../../debug.log';
error_log("DEBUG: two_factor_auth_new.php accessed - Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . " - " . date('Y-m-d H:i:s'), 3, $debug_log);

if (!empty($_POST)) {
    error_log("DEBUG: POST data in two_factor_auth_new.php: " . print_r($_POST, true), 3, $debug_log);
}

// Process 2FA verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_2fa'])) {
    error_log("DEBUG: About to call verify2FA function", 3, $debug_log);
    $verification_code = trim($_POST['verification_code'] ?? '');
    
    if (empty($verification_code)) {
        $ObjFncs->setMsg('msg', 'Please enter the verification code.', 'danger');
        error_log("DEBUG: Empty verification code", 3, $debug_log);
    } else {
        // Verify the 2FA code
        $result = $ObjAuth->verify2FA($conf, $ObjFncs, $verification_code);
        if ($result) {
            error_log("DEBUG: 2FA verification successful, redirecting to dashboard", 3, $debug_log);
            // Success - redirect to dashboard
            header('Location: ../dashboard.php');
            exit();
        } else {
            error_log("DEBUG: 2FA verification failed", 3, $debug_log);
        }
        // Error message will be set by verify2FA function
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
    error_log("DEBUG: Resend code requested", 3, $debug_log);
    try {
        $user_id = $_SESSION['temp_user_id'];
        $user_email = $_SESSION['temp_user_email'];
        
        // Generate new 2FA code
        $new_code = sprintf("%06d", mt_rand(100000, 999999));
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update the code in database
        $db = new Database($conf);
        $sql = "UPDATE two_factor_codes SET code = ?, expires_at = ?, attempts = 0, is_used = 0 
                WHERE user_id = ? AND code_type = 'login' AND is_used = 0";
        $db->query($sql, [$new_code, $expires_at, $user_id]);
        
        // Send new code via email
        $mailCnt = "Your new verification code is: " . $new_code . "\n\nThis code expires in 10 minutes.";
        
        // Set recipient email in configuration
        $conf['to_email'] = $user_email;
        $conf['mail_subject'] = "New Login Verification Code - " . $conf['site_name'];
        
        $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
        
        if ($email_sent) {
            $ObjFncs->setMsg('msg', '📧 A new verification code has been sent to your email!', 'success');
        } else {
            $ObjFncs->setMsg('msg', '❌ Failed to send verification code. Please try again.', 'danger');
        }
        
    } catch (Exception $e) {
        error_log('Resend code error: ' . $e->getMessage(), 3, $debug_log);
        $ObjFncs->setMsg('msg', '⚠️ An error occurred. Please try again.', 'danger');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG: POST request received but verify_2fa parameter missing: " . print_r(array_keys($_POST), true), 3, $debug_log);
}

// Get any error messages
$err = $ObjFncs->getMsg('errors') ?: array();
$msg = $ObjFncs->getMsg('msg') ?: '';
$user_email = $_SESSION['temp_user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="20" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            max-width: 450px;
        }
        
        .auth-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .auth-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .auth-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            color: #666;
            font-weight: 400;
        }
        
        .auth-form {
            padding: 2rem;
        }
        
        .verification-code {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .verification-code:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }
        
        .btn-verify {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-resend {
            border: 2px solid #667eea;
            background: transparent;
            color: #667eea;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-resend:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
            border-left: 4px solid #198754;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #764ba2;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .auth-card {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .auth-header, .auth-form {
                padding: 1.5rem;
            }
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .auth-card {
            animation: slideInUp 0.8s ease forwards;
        }
        
        .email-mask {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-logo">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h2 class="auth-title">Two-Factor Authentication</h2>
                            <p class="auth-subtitle">
                                We've sent a 6-digit verification code to<br>
                                <span class="email-mask"><?php echo $user_email; ?></span>
                            </p>
                        </div>
                        
                        <div class="auth-form">
                            <?php
                            // Display success/error messages
                            if (!empty($msg)) {
                                echo $msg;
                            }
                            ?>
                            
                            <form action="" method="post" autocomplete="off">
                                <!-- Hidden input to ensure verify_2fa parameter is sent -->
                                <input type="hidden" name="verify_2fa" value="1">
                                
                                <!-- Verification Code Field -->
                                <div class="mb-4">
                                    <label for="verification_code" class="form-label fw-semibold text-dark text-center d-block">
                                        <i class="bi bi-key me-2 text-primary"></i>Enter Verification Code
                                    </label>
                                    <input type="text" 
                                           class="form-control verification-code <?php echo isset($err['code_error']) ? 'is-invalid' : ''; ?>" 
                                           id="verification_code" 
                                           name="verification_code" 
                                           placeholder="000000"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           inputmode="numeric"
                                           autocomplete="one-time-code"
                                           required>
                                    <div class="form-text text-center mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>Code expires in 10 minutes
                                        </small>
                                    </div>
                                    <?php if(isset($err['code_error'])) { ?>
                                        <div class="invalid-feedback text-center">
                                            <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['code_error']; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <!-- Verify Button -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-verify" id="verifyBtn">
                                        <i class="bi bi-shield-check me-2"></i>Verify & Continue
                                    </button>
                                </div>
                            </form>
                            
                            <!-- Resend Code Form -->
                            <form action="" method="post">
                                <div class="d-grid">
                                    <button type="submit" name="resend_code" value="1" class="btn btn-resend">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Resend Code
                                    </button>
                                </div>
                            </form>
                            
                            <div class="back-link">
                                <a href="signin.php">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Sign In
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on verification code input
        window.addEventListener('load', function() {
            document.getElementById('verification_code').focus();
        });
        
        // Only allow numbers in verification code
        document.getElementById('verification_code').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits entered
            if (e.target.value.length === 6) {
                // Add visual feedback
                e.target.style.borderColor = '#198754';
                e.target.style.backgroundColor = '#f8fff9';
                
                // Small delay to show complete code
                setTimeout(() => {
                    document.querySelector('form').submit();
                }, 500);
            }
        });
        
        // Add real-time validation feedback
        document.getElementById('verification_code').addEventListener('keyup', function(e) {
            const value = e.target.value;
            if (value.length === 6) {
                e.target.classList.remove('is-invalid');
                e.target.classList.add('is-valid');
            } else if (value.length > 0) {
                e.target.classList.remove('is-valid', 'is-invalid');
            }
        });
        
        // Handle paste events to extract only numbers
        document.getElementById('verification_code').addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numbers = paste.replace(/[^0-9]/g, '').substring(0, 6);
            e.target.value = numbers;
            e.target.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>
