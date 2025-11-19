<?php
class auth{

    // Method to bind email template variables
    public function bindEmailTemplate($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }
        return $template;
    }

    public function signup($conf, $ObjFncs, $lang, $ObjSendMail){
        // Debug output to custom log file
        $debug_log = dirname(__DIR__, 3) . '/debug.log';
        error_log("DEBUG: Signup function called - " . date('Y-m-d H:i:s'), 3, $debug_log);
        
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])){
            error_log("DEBUG: POST request with signup button detected", 3, $debug_log);

        $errors = array(); // Initialize an array to hold error messages

        $fullname = $_SESSION['fullname'] = ucwords(strtolower($_POST['fullname']));
        $email = $_SESSION['email'] = strtolower($_POST['email']);
        $password = $_SESSION['password'] = $_POST['password'];
        
        error_log("DEBUG: Form data - Name: $fullname, Email: $email", 3, $debug_log);

            // Simple validation (you can expand this as needed)

            // Verify fullname
            if(empty($fullname) || !preg_match("/^[a-zA-Z ]*$/", $fullname)) {
                $errors['fullname_error'] = "Only letters and white space allowed in fullname";
            }

            // Verify email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['mailFormat_error'] = "Invalid Email format";
            }

            // Verify if the email domainis valid
            $email_domain = substr(strrchr($email, "@"), 1);
            if (!in_array($email_domain, $conf['valid_email_domain'])) {
                $errors['mailDomain_error'] = "Email domain must be one of the following: " . implode(", ", $conf['valid_email_domain']);
            }
            // Verify password length
            if(strlen($password) < $conf['min_password_length']) {
                $errors['password_error'] = "Password must be at least " . $conf['min_password_length'] . " characters long";
            }
            //HAsh the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            error_log("DEBUG: Validation completed. Errors count: " . count($errors), 3, $debug_log);

            // If there are errors, display them
            if(!count($errors)){
                error_log("DEBUG: No validation errors, proceeding with database operations", 3, $debug_log);

                try {
                    // Check if email already exists
                    $db = new Database($conf);
                    error_log("DEBUG: Database connection established", 3, $debug_log);
                    
                    $sql_check = "SELECT id FROM users WHERE email = :email LIMIT 1";
                    $existing_user = $db->fetchOne($sql_check, [':email' => $email]);
                    
                    if ($existing_user) {
                        error_log("DEBUG: Email already exists", 3, $debug_log);
                        $ObjFncs->setMsg('msg', 'An account with this email already exists. Please sign in instead.', 'danger');
                        return false;
                    }
                    
                    error_log("DEBUG: Email is available, proceeding with insert", 3, $debug_log);
                    
                    // Save user to database (match current schema)
                    // Note: some deployments do not have `username` or `is_verified` columns,
                    // so we insert into existing columns `email`, `password`, `full_name`,
                    // `email_verified` and `is_2fa_enabled`.
                    $sql_insert = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) \
                                   VALUES (:email, :password, :full_name, 1, 1, NOW())";
                    $stmt = $db->query($sql_insert, [
                        ':email' => $email,
                        ':password' => $hashedPassword,
                        ':full_name' => $fullname
                    ]);
                    
                    $rows_affected = $stmt->rowCount();
                    error_log("DEBUG: Insert executed, rows affected: $rows_affected", 3, $debug_log);
                    
                    if ($rows_affected > 0) {
                        // Get the new user ID  
                        $user_id = $db->getPDO()->lastInsertId();
                        error_log("DEBUG: User created with ID: $user_id", 3, $debug_log);
                        
                        // Send confirmation/welcome email
                        $mailCnt = [
                            'name_from' => $conf['site_name'],
                            'mail_from' => $conf['admin_email'],
                            'name_to' => $fullname,
                            'mail_to' => $email,
                            'subject' => 'Welcome to ' . $conf['site_name'] . ' - Account Created Successfully!',
                            'body' => $this->buildConfirmationEmail($fullname, $email, $conf)
                        ];

                        error_log("DEBUG: Attempting to send email", 3, $debug_log);
                        $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
                        error_log("DEBUG: Email sent result: " . ($email_sent ? 'true' : 'false'), 3, $debug_log);
                        
                        // Clear signup session data
                        unset($_SESSION['fullname']);
                        unset($_SESSION['email']);
                        unset($_SESSION['password']);
                        
                        // Set success message for home page
                        if ($email_sent) {
                            $ObjFncs->setMsg('signup_success', 
                                "🎉 Account created successfully! A confirmation email has been sent to $email. You can now sign in with your credentials.", 
                                'success'
                            );
                        } else {
                            $ObjFncs->setMsg('signup_success', 
                                "🎉 Account created successfully! However, there was an issue sending the confirmation email. You can still sign in with your credentials.", 
                                'warning'
                            );
                        }
                        
                        error_log("DEBUG: About to redirect to index.php", 3, $debug_log);
                        
                        // Redirect to home page
                        header('Location: ../../views/index.php');
                        exit();
                    } else {
                        $ObjFncs->setMsg('msg', 'Failed to create account. Please try again.', 'danger');
                    }
                    
                } catch (Exception $e) {
                    error_log('Signup error: ' . $e->getMessage());
                    error_log('Signup error trace: ' . $e->getTraceAsString());
                    $ObjFncs->setMsg('msg', 'An error occurred during signup: ' . $e->getMessage() . '. Please try again later.', 'danger');
                }
            } else {
                // Setting errors
                $ObjFncs->setMsg('errors', $errors, 'danger');
                $ObjFncs->setMsg('msg', 'Please fix the errors below and try again.', 'danger');
            }
            
            
            

    }
    }

    // Handle login form submission (DB-backed with 2FA)
    public function login($conf, $ObjFncs, $ObjSendMail){
        $debug_log = __DIR__ . '/../../../debug.log';
        error_log("DEBUG: Login function started - " . date('Y-m-d H:i:s'), 3, $debug_log);
        
        // Expecting POST with name 'signin'
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signin'])){
            // Basic sanitization
            $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $remember_me = isset($_POST['remember_me']) ? true : false;

            // Basic validation
            if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
                $ObjFncs->setMsg('msg', 'Please provide a valid email address.', 'danger');
                return false;
            }

            if(empty($password)){
                $ObjFncs->setMsg('msg', 'Please provide your password.', 'danger');
                return false;
            }

            try{
                // Use the Database class directly to look up the user
                error_log("DEBUG: Starting database lookup for email: " . $email, 3, $debug_log);
                $db = new Database($conf);
                $sql = "SELECT id, email, password, full_name FROM users WHERE email = :email LIMIT 1";
                $user = $db->fetchOne($sql, [':email' => $email]);

                error_log("DEBUG: Database query completed. User found: " . ($user ? 'YES' : 'NO'), 3, $debug_log);

                if(!$user || !isset($user['password']) || !isset($user['id'])){
                    error_log("DEBUG: User not found or missing data", 3, $debug_log);
                    $ObjFncs->setMsg('msg', 'Invalid email or password.', 'danger');
                    return false;
                }

                // Verify password against hashed value in DB
                if(password_verify($password, $user['password'])){
                    error_log("DEBUG: Password verification successful", 3, $debug_log);
                    // Password is correct - generate 2FA code
                    if(session_status() !== PHP_SESSION_ACTIVE){
                        session_start();
                    }
                    // Regenerate session id for session fixation protection
                    if(function_exists('session_regenerate_id')){
                        session_regenerate_id(true);
                    }

                    // Generate 6-digit OTP code
                    $otp_code = sprintf("%06d", mt_rand(100000, 999999));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                    error_log("DEBUG: Generated OTP: $otp_code, expires: $expires_at", 3, $debug_log);

                    // Insert OTP into two_factor_codes table
                    $sql_insert = "INSERT INTO two_factor_codes (user_id, code, code_type, expires_at, attempts_used, ip_address) 
                                   VALUES (:user_id, :code, :code_type, :expires_at, 0, :ip_address)";
                    
                    error_log("DEBUG: About to insert OTP into database for user_id: " . $user['id'], 3, $debug_log);
                    
                    try {
                        $db->query($sql_insert, [
                            ':user_id' => $user['id'],
                            ':code' => $otp_code,
                            ':code_type' => 'login',
                            ':expires_at' => $expires_at,
                            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                        ]);
                        error_log("DEBUG: OTP inserted successfully into database", 3, $debug_log);
                    } catch (Exception $insert_error) {
                        error_log("DEBUG: Database INSERT error: " . $insert_error->getMessage(), 3, $debug_log);
                        throw $insert_error; // Re-throw to be caught by outer catch
                    }

                    // Store temporary user info in session
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_user_email'] = $user['email'];
                    $_SESSION['temp_user_name'] = $user['full_name'] ?? 'User';
                    $_SESSION['temp_remember_me'] = $remember_me;

                    // Send OTP via email
                    $mailCnt = [
                        'name_from' => $conf['site_name'],
                        'mail_from' => $conf['admin_email'],
                        'name_to' => $user['full_name'] ?? 'User',
                        'mail_to' => $user['email'],
                        'subject' => 'Your Login Verification Code - ' . $conf['site_name'],
                        'body' => "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <div style='background: #007bff; color: white; padding: 20px; text-align: center;'>
                                <h2>🔐 Login Verification Code</h2>
                            </div>
                            <div style='padding: 30px; background: #f8f9fa;'>
                                <h3>Hello {$user['full_name']}!</h3>
                                <p>Your verification code for logging into <strong>{$conf['site_name']}</strong> is:</p>
                                <div style='text-align: center; margin: 30px 0;'>
                                    <div style='background: #28a745; color: white; padding: 15px 30px; font-size: 24px; font-weight: bold; border-radius: 5px; display: inline-block; letter-spacing: 3px;'>
                                        $otp_code
                                    </div>
                                </div>
                                <p>This code will expire in 10 minutes.</p>
                                <p>If you didn't try to log in, please ignore this email.</p>
                                <hr>
                                <small style='color: #666;'>This is an automated message from {$conf['site_name']}</small>
                            </div>
                        </div>"
                    ];

                    error_log("DEBUG: About to send OTP email to " . $user['email'], 3, $debug_log);
                    
                    try {
                        $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
                        error_log("DEBUG: Email send result: " . ($email_sent ? 'SUCCESS' : 'FAILED'), 3, $debug_log);
                    } catch (Exception $e) {
                        error_log("DEBUG: Exception during email sending: " . $e->getMessage(), 3, $debug_log);
                        $email_sent = false;
                    }

                    // Store OTP in session for debugging (in case email fails)
                    $_SESSION['debug_otp'] = $otp_code;
                    
                    if ($email_sent) {
                        error_log("DEBUG: Email sent successfully, redirecting to 2FA page", 3, $debug_log);
                        $_SESSION['2fa_email_status'] = 'sent';
                    } else {
                        error_log("DEBUG: Email sending failed but proceeding to 2FA page", 3, $debug_log);
                        $_SESSION['2fa_email_status'] = 'failed';
                    }
                    
                    // Always redirect to 2FA page (even if email fails)
                    error_log("DEBUG: Redirecting to 2FA verification page", 3, $debug_log);
                    header('Location: two_factor_auth_new.php');
                    exit();
                } else {
                    $ObjFncs->setMsg('msg', 'Invalid email or password.', 'danger');
                    return false;
                }

            }catch(Exception $e){
                error_log('DEBUG: Login error caught: ' . $e->getMessage(), 3, $debug_log);
                error_log('DEBUG: Login error trace: ' . $e->getTraceAsString(), 3, $debug_log);
                $ObjFncs->setMsg('msg', 'An error occurred while trying to log you in. Please try again later.', 'danger');
                return false;
            }
        }
    }

    // Build 2FA email template
    private function build2FAEmailTemplate($variables) {
        return "
        <html>
        <head>
            <title>Two-Factor Authentication Code</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
                .header { color: #667eea; text-align: center; }
                .code-box { text-align: center; margin: 30px 0; }
                .code { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px; letter-spacing: 5px; font-size: 2em; display: inline-block; }
                .footer { font-size: 12px; color: #888; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2 class='header'>Two-Factor Authentication</h2>
                <p>Hello " . htmlspecialchars($variables['full_name']) . ",</p>
                <p>Your verification code for signing in to " . htmlspecialchars($variables['site_name']) . " is:</p>
                <div class='code-box'>
                    <div class='code'>" . $variables['otp_code'] . "</div>
                </div>
                <p><strong>This code will expire in 10 minutes.</strong></p>
                <p>If you didn't attempt to sign in, please ignore this email or contact support if you have concerns.</p>
                <div class='footer'>
                    <p>" . htmlspecialchars($variables['site_name']) . " - Secure Learning Platform</p>
                </div>
            </div>
        </body>
        </html>";
    }

    // Build welcome email template for new signups
    private function buildWelcomeEmail($variables) {
        return "
        <html>
        <head>
            <title>Welcome to " . htmlspecialchars($variables['site_name']) . "</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9; }
                .header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; margin: -20px -20px 20px -20px; }
                .content { background: white; padding: 30px; border-radius: 10px; }
                .button { display: inline-block; background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; margin: 20px 0; }
                .features { background: #f0f0f0; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .footer { font-size: 12px; color: #888; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin: 0;'>🎉 Welcome to " . htmlspecialchars($variables['site_name']) . "!</h1>
                </div>
                <div class='content'>
                    <h2>Hello " . htmlspecialchars($variables['fullname']) . "! 👋</h2>
                    <p>We're excited to have you join our learning community!</p>
                    <p>Your account has been successfully created with the email: <strong>" . htmlspecialchars($variables['email']) . "</strong></p>
                    
                    <div class='features'>
                        <h3>🚀 Getting Started:</h3>
                        <ul>
                            <li><strong>Sign In:</strong> Use your email and password to access your dashboard</li>
                            <li><strong>2FA Security:</strong> For your security, we'll send a verification code each time you sign in</li>
                            <li><strong>Share Notes:</strong> Start sharing and collaborating with your classmates</li>
                            <li><strong>Build Community:</strong> Connect with other learners and grow together</li>
                        </ul>
                    </div>
                    
                    <p>Ready to get started? Click the button below to sign in:</p>
                    <div style='text-align: center;'>
                        <a href='http://localhost/IAPnotesharingapp-1/signin.php' class='button'>Sign In Now</a>
                    </div>
                    
                    <p><strong>Note:</strong> Your account is secured with Two-Factor Authentication (2FA). Each time you sign in, we'll send a verification code to this email address.</p>
                </div>
                <div class='footer'>
                    <p>If you didn't create this account, please ignore this email.</p>
                    <p>" . htmlspecialchars($variables['site_name']) . " - Secure Learning Platform</p>
                </div>
            </div>
        </body>
        </html>";
    }

    // Build confirmation email template for new signups
    private function buildConfirmationEmail($fullname, $email, $conf) {
        return "
        <html>
        <head>
            <title>Account Created Successfully - " . htmlspecialchars($conf['site_name']) . "</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
                .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; padding: 40px 20px; }
                .header h1 { margin: 0; font-size: 28px; font-weight: 300; }
                .content { padding: 40px 30px; }
                .welcome-message { background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 20px 0; text-align: center; }
                .credentials-box { background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #2196f3; }
                .button { display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; margin: 20px 0; font-weight: 500; transition: transform 0.2s; }
                .button:hover { transform: translateY(-2px); }
                .features { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
                .feature { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
                .security-note { background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Welcome to " . htmlspecialchars($conf['site_name']) . "!</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Your account has been created successfully</p>
                </div>
                
                <div class='content'>
                    <div class='welcome-message'>
                        <h2 style='color: #667eea; margin-top: 0;'>Hello " . htmlspecialchars($fullname) . "! 👋</h2>
                        <p style='font-size: 16px; color: #555;'>Thank you for joining our learning community!</p>
                    </div>
                    
                    <div class='credentials-box'>
                        <h3 style='margin-top: 0; color: #1976d2;'>📧 Your Account Details</h3>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Status:</strong> ✅ Account Active & Ready</p>
                        <p><strong>Security:</strong> 🔐 2FA Protection Enabled</p>
                    </div>
                    
                    <div class='features'>
                        <div class='feature'>
                            <h4 style='color: #667eea; margin-top: 0;'>📝 Share Notes</h4>
                            <p style='font-size: 14px;'>Upload and share your study materials with classmates</p>
                        </div>
                        <div class='feature'>
                            <h4 style='color: #667eea; margin-top: 0;'>🤝 Collaborate</h4>
                            <p style='font-size: 14px;'>Work together and learn from each other</p>
                        </div>
                    </div>
                    
                    <div class='security-note'>
                        <h4 style='color: #856404; margin-top: 0;'>🔒 Security Information</h4>
                        <p style='margin-bottom: 0;'>Your account is protected with Two-Factor Authentication. Each time you sign in, we'll send a verification code to this email address for added security.</p>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <p style='font-size: 18px; color: #555;'>Ready to get started?</p>
                        <a href='" . $conf['site_url'] . "/signin.php' class='button'>🚀 Sign In Now</a>
                    </div>
                    
                    <div style='background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;'>
                        <h4 style='color: #155724; margin-top: 0;'>✅ Next Steps:</h4>
                        <ol style='margin-bottom: 0; color: #155724;'>
                            <li>Click the 'Sign In Now' button above</li>
                            <li>Enter your email and password</li>
                            <li>Check your email for the 2FA verification code</li>
                            <li>Enter the code and start sharing notes!</li>
                        </ol>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>If you didn't create this account, please ignore this email.</p>
                    <p><strong>" . htmlspecialchars($conf['site_name']) . "</strong> - Secure Learning Platform</p>
                    <p style='margin-top: 10px;'>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    // Create Remember Me token
    public function createRememberToken($user_id, $conf) {
        try {
            // Generate secure random token
            $token = bin2hex(random_bytes(32));
            
            // Get user device info
            $device_info = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            
            // Set expiration (30 days from now)
            $expires_at = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
            
            // Store in database
            $db = new Database($conf);
            $sql = "INSERT INTO remember_tokens (user_id, token, expires_at, device_info, ip_address) 
                    VALUES (:user_id, :token, :expires_at, :device_info, :ip_address)";
            
            $params = [
                ':user_id' => $user_id,
                ':token' => $token,
                ':expires_at' => $expires_at,
                ':device_info' => $device_info,
                ':ip_address' => $ip_address
            ];
            
            $db->execute($sql, $params);
            
            // Set secure cookie (30 days)
            setcookie(
                'remember_token', 
                $token, 
                time() + (30 * 24 * 60 * 60), // 30 days
                '/', // Path
                '', // Domain
                false, // Secure (set to true for HTTPS)
                true // HttpOnly
            );
            
            return true;
            
        } catch (Exception $e) {
            error_log('Remember token creation error: ' . $e->getMessage());
            return false;
        }
    }

    // Check for existing remember token on page load
    public function checkRememberToken($conf, $ObjFncs) {
        // Skip if user is already logged in
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Check for remember token cookie
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        
        try {
            $db = new Database($conf);
            
            // Find valid token
            $sql = "SELECT rt.user_id, rt.expires_at, u.email, u.full_name 
                    FROM remember_tokens rt 
                    JOIN users u ON rt.user_id = u.id 
                    WHERE rt.token = :token AND rt.expires_at > NOW() 
                    LIMIT 1";
            
            $result = $db->fetchOne($sql, [':token' => $token]);
            
            if ($result) {
                // Valid token found - auto login user
                if(session_status() !== PHP_SESSION_ACTIVE){
                    session_start();
                }
                
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_email'] = $result['email'];
                $_SESSION['user_name'] = $result['full_name'] ?? 'User';
                $_SESSION['auto_login'] = true; // Flag to show "Welcome back" message
                
                // Update token last used time
                $update_sql = "UPDATE remember_tokens SET created_at = NOW() WHERE token = :token";
                $db->execute($update_sql, [':token' => $token]);
                
                return true;
            } else {
                // Invalid or expired token - delete cookie
                setcookie('remember_token', '', time() - 3600, '/');
                return false;
            }
            
        } catch (Exception $e) {
            error_log('Remember token check error: ' . $e->getMessage());
            return false;
        }
    }

    // Logout and clear remember tokens
    public function logout($conf) {
        $debug_log = __DIR__ . '/../../../debug.log';
        error_log("DEBUG: Logout function called - " . date('Y-m-d H:i:s'), 3, $debug_log);
        
        if(session_status() !== PHP_SESSION_ACTIVE){
            session_start();
        }
        
        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            error_log("DEBUG: Clearing remember token", 3, $debug_log);
            
            try {
                $db = new Database($conf);
                // Delete token from database
                $sql = "DELETE FROM remember_tokens WHERE token = :token";
                $db->execute($sql, [':token' => $token]);
                error_log("DEBUG: Remember token deleted from database", 3, $debug_log);
            } catch (Exception $e) {
                error_log('Token deletion error: ' . $e->getMessage(), 3, $debug_log);
            }
            
            // Delete cookie
            setcookie('remember_token', '', time() - 3600, '/');
            error_log("DEBUG: Remember token cookie cleared", 3, $debug_log);
        }
        
        // Clear session
        error_log("DEBUG: Clearing session data", 3, $debug_log);
        session_destroy();
        
        // Redirect to signin (adjust path based on where logout is called from)
        error_log("DEBUG: Redirecting to signin page", 3, $debug_log);
        
        // Check if we're being called from views/logout.php or elsewhere
        $redirect_path = 'auth/signin.php'; // Default for views/logout.php
        if (strpos($_SERVER['REQUEST_URI'], '/views/') === false) {
            $redirect_path = '../../views/auth/signin.php'; // For other locations
        }
        
        header('Location: ' . $redirect_path);
        exit;
    }

    // Verify 2FA code during login
    public function verify2FA($conf, $ObjFncs, $verification_code) {
        $debug_log = __DIR__ . '/../../../debug.log';
        error_log("DEBUG: verify2FA called with code: $verification_code", 3, $debug_log);
        
        if (!isset($_SESSION['temp_user_id'])) {
            error_log("DEBUG: No temp_user_id in session", 3, $debug_log);
            $ObjFncs->setMsg('msg', 'Session expired. Please sign in again.', 'danger');
            return false;
        }

        $user_id = $_SESSION['temp_user_id'];
        error_log("DEBUG: Verifying code for user_id: $user_id", 3, $debug_log);
        
        try {
            $db = new Database($conf);
            
            // Check if code is valid and not expired
            $sql = "SELECT id, code, expires_at, attempts_used, max_attempts, used_at 
                    FROM two_factor_codes 
                    WHERE user_id = :user_id 
                    AND code = :code 
                    AND code_type = 'login' 
                    AND used_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT 1";
            
            $code_record = $db->fetchOne($sql, [
                ':user_id' => $user_id,
                ':code' => $verification_code
            ]);
            
            error_log("DEBUG: Code record found: " . ($code_record ? 'YES' : 'NO'), 3, $debug_log);
            if ($code_record) {
                error_log("DEBUG: Code details - expires_at: " . $code_record['expires_at'] . ", attempts: " . $code_record['attempts_used'], 3, $debug_log);
            }
            
            if (!$code_record) {
                // Increment failed attempts for all valid codes for this user
                $sql_increment = "UPDATE two_factor_codes 
                                  SET attempts_used = attempts_used + 1 
                                  WHERE user_id = :user_id 
                                  AND code_type = 'login' 
                                  AND used_at IS NULL 
                                  AND expires_at > NOW()";
                $db->query($sql_increment, [':user_id' => $user_id]);
                
                error_log("DEBUG: Code not found in database", 3, $debug_log);
                $ObjFncs->setMsg('msg', 'Invalid verification code. Please check your email and try again.', 'danger');
                return false;
            }
            
            // Check if code has expired
            if (strtotime($code_record['expires_at']) < time()) {
                error_log("DEBUG: Code expired", 3, $debug_log);
                $ObjFncs->setMsg('msg', 'Verification code has expired. Please sign in again to get a new code.', 'danger');
                return false;
            }
            
            // Check if too many attempts
            if ($code_record['attempts_used'] >= ($code_record['max_attempts'] ?? 5)) {
                error_log("DEBUG: Too many attempts", 3, $debug_log);
                $ObjFncs->setMsg('msg', 'Too many failed attempts. Please sign in again to get a new code.', 'danger');
                return false;
            }
            
            error_log("DEBUG: Code is valid, marking as used", 3, $debug_log);
            
            // Code is valid - mark it as used
            $sql_mark_used = "UPDATE two_factor_codes 
                              SET used_at = NOW() 
                              WHERE id = :code_id";
            $db->query($sql_mark_used, [':code_id' => $code_record['id']]);
            
            // Get full user data
            $sql_user = "SELECT id, email, full_name FROM users WHERE id = :user_id LIMIT 1";
            $user = $db->fetchOne($sql_user, [':user_id' => $user_id]);
            
            if (!$user) {
                error_log("DEBUG: User not found with id: $user_id", 3, $debug_log);
                $ObjFncs->setMsg('msg', 'User not found. Please sign in again.', 'danger');
                return false;
            }
            
            error_log("DEBUG: Setting session variables for user: " . $user['email'], 3, $debug_log);
            
            // Complete the login process
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            
            // Regenerate session ID for security
            if (function_exists('session_regenerate_id')) {
                session_regenerate_id(true);
            }
            
            // Set user session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'] ?? 'User';
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Set welcome message for dashboard
            $ObjFncs->setMsg('msg', '🎉 Welcome back, ' . $user['full_name'] . '! You have successfully logged in.', 'success');
            
            // Handle "Remember Me" functionality
            if (isset($_SESSION['temp_remember_me']) && $_SESSION['temp_remember_me']) {
                // Generate secure remember token
                $remember_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Store token in database
                $sql_remember = "INSERT INTO remember_tokens (user_id, token, expires_at) 
                                 VALUES (:user_id, :token, :expires_at)";
                $db->query($sql_remember, [
                    ':user_id' => $user['id'],
                    ':token' => password_hash($remember_token, PASSWORD_DEFAULT),
                    ':expires_at' => $expires_at
                ]);
                
                // Set cookie (raw token, not hashed)
                setcookie('remember_token', $remember_token, strtotime('+30 days'), '/', '', false, true);
            }
            
            // Clean up temporary session data
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_email']);
            unset($_SESSION['temp_user_name']);
            unset($_SESSION['temp_remember_me']);
            
            error_log("DEBUG: 2FA verification successful, returning true", 3, $debug_log);
            return true;
            
        } catch (Exception $e) {
            error_log('DEBUG: 2FA verification error: ' . $e->getMessage(), 3, $debug_log);
            error_log('DEBUG: 2FA error trace: ' . $e->getTraceAsString(), 3, $debug_log);
            $ObjFncs->setMsg('msg', 'An error occurred during verification. Please try again.', 'danger');
            return false;
        }
    }
}
