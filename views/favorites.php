<?php
/**
 * Favorites Page - User's Favorite Notes
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/signin.php');
    exit();
}

require_once '../config/conf.php';
require_once '../app/Services/Global/Database.php';
require_once '../app/Controllers/FavoritesController.php';
require_once '../app/Services/Global/fncs.php';

$db = new Database($conf);
$favoritesController = new FavoritesController($db);
$ObjFncs = new fncs();

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Handle search functionality
$search = $_GET['search'] ?? '';

// Get user's favorite notes
$favorites = $favoritesController->getUserFavorites($user_id, $search, 50);
$favorites_count = $favoritesController->getFavoritesCount($user_id);

// Get messages
$success_msg = $ObjFncs->getMsg('success');
$error_msg = $ObjFncs->getMsg('errors');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 30px;
        }
        
        .note-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
        }
        
        .note-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .note-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.75rem;
            z-index: 2;
        }
        
        .note-type-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .note-type-file {
            background: linear-gradient(135deg, #ff9a8b 0%, #fecfef 100%);
            color: #333;
        }
        
        /* Favorite button styles removed */
        
        .note-content {
            padding: 20px;
        }
        
        .note-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .note-meta {
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 15px;
        }
        
        .note-author {
            color: #667eea;
            font-weight: 500;
        }
        
        .note-summary {
            color: #555;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .tags-container {
            margin-bottom: 15px;
        }
        
        .tag {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            margin: 2px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .view-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
        }
        
        .favorited-date {
            color: #28a745;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid #e9ecef;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="bi bi-journal-text me-2"></i>NotesShare Academy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shared-notes.php">
                            <i class="bi bi-share me-1"></i>Shared Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-heart-fill me-1"></i>My Favorites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notes/create.php">
                            <i class="bi bi-plus-circle me-1"></i>Create Note
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
                    <a class="nav-link" href="auth/settings.php">
                        <i class="bi bi-gear me-1"></i>Settings
                    </a>
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="display-5 fw-bold text-dark mb-3">
                <i class="bi bi-heart-fill me-3" style="color: #ff6b6b;"></i>
                My Favorite Notes
            </h1>
            <p class="lead text-muted">Your carefully curated collection of saved notes</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Card -->
        <div class="row mb-4">
            <div class="col-md-4 mx-auto">
                <div class="stats-card">
                    <i class="bi bi-heart-fill" style="font-size: 2rem; color: #ff6b6b; margin-bottom: 10px;"></i>
                    <h3 class="fw-bold text-primary mb-1"><?php echo $favorites_count; ?></h3>
                    <p class="text-muted mb-0 small">Favorite Notes</p>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="card search-card">
            <div class="card-body">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" class="form-control search-box me-3" 
                           placeholder="Search your favorite notes..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="favorites.php" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Notes Grid -->
        <?php if (!empty($favorites)): ?>
            <div class="row">
                <?php foreach ($favorites as $note): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="note-card">
                            <!-- Note Type Badge -->
                            <div class="note-type-badge note-type-<?php echo $note['note_type'] ?? 'text'; ?>">
                                <?php if (($note['note_type'] ?? 'text') === 'file'): ?>
                                    <i class="bi bi-file-earmark me-1"></i>File
                                <?php else: ?>
                                    <i class="bi bi-file-text me-1"></i>Text
                                <?php endif; ?>
                            </div>

                            <!-- Favorite Button Removed -->

                            <div class="note-content">
                                <h5 class="note-title"><?php echo htmlspecialchars($note['title']); ?></h5>
                                
                                <div class="note-meta">
                                    <i class="bi bi-person me-1"></i>
                                    <span class="note-author"><?php echo htmlspecialchars($note['author_name'] ?? 'Unknown'); ?></span>
                                    <?php if (!empty($note['category_name'])): ?>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($note['category_name']); ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="favorited-date">
                                    <i class="bi bi-heart me-1"></i>Favorited on <?php echo date('M j, Y', strtotime($note['favorited_at'])); ?>
                                </div>
                                
                                <?php if (!empty($note['summary'])): ?>
                                    <div class="note-summary">
                                        <?php echo htmlspecialchars(substr($note['summary'], 0, 120)); ?>
                                        <?php if (strlen($note['summary']) > 120): ?>...<?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($note['tags'])): ?>
                                    <div class="tags-container">
                                        <?php 
                                        $tags = explode(',', $note['tags']);
                                        foreach (array_slice($tags, 0, 3) as $tag): 
                                            $tag = trim($tag);
                                            if (!empty($tag)):
                                        ?>
                                            <span class="tag">#<?php echo htmlspecialchars($tag); ?></span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        if (count($tags) > 3): 
                                        ?>
                                            <span class="tag">+<?php echo count($tags) - 3; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="notes/view.php?id=<?php echo $note['id']; ?>" class="view-btn">
                                        <i class="bi bi-eye me-1"></i>View Note
                                    </a>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="bi bi-heart"></i>
                <h4 class="mb-3">
                    <?php if (!empty($search)): ?>
                        No favorites found for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        No favorites yet
                    <?php endif; ?>
                </h4>
                <p class="text-muted mb-4">
                    <?php if (!empty($search)): ?>
                        Try a different search term or browse all your favorites.
                    <?php else: ?>
                        Start exploring notes and save your favorites by clicking the heart icon.
                    <?php endif; ?>
                </p>
                <div>
                    <?php if (!empty($search)): ?>
                        <a href="favorites.php" class="btn btn-outline-primary me-3">
                            <i class="bi bi-heart me-2"></i>View All Favorites
                        </a>
                    <?php endif; ?>
                    <a href="shared-notes.php" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Explore Notes
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle favorite function removed - no longer needed on favorites page
        
        // Toast notification function
        function showToast(message, type) {
            // Create toast element
            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.querySelector('.toast:last-child');
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
            
            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
        
        // Add smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.note-card, .stats-card, .search-card');
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
    </script>
</body>
</html>
