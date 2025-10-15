<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authentication Pages - NotesShare Academy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .auth-grid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .auth-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        .auth-icon {
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

        .auth-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .auth-card p {
            color: #666;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .auth-card .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .auth-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-working {
            background: #28a745;
            color: white;
        }

        .status-new {
            background: #17a2b8;
            color: white;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .features-list li {
            padding: 0.3rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .features-list li i {
            color: #28a745;
            margin-right: 0.5rem;
        }

        .back-home {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 1rem 1.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }

        .database-notice {
            background: rgba(255, 193, 7, 0.2);
            border: 2px solid rgba(255, 193, 7, 0.5);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
            text-align: center;
        }

        .database-notice h4 {
            margin-bottom: 1rem;
        }

        .database-notice code {
            background: rgba(0, 0, 0, 0.2);
            padding: 0.3rem 0.5rem;
            border-radius: 5px;
            color: #fff;
        }

        @media (max-width: 768px) {
            .auth-grid {
                padding: 1rem;
            }
            
            .back-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 2rem;
                display: inline-block;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>

    <div class="auth-grid">
        <div class="page-header">
            <h1><i class="bi bi-shield-lock me-3"></i>Authentication System</h1>
            <p>Complete authentication flow with security features</p>
        </div>

        <div class="database-notice">
            <h4><i class="bi bi-database me-2"></i>Database Setup Required</h4>
            <p>Before testing these pages, make sure to run the SQL files:</p>
            <p>
                <code>database_setup.sql</code> (main tables) and 
                <code>password_reset_table.sql</code> (password reset)
            </p>
        </div>

        <div class="row g-4">
            <!-- Main Authentication Pages -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-working">Working</span>
                    <div class="auth-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h3>Sign Up</h3>
                    <p>User registration with email validation and modern UI design</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Form validation</li>
                        <li><i class="bi bi-check-circle"></i>Glass-morphism design</li>
                        <li><i class="bi bi-check-circle"></i>Responsive layout</li>
                    </ul>
                    <a href="signup.php" class="btn">
                        <i class="bi bi-person-plus me-2"></i>Sign Up Page
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-working">Working</span>
                    <div class="auth-icon">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <h3>Sign In</h3>
                    <p>Secure login with "Remember Me" functionality and auto-login detection</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Remember Me (30 days)</li>
                        <li><i class="bi bi-check-circle"></i>Auto-login detection</li>
                        <li><i class="bi bi-check-circle"></i>Personalized welcome</li>
                    </ul>
                    <a href="signin.php" class="btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Page
                    </a>
                </div>
            </div>

            <!-- Password Recovery -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-new">New</span>
                    <div class="auth-icon">
                        <i class="bi bi-key"></i>
                    </div>
                    <h3>Forgot Password</h3>
                    <p>Secure password recovery with email verification and token-based reset</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Email verification</li>
                        <li><i class="bi bi-check-circle"></i>Secure tokens (1 hour)</li>
                        <li><i class="bi bi-check-circle"></i>Professional emails</li>
                    </ul>
                    <a href="forgot_password.php" class="btn">
                        <i class="bi bi-key me-2"></i>Forgot Password
                    </a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-new">New</span>
                    <div class="auth-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3>Reset Password</h3>
                    <p>Secure password reset with strength validation and confirmation</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Password strength meter</li>
                        <li><i class="bi bi-check-circle"></i>Token validation</li>
                        <li><i class="bi bi-check-circle"></i>Password toggle visibility</li>
                    </ul>
                    <a href="reset_password.php?token=demo" class="btn">
                        <i class="bi bi-shield-lock me-2"></i>Reset Password
                    </a>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-new">New</span>
                    <div class="auth-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Two-Factor Auth</h3>
                    <p>Enhanced security with 6-digit OTP codes sent via email</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>6-digit OTP input</li>
                        <li><i class="bi bi-check-circle"></i>Auto-submit on completion</li>
                        <li><i class="bi bi-check-circle"></i>Resend with countdown</li>
                    </ul>
                    <a href="two_factor_auth.php" class="btn">
                        <i class="bi bi-shield-check me-2"></i>2FA Demo
                    </a>
                </div>
            </div>

            <!-- Email Verification -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-working">Working</span>
                    <div class="auth-icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <h3>Email Verification</h3>
                    <p>Account verification with countdown timer and modern OTP interface</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>6-digit code input</li>
                        <li><i class="bi bi-check-circle"></i>Countdown timer</li>
                        <li><i class="bi bi-check-circle"></i>Resend functionality</li>
                    </ul>
                    <a href="verify.php" class="btn">
                        <i class="bi bi-envelope-check me-2"></i>Verify Account
                    </a>
                </div>
            </div>

            <!-- Dashboard -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-working">Working</span>
                    <div class="auth-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <h3>Dashboard</h3>
                    <p>User dashboard with session management and logout functionality</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Session management</li>
                        <li><i class="bi bi-check-circle"></i>User profile display</li>
                        <li><i class="bi bi-check-circle"></i>Secure logout</li>
                    </ul>
                    <a href="dashboard.php" class="btn">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </div>
            </div>

            <!-- Logout -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-working">Working</span>
                    <div class="auth-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h3>Logout</h3>
                    <p>Secure logout with session cleanup and remember token removal</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Session destruction</li>
                        <li><i class="bi bi-check-circle"></i>Cookie cleanup</li>
                        <li><i class="bi bi-check-circle"></i>Token invalidation</li>
                    </ul>
                    <a href="logout.php" class="btn">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>

            <!-- Testing & Debug -->
            <div class="col-lg-4 col-md-6">
                <div class="auth-card position-relative">
                    <span class="status-badge status-new">Debug</span>
                    <div class="auth-icon">
                        <i class="bi bi-bug"></i>
                    </div>
                    <h3>Test Pages</h3>
                    <p>Testing utilities for cookies and remember functionality</p>
                    <ul class="features-list">
                        <li><i class="bi bi-check-circle"></i>Cookie viewer</li>
                        <li><i class="bi bi-check-circle"></i>Remember token test</li>
                        <li><i class="bi bi-check-circle"></i>Debug information</li>
                    </ul>
                    <a href="view_cookies.php" class="btn">
                        <i class="bi bi-bug me-2"></i>Debug Tools
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <div class="database-notice">
                <h4><i class="bi bi-info-circle me-2"></i>Quick Setup Guide</h4>
                <div class="row">
                    <div class="col-md-4">
                        <h6>1. Database Setup</h6>
                        <p>Run <code>database_setup.sql</code> in phpMyAdmin</p>
                    </div>
                    <div class="col-md-4">
                        <h6>2. Email Configuration</h6>
                        <p>Update SMTP settings in <code>conf.php</code></p>
                    </div>
                    <div class="col-md-4">
                        <h6>3. Test Authentication</h6>
                        <p>Create account and test all features</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>