<?php
require 'conf.php';

$directories = ['Layouts', 'Forms', 'Global', 'Proc'];

spl_autoload_register(function ($class_name) use ($directories) {
    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
// Create an instance of the class
$ObjSendMail = new SendMail();
$ObjLayout = new Layouts();
$ObjForm = new Forms();

$ObjAuth = new auth();
$ObjFncs = new fncs();


// Handle login if signin form submitted
// Load provider helpers (if present) so get_user_by_email() is available
if (file_exists(__DIR__ . '/Global/providers.php')) {
    require_once __DIR__ . '/Global/providers.php';
}

$ObjAuth->login($conf, $ObjFncs);