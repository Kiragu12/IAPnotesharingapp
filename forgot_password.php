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

            <form method="POST" action="" id="forgotForm" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold text-dark">
                        <i class="bi bi-envelope me-2 text-primary"></i>Email Address
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-envelope text-primary"></i>
                        </span>
                        <input type="email" 
                               class="form-control border-start-0 <?php echo isset($errors['email_error']) ? 'is-invalid' : ''; ?>" 
                               id="email" 
                               name="email" 
                               placeholder="Enter your email address"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               style="border-radius: 0 12px 12px 0; background: rgba(255, 255, 255, 0.9); padding-left: 0.5rem;"
                               required>
                        <div class="valid-feedback">
                            <i class="bi bi-check-circle me-1"></i>Valid email format!
                        </div>
                        <?php if (isset($errors['email_error'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $errors['email_error']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-text mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        We'll send reset instructions to this email address
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" name="forgot_password" class="btn btn-forgot position-relative" id="submitBtn">
                        <span id="submitText">
                            <i class="bi bi-send me-2"></i>Send Reset Instructions
                        </span>
                        <span id="submitSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Sending Instructions...
                        </span>
                    </button>
                </div>

                <!-- Quick Actions Card -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="mb-1 fw-semibold">Need immediate help?</h6>
                                <small class="text-muted">Contact our support team</small>
                            </div>
                            <div class="col-auto">
                                <a href="mailto:support@noteshareacademy.com" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-headset me-1"></i>Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
        // Bootstrap Form Validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            // Show loading state
                            showLoadingState();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Real-time email validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email.length > 0) {
                if (emailRegex.test(email)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });

        // Loading state for form submission
        function showLoadingState() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');
            
            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitSpinner.classList.remove('d-none');
        }

        // Enhanced form input effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-1px)';
                this.parentElement.style.boxShadow = '0 4px 12px rgba(102, 126, 234, 0.15)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
                this.parentElement.style.boxShadow = 'none';
            });
        });

        // Auto-focus email field
        window.addEventListener('load', function() {
            const emailField = document.getElementById('email');
            if (emailField && emailField.value === '') {
                emailField.focus();
            }
        });

        // Auto-hide success alerts after 8 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(function(alert) {
                if (!alert.closest('.success-message')) { // Don't hide main success message
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 8000);

        // Enhanced keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                document.getElementById('forgotForm')?.dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>

<?php
// Clear the session flag
if (isset($_SESSION['reset_email_sent'])) {
    unset($_SESSION['reset_email_sent']);
}
?>