<?php
// Admin User Details Page
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();
$userId = (int)($_GET['id'] ?? 0);

if (!$userId) {
    header('Location: users.php');
    exit;
}

// Get user details
$user = $adminController->getUserDetails($userId);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: users.php');
    exit;
}

// Get user's notes
$notes = $adminController->getUserNotes($userId);

// Get suspension history
$suspensions = $adminController->getUserSuspensions($userId);

// Calculate stats
$totalNotes = count($notes);
$totalViews = array_sum(array_column($notes, 'views'));
$publicNotes = count(array_filter($notes, function($note) { return $note['is_public']; }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - <?php echo htmlspecialchars($user['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .avatar {
            width: 100px;
            height: 100px;
            background: white;
            color: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .badge-status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="container text-center">
                <div class="avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <?php if ($user['is_admin']): ?>
                        <span class="badge badge-status bg-danger">
                            <i class="bi bi-shield-check me-1"></i>Administrator
                        </span>
                    <?php endif; ?>
                    <span class="badge badge-status bg-info">
                        <i class="bi bi-calendar me-1"></i>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-0"><?php echo $totalNotes; ?></h3>
                        <small class="text-muted">Total Notes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-0"><?php echo $publicNotes; ?></h3>
                        <small class="text-muted">Public Notes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-0"><?php echo number_format($totalViews); ?></h3>
                        <small class="text-muted">Total Views</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-0"><?php echo count($suspensions); ?></h3>
                        <small class="text-muted">Suspensions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#notes" type="button">
                    <i class="bi bi-journal-text me-2"></i>Notes (<?php echo $totalNotes; ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#suspensions" type="button">
                    <i class="bi bi-clock-history me-2"></i>Suspension History
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#account" type="button">
                    <i class="bi bi-person-gear me-2"></i>Account Info
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Notes Tab -->
            <div class="tab-pane fade show active" id="notes">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($notes)): ?>
                            <p class="text-muted text-center py-4">No notes found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Visibility</th>
                                            <th>Views</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notes as $note): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($note['title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $note['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($note['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($note['is_public']): ?>
                                                        <i class="bi bi-globe text-success"></i> Public
                                                    <?php else: ?>
                                                        <i class="bi bi-lock text-muted"></i> Private
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo number_format($note['views']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($note['created_at'])); ?></td>
                                                <td>
                                                    <a href="../notes/view.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Suspensions Tab -->
            <div class="tab-pane fade" id="suspensions">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($suspensions)): ?>
                            <p class="text-muted text-center py-4">No suspension history</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reason</th>
                                            <th>Suspended By</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suspensions as $suspension): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($suspension['created_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($suspension['reason']); ?></td>
                                                <td><?php echo htmlspecialchars($suspension['suspended_by_name'] ?? 'Unknown'); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($suspension['suspended_until']) {
                                                        echo date('M j, Y', strtotime($suspension['suspended_until']));
                                                    } else {
                                                        echo 'Permanent';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if ($suspension['is_active']): ?>
                                                        <span class="badge bg-danger">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Lifted</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Account Info Tab -->
            <div class="tab-pane fade" id="account">
                <div class="card">
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">User ID</dt>
                            <dd class="col-sm-9"><?php echo $user['id']; ?></dd>

                            <dt class="col-sm-3">Full Name</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($user['full_name']); ?></dd>

                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9"><?php echo htmlspecialchars($user['email']); ?></dd>

                            <dt class="col-sm-3">Role</dt>
                            <dd class="col-sm-9">
                                <?php echo $user['is_admin'] ? 'Administrator' : 'User'; ?>
                            </dd>

                            <dt class="col-sm-3">Account Created</dt>
                            <dd class="col-sm-9"><?php echo date('F j, Y g:i A', strtotime($user['created_at'])); ?></dd>

                            <dt class="col-sm-3">Last Updated</dt>
                            <dd class="col-sm-9"><?php echo date('F j, Y g:i A', strtotime($user['updated_at'])); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
