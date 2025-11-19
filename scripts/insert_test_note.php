<?php
// Insert a public test note and print its view URL
require_once __DIR__ . '/../conf.php';
require_once __DIR__ . '/../app/Services/Global/Database.php';
try {
    $db = new Database($conf);
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}

// pick a user id (first user)
$user = $db->fetchOne("SELECT id, email, full_name FROM users LIMIT 1");
if (!$user) { echo "No users present in database. Create a user first.\n"; exit(1); }
$user_id = $user['id'];

// pick a category id
$cat = $db->fetchOne("SELECT id FROM categories LIMIT 1");
$category_id = $cat ? $cat['id'] : null;

$title = 'Automated Test Note ' . date('YmdHis');
$content = "This is a test note inserted by an automated test on " . date('Y-m-d H:i:s') . "\n\nPlease delete this test note if not needed.";
$summary = 'Automated test note summary';
$tags = '#test #automation';
$is_public = 1;
$status = 'published';
$note_type = 'text';

$sql = "INSERT INTO notes (user_id, category_id, title, content, summary, tags, is_public, status, views, note_type) VALUES (:u,:c,:t,:cont,:s,:tags,:pub,:st,0,:nt)";
$params = [':u'=>$user_id, ':c'=>$category_id, ':t'=>$title, ':cont'=>$content, ':s'=>$summary, ':tags'=>$tags, ':pub'=>$is_public, ':st'=>$status, ':nt'=>$note_type];
try {
    $id = $db->insert($sql, $params);
    echo "Inserted test note with id: $id\n";
    $base = rtrim($conf['site_url'], '/');
    $viewUrl = $base . '/views/notes/view.php?id=' . $id;
    $sharedUrl = $base . '/views/shared-notes.php';
    echo "View URL: $viewUrl\n";
    echo "Shared notes URL: $sharedUrl\n";
} catch (Exception $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
    exit(1);
}
