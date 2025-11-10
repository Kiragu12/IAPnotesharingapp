<?php
/*
 * filter_notes.php
 * Purpose: Handle GET filter/search parameters and return matching notes HTML.
 * Usage: Called by `browse_community.php` via form action. Ensure `db_connect.php` is present.
 * Security note: Current query concatenates user input into SQL (vulnerable to SQL injection). Consider prepared statements.
 */
include 'db_connect.php';

$search = $_GET['search'] ?? '';
$subject = $_GET['subject'] ?? '';
$course = $_GET['course'] ?? '';
$author = $_GET['author'] ?? '';

$query = "SELECT notes.*, users.username,
         (SELECT COUNT(*) FROM likes WHERE likes.note_id = notes.id) AS like_count,
         (SELECT ROUND(AVG(rating),1) FROM ratings WHERE ratings.note_id = notes.id) AS avg_rating
          FROM notes
          JOIN users ON notes.author_id = users.id
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (notes.title LIKE '%$search%' OR notes.content LIKE '%$search%' OR notes.tags LIKE '%$search%')";
}
if (!empty($subject)) {
    $query .= " AND notes.subject = '$subject'";
}
if (!empty($course)) {
    $query .= " AND notes.course = '$course'";
}
if (!empty($author)) {
    $query .= " AND users.username LIKE '%$author%'";
}

$query .= " ORDER BY notes.created_at DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='note'>";
        echo "<h3>".htmlspecialchars($row['title'])."</h3>";
        echo "<p>".htmlspecialchars(substr($row['content'], 0, 150))."...</p>";
        echo "<p><em>Subject:</em> ".$row['subject']." | <em>Course:</em> ".$row['course']." | <em>Author:</em> ".$row['username']."</p>";
        echo "</div>";
    }
} else {
    echo "<p>No notes found for your search.</p>";
}

$conn->close();
?>
