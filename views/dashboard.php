<?php
// Start session
session_start();

// Load classes for message handling
require_once '../app/Services/Global/fncs.php';
$ObjFncs = new fncs();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header('Location: auth/signin.php');
    exit();
}


$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'];

// Check for welcome messages
$welcome_msg = $ObjFncs->getMsg('msg');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - NotesShare Academy</title>
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: #666;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 1rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: #333;
            transform: translateY(-4px);
        }
        .sidebar .nav-link.active {
            color: #666;
        }

        .content-area {
            padding: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        .note-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .note-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .category-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            align-items: center;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1rem;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .quick-action-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            margin: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .quick-action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
        }
        .dropdown-toggle::after {
            display: none;
        }
        .profile-dropdown {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-journal-bookmark me-2"></i>Notes Sharing App
            </a>
            
            <!-- Search Bar -->
            <div class="d-flex flex-grow-1 mx-4">
                <input type="search" class="form-control search-box me-3" placeholder="Search notes, subjects, or users..." style="max-width: 500px;">
                <button class="btn btn-outline-light" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            
            <!-- User Menu -->
            <div class="dropdown">
                <button class="btn profile-dropdown dropdown-toggle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2 border-bottom">
                        <div class="d-flex flex-column">
                            <span class="fw-bold"><?php echo htmlspecialchars($user_name); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                        </div>
                    </li>
                    <?php if ($is_logged_in): ?>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="auth/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-shield-check me-2"></i>Security (2FA)</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle me-2"></i>Help</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item text-success" href="auth/signin.php"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</a></li>
                        <li><a class="dropdown-item" href="auth/signup.php"><i class="bi bi-person-plus me-2"></i>Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <button class="nav-link active w-100 text-start border-0" style="background: none;" onclick="navigateTo('dashboard')">
                                <i class="bi bi-house-door me-2"></i>Dashboard
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start border-0" style="background: none;" onclick="navigateTo('my-notes')">
                                <i class="bi bi-journal-text me-2"></i>My Notes
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start border-0" style="background: none;" onclick="navigateTo('shared-notes')">
                                <i class="bi bi-share me-2"></i>Shared Notes
                            </button>
                        </li>
                         <li class="nav-item">
                            <button class="nav-link w-100 text-start border-0" style="background: none;" onclick="navigateTo('settings')">
                                <i class="bi bi-gear me-2"></i>Settings
                            </button>
                        </li>
                        
                        
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="content-area">
                    <!-- Welcome/Success Messages -->
                    <?php
                    // Display welcome messages or alerts
                    if (!empty($welcome_msg)) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                            <i class="bi bi-check-circle me-2"></i>' . $welcome_msg . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
                    }
                    
                    // Show first login welcome
                    if ($first_login) {
                        echo '<div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                            <i class="bi bi-info-circle me-2"></i><strong>Welcome to Notes Sharing Academy!</strong> 
                            This is your dashboard where you can manage your notes, collaborate with classmates, and access all platform features.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
                    }
                    ?>
                    
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h2>
                                <p class="mb-3 opacity-75">Ready to share knowledge and collaborate with your classmates? Start exploring notes and contributing to the community.</p>
                                <div>
                                    <a href="notes/create.php" class="quick-action-btn">
                                        <i class="bi bi-plus-circle me-2"></i>Create Note
                                    </a>
                                    <a href="notes/my-notes.php" class="quick-action-btn">
                                        <i class="bi bi-journals me-2"></i>My Notes
                                    </a>
                                    <a href="shared-notes.php" class="quick-action-btn">
                                        <i class="bi bi-people me-2"></i>Browse Community
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="bi bi-journal-bookmark" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards - Only My Notes and Shared Notes -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card stats-card border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="stats-icon bg-primary mx-auto mb-3">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="fw-bold text-primary mb-1">0</h3>
                                            <p class="text-muted mb-0 small">My Notes</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card stats-card border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="stats-icon bg-success mx-auto mb-3">
                                        <i class="bi bi-share"></i>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="fw-bold text-success mb-1">0</h3>
                                            <p class="text-muted mb-0 small">Shared Notes</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Notes with Enhanced Bootstrap Cards -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                                    <h4 class="fw-bold mb-0">
                                        <i class="bi bi-journal-text me-2 text-primary"></i>Recent Notes
                                    </h4>
                                    <div class="btn-group">
                                        <a href="notes/create.php" class="btn btn-primary-custom btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i>New Note
                                        </a>
                                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                            <span class="visually-hidden">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="notes/create.php?type=text"><i class="bi bi-file-text me-2"></i>Text Note</a></li>
                                            <li><a class="dropdown-item" href="notes/create.php?type=file"><i class="bi bi-upload me-2"></i>Upload File</a></li>
                                            <li><a class="dropdown-item" href="notes/create.php"><i class="bi bi-plus-circle me-2"></i>New Note</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <!-- Note Card 1 -->
                                    <div class="note-card border-0 border-bottom">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <h5 class="fw-bold mb-0 me-3">Data Structures & Algorithms</h5>
                                                        <span class="badge category-badge">Computer Science</span>
                                                    </div>
                                                    <p class="text-muted mb-0">Comprehensive notes on binary trees, sorting algorithms, and complexity analysis...</p>
                                                </div>
                                                <div class="dropdown ms-3">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center text-muted">
                                                    <small class="me-3">
                                                        <i class="bi bi-clock me-1"></i>2 hours ago
                                                    </small>
                                                    <small class="me-3">
                                                        <i class="bi bi-file-text me-1"></i>5 pages
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        <i class="bi bi-eye me-1"></i>23 views
                                                    </span>
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-heart me-1"></i>5 likes
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Note Card 2 -->
                                    <div class="note-card border-0 border-bottom">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <h5 class="fw-bold mb-0 me-3">Database Design Principles</h5>
                                                        <span class="badge bg-info text-white">Database</span>
                                                    </div>
                                                    <p class="text-muted mb-0">Normalization forms, ER diagrams, and best practices for designing efficient database schemas.</p>
                                                </div>
                                                <div class="dropdown ms-3">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center text-muted">
                                                    <small class="me-3">
                                                        <i class="bi bi-clock me-1"></i>Yesterday
                                                    </small>
                                                    <small class="me-3">
                                                        <i class="bi bi-file-text me-1"></i>8 pages
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        <i class="bi bi-eye me-1"></i>41 views
                                                    </span>
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-heart me-1"></i>12 likes
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Note Card 3 -->
                                    <div class="note-card border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <h5 class="fw-bold mb-0 me-3">Web Development Frameworks</h5>
                                                        <span class="badge bg-warning text-dark">Web Development</span>
                                                    </div>
                                                    <p class="text-muted mb-0">Comparison of React, Vue, and Angular frameworks. Includes pros, cons, and use cases for each.</p>
                                                </div>
                                                <div class="dropdown ms-3">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                        <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center text-muted">
                                                    <small class="me-3">
                                                        <i class="bi bi-clock me-1"></i>3 days ago
                                                    </small>
                                                    <small class="me-3">
                                                        <i class="bi bi-file-text me-1"></i>12 pages
                                                    </small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        <i class="bi bi-eye me-1"></i>67 views
                                                    </span>
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="bi bi-heart me-1"></i>18 likes
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity with Enhanced Bootstrap Components -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="fw-bold mb-0">
                                            <i class="bi bi-activity me-2 text-primary"></i>Recent Activity
                                        </h5>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="activity-icon bg-success text-white me-3 flex-shrink-0">
                                                    <i class="bi bi-plus-circle"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-1 fw-semibold">New note created</h6>
                                                        <small class="text-muted">2h</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Data Structures & Algorithms</p>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success-subtle text-success me-2">
                                                            <i class="bi bi-file-text me-1"></i>Note
                                                        </span>
                                                        <small class="text-muted">by You</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="activity-icon bg-primary text-white me-3 flex-shrink-0">
                                                    <i class="bi bi-share"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-1 fw-semibold">Note shared with you</h6>
                                                        <small class="text-muted">5h</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Machine Learning Basics</p>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary-subtle text-primary me-2">
                                                            <i class="bi bi-share me-1"></i>Shared
                                                        </span>
                                                        <small class="text-muted">by Sarah Chen</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="activity-icon bg-warning text-white me-3 flex-shrink-0">
                                                    <i class="bi bi-heart"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-1 fw-semibold">Note liked</h6>
                                                        <small class="text-muted">1d</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Someone liked "Database Design"</p>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-warning-subtle text-warning me-2">
                                                            <i class="bi bi-heart me-1"></i>Like
                                                        </span>
                                                        <small class="text-muted">by Mike Johnson</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="list-group-item border-0 py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="activity-icon bg-info text-white me-3 flex-shrink-0">
                                                    <i class="bi bi-chat"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-1 fw-semibold">New comment</h6>
                                                        <small class="text-muted">2d</small>
                                                    </div>
                                                    <p class="mb-1 text-muted small">Comment on "Web Frameworks"</p>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info-subtle text-info me-2">
                                                            <i class="bi bi-chat me-1"></i>Comment
                                                        </span>
                                                        <small class="text-muted">by Alex Rivera</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 text-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right me-1"></i>View All Activity
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Custom JS -->
    <script>
        // Navigation function
        function navigateTo(page) {
            console.log('Navigating to:', page);
            
            switch(page) {
                case 'dashboard':
                    // Stay on dashboard
                    return;
                case 'my-notes':
                    // Navigate to my notes
                    window.location.href = 'notes/my-notes.php';
                    break;
                case 'shared-notes':
                    // Navigate to shared notes
                    window.location.href = 'shared-notes.php';
                    break;
                case 'settings':
                    // Navigate to settings
                    window.location.href = 'auth/settings.php';
                    break;
                default:
                    console.error('Unknown navigation target:', page);
            }
        }
        

        
        // Search functionality
        document.querySelector('.search-box').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const noteCards = document.querySelectorAll('.note-card');
            
            noteCards.forEach(card => {
                const title = card.querySelector('h5').textContent.toLowerCase();
                const content = card.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || content.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Add smooth animations on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stats-card, .note-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
        
        // Real-time clock in welcome banner
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            // You can add this to the welcome banner if needed
        }
        
        setInterval(updateTime, 1000);
    </script>
</body>
</html>