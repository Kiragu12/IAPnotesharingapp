<?php
include_once "ClassAutoLoad.php";

// Handle form submission
if (isset($_POST['forgot_password'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $ObjFncs->setMsg('errors', 'email_error', 'Email address is required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $ObjFncs->setMsg('errors', 'email_error', 'Please enter a valid email address');
    } else {
        // Check if email exists in database
        $sql = "SELECT id, full_name FROM users WHERE email = ? AND is_active = 1";
        $stmt = $ObjDatabase->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store reset token in database
            $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
            $stmt = $ObjDatabase->prepare($sql);
            $stmt->bind_param("issss", $user['id'], $reset_token, $expires_at, $reset_token, $expires_at);
            
            if ($stmt->execute()) {
                // Send reset email
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=" . $reset_token;
                $subject = "Password Reset Request - NotesShare Academy";
                $message = "
                <html>
                <head><title>Password Reset</title></head>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: #667eea; text-align: center;'>Password Reset Request</h2>
                        <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                        <p>We received a request to reset your password for your NotesShare Academy account.</p>
                        <p>Click the button below to reset your password:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='" . $reset_link . "' style='background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Reset Password</a>
                        </div>
                        <p>Or copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color: #667eea;'>" . $reset_link . "</p>
                        <p><strong>This link will expire in 1 hour for security reasons.</strong></p>
                        <p>If you didn't request this password reset, please ignore this email. Your password will not be changed.</p>
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                        <p style='font-size: 12px; color: #888; text-align: center;'>NotesShare Academy - Secure Learning Platform</p>
                    </div>
                </body>
                </html>";
                
                // Send email using your mail function
                if ($ObjSendMail->send_mail($email, $subject, $message)) {
                    $ObjFncs->setMsg('msg', 'success', 'Password reset instructions have been sent to your email address.');
                    $_SESSION['reset_email_sent'] = true;
                } else {
                    $ObjFncs->setMsg('errors', 'email_error', 'Failed to send reset email. Please try again.');
                }
            } else {
                $ObjFncs->setMsg('errors', 'email_error', 'Something went wrong. Please try again.');
            }
        } else {
            // Don't reveal if email exists or not for security
            $ObjFncs->setMsg('msg', 'success', 'If this email address exists in our system, you will receive password reset instructions.');
            $_SESSION['reset_email_sent'] = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - NotesShare Academy</title>
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

        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            width: 100%;
            max-width: 450px;
            margin: 2rem;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .forgot-icon {
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

        .forgot-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .forgot-subtitle {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        .form-floating label {
            color: #666;
            font-weight: 500;
        }

        .btn-forgot {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            width: 100%;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .btn-forgot:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-forgot:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
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

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 5;
        }

        .success-message {
            text-align: center;
            padding: 2rem;
        }

        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .loading-spinner {
            display: none;
            margin-right: 0.5rem;
        }

        @media (max-width: 576px) {
            .forgot-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <?php if (isset($_SESSION['reset_email_sent']) && $_SESSION['reset_email_sent']): ?>
            <!-- Success Message -->
            <div class="success-message">
                <i class="bi bi-check-circle success-icon"></i>
                <h3 class="forgot-title">Check Your Email</h3>
                <p class="forgot-subtitle">
                    We've sent password reset instructions to your email address. 
                    Please check your inbox and follow the link to reset your password.
                </p>
                <div class="alert alert-success">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Didn't receive the email?</strong> Check your spam folder or 
                    <a href="forgot_password.php" class="text-white fw-bold">try again</a>.
                </div>
            </div>
        <?php else: ?>
            <!-- Forgot Password Form -->
            <div class="forgot-header">
                <div class="forgot-icon">
                    <i class="bi bi-key"></i>
                </div>
                <h2 class="forgot-title">Forgot Password?</h2>
                <p class="forgot-subtitle">
                    Don't worry! Enter your email address and we'll send you instructions to reset your password.
                </p>
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

            <form method="POST" action="" id="forgotForm">
                <div class="form-floating input-icon">
                    <input type="email" 
                           class="form-control <?php echo isset($errors['email_error']) ? 'is-invalid' : ''; ?>" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                    <label for="email">Email Address</label>
                    <i class="bi bi-envelope"></i>
                    <?php if (isset($errors['email_error'])): ?>
                        <div class="invalid-feedback">
                            <?php echo $errors['email_error']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="forgot_password" class="btn btn-forgot" id="submitBtn">
                    <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                    <i class="bi bi-send me-2"></i>Send Reset Instructions
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <i class="bi bi-arrow-left me-2"></i>
            <a href="signin.php">Back to Sign In</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('forgotForm')?.addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.querySelector('.loading-spinner');
            
            // Disable button and show loading
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Sending...';
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>

<?php
// Clear the session flag
if (isset($_SESSION['reset_email_sent'])) {
    unset($_SESSION['reset_email_sent']);
}
?>