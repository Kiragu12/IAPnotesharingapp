<?php
require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';
try {
    $db = new Database($conf);
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}
$cols = $db->fetchAll("SELECT column_name, column_type, is_nullable FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'notes'");
echo "Notes table columns:\n";
foreach ($cols as $c) {
    echo " - {$c['column_name']} ({$c['column_type']}) nullable: {$c['is_nullable']}\n";
}
