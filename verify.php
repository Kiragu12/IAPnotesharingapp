<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes Sharing App - Verify Account</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verify-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 2rem;
        }
        .verify-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            color: white;
            text-align: center;
        }
        .verification-code-input {
            width: 60px;
            height: 60px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 0.5rem;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            transition: all 0.3s ease;
        }
        .verification-code-input:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
            background: white;
            outline: none;
        }
        .verification-code-input.filled {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        .btn-verify {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            color: white;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }
        .btn-resend {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-resend:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }
        .text-light-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .navbar {
            background: rgba(0, 0, 0, 0.1) !important;
            backdrop-filter: blur(10px);
        }
        .countdown {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffc107;
        }
        .expired {
            color: #dc3545 !important;
        }
        .security-notice {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-journal-bookmark me-2"></i>Notes Sharing App
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link" href="signin.php">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-8">
                <!-- Verification Form -->
                <div class="verify-container">
                    <div class="verify-card">
                        <!-- Header -->
                        <div class="mb-4">
                            <div class="icon-circle">
                                <i class="bi bi-shield-check" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Verify Your Account</h2>
                            <p class="text-light-50 mb-3">
                                We've sent a 6-digit verification code to your email address. 
                                Enter the code below to complete your registration.
                            </p>
                            <div class="countdown" id="countdown">
                                <i class="bi bi-clock me-2"></i>Code expires in: <span id="timer">10:00</span>
                            </div>
                        </div>

                        <!-- Verification Code Form -->
                        <form action="" method="post" id="verifyForm">
                            <div class="mb-4">
                                <label class="form-label fw-semibold mb-3">
                                    <i class="bi bi-key me-2"></i>Enter Verification Code
                                </label>
                                <div class="d-flex justify-content-center">
                                    <input type="text" maxlength="1" class="verification-code-input" id="code1" name="code1" required>
                                    <input type="text" maxlength="1" class="verification-code-input" id="code2" name="code2" required>
                                    <input type="text" maxlength="1" class="verification-code-input" id="code3" name="code3" required>
                                    <input type="text" maxlength="1" class="verification-code-input" id="code4" name="code4" required>
                                    <input type="text" maxlength="1" class="verification-code-input" id="code5" name="code5" required>
                                    <input type="text" maxlength="1" class="verification-code-input" id="code6" name="code6" required>
                                </div>
                                <input type="hidden" name="verification_code" id="fullCode">
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" name="verify" class="btn btn-verify btn-lg" id="verifyBtn">
                                    <i class="bi bi-check-circle me-2"></i>Verify Account
                                </button>
                            </div>
                            
                            <div class="text-center">
                                <p class="text-light-50 mb-2">Didn't receive the code?</p>
                                <button type="button" class="btn btn-resend" id="resendBtn">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Resend Code
                                </button>
                            </div>
                        </form>

                        <!-- Security Notice -->
                        <div class="security-notice">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle me-3 fs-4"></i>
                                <div>
                                    <h6 class="mb-1">Security Notice</h6>
                                    <small class="text-light-50">
                                        For your security, this verification code will expire in 10 minutes. 
                                        If you didn't request this verification, please contact support.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-shield-lock text-white"></i>
                            </div>
                            <h6 class="fw-bold">Secure Verification</h6>
                            <p class="text-muted mb-0 small">Two-factor authentication keeps your account safe from unauthorized access.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-clock-history text-white"></i>
                            </div>
                            <h6 class="fw-bold">Quick Process</h6>
                            <p class="text-muted mb-0 small">Verification takes just a few seconds once you enter the correct code.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3">
                            <div class="bg-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope-check text-white"></i>
                            </div>
                            <h6 class="fw-bold">Email Protected</h6>
                            <p class="text-muted mb-0 small">Verification ensures your email address is valid and belongs to you.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-focus and navigation between inputs
        const inputs = document.querySelectorAll('.verification-code-input');
        const fullCodeInput = document.getElementById('fullCode');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                // Add filled class
                if (value) {
                    e.target.classList.add('filled');
                    // Move to next input
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }
                
                // Update full code
                updateFullCode();
            });
            
            input.addEventListener('keydown', function(e) {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                    updateFullCode();
                }
                
                // Handle paste
                if (e.key === 'v' && e.ctrlKey) {
                    setTimeout(() => {
                        const pastedText = e.target.value;
                        if (pastedText.length === 6 && /^\d{6}$/.test(pastedText)) {
                            for (let i = 0; i < 6; i++) {
                                inputs[i].value = pastedText[i];
                                inputs[i].classList.add('filled');
                            }
                            updateFullCode();
                        }
                    }, 10);
                }
            });
        });
        
        function updateFullCode() {
            const code = Array.from(inputs).map(input => input.value).join('');
            fullCodeInput.value = code;
            
            // Enable/disable verify button
            const verifyBtn = document.getElementById('verifyBtn');
            if (code.length === 6) {
                verifyBtn.disabled = false;
                verifyBtn.style.opacity = '1';
            } else {
                verifyBtn.disabled = true;
                verifyBtn.style.opacity = '0.7';
            }
        }
        
        // Countdown timer
        let timeLeft = 600; // 10 minutes in seconds
        const timerElement = document.getElementById('timer');
        const countdownElement = document.getElementById('countdown');
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                countdownElement.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>Code has expired';
                countdownElement.classList.add('expired');
                document.getElementById('verifyBtn').disabled = true;
                document.getElementById('verifyBtn').innerHTML = '<i class="bi bi-x-circle me-2"></i>Code Expired';
            } else {
                timeLeft--;
            }
        }
        
        // Update timer every second
        setInterval(updateTimer, 1000);
        updateTimer();
        
        // Resend code functionality
        document.getElementById('resendBtn').addEventListener('click', function() {
            // Reset timer
            timeLeft = 600;
            countdownElement.classList.remove('expired');
            countdownElement.innerHTML = '<i class="bi bi-clock me-2"></i>Code expires in: <span id="timer">10:00</span>';
            
            // Clear inputs
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('filled');
            });
            updateFullCode();
            
            // Focus first input
            inputs[0].focus();
            
            // Show success message
            this.innerHTML = '<i class="bi bi-check-circle me-2"></i>Code Sent!';
            this.style.background = 'rgba(40, 167, 69, 0.3)';
            this.style.borderColor = '#28a745';
            
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Resend Code';
                this.style.background = 'transparent';
                this.style.borderColor = 'rgba(255, 255, 255, 0.3)';
            }, 2000);
            
            // Here you would make an AJAX call to resend the code
            // fetch('resend_code.php', { method: 'POST' });
        });
        
        // Auto-focus first input on load
        document.addEventListener('DOMContentLoaded', function() {
            inputs[0].focus();
            
            // Add smooth animation
            const card = document.querySelector('.verify-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Form submission
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const code = fullCodeInput.value;
            if (code.length !== 6) {
                e.preventDefault();
                alert('Please enter the complete 6-digit verification code.');
            }
        });
    </script>
</body>
</html>