<?php
// Admin Dashboard - Main Overview
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();
$stats = $adminController->getDashboardStats();
$recentActivity = $adminController->getRecentActivity(10);

$admin_name = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f8f9fa;
        }
        
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
            transform: translateX(5px);
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .activity-item {
            border-left: 3px solid #667eea;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .alert-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0 admin-sidebar">
                <div class="p-4">
                    <h4 class="text-white mb-4">
                        <i class="bi bi-shield-lock-fill me-2"></i>Admin Panel
                    </h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="bi bi-people-fill me-2"></i>Users
                                <?php if ($stats['users_today'] > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $stats['users_today']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notes.php">
                                <i class="bi bi-journal-text me-2"></i>Notes
                                <?php if ($stats['notes_today'] > 0): ?>
                                    <span class="badge bg-success ms-2"><?php echo $stats['notes_today']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="deleted_notes.php">
                                <i class="bi bi-trash-fill me-2"></i>Deleted Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="bi bi-graph-up me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="../dashboard.php">
                                <i class="bi bi-arrow-left-circle me-2"></i>Back to Site
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</h2>
                            <p class="text-muted mb-0">Here's what's happening with your platform today</p>
                        </div>
                        <div>
                            <span class="text-muted"><i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Total Users</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_users']); ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> <?php echo $stats['new_users_this_month']; ?> this month
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Total Notes</p>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_notes']); ?></h3>
                                        <small class="text-info">
                                            <i class="bi bi-globe"></i> <?php echo $stats['public_notes']; ?> public
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Storage Used</p>
                                        <h3 class="mb-0"><?php echo round($stats['storage_used'] / (1024*1024), 2); ?> MB</h3>
                                        <small class="text-warning">
                                            <i class="bi bi-hdd"></i> File uploads
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-hdd-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Active Users</p>
                                        <h4 class="mb-0"><?php echo number_format($stats['active_users']); ?></h4>
                                        <small class="text-muted">Last 30 days</small>
                                    </div>
                                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-person-check-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Deleted Notes</p>
                                        <h4 class="mb-0 text-warning"><?php echo $stats['deleted_notes_count']; ?></h4>
                                        <small class="text-muted">Archived</small>
                                    </div>
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                        <i class="bi bi-trash"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-danger">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Suspended</p>
                                        <h4 class="mb-0 text-danger"><?php echo $stats['suspended_users']; ?></h4>
                                        <small class="text-muted">Users</small>
                                    </div>
                                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-person-x-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1">Deleted</p>
                                        <h4 class="mb-0 text-secondary"><?php echo $stats['deleted_accounts']; ?></h4>
                                        <small class="text-muted">Accounts</small>
                                    </div>
                                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-trash-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-lightning-charge-fill me-2 text-warning"></i>Quick Actions
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-3 col-6">
                                        <a href="users.php" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-people me-2"></i>Manage Users
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="notes.php" class="btn btn-outline-success w-100">
                                            <i class="bi bi-journal-text me-2"></i>View Notes
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="deleted_notes.php" class="btn btn-outline-warning w-100">
                                            <i class="bi bi-trash me-2"></i>Deleted Notes
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="analytics.php" class="btn btn-outline-info w-100">
                                            <i class="bi bi-graph-up me-2"></i>Analytics
                                        </a>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <a href="export.php" class="btn btn-outline-secondary w-100">
                                            <i class="bi bi-download me-2"></i>Export Data
                                        </a>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <h6 class="mb-3">System Status</h6>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Database</small>
                                        <small class="text-success"><i class="bi bi-circle-fill"></i> Online</small>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Email Service</small>
                                        <small class="text-success"><i class="bi bi-circle-fill"></i> Active</small>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>File Uploads</small>
                                        <small class="text-success"><i class="bi bi-circle-fill"></i> Enabled</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
