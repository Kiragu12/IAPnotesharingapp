<?php
/*
 * post_comment.php
 * Purpose: Accept a posted comment for a note and store it in the database.
 * Usage: POST `note_id` and `comment_text`. Currently uses a fixed demo user (user_id = 1).
 */
include 'db_connect.php';

$note_id = $_POST['note_id'];
$comment_text = $_POST['comment_text'];
$user_id = 1; // Temporary user (replace with session user later)

$stmt = $conn->prepare("INSERT INTO comments (note_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $note_id, $user_id, $comment_text);
$stmt->execute();

header("Location: browse_community.php");
exit;
?>
