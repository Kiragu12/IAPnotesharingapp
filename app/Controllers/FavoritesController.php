<?php
/**
 * Favorites Controller
 * Handles all favorites-related operations
 */

class FavoritesController {
    private $db;
    private $ObjFncs;
    
    public function __construct($database = null) {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            session_start();
        }
        
        // Use provided database instance or create new one
        if ($database !== null && $database instanceof Database) {
            $this->db = $database;
            // Initialize fncs when using external database
            if (class_exists('fncs')) {
                $this->ObjFncs = new fncs();
            } else {
                $this->ObjFncs = null;
            }
        } else {
            // Create our own database connection (includes fncs setup)
            $this->initializeDatabase();
        }
    }
    
    /**
     * Initialize database connection when no database instance is provided
     */
    private function initializeDatabase() {
        // Get the project root directory
        $project_root = dirname(dirname(__DIR__));
        
        // Load configuration first
        $conf = null;
        $config_path = $project_root . '/config/conf.php';
        
        if (file_exists($config_path)) {
            require_once $config_path;
        }
        
        // Validate configuration was loaded
        if (!isset($conf) || !is_array($conf)) {
            throw new Exception("Failed to load database configuration from: " . $config_path);
        }
        
        // Include database class
        if (!class_exists('Database')) {
            require_once $project_root . '/app/Services/Global/Database.php';
        }
        if (!class_exists('fncs')) {
            require_once $project_root . '/app/Services/Global/fncs.php';
        }
        
        $this->db = new Database($conf);
        
        // Make fncs optional since it's not essential for core functionality
        if (class_exists('fncs')) {
            $this->ObjFncs = new fncs();
        } else {
            $this->ObjFncs = null;
        }
    }
    
    /**
     * Add note to favorites
     */
    public function addToFavorites($user_id, $note_id) {
        try {
            // Check if already favorited
            $existing = $this->db->fetchOne(
                "SELECT id FROM favorites WHERE user_id = ? AND note_id = ?",
                [$user_id, $note_id]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Note is already in favorites'];
            }
            
            // Check if note exists and is accessible
            $note = $this->db->fetchOne(
                "SELECT id, title FROM notes WHERE id = ? AND (is_public = 1 OR user_id = ?)",
                [$note_id, $user_id]
            );
            
            if (!$note) {
                return ['success' => false, 'message' => 'Note not found or not accessible'];
            }
            
            // Add to favorites
            $result = $this->db->execute(
                "INSERT INTO favorites (user_id, note_id) VALUES (?, ?)",
                [$user_id, $note_id]
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'Note added to favorites!'];
            } else {
                return ['success' => false, 'message' => 'Failed to add to favorites'];
            }
            
        } catch (Exception $e) {
            error_log("Add to Favorites Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Remove note from favorites
     */
    public function removeFromFavorites($user_id, $note_id) {
        try {
            $result = $this->db->execute(
                "DELETE FROM favorites WHERE user_id = ? AND note_id = ?",
                [$user_id, $note_id]
            );
            
            if ($result) {
                return ['success' => true, 'message' => 'Note removed from favorites'];
            } else {
                return ['success' => false, 'message' => 'Note not found in favorites'];
            }
            
        } catch (Exception $e) {
            error_log("Remove from Favorites Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Check if note is favorited by user
     */
    public function isFavorited($user_id, $note_id) {
        try {
            $favorite = $this->db->fetchOne(
                "SELECT id FROM favorites WHERE user_id = ? AND note_id = ?",
                [$user_id, $note_id]
            );
            
            return !empty($favorite);
            
        } catch (Exception $e) {
            error_log("Check Favorite Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's favorite notes
     */
    public function getUserFavorites($user_id, $search = '', $limit = 20) {
        try {
            $sql = "SELECT n.*, u.full_name as author_name, c.name as category_name, f.created_at as favorited_at
                    FROM favorites f
                    JOIN notes n ON f.note_id = n.id
                    LEFT JOIN users u ON n.user_id = u.id
                    LEFT JOIN categories c ON n.category_id = c.id
                    WHERE f.user_id = ? AND (n.is_public = 1 OR n.user_id = ?)";
            
            $params = [$user_id, $user_id];
            
            if (!empty($search)) {
                $sql .= " AND (n.title LIKE ? OR n.content LIKE ? OR n.tags LIKE ?)";
                $search_param = '%' . $search . '%';
                $params[] = $search_param;
                $params[] = $search_param;
                $params[] = $search_param;
            }
            
            $sql .= " ORDER BY f.created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = (int)$limit;
            }
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            error_log("Get User Favorites Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get favorites count for user
     */
    public function getFavoritesCount($user_id) {
        try {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM favorites WHERE user_id = ?",
                [$user_id]
            );
            
            return $result ? (int)$result['count'] : 0;
            
        } catch (Exception $e) {
            error_log("Get Favorites Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get most favorited notes (popular)
     */
    public function getMostFavorited($limit = 10) {
        try {
            $sql = "SELECT n.*, u.full_name as author_name, c.name as category_name, 
                           COUNT(f.id) as favorites_count
                    FROM notes n
                    LEFT JOIN favorites f ON n.id = f.note_id
                    LEFT JOIN users u ON n.user_id = u.id
                    LEFT JOIN categories c ON n.category_id = c.id
                    WHERE n.is_public = 1 AND n.status = 'published'
                    GROUP BY n.id
                    HAVING favorites_count > 0
                    ORDER BY favorites_count DESC, n.created_at DESC
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [(int)$limit]);
            
        } catch (Exception $e) {
            error_log("Get Most Favorited Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Toggle favorite status (add if not favorited, remove if favorited)
     */
    public function toggleFavorite($user_id, $note_id) {
        if ($this->isFavorited($user_id, $note_id)) {
            return $this->removeFromFavorites($user_id, $note_id);
        } else {
            return $this->addToFavorites($user_id, $note_id);
        }
    }
}

// Handle AJAX requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
    
    $favoritesController = new FavoritesController();
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'toggle_favorite':
            $note_id = $_POST['note_id'] ?? 0;
            $result = $favoritesController->toggleFavorite($user_id, $note_id);
            echo json_encode($result);
            break;
            
        case 'add_favorite':
            $note_id = $_POST['note_id'] ?? 0;
            $result = $favoritesController->addToFavorites($user_id, $note_id);
            echo json_encode($result);
            break;
            
        case 'remove_favorite':
            $note_id = $_POST['note_id'] ?? 0;
            $result = $favoritesController->removeFromFavorites($user_id, $note_id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
