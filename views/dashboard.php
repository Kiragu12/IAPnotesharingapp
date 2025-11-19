<?php
// Start session
session_start();

// Prevent page caching to ensure fresh data
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Load classes for message handling
require_once '../app/Services/Global/fncs.php';
require_once '../config/conf.php';
require_once '../app/Services/Global/Database.php';
// If running without DB, load stub provider
require_once __DIR__ . '/../app/Services/Global/StubData.php';

$ObjFncs = new fncs();
$db = new Database($conf);
$stubProvider = new StubData();

// If DB is stubbed, ensure a demo user is present in session for UI testing
if ($db->isStubMode()) {
    if (!isset($_SESSION['user_id'])) {
        $u = $stubProvider->getSampleUser();
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['user_name'] = $u['full_name'];
        $_SESSION['user_email'] = $u['email'];
    }
}

$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header('Location: auth/signin.php');
    exit();
}


$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'];

// Handle search functionality
$search = $_GET['search'] ?? '';

// Get user's recent notes with search capability
try {
    if ($db->isStubMode()) {
        // Use stub data for UI testing
        $user_notes = $stubProvider->getUserNotes($user_id);
        $stats = $stubProvider->getStats($user_id);
        $nav_counters = $stubProvider->getNavCounters();
    } else {
        $where_clauses = ["notes.user_id = ?"];
        $params = [$user_id];
        
        if (!empty($search)) {
            $search_term = "%$search%";
            $where_clauses[] = "(notes.title LIKE ? OR notes.content LIKE ? OR notes.tags LIKE ?)";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        $sql = "SELECT notes.*, 
                       CASE 
                           WHEN notes.note_type = 'file' THEN notes.content
                           ELSE SUBSTRING(notes.content, 1, 150)
                       END as preview
                FROM notes 
                WHERE $where_sql 
                ORDER BY notes.updated_at DESC 
                LIMIT 6";
        
        $user_notes = $db->fetchAll($sql, $params);
        
        // Get user statistics
        $stats = [
            'total_notes' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE user_id = ?", [$user_id])['count'],
            'public_notes' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE user_id = ? AND is_public = ?", [$user_id, 1])['count']
        ];
        
        // Get navigation counters
        $nav_counters = [
            'my_notes_count' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE user_id = ?", [$user_id])['count'],
            'shared_notes_count' => $db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE is_public = 1", [])['count']
        ];
    }
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $user_notes = [];
    $stats = ['total_notes' => 0, 'public_notes' => 0];
    $nav_counters = ['my_notes_count' => 0, 'shared_notes_count' => 0];
}

// Check for welcome messages
$welcome_msg = $ObjFncs->getMsg('msg');
$first_login = false; // Set to false by default, can be modified by login logic

// Check for URL parameter messages
$success_msg = '';
$success_icon = 'bi-check-circle';
$error_msg = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'note_deleted':
            $success_msg = 'Note successfully deleted!';
            $success_icon = 'bi-trash3';
            break;
        case 'note_created':
            $success_msg = 'Note successfully created!';
            $success_icon = 'bi-plus-circle';
            break;
        case 'note_updated':
            $success_msg = 'Note successfully updated!';
            $success_icon = 'bi-pencil-square';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_note_id':
            $error_msg = 'Invalid note ID. The note may have already been deleted.';
            break;
        case 'delete_failed':
            $error_msg = 'Failed to delete the note. Please try again.';
            break;
        case 'note_not_found':
            $error_msg = 'Note not found. It may have been deleted or you do not have permission to access it.';
            break;
        default:
            $error_msg = 'An error occurred. Please try again.';
    }
}
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
        .nav-link .badge {
            font-size: 0.7rem;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-link:hover .badge {
            transform: scale(1.1);
            transition: transform 0.2s ease;
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
                <form method="GET" class="d-flex w-100" style="max-width: 500px;">
                    <input type="search" name="search" class="form-control search-box me-2" 
                           placeholder="Search your notes..." value="<?php echo htmlspecialchars($search); ?>"
                           style="max-width: 400px;">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
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
                            <button class="nav-link w-100 text-start border-0 d-flex justify-content-between align-items-center" style="background: none;" onclick="navigateTo('my-notes')">
                                <span><i class="bi bi-journal-text me-2"></i>My Notes</span>
                                <span class="badge bg-primary rounded-pill"><?php echo $nav_counters['my_notes_count']; ?></span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link w-100 text-start border-0 d-flex justify-content-between align-items-center" style="background: none;" onclick="navigateTo('shared-notes')">
                                <span><i class="bi bi-share me-2"></i>Shared Notes</span>
                                <span class="badge bg-success rounded-pill"><?php echo $nav_counters['shared_notes_count']; ?></span>
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
                    
                    // Display success messages
                    if (!empty($success_msg)) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                            <i class="bi ' . $success_icon . ' me-2"></i>' . $success_msg . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>';
                    }
                    
                    // Display error messages
                    if (!empty($error_msg)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                            <i class="bi bi-exclamation-triangle me-2"></i>' . $error_msg . '
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
                                    <a href="#my-notes-section" class="quick-action-btn" onclick="scrollToMyNotes()"
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

                    <!-- Stats Cards - Real Data -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card stats-card border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="stats-icon bg-primary mx-auto mb-3">
                                        <i class="bi bi-journal-text"></i>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="fw-bold text-primary mb-1"><?php echo $stats['total_notes']; ?></h3>
                                            <p class="text-muted mb-0 small">Total Notes</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: <?php echo min(100, $stats['total_notes'] * 10); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stats-card border-0 h-100">
                                <div class="card-body text-center">
                                    <div class="stats-icon bg-success mx-auto mb-3">
                                        <i class="bi bi-globe"></i>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="fw-bold text-success mb-1"><?php echo $stats['public_notes']; ?></h3>
                                            <p class="text-muted mb-0 small">Public Notes</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo min(100, $stats['public_notes'] * 10); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <!-- Recent Notes with Enhanced Bootstrap Cards -->
                        <div id="my-notes-section">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                                    <h4 class="fw-bold mb-0">
                                        <i class="bi bi-journal-text me-2 text-primary"></i>
                                        <?php if (!empty($search)): ?>
                                            Search Results for "<?php echo htmlspecialchars($search); ?>"
                                        <?php else: ?>
                                            Recent Notes
                                        <?php endif; ?>
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
                                    <?php if (empty($user_notes)): ?>
                                        <!-- No notes state -->
                                        <div class="note-card border-0">
                                            <div class="card-body text-center py-5">
                                                <i class="bi bi-journal-plus text-muted" style="font-size: 3rem;"></i>
                                                <h5 class="text-muted mt-3">
                                                    <?php if (!empty($search)): ?>
                                                        No notes found for "<?php echo htmlspecialchars($search); ?>"
                                                    <?php else: ?>
                                                        No notes yet
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="text-muted mb-4">
                                                    <?php if (!empty($search)): ?>
                                                        Try a different search term or create a new note.
                                                    <?php else: ?>
                                                        Start creating your first note to see it here.
                                                    <?php endif; ?>
                                                </p>
                                                <a href="notes/create.php" class="btn btn-primary">
                                                    <i class="bi bi-plus-circle me-2"></i>Create Your First Note
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Dynamic notes from database -->
                                        <?php foreach ($user_notes as $index => $note): ?>
                                            <div class="note-card border-0 <?php echo $index < count($user_notes) - 1 ? 'border-bottom' : ''; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <h5 class="fw-bold mb-0 me-3"><?php echo htmlspecialchars($note['title']); ?></h5>
                                                                <?php if ($note['note_type'] === 'file'): ?>
                                                                    <span class="badge bg-success text-white">
                                                                        <i class="bi bi-file-earmark me-1"></i>File
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-primary text-white">
                                                                        <i class="bi bi-file-text me-1"></i>Text
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if ($note['is_public']): ?>
                                                                    <span class="badge bg-info text-white ms-2">
                                                                        <i class="bi bi-globe me-1"></i>Public
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary text-white ms-2">
                                                                        <i class="bi bi-lock me-1"></i>Private
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($note['preview']); ?><?php echo strlen($note['content']) > 150 ? '...' : ''; ?></p>
                                                        </div>
                                                        <div class="dropdown ms-3">
                                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li><a class="dropdown-item" href="notes/view.php?id=<?php echo $note['id']; ?>"><i class="bi bi-eye me-2"></i>View</a></li>
                                                                <li><a class="dropdown-item" href="notes/edit.php?id=<?php echo $note['id']; ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                                <?php if ($note['note_type'] === 'file' && $note['file_path']): ?>
                                                                    <li><a class="dropdown-item" href="notes/download.php?id=<?php echo $note['id']; ?>" target="_blank"><i class="bi bi-download me-2"></i>Download</a></li>
                                                                <?php endif; ?>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteNote(<?php echo $note['id']; ?>)"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center text-muted">
                                                            <small class="me-3">
                                                                <i class="bi bi-clock me-1"></i><?php echo date('M j, Y • g:i A', strtotime($note['updated_at'])); ?>
                                                            </small>
                                                            <?php if ($note['note_type'] === 'file'): ?>
                                                                <small class="me-3">
                                                                    <i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($note['file_name']); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                            <?php if ($note['tags']): ?>
                                                                <small class="me-3">
                                                                    <i class="bi bi-tags me-1"></i><?php echo htmlspecialchars($note['tags']); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <span class="badge bg-light text-dark">
                                                                <i class="bi bi-pencil me-1"></i><?php echo $note['status']; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                    // Stay on dashboard and scroll to notes section
                    document.getElementById('my-notes-section')?.scrollIntoView({ behavior: 'smooth' });
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
        

        
        // Enhanced search functionality with real-time server search
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const searchForm = document.querySelector('form');
            let searchTimeout;

            if (searchInput) {
                // Real-time search with debouncing
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        // Auto-submit form for real-time search
                        if (searchInput.value.length >= 1 || searchInput.value.length === 0) {
                            searchForm.submit();
                        }
                    }, 600); // 600ms delay
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

            // Highlight search terms in results
            const searchTerm = "<?php echo htmlspecialchars($search); ?>";
            if (searchTerm.length > 0) {
                highlightSearchTerms(searchTerm);
            }
        });

        // Function to highlight search terms in note titles and content
        function highlightSearchTerms(term) {
            const noteElements = document.querySelectorAll('.note-card h5, .note-card p');
            const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
            
            noteElements.forEach(element => {
                if (element.textContent.toLowerCase().includes(term.toLowerCase())) {
                    element.innerHTML = element.innerHTML.replace(regex, '<mark style="background: #fff3cd; color: #856404; padding: 2px 4px; border-radius: 3px;">$1</mark>');
                }
            });
        }

        // Helper function to escape special characters in regex
        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Delete note function
        function deleteNote(noteId) {
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'notes/delete-handler.php';
                
                const noteIdInput = document.createElement('input');
                noteIdInput.type = 'hidden';
                noteIdInput.name = 'note_id';
                noteIdInput.value = noteId;
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                // Preserve current search parameters
                const currentSearch = new URLSearchParams(window.location.search).get('search');
                if (currentSearch) {
                    const searchInput = document.createElement('input');
                    searchInput.type = 'hidden';
                    searchInput.name = 'current_search';
                    searchInput.value = currentSearch;
                    form.appendChild(searchInput);
                }
                
                form.appendChild(noteIdInput);
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Scroll to notes section
        function scrollToMyNotes() {
            document.getElementById('my-notes-section')?.scrollIntoView({ behavior: 'smooth' });
        }
        
        // Add smooth animations on load
        document.addEventListener('DOMContentLoaded', function() {
            // Force refresh if coming from an update
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('message') && urlParams.has('t')) {
                // Remove the timestamp parameter to clean the URL
                const newUrl = new URL(window.location);
                newUrl.searchParams.delete('t');
                window.history.replaceState({}, '', newUrl);
            }
            
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
            
            // Auto-scroll to notes section if there's a hash in URL or after actions
            if (window.location.hash === '#my-notes-section' || 
                window.location.search.includes('message=note_deleted') ||
                window.location.search.includes('message=note_updated') ||
                window.location.search.includes('message=note_created') ||
                window.location.search.includes('error=delete_failed') ||
                window.location.search.includes('error=invalid_note_id')) {
                setTimeout(() => {
                    scrollToMyNotes();
                }, 500); // Wait for animations to start
            }
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