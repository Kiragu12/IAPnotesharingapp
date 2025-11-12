<?php
/**
 * Note Delete Handler - Fixed Version
 * Processes note deletions and redirects to dashboard
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

// Load required files
try {
    require_once '../../config/conf.php';
    require_once '../../app/Controllers/NotesController.php';
    
    $notesController = new NotesController();
    $user_id = $_SESSION['user_id'];
} catch (Exception $e) {
    header('Location: ../dashboard.php?error=system_error&message=' . urlencode($e->getMessage()));
    exit();
}

// Handle note deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $note_id = $_POST['note_id'] ?? null;
    
    if ($note_id && is_numeric($note_id)) {
        $result = $notesController->deleteNote($note_id, $user_id);
        
        if ($result) {
            header('Location: ../dashboard.php?message=note_deleted');
        } else {
            header('Location: ../dashboard.php?error=delete_failed');
        }
    } else {
        header('Location: ../dashboard.php?error=invalid_note_id');
    }
} else {
    // Invalid request, redirect to dashboard
    header('Location: ../dashboard.php');
}
exit();
?>
