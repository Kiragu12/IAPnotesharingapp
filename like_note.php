<?php
/*
 * like_note.php
 * Purpose: Record a like for a given note by a user. Prevents duplicate likes.
 * Usage: POST `note_id`. Currently uses a fixed user (user_id = 1) for demo — replace with session user.
 */
include 'db_connect.php';

$note_id = $_POST['note_id'];
$user_id = 1; // Assume logged in user (change to session later)

// Prevent duplicate likes
$check = $conn->prepare("SELECT * FROM likes WHERE note_id = ? AND user_id = ?");
$check->bind_param("ii", $note_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO likes (note_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $note_id, $user_id);
    $stmt->execute();
}

header("Location: browse_community.php");
exit;
?>
