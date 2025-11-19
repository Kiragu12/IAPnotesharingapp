<?php
// Admin Analytics Dashboard
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();
$period = $_GET['period'] ?? '30days';
$analytics = $adminController->getAnalytics($period);
$stats = $adminController->getDashboardStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
            position: relative;
            height: 300px;
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
                        <li class="nav-item"><a class="nav-link" href="deleted_notes.php"><i class="bi bi-trash-fill me-2"></i>Deleted Notes</a></li>
                        <li class="nav-item"><a class="nav-link active" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-graph-up me-2"></i>Analytics & Statistics</h2>
                            <p class="text-muted mb-0">Platform growth and usage insights</p>
                        </div>
                        <div>
                            <select class="form-select" onchange="window.location.href='?period='+this.value">
                                <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>Last 7 Days</option>
                                <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Last 30 Days</option>
                                <option value="90days" <?php echo $period === '90days' ? 'selected' : ''; ?>>Last 90 Days</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row 1 -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-people me-2 text-primary"></i>User Growth</h5>
                                <div class="chart-container">
                                    <canvas id="userGrowthChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-journal-plus me-2 text-success"></i>Note Creation</h5>
                                <div class="chart-container">
                                    <canvas id="noteCreationChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row 2 -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-tags me-2 text-info"></i>Popular Categories</h5>
                                <div class="chart-container">
                                    <canvas id="categoriesChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-file-earmark me-2 text-warning"></i>Note Type Distribution</h5>
                                <div class="chart-container">
                                    <canvas id="noteTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Contributors -->
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-trophy me-2 text-warning"></i>Top Contributors</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Notes</th>
                                                <th>Total Views</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($analytics['top_contributors'])): ?>
                                                <?php $rank = 1; foreach ($analytics['top_contributors'] as $contributor): ?>
                                                    <tr>
                                                        <td>
                                                            <?php if ($rank === 1): ?>
                                                                <i class="bi bi-trophy-fill text-warning"></i>
                                                            <?php elseif ($rank === 2): ?>
                                                                <i class="bi bi-award-fill text-secondary"></i>
                                                            <?php elseif ($rank === 3): ?>
                                                                <i class="bi bi-award-fill" style="color: #cd7f32;"></i>
                                                            <?php else: ?>
                                                                <?php echo $rank; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($contributor['full_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($contributor['email']); ?></td>
                                                        <td><span class="badge bg-primary"><?php echo $contributor['note_count']; ?></span></td>
                                                        <td><span class="badge bg-success"><?php echo number_format($contributor['total_views'] ?? 0); ?></span></td>
                                                    </tr>
                                                    <?php $rank++; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No data available</td>
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
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Growth Chart
        const userGrowthData = <?php echo json_encode($analytics['user_growth']); ?>;
        const userGrowthChart = new Chart(document.getElementById('userGrowthChart'), {
            type: 'line',
            data: {
                labels: userGrowthData.map(d => d.date),
                datasets: [{
                    label: 'New Users',
                    data: userGrowthData.map(d => d.count),
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        // Note Creation Chart
        const noteCreationData = <?php echo json_encode($analytics['note_creation']); ?>;
        const noteCreationChart = new Chart(document.getElementById('noteCreationChart'), {
            type: 'bar',
            data: {
                labels: noteCreationData.map(d => d.date),
                datasets: [{
                    label: 'Notes Created',
                    data: noteCreationData.map(d => d.count),
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        // Categories Chart
        const categoriesData = <?php echo json_encode($analytics['popular_categories']); ?>;
        const categoriesChart = new Chart(document.getElementById('categoriesChart'), {
            type: 'doughnut',
            data: {
                labels: categoriesData.map(d => d.name),
                datasets: [{
                    data: categoriesData.map(d => d.count),
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Note Type Distribution
        const noteTypeData = <?php echo json_encode($analytics['note_type_distribution']); ?>;
        const noteTypeChart = new Chart(document.getElementById('noteTypeChart'), {
            type: 'pie',
            data: {
                labels: noteTypeData.map(d => d.note_type === 'file' ? 'File Notes' : 'Text Notes'),
                datasets: [{
                    data: noteTypeData.map(d => d.count),
                    backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(102, 126, 234, 0.8)']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
