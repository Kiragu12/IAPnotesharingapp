<?php
/*
 * rate_note.php
 * Purpose: Record or update a user's rating for a specific note.
 * Usage: POST `note_id` and `rating` (1-5). Currently uses a demo user (user_id = 1).
 */
include 'db_connect.php';

$note_id = $_POST['note_id'];
$rating = $_POST['rating'];
$user_id = 1; // Example user

if (!empty($rating)) {
    // Check if the user has already rated this note
    $check = $conn->prepare("SELECT * FROM ratings WHERE note_id = ? AND user_id = ?");
    $check->bind_param("ii", $note_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE ratings SET rating = ? WHERE note_id = ? AND user_id = ?");
        $update->bind_param("iii", $rating, $note_id, $user_id);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO ratings (note_id, user_id, rating) VALUES (?, ?, ?)");
        $insert->bind_param("iii", $note_id, $user_id, $rating);
        $insert->execute();
    }
}

header("Location: browse_community.php");
exit;
?>
