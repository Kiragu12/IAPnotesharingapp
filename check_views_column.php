<?php
// Direct database connection using PDO
try {
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=noteshare_db;charset=utf8mb4', 'root', 'paul', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "=================================\n";
echo "CHECKING NOTES TABLE STRUCTURE\n";
echo "=================================\n\n";

// Check if views column exists
$stmt = $pdo->query("DESCRIBE notes");
$columns = $stmt->fetchAll();

echo "Notes Table Columns:\n";
echo str_pad('Field', 20) . str_pad('Type', 20) . str_pad('Default', 15) . "\n";
echo str_repeat('-', 55) . "\n";

$hasViews = false;
foreach ($columns as $col) {
    echo str_pad($col['Field'], 20) . str_pad($col['Type'], 20) . str_pad($col['Default'] ?? 'NULL', 15) . "\n";
    if ($col['Field'] === 'views') {
        $hasViews = true;
    }
}

echo "\n";
if ($hasViews) {
    echo "✓ 'views' column EXISTS\n\n";
    
    // Check sample data
    $stmt = $pdo->query("SELECT id, title, views FROM notes LIMIT 5");
    $notes = $stmt->fetchAll();
    
    echo "Sample Notes with Views:\n";
    echo str_pad('ID', 5) . str_pad('Views', 10) . "Title\n";
    echo str_repeat('-', 60) . "\n";
    foreach ($notes as $note) {
        echo str_pad($note['id'], 5) . str_pad($note['views'] ?? '0', 10) . $note['title'] . "\n";
    }
    
    echo "\n";
    
    // Check total views per user
    $stmt = $pdo->query("
        SELECT 
            users.full_name,
            users.email,
            COUNT(notes.id) as note_count,
            SUM(notes.views) as total_views
        FROM users
        LEFT JOIN notes ON notes.user_id = users.id
        GROUP BY users.id
        ORDER BY total_views DESC
        LIMIT 10
    ");
    $users = $stmt->fetchAll();
    
    echo "\nUser Total Views (Top 10):\n";
    echo str_pad('Name', 25) . str_pad('Notes', 10) . str_pad('Total Views', 15) . "\n";
    echo str_repeat('-', 50) . "\n";
    foreach ($users as $user) {
        echo str_pad($user['full_name'], 25) . 
             str_pad($user['note_count'], 10) . 
             str_pad($user['total_views'] ?? '0', 15) . "\n";
    }
    
} else {
    echo "✗ 'views' column DOES NOT EXIST!\n";
    echo "\nThe 'total_views' are coming from a column that doesn't exist in your database.\n";
    echo "You need to add the 'views' column to the notes table.\n";
}

echo "\n=================================\n";
