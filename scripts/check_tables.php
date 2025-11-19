<?php
require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';
try {
    $db = new Database($conf);
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}
$tables = $db->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()");
echo "Tables in database '" . $conf['db_name'] . "':\n";
foreach ($tables as $t) echo " - " . $t['table_name'] . "\n";

// Check users and categories
foreach (['users','categories'] as $want) {
    $exists = $db->tableExists($want);
    echo "Table $want exists? " . ($exists ? 'YES' : 'NO') . "\n";
    if ($exists) {
        $cols = $db->fetchAll("SELECT column_name, column_type, is_nullable FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t", [':t'=>$want]);
        echo " Columns for $want:\n";
        foreach ($cols as $c) echo "  - {$c['column_name']} ({$c['column_type']}) nullable: {$c['is_nullable']}\n";
    }
}
