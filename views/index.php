<?php
// Start session
session_start();

// Load required files for message handling
try {
    require_once '../config/ClassAutoLoad.php';
    
    // Redirect to dashboard if already logged in
    if (isset($_SESSION['user_id'])) {
        header('Location: dashboard.php');
        exit();
    }
    
    // Get any signup success messages
    $signup_message = $ObjFncs->getMsg('signup_success') ?: '';
} catch (Exception $e) {
    // If there's an error loading dependencies, continue without messages
    $signup_message = '';
    error_log("Index.php error loading dependencies: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NotesShare Academy - Share Knowledge, Build Together</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            transform: translateY(-2px);
        }
        
        .btn-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }
        
        .btn-nav:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="20" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .hero-buttons .btn {
            margin: 0.5rem;
            padding: 1rem 2.5rem;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary-hero {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .btn-primary-hero:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-outline-hero {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: white;
        }
        
        .btn-outline-hero:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            color: white;
        }
        
        /* Features Section */
        .features-section {
            padding: 5rem 0;
            background: #f8f9fa;
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-item {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 5rem 0;
            background: white;
            text-align: center;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .cta-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2.5rem;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .footer h5 {
            color: white;
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: white;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-up {
            animation: fadeInUp 0.8s ease forwards;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .feature-card {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-journal-bookmark me-2"></i>NotesShare Academy
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/signin.php">Sign In</a>
                    </li>
                </ul>
                <a href="auth/signup.php" class="btn btn-nav">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            
            <?php if (!empty($signup_message)): ?>
                <!-- Signup Success Message -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-radius: 15px; border: none; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-3" style="font-size: 2rem; color: #28a745;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1">Account Created Successfully! 🎉</h5>
                                    <p class="mb-0"><?= htmlspecialchars($signup_message) ?></p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content animate-fade-up">
                        <h1 class="hero-title">Share Knowledge,<br>Build Together</h1>
                        <p class="hero-subtitle">Connect with fellow students, share your notes, and create a collaborative learning community that grows together.</p>
                        <div class="hero-buttons">
                            <?php if (!empty($signup_message)): ?>
                                <a href="auth/signin.php" class="btn btn-primary-hero">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In Now
                                </a>
                                <a href="auth/dashboard-preview.php" class="btn btn-outline-hero">
                                    <i class="bi bi-eye me-2"></i>View Dashboard
                                </a>
                            <?php else: ?>
                                <a href="auth/signup.php" class="btn btn-primary-hero">
                                    <i class="bi bi-rocket-takeoff me-2"></i>Start Sharing
                                </a>
                                <a href="auth/dashboard-preview.php" class="btn btn-outline-hero">
                                    <i class="bi bi-eye me-2"></i>View Dashboard
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="bi bi-journal-text" style="font-size: 15rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <!-- Header Row -->
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Why Choose NotesShare Academy?</h2>
                    <p class="lead text-muted">Everything you need to create, share, and collaborate on academic content</p>
                </div>
            </div>
            
            <!-- Features Grid -->
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-share"></i>
                            </div>
                            <h4 class="card-title feature-title">Easy Sharing</h4>
                            <p class="card-text feature-description">Share your notes instantly with classmates and build a collaborative study environment.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h4 class="card-title feature-title">Secure Platform</h4>
                            <p class="card-text feature-description">Your academic content is protected with enterprise-grade security and privacy controls.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4 class="card-title feature-title">Community Driven</h4>
                            <p class="card-text feature-description">Join a vibrant community of learners who support each other's academic journey.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-laptop"></i>
                            </div>
                            <h4 class="card-title feature-title">Cross Platform</h4>
                            <p class="card-text feature-description">Access your notes from any device - desktop, tablet, or mobile. Study anywhere, anytime.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-search"></i>
                            </div>
                            <h4 class="card-title feature-title">Smart Search</h4>
                            <p class="card-text feature-description">Find exactly what you need with our intelligent search that understands academic content.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card feature-card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon mb-3">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h4 class="card-title feature-title">Track Progress</h4>
                            <p class="card-text feature-description">Monitor your learning progress and see how your shared notes help others succeed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="mb-3">
                            <i class="bi bi-people-fill" style="font-size: 2.5rem; opacity: 0.8;"></i>
                        </div>
                        <span class="stat-number">500+</span>
                        <span class="stat-label d-block">Active Students</span>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="mb-3">
                            <i class="bi bi-journal-text" style="font-size: 2.5rem; opacity: 0.8;"></i>
                        </div>
                        <span class="stat-number">2.5k+</span>
                        <span class="stat-label d-block">Notes Shared</span>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="mb-3">
                            <i class="bi bi-collection" style="font-size: 2.5rem; opacity: 0.8;"></i>
                        </div>
                        <span class="stat-number">50+</span>
                        <span class="stat-label d-block">Study Groups</span>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item">
                        <div class="mb-3">
                            <i class="bi bi-trophy" style="font-size: 2.5rem; opacity: 0.8;"></i>
                        </div>
                        <span class="stat-number">95%</span>
                        <span class="stat-label d-block">Success Rate</span>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: 95%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-6 fw-bold mb-3">What Students Say</h2>
                    <p class="lead text-muted">Real feedback from our learning community</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <p class="card-text">"NotesShare Academy has completely transformed how I study. The collaboration features are amazing!"</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Sarah Johnson</h6>
                                    <small class="text-muted">Computer Science Student</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <p class="card-text">"The security features give me confidence to share my work. The 2FA system is excellent!"</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Mike Chen</h6>
                                    <small class="text-muted">Engineering Student</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex mb-3">
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                                <i class="bi bi-star-fill text-warning"></i>
                            </div>
                            <p class="card-text">"Perfect platform for group projects. The interface is intuitive and modern!"</p>
                            <div class="d-flex align-items-center mt-3">
                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0">Emma Davis</h6>
                                    <small class="text-muted">Business Student</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="cta-title">Ready to Transform Your Learning?</h2>
                    <p class="cta-subtitle">Join thousands of students who are already sharing knowledge and achieving better results together.</p>
                    <div class="hero-buttons">
                        <a href="auth/signup.php" class="btn btn-primary-hero" style="color: white; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </a>
                        <a href="auth/signin.php" class="btn btn-outline-hero" style="color: #667eea; border-color: #667eea;">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>NotesShare Academy</h5>
                    <p class="text-muted">Empowering students through collaborative learning and knowledge sharing.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Platform</h5>
                    <ul class="list-unstyled">
                        <li><a href="auth/signup.php">Sign Up</a></li>
                        <li><a href="auth/signin.php">Sign In</a></li>
                        <li><a href="auth/dashboard-preview.php">Dashboard Preview</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Features</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Note Sharing</a></li>
                        <li><a href="#">Study Groups</a></li>
                        <li><a href="#">Collaboration</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contact</h5>
                    <p class="text-muted">
                        <i class="bi bi-envelope me-2"></i>support@noteshareacademy.com<br>
                        <i class="bi bi-phone me-2"></i>+254 700 123 456
                    </p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted">&copy; 2025 NotesShare Academy. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
                    }
                });
            }, observerOptions);
            
            // Observe all feature cards
            document.querySelectorAll('.feature-card, .stat-item').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>