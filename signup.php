<?php
// Start session
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Load required files
require_once 'ClassAutoLoad.php';

// Process signup if form submitted BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // This will redirect and exit if successful, so no further code will run
    $ObjAuth->signup($conf, $ObjFncs, $lang, $ObjSendMail);
}

// Initialize variables for displaying the form
$err = $ObjFncs->getMsg('errors') ?: array();
$msg = $ObjFncs->getMsg('msg') ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - NotesShare Academy</title>
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
        
        .signup-container {
            position: relative;
            z-index: 2;
            width: 100%;
        }
        
        .signup-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .signup-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .signup-logo {
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
        
        .signup-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .signup-subtitle {
            color: #666;
            font-weight: 400;
        }
        
        .signup-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }
        
        .form-control.is-valid {
            border-color: #198754;
            background: #f8fff9;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background: #fff8f8;
        }
        
        .input-group-text {
            border: 2px solid #e9ecef;
            background: #f8f9fa;
            color: #667eea;
            font-weight: 500;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
            background: white;
        }
        
        .input-group:focus-within .form-control {
            border-color: #667eea;
        }
        
        .valid-feedback, .invalid-feedback {
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 0.5rem;
        }
        
        .valid-feedback {
            color: #198754;
        }
        
        .invalid-feedback {
            color: #dc3545;
        }
        
        .progress {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 2px;
        }
        
        .progress-bar {
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .form-check-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-signup {
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
        
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #764ba2;
        }
        
        .back-btn {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .signup-card {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .signup-header, .signup-form {
                padding: 1.5rem;
            }
            
            .back-btn {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
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
        
        .signup-card {
            animation: slideInUp 0.8s ease forwards;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="signup-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7 col-sm-9">
                    <div class="signup-card">
                        <div class="signup-header">
                            <div class="signup-logo">
                                <i class="bi bi-journal-bookmark"></i>
                            </div>
                            <h2 class="signup-title">Join NotesShare Academy</h2>
                            <p class="signup-subtitle">Start your collaborative learning journey today</p>
                        </div>
                        
                        <div class="signup-form">
                            <?php
                            // Display success/error messages
                            if (!empty($msg)) {
                                echo $msg;
                            }
                            ?>
                            
                            <form action="" method="post" autocomplete="off" class="needs-validation" novalidate>
                                <!-- Full Name Field with Bootstrap Input Group -->
                                <div class="mb-3">
                                    <label for="fullname" class="form-label fw-semibold text-dark">
                                        <i class="bi bi-person me-2 text-primary"></i>Full Name
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                            <i class="bi bi-person text-primary"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control border-start-0 <?php echo isset($err['fullname_error']) ? 'is-invalid' : ''; ?>" 
                                               id="fullname" 
                                               name="fullname" 
                                               placeholder="Enter your full name" 
                                               value="<?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : ''; ?>" 
                                               style="border-radius: 0 12px 12px 0; background: #f8f9fa; padding-left: 0.5rem;"
                                               required>
                                        <div class="valid-feedback">
                                            <i class="bi bi-check-circle me-1"></i>Looks good!
                                        </div>
                                        <?php if(isset($err['fullname_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['fullname_error']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <!-- Email Field with Bootstrap Input Group -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold text-dark">
                                        <i class="bi bi-envelope me-2 text-primary"></i>Email Address
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                            <i class="bi bi-envelope text-primary"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control border-start-0 <?php echo (isset($err['mailFormat_error']) || isset($err['mailDomain_error'])) ? 'is-invalid' : ''; ?>" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email address" 
                                               value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" 
                                               style="border-radius: 0 12px 12px 0; background: #f8f9fa; padding-left: 0.5rem;"
                                               required>
                                        <div class="valid-feedback">
                                            <i class="bi bi-check-circle me-1"></i>Valid email format!
                                        </div>
                                        <?php if(isset($err['mailFormat_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['mailFormat_error']; ?>
                                            </div>
                                        <?php } ?>
                                        <?php if(isset($err['mailDomain_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['mailDomain_error']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <!-- Password Field with Bootstrap Input Group and Strength Indicator -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold text-dark">
                                        <i class="bi bi-lock me-2 text-primary"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px;">
                                            <i class="bi bi-lock text-primary"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control border-start-0 border-end-0 <?php echo isset($err['password_error']) ? 'is-invalid' : ''; ?>" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Create a strong password" 
                                               value="<?php echo isset($_SESSION['password']) ? $_SESSION['password'] : ''; ?>" 
                                               style="background: #f8f9fa; padding-left: 0.5rem; padding-right: 0.5rem;"
                                               required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" style="border-radius: 0 12px 12px 0; background: #f8f9fa;">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                        <div class="valid-feedback">
                                            <i class="bi bi-check-circle me-1"></i>Strong password!
                                        </div>
                                        <?php if(isset($err['password_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['password_error']; ?>
                                            </div>
                                        <?php } ?>
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
                                
                                <!-- Terms and Conditions with Custom Checkbox -->
                                <div class="mb-4">
                                    <div class="form-check d-flex align-items-start">
                                        <input class="form-check-input me-3 mt-1" type="checkbox" id="terms" required style="transform: scale(1.2);">
                                        <label class="form-check-label text-muted" for="terms">
                                            I agree to the <a href="#" class="text-decoration-none text-primary fw-semibold">Terms of Service</a> and <a href="#" class="text-decoration-none text-primary fw-semibold">Privacy Policy</a>
                                        </label>
                                        <div class="invalid-feedback">
                                            You must agree to the terms before submitting.
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Submit Button with Loading State -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-signup position-relative" name="signup" id="submitBtn">
                                        <span id="submitText">
                                            <i class="bi bi-person-plus me-2"></i>Create Account
                                        </span>
                                        <span id="submitSpinner" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Creating Account...
                                        </span>
                                    </button>
                                </div>
                            </form>
                            
                            <div class="login-link">
                                <span class="text-muted">Already have an account?</span>
                                <a href="signin.php">Sign in here</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
        
        // Enhanced password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthUI(strength);
            
            // Add real-time validation
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
        
        function calculatePasswordStrength(password) {
            let score = 0;
            let feedback = [];
            
            if (password.length >= 8) {
                score++;
            } else {
                feedback.push('At least 8 characters');
            }
            
            if (/[a-z]/.test(password)) {
                score++;
            } else {
                feedback.push('Lowercase letter');
            }
            
            if (/[A-Z]/.test(password)) {
                score++;
            } else {
                feedback.push('Uppercase letter');
            }
            
            if (/[0-9]/.test(password)) {
                score++;
            } else {
                feedback.push('Number');
            }
            
            if (/[^A-Za-z0-9]/.test(password)) {
                score++;
            } else {
                feedback.push('Special character');
            }
            
            return { score, feedback };
        }
        
        function updatePasswordStrengthUI(strength) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            const percentage = (strength.score / 5) * 100;
            strengthBar.style.width = percentage + '%';
            
            // Update color and text based on strength
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
            }
        });
        
        // Real-time full name validation
        document.getElementById('fullname').addEventListener('input', function() {
            const fullname = this.value.trim();
            
            if (fullname.length > 0) {
                if (fullname.length >= 2 && /^[a-zA-Z\s]+$/.test(fullname)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
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
        
        // Auto-focus first field
        window.addEventListener('load', function() {
            document.getElementById('fullname').focus();
        });
    </script>
</body>
</html>