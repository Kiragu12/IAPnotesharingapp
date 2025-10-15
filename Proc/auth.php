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

                // Bind email template variables
                $email_variables = [
                    'site_name' => $conf['site_name'],
                    'fullname' => $fullname,
                    'activation_code' => $conf['reg_ver_code'] // This should be a real activation code
                ];

                $mailCnt = [
                    'name_from' => $conf['site_name'],
                    'mail_from' => $conf['admin_email'],
                    'name_to' => $fullname,
                    'mail_to' => $email,
                    'subject' => $this->bindEmailTemplate($lang["ver_reg_subj"], $email_variables),
                    'body' => nl2br($this->bindEmailTemplate($lang["ver_reg_body"], $email_variables))
                ];

                $ObjSendMail->Send_Mail($conf, $mailCnt);

                // No errors, proceed with further processing (e.g., save to database)
                // die($fullname . ' ' . $email . ' ' . $password);
                $ObjFncs->setMsg('msg', 'Signup successful! Please check your email for activation instructions.', 'success');

                // Clear session data after successful signup
                unset($_SESSION['fullname']);
                unset($_SESSION['email']);
                unset($_SESSION['password']);
            } else {
                // Setting errors
                $ObjFncs->setMsg('errors', $errors, 'danger');
                $ObjFncs->setMsg('msg', 'Please fix the errors below and try again.', 'danger');
            }
            
            
            

    }
    }

    // Handle login form submission (DB-backed)
    public function login($conf, $ObjFncs){
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
                    // Successful login: start session and set user id
                    if(session_status() !== PHP_SESSION_ACTIVE){
                        session_start();
                    }
                    // Regenerate session id for session fixation protection
                    if(function_exists('session_regenerate_id')){
                        session_regenerate_id(true);
                    }
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'] ?? 'User';

                    // Handle "Remember Me" functionality
                    if($remember_me) {
                        $this->createRememberToken($user['id'], $conf);
                    }

                    // Redirect to dashboard
                    header('Location: dashboard.php');
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

    // Create Remember Me token
    private function createRememberToken($user_id, $conf) {
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
