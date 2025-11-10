<?php
/**
 * Create New Note Page
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

require_once '../../config/conf.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';

$ObjFncs = new fncs();
$db = new Database($conf);

// Get categories for dropdown
try {
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
} catch (Exception $e) {
    $categories = [];
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get user info
$user_name = $_SESSION['full_name'] ?? 'User';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_note'])) {
    require_once '../../app/Controllers/NotesController.php';
    $notesController = new NotesController();
    
    $data = [
        'user_id' => $_SESSION['user_id'],
        'title' => $_POST['title'] ?? '',
        'content' => $_POST['content'] ?? '',
        'summary' => $_POST['summary'] ?? '',
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'tags' => $_POST['tags'] ?? '',
        'is_public' => isset($_POST['is_public']),
        'status' => $_POST['status'] ?? 'draft'
    ];
    
    $note_id = $notesController->createNote($data);
    if ($note_id) {
        // Redirect to view the created note
        header("Location: view.php?id=$note_id");
        exit();
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
    <title>Create New Note - <?php echo htmlspecialchars($conf['site_name']); ?></title>
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
        .category-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
        }
        #content {
            min-height: 300px;
            resize: vertical;
        }
        .char-counter {
            font-size: 0.875rem;
            color: #6c757d;
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
                <a class="nav-link" href="my-notes.php">
                    <i class="bi bi-journals me-1"></i>My Notes
                </a>
                <a class="nav-link" href="../auth/dashboard.php">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
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
                            <i class="bi bi-plus-circle text-primary me-3"></i>Create New Note
                        </h1>
                        <p class="text-muted mb-0">Share your knowledge with the community</p>
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

                <!-- Create Note Form -->
                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" id="createNoteForm">
                            <!-- Title -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="title" name="title" placeholder="Enter note title" required maxlength="255">
                                <label for="title">Note Title *</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="titleCounter">0</span>/255 characters
                                </div>
                            </div>

                            <!-- Category and Status Row -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
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
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                        </select>
                                        <label for="status">Status</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="content" name="content" placeholder="Write your note content here..." required style="height: 300px;"></textarea>
                                <label for="content">Note Content *</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="contentCounter">0</span> characters
                                </div>
                            </div>

                            <!-- Summary (Optional) -->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="summary" name="summary" placeholder="Brief summary of your note (optional)" maxlength="500" style="height: 100px;"></textarea>
                                <label for="summary">Summary (Optional)</label>
                                <div class="char-counter text-end mt-1">
                                    <span id="summaryCounter">0</span>/500 characters
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags separated by commas">
                                <label for="tags">Tags (comma-separated)</label>
                                <div class="form-text">e.g., math, calculus, derivatives, limits</div>
                            </div>

                            <!-- Options -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public">
                                        <label class="form-check-label" for="is_public">
                                            <i class="bi bi-globe me-1"></i>Make this note public
                                        </label>
                                        <div class="form-text">Public notes can be viewed by all users</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="../auth/dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                </a>
                                <button type="submit" name="create_note" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-check-circle me-1"></i>Create Note
                                </button>
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
        }

        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter, 255));
        contentInput.addEventListener('input', () => updateCounter(contentInput, contentCounter));
        summaryInput.addEventListener('input', () => updateCounter(summaryInput, summaryCounter, 500));

        // Form validation
        document.getElementById('createNoteForm').addEventListener('submit', function(e) {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();
            
            if (!title || !content) {
                e.preventDefault();
                alert('Please fill in both title and content fields.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
            submitBtn.disabled = true;
        });

        // Auto-generate summary if not provided
        contentInput.addEventListener('blur', function() {
            const summaryInput = document.getElementById('summary');
            if (!summaryInput.value.trim() && this.value.trim()) {
                const content = this.value.trim();
                const summary = content.substring(0, 150) + (content.length > 150 ? '...' : '');
                summaryInput.value = summary;
                updateCounter(summaryInput, summaryCounter, 500);
            }
        });
    </script>
</body>
</html>
