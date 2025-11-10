<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "notes_app");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine view mode: recent (default) or top
$mode = isset($_GET['mode']) && $_GET['mode'] === 'top' ? 'top' : 'recent';

// Handle search
$search = "";
$where = "";
if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $where = "WHERE title LIKE '%$search%' OR description LIKE '%$search%' OR hashtags LIKE '%$search%'";
}

if ($mode === 'top') {
    $sql = "SELECT * FROM notes $where ORDER BY views DESC LIMIT 20";
} else {
    $sql = "SELECT * FROM notes $where ORDER BY upload_date DESC LIMIT 20";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>discover notes</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f6f8; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; }
    .topbar { display:flex; gap:10px; align-items:center; margin-bottom:15px; }
    .search { flex:1; }
    input[type="text"]{ padding:10px; width:100%; border-radius:8px; border:1px solid #ccc; }
    button { padding:10px 14px; border-radius:8px; border:none; cursor:pointer; }
    .btn-primary { background:#0069d9; color:white; }
    .btn-secondary { background:#e9ecef; color:#333; }
    .note { background:white; padding:14px; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,0.06); margin-bottom:12px; }
    .meta { color:#666; font-size:13px; margin-bottom:8px; }
    .hashtags { color:#0d6efd; margin-top:8px; }
    .modes { margin-left:8px; }
    a.tag { color:#0d6efd; text-decoration:none; margin-right:6px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>📚 Browse Community Notes</h2>

    <div class="topbar">
      <form class="search" method="get" action="">
        <input type="text" name="search" placeholder="Search notes or #hashtags" value="<?php echo htmlspecialchars($search); ?>">
      </form>

      <div class="modes">
        <a href="?<?php echo http_build_query(['mode'=>'recent','search'=>$search]); ?>" class="btn-secondary btn">Recent</a>
        <a href="?<?php echo http_build_query(['mode'=>'top','search'=>$search]); ?>" class="btn-primary btn">Top</a>
      </div>
    </div>

    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $id = (int)$row['id'];
            echo "<div class='note'>";
            echo "<div class='title'><a href='view_note.php?id={$id}'>" . htmlspecialchars($row['title']) . "</a></div>";
            echo "<div class='meta'>Uploaded by " . htmlspecialchars($row['uploader']) . " • " . htmlspecialchars($row['upload_date']) . " • " . intval($row['views']) . " views</div>";
            echo "<div>" . nl2br(htmlspecialchars($row['description'])) . "</div>";
            // split hashtags into clickable tags
            $tags = array_filter(array_map('trim', explode(' ', $row['hashtags'])));
            if (!empty($tags)) {
                echo "<div class='hashtags'>";
                foreach ($tags as $t) {
                    $tenc = urlencode($t);
                    echo "<a class='tag' href='?search={$tenc}&mode={$mode}'>" . htmlspecialchars($t) . "</a>";
                }
                echo "</div>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No notes found.</p>";
    }
    $conn->close();
    ?>
  </div>
</body>
</html>
