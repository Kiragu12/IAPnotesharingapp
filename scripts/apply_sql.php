<?php
// apply_sql.php
// Usage: php scripts/apply_sql.php sql/create_notes_table.sql sql/update_notes_for_files.sql

require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';

$files = array_slice($argv, 1);
if (empty($files)) {
    echo "No SQL files specified. Usage: php scripts/apply_sql.php <file1.sql> [file2.sql ...]\n";
    exit(1);
}

$db = null;
try {
    $db = new Database($conf);
} catch (Exception $e) {
    echo "Failed to connect to database: " . $e->getMessage() . "\n";
    exit(1);
}

foreach ($files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (!file_exists($path)) {
        echo "SQL file not found: $path\n";
        continue;
    }
    echo "Applying $file...\n";
    $content = file_get_contents($path);
    // Remove comments (lines starting with --) and block comments
    $content = preg_replace('#/\*.*?\*/#s', '', $content);
    $lines = explode("\n", $content);
    $clean = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') continue;
        if (strpos($trim, '--') === 0) continue;
        $clean[] = $line;
    }
    $content = implode("\n", $clean);
    // Split statements by semicolon
    $statements = preg_split('/;\s*\n/', $content);
    $applied = 0;
    foreach ($statements as $stmt) {
        $s = trim($stmt);
        if ($s === '') continue;
        try {
            $db->execute($s);
            $applied++;
        } catch (Exception $e) {
            echo "Failed to execute statement:\n";
            echo $s . "\n";
            echo "Error: " . $e->getMessage() . "\n";
            // Do not exit; continue to next statement
        }
    }
    echo "Finished applying $file. Statements applied: $applied\n";
}

// Show created tables related to notes
try {
    $res = $db->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name LIKE '%note%'");
    echo "Related tables in database:\n";
    foreach ($res as $r) echo " - " . $r['table_name'] . "\n";
} catch (Exception $e) {
    echo "Could not list tables: " . $e->getMessage() . "\n";
}

echo "Done.\n";
