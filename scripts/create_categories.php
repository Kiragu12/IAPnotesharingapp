<?php
require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';
try {
    $db = new Database($conf);
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}

$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $db->execute($sql);
    echo "categories table created or already exists.\n";
} catch (Exception $e) {
    echo "Failed to create categories: " . $e->getMessage() . "\n";
    exit(1);
}

// Optionally insert some default categories
$defaults = ['Mathematics','Physics','Chemistry','Biology','Computer Science','English'];
foreach ($defaults as $d) {
    try {
        $exists = $db->fetchOne("SELECT id FROM categories WHERE name = :n", [':n'=>$d]);
        if (!$exists) {
            $db->execute("INSERT INTO categories (name, description) VALUES (:n, :d)", [':n'=>$d, ':d'=>null]);
            echo "Inserted default category: $d\n";
        }
    } catch (Exception $e) {
        echo "Could not insert default category $d: " . $e->getMessage() . "\n";
    }
}

