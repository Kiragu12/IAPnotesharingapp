<?php
/**
 * Delete Account - Permanently remove user account and all data
 */
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}

require_once __DIR__ . '/../../config/conf.php';
require_once __DIR__ . '/../../app/Services/Global/Database.php';
require_once __DIR__ . '/../../app/Services/Global/fncs.php';

$db = new Database($conf);
$ObjFncs = new fncs();
$user_id = (int) $_SESSION['user_id'];

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $password = $_POST['password'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    if ($password === '' || $confirmation !== 'DELETE') {
        $error_message = 'Please provide your password and type "DELETE" to confirm.';
    } else {
        try {
            // Verify password
            $user = $db->fetchOne('SELECT password FROM users WHERE id = ? LIMIT 1', [$user_id]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Delete user data in proper order (foreign keys)
                $db->beginTransaction();
                
                // Delete notes
                $db->query('DELETE FROM notes WHERE user_id = ?', [$user_id]);
                
                // Delete 2FA codes
                $db->query('DELETE FROM two_factor_codes WHERE user_id = ?', [$user_id]);
                
                // Delete remember tokens
                $db->query('DELETE FROM remember_tokens WHERE user_id = ?', [$user_id]);
                
                // Delete password reset tokens
                $db->query('DELETE FROM password_resets WHERE user_id = ?', [$user_id]);
                
                // Finally delete user account
                $db->query('DELETE FROM users WHERE id = ?', [$user_id]);
                
                $db->commit();
                
                // Clear session
                session_destroy();
                
                // Redirect to homepage with message
                header('Location: ../index.php?message=account_deleted');
                exit();
                
            } else {
                $error_message = 'Incorrect password. Account deletion cancelled.';
            }
            
        } catch (Exception $e) {
            $db->rollback();
            error_log('Delete account error: ' . $e->getMessage());
            $error_message = 'An error occurred while deleting your account. Please try again or contact support.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .delete-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 3px solid #dc3545;
        }
        .danger-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 25px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="delete-card">
                    <div class="danger-header text-center">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                        <h3 class="fw-bold mt-3">Delete Account</h3>
                        <p class="mb-0">This action cannot be undone</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-warning">
                            <h6><strong>What will be deleted:</strong></h6>
                            <ul class="mb-0">
                                <li>Your user account and profile</li>
                                <li>All your notes (text and file uploads)</li>
                                <li>All your uploaded files</li>
                                <li>Your login sessions and security settings</li>
                                <li>All associated data</li>
                            </ul>
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Enter your password to confirm:</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Type "DELETE" to confirm:</label>
                                <input type="text" name="confirmation" class="form-control" placeholder="Type DELETE here" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="confirm_delete" class="btn btn-danger btn-lg">
                                    <i class="bi bi-trash me-2"></i>Permanently Delete Account
                                </button>
                                <a href="settings.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel - Go Back
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
