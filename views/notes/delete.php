<?php
/**
 * Note Deletion Handler
 * Redirects to proper deletion handling in my-notes.php
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

// Get note ID from URL
$note_id = $_GET['id'] ?? null;

if ($note_id && is_numeric($note_id)) {
    // Redirect to my-notes.php with deletion parameters
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Deleting Note...</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form id="deleteForm" method="POST" action="my-notes.php">
            <input type="hidden" name="note_id" value="<?php echo htmlspecialchars($note_id); ?>">
            <input type="hidden" name="action" value="delete">
        </form>
        
        <script>
            // Auto-submit the form after confirming deletion
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            } else {
                // Redirect back to my-notes if user cancels
                window.location.href = 'my-notes.php';
            }
        </script>
    </body>
    </html>
    <?php
} else {
    // Invalid note ID, redirect to my-notes
    header('Location: my-notes.php?error=invalid_note_id');
    exit();
}
?>
