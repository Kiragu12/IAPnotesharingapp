<?php
require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

$db = new Database($conf);
$columns = $db->fetchAll("DESCRIBE flagged_notes");
foreach ($columns as $col) {
    echo $col['Field'] . "\n";
}
?>