<?php
/**
 * Database-backed provider helpers
 * Provides get_user_by_email($email) used by Proc/auth.php
 */

/**
 * Return a user row by email or false on error/not found.
 * Expected return shape: ['id' => ..., 'email' => ..., 'password' => 'hashed_password']
 */
function get_user_by_email($email){
    global $conf;

    $email = strtolower(trim($email));
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        return false;
    }

    try{
        $db = new Database($conf);

        // Try common column names for id and password to be flexible with schema
        $sql = "SELECT 
                    COALESCE(id, user_id) AS id, 
                    email, 
                    COALESCE(password, pass) AS password
                FROM users WHERE email = :email LIMIT 1";

        $user = $db->fetchOne($sql, [':email' => $email]);

        if($user && isset($user['id']) && isset($user['password'])){
            return $user;
        }
        return false;
    }catch(Exception $e){
        error_log("get_user_by_email error: " . $e->getMessage());
        return false;
    }
}
