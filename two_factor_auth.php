<?php
include_once "ClassAutoLoad.php";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Handle 2FA form submission
if (isset($_POST['verify_2fa'])) {
    $user_id = $_SESSION['temp_user_id'] ?? 0;
    $otp_code = trim($_POST['otp_code']);
    
    if (empty($user_id)) {
        header('Location: signin.php');
        exit();
    }
    
    if (empty($otp_code)) {
        $ObjFncs->setMsg('msg', 'Please enter the 6-digit code', 'danger');
    } elseif (!preg_match('/^\d{6}$/', $otp_code)) {
        $ObjFncs->setMsg('msg', 'Code must be exactly 6 digits', 'danger');
    } else {
        try {
            $db = new Database($conf);
            
            // Check for valid OTP code in two_factor_codes table
            $sql = "SELECT tfc.id as code_id, tfc.attempts_used, tfc.max_attempts, u.id, u.email, u.full_name 
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
            
            if ($result) {
                // Check if too many attempts
                $max_attempts = $result['max_attempts'] ?? 3;
                if ($result['attempts_used'] >= $max_attempts) {
                    $ObjFncs->setMsg('msg', 'Too many failed attempts. Please request a new code.', 'danger');
                    
                    // Delete the code
                    $delete_sql = "DELETE FROM two_factor_codes WHERE id = :code_id";
                    $db->execute($delete_sql, [':code_id' => $result['code_id']]);
                } else {
                    // Valid code - log user in
                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['user_email'] = $result['email'];
                    $_SESSION['user_name'] = $result['full_name'];
                    $_SESSION['login_time'] = time();
                    
                    // Handle "Remember Me" if it was selected
                    if (isset($_SESSION['temp_remember_me']) && $_SESSION['temp_remember_me']) {
                        $auth = new auth();
                        $auth->createRememberToken($result['id'], $conf);
                    }
                    
                    // Mark the code as used instead of deleting
                    $mark_used_sql = "UPDATE two_factor_codes SET used_at = NOW() WHERE id = :code_id";
                    $db->execute($mark_used_sql, [':code_id' => $result['code_id']]);
                    
                    // Clean up temporary session data
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['temp_user_email']);
                    unset($_SESSION['temp_user_name']);
                    unset($_SESSION['temp_remember_me']);
                    
                    // Set welcome message for dashboard
                    $ObjFncs->setMsg('msg', '🎉 Login successful! Welcome back to Notes Sharing Academy, ' . $result['full_name'] . '!', 'success');
                    $_SESSION['first_login'] = true; // Flag for first-time dashboard visit
                    
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                // Invalid code - increment attempts
                $update_sql = "UPDATE two_factor_codes 
                              SET attempts_used = attempts_used + 1 
                              WHERE user_id = :user_id 
                              AND expires_at > NOW()
                              AND used_at IS NULL";
                $db->execute($update_sql, [':user_id' => $user_id]);
                
                $ObjFncs->setMsg('msg', 'Invalid or expired verification code. Please try again.', 'danger');
            }
        } catch (Exception $e) {
            error_log('2FA verification error: ' . $e->getMessage());
            $ObjFncs->setMsg('msg', 'An error occurred during verification. Please try again.', 'danger');
        }
    }
}

// Handle resend OTP
if (isset($_POST['resend_otp'])) {
    $user_id = $_SESSION['temp_user_id'] ?? 0;
    
    if (!empty($user_id)) {
        try {
            $db = new Database($conf);
            
            // Get user details
            $sql = "SELECT email, full_name FROM users WHERE id = :user_id";
            $user = $db->fetchOne($sql, [':user_id' => $user_id]);
            
            if ($user) {
                // Delete old codes for this user
                $delete_sql = "DELETE FROM two_factor_codes WHERE user_id = :user_id";
                $db->execute($delete_sql, [':user_id' => $user_id]);
                
                // Generate new OTP
                $otp_code = sprintf("%06d", mt_rand(100000, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Insert new code
                $insert_sql = "INSERT INTO two_factor_codes (user_id, code, expires_at, attempts_used, code_type) 
                               VALUES (:user_id, :code, :expires_at, 0, 'login')";
                $db->execute($insert_sql, [
                    ':user_id' => $user_id,
                    ':code' => $otp_code,
                    ':expires_at' => $expires_at
                ]);
                
                // Send OTP email
                $email_variables = [
                    'site_name' => $conf['site_name'],
                    'full_name' => $user['full_name'],
                    'otp_code' => $otp_code
                ];

                $mailCnt = [
                    'name_from' => $conf['site_name'],
                    'mail_from' => $conf['admin_email'],
                    'name_to' => $user['full_name'],
                    'mail_to' => $user['email'],
                    'subject' => 'Two-Factor Authentication Code - ' . $conf['site_name'],
                    'body' => build2FAEmailTemplate($email_variables, $conf['site_name'])
                ];

                $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
                
                if ($email_sent) {
                    $ObjFncs->setMsg('msg', 'New verification code sent to your email', 'success');
                } else {
                    $ObjFncs->setMsg('msg', 'Failed to send verification code. Please try again.', 'danger');
                }
                $_SESSION['otp_resent_time'] = time();
            } else {
                $ObjFncs->setMsg('msg', 'User not found', 'danger');
            }
        } catch (Exception $e) {
            error_log('Resend OTP error: ' . $e->getMessage());
            $ObjFncs->setMsg('msg', 'Failed to send verification code. Please try again.', 'danger');
        }
    }
}

// Helper function for building 2FA email template
function build2FAEmailTemplate($variables, $site_name) {
    return "
    <html>
    <head>
        <title>Two-Factor Authentication Code</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
            .header { color: #667eea; text-align: center; }
            .code-box { text-align: center; margin: 30px 0; }
            .code { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px; letter-spacing: 5px; font-size: 2em; display: inline-block; }
            .footer { font-size: 12px; color: #888; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2 class='header'>Two-Factor Authentication</h2>
            <p>Hello " . htmlspecialchars($variables['full_name']) . ",</p>
            <p>Your verification code for signing in to " . htmlspecialchars($site_name) . " is:</p>
            <div class='code-box'>
                <div class='code'>" . $variables['otp_code'] . "</div>
            </div>
            <p><strong>This code will expire in 10 minutes.</strong></p>
            <p>If you didn't attempt to sign in, please ignore this email or contact support if you have concerns.</p>
            <div class='footer'>
                <p>" . htmlspecialchars($site_name) . " - Secure Learning Platform</p>
            </div>
        </div>
    </body>
    </html>";
}

// Check if user should be here
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: signin.php');
    exit();
}

// Get user email for display
$user_email = $_SESSION['temp_user_email'] ?? '';
if (empty($user_email)) {
    header('Location: signin.php');
    exit();
}
$masked_email = substr($user_email, 0, 2) . str_repeat('*', strlen($user_email) - 6) . substr($user_email, -4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Factor Authentication - NotesShare Academy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
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
            justify-content: center;
        }

        .twofa-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            width: 100%;
            max-width: 450px;
            margin: 2rem;
        }

        .twofa-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .twofa-icon {
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

        .twofa-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .twofa-subtitle {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .email-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .email-info strong {
            color: #667eea;
        }

        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        .otp-input {
            width: 50px;
            height: 60px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .otp-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            outline: none;
        }

        .otp-input.filled {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .btn-verify {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            width: 100%;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-verify:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
        }

        .resend-section {
            text-align: center;
            margin: 1.5rem 0;
        }

        .btn-resend {
            background: transparent;
            border: 2px solid #667eea;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            color: #667eea;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-resend:hover {
            background: #667eea;
            color: white;
        }

        .btn-resend:disabled {
            background: transparent;
            border-color: #ccc;
            color: #ccc;
        }

        .countdown {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #764ba2;
        }

        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
        }

        .security-notice {
            background: rgba(255, 193, 7, 0.1);
            border-left: 4px solid #ffc107;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .twofa-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .otp-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="twofa-container">
        <div class="twofa-header">
            <div class="twofa-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h2 class="twofa-title">Two-Factor Authentication</h2>
            <p class="twofa-subtitle">
                For your security, please enter the 6-digit verification code sent to your email.
            </p>
        </div>

        <div class="email-info">
            <i class="bi bi-envelope me-2"></i>
            Code sent to: <strong><?php echo htmlspecialchars($masked_email); ?></strong>
        </div>

        <!-- Display Messages -->
        <?php 
        $errors = $ObjFncs->getMsg('errors');
        $msg = $ObjFncs->getMsg('msg');
        if ($msg): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="twofaForm" class="needs-validation" novalidate>
            <!-- OTP Input Section with Bootstrap Enhancement -->
            <div class="mb-4">
                <label class="form-label fw-semibold text-dark text-center d-block mb-3">
                    <i class="bi bi-key me-2 text-primary"></i>Enter 6-Digit Verification Code
                </label>
                <div class="row g-2 justify-content-center">
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                    <div class="col-auto">
                        <input type="text" class="otp-input form-control text-center" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>
                </div>
                <div class="invalid-feedback text-center mt-2" id="otpFeedback">
                    Please enter all 6 digits of the verification code
                </div>
            </div>

            <input type="hidden" name="otp_code" id="otpCode">

            <!-- Error Display -->
            <?php if (isset($errors['otp_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $errors['otp_error']; ?>
                </div>
            <?php endif; ?>

            <!-- Verify Button -->
            <div class="d-grid mb-3">
                <button type="submit" name="verify_2fa" class="btn btn-verify position-relative" id="verifyBtn" disabled>
                    <span id="verifyText">
                        <i class="bi bi-shield-check me-2"></i>Verify & Sign In
                    </span>
                    <span id="verifySpinner" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Verifying...
                    </span>
                </button>
            </div>

            <!-- Code Progress Indicator -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Code Entry Progress</small>
                    <small id="progressText" class="text-muted">0/6</small>
                </div>
                <div class="progress" style="height: 4px;">
                    <div id="progressBar" class="progress-bar bg-primary" style="width: 0%; transition: all 0.3s ease;"></div>
                </div>
            </div>
        </form>

        <!-- Resend Section with Enhanced Bootstrap Components -->
        <div class="card border-0 bg-light mb-3">
            <div class="card-body text-center py-3">
                <h6 class="mb-2">Didn't receive the code?</h6>
                <div class="row align-items-center">
                    <div class="col">
                        <form method="POST" action="" class="d-inline">
                            <button type="submit" name="resend_otp" class="btn btn-resend btn-sm" id="resendBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
                            </button>
                        </form>
                    </div>
                </div>
                <div class="countdown mt-2" id="resendCountdown"></div>
                <?php if (isset($errors['resend_error'])): ?>
                    <div class="alert alert-danger mt-2 mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i><?php echo $errors['resend_error']; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Code Expiry Info -->
        <div class="alert alert-warning border-0 mb-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div class="col">
                    <h6 class="mb-1">Code Expiry</h6>
                    <small>This verification code expires in <strong>10 minutes</strong></small>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="card border-0 bg-warning-subtle mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <i class="bi bi-shield-exclamation text-warning fs-4"></i>
                    </div>
                    <div class="col">
                        <h6 class="mb-1 text-warning-emphasis">Security Notice</h6>
                        <small class="text-warning-emphasis">Never share this code with anyone. Our support team will never ask for this code.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="back-link">
            <i class="bi bi-arrow-left me-2"></i>
            <a href="signin.php">Back to Sign In</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.otp-input');
            const otpCodeInput = document.getElementById('otpCode');
            const verifyBtn = document.getElementById('verifyBtn');
            const resendBtn = document.getElementById('resendBtn');
            const resendCountdown = document.getElementById('resendCountdown');

            // Focus first input
            inputs[0].focus();

            // Handle OTP input
            inputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    // Only allow numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    if (this.value.length === 1) {
                        this.classList.add('filled');
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    } else {
                        this.classList.remove('filled');
                    }
                    
                    updateOTPCode();
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        inputs[index - 1].classList.remove('filled');
                        updateOTPCode();
                    }
                });

                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const paste = e.clipboardData.getData('text');
                    const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);
                    
                    for (let i = 0; i < digits.length && i < inputs.length; i++) {
                        inputs[i].value = digits[i];
                        inputs[i].classList.add('filled');
                    }
                    
                    updateOTPCode();
                    
                    if (digits.length < inputs.length) {
                        inputs[digits.length].focus();
                    }
                });
            });

            function updateOTPCode() {
                const code = Array.from(inputs).map(input => input.value).join('');
                otpCodeInput.value = code;
                verifyBtn.disabled = code.length !== 6;
            }

            // Resend countdown
            <?php if (isset($_SESSION['otp_resent_time'])): ?>
                let resendTime = <?php echo $_SESSION['otp_resent_time']; ?>;
                let currentTime = Math.floor(Date.now() / 1000);
                let timeLeft = 60 - (currentTime - resendTime);
                
                if (timeLeft > 0) {
                    resendBtn.disabled = true;
                    let countdown = setInterval(function() {
                        if (timeLeft <= 0) {
                            clearInterval(countdown);
                            resendBtn.disabled = false;
                            resendCountdown.textContent = '';
                        } else {
                            resendCountdown.textContent = `Resend available in ${timeLeft} seconds`;
                            timeLeft--;
                        }
                    }, 1000);
                }
            <?php endif; ?>

            // Auto-submit when all digits entered
            function checkAutoSubmit() {
                const code = Array.from(inputs).map(input => input.value).join('');
                if (code.length === 6) {
                    setTimeout(() => {
                        if (document.getElementById('twofaForm')) {
                            document.getElementById('twofaForm').submit();
                        }
                    }, 500);
                }
            }

            inputs.forEach(input => {
                input.addEventListener('input', checkAutoSubmit);
            });
        });
    </script>
</body>
</html>

<?php
// Clean up old session data if needed
if (isset($_SESSION['otp_resent_time']) && time() - $_SESSION['otp_resent_time'] > 300) {
    unset($_SESSION['otp_resent_time']);
}
?>