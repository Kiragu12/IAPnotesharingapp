<?php
require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';

echo "Testing DB connection...\n";
try {
    $db = new Database($conf);
    if ($db->isStubMode()) {
        echo "Database is in stub mode (no connection).\n";
        exit(0);
    }
    $pdo = $db->getPDO();
    if ($pdo) {
        echo "Connected to DB successfully. Server info: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    } else {
        echo "PDO instance not available.\n";
    }
    // Run a simple query
    $row = $db->fetchOne('SELECT 1 as test');
    var_dump($row);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Full trace: " . $e->__toString() . "\n";
}
