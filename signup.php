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
            padding: 1rem 1rem 1rem 3rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }
        
        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.1rem;
            z-index: 3;
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
                            // Initialize variables to prevent errors
                            $err = array();
                            $msg = '';
                            
                            // Only include ClassAutoLoad if we need form processing
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                require_once 'ClassAutoLoad.php';
                                $ObjFncs = new fncs();
                                $err = $ObjFncs->getMsg('errors') ?: array();
                                $msg = $ObjFncs->getMsg('msg') ?: '';
                                echo $msg;
                            }
                            ?>
                            
                            <form action="" method="post" autocomplete="off">
                                <div class="form-group">
                                    <label for="fullname" class="form-label">
                                        <i class="bi bi-person me-2"></i>Full Name
                                    </label>
                                    <div class="position-relative">
                                        <i class="bi bi-person form-icon"></i>
                                        <input type="text" 
                                               class="form-control" 
                                               id="fullname" 
                                               name="fullname" 
                                               placeholder="Enter your full name" 
                                               value="<?php echo isset($_SESSION['fullname']) ? $_SESSION['fullname'] : ''; ?>" 
                                               required>
                                    </div>
                                    <?php if(isset($err['fullname_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['fullname_error']; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
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
                                    <?php if(isset($err['mailFormat_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['mailFormat_error']; ?>
                                        </div>
                                    <?php } ?>
                                    <?php if(isset($err['mailDomain_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['mailDomain_error']; ?>
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
                                               placeholder="Create a strong password" 
                                               value="<?php echo isset($_SESSION['password']) ? $_SESSION['password'] : ''; ?>" 
                                               required>
                                    </div>
                                    <?php if(isset($err['password_error'])) { ?>
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $err['password_error']; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-signup" name="signup">
                                    <i class="bi bi-person-plus me-2"></i>Create Account
                                </button>
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
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = getPasswordStrength(password);
            // You can add visual password strength indicator here
        });
        
        function getPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
    </script>
</body>
</html>