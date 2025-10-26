<?php
session_start();
require_once '../config/ClassAutoLoad.php';

// Debug logging
$debug_log = __DIR__ . '/../debug.log';
error_log("DEBUG: logout.php accessed - " . date('Y-m-d H:i:s'), 3, $debug_log);

try {
    // Initialize configuration and auth object (these should be available from ClassAutoLoad)
    if (!isset($ObjAuth)) {
        error_log("DEBUG: ObjAuth not found, creating new instance", 3, $debug_log);
        $ObjAuth = new auth();
    }
    
    if (!isset($conf)) {
        error_log("DEBUG: conf variable not found, this is a problem", 3, $debug_log);
        // Load configuration manually if needed
        require_once '../config/conf.php';
    }
    
    error_log("DEBUG: About to call logout function", 3, $debug_log);
    
    // Logout user and clear remember tokens
    $ObjAuth->logout($conf);
    
} catch (Exception $e) {
    error_log("DEBUG: Exception in logout.php: " . $e->getMessage(), 3, $debug_log);
    // If logout fails, manually clear session and redirect
    session_destroy();
    header('Location: auth/signin.php');
    exit;
}
?>