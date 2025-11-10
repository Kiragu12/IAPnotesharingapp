<?php
// Start session
session_start();

// Load core config and services needed for signin processing
require_once '../../conf.php';
require_once '../../config/Lang/en.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';
require_once '../../app/Services/Global/SendMail.php';
require_once '../../app/Controllers/Proc/auth.php';

// instantiate helpers used by the form and auth controller
$ObjFncs = new fncs();
$ObjSendMail = new SendMail();
$ObjAuth = new auth();
$db = new Database($conf);

// Redirect to dashboard if already logged in (but not from auto-login)
if (isset($_SESSION['user_id']) && !isset($_SESSION['auto_login'])) {
    header('Location: ../dashboard.php');
    exit();
}

// Debug logging for signin.php
$debug_log = __DIR__ . '/../../debug.log';
error_log("DEBUG: signin.php accessed - Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . " - " . date('Y-m-d H:i:s'), 3, $debug_log);

if (!empty($_POST)) {
    error_log("DEBUG: POST data in signin.php: " . print_r($_POST, true), 3, $debug_log);
}

// Process login BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signin'])) {
    error_log("DEBUG: About to call login function from signin.php", 3, $debug_log);
    require_once '../../config/ClassAutoLoad.php';
    // This will redirect to two_factor_auth.php if successful
    $ObjAuth->login($conf, $ObjFncs, $ObjSendMail);
    error_log("DEBUG: Returned from login function (should not see this if redirect works)", 3, $debug_log);
    // If we're still here, login failed - errors are in session
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG: POST request received but signin parameter missing: " . print_r(array_keys($_POST), true), 3, $debug_log);
}

// Load classes for displaying messages
require_once '../../config/ClassAutoLoad.php';

// Get any error messages
$err = $ObjFncs->getMsg('errors') ?: array();
$msg = $ObjFncs->getMsg('msg') ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - NotesShare Academy</title>
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
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
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
        
        .signin-container {
            position: relative;
            z-index: 2;
            width: 100%;
        }
        
        .signin-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .signin-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            padding: 2.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .signin-logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .signin-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .signin-subtitle {
            color: #666;
            font-weight: 400;
            font-size: 1.1rem;
        }
        
        .signin-form {
            padding: 2.5rem;
        }
        
        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.7rem;
            display: block;
            font-size: 1rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
            height: auto;
        }
        
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
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
            color: #764ba2;
            font-weight: 500;
            font-size: 1.1rem;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #764ba2;
            background: white;
        }
        
        .input-group:focus-within .form-control {
            border-color: #764ba2;
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
        
        .form-check-input:checked {
            background-color: #764ba2;
            border-color: #764ba2;
        }
        
        .form-check-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        
        .btn-signin {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            border: none;
            border-radius: 15px;
            padding: 1.2rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }
        
        .btn-signin:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(118, 75, 162, 0.4);
            color: white;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .form-check {
            display: flex;
            align-items: center;
        }
        
        .form-check-input {
            margin-right: 0.7rem;
            border-radius: 4px;
        }
        
        .form-check-input:checked {
            background-color: #764ba2;
            border-color: #764ba2;
        }
        
        .forgot-link {
            color: #764ba2;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #667eea;
            text-decoration: underline;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }
        
        .signup-link a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .signup-link a:hover {
            color: #667eea;
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
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
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
        
        .welcome-message {
            background: linear-gradient(135deg, rgba(118, 75, 162, 0.1) 0%, rgba(102, 126, 234, 0.1) 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid #764ba2;
        }
        
        .welcome-message h6 {
            color: #764ba2;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .welcome-message p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .signin-card {
                margin: 1rem;
                border-radius: 15px;
            }
            
            .signin-header, .signin-form {
                padding: 2rem 1.5rem;
            }
            
            .back-btn {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .social-login {
                flex-direction: column;
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
        
        .signin-card {
            animation: slideInUp 0.8s ease forwards;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-btn">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="signin-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7 col-sm-9">
                    <div class="signin-card">
                        <div class="signin-header">
                            <div class="signin-logo">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <h2 class="signin-title">Welcome Back!</h2>
                            <p class="signin-subtitle">Sign in to continue your learning journey</p>
                        </div>
                        
                        <div class="signin-form">
                            <?php
                            // Initialize variables to prevent errors
                            $err = array();
                            $msg = '';
                            $welcome_message = "Welcome Back";
                            $welcome_subtitle = "Sign in to access your personalized dashboard and shared notes collection.";
                            
                            // Check for signup success message
                            if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
                                $signup_msg = '';
                                if (isset($_GET['msg'])) {
                                    $signup_msg = $_GET['msg'];
                                } else {
                                    require_once 'ClassAutoLoad.php';
                                    $signup_msg = $ObjFncs->getMsg('welcome_msg');
                                }
                                
                                if ($signup_msg) {
                                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                                        <i class="bi bi-check-circle me-2"></i>' . htmlspecialchars($signup_msg) . '
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>';
                                    $welcome_message = "Account Created Successfully! 🎉";
                                    $welcome_subtitle = "Your account has been created. Please sign in below to access your dashboard.";
                                }
                            }
                            
                            // Check for logout success message
                            if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
                                $welcome_message = "Logged Out Successfully";
                                $welcome_subtitle = "You have been securely logged out. Sign in again to continue.";
                                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>You have been successfully logged out!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>';
                            }
                            
                            // Check if user was auto-logged in via remember token
                            if (isset($_SESSION['auto_login']) && $_SESSION['auto_login']) {
                                $user_name = $_SESSION['user_name'] ?? 'User';
                                $welcome_message = "Welcome Back, " . htmlspecialchars($user_name) . "!";
                                $welcome_subtitle = "We remembered you! Redirecting to your dashboard...";
                                
                                // Redirect to dashboard after 2 seconds
                                echo '<script>
                                    setTimeout(function() {
                                        window.location.href = "../dashboard.php";
                                    }, 2000);
                                </script>';
                                
                                unset($_SESSION['auto_login']); // Clear the flag
                            }
                            
                            // Display any error or success messages
                            if ($msg) {
                                echo '<div class="alert alert-dismissible fade show" role="alert">' . $msg . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>';
                            }
                            ?>
                            
                            <div class="welcome-message">
                                <h6><i class="bi bi-lightbulb me-2"></i><?php echo $welcome_message; ?></h6>
                                <p><?php echo $welcome_subtitle; ?></p>
                            </div>
                            
                            <form action="" method="post" autocomplete="off" class="needs-validation" novalidate>
                                <!-- Hidden input to ensure signin parameter is sent -->
                                <input type="hidden" name="signin" value="1">
                                
                                <!-- Email Field with Bootstrap Input Group -->
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold text-dark">
                                        <i class="bi bi-envelope me-2 text-primary"></i>Email Address
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 15px 0 0 15px;">
                                            <i class="bi bi-envelope text-primary"></i>
                                        </span>
                                        <input type="email" 
                                               class="form-control border-start-0 <?php echo isset($err['email_error']) ? 'is-invalid' : ''; ?>" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email address" 
                                               value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" 
                                               style="border-radius: 0 15px 15px 0; background: #f8f9fa; padding-left: 0.5rem;"
                                               required>
                                        <div class="valid-feedback">
                                            <i class="bi bi-check-circle me-1"></i>Valid email!
                                        </div>
                                        <?php if(isset($err['email_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['email_error']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <!-- Password Field with Bootstrap Input Group and Show/Hide -->
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold text-dark">
                                        <i class="bi bi-lock me-2 text-primary"></i>Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 15px 0 0 15px;">
                                            <i class="bi bi-lock text-primary"></i>
                                        </span>
                                        <input type="password" 
                                               class="form-control border-start-0 border-end-0 <?php echo isset($err['password_error']) ? 'is-invalid' : ''; ?>" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Enter your password" 
                                               style="background: #f8f9fa; padding-left: 0.5rem; padding-right: 0.5rem;"
                                               required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" style="border-radius: 0 15px 15px 0; background: #f8f9fa;">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                        <div class="valid-feedback">
                                            <i class="bi bi-check-circle me-1"></i>Password entered!
                                        </div>
                                        <?php if(isset($err['password_error'])) { ?>
                                            <div class="invalid-feedback">
                                                <i class="bi bi-exclamation-circle me-1"></i><?php echo $err['password_error']; ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <!-- Remember Me and Forgot Password Row -->
                                <div class="row align-items-center mb-4">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember_me" value="1" style="transform: scale(1.1);">
                                            <label class="form-check-label text-muted" for="remember">
                                                Remember me for 30 days
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <a href="forgot_password.php" class="text-decoration-none text-primary fw-semibold">
                                            <i class="bi bi-question-circle me-1"></i>Forgot Password?
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Submit Button with Loading State -->
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-signin position-relative" name="signin" value="1" id="submitBtn">
                                        <span id="submitText">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
                                        </span>
                                        <span id="submitSpinner" class="d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Signing In...
                                        </span>
                                    </button>
                                </div>
                                
                                <!-- Demo Credentials Card -->
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body py-2 px-3">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle me-1"></i>Try demo credentials
                                                </small>
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="demoBtn">
                                                    <i class="bi bi-person-gear me-1"></i>Use Demo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            
                            <div class="signup-link">
                                <span class="text-muted">Don't have an account yet?</span>
                                <a href="signup.php">Create your account</a>
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
        
        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0) {
                if (password.length >= 6) {
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
        
        // Demo credentials functionality
        document.getElementById('demoBtn').addEventListener('click', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            emailInput.value = 'demo@noteshareacademy.com';
            passwordInput.value = 'demo123';
            
            // Trigger validation
            emailInput.dispatchEvent(new Event('input'));
            passwordInput.dispatchEvent(new Event('input'));
            
            // Add animation feedback
            this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Applied!';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-person-gear me-1"></i>Use Demo';
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
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
                this.parentElement.style.boxShadow = '0 4px 12px rgba(118, 75, 162, 0.15)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
                this.parentElement.style.boxShadow = 'none';
            });
        });
        
        // Auto-focus first field
        window.addEventListener('load', function() {
            const emailField = document.getElementById('email');
            if (emailField.value === '') {
                emailField.focus();
            }
        });
        
        // Remember me tooltip
        const rememberCheckbox = document.getElementById('remember');
        rememberCheckbox.setAttribute('title', 'Stay signed in for 30 days on this device');
        
        // Enhanced keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                document.querySelector('form').dispatchEvent(new Event('submit'));
            }
            
            // Alt + D for demo credentials
            if (e.altKey && e.key === 'd') {
                e.preventDefault();
                document.getElementById('demoBtn').click();
            }
        });
    </script>
</body>
</html>