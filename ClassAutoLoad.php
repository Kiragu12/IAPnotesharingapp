<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once 'conf.php';

// Load required classes
require_once 'Global/fncs.php';
require_once 'Layouts/layouts.php';
require_once 'Forms/forms.php';
require_once 'Proc/auth.php';
require_once 'Global/SendMail.php';

// Initialize objects
$ObjFncs = new fncs();
$ObjLayout = new Layouts();
$ObjForm = new forms();
$ObjAuth = new auth();
$ObjSendMail = new SendMail();

// Check for remember token before processing login
$ObjAuth->checkRememberToken($conf, $ObjFncs);

