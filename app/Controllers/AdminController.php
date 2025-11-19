<?php
/**
 * Admin Controller
 * Handles all admin-related operations
 */

class AdminController {
    private $db;
    private $ObjFncs;
    
    public function __construct() {
        // Ensure session has started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Get the project root directory
        $project_root = dirname(dirname(__DIR__));
        
        // Include required files
        require_once $project_root . '/conf.php';
        require_once $project_root . '/app/Services/Global/Database.php';
        require_once $project_root . '/app/Services/Global/fncs.php';
        
        global $conf;
        $this->db = new Database($conf);
        $this->ObjFncs = new fncs();
    }
    
    /**
     * Check if current user is admin
     */
    public function isAdmin($user_id = null) {
        if ($user_id === null) {
            $user_id = $_SESSION['user_id'] ?? null;
        }
        
        if (!$user_id) {
            return false;
        }
        
        try {
            $user = $this->db->fetchOne(
                "SELECT is_admin FROM users WHERE id = :uid",
                [':uid' => $user_id]
            );
            
            return $user && $user['is_admin'] == 1;
        } catch (Exception $e) {
            error_log('Admin check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Total users
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $result['count'];
            
            // Active users (logged in last 30 days)
            $result = $this->db->fetchOne(
                "SELECT COUNT(DISTINCT user_id) as count FROM remember_tokens WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stats['active_users'] = $result['count'];
            
            // New users this month
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
            );
            $stats['new_users_this_month'] = $result['count'];
            
            // Total notes
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM notes");
            $stats['total_notes'] = $result['count'];
            
            // Public notes
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE is_public = 1");
            $stats['public_notes'] = $result['count'];
            
            // Total views
            $result = $this->db->fetchOne("SELECT SUM(views) as total FROM notes");
            $stats['total_views'] = $result['total'] ?? 0;
            
            // Total categories
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM categories");
            $stats['total_categories'] = $result['count'];
            
            // Deleted notes
            try {
                $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM deleted_notes");
                $stats['deleted_notes_count'] = $result['count'] ?? 0;
            } catch (Exception $e) {
                $stats['deleted_notes_count'] = 0;
            }
            
            // Deleted accounts
            try {
                $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM deleted_accounts");
                $stats['deleted_accounts'] = $result['count'] ?? 0;
            } catch (Exception $e) {
                $stats['deleted_accounts'] = 0;
            }
            
            // Suspended users
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM user_suspensions WHERE is_active = 1");
            $stats['suspended_users'] = $result['count'] ?? 0;
            
            // Storage used (file notes)
            $result = $this->db->fetchOne("SELECT SUM(file_size) as total FROM notes WHERE note_type = 'file'");
            $stats['storage_used'] = $result['total'] ?? 0;
            
            // Notes created today
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE DATE(created_at) = CURDATE()");
            $stats['notes_today'] = $result['count'];
            
            // Users registered today
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
            $stats['users_today'] = $result['count'];
            
            return $stats;
        } catch (Exception $e) {
            error_log('Dashboard stats error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all users with pagination
     */
    public function getUsers($limit = 50, $offset = 0, $search = '', $filter = 'all') {
        try {
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(users.full_name LIKE :search OR users.email LIKE :search OR users.username LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            // Filter logic
            if ($filter === 'admins') {
                $where[] = "users.is_admin = 1";
            } elseif ($filter === 'suspended') {
                $where[] = "EXISTS (SELECT 1 FROM user_suspensions WHERE user_id = users.id AND is_active = 1)";
            } elseif ($filter === 'verified') {
                $where[] = "users.is_verified = 1";
            } elseif ($filter === 'unverified') {
                $where[] = "users.is_verified = 0";
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        users.*,
                        COUNT(DISTINCT notes.id) as note_count,
                        COALESCE(SUM(notes.views), 0) as total_views,
                        (SELECT COUNT(*) FROM user_suspensions WHERE user_id = users.id AND is_active = 1) as is_suspended
                    FROM users
                    LEFT JOIN notes ON notes.user_id = users.id
                    $whereClause
                    GROUP BY users.id
                    ORDER BY users.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log('Get users error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user count (for pagination)
     */
    public function getUserCount($search = '', $filter = 'all') {
        try {
            $where = [];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(full_name LIKE :search OR email LIKE :search OR username LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if ($filter === 'admins') {
                $where[] = "is_admin = 1";
            } elseif ($filter === 'suspended') {
                $where[] = "EXISTS (SELECT 1 FROM user_suspensions WHERE user_id = users.id AND is_active = 1)";
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users $whereClause", $params);
            return $result['count'];
        } catch (Exception $e) {
            error_log('Get user count error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Delete user account (admin action)
     */
    public function deleteUser($user_id, $admin_id) {
        try {
            // Don't allow deleting yourself
            if ($user_id == $admin_id) {
                return ['success' => false, 'message' => 'Cannot delete your own account'];
            }
            
            // Check if user exists
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = :uid", [':uid' => $user_id]);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Log to deleted_accounts table before deletion
            try {
                // Get note count
                $noteCount = $this->db->fetchOne("SELECT COUNT(*) as count FROM notes WHERE user_id = :uid", [':uid' => $user_id]);
                $count = $noteCount['count'] ?? 0;
                
                $this->db->execute(
                    "INSERT INTO deleted_accounts (original_user_id, full_name, email, notes_count, deleted_by, deletion_reason) 
                     VALUES (:uid, :name, :email, :count, :aid, 'Admin deletion')",
                    [
                        ':uid' => $user_id,
                        ':name' => $user['full_name'],
                        ':email' => $user['email'],
                        ':count' => $count,
                        ':aid' => $admin_id
                    ]
                );
            } catch (Exception $e) {
                // Continue with deletion even if logging fails, but log the error
                error_log('Error logging deleted user: ' . $e->getMessage());
            }
            
            // Delete user (cascade will handle related records)
            $this->db->execute("DELETE FROM users WHERE id = :uid", [':uid' => $user_id]);
            
            // Log action
            $this->logAdminActivity($admin_id, 'user_delete', 'user', $user_id, 
                "Deleted user: {$user['email']} ({$user['full_name']})");
            
            return ['success' => true, 'message' => 'User deleted successfully'];
        } catch (Exception $e) {
            error_log('Delete user error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Suspend user account
     */
    public function suspendUser($user_id, $admin_id, $reason, $duration_days = null) {
        try {
            $user = $this->db->fetchOne("SELECT * FROM users WHERE id = :uid", [':uid' => $user_id]);
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            $suspended_until = $duration_days ? date('Y-m-d H:i:s', strtotime("+{$duration_days} days")) : null;
            
            $this->db->query(
                "INSERT INTO user_suspensions (user_id, suspended_by, reason, expires_at, is_active) 
                 VALUES (:uid, :aid, :reason, :until, 1)",
                [':uid' => $user_id, ':aid' => $admin_id, ':reason' => $reason, ':until' => $suspended_until]
            );
            
            // Log action
            $this->logAdminActivity($admin_id, 'user_suspend', 'user', $user_id, 
                "Suspended user: {$user['email']} - Reason: $reason");
            
            return ['success' => true, 'message' => 'User suspended successfully'];
        } catch (Exception $e) {
            error_log('Suspend user error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error suspending user: ' . $e->getMessage()];
        }
    }
    
    /**
     * Unsuspend user account
     */
    public function unsuspendUser($user_id, $admin_id) {
        try {
            $this->db->execute(
                "UPDATE user_suspensions SET is_active = 0, unsuspended_at = NOW(), unsuspended_by = :aid 
                 WHERE user_id = :uid AND is_active = 1",
                [':uid' => $user_id, ':aid' => $admin_id]
            );
            
            $this->logAdminActivity($admin_id, 'user_activate', 'user', $user_id, "Unsuspended user");
            
            return ['success' => true, 'message' => 'User unsuspended successfully'];
        } catch (Exception $e) {
            error_log('Unsuspend user error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error unsuspending user'];
        }
    }
    
    /**
     * Make user admin
     */
    public function makeAdmin($user_id, $admin_id) {
        try {
            $this->db->execute("UPDATE users SET is_admin = 1 WHERE id = :uid", [':uid' => $user_id]);
            $this->logAdminActivity($admin_id, 'settings_change', 'user', $user_id, "Granted admin privileges");
            return ['success' => true, 'message' => 'User granted admin privileges'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error granting admin privileges'];
        }
    }
    
    /**
     * Remove admin privileges
     */
    public function removeAdmin($user_id, $admin_id) {
        try {
            if ($user_id == $admin_id) {
                return ['success' => false, 'message' => 'Cannot remove your own admin privileges'];
            }
            
            $this->db->execute("UPDATE users SET is_admin = 0 WHERE id = :uid", [':uid' => $user_id]);
            $this->logAdminActivity($admin_id, 'settings_change', 'user', $user_id, "Removed admin privileges");
            return ['success' => true, 'message' => 'Admin privileges removed'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error removing admin privileges'];
        }
    }
    
    /**
     * Get all notes with filters
     */
    public function getNotes($limit = 50, $offset = 0, $search = '', $filter = 'all') {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (!empty($search)) {
                $where[] = "(notes.title LIKE :search OR notes.content LIKE :search OR notes.tags LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            if ($filter === 'public') {
                $where[] = "notes.is_public = 1";
            } elseif ($filter === 'private') {
                $where[] = "notes.is_public = 0";
            } elseif ($filter === 'flagged') {
                $where[] = "EXISTS (SELECT 1 FROM flagged_notes WHERE note_id = notes.id AND status = 'pending')";
            } elseif ($filter === 'files') {
                $where[] = "notes.note_type = 'file'";
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT 
                        notes.*,
                        users.full_name as author_name,
                        users.email as author_email,
                        categories.name as category_name,
                        (SELECT COUNT(*) FROM flagged_notes WHERE note_id = notes.id AND status = 'pending') as flag_count
                    FROM notes
                    LEFT JOIN users ON notes.user_id = users.id
                    LEFT JOIN categories ON notes.category_id = categories.id
                    WHERE $whereClause
                    ORDER BY notes.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log('Get notes error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete note (admin action)
     */
    public function deleteNote($note_id, $admin_id) {
        try {
            $note = $this->db->fetchOne("SELECT * FROM notes WHERE id = :nid", [':nid' => $note_id]);
            if (!$note) {
                return ['success' => false, 'message' => 'Note not found'];
            }
            
            // Archive to deleted_notes before deletion
            try {
                $this->db->execute(
                    "INSERT INTO deleted_notes (original_note_id, user_id, title, content, file_path, note_type, deleted_by, deletion_reason, created_at) 
                     VALUES (:nid, :uid, :title, :content, :path, :type, :aid, 'Admin deletion', :created)",
                    [
                        ':nid' => $note['id'],
                        ':uid' => $note['user_id'],
                        ':title' => $note['title'],
                        ':content' => $note['content'],
                        ':path' => $note['file_path'],
                        ':type' => $note['note_type'],
                        ':aid' => $admin_id,
                        ':created' => $note['created_at']
                    ]
                );
            } catch (Exception $e) {
                error_log('Error archiving deleted note: ' . $e->getMessage());
            }
            
            // Delete physical file if exists
            if ($note['note_type'] === 'file' && !empty($note['file_path'])) {
                $filePath = dirname(dirname(__DIR__)) . '/' . $note['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            $this->db->execute("DELETE FROM notes WHERE id = :nid", [':nid' => $note_id]);
            
            $this->logAdminActivity($admin_id, 'note_delete', 'note', $note_id, 
                "Deleted note: {$note['title']}");
            
            return ['success' => true, 'message' => 'Note deleted successfully'];
        } catch (Exception $e) {
            error_log('Delete note error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error deleting note'];
        }
    }
    
    /**
     * Flag a note
     */
    public function flagNote($note_id, $admin_id, $reason) {
        try {
            // Check if already flagged
            $existing = $this->db->fetchOne(
                "SELECT id FROM flagged_notes WHERE note_id = :nid AND status = 'pending'",
                [':nid' => $note_id]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Note is already flagged and pending review'];
            }
            
            $this->db->query(
                "INSERT INTO flagged_notes (note_id, reported_by, reason, status, created_at) 
                 VALUES (:nid, :aid, :reason, 'pending', NOW())",
                [':nid' => $note_id, ':aid' => $admin_id, ':reason' => $reason]
            );
            
            $this->logAdminActivity($admin_id, 'note_flag', 'note', $note_id, "Flagged note: $reason");
            
            return ['success' => true, 'message' => 'Note flagged successfully'];
        } catch (Exception $e) {
            error_log('Flag note error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error flagging note'];
        }
    }

    /**
     * Get analytics data
     */
    public function getAnalytics($period = '30days') {
        try {
            $analytics = [];
            
            // Determine date range
            switch ($period) {
                case '7days':
                    $days = 7;
                    break;
                case '90days':
                    $days = 90;
                    break;
                default:
                    $days = 30;
            }
            
            // User growth (daily)
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM users 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            $analytics['user_growth'] = $this->db->fetchAll($sql, [':days' => $days]);
            
            // Note creation (daily)
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                    FROM notes 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            $analytics['note_creation'] = $this->db->fetchAll($sql, [':days' => $days]);
            
            // Popular categories
            $sql = "SELECT categories.name, COUNT(notes.id) as count 
                    FROM categories
                    LEFT JOIN notes ON categories.id = notes.category_id
                    GROUP BY categories.id
                    ORDER BY count DESC
                    LIMIT 10";
            $analytics['popular_categories'] = $this->db->fetchAll($sql);
            
            // Top contributors
            $sql = "SELECT users.full_name, users.email, COUNT(notes.id) as note_count, SUM(notes.views) as total_views
                    FROM users
                    LEFT JOIN notes ON users.id = notes.user_id
                    GROUP BY users.id
                    ORDER BY note_count DESC
                    LIMIT 10";
            $analytics['top_contributors'] = $this->db->fetchAll($sql);
            
            // Note type distribution
            $sql = "SELECT note_type, COUNT(*) as count FROM notes GROUP BY note_type";
            $analytics['note_type_distribution'] = $this->db->fetchAll($sql);
            
            // Public vs Private
            $sql = "SELECT 
                        SUM(CASE WHEN is_public = 1 THEN 1 ELSE 0 END) as public_count,
                        SUM(CASE WHEN is_public = 0 THEN 1 ELSE 0 END) as private_count
                    FROM notes";
            $analytics['visibility_distribution'] = $this->db->fetchOne($sql);
            
            return $analytics;
        } catch (Exception $e) {
            error_log('Get analytics error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent admin activity
     */
    public function getRecentActivity($limit = 20) {
        try {
            $sql = "SELECT 
                        admin_activity_logs.*,
                        users.full_name as admin_name
                    FROM admin_activity_logs
                    LEFT JOIN users ON admin_activity_logs.admin_user_id = users.id
                    ORDER BY created_at DESC
                    LIMIT :limit";
            
            return $this->db->fetchAll($sql, [':limit' => (int)$limit]);
        } catch (Exception $e) {
            error_log('Get recent activity error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log admin activity
     */
    private function logAdminActivity($admin_id, $action_type, $target_type, $target_id, $description) {
        try {
            $sql = "INSERT INTO admin_activity_logs 
                    (admin_user_id, action_type, target_type, target_id, description, ip_address, user_agent) 
                    VALUES (:aid, :action, :target_type, :target_id, :desc, :ip, :ua)";
            
            $this->db->query($sql, [
                ':aid' => $admin_id,
                ':action' => $action_type,
                ':target_type' => $target_type,
                ':target_id' => $target_id,
                ':desc' => $description,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (Exception $e) {
            error_log('Log admin activity error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get flagged notes for review
     */
    public function getFlaggedNotes($status = 'pending') {
        try {
            $sql = "SELECT 
                        flagged_notes.*,
                        notes.title as note_title,
                        notes.content as note_content,
                        reporter.full_name as reporter_name,
                        note_author.full_name as author_name
                    FROM flagged_notes
                    LEFT JOIN notes ON flagged_notes.note_id = notes.id
                    LEFT JOIN users as reporter ON flagged_notes.flagged_by = reporter.id
                    LEFT JOIN users as note_author ON notes.user_id = note_author.id
                    WHERE flagged_notes.status = :status
                    ORDER BY flagged_notes.created_at DESC";
            
            return $this->db->fetchAll($sql, [':status' => $status]);
        } catch (Exception $e) {
            error_log('Get flagged notes error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export data to CSV
     */
    public function exportData($type, $admin_id) {
        try {
            $filename = '';
            $data = [];
            
            if ($type === 'users') {
                $data = $this->db->fetchAll("SELECT id, username, email, full_name, is_verified, is_admin, created_at FROM users ORDER BY id");
                $filename = 'users_export_' . date('Y-m-d') . '.csv';
            } elseif ($type === 'notes') {
                $data = $this->db->fetchAll("SELECT id, user_id, title, note_type, is_public, status, views, created_at FROM notes ORDER BY id");
                $filename = 'notes_export_' . date('Y-m-d') . '.csv';
            }
            
            if (!empty($data)) {
                $this->logAdminActivity($admin_id, 'export_data', 'system', null, "Exported $type data");
            }
            
            return ['success' => true, 'data' => $data, 'filename' => $filename];
        } catch (Exception $e) {
            error_log('Export data error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error exporting data'];
        }
    }
    
    /**
     * Get user details by ID
     */
    public function getUserDetails($user_id) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = :uid", [':uid' => $user_id]);
    }
    
    /**
     * Get user's notes
     */
    public function getUserNotes($user_id) {
        return $this->db->fetchAll("
            SELECT id, title, status, views, created_at, is_public
            FROM notes 
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ", [':uid' => $user_id]);
    }
    
    /**
     * Get user's suspension history
     */
    public function getUserSuspensions($user_id) {
        return $this->db->fetchAll("
            SELECT us.*, u.full_name as suspended_by_name
            FROM user_suspensions us
            LEFT JOIN users u ON us.suspended_by = u.id
            WHERE us.user_id = :uid
            ORDER BY us.created_at DESC
        ", [':uid' => $user_id]);
    }
    
    /**
     * Get deleted notes
     */
    public function getDeletedNotes($limit = 50, $offset = 0) {
        try {
            $sql = "SELECT 
                        deleted_notes.*,
                        users.full_name as deleted_by_name
                    FROM deleted_notes
                    LEFT JOIN users ON deleted_notes.deleted_by = users.id
                    ORDER BY deleted_at DESC
                    LIMIT :limit OFFSET :offset";
            
            return $this->db->fetchAll($sql, [
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
        } catch (Exception $e) {
            error_log('Get deleted notes error: ' . $e->getMessage());
            return [];
        }
    }
    
}
