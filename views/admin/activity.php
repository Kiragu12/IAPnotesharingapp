<?php
// Admin Activity Log
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();
$db = new Database();

// Filters
$admin_filter = $_GET['admin'] ?? 'all';
$action_filter = $_GET['action'] ?? 'all';
$limit = 50;
$offset = isset($_GET['page']) ? (intval($_GET['page']) - 1) * $limit : 0;

// Build query
$where = [];
$params = [];

if ($admin_filter !== 'all') {
    $where[] = "aal.admin_id = :admin_id";
    $params[':admin_id'] = $admin_filter;
}

if ($action_filter !== 'all') {
    $where[] = "aal.action_type = :action_type";
    $params[':action_type'] = $action_filter;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get activities
$db->query("
    SELECT aal.*, u.full_name, u.email
    FROM admin_activity_logs aal
    JOIN users u ON aal.admin_id = u.id
    $whereClause
    ORDER BY aal.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':limit', $limit);
$db->bind(':offset', $offset);
$activities = $db->resultSet();

// Get total count for pagination
$db->query("
    SELECT COUNT(*) as total
    FROM admin_activity_logs aal
    $whereClause
");
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$total = $db->single()['total'];
$totalPages = ceil($total / $limit);
$currentPage = ($offset / $limit) + 1;

// Get all admins for filter
$db->query("SELECT DISTINCT u.id, u.full_name FROM users u JOIN admin_activity_logs aal ON u.id = aal.admin_id ORDER BY u.full_name");
$admins = $db->resultSet();

// Get all action types
$db->query("SELECT DISTINCT action_type FROM admin_activity_logs ORDER BY action_type");
$actionTypes = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin Panel</title>
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
        .activity-item {
            border-left: 3px solid #667eea;
            transition: all 0.3s;
        }
        .activity-item:hover {
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
                        <li class="nav-item"><a class="nav-link" href="flagged.php"><i class="bi bi-flag-fill me-2"></i>Flagged Content</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item"><a class="nav-link" href="categories.php"><i class="bi bi-tags-fill me-2"></i>Categories</a></li>
                        <li class="nav-item"><a class="nav-link active" href="activity.php"><i class="bi bi-clock-history me-2"></i>Activity Log</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-clock-history me-2"></i>Admin Activity Log</h2>
                            <p class="text-muted mb-0">Complete audit trail of admin actions</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="export.php?type=activity" class="btn btn-outline-primary">
                                <i class="bi bi-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Admin</label>
                                <select name="admin" class="form-select">
                                    <option value="all">All Admins</option>
                                    <?php foreach ($admins as $admin): ?>
                                        <option value="<?php echo $admin['id']; ?>" <?php echo $admin_filter == $admin['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($admin['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Action Type</label>
                                <select name="action" class="form-select">
                                    <option value="all">All Actions</option>
                                    <?php foreach ($actionTypes as $type): ?>
                                        <option value="<?php echo $type['action_type']; ?>" <?php echo $action_filter == $type['action_type'] ? 'selected' : ''; ?>>
                                            <?php echo ucwords(str_replace('_', ' ', $type['action_type'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel me-2"></i>Apply Filters
                                </button>
                                <a href="activity.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-2"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Activity List -->
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item bg-light p-3 mb-3 rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <span class="badge bg-primary">
                                                    <?php echo ucwords(str_replace('_', ' ', $activity['action_type'])); ?>
                                                </span>
                                            </h6>
                                            <p class="mb-2"><?php echo htmlspecialchars($activity['description']); ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($activity['full_name']); ?>
                                                <i class="bi bi-envelope ms-3 me-1"></i><?php echo htmlspecialchars($activity['email']); ?>
                                            </small>
                                        </div>
                                        <div class="text-end text-muted small">
                                            <div><i class="bi bi-calendar me-1"></i><?php echo date('M j, Y', strtotime($activity['created_at'])); ?></div>
                                            <div><i class="bi bi-clock me-1"></i><?php echo date('g:i A', strtotime($activity['created_at'])); ?></div>
                                            <?php if ($activity['ip_address']): ?>
                                                <div><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($activity['ip_address']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&admin=<?php echo $admin_filter; ?>&action=<?php echo $action_filter; ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&admin=<?php echo $admin_filter; ?>&action=<?php echo $action_filter; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&admin=<?php echo $admin_filter; ?>&action=<?php echo $action_filter; ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-clock-history display-1 text-muted"></i>
                                <h4 class="mt-3">No Activity</h4>
                                <p class="text-muted">No admin activity found matching your filters.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
