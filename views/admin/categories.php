<?php
// Admin Categories Management
require_once __DIR__ . '/../../app/Middleware/AdminMiddleware.php';
require_once __DIR__ . '/../../Global/Database.php';
require_once __DIR__ . '/../../app/Controllers/AdminController.php';

$adminController = new AdminController();
$db = new Database();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_category':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if (!empty($name)) {
                    $db->query("INSERT INTO categories (name, description, created_at) VALUES (:name, :desc, NOW())");
                    $db->bind(':name', $name);
                    $db->bind(':desc', $description);
                    $db->execute();
                    $adminController->logAdminActivity($_SESSION['user_id'], 'category_created', "Created category: $name");
                    header('Location: categories.php?msg=created');
                    exit;
                }
                break;
                
            case 'edit_category':
                $cat_id = intval($_POST['cat_id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if ($cat_id && !empty($name)) {
                    $db->query("UPDATE categories SET name = :name, description = :desc WHERE id = :id");
                    $db->bind(':name', $name);
                    $db->bind(':desc', $description);
                    $db->bind(':id', $cat_id);
                    $db->execute();
                    $adminController->logAdminActivity($_SESSION['user_id'], 'category_updated', "Updated category #$cat_id: $name");
                    header('Location: categories.php?msg=updated');
                    exit;
                }
                break;
                
            case 'delete_category':
                $cat_id = intval($_POST['cat_id'] ?? 0);
                if ($cat_id) {
                    // Get category name for logging
                    $db->query("SELECT name FROM categories WHERE id = :id");
                    $db->bind(':id', $cat_id);
                    $cat = $db->single();
                    
                    // Delete category (notes will be set to NULL due to foreign key)
                    $db->query("DELETE FROM categories WHERE id = :id");
                    $db->bind(':id', $cat_id);
                    $db->execute();
                    $adminController->logAdminActivity($_SESSION['user_id'], 'category_deleted', "Deleted category: " . $cat['name']);
                    header('Location: categories.php?msg=deleted');
                    exit;
                }
                break;
        }
    }
}

// Get all categories with note counts
$db->query("
    SELECT c.*, COUNT(n.id) as note_count
    FROM categories c
    LEFT JOIN notes n ON c.id = n.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $db->resultSet();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 1rem 1.5rem;
            margin: 0.25rem 0;
            border-radius: 10px;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .category-card {
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        .category-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 px-0 admin-sidebar">
                <div class="p-4">
                    <h4 class="text-white mb-4"><i class="bi bi-shield-lock-fill me-2"></i>Admin Panel</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="users.php"><i class="bi bi-people-fill me-2"></i>Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="notes.php"><i class="bi bi-journal-text me-2"></i>Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="flagged.php"><i class="bi bi-flag-fill me-2"></i>Flagged Content</a></li>
                        <li class="nav-item"><a class="nav-link" href="analytics.php"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li class="nav-item"><a class="nav-link active" href="categories.php"><i class="bi bi-tags-fill me-2"></i>Categories</a></li>
                        <li class="nav-item"><a class="nav-link" href="activity.php"><i class="bi bi-clock-history me-2"></i>Activity Log</a></li>
                        <li class="nav-item mt-4"><a class="nav-link" href="../dashboard.php"><i class="bi bi-arrow-left-circle me-2"></i>Back to Site</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><i class="bi bi-tags-fill me-2"></i>Categories Management</h2>
                            <p class="text-muted mb-0">Manage note categories and organization</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="bi bi-plus-lg me-2"></i>Add Category
                        </button>
                    </div>
                </div>
                
                <?php if (isset($_GET['msg'])): ?>
                    <?php
                    $messages = [
                        'created' => ['success', 'Category has been created successfully.'],
                        'updated' => ['success', 'Category has been updated successfully.'],
                        'deleted' => ['success', 'Category has been deleted successfully.']
                    ];
                    $msgData = $messages[$_GET['msg']] ?? ['info', 'Action completed.'];
                    ?>
                    <div class="alert alert-<?php echo $msgData[0]; ?> alert-dismissible fade show">
                        <?php echo $msgData[1]; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Categories Grid -->
                <div class="row g-4">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <div class="col-md-4">
                                <div class="card category-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">
                                                <i class="bi bi-tag-fill text-primary me-2"></i>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h5>
                                            <span class="badge bg-primary"><?php echo $category['note_count']; ?> notes</span>
                                        </div>
                                        
                                        <?php if (!empty($category['description'])): ?>
                                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['description'], ENT_QUOTES); ?>')">
                                                <i class="bi bi-pencil me-1"></i>Edit
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category? Notes will be uncategorized.')">
                                                <input type="hidden" name="action" value="delete_category">
                                                <input type="hidden" name="cat_id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-tags display-1 text-muted"></i>
                                    <h4 class="mt-3">No Categories</h4>
                                    <p class="text-muted">Create your first category to organize notes.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                        <i class="bi bi-plus-lg me-2"></i>Add Category
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_category">
                        <input type="hidden" name="cat_id" id="edit_cat_id">
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(id, name, description) {
            document.getElementById('edit_cat_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    </script>
</body>
</html>
