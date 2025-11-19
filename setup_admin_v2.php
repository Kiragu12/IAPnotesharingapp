<?php
/**
 * Admin Panel Setup Script - PostgreSQL/MySQL Compatible
 * This script creates the necessary database tables for the admin panel
 */

require_once 'app/Services/Global/Database.php';
require_once 'conf.php';

echo "=================================\n";
echo "Admin Panel Setup Script\n";
echo "=================================\n\n";

try {
    $db = new Database($conf);
    
    // Detect database driver
    $driver = isset($conf['db_driver']) ? $conf['db_driver'] : 'mysql';
    echo "Database Driver: $driver\n\n";
    
    // For PostgreSQL (Supabase), we need different SQL syntax
    if ($driver === 'pgsql') {
        echo "Creating PostgreSQL tables...\n\n";
        
        // 1. Add is_admin column to users table
        try {
            $db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin INTEGER DEFAULT 0");
            $db->execute();
            echo "✓ Added is_admin column to users table\n";
        } catch (Exception $e) {
            echo "✓ is_admin column already exists\n";
        }
        
        // 2. Create admin_activity_logs table
        $db->query("
            CREATE TABLE IF NOT EXISTS admin_activity_logs (
                id SERIAL PRIMARY KEY,
                admin_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                action_type VARCHAR(50) NOT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                user_agent VARCHAR(500),
                metadata TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        echo "✓ Created admin_activity_logs table\n";
        
        // Create indexes
        try {
            $db->query("CREATE INDEX IF NOT EXISTS idx_admin_activity_admin ON admin_activity_logs(admin_id)");
            $db->execute();
            $db->query("CREATE INDEX IF NOT EXISTS idx_admin_activity_created ON admin_activity_logs(created_at)");
            $db->execute();
            echo "✓ Created indexes for admin_activity_logs\n";
        } catch (Exception $e) {
            echo "  Indexes already exist\n";
        }
        
        // 3. Create user_suspensions table
        $db->query("
            CREATE TABLE IF NOT EXISTS user_suspensions (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                suspended_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                reason TEXT,
                suspended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NULL,
                is_permanent BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        echo "✓ Created user_suspensions table\n";
        
        try {
            $db->query("CREATE INDEX IF NOT EXISTS idx_user_suspensions_user ON user_suspensions(user_id)");
            $db->execute();
            $db->query("CREATE INDEX IF NOT EXISTS idx_user_suspensions_active ON user_suspensions(is_active)");
            $db->execute();
            echo "✓ Created indexes for user_suspensions\n";
        } catch (Exception $e) {
            echo "  Indexes already exist\n";
        }
        
        // 4. Create flagged_notes table
        $db->query("
            CREATE TABLE IF NOT EXISTS flagged_notes (
                id SERIAL PRIMARY KEY,
                note_id INTEGER NOT NULL REFERENCES notes(id) ON DELETE CASCADE,
                reported_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
                reason TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'pending',
                reviewed_by INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
                reviewed_at TIMESTAMP NULL,
                resolution_notes TEXT,
                priority VARCHAR(20) DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        echo "✓ Created flagged_notes table\n";
        
        try {
            $db->query("CREATE INDEX IF NOT EXISTS idx_flagged_notes_note ON flagged_notes(note_id)");
            $db->execute();
            $db->query("CREATE INDEX IF NOT EXISTS idx_flagged_notes_status ON flagged_notes(status)");
            $db->execute();
            echo "✓ Created indexes for flagged_notes\n";
        } catch (Exception $e) {
            echo "  Indexes already exist\n";
        }
        
        // 5. Create system_statistics table
        $db->query("
            CREATE TABLE IF NOT EXISTS system_statistics (
                id SERIAL PRIMARY KEY,
                stat_key VARCHAR(100) UNIQUE NOT NULL,
                stat_value TEXT NOT NULL,
                metadata TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        echo "✓ Created system_statistics table\n";
        
        try {
            $db->query("CREATE INDEX IF NOT EXISTS idx_system_stats_key ON system_statistics(stat_key)");
            $db->execute();
            echo "✓ Created indexes for system_statistics\n";
        } catch (Exception $e) {
            echo "  Indexes already exist\n";
        }
        
        // 6. Create admin_notifications table
        $db->query("
            CREATE TABLE IF NOT EXISTS admin_notifications (
                id SERIAL PRIMARY KEY,
                admin_id INTEGER NULL REFERENCES users(id) ON DELETE CASCADE,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                priority VARCHAR(20) DEFAULT 'medium',
                related_id INTEGER NULL,
                metadata TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        echo "✓ Created admin_notifications table\n";
        
        try {
            $db->query("CREATE INDEX IF NOT EXISTS idx_admin_notif_admin ON admin_notifications(admin_id)");
            $db->execute();
            $db->query("CREATE INDEX IF NOT EXISTS idx_admin_notif_read ON admin_notifications(is_read)");
            $db->execute();
            echo "✓ Created indexes for admin_notifications\n";
        } catch (Exception $e) {
            echo "  Indexes already exist\n";
        }
        
    } else {
        // MySQL version - use the original SQL file
        echo "Creating MySQL tables...\n\n";
        
        $sqlFile = 'sql/create_admin_tables.sql';
        if (!file_exists($sqlFile)) {
            die("ERROR: SQL file not found at: $sqlFile\n");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Split statements by semicolon
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            // Skip empty statements and comments
            if (empty($statement) || strpos(trim($statement), '--') === 0) {
                continue;
            }
            
            try {
                $db->query($statement);
                $db->execute();
                
                // Extract table name for better feedback
                if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                    echo "✓ Created table: {$matches[1]}\n";
                } elseif (preg_match('/ALTER TABLE (\w+)/i', $statement, $matches)) {
                    echo "✓ Modified table: {$matches[1]}\n";
                } elseif (preg_match('/CREATE INDEX (\w+)/i', $statement, $matches)) {
                    echo "✓ Created index: {$matches[1]}\n";
                }
            } catch (Exception $e) {
                // Silently skip errors for already existing objects
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "✗ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n=================================\n";
    echo "Setup Complete!\n";
    echo "=================================\n\n";
    
    echo "Next Steps:\n";
    echo "1. Create your first admin user by running:\n";
    echo "   php create_admin.php your-email@example.com\n\n";
    echo "2. Access admin panel:\n";
    if ($driver === 'pgsql') {
        echo "   https://your-app.supabase.co/views/admin/dashboard.php\n\n";
    } else {
        echo "   http://localhost/IAPnotesharingapp-1/views/admin/dashboard.php\n\n";
    }
    
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
