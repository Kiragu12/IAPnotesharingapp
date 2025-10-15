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
            padding: 1.2rem 1.2rem 1.2rem 3.5rem;
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
        
        .form-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #764ba2;
            font-size: 1.3rem;
            z-index: 3;
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
    <a href="index.php" class="back-btn">
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
                            
                            // Check if user was auto-logged in via remember token
                            if (isset($_SESSION['auto_login']) && $_SESSION['auto_login']) {
                                $user_name = $_SESSION['user_name'] ?? 'User';
                                $welcome_message = "Welcome Back, " . htmlspecialchars($user_name) . "!";
                                $welcome_subtitle = "We remembered you! Redirecting to your dashboard...";
                                
                                // Redirect to dashboard after 2 seconds
                                echo '<script>
                                    setTimeout(function() {
                                        window.location.href = "dashboard.php";
                                    }, 2000);
                                </script>';
                                
                                unset($_SESSION['auto_login']); // Clear the flag
                            }
                            
                            // Only include ClassAutoLoad if we need form processing
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                require_once 'ClassAutoLoad.php';
                                $ObjFncs = new fncs();
                                $err = $ObjFncs->getMsg('errors') ?: array();
                                $msg = $ObjFncs->getMsg('msg') ?: '';
                                echo $msg;
                            }
                            ?>
                            
                            <div class="welcome-message">
                                <h6><i class="bi bi-lightbulb me-2"></i><?php echo $welcome_message; ?></h6>
                                <p><?php echo $welcome_subtitle; ?></p>
                            </div>
                            
                            <form action="" method="post" autocomplete="off">
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-2"></i>Email Address
                                    </label>
                                    <div class="position-relative">
                                        <i class="bi bi-envelope form-icon"></i>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               placeholder="Enter your email address" 
                                               value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" 
                                               required>
                                    </div>
                                    <?php if(isset($err['email_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['email_error']; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock me-2"></i>Password
                                    </label>
                                    <div class="position-relative">
                                        <i class="bi bi-lock form-icon"></i>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Enter your password" 
                                               required>
                                    </div>
                                    <?php if(isset($err['password_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['password_error']; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="remember-forgot">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember_me">
                                        <label class="form-check-label" for="remember">
                                            Remember me for 30 days
                                        </label>
                                    </div>
                                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                                </div>
                                
                                <button type="submit" class="btn btn-signin" name="signin">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
                                </button>
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
        // Add floating label effect
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Demo credentials helper
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            // Add demo credentials option
            const demoBtn = document.createElement('button');
            demoBtn.type = 'button';
            demoBtn.className = 'btn btn-outline-secondary btn-sm mt-2';
            demoBtn.innerHTML = '<i class="bi bi-person-gear me-2"></i>Use Demo Credentials';
            demoBtn.style.width = '100%';
            
            demoBtn.addEventListener('click', function() {
                emailInput.value = 'demo@noteshareacademy.com';
                passwordInput.value = 'demo123';
            });
            
            document.querySelector('.signin-form form').appendChild(demoBtn);
        });
    </script>
</body>
</html>