<?php
// Admin Users Management
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();

// Handle actions
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    $adminId = $_SESSION['user_id'];
    
    switch ($action) {
        case 'delete':
            $result = $adminController->deleteUser($userId, $adminId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
            
        case 'suspend':
            $reason = $_POST['reason'] ?? 'Violation of terms';
            $duration = (int)($_POST['duration'] ?? 30);
            $result = $adminController->suspendUser($userId, $adminId, $reason, $duration);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
            
        case 'unsuspend':
            $result = $adminController->unsuspendUser($userId, $adminId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
            
        case 'make_admin':
            $result = $adminController->makeAdmin($userId, $adminId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
            
        case 'remove_admin':
            $result = $adminController->removeAdmin($userId, $adminId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
            break;
    }
}

// Get pagination parameters
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

// Get users
$users = $adminController->getUsers($perPage, $offset, $search, $filter);
$totalUsers = $adminController->getUserCount($search, $filter);
$totalPages = ceil($totalUsers / $perPage);

$stats = $adminController->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            margin: 0.25rem 0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .user-row:hover { background: #f8f9fa; }
        .badge-role { font-size: 0.7rem; }
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (same as dashboard) -->
            <div class="col-md-2 px-0 admin-sidebar">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-shield-lock-fill me-2"></i>Admin Panel
                    </h4>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link active" href="users.php"><i class="bi bi-people-fill me-2"></i>Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="notes.php"><i class="bi bi-journal-text me-2"></i>Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="deleted_notes.php"><i class="bi bi-trash-fill me-2"></i>Deleted Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <h2><i class="bi bi-people-fill me-2"></i>User Management</h2>
                    <p class="text-muted mb-0">Manage user accounts, permissions, and suspensions</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Search and Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search by name, email, username..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="filter">
                                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Users</option>
                                    <option value="admins" <?php echo $filter === 'admins' ? 'selected' : ''; ?>>Admins</option>
                                    <option value="suspended" <?php echo $filter === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    <option value="verified" <?php echo $filter === 'verified' ? 'selected' : ''; ?>>Verified</option>
                                    <option value="unverified" <?php echo $filter === 'unverified' ? 'selected' : ''; ?>>Unverified</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="export.php?type=users" class="btn btn-outline-secondary">
                                    <i class="bi bi-download me-2"></i>Export CSV
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Notes</th>
                                        <th>Views</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr class="user-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-primary text-white rounded-circle me-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                                            <br><small class="text-muted">@<?php echo htmlspecialchars($user['username'] ?? strtolower(str_replace(' ', '', $user['full_name']))); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['is_admin']): ?>
                                                        <span class="badge bg-danger badge-role">ADMIN</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary badge-role">USER</span>
                                                    <?php endif; ?>
                                                    <?php if (isset($user['is_verified']) && $user['is_verified']): ?>
                                                        <span class="badge bg-success badge-role"><i class="bi bi-check-circle-fill"></i></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $user['note_count']; ?></td>
                                                <td><?php echo number_format($user['total_views'] ?? 0); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($user['is_suspended']): ?>
                                                        <span class="badge bg-danger">Suspended</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="viewUser(<?php echo $user['id']; ?>)"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                                            <?php if (!$user['is_suspended']): ?>
                                                                <li><a class="dropdown-item text-warning" href="#" onclick="suspendUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"><i class="bi bi-pause-circle me-2"></i>Suspend</a></li>
                                                            <?php else: ?>
                                                                <li><a class="dropdown-item text-success" href="#" onclick="unsuspendUser(<?php echo $user['id']; ?>)"><i class="bi bi-play-circle me-2"></i>Unsuspend</a></li>
                                                            <?php endif; ?>
                                                            <?php if (!$user['is_admin']): ?>
                                                                <li><a class="dropdown-item" href="#" onclick="makeAdmin(<?php echo $user['id']; ?>)"><i class="bi bi-shield-check me-2"></i>Make Admin</a></li>
                                                            <?php else: ?>
                                                                <li><a class="dropdown-item" href="#" onclick="removeAdmin(<?php echo $user['id']; ?>)"><i class="bi bi-shield-x me-2"></i>Remove Admin</a></li>
                                                            <?php endif; ?>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"><i class="bi bi-trash me-2"></i>Delete User</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="text-muted">No users found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="suspend">
                        <input type="hidden" name="user_id" id="suspend_user_id">
                        <p>Suspend <strong id="suspend_user_name"></strong>?</p>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (days)</label>
                            <input type="number" class="form-control" name="duration" value="30" min="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Suspend User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function suspendUser(userId, userName) {
            document.getElementById('suspend_user_id').value = userId;
            document.getElementById('suspend_user_name').textContent = userName;
            new bootstrap.Modal(document.getElementById('suspendModal')).show();
        }
        
        function unsuspendUser(userId) {
            if (confirm('Unsuspend this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="unsuspend"><input type="hidden" name="user_id" value="' + userId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteUser(userId, userName) {
            if (confirm('DELETE user "' + userName + '"? This action cannot be undone!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="' + userId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function makeAdmin(userId) {
            if (confirm('Grant admin privileges to this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="make_admin"><input type="hidden" name="user_id" value="' + userId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function removeAdmin(userId) {
            if (confirm('Remove admin privileges from this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="remove_admin"><input type="hidden" name="user_id" value="' + userId + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function viewUser(userId) {
            window.location.href = 'user-details.php?id=' + userId;
        }
    </script>
</body>
</html>
