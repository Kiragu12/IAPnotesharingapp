<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/signin.php');
    exit();
}

// Simple error checking
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/conf.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';

$ObjFncs = new fncs();
$db = new Database($conf);

// Get user info
$user_name = $_SESSION['user_name'] ?? 'User';
$user_id = $_SESSION['user_id'];

// Get categories safely
try {
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_type = $_POST['note_type'] ?? 'text';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (empty($title)) {
        $error_message = "Please provide a title for your note.";
    } else {
        try {
            require_once '../../app/Controllers/NotesController.php';
            $notesController = new NotesController();
            
            if ($note_type === 'file' && isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] === UPLOAD_ERR_OK) {
                // File upload logic
                $file = $_FILES['uploaded_file'];
                $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_extensions) && $file['size'] <= 10*1024*1024) {
                    $upload_dir = '../../uploads/documents/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $unique_name;
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        $data = [
                            'user_id' => $user_id,
                            'title' => $title,
                            'content' => $description ?: 'File upload: ' . $file['name'],
                            'note_type' => 'file',
                            'file_path' => 'uploads/documents/' . $unique_name,
                            'file_name' => $file['name'],
                            'file_type' => $file['type'],
                            'file_size' => $file['size'],
                            'is_public' => 1, // Always make notes public
                            'status' => 'published'
                        ];
                        
                        $note_id = $notesController->createNote($data);
                        if ($note_id) {
                            // Set success message and redirect to My Notes page
                            $ObjFncs->setMsg('success', 'File uploaded successfully! Your note has been created.', 'success');
                            header('Location: ../dashboard.php?message=note_created');
                            exit();
                        } else {
                            $error_message = "Failed to save note to database.";
                        }
                    } else {
                        $error_message = "Failed to upload file.";
                    }
                } else {
                    $error_message = "Invalid file type or file too large.";
                }
            } elseif ($note_type === 'text' && !empty($content)) {
                // Text note logic
                $data = [
                    'user_id' => $user_id,
                    'title' => $title,
                    'content' => $content,
                    'note_type' => 'text',
                    'is_public' => 1, // Always make notes public
                    'status' => 'published'
                ];
                
                $note_id = $notesController->createNote($data);
                if ($note_id) {
                    // Set success message and redirect to My Notes page
                    $ObjFncs->setMsg('success', 'Text note created successfully! Your note has been added.', 'success');
                    header('Location: ../dashboard.php?message=note_created');
                    exit();
                } else {
                    $error_message = "Failed to save note to database.";
                }
            } else {
                $error_message = "Please provide content for your note.";
            }
            
        } catch (Exception $e) {
            error_log("Note creation error: " . $e->getMessage());
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Note - NotesShare Academy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 20px 30px;
        }
        
        .note-type-selector {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .note-type-selector:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        
        .note-type-selector.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #e7f1ff 100%);
        }
        
        .file-drop-zone {
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .file-drop-zone:hover,
        .file-drop-zone.dragover {
            border-color: #667eea;
            background: #f0f8ff;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 500;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'my-notes');">
                <i class="bi bi-journal-text me-2"></i>NotesShare Academy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php" onclick="sessionStorage.setItem('selectedNavPage', 'my-notes');">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-plus-circle me-1"></i>Create Note
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../shared-notes.php">
                            <i class="bi bi-share me-1"></i>Shared Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-folder me-1"></i>My Notes
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
                    <a class="nav-link" href="../auth/settings.php">
                        <i class="bi bi-gear me-1"></i>Settings
                    </a>
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Success/Error Messages -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Main Card -->
                <div class="card main-card">
                    <div class="card-header text-white">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-plus-circle-fill me-2"></i>Create New Note
                        </h3>
                        <p class="mb-0 opacity-75">Share your ideas with text or upload files</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" id="createNoteForm">
                            
                            <!-- Note Type Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold fs-5 mb-3">Choose Note Type:</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="note-type-selector active" data-type="text" onclick="selectNoteType('text')">
                                            <div class="text-center">
                                                <i class="bi bi-pencil-square display-4 text-primary mb-2"></i>
                                                <h5 class="fw-bold">Text Note</h5>
                                                <p class="text-muted mb-0">Write and format your own content</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="note-type-selector" data-type="file" onclick="selectNoteType('file')">
                                            <div class="text-center">
                                                <i class="bi bi-cloud-upload display-4 text-success mb-2"></i>
                                                <h5 class="fw-bold">File Upload</h5>
                                                <p class="text-muted mb-0">Upload documents, PDFs, or images</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="note_type" id="note_type" value="text">
                            </div>

                            <!-- Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">
                                    <i class="bi bi-card-heading me-1"></i>Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                       placeholder="Enter a descriptive title for your note" required>
                            </div>

                            <!-- Text Note Content -->
                            <div id="text-content-section">
                                <div class="mb-3">
                                    <label for="content" class="form-label fw-bold">
                                        <i class="bi bi-file-text me-1"></i>Content <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="content" name="content" rows="8" 
                                              placeholder="Write your note content here..."></textarea>
                                </div>
                            </div>

                            <!-- File Upload Section -->
                            <div id="file-content-section" style="display: none;">
                                <div class="mb-3">
                                    <label for="uploaded_file" class="form-label fw-bold">
                                        <i class="bi bi-cloud-upload me-1"></i>Upload File
                                    </label>
                                    <div class="file-drop-zone" onclick="document.getElementById('uploaded_file').click();">
                                        <i class="bi bi-cloud-upload display-1 text-muted mb-3"></i>
                                        <h5>Click to upload or drag and drop</h5>
                                        <p class="text-muted">Supports: PDF, DOC, DOCX, Images (JPG, PNG, GIF)</p>
                                        <p class="text-muted">Maximum size: 10MB</p>
                                    </div>
                                    <input type="file" class="form-control d-none" id="uploaded_file" name="uploaded_file" 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">
                                        <i class="bi bi-chat-text me-1"></i>Description
                                    </label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              placeholder="Add a description for your uploaded file..."></textarea>
                                </div>
                            </div>

                            <!-- Options -->


                            <!-- Submit Buttons -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-gradient flex-fill">
                                    <i class="bi bi-check-circle-fill me-2"></i>Create Note
                                </button>
                                <a href="../dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectNoteType(type) {
            // Update UI
            document.querySelectorAll('.note-type-selector').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelector(`[data-type="${type}"]`).classList.add('active');
            
            // Update hidden input
            document.getElementById('note_type').value = type;
            
            // Show/hide sections
            if (type === 'text') {
                document.getElementById('text-content-section').style.display = 'block';
                document.getElementById('file-content-section').style.display = 'none';
                document.getElementById('content').required = true;
                document.getElementById('uploaded_file').required = false;
            } else {
                document.getElementById('text-content-section').style.display = 'none';
                document.getElementById('file-content-section').style.display = 'block';
                document.getElementById('content').required = false;
                document.getElementById('uploaded_file').required = true;
            }
        }
        
        // File upload preview
        document.getElementById('uploaded_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const dropZone = document.querySelector('.file-drop-zone');
                dropZone.innerHTML = `
                    <i class="bi bi-file-earmark-check display-1 text-success mb-3"></i>
                    <h5>File Selected: ${file.name}</h5>
                    <p class="text-muted">Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    <p class="text-success">Click to change file</p>
                `;
            }
        });
        
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
