<?php
require_once 'ClassAutoLoad.php';

// Logout user and clear remember tokens
$ObjAuth->logout($conf);
?>