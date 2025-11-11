<?php
/**
 * Enhanced Shared Notes Page - Beautiful Gallery for Community Content
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/signin.php');
    exit();
}

require_once '../config/conf.php';
require_once '../app/Services/Global/Database.php';
require_once '../app/Services/Global/fncs.php';

$ObjFncs = new fncs();
$db = new Database($conf);

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Handle search and filtering
$search = $_GET['search'] ?? '';
$filter_type = $_GET['type'] ?? '';
$sort_by = $_GET['sort'] ?? 'newest';

// Build query for public notes
$where_clauses = ["notes.is_public = 1"];
$params = [];

if (!empty($search)) {
    $search_term = "%$search%";
    $where_clauses[] = "(notes.title LIKE ? OR notes.content LIKE ? OR notes.tags LIKE ? OR users.full_name LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($filter_type)) {
    $where_clauses[] = "notes.note_type = ?";
    $params[] = $filter_type;
}

$where_sql = implode(' AND ', $where_clauses);

// Determine sort order
$order_sql = "ORDER BY notes.created_at DESC";
switch ($sort_by) {
    case 'oldest':
        $order_sql = "ORDER BY notes.created_at ASC";
        break;
    case 'title':
        $order_sql = "ORDER BY notes.title ASC";
        break;
    case 'author':
        $order_sql = "ORDER BY users.full_name ASC";
        break;
    default:
        $order_sql = "ORDER BY notes.created_at DESC";
}

// Get notes with user information
try {
    $sql = "SELECT notes.*, users.full_name, users.email,
                   CASE 
                       WHEN notes.note_type = 'file' THEN notes.content
                       ELSE SUBSTRING(notes.content, 1, 200)
                   END as preview
            FROM notes 
            LEFT JOIN users ON notes.user_id = users.id 
            WHERE $where_sql 
            $order_sql";
    
    $notes = $db->fetchAll($sql, $params);
    
    // Get statistics
    $stats = [
        'total_notes' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE is_public = ?", [1])['count'],
        'text_notes' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE is_public = ? AND note_type = ?", [1, 'text'])['count'],
        'file_notes' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE is_public = ? AND note_type = ?", [1, 'file'])['count'],
        'total_users' => $db->fetchOne("SELECT COUNT(DISTINCT user_id) as count FROM notes WHERE is_public = ?", [1])['count']
    ];
    
} catch (Exception $e) {
    error_log("Error fetching shared notes: " . $e->getMessage());
    $notes = [];
    $stats = ['total_notes' => 0, 'text_notes' => 0, 'file_notes' => 0, 'total_users' => 0];
}

// Get messages
$success_msg = $ObjFncs->getMsg('success');
$error_msg = $ObjFncs->getMsg('errors');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Shared Notes - NotesShare Academy</title>
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
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: none;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .note-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border: none;
            overflow: hidden;
            height: 100%;
        }
        
        .note-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .note-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .note-type-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .note-type-file {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .file-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }
        
        .pdf-icon { color: #dc3545; }
        .doc-icon { color: #0d6efd; }
        .img-icon { color: #198754; }
        .xlsx-icon { color: #fd7e14; }
        .ppt-icon { color: #e83e8c; }
        .default-icon { color: #6c757d; }
        
        .search-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .search-input {
            background: #f8f9fa;
            border: 2px solid transparent;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        /* Search highlighting styles */
        mark {
            background: #fff3cd !important;
            color: #856404 !important;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .download-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
            color: white;
        }
        
        .view-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'shared-notes');">
                <i class="bi bi-journal-text me-2"></i>NotesShare Academy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'shared-notes');">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-share me-1"></i>Shared Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notes/create.php">
                            <i class="bi bi-plus-circle me-1"></i>Create Note
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notes/my-notes.php">
                            <i class="bi bi-folder me-1"></i>My Notes
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
                <i class="bi bi-globe2 me-3" style="color: #667eea;"></i>
                Community Knowledge Hub
            </h1>
            <p class="lead text-muted">Discover, learn, and share amazing content with our learning community</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-journal-text stats-icon" style="color: #667eea;"></i>
                    <h3 class="fw-bold mb-1"><?php echo number_format($stats['total_notes']); ?></h3>
                    <p class="text-muted mb-0">Total Shared</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-file-text stats-icon" style="color: #28a745;"></i>
                    <h3 class="fw-bold mb-1"><?php echo number_format($stats['text_notes']); ?></h3>
                    <p class="text-muted mb-0">Text Notes</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-file-earmark stats-icon" style="color: #fd7e14;"></i>
                    <h3 class="fw-bold mb-1"><?php echo number_format($stats['file_notes']); ?></h3>
                    <p class="text-muted mb-0">File Uploads</p>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <i class="bi bi-people stats-icon" style="color: #e83e8c;"></i>
                    <h3 class="fw-bold mb-1"><?php echo number_format($stats['total_users']); ?></h3>
                    <p class="text-muted mb-0">Contributors</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" class="form-control search-input ps-5" 
                               placeholder="Try: python, calculus, shakespeare, physics..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select search-input">
                        <option value="">All Types</option>
                        <option value="text" <?php echo $filter_type === 'text' ? 'selected' : ''; ?>>📝 Text Notes</option>
                        <option value="file" <?php echo $filter_type === 'file' ? 'selected' : ''; ?>>📁 Files</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select search-input">
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                        <option value="author" <?php echo $sort_by === 'author' ? 'selected' : ''; ?>>Author A-Z</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-gradient w-100">
                        <i class="bi bi-funnel-fill me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Notes Grid -->
        <?php if (empty($notes)): ?>
            <div class="empty-state">
                <i class="bi bi-folder-x"></i>
                <h3 class="text-muted">No shared content found</h3>
                <p class="text-muted">Be the first to share knowledge with our community!</p>
                <a href="notes/create.php" class="btn btn-gradient mt-3">
                    <i class="bi bi-plus-circle me-2"></i>Create Your First Note
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($notes as $note): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card note-card h-100 position-relative">
                            <!-- Note Type Badge -->
                            <span class="note-type-badge note-type-<?php echo $note['note_type']; ?>">
                                <?php if ($note['note_type'] === 'file'): ?>
                                    <i class="bi bi-file-earmark me-1"></i>File
                                <?php else: ?>
                                    <i class="bi bi-file-text me-1"></i>Text
                                <?php endif; ?>
                            </span>
                            
                            <div class="card-body d-flex flex-column p-4">
                                
                                <!-- Author Info -->
                                <div class="author-info">
                                    <div class="user-avatar me-3">
                                        <?php echo strtoupper(substr($note['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($note['full_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('M j, Y • g:i A', strtotime($note['created_at'])); ?></small>
                                    </div>
                                </div>

                                <?php if ($note['note_type'] === 'file'): ?>
                                    <!-- File Display -->
                                    <div class="text-center mb-3">
                                        <?php
                                        $file_ext = strtolower(pathinfo($note['file_name'], PATHINFO_EXTENSION));
                                        $icon_class = 'default-icon';
                                        $icon = 'bi-file-earmark';
                                        
                                        switch ($file_ext) {
                                            case 'pdf':
                                                $icon_class = 'pdf-icon';
                                                $icon = 'bi-file-earmark-pdf-fill';
                                                break;
                                            case 'doc':
                                            case 'docx':
                                                $icon_class = 'doc-icon';
                                                $icon = 'bi-file-earmark-word-fill';
                                                break;
                                            case 'jpg':
                                            case 'jpeg':
                                            case 'png':
                                            case 'gif':
                                                $icon_class = 'img-icon';
                                                $icon = 'bi-file-earmark-image-fill';
                                                break;
                                            case 'xlsx':
                                            case 'xls':
                                                $icon_class = 'xlsx-icon';
                                                $icon = 'bi-file-earmark-excel-fill';
                                                break;
                                            case 'ppt':
                                            case 'pptx':
                                                $icon_class = 'ppt-icon';
                                                $icon = 'bi-file-earmark-ppt-fill';
                                                break;
                                        }
                                        ?>
                                        <i class="bi <?php echo $icon; ?> file-icon <?php echo $icon_class; ?>"></i>
                                        <h6 class="fw-bold text-truncate"><?php echo htmlspecialchars($note['file_name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo number_format($note['file_size'] / 1024, 1); ?> KB
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Note Title -->
                                <h5 class="card-title fw-bold mb-3 lh-base">
                                    <?php echo htmlspecialchars($note['title']); ?>
                                </h5>
                                
                                <!-- Note Content/Preview -->
                                <div class="card-text text-muted mb-4 flex-grow-1">
                                    <p class="mb-0"><?php echo htmlspecialchars($note['preview']); ?><?php echo strlen($note['content']) > 200 ? '...' : ''; ?></p>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 mt-auto">
                                    <a href="notes/view.php?id=<?php echo $note['id']; ?>" 
                                       class="btn view-btn flex-fill">
                                        <i class="bi bi-eye-fill me-1"></i>View
                                    </a>
                                    
                                    <?php if ($note['note_type'] === 'file' && $note['file_path']): ?>
                                        <a href="notes/download.php?id=<?php echo $note['id']; ?>" 
                                           class="btn download-btn flex-fill" 
                                           target="_blank">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Create Note FAB -->
        <a href="notes/create.php" class="btn btn-gradient rounded-circle position-fixed shadow-lg" 
           style="bottom: 30px; right: 30px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; z-index: 1000;">
            <i class="bi bi-plus-lg fs-3"></i>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Enhanced search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const searchForm = document.querySelector('.search-section form');
            let searchTimeout;

            // Real-time search (search as you type with debouncing)
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        // Search with any length input (even 1 character)
                        if (searchInput.value.length >= 1 || searchInput.value.length === 0) {
                            searchForm.submit();
                        }
                    }, 600); // Faster response - 600ms after user stops typing
                });

                // Submit on Enter key
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(searchTimeout);
                        searchForm.submit();
                    }
                });
            }

            // Auto-submit when filter dropdowns change
            const filterSelects = document.querySelectorAll('select[name="type"], select[name="sort"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    searchForm.submit();
                });
            });

            // Highlight search terms in results
            const searchTerm = "<?php echo htmlspecialchars($search); ?>";
            if (searchTerm.length > 0) {
                highlightSearchTerms(searchTerm);
            }
        });

        // Function to highlight search terms in note titles and content
        function highlightSearchTerms(term) {
            // Target the correct elements in note cards
            const noteElements = document.querySelectorAll('.card-title, .card-text p');
            const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
            
            noteElements.forEach(element => {
                if (element.textContent.toLowerCase().includes(term.toLowerCase())) {
                    element.innerHTML = element.innerHTML.replace(regex, '<mark>$1</mark>');
                }
            });
        }

        // Helper function to escape special characters in regex
        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
    </script>
</body>
</html>
