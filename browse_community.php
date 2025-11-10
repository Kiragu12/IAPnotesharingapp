<?php
/*
 * browse_community.php
 * Purpose: Display all community-shared notes with filtering/search, allow likes, ratings, and comments.
 * Usage: Ensure `db_connect.php` is configured to connect to the `notes_app` database. Access via web server.
 * Notes: Output is escaped with htmlspecialchars where appropriate. Replace temporary user handling with sessions in production.
 */
include 'db_connect.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Community Notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2> Browse Community Notes</h2>

    <!-- Filter & Search Form -->
    <form method="GET" action="filter_notes.php">
        <input type="text" name="search" placeholder="Search notes...">
        <input type="text" name="subject" placeholder="Subject">
        <input type="text" name="course" placeholder="Course">
        <input type="text" name="author" placeholder="Author">
        <button type="submit">Filter</button>
    </form>

    <hr>

    <?php
    // Show all notes by default
    $query = "SELECT notes.*, users.username,
             (SELECT COUNT(*) FROM likes WHERE likes.note_id = notes.id) AS like_count,
             (SELECT ROUND(AVG(rating),1) FROM ratings WHERE ratings.note_id = notes.id) AS avg_rating
              FROM notes
              JOIN users ON notes.author_id = users.id
              ORDER BY notes.created_at DESC";

    $result = $conn->query($query);

    if ($result->num_rows > 0):
        while($row = $result->fetch_assoc()):
    ?>
        <div class="note">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars(substr($row['content'], 0, 150))) ?>...</p>
            <div class="meta">
                Subject: <?= htmlspecialchars($row['subject']) ?> |
                Course: <?= htmlspecialchars($row['course']) ?> |
                Author: <?= htmlspecialchars($row['username']) ?> |
                Likes: <?= $row['like_count'] ?> |
                 Rating: <?= $row['avg_rating'] ?? 'No ratings yet' ?>
            </div>

            <!-- Like and Comment Buttons -->
            <form method="POST" action="like_note.php" style="display:inline;">
                <input type="hidden" name="note_id" value="<?= $row['id'] ?>">
                <button type="submit"> Like</button>
            </form>

            <form method="POST" action="rate_note.php" style="display:inline;">
                <input type="hidden" name="note_id" value="<?= $row['id'] ?>">
                <select name="rating">
                    <option value=""> Rate</option>
                    <option value="1">1</option><option value="2">2</option>
                    <option value="3">3</option><option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <button type="submit">Submit</button>
            </form>

            <!-- Comments Section -->
            <div class="comments">
                <?php
                $note_id = $row['id'];
                $comment_query = "SELECT comments.comment_text, users.username 
                                  FROM comments 
                                  JOIN users ON comments.user_id = users.id 
                                  WHERE comments.note_id = $note_id
                                  ORDER BY comments.created_at DESC";
                $comments = $conn->query($comment_query);

                if ($comments->num_rows > 0) {
                    while($c = $comments->fetch_assoc()) {
                        echo "<p><strong>".htmlspecialchars($c['username']).":</strong> ".htmlspecialchars($c['comment_text'])."</p>";
                    }
                } else {
                    echo "<p>No comments yet.</p>";
                }
                ?>

                <form method="POST" action="post_comment.php">
                    <input type="hidden" name="note_id" value="<?= $row['id'] ?>">
                    <input type="text" name="comment_text" placeholder="Add a comment..." required>
                    <button type="submit">Post</button>
                </form>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<p>No notes found.</p>";
    endif;

    $conn->close();
    ?>
</body>
</html>
