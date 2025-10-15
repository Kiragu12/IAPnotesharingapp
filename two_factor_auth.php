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
        $ObjFncs->setMsg('errors', 'otp_error', 'Please enter the 6-digit code');
    } elseif (!preg_match('/^\d{6}$/', $otp_code)) {
        $ObjFncs->setMsg('errors', 'otp_error', 'Code must be exactly 6 digits');
    } else {
        // Verify OTP code
        $sql = "SELECT id, email, full_name, verification_code, code_expiry 
                FROM users 
                WHERE id = ? AND verification_code = ? AND code_expiry > NOW()";
        $stmt = $ObjDatabase->prepare($sql);
        $stmt->bind_param("is", $user_id, $otp_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Clear verification code
            $sql = "UPDATE users SET verification_code = NULL, code_expiry = NULL WHERE id = ?";
            $stmt = $ObjDatabase->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Log user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['login_time'] = time();
            
            // Handle "Remember Me" if it was selected
            if (isset($_SESSION['temp_remember_me']) && $_SESSION['temp_remember_me']) {
                $auth = new auth($conf, $ObjDatabase, $ObjFncs, $ObjSendMail);
                $auth->createRememberToken($user['id']);
            }
            
            // Clean up temporary session data
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_remember_me']);
            
            // Log activity
            $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) 
                    VALUES (?, 'login_2fa', ?, ?, ?)";
            $stmt = $ObjDatabase->prepare($sql);
            $details = json_encode(['method' => '2FA verification']);
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $stmt->bind_param("isss", $user['id'], $details, $ip, $user_agent);
            $stmt->execute();
            
            header('Location: dashboard.php');
            exit();
        } else {
            $ObjFncs->setMsg('errors', 'otp_error', 'Invalid or expired verification code');
        }
    }
}

// Handle resend OTP
if (isset($_POST['resend_otp'])) {
    $user_id = $_SESSION['temp_user_id'] ?? 0;
    
    if (!empty($user_id)) {
        // Get user details
        $sql = "SELECT email, full_name FROM users WHERE id = ?";
        $stmt = $ObjDatabase->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate new OTP
            $otp_code = sprintf("%06d", mt_rand(100000, 999999));
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Update database
            $sql = "UPDATE users SET verification_code = ?, code_expiry = ? WHERE id = ?";
            $stmt = $ObjDatabase->prepare($sql);
            $stmt->bind_param("ssi", $otp_code, $expiry, $user_id);
            
            if ($stmt->execute()) {
                // Send OTP email
                $subject = "Two-Factor Authentication Code - NotesShare Academy";
                $message = "
                <html>
                <head><title>2FA Verification Code</title></head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: #667eea; text-align: center;'>Two-Factor Authentication</h2>
                        <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                        <p>Your verification code for signing in to NotesShare Academy is:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <h1 style='background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px; letter-spacing: 5px; font-size: 2em;'>" . $otp_code . "</h1>
                        </div>
                        <p><strong>This code will expire in 10 minutes.</strong></p>
                        <p>If you didn't attempt to sign in, please ignore this email or contact support if you have concerns.</p>
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                        <p style='font-size: 12px; color: #888; text-align: center;'>NotesShare Academy - Secure Learning Platform</p>
                    </div>
                </body>
                </html>";
                
                if ($ObjSendMail->send_mail($user['email'], $subject, $message)) {
                    $ObjFncs->setMsg('msg', 'success', 'New verification code sent to your email');
                    $_SESSION['otp_resent_time'] = time();
                } else {
                    $ObjFncs->setMsg('errors', 'resend_error', 'Failed to send verification code');
                }
            } else {
                $ObjFncs->setMsg('errors', 'resend_error', 'Failed to generate new code');
            }
        }
    }
}

// Check if user should be here
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: signin.php');
    exit();
}

// Get user email for display
$sql = "SELECT email FROM users WHERE id = ?";
$stmt = $ObjDatabase->prepare($sql);
$stmt->bind_param("i", $_SESSION['temp_user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_email = $result->num_rows > 0 ? $result->fetch_assoc()['email'] : '';
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

        <form method="POST" action="" id="twofaForm">
            <div class="otp-input-container">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
            </div>

            <input type="hidden" name="otp_code" id="otpCode">

            <?php if (isset($errors['otp_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $errors['otp_error']; ?>
                </div>
            <?php endif; ?>

            <button type="submit" name="verify_2fa" class="btn btn-verify" id="verifyBtn" disabled>
                <i class="bi bi-shield-check me-2"></i>Verify & Sign In
            </button>
        </form>

        <div class="resend-section">
            <p class="text-muted mb-2">Didn't receive the code?</p>
            <form method="POST" action="" style="display: inline-block;">
                <button type="submit" name="resend_otp" class="btn btn-resend" id="resendBtn">
                    <i class="bi bi-arrow-clockwise me-2"></i>Resend Code
                </button>
            </form>
            <div class="countdown" id="resendCountdown"></div>
            <?php if (isset($errors['resend_error'])): ?>
                <div class="alert alert-danger mt-2">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $errors['resend_error']; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="security-notice">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Security Notice:</strong> This code expires in 10 minutes. 
            Never share this code with anyone.
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