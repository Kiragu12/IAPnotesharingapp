<?php
/**
 * Notes Controller
 * Handles all note-related operations (CRUD)
 */

session_start();
require_once '../../config/conf.php';
require_once '../../app/Services/Global/Database.php';
require_once '../../app/Services/Global/fncs.php';

class NotesController {
    private $db;
    private $ObjFncs;
    
    public function __construct() {
        global $conf;
        $this->db = new Database($conf);
        $this->ObjFncs = new fncs();
    }
    
    /**
     * Create a new note
     */
    public function createNote($data) {
        try {
            // Validate required fields
            if (empty($data['title']) || empty($data['content']) || empty($data['user_id'])) {
                throw new Exception('Title, content, and user ID are required');
            }
            
            // Sanitize input
            $title = trim($data['title']);
            $content = trim($data['content']);
            $summary = !empty($data['summary']) ? trim($data['summary']) : $this->generateSummary($content);
            $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
            $tags = !empty($data['tags']) ? trim($data['tags']) : '';
            $is_public = isset($data['is_public']) ? (bool)$data['is_public'] : false;
            $status = !empty($data['status']) ? $data['status'] : 'draft';
            
            // Insert note into database
            $sql = "INSERT INTO notes (user_id, category_id, title, content, summary, tags, is_public, status, created_at, updated_at) 
                    VALUES (:user_id, :category_id, :title, :content, :summary, :tags, :is_public, :status, NOW(), NOW())";
            
            $params = [
                ':user_id' => $data['user_id'],
                ':category_id' => $category_id,
                ':title' => $title,
                ':content' => $content,
                ':summary' => $summary,
                ':tags' => $tags,
                ':is_public' => $is_public,
                ':status' => $status
            ];
            
            $result = $this->db->execute($sql, $params);
            
            if ($result) {
                $note_id = $this->db->getLastInsertId();
                $this->ObjFncs->setMsg('success', 'Note created successfully!');
                return $note_id;
            } else {
                throw new Exception('Failed to create note');
            }
            
        } catch (Exception $e) {
            error_log("Note Creation Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage());
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
                $this->ObjFncs->setMsg('success', 'Note updated successfully!');
                return true;
            } else {
                throw new Exception('Failed to update note');
            }
            
        } catch (Exception $e) {
            error_log("Update Note Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($note_id, $user_id) {
        try {
            // Check if user owns the note
            $existing_note = $this->db->fetchOne(
                "SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id",
                [':note_id' => $note_id, ':user_id' => $user_id]
            );
            
            if (!$existing_note) {
                throw new Exception('Note not found or you do not have permission to delete it');
            }
            
            $sql = "DELETE FROM notes WHERE id = :note_id AND user_id = :user_id";
            $result = $this->db->execute($sql, [':note_id' => $note_id, ':user_id' => $user_id]);
            
            if ($result) {
                $this->ObjFncs->setMsg('success', 'Note deleted successfully!');
                return true;
            } else {
                throw new Exception('Failed to delete note');
            }
            
        } catch (Exception $e) {
            error_log("Delete Note Error: " . $e->getMessage());
            $this->ObjFncs->setMsg('errors', $e->getMessage());
            return false;
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $notesController = new NotesController();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }
    
    $action = $_POST['action'];
    $user_id = $_SESSION['user_id'];
    
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
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
