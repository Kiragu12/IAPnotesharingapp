<?php
$conn = new mysqli("localhost", "root", "", "notes_app");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['id'])) {
    die("No note specified.");
}
$id = (int)$_GET['id'];

// increment views safely
$update = $conn->prepare("UPDATE notes SET views = views + 1 WHERE id = ?");
$update->bind_param("i", $id);
$update->execute();
$update->close();

$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) { echo "Note not found."; exit; }
$row = $res->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title><?php echo htmlspecialchars($row['title']); ?></title></head>
<body>
  <h2><?php echo htmlspecialchars($row['title']); ?></h2>
  <div>Uploaded by <?php echo htmlspecialchars($row['uploader']); ?> on <?php echo htmlspecialchars($row['upload_date']); ?></div>
  <hr>
  <div><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
  <p>Tags: <?php echo htmlspecialchars($row['hashtags']); ?></p>
  <p><a href="browse_community.php">Back to browse</a></p>
</body>
</html>
