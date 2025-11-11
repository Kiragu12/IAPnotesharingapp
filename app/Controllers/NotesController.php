<?php
/**
 * Notes Controller
 * Handles all note-related operations (CRUD)
 */

class NotesController {
    private $db;
    private $ObjFncs;
    
    public function __construct() {
        // Avoid starting session if already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get the project root directory
        $project_root = dirname(dirname(__DIR__));
        
        // Include required files using project root
        require_once $project_root . '/config/conf.php';
        require_once $project_root . '/app/Services/Global/Database.php';
        require_once $project_root . '/app/Services/Global/fncs.php';
        
        global $conf;
        $this->db = new Database($conf);
        $this->ObjFncs = new fncs();
    }
    
    /**
     * Create a new note
     */
    public function createNote($data) {
        error_log("DEBUG: NotesController::createNote called with data: " . print_r($data, true));
        
        try {
            error_log("DEBUG: Starting note creation validation...");
            
            // Validate required fields
            if (empty($data['title']) || empty($data['content']) || empty($data['user_id'])) {
                throw new Exception('Title, content, and user ID are required');
            }
            
            error_log("DEBUG: Validation passed, sanitizing data...");
            
            // Sanitize input
            $title = trim($data['title']);
            $content = trim($data['content']);
            $summary = !empty($data['summary']) ? trim($data['summary']) : $this->generateSummary($content);
            $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
            $tags = !empty($data['tags']) ? trim($data['tags']) : '';
            $is_public = isset($data['is_public']) && $data['is_public'] ? 1 : 0; // Convert to integer for MySQL
            $status = !empty($data['status']) ? $data['status'] : 'draft';
            
            // Handle file upload fields
            $note_type = !empty($data['note_type']) ? $data['note_type'] : 'text';
            $file_path = !empty($data['file_path']) ? $data['file_path'] : null;
            $file_name = !empty($data['file_name']) ? $data['file_name'] : null;
            $file_type = !empty($data['file_type']) ? $data['file_type'] : null;
            $file_size = !empty($data['file_size']) ? (int)$data['file_size'] : null;
            
            error_log("DEBUG: Data sanitized. Note type: $note_type. Preparing SQL query...");
            
            // Insert note into database with file support
            $sql = "INSERT INTO notes (user_id, category_id, title, content, summary, tags, is_public, status, note_type, file_path, file_name, file_type, file_size, created_at, updated_at) 
                    VALUES (:user_id, :category_id, :title, :content, :summary, :tags, :is_public, :status, :note_type, :file_path, :file_name, :file_type, :file_size, NOW(), NOW())";
            
            $params = [
                ':user_id' => $data['user_id'],
                ':category_id' => $category_id,
                ':title' => $title,
                ':content' => $content,
                ':summary' => $summary,
                ':tags' => $tags,
                ':is_public' => $is_public,
                ':status' => $status,
                ':note_type' => $note_type,
                ':file_path' => $file_path,
                ':file_name' => $file_name,
                ':file_type' => $file_type,
                ':file_size' => $file_size
            ];
            
            error_log("DEBUG: Executing SQL with params: " . print_r($params, true));
            
            $result = $this->db->query($sql, $params);
            
            if ($result) {
                $note_id = $this->db->getPDO()->lastInsertId();
                error_log("DEBUG: Note created successfully with ID: " . $note_id);
                $this->ObjFncs->setMsg('success', 'Note created successfully!', 'success');
                return $note_id;
            } else {
                error_log("DEBUG: Database execute returned false");
                throw new Exception('Failed to create note');
            }
            
        } catch (Exception $e) {
            error_log("Note Creation Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage(), 'danger');
            return false;
        }
    }
    
    /**
     * Get user's notes
     */
    public function getUserNotes($user_id, $status = null, $limit = null) {
        try {
            $sql = "SELECT n.*, c.name as category_name 
                    FROM notes n 
                    LEFT JOIN categories c ON n.category_id = c.id 
                    WHERE n.user_id = :user_id";
            
            $params = [':user_id' => $user_id];
            
            if ($status) {
                $sql .= " AND n.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY n.updated_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$limit;
            }
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get User Notes Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search notes (title, content, tags)
     * Returns public notes and the user's own notes
     */
    public function searchNotes($user_id, $query = '', $limit = 20) {
        try {
            // Search across title, content, tags, author name and category name
                $sql = "SELECT n.id, n.title, n.summary, c.name as category_name, u.full_name as author_name, n.updated_at
                        FROM notes n
                        LEFT JOIN categories c ON n.category_id = c.id
                        LEFT JOIN users u ON n.user_id = u.id
                        WHERE (n.is_public = 1 OR n.user_id = :user_id)";

            $params = [':user_id' => $user_id];

            if (!empty($query)) {
                $sql .= " AND (n.title LIKE :q OR n.content LIKE :q OR n.tags LIKE :q OR u.full_name LIKE :q OR c.name LIKE :q)";
                $params[':q'] = '%' . $query . '%';
            }

            $sql .= " ORDER BY n.updated_at DESC LIMIT " . (int)$limit;

            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Search Notes Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single note by ID
     */
    public function getNote($note_id, $user_id = null) {
        try {
            $sql = "SELECT n.*, c.name as category_name, u.full_name as author_name 
                    FROM notes n 
                    LEFT JOIN categories c ON n.category_id = c.id 
                    LEFT JOIN users u ON n.user_id = u.id 
                    WHERE n.id = :note_id";
            
            $params = [':note_id' => $note_id];
            
            // If user_id is provided, check ownership or public access
            if ($user_id) {
                $sql .= " AND (n.user_id = :user_id OR n.is_public = 1)";
                $params[':user_id'] = $user_id;
            } else {
                $sql .= " AND n.is_public = 1";
            }
            
            $note = $this->db->fetchOne($sql, $params);
            
            // Increment view count if note exists
            if ($note) {
                $this->incrementViewCount($note_id);
            }
            
            return $note;
            
        } catch (Exception $e) {
            error_log("Get Note Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update an existing note
     */
    public function updateNote($note_id, $data, $user_id) {
        try {
            // Check if user owns the note
            $existing_note = $this->db->fetchOne(
                "SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id",
                [':note_id' => $note_id, ':user_id' => $user_id]
            );
            
            if (!$existing_note) {
                throw new Exception('Note not found or you do not have permission to edit it');
            }
            
            // Prepare update data
            $updateFields = [];
            $params = [':note_id' => $note_id, ':user_id' => $user_id];
            
            if (!empty($data['title'])) {
                $updateFields[] = "title = :title";
                $params[':title'] = trim($data['title']);
            }
            
            if (!empty($data['content'])) {
                $updateFields[] = "content = :content";
                $params[':content'] = trim($data['content']);
                
                // Auto-generate summary if not provided
                if (empty($data['summary'])) {
                    $updateFields[] = "summary = :summary";
                    $params[':summary'] = $this->generateSummary($data['content']);
                }
            }
            
            if (isset($data['summary']) && !empty($data['summary'])) {
                $updateFields[] = "summary = :summary";
                $params[':summary'] = trim($data['summary']);
            }
            
            if (isset($data['category_id'])) {
                $updateFields[] = "category_id = :category_id";
                $params[':category_id'] = $data['category_id'] ? (int)$data['category_id'] : null;
            }
            
            if (isset($data['tags'])) {
                $updateFields[] = "tags = :tags";
                $params[':tags'] = trim($data['tags']);
            }
            
            if (isset($data['is_public'])) {
                $updateFields[] = "is_public = :is_public";
                $params[':is_public'] = (bool)$data['is_public'];
            }
            
            if (isset($data['status'])) {
                $updateFields[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            $updateFields[] = "updated_at = NOW()";
            
            if (empty($updateFields)) {
                throw new Exception('No fields to update');
            }
            
            $sql = "UPDATE notes SET " . implode(', ', $updateFields) . " WHERE id = :note_id AND user_id = :user_id";
            
            $result = $this->db->execute($sql, $params);
            
            if ($result) {
                $this->ObjFncs->setMsg('success', 'Note updated successfully!', 'success');
                return true;
            } else {
                throw new Exception('Failed to update note');
            }
            
        } catch (Exception $e) {
            error_log("Update Note Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage(), 'danger');
            return false;
        }
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($note_id, $user_id) {
        try {
            // Get note details including file information
            $note = $this->db->fetchOne(
                "SELECT id, note_type, file_path FROM notes WHERE id = :note_id AND user_id = :user_id",
                [':note_id' => $note_id, ':user_id' => $user_id]
            );
            
            if (!$note) {
                throw new Exception('Note not found or you do not have permission to delete it');
            }
            
            // Delete the database record first
            $sql = "DELETE FROM notes WHERE id = :note_id AND user_id = :user_id";
            $result = $this->db->execute($sql, [':note_id' => $note_id, ':user_id' => $user_id]);
            
            if (!$result) {
                throw new Exception('Failed to delete note from database');
            }
            
            // If it's a file note, delete the physical file
            if ($note['note_type'] === 'file' && !empty($note['file_path'])) {
                $this->deletePhysicalFile($note['file_path']);
            }
            
            $this->ObjFncs->setMsg('success', 'Note and associated files deleted successfully!', 'success');
            return true;
            
        } catch (Exception $e) {
            error_log("Delete Note Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage(), 'danger');
            return false;
        }
    }
    
    /**
     * Delete physical file from server
     */
    private function deletePhysicalFile($file_path) {
        try {
            // Construct the full file path
            $full_path = __DIR__ . '/../../' . $file_path;
            
            // Security check - ensure file is within uploads directory
            $uploads_dir = realpath(__DIR__ . '/../../uploads/');
            $file_real_path = realpath($full_path);
            
            if ($file_real_path && strpos($file_real_path, $uploads_dir) === 0) {
                if (file_exists($full_path)) {
                    if (unlink($full_path)) {
                        error_log("Successfully deleted file: " . $file_path);
                    } else {
                        error_log("Failed to delete file: " . $file_path);
                    }
                } else {
                    error_log("File not found for deletion: " . $file_path);
                }
            } else {
                error_log("Security violation: Attempted to delete file outside uploads directory: " . $file_path);
            }
        } catch (Exception $e) {
            error_log("Error deleting physical file: " . $e->getMessage());
        }
    }
    
    /**
     * Get available categories
     */
    public function getCategories() {
        try {
            $sql = "SELECT * FROM categories ORDER BY name ASC";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            error_log("Get Categories Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Increment view count for a note
     */
    private function incrementViewCount($note_id) {
        try {
            $sql = "UPDATE notes SET view_count = view_count + 1 WHERE id = :note_id";
            $this->db->execute($sql, [':note_id' => $note_id]);
        } catch (Exception $e) {
            error_log("Increment View Count Error: " . $e->getMessage());
        }
    }
    
    /**
     * Generate summary from content
     */
    private function generateSummary($content, $length = 150) {
        $summary = strip_tags($content);
        if (strlen($summary) > $length) {
            $summary = substr($summary, 0, $length) . '...';
        }
        return $summary;
    }
}

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure config is available
    global $conf;

    $action = $_POST['action'];

    // Allow dev-only unauthenticated search when explicitly enabled in config
    $devAllow = isset($conf['dev_allow_unauth_search']) && $conf['dev_allow_unauth_search'];

    // If user not logged in and action is not allowed by dev flag, block
    if (!isset($_SESSION['user_id']) && !($devAllow && $action === 'search')) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $notesController = new NotesController();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    switch ($action) {
        case 'create':
            $data = [
                'user_id' => $user_id,
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'summary' => $_POST['summary'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'tags' => $_POST['tags'] ?? '',
                'is_public' => isset($_POST['is_public']),
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            $note_id = $notesController->createNote($data);
            if ($note_id) {
                echo json_encode(['success' => true, 'note_id' => $note_id, 'message' => 'Note created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create note']);
            }
            break;
            
        case 'update':
            $note_id = $_POST['note_id'] ?? 0;
            $data = [
                'title' => $_POST['title'] ?? '',
                'content' => $_POST['content'] ?? '',
                'summary' => $_POST['summary'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'tags' => $_POST['tags'] ?? '',
                'is_public' => isset($_POST['is_public']),
                'status' => $_POST['status'] ?? 'draft'
            ];
            
            $result = $notesController->updateNote($note_id, $data, $user_id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Note updated successfully!' : 'Failed to update note']);
            break;
            
        case 'delete':
            $note_id = $_POST['note_id'] ?? 0;
            $result = $notesController->deleteNote($note_id, $user_id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Note deleted successfully!' : 'Failed to delete note']);
            break;

        case 'search':
            // Search notes (title, content, tags, author, category) - returns user's notes + public notes
                header('Content-Type: application/json');
                $q = isset($_POST['q']) ? trim($_POST['q']) : '';
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 20;
                $limit = max(1, min(50, $limit));

                // Enforce min query length server-side
                if ($q !== '' && mb_strlen($q) < 2) {
                    echo json_encode(['success' => false, 'message' => 'Query too short', 'results' => []]);
                    exit;
                }

                $results = $notesController->searchNotes($user_id, $q, $limit);
                echo json_encode(['success' => true, 'results' => $results]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
