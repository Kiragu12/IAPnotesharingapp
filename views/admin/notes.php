<?php
// Admin Notes Management
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();

// Handle delete and flag actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $noteId = (int)($_POST['note_id'] ?? 0);
    $adminId = $_SESSION['user_id'];
    
    if ($_POST['action'] === 'delete') {
        $result = $adminController->deleteNote($noteId, $adminId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    } elseif ($_POST['action'] === 'flag') {
        $reason = $_POST['reason'] ?? 'Flagged by admin';
        $result = $adminController->flagNote($noteId, $adminId, urldecode($reason));
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'danger';
    }
}

// Get parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

// Get notes
$notes = $adminController->getNotes($perPage, $offset, $search, $filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes Management - Admin Panel</title>
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
                        <li class="nav-item"><a class="nav-link active" href="notes.php"><i class="bi bi-journal-text me-2"></i>Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="deleted_notes.php"><i class="bi bi-trash-fill me-2"></i>Deleted Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <h2><i class="bi bi-journal-text me-2"></i>Notes Management</h2>
                    <p class="text-muted mb-0">Monitor and manage all notes in the system</p>
                </div>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search notes..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="filter">
                                    <option value="all">All Notes</option>
                                    <option value="public" <?php echo $filter === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="private" <?php echo $filter === 'private' ? 'selected' : ''; ?>>Private</option>
                                    <option value="files" <?php echo $filter === 'files' ? 'selected' : ''; ?>>Files</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>Search</button>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="export.php?type=notes" class="btn btn-outline-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Notes Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Type</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($notes)): ?>
                                        <?php foreach ($notes as $note): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(substr($note['title'], 0, 50)); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($note['author_name']); ?></td>
                                                <td>
                                                    <?php if ($note['note_type'] === 'file'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-file-earmark"></i> File</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary"><i class="bi bi-file-text"></i> Text</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($note['category_name'] ?? 'None'); ?></td>
                                                <td>
                                                    <?php if ($note['is_public']): ?>
                                                        <span class="badge bg-info">Public</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Private</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($note['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteNote(<?php echo $note['id']; ?>, '<?php echo htmlspecialchars(addslashes($note['title'])); ?>')" title="Delete Note">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="text-muted">No notes found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteNote(noteId, title) {
            if (confirm('DELETE note "' + title + '"? This action cannot be undone!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="note_id" value="' + noteId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function flagNote(noteId, title) {
            const reason = prompt('Flag note "' + title + '"?\n\nPlease enter the reason for flagging this note:');
            if (reason && reason.trim()) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="flag"><input type="hidden" name="note_id" value="' + noteId + '"><input type="hidden" name="reason" value="' + encodeURIComponent(reason) + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
