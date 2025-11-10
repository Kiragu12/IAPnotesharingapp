<?php
include_once "ClassAutoLoad.php";

// Check if token is provided
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

// Verify token and get user information
$sql = "SELECT pr.user_id, pr.expires_at, u.email, u.full_name 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = ? AND pr.expires_at > NOW() AND u.is_active = 1";
$stmt = $ObjDatabase->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $token_expired = true;
} else {
    $user_data = $result->fetch_assoc();
    $token_expired = false;
}

// Handle password reset form submission
if (isset($_POST['reset_password']) && !$token_expired) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    $errors = [];
    
    // Validate password
    if (empty($password)) {
        $errors['password_error'] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors['password_error'] = 'Password must be at least 8 characters long';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors['password_error'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }
    
    // Validate confirm password
    if (empty($confirm_password)) {
        $errors['confirm_password_error'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password_error'] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $ObjDatabase->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_data['user_id']);
        
        if ($stmt->execute()) {
            // Delete the reset token
            $sql = "DELETE FROM password_resets WHERE token = ?";
            $stmt = $ObjDatabase->prepare($sql);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            // Delete all remember tokens for this user (force re-login)
            $sql = "DELETE FROM remember_tokens WHERE user_id = ?";
            $stmt = $ObjDatabase->prepare($sql);
            $stmt->bind_param("i", $user_data['user_id']);
            $stmt->execute();
            
            // Send confirmation email
            $subject = "Password Successfully Reset - NotesShare Academy";
            $message = "
            <html>
            <head><title>Password Reset Confirmation</title></head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                    <h2 style='color: #28a745; text-align: center;'>Password Reset Successful</h2>
                    <p>Hello " . htmlspecialchars($user_data['full_name']) . ",</p>
                    <p>Your password has been successfully reset for your NotesShare Academy account.</p>
                    <p>If you didn't make this change, please contact our support team immediately.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://" . $_SERVER['HTTP_HOST'] . "/signin.php' style='background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Sign In Now</a>
                    </div>
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #888; text-align: center;'>NotesShare Academy - Secure Learning Platform</p>
                </div>
            </body>
            </html>";
            
            $ObjSendMail->send_mail($user_data['email'], $subject, $message);
            
            $reset_success = true;
        } else {
            $errors['general_error'] = 'Failed to reset password. Please try again.';
        }
    }
    
    $ObjFncs->setMsg('errors', $errors);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - NotesShare Academy</title>
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

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem 2rem;
            width: 100%;
            max-width: 450px;
            margin: 2rem;
        }

        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }

        .expired-icon {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
        }

        .reset-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .reset-subtitle {
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

        .btn-reset {
            background: linear-gradient(45deg, #28a745, #20c997);
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-new-request {
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

        .btn-new-request:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
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

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }

        .success-message {
            text-align: center;
            padding: 2rem;
        }

        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            z-index: 5;
        }

        @media (max-width: 576px) {
            .reset-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <?php if (isset($reset_success) && $reset_success): ?>
            <!-- Success Message -->
            <div class="success-message">
                <i class="bi bi-check-circle success-icon"></i>
                <h3 class="reset-title">Password Reset Successful!</h3>
                <p class="reset-subtitle">
                    Your password has been successfully reset. You can now sign in with your new password.
                </p>
                <a href="signin.php" class="btn btn-reset">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Now
                </a>
            </div>
        <?php elseif ($token_expired): ?>
            <!-- Expired Token Message -->
            <div class="reset-header">
                <div class="reset-icon expired-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h2 class="reset-title">Link Expired</h2>
                <p class="reset-subtitle">
                    This password reset link has expired or is invalid. Please request a new password reset.
                </p>
            </div>
            
            <a href="forgot_password.php" class="btn btn-new-request">
                <i class="bi bi-arrow-clockwise me-2"></i>Request New Reset Link
            </a>
        <?php else: ?>
            <!-- Reset Password Form -->
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2 class="reset-title">Create New Password</h2>
                <p class="reset-subtitle">
                    Enter your new password below. Make sure it's strong and secure.
                </p>
            </div>

            <!-- Display Messages -->
            <?php 
            $errors = $ObjFncs->getMsg('errors');
            if (isset($errors['general_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i><?php echo $errors['general_error']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="resetForm" class="needs-validation" novalidate>
                <!-- New Password Field with Bootstrap Input Group -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-dark">
                        <i class="bi bi-lock me-2 text-primary"></i>New Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-lock text-primary"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0 border-end-0 <?php echo isset($errors['password_error']) ? 'is-invalid' : ''; ?>" 
                               id="password" 
                               name="password" 
                               placeholder="Enter new password"
                               style="background: rgba(255, 255, 255, 0.9); padding-left: 0.5rem; padding-right: 0.5rem;"
                               minlength="8"
                               required>
                        <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" style="border-radius: 0 12px 12px 0; background: rgba(255, 255, 255, 0.9);">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                        <div class="valid-feedback">
                            <i class="bi bi-check-circle me-1"></i>Strong password!
                        </div>
                        <?php if (isset($errors['password_error'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $errors['password_error']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Password Strength Indicator -->
                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Password strength:</small>
                            <small id="strengthText" class="text-muted">Weak</small>
                        </div>
                        <div class="progress" style="height: 4px; border-radius: 2px;">
                            <div id="strengthBar" class="progress-bar" role="progressbar" style="width: 0%; transition: all 0.3s ease;"></div>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password Field with Bootstrap Input Group -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold text-dark">
                        <i class="bi bi-shield-check me-2 text-primary"></i>Confirm Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-shield-check text-primary"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0 border-end-0 <?php echo isset($errors['confirm_password_error']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Confirm your password"
                               style="background: rgba(255, 255, 255, 0.9); padding-left: 0.5rem; padding-right: 0.5rem;"
                               required>
                        <button class="btn btn-outline-secondary border-start-0" type="button" id="toggleConfirmPassword" style="border-radius: 0 12px 12px 0; background: rgba(255, 255, 255, 0.9);">
                            <i class="bi bi-eye" id="eyeIcon2"></i>
                        </button>
                        <div class="valid-feedback">
                            <i class="bi bi-check-circle me-1"></i>Passwords match!
                        </div>
                        <?php if (isset($errors['confirm_password_error'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $errors['confirm_password_error']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password Requirements Card -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body py-3">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-info-circle me-2 text-info"></i>Password Requirements
                        </h6>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li><i class="bi bi-check2 me-2" id="req1"></i>At least 8 characters</li>
                            <li><i class="bi bi-check2 me-2" id="req2"></i>One uppercase letter</li>
                            <li><i class="bi bi-check2 me-2" id="req3"></i>One lowercase letter</li>
                            <li><i class="bi bi-check2 me-2" id="req4"></i>One number</li>
                        </ul>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="reset_password" class="btn btn-reset position-relative" id="submitBtn">
                        <span id="submitText">
                            <i class="bi bi-shield-check me-2"></i>Reset Password
                        </span>
                        <span id="submitSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Resetting Password...
                        </span>
                    </button>
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
                            showLoadingState();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const eyeIcon2 = document.getElementById('eyeIcon2');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                eyeIcon2.className = 'bi bi-eye-slash';
            } else {
                confirmPasswordInput.type = 'password';
                eyeIcon2.className = 'bi bi-eye';
            }
        });

        // Enhanced password strength checker
        document.getElementById('password')?.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthUI(strength);
            updateRequirements(password);
            
            // Real-time validation
            if (password.length > 0) {
                if (strength.score >= 3) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            }
        });

        // Confirm password validation
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword && password.length >= 8) {
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

        function calculatePasswordStrength(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            return { score, feedback };
        }

        function updatePasswordStrengthUI(strength) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            const percentage = (strength.score / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            if (strength.score <= 1) {
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-danger';
            } else if (strength.score <= 2) {
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-warning';
            } else if (strength.score <= 3) {
                strengthBar.className = 'progress-bar bg-info';
                strengthText.textContent = 'Good';
                strengthText.className = 'text-info';
            } else if (strength.score <= 4) {
                strengthBar.className = 'progress-bar bg-primary';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-primary';
            } else {
                strengthBar.className = 'progress-bar bg-success';
                strengthText.textContent = 'Very Strong';
                strengthText.className = 'text-success';
            }
        }

        function updateRequirements(password) {
            const requirements = [
                { id: 'req1', test: password.length >= 8 },
                { id: 'req2', test: /[A-Z]/.test(password) },
                { id: 'req3', test: /[a-z]/.test(password) },
                { id: 'req4', test: /[0-9]/.test(password) }
            ];

            requirements.forEach(req => {
                const element = document.getElementById(req.id);
                if (req.test) {
                    element.className = 'bi bi-check-circle-fill me-2 text-success';
                } else {
                    element.className = 'bi bi-check2 me-2 text-muted';
                }
            });
        }

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

        // Auto-focus password field
        window.addEventListener('load', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.focus();
            }
        });
    </script>
</body>
</html>