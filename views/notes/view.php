<?php
/**
 * View Single Note Page
 */

session_start();

// Check if note ID is provided
if (!isset($_GET['id'])) {
    header('Location: ../auth/dashboard.php');
    exit();
}

$note_id = (int)$_GET['id'];

require_once '../../config/conf.php';
require_once '../../app/Controllers/NotesController.php';
require_once '../../app/Services/Global/fncs.php';

$notesController = new NotesController();
$ObjFncs = new fncs();

// Get the note
$user_id = $_SESSION['user_id'] ?? null;
$note = $notesController->getNote($note_id, $user_id);

if (!$note) {
    header('Location: ../auth/dashboard.php?error=note_not_found');
    exit();
}

// Check if user owns this note
$is_owner = $user_id && $note['user_id'] == $user_id;
$user_name = $_SESSION['full_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($note['title']); ?> - <?php echo htmlspecialchars($conf['site_name']); ?></title>
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
        }
        .note-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .note-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .category-badge {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        .tag-badge {
            background: rgba(118, 75, 162, 0.1);
            color: #764ba2;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin: 0.1rem;
            display: inline-block;
        }
        .author-info {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1rem;
        }
        .stat-item {
            text-align: center;
            padding: 0.5rem;
        }
        .stat-value {
            font-size: 1.25rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .btn-action {
            border-radius: 10px;
            padding: 0.5rem 1rem;
        }
        .note-meta {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../auth/dashboard.php">
                <i class="bi bi-journal-bookmark me-2"></i><?php echo htmlspecialchars($conf['site_name']); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <?php if ($user_id): ?>
                    <a class="nav-link" href="create.php">
                        <i class="bi bi-plus-circle me-1"></i>Create Note
                    </a>
                    <a class="nav-link" href="my-notes.php">
                        <i class="bi bi-journals me-1"></i>My Notes
                    </a>
                    <a class="nav-link" href="../auth/dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="../auth/signin.php">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                    </a>
                    <a class="nav-link" href="../auth/signup.php">
                        <i class="bi bi-person-plus me-1"></i>Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Note Card -->
                <div class="card mb-4">
                    <!-- Note Header -->
                    <div class="note-header p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h1 class="mb-3"><?php echo htmlspecialchars($note['title']); ?></h1>
                                
                                <!-- Meta Information -->
                                <div class="note-meta mb-3">
                                    <i class="bi bi-person me-1"></i>By <?php echo htmlspecialchars($note['author_name']); ?>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-calendar me-1"></i><?php echo date('M j, Y', strtotime($note['created_at'])); ?>
                                    <?php if ($note['created_at'] !== $note['updated_at']): ?>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-pencil me-1"></i>Updated <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                                    <?php endif; ?>
                                </div>

                                <!-- Category and Status -->
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if ($note['category_name']): ?>
                                        <span class="category-badge">
                                            <i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($note['category_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-eye me-1"></i><?php echo $note['status']; ?>
                                    </span>
                                    
                                    <?php if ($note['is_public']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-globe me-1"></i>Public
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-lock me-1"></i>Private
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Action Buttons for Owner -->
                            <?php if ($is_owner): ?>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="edit.php?id=<?php echo $note['id']; ?>">
                                            <i class="bi bi-pencil me-2"></i>Edit Note
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $note['id']; ?>)">
                                            <i class="bi bi-trash me-2"></i>Delete Note
                                        </a></li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Note Body -->
                    <div class="card-body p-4">
                        <!-- Summary (if different from content start) -->
                        <?php if ($note['summary'] && strlen($note['summary']) < strlen($note['content'])): ?>
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Summary</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($note['summary']); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Main Content -->
                        <div class="note-content">
                            <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                        </div>

                        <!-- Tags -->
                        <?php if ($note['tags']): ?>
                            <hr class="my-4">
                            <div>
                                <h6 class="text-muted mb-2">Tags:</h6>
                                <?php 
                                $tags = explode(',', $note['tags']);
                                foreach ($tags as $tag): 
                                    $tag = trim($tag);
                                    if ($tag):
                                ?>
                                    <span class="tag-badge"><?php echo htmlspecialchars($tag); ?></span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Author Info -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-person-circle me-2"></i>Author Information
                        </h6>
                        <div class="author-info">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person text-white fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($note['author_name']); ?></h6>
                                    <small class="text-muted">Note Author</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Note Statistics -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-graph-up me-2"></i>Note Statistics
                        </h6>
                        <div class="row text-center">
                            <div class="col-4 stat-item">
                                <div class="stat-value"><?php echo number_format($note['view_count']); ?></div>
                                <div class="stat-label">Views</div>
                            </div>
                            <div class="col-4 stat-item">
                                <div class="stat-value"><?php echo number_format($note['like_count']); ?></div>
                                <div class="stat-label">Likes</div>
                            </div>
                            <div class="col-4 stat-item">
                                <div class="stat-value"><?php echo number_format($note['share_count']); ?></div>
                                <div class="stat-label">Shares</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </h6>
                        <div class="d-grid gap-2">
                            <?php if ($user_id): ?>
                                <button class="btn btn-outline-primary btn-action">
                                    <i class="bi bi-heart me-2"></i>Like Note
                                </button>
                                <button class="btn btn-outline-secondary btn-action">
                                    <i class="bi bi-share me-2"></i>Share Note
                                </button>
                                <a href="create.php" class="btn btn-primary btn-action">
                                    <i class="bi bi-plus-circle me-2"></i>Create New Note
                                </a>
                            <?php else: ?>
                                <a href="../auth/signin.php" class="btn btn-primary btn-action">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Interact
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Like note functionality (placeholder)
        document.querySelector('.btn-outline-primary')?.addEventListener('click', function() {
            // TODO: Implement like functionality
            alert('Like functionality coming soon!');
        });

        // Share note functionality (placeholder)
        document.querySelector('.btn-outline-secondary')?.addEventListener('click', function() {
            // TODO: Implement share functionality
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($note['title']); ?>',
                    text: '<?php echo addslashes($note['summary'] ?: substr($note['content'], 0, 100)); ?>',
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Note URL copied to clipboard!');
                });
            }
        });
    </script>
</body>
</html>
