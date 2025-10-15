<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICS Notes App - Active Pages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem 0;
        }
        .page-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }
        .page-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .page-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .status-working { background: linear-gradient(135deg, #28a745, #20c997); }
        .status-utility { background: linear-gradient(135deg, #17a2b8, #6f42c1); }
        .btn-visit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-visit:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold mb-3">📚 ICS Notes Sharing App</h1>
                    <p class="lead">Active Pages & Features Overview</p>
                    <small class="text-muted">All pages are working and ready to view!</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h3 class="mb-4">🌟 Main Application Pages</h3>
                        
                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-working">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Home Page</h5>
                                    <p class="mb-2 text-muted">Landing page with navigation and banner</p>
                                    <a href="index.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-working">
                                    <i class="bi bi-person-plus"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Sign Up</h5>
                                    <p class="mb-2 text-muted">User registration with email validation</p>
                                    <a href="signup.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-working">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Sign In</h5>
                                    <p class="mb-2 text-muted">User login form with authentication</p>
                                    <a href="signin.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-working">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Dashboard</h5>
                                    <p class="mb-2 text-muted">Beautiful interface with notes management</p>
                                    <a href="dashboard.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h3 class="mb-4">🔧 Utility & Test Pages</h3>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-utility">
                                    <i class="bi bi-cookie"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Cookie Viewer</h5>
                                    <p class="mb-2 text-muted">View and test cookie functionality</p>
                                    <a href="view_cookies.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-utility">
                                    <i class="bi bi-database"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Database Test</h5>
                                    <p class="mb-2 text-muted">Test database connection and settings</p>
                                    <a href="test_db_connection.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-utility">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Email Test</h5>
                                    <p class="mb-2 text-muted">Test email sending functionality</p>
                                    <a href="send_test_mail.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>

                        <div class="page-card">
                            <div class="d-flex align-items-center">
                                <div class="page-icon status-utility">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">Simple DB Test</h5>
                                    <p class="mb-2 text-muted">Clean database connection verification</p>
                                    <a href="simple_db_test.php" class="btn-visit">Visit Page</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div class="page-card">
                            <div class="text-center">
                                <h4 class="mb-3">📊 Current Development Status</h4>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-success">
                                            <i class="bi bi-check-circle-fill fs-2"></i>
                                            <h6 class="mt-2">Frontend UI</h6>
                                            <small>100% Complete</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-warning">
                                            <i class="bi bi-clock-fill fs-2"></i>
                                            <h6 class="mt-2">Authentication</h6>
                                            <small>In Progress</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-info">
                                            <i class="bi bi-gear-fill fs-2"></i>
                                            <h6 class="mt-2">Database</h6>
                                            <small>Ready for Setup</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-secondary">
                                            <i class="bi bi-plus-circle-fill fs-2"></i>
                                            <h6 class="mt-2">Notes CRUD</h6>
                                            <small>Next Phase</small>
                                        </div>
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