<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Preview - IAP Note Sharing App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="../index.php">
                    <i class="bi bi-journal-text me-2"></i>IAP Note Sharing
                </a>
                <div class="ms-auto">
                    <a href="signin.php" class="btn btn-primary">Sign In to Access</a>
                </div>
            </div>
        </nav>

        <div class="container mt-5">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 mb-4">📚 Dashboard Preview</h1>
                    <p class="lead text-muted mb-5">This is what your dashboard will look like after you sign in</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- My Notes Card -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-file-text-fill text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">My Notes</h5>
                            <p class="card-text text-muted">Create, organize, and manage your personal study notes</p>
                            <div class="badge bg-light text-dark">24 Notes</div>
                        </div>
                    </div>
                </div>

                <!-- Shared Notes Card -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-share-fill text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Shared Notes</h5>
                            <p class="card-text text-muted">Access notes shared by your classmates and friends</p>
                            <div class="badge bg-light text-dark">156 Available</div>
                        </div>
                    </div>
                </div>

                <!-- Study Groups Card -->
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-people-fill text-warning mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Study Groups</h5>
                            <p class="card-text text-muted">Join study groups and collaborate with other students</p>
                            <div class="badge bg-light text-dark">8 Groups</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-graph-up text-info me-2"></i>Recent Activity
                            </h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bi bi-plus-circle text-success me-2"></i>
                                    <span class="text-muted">Math 101 - Calculus Notes uploaded</span>
                                    <small class="text-muted ms-2">2 hours ago</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-share text-primary me-2"></i>
                                    <span class="text-muted">Physics Lab Report shared with Study Group</span>
                                    <small class="text-muted ms-2">1 day ago</small>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-heart text-danger me-2"></i>
                                    <span class="text-muted">Chemistry Notes favorited by 5 students</span>
                                    <small class="text-muted ms-2">3 days ago</small>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5 mb-5">
                <div class="col-12 text-center">
                    <h3 class="mb-3">Ready to get started?</h3>
                    <p class="text-muted mb-4">Sign up for free and join thousands of students sharing knowledge</p>
                    <a href="signup.php" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-person-plus me-2"></i>Sign Up Free
                    </a>
                    <a href="signin.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
