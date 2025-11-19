<?php
/**
 * Edit Note Page
 */

// Add cache control headers to ensure fresh data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

// Check if note ID is provided
if (!isset($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$note_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

require_once '../../config/conf.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';
require_once '../../app/Controllers/NotesController.php';

$db = new Database($conf);
$ObjFncs = new fncs();
$notesController = new NotesController($db);

// Get the note and verify ownership
$note = $notesController->getNote($note_id, $user_id);

if (!$note || $note['user_id'] != $user_id) {
    header('Location: ../dashboard.php?error=note_not_found');
    exit();
}

// Get categories for dropdown
try {
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
} catch (Exception $e) {
    $categories = [];
    error_log("Error fetching categories: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_note'])) {
    $data = [
        'title' => $_POST['title'] ?? '',
        'content' => $_POST['content'] ?? '',
        'summary' => $_POST['summary'] ?? '',
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'tags' => $_POST['tags'] ?? '',
        'is_public' => isset($_POST['is_public']) && $_POST['is_public'] === '1',
        'status' => $_POST['status'] ?? 'draft'
    ];
    
    $result = $notesController->updateNote($note_id, $data, $user_id);
    if ($result) {
        // Default redirect to shared-notes page
        $redirect_url = '../shared-notes.php?message=note_updated&t=' . time();
        
        // Check if there's a specific referring page to go back to
        if (isset($_POST['redirect_back']) && !empty($_POST['redirect_back'])) {
            $redirect_back = $_POST['redirect_back'];
            
            // If coming from shared-notes or view page, use that; otherwise use shared-notes
            if (strpos($redirect_back, 'shared-notes.php') !== false || 
                strpos($redirect_back, 'view.php') !== false ||
                strpos($redirect_back, 'dashboard.php') !== false) {
                
                // Add success message and timestamp to prevent caching
                $separator = strpos($redirect_back, '?') !== false ? '&' : '?';
                $redirect_url = $redirect_back . $separator . 'message=note_updated&t=' . time();
            }
        }
        
        // Add cache-busting headers to ensure changes reflect everywhere
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        header('Location: ' . $redirect_url);
        exit();
    } else {
        // Refresh note data if update failed
        $note = $notesController->getNote($note_id, $user_id);
    }
}

// Get any messages
$success_message = $ObjFncs->getMsg('success');
$error_messages = $ObjFncs->getMsg('errors');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note - <?php echo htmlspecialchars($conf['site_name']); ?></title>
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
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        .form-floating > label {
            color: #6c757d;
        }
        #content {
            min-height: 300px;
            resize: vertical;
        }
        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .note-info {
            background: rgba(102, 126, 234, 0.05);
            border-left: 4px solid #667eea;
            border-radius: 0 10px 10px 0;
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
                <a class="nav-link" href="../dashboard.php">
                    <i class="bi bi-journals me-1"></i>My Notes
                </a>
                <a class="nav-link" href="../favorites.php">
                    <i class="bi bi-heart me-1"></i>Favorites
                </a>
                <a class="nav-link" href="view.php?id=<?php echo $note['id']; ?>">
                    <i class="bi bi-eye me-1"></i>View Note
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Page Header -->
                <div class="card mb-4">
                    <div class="card-body text-center py-4">
                        <h1 class="display-6 fw-bold mb-2">
                            <i class="bi bi-pencil text-primary me-3"></i>Edit Note
                        </h1>
                        <p class="text-muted mb-0">Update your note content and settings</p>
                    </div>
                </div>

                <!-- Note Info -->
                <div class="alert note-info" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <div>
                            <strong>Note Information:</strong> Created on <?php echo date('M j, Y \a\t g:i A', strtotime($note['created_at'])); ?>
                            <?php if ($note['created_at'] !== $note['updated_at']): ?>
                                • Last updated <?php echo date('M j, Y \a\t g:i A', strtotime($note['updated_at'])); ?>
                            <?php endif; ?>
                            • <?php echo $note['view_count']; ?> views
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_messages): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_messages); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Note Form -->
                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" id="editNoteForm">
                            <!-- Hidden field to store referring page for redirect -->
                            <?php 
                            // Determine where to redirect after update
                            $referer = $_SERVER['HTTP_REFERER'] ?? '';
                            
                            // If no referer, coming from edit page, or coming from outside the app, default to shared-notes
                            if (empty($referer) || 
                                strpos($referer, 'edit.php') !== false || 
                                strpos($referer, 'localhost/notesharingapp') === false) {
                                $referer = '../shared-notes.php';
                            }
                            
                            // Also handle GET parameter for explicit redirect
                            if (isset($_GET['from']) && $_GET['from'] === 'shared') {
                                $referer = '../shared-notes.php';
                            } elseif (isset($_GET['from']) && $_GET['from'] === 'view') {
                                $referer = 'view.php?id=' . $note_id;
                            } elseif (isset($_GET['from']) && $_GET['from'] === 'dashboard') {
                                $referer = '../dashboard.php';
                            }
                            ?>
                            <input type="hidden" name="redirect_back" value="<?php echo htmlspecialchars($referer); ?>">>
                            
                            <!-- Title -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter note title" required maxlength="255" value="<?php echo htmlspecialchars($note['title']); ?>">
                                <label for="title">Note Title *</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="titleCounter"><?php echo strlen($note['title']); ?></span>/255 characters
                                </div>
                            </div>

                            <!-- Category and Status Row -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $note['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="category_id">Category</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="status" name="status">
                                            <option value="draft" <?php echo $note['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="published" <?php echo $note['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                            <option value="archived" <?php echo $note['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                        </select>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="content" name="content" placeholder="Write your note content here..." required style="height: 300px;"><?php echo htmlspecialchars($note['content']); ?></textarea>
                                <label for="content">Note Content *</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="contentCounter"><?php echo strlen($note['content']); ?></span> characters
                                </div>
                            </div>

                            <!-- Summary -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="summary" name="summary" placeholder="Brief summary of your note" maxlength="500" style="height: 100px;"><?php echo htmlspecialchars($note['summary']); ?></textarea>
                                <label for="summary">Summary</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="summaryCounter"><?php echo strlen($note['summary']); ?></span>/500 characters
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags separated by commas" value="<?php echo htmlspecialchars($note['tags']); ?>">
                                <label for="tags">Tags (comma-separated)</label>
                                <div class="form-text">e.g., math, calculus, derivatives, limits</div>
                            </div>

                            <!-- Privacy Settings -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" <?php echo $note['is_public'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_public">
                                            <i class="bi bi-globe me-1"></i>Make Note Public
                                        </label>
                                        <div class="form-text">Public notes can be viewed by other users</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">
                                        <strong>Current Status:</strong>
                                        <span class="badge bg-<?php echo $note['status'] === 'published' ? 'success' : ($note['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($note['status']); ?>
                                        </span>
                                        <?php if ($note['is_public']): ?>
                                            <span class="badge bg-info ms-1">Public</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1">Private</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 justify-content-between">
                                <div>
                                    <a href="view.php?id=<?php echo $note['id']; ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Preview Note
                                    </a>
                                </div>
                                <div>
                                    <a href="../dashboard.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                    </a>
                                    <button type="submit" name="update_note" class="btn btn-primary" id="submitBtn">
                                        <i class="bi bi-check-circle me-1"></i>Update Note
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counters
        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        const summaryInput = document.getElementById('summary');
        const titleCounter = document.getElementById('titleCounter');
        const contentCounter = document.getElementById('contentCounter');
        const summaryCounter = document.getElementById('summaryCounter');

        function updateCounter(input, counter, max = null) {
            const length = input.value.length;
            counter.textContent = length;
            
            if (max && length > max * 0.9) {
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-warning');
            }
            
            if (max && length >= max) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        }

        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter, 255));
        contentInput.addEventListener('input', () => updateCounter(contentInput, contentCounter));
        summaryInput.addEventListener('input', () => updateCounter(summaryInput, summaryCounter, 500));

        // Initialize counters
        updateCounter(titleInput, titleCounter, 255);
        updateCounter(contentInput, contentCounter);
        updateCounter(summaryInput, summaryCounter, 500);

        // Form validation
        document.getElementById('editNoteForm').addEventListener('submit', function(e) {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                alert('Please fill in both title and content fields.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
                submitBtn.disabled = true;
            }
            
            // Let the form submit naturally
            return true;
        });

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        function autoSave() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Could implement auto-save here
                console.log('Auto-save triggered');
            }, 30000); // Auto-save every 30 seconds
        }

        titleInput.addEventListener('input', autoSave);
        contentInput.addEventListener('input', autoSave);
        summaryInput.addEventListener('input', autoSave);

        // Warn about unsaved changes
        let hasUnsavedChanges = false;
        
        document.querySelectorAll('input, textarea, select').forEach(element => {
            element.addEventListener('input', () => {
                hasUnsavedChanges = true;
            });
        });

        document.getElementById('editNoteForm').addEventListener('submit', () => {
            hasUnsavedChanges = false;
        });

        window.addEventListener('beforeunload', (e) => {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    </script>
</body>
</html>
