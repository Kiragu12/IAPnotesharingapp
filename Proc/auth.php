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
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])){

        $errors = array(); // Initialize an array to hold error messages

        $fullname = $_SESSION['fullname'] = ucwords(strtolower($_POST['fullname']));
        $email = $_SESSION['email'] = strtolower($_POST['email']);
        $password = $_SESSION['password'] = $_POST['password'];

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

            // If there are errors, display them
            if(!count($errors)){

                try {
                    // Check if email already exists
                    $db = new Database($conf);
                    $sql_check = "SELECT id FROM users WHERE email = :email LIMIT 1";
                    $existing_user = $db->fetchOne($sql_check, [':email' => $email]);
                    
                    if ($existing_user) {
                        $ObjFncs->setMsg('msg', 'An account with this email already exists. Please sign in instead.', 'danger');
                        return false;
                    }
                    
                    // Save user to database
                    $sql_insert = "INSERT INTO users (email, password, full_name, email_verified, is_2fa_enabled, created_at) 
                                   VALUES (:email, :password, :full_name, 0, 1, NOW())";
                    $db->execute($sql_insert, [
                        ':email' => $email,
                        ':password' => $hashedPassword,
                        ':full_name' => $fullname
                    ]);
                    
                    // Send welcome email
                    $email_variables = [
                        'site_name' => $conf['site_name'],
                        'fullname' => $fullname,
                        'email' => $email
                    ];

                    $mailCnt = [
                        'name_from' => $conf['site_name'],
                        'mail_from' => $conf['admin_email'],
                        'name_to' => $fullname,
                        'mail_to' => $email,
                        'subject' => 'Welcome to ' . $conf['site_name'] . '!',
                        'body' => $this->buildWelcomeEmail($email_variables)
                    ];

                    $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);

                    if ($email_sent) {
                        $ObjFncs->setMsg('welcome_msg', 'Account created successfully! 🎉 A welcome email has been sent to ' . $email . '. You can now sign in with your credentials.', 'success');
                    } else {
                        $ObjFncs->setMsg('welcome_msg', 'Account created successfully! 🎉 However, there was an issue sending the welcome email. You can still sign in with your credentials.', 'warning');
                    }

                    // Clear session data after successful signup
                    unset($_SESSION['fullname']);
                    unset($_SESSION['email']);
                    unset($_SESSION['password']);
                    
                    // Redirect to signin page with success message
                    header('Location: signin.php?signup=success&msg=' . urlencode('Account created successfully! Please check your email for the welcome message.'));
                    exit();
                    
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
                $db = new Database($conf);
                $sql = "SELECT id, email, password, full_name FROM users WHERE email = :email LIMIT 1";
                $user = $db->fetchOne($sql, [':email' => $email]);

                if(!$user || !isset($user['password']) || !isset($user['id'])){
                    $ObjFncs->setMsg('msg', 'Invalid email or password.', 'danger');
                    return false;
                }

                // Verify password against hashed value in DB
                if(password_verify($password, $user['password'])){
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

                    // Insert OTP into two_factor_codes table
                    $sql_insert = "INSERT INTO two_factor_codes (user_id, code, expires_at, attempts_used, code_type) 
                                   VALUES (:user_id, :code, :expires_at, 0, 'login')";
                    $db->execute($sql_insert, [
                        ':user_id' => $user['id'],
                        ':code' => $otp_code,
                        ':expires_at' => $expires_at
                    ]);

                    // Store temporary user info in session
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_user_email'] = $user['email'];
                    $_SESSION['temp_user_name'] = $user['full_name'] ?? 'User';
                    $_SESSION['temp_remember_me'] = $remember_me;

                    // Send OTP via email
                    $email_variables = [
                        'site_name' => $conf['site_name'],
                        'full_name' => $user['full_name'],
                        'otp_code' => $otp_code
                    ];

                    $mailCnt = [
                        'name_from' => $conf['site_name'],
                        'mail_from' => $conf['admin_email'],
                        'name_to' => $user['full_name'],
                        'mail_to' => $user['email'],
                        'subject' => 'Two-Factor Authentication Code - ' . $conf['site_name'],
                        'body' => $this->build2FAEmailTemplate($email_variables)
                    ];

                    $email_sent = $ObjSendMail->Send_Mail($conf, $mailCnt);
                    
                    // Note: We proceed with 2FA even if email fails, user can request resend
                    if (!$email_sent) {
                        error_log('2FA email failed to send to: ' . $user['email']);
                    }

                    // Redirect to 2FA verification page
                    header('Location: two_factor_auth.php');
                    exit;
                } else {
                    $ObjFncs->setMsg('msg', 'Invalid email or password.', 'danger');
                    return false;
                }

            }catch(Exception $e){
                error_log('Login error: ' . $e->getMessage());
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
        if(session_status() !== PHP_SESSION_ACTIVE){
            session_start();
        }
        
        // Clear remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            try {
                $db = new Database($conf);
                // Delete token from database
                $sql = "DELETE FROM remember_tokens WHERE token = :token";
                $db->execute($sql, [':token' => $token]);
            } catch (Exception $e) {
                error_log('Token deletion error: ' . $e->getMessage());
            }
            
            // Delete cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear session
        session_destroy();
        
        // Redirect to signin
        header('Location: signin.php');
        exit;
    }
}
