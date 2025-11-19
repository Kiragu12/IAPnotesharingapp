<?php
// Admin Deleted Notes Management
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();

// Get pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get deleted notes
$deletedNotes = $adminController->getDeletedNotes($perPage, $offset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Notes - Admin Panel</title>
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
                        <li class="nav-item"><a class="nav-link" href="notes.php"><i class="bi bi-journal-text me-2"></i>Notes</a></li>
                        <li class="nav-item"><a class="nav-link active" href="deleted_notes.php"><i class="bi bi-trash-fill me-2"></i>Deleted Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <h2><i class="bi bi-trash-fill me-2"></i>Deleted Notes</h2>
                    <p class="text-muted mb-0">Archive of deleted notes</p>
                </div>
                
                <!-- Notes Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Deleted By</th>
                                        <th>Type</th>
                                        <th>Deleted At</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($deletedNotes)): ?>
                                        <?php foreach ($deletedNotes as $note): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(substr($note['title'], 0, 50)); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($note['deleted_by_name'] ?? 'Unknown'); ?></td>
                                                <td>
                                                    <?php if ($note['note_type'] === 'file'): ?>
                                                        <span class="badge bg-success"><i class="bi bi-file-earmark"></i> File</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-primary"><i class="bi bi-file-text"></i> Text</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y H:i', strtotime($note['deleted_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($note['deletion_reason']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="text-muted">No deleted notes found</p>
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
</body>
</html>
