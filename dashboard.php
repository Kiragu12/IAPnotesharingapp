<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes Sharing App - Dashboard</title>
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
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateX(5px);
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
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle me-2"></i>Help</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="signin.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
                            <a class="nav-link active" href="#dashboard">
                                <i class="bi bi-house-door me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#my-notes">
                                <i class="bi bi-journal-text me-2"></i>My Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#shared-notes">
                                <i class="bi bi-share me-2"></i>Shared Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#favorites">
                                <i class="bi bi-heart me-2"></i>Favorites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#categories">
                                <i class="bi bi-tags me-2"></i>Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#trash">
                                <i class="bi bi-trash me-2"></i>Trash
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="#settings">
                                <i class="bi bi-gear me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="content-area">
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">Welcome back, Student! 👋</h2>
                                <p class="mb-3 opacity-75">Ready to share knowledge and collaborate with your classmates? You have 5 new shared notes waiting for you.</p>
                                <div>
                                    <a href="#" class="quick-action-btn">
                                        <i class="bi bi-plus-circle me-2"></i>Create Note
                                    </a>
                                    <a href="#" class="quick-action-btn">
                                        <i class="bi bi-upload me-2"></i>Upload File
                                    </a>
                                    <a href="#" class="quick-action-btn">
                                        <i class="bi bi-people me-2"></i>Browse Community
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="bi bi-journal-bookmark" style="font-size: 4rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon bg-primary mx-auto">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <h3 class="fw-bold mb-1">24</h3>
                                <p class="text-muted mb-0">My Notes</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon bg-success mx-auto">
                                    <i class="bi bi-share"></i>
                                </div>
                                <h3 class="fw-bold mb-1">12</h3>
                                <p class="text-muted mb-0">Shared with Me</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon bg-warning mx-auto">
                                    <i class="bi bi-heart"></i>
                                </div>
                                <h3 class="fw-bold mb-1">8</h3>
                                <p class="text-muted mb-0">Favorites</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card text-center">
                                <div class="stats-icon bg-info mx-auto">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h3 class="fw-bold mb-1">156</h3>
                                <p class="text-muted mb-0">Collaborators</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Notes -->
                        <div class="col-lg-8">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-bold">Recent Notes</h4>
                                <button class="btn btn-primary-custom">
                                    <i class="bi bi-plus-circle me-2"></i>New Note
                                </button>
                            </div>

                            <!-- Note Cards -->
                            <div class="note-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">Data Structures & Algorithms</h5>
                                        <span class="category-badge">Computer Science</span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Comprehensive notes on binary trees, sorting algorithms, and complexity analysis. Includes examples and practice problems.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>Last edited 2 hours ago
                                    </small>
                                    <div>
                                        <span class="badge bg-primary me-2">
                                            <i class="bi bi-eye me-1"></i>23 views
                                        </span>
                                        <span class="badge bg-success">
                                            <i class="bi bi-heart me-1"></i>5 likes
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="note-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">Database Design Principles</h5>
                                        <span class="category-badge bg-info">Database</span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Normalization forms, ER diagrams, and best practices for designing efficient database schemas.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>Last edited yesterday
                                    </small>
                                    <div>
                                        <span class="badge bg-primary me-2">
                                            <i class="bi bi-eye me-1"></i>41 views
                                        </span>
                                        <span class="badge bg-success">
                                            <i class="bi bi-heart me-1"></i>12 likes
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="note-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">Web Development Frameworks</h5>
                                        <span class="category-badge bg-warning">Web Development</span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-share me-2"></i>Share</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="text-muted mb-3">Comparison of React, Vue, and Angular frameworks. Includes pros, cons, and use cases for each.</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>Last edited 3 days ago
                                    </small>
                                    <div>
                                        <span class="badge bg-primary me-2">
                                            <i class="bi bi-eye me-1"></i>67 views
                                        </span>
                                        <span class="badge bg-success">
                                            <i class="bi bi-heart me-1"></i>18 likes
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="col-lg-4">
                            <div class="recent-activity">
                                <h5 class="fw-bold mb-3">Recent Activity</h5>
                                
                                <div class="activity-item">
                                    <div class="activity-icon bg-success text-white">
                                        <i class="bi bi-plus-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">New note created</h6>
                                        <small class="text-muted">Data Structures & Algorithms</small>
                                        <br><small class="text-muted">2 hours ago</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon bg-primary text-white">
                                        <i class="bi bi-share"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Note shared with you</h6>
                                        <small class="text-muted">Machine Learning Basics</small>
                                        <br><small class="text-muted">5 hours ago</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon bg-warning text-white">
                                        <i class="bi bi-heart"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Note liked</h6>
                                        <small class="text-muted">Someone liked "Database Design"</small>
                                        <br><small class="text-muted">1 day ago</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon bg-info text-white">
                                        <i class="bi bi-chat"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">New comment</h6>
                                        <small class="text-muted">Comment on "Web Frameworks"</small>
                                        <br><small class="text-muted">2 days ago</small>
                                    </div>
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
        // Sidebar navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links
                document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
            });
        });
        
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