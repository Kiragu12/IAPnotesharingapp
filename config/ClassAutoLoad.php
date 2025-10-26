<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/conf.php';

// Load language file
$lang = array();
require_once __DIR__ . '/Lang/en.php';

// Load required classes
require_once __DIR__ . '/../app/Services/Global/fncs.php';
require_once __DIR__ . '/../views/Layouts/layouts.php';
require_once __DIR__ . '/../views/Forms/forms.php';
require_once __DIR__ . '/../app/Controllers/Proc/auth.php';
require_once __DIR__ . '/../app/Services/Global/SendMail.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';

// Initialize objects
$ObjFncs = new fncs();
$ObjLayout = new Layouts();
$ObjForm = new forms();
$ObjAuth = new auth();
$ObjSendMail = new SendMail();

// Check for remember token before processing login
$ObjAuth->checkRememberToken($conf, $ObjFncs);

