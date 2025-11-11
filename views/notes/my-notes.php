<?php
/**
 * My Notes - List User's Notes
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

require_once '../../config/conf.php';
require_once '../../app/Controllers/NotesController.php';
require_once '../../app/Services/Global/fncs.php';

$notesController = new NotesController();
$ObjFncs = new fncs();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'User';

// Get filter parameters
$status_filter = $_GET['status'] ?? null;
$search = $_GET['search'] ?? '';

// Get user's notes
$notes = $notesController->getUserNotes($user_id, $status_filter);

// Filter by search if provided
if ($search) {
    $notes = array_filter($notes, function($note) use ($search) {
        return stripos($note['title'], $search) !== false || 
               stripos($note['content'], $search) !== false ||
               stripos($note['tags'], $search) !== false;
    });
}

// Get any messages
$success_message = $ObjFncs->getMsg('success');
$error_message = $ObjFncs->getMsg('errors');

// Count notes by status
$draft_count = count(array_filter($notes, fn($n) => $n['status'] === 'draft'));
$published_count = count(array_filter($notes, fn($n) => $n['status'] === 'published'));
$total_count = count($notes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes - <?php echo htmlspecialchars($conf['site_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .note-card {
            height: 100%;
            cursor: pointer;
        }
        .note-card .card-body {
            display: flex;
            flex-direction: column;
        }
        .note-content {
            flex-grow: 1;
        }
        .category-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        .status-draft {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
        }
        .status-published {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .filter-pills .nav-link {
            border-radius: 50px;
            margin-right: 0.5rem;
        }
        .filter-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .note-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .search-box {
            border-radius: 50px;
            border: 2px solid rgba(102, 126, 234, 0.1);
        }
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'my-notes');">
                <i class="bi bi-journal-bookmark me-2"></i><?php echo htmlspecialchars($conf['site_name']); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="create.php">
                    <i class="bi bi-plus-circle me-1"></i>Create Note
                </a>
                <a class="nav-link active" href="my-notes.php">
                    <i class="bi bi-journals me-1"></i>My Notes
                </a>
                <a class="nav-link" href="../dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'my-notes');">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="../auth/settings.php">
                    <i class="bi bi-gear me-1"></i>Settings
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold">
                    <i class="bi bi-journals text-primary me-3"></i>My Notes
                </h1>
                <p class="text-muted">Manage and organize your personal notes</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="create.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Create New Note
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1"><?php echo $total_count; ?></h3>
                        <p class="mb-0">Total Notes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1"><?php echo $published_count; ?></h3>
                        <p class="mb-0">Published</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center py-3">
                        <h3 class="mb-1"><?php echo $draft_count; ?></h3>
                        <p class="mb-0">Drafts</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <!-- Filter Pills -->
                        <ul class="nav filter-pills">
                            <li class="nav-item">
                                <a class="nav-link <?php echo !$status_filter ? 'active' : ''; ?>" href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>">
                                    All Notes
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $status_filter === 'published' ? 'active' : ''; ?>" href="?status=published<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    Published
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $status_filter === 'draft' ? 'active' : ''; ?>" href="?status=draft<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                    Drafts
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <!-- Search Box -->
                        <form method="GET" class="d-flex">
                            <?php if ($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                            <?php endif; ?>
                            <input type="text" name="search" class="form-control search-box me-2" 
                                   placeholder="Search notes..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Grid -->
        <?php if (empty($notes)): ?>
            <div class="empty-state">
                <i class="bi bi-journal-x"></i>
                <h3>No Notes Found</h3>
                <?php if ($search): ?>
                    <p>No notes match your search criteria. <a href="my-notes.php">Clear search</a> to see all notes.</p>
                <?php else: ?>
                    <p>You haven't created any notes yet. Start sharing your knowledge!</p>
                    <a href="create.php" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>Create Your First Note
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($notes as $note): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card note-card" onclick="viewNote(<?php echo $note['id']; ?>)">
                            <div class="card-body">
                                <!-- Note Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="note-content flex-grow-1">
                                        <h5 class="card-title mb-2"><?php echo htmlspecialchars($note['title']); ?></h5>
                                    </div>
                                    <div class="dropdown" onclick="event.stopPropagation();">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="view.php?id=<?php echo $note['id']; ?>">
                                                <i class="bi bi-eye me-2"></i>View
                                            </a></li>
                                            <li><a class="dropdown-item" href="edit.php?id=<?php echo $note['id']; ?>">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $note['id']; ?>)">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Note Summary -->
                                <p class="card-text text-muted mb-3">
                                    <?php echo htmlspecialchars($note['summary'] ?: substr($note['content'], 0, 100) . '...'); ?>
                                </p>

                                <!-- Note Meta -->
                                <div class="note-meta mb-3">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <?php if ($note['category_name']): ?>
                                            <span class="badge category-badge">
                                                <?php echo htmlspecialchars($note['category_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <span class="badge status-<?php echo $note['status']; ?>">
                                            <?php echo ucfirst($note['status']); ?>
                                        </span>
                                        
                                        <?php if ($note['is_public']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-globe me-1"></i>Public
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <small>
                                        <i class="bi bi-calendar me-1"></i>
                                        <?php echo date('M j, Y', strtotime($note['created_at'])); ?>
                                        
                                        <span class="mx-2">•</span>
                                        
                                        <i class="bi bi-eye me-1"></i>
                                        <?php echo $note['view_count']; ?> views
                                    </small>
                                </div>

                                <!-- Tags -->
                                <?php if ($note['tags']): ?>
                                    <div class="mt-auto">
                                        <small class="text-muted">
                                            <i class="bi bi-tags me-1"></i>
                                            <?php 
                                            $tags = array_slice(explode(',', $note['tags']), 0, 3);
                                            echo htmlspecialchars(implode(', ', array_map('trim', $tags)));
                                            if (count(explode(',', $note['tags'])) > 3) echo '...';
                                            ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewNote(noteId) {
            window.location.href = `view.php?id=${noteId}`;
        }

        function confirmDelete(noteId) {
            if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../../app/Controllers/NotesController.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const noteIdInput = document.createElement('input');
                noteIdInput.type = 'hidden';
                noteIdInput.name = 'note_id';
                noteIdInput.value = noteId;
                
                form.appendChild(actionInput);
                form.appendChild(noteIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Prevent card click when clicking dropdown
        document.querySelectorAll('.dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>
