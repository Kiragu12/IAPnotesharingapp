<?php
/**
 * Individual Note Viewing Page
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

// Get note ID from URL
$note_id = $_GET['id'] ?? null;
if (!$note_id || !is_numeric($note_id)) {
    header('Location: ../shared-notes.php');
    exit();
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// Get note details
try {
    $sql = "SELECT notes.*, users.full_name, users.email
            FROM notes 
            LEFT JOIN users ON notes.user_id = users.id 
            WHERE notes.id = :note_id AND (notes.is_public = 1 OR notes.user_id = :user_id)";
    
    $note = $db->fetchOne($sql, [':note_id' => $note_id, ':user_id' => $user_id]);
    
    if (!$note) {
        header('Location: ../shared-notes.php?error=note_not_found');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Error fetching note: " . $e->getMessage());
    header('Location: ../shared-notes.php?error=database_error');
    exit();
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
    <title><?php echo htmlspecialchars($note['title']); ?> - NotesShare Academy</title>
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
        
        .main-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            overflow: hidden;
            border: none;
        }
        
        .note-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            padding: 40px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .note-type-badge {
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 12px;
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
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }
        
        .file-preview {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin: 30px 0;
        }
        
        .file-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            display: block;
        }
        
        .pdf-icon { color: #dc3545; }
        .doc-icon { color: #0d6efd; }
        .img-icon { color: #198754; }
        .xlsx-icon { color: #fd7e14; }
        .ppt-icon { color: #e83e8c; }
        .default-icon { color: #6c757d; }
        
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
        
        .download-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(17, 153, 142, 0.4);
            color: white;
        }
        
        .content-section {
            padding: 40px;
            line-height: 1.8;
        }
        
        .back-btn {
            background: #6c757d;
            border: none;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                <i class="bi bi-journal-text me-2"></i>NotesShare Academy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">
                            <i class="bi bi-house-door me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../shared-notes.php">
                            <i class="bi bi-share me-1"></i>Shared Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php">
                            <i class="bi bi-plus-circle me-1"></i>Create Note
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-notes.php">
                            <i class="bi bi-folder me-1"></i>My Notes
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($user_name); ?>!</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="../shared-notes.php" class="back-btn">
                        <i class="bi bi-arrow-left me-2"></i>Back to Shared Notes
                    </a>
                </div>
                
                <!-- Main Note Card -->
                <div class="main-card">
                    
                    <!-- Note Header -->
                    <div class="note-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="note-type-badge note-type-<?php echo $note['note_type'] ?? 'text'; ?> me-3">
                                        <?php if (($note['note_type'] ?? 'text') === 'file'): ?>
                                            <i class="bi bi-file-earmark me-1"></i>File
                                        <?php else: ?>
                                            <i class="bi bi-file-text me-1"></i>Text
                                        <?php endif; ?>
                                    </span>
                                    <?php if ($note['is_public']): ?>
                                        <span class="badge bg-success">Public</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Private</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h1 class="display-6 fw-bold text-dark mb-3">
                                    <?php echo htmlspecialchars($note['title']); ?>
                                </h1>
                                
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">
                                        <?php echo strtoupper(substr($note['full_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($note['full_name']); ?></h6>
                                        <p class="text-muted mb-0">
                                            Created on <?php echo date('F j, Y \a\t g:i A', strtotime($note['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-end">
                                <?php if (($note['note_type'] ?? 'text') === 'file' && !empty($note['file_path'])): ?>
                                    <a href="../../<?php echo htmlspecialchars($note['file_path']); ?>" 
                                       class="btn download-btn mb-2 d-block"
                                       target="_blank"
                                       download="<?php echo htmlspecialchars($note['file_name'] ?? 'download'); ?>">
                                        <i class="bi bi-download me-2"></i>Download File
                                    </a>
                                    <small class="text-muted">
                                        <?php echo !empty($note['file_size']) ? number_format($note['file_size'] / 1024, 1) . ' KB' : 'File size unknown'; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- File Preview Section (for files) -->
                    <?php if (($note['note_type'] ?? 'text') === 'file'): ?>
                        <div class="file-preview">
                            <?php
                            $file_name = $note['file_name'] ?? 'unknown_file';
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
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
                            <h4 class="fw-bold"><?php echo htmlspecialchars($file_name); ?></h4>
                            <p class="text-muted">Click the download button above to view or save this file</p>
                            
                            <!-- Image Preview for image files -->
                            <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']) && !empty($note['file_path']) && file_exists('../../' . $note['file_path'])): ?>
                                <div class="mt-4">
                                    <img src="../../<?php echo htmlspecialchars($note['file_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($file_name); ?>"
                                         class="img-fluid rounded shadow"
                                         style="max-height: 400px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Content Section -->
                    <div class="content-section">
                        <?php if (($note['note_type'] ?? 'text') === 'text' || !empty($note['content'])): ?>
                            <h5 class="fw-bold mb-3">
                                <?php echo (($note['note_type'] ?? 'text') === 'file') ? 'Description' : 'Content'; ?>
                            </h5>
                            <div class="fs-6 text-dark">
                                <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($note['tags'])): ?>
                            <div class="mt-4">
                                <h6 class="fw-bold text-muted mb-2">Tags:</h6>
                                <div>
                                    <?php 
                                    $tags = explode(',', $note['tags']);
                                    foreach ($tags as $tag): 
                                        $tag = trim($tag);
                                        if (!empty($tag)):
                                    ?>
                                        <span class="badge bg-light text-dark me-2 mb-2 px-3 py-2">
                                            #<?php echo htmlspecialchars($tag); ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="../shared-notes.php" class="btn btn-gradient me-3">
                        <i class="bi bi-arrow-left me-2"></i>Back to Gallery
                    </a>
                    
                    <?php if ($note['user_id'] == $user_id): ?>
                        <a href="edit.php?id=<?php echo $note['id']; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Note
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
