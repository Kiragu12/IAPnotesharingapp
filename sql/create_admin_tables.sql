-- Admin System Tables for NotesShare Academy
-- Created: November 18, 2025

-- Add is_admin column to users table if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_admin TINYINT(1) DEFAULT 0 AFTER is_2fa_enabled;

-- Activity logs table for admin monitoring
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action_type ENUM('user_delete', 'user_suspend', 'user_activate', 'note_delete', 'note_flag', 'settings_change', 'view_dashboard', 'export_data') NOT NULL,
    target_type ENUM('user', 'note', 'category', 'system') NOT NULL,
    target_id INT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_user (admin_user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User suspensions table
CREATE TABLE IF NOT EXISTS user_suspensions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    suspended_by INT NOT NULL,
    reason TEXT,
    suspended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    suspended_until DATETIME NULL,
    is_active TINYINT(1) DEFAULT 1,
    unsuspended_at DATETIME NULL,
    unsuspended_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (suspended_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Flagged notes table for admin review
CREATE TABLE IF NOT EXISTS flagged_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    flagged_by INT NOT NULL,
    reason ENUM('spam', 'inappropriate', 'copyright', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (flagged_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_note (note_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System statistics cache table (for performance)
CREATE TABLE IF NOT EXISTS system_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_key VARCHAR(100) NOT NULL UNIQUE,
    stat_value TEXT,
    metadata JSON,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (stat_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin notifications table
CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_type ENUM('new_user', 'flagged_note', 'user_report', 'system_alert', 'milestone') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    related_type VARCHAR(50) NULL,
    related_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial admin statistics keys
INSERT INTO system_statistics (stat_key, stat_value, metadata) VALUES
('total_users', '0', '{}'),
('total_notes', '0', '{}'),
('total_uploads_size', '0', '{}'),
('active_users_today', '0', '{}'),
('new_users_this_month', '0', '{}')
ON DUPLICATE KEY UPDATE stat_key = stat_key;
