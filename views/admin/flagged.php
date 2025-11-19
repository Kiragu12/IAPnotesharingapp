<?php
// Admin Flagged Content Management
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $flag_id = intval($_POST['flag_id'] ?? 0);
        $note_id = intval($_POST['note_id'] ?? 0);
        
        switch ($_POST['action']) {
            case 'dismiss':
                // Dismiss flag
                $db = new Database();
                $db->query("UPDATE flagged_notes SET status = 'dismissed', reviewed_by = :admin_id, reviewed_at = NOW() WHERE id = :flag_id");
                $db->bind(':admin_id', $_SESSION['user_id']);
                $db->bind(':flag_id', $flag_id);
                $db->execute();
                $adminController->logAdminActivity($_SESSION['user_id'], 'flag_dismissed', "Dismissed flag #$flag_id");
                header('Location: flagged.php?msg=dismissed');
                exit;
                
            case 'resolve':
                // Resolve flag
                $db = new Database();
                $db->query("UPDATE flagged_notes SET status = 'resolved', reviewed_by = :admin_id, reviewed_at = NOW() WHERE id = :flag_id");
                $db->bind(':admin_id', $_SESSION['user_id']);
                $db->bind(':flag_id', $flag_id);
                $db->execute();
                $adminController->logAdminActivity($_SESSION['user_id'], 'flag_resolved', "Resolved flag #$flag_id");
                header('Location: flagged.php?msg=resolved');
                exit;
                
            case 'delete_note':
                // Delete the flagged note
                $adminController->deleteNote($note_id, $_SESSION['user_id']);
                header('Location: flagged.php?msg=note_deleted');
                exit;
        }
    }
}

$status = $_GET['status'] ?? 'pending';
$flaggedNotes = $adminController->getFlaggedNotes($status);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flagged Content - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            margin: 0.25rem 0;
            border-radius: 10px;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .flag-card {
            border-left: 4px solid #dc3545;
            transition: all 0.3s;
        }
        .flag-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0 admin-sidebar">
                <div class="p-4">
                    <h4 class="text-white mb-4"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people-fill me-2"></i>Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="notes.php"><i class="bi bi-journal-text me-2"></i>Notes</a></li>
                        <li class="nav-item"><a class="nav-link active" href="flagged.php"><i class="bi bi-flag-fill me-2"></i>Flagged Content</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-flag-fill me-2"></i>Flagged Content</h2>
                            <p class="text-muted mb-0">Review and moderate user-reported content</p>
                        </div>
                        <div>
                            <select class="form-select" onchange="window.location.href='?status='+this.value">
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="dismissed" <?php echo $status === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php
                    $messages = [
                        'dismissed' => ['success', 'Flag has been dismissed successfully.'],
                        'resolved' => ['success', 'Flag has been marked as resolved.'],
                        'note_deleted' => ['success', 'Note has been deleted successfully.']
                    ];
                    $msgData = $messages[$_GET['msg']] ?? ['info', 'Action completed.'];
                    ?>
                    <div class="alert alert-<?php echo $msgData[0]; ?> alert-dismissible fade show">
                        <?php echo $msgData[1]; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Flagged Notes -->
                <div class="row g-4">
                    <?php if (!empty($flaggedNotes)): ?>
                        <?php foreach ($flaggedNotes as $flag): ?>
                            <div class="col-md-6">
                                <div class="card flag-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($flag['note_title']); ?></h5>
                                                <small class="text-muted">
                                                    by <?php echo htmlspecialchars($flag['note_author']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php 
                                                echo $flag['status'] === 'pending' ? 'warning' : 
                                                    ($flag['status'] === 'resolved' ? 'success' : 
                                                    ($flag['status'] === 'dismissed' ? 'secondary' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($flag['status']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="alert alert-danger mb-3">
                                            <strong><i class="bi bi-flag-fill me-2"></i>Reason:</strong>
                                            <p class="mb-0 mt-1"><?php echo htmlspecialchars($flag['reason']); ?></p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i>Reported by: 
                                                <?php echo htmlspecialchars($flag['reporter_name']); ?>
                                            </small><br>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?php echo date('M j, Y g:i A', strtotime($flag['created_at'])); ?>
                                            </small>
                                        </div>
                                        
                                        <?php if ($flag['reviewed_by_name']): ?>
                                            <div class="alert alert-info mb-3">
                                                <small>
                                                    <strong>Reviewed by:</strong> <?php echo htmlspecialchars($flag['reviewed_by_name']); ?><br>
                                                    <strong>On:</strong> <?php echo date('M j, Y g:i A', strtotime($flag['reviewed_at'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2">
                                            <a href="../notes/view.php?id=<?php echo $flag['note_id']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>View Note
                                            </a>
                                            
                                            <?php if ($flag['status'] === 'pending'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Dismiss this flag?')">
                                                    <input type="hidden" name="flag_id" value="<?php echo $flag['id']; ?>">
                                                    <input type="hidden" name="action" value="dismiss">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-x-circle me-1"></i>Dismiss
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Mark as resolved?')">
                                                    <input type="hidden" name="flag_id" value="<?php echo $flag['id']; ?>">
                                                    <input type="hidden" name="action" value="resolve">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check-circle me-1"></i>Resolve
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this note permanently?')">
                                                    <input type="hidden" name="flag_id" value="<?php echo $flag['id']; ?>">
                                                    <input type="hidden" name="note_id" value="<?php echo $flag['note_id']; ?>">
                                                    <input type="hidden" name="action" value="delete_note">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash me-1"></i>Delete Note
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-flag display-1 text-muted"></i>
                                    <h4 class="mt-3">No Flagged Content</h4>
                                    <p class="text-muted">There are no <?php echo $status !== 'all' ? $status : ''; ?> flagged items at this time.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
