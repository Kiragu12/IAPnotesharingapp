<?php
/**
 * Manual Admin Tables Creation for MySQL
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

echo "=================================\n";
echo "Creating Admin Tables (MySQL)\n";
echo "=================================\n\n";

try {
    $db = new Database($conf);
    
    // 1. Add is_admin column if not exists
    echo "1. Updating users table...\n";
    try {
        $db->query("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0 AFTER email_verified_at");
        echo "   ✓ Added is_admin column\n";
    } catch (Exception $e) {
        echo "   ✓ is_admin column already exists\n";
    }
    
    // 2. Create admin_activity_logs
    echo "2. Creating admin_activity_logs table...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS admin_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(500),
            metadata TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_id (admin_id),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   ✓ Created admin_activity_logs\n";
    
    // 3. Create user_suspensions
    echo "3. Creating user_suspensions table...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS user_suspensions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            suspended_by INT NOT NULL,
            reason TEXT,
            suspended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NULL,
            is_permanent TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_is_active (is_active),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (suspended_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   ✓ Created user_suspensions\n";
    
    // 4. Create flagged_notes
    echo "4. Creating flagged_notes table...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS flagged_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            note_id INT NOT NULL,
            reported_by INT NOT NULL,
            reason TEXT NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            reviewed_by INT NULL,
            reviewed_at TIMESTAMP NULL,
            resolution_notes TEXT,
            priority VARCHAR(20) DEFAULT 'medium',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_note_id (note_id),
            INDEX idx_status (status),
            FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
            FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   ✓ Created flagged_notes\n";
    
    // 5. Create system_statistics
    echo "5. Creating system_statistics table...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS system_statistics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_key VARCHAR(100) UNIQUE NOT NULL,
            stat_value TEXT NOT NULL,
            metadata TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_stat_key (stat_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   ✓ Created system_statistics\n";
    
    // 6. Create admin_notifications
    echo "6. Creating admin_notifications table...\n";
    $db->query("
        CREATE TABLE IF NOT EXISTS admin_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            priority VARCHAR(20) DEFAULT 'medium',
            related_id INT NULL,
            metadata TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_admin_id (admin_id),
            INDEX idx_is_read (is_read),
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "   ✓ Created admin_notifications\n";
    
    echo "\n=================================\n";
    echo "✓ All tables created successfully!\n";
    echo "=================================\n\n";
    
    echo "Admin panel is ready to use!\n";
    echo "Access: http://localhost/IAPnotesharingapp-1/views/admin/dashboard.php\n\n";
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
