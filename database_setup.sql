-- =====================================================-- =====================================================-- =====================================================

-- IAP NOTE SHARING APP - COMPLETE DATABASE SETUP

-- =====================================================-- IAP NOTE SHARING APP - COMPLETE DATABASE SETUP-- NotesShare Academy Database Setup

-- This script creates all required tables for the application

-- Run this on a fresh database to set up everything you need-- =====================================================-- Complete database schema for the notes sharing app

-- =====================================================

-- This script creates all required tables for the application-- =====================================================

-- Step 1: Create the database (optional - uncomment if needed)

-- CREATE DATABASE IF NOT EXISTS iap_notesharing;-- Run this on a fresh database to set up everything you need

-- USE iap_notesharing;

-- =====================================================-- Create the main database (uncomment if needed)

-- =====================================================

-- TABLE 1: USERS-- CREATE DATABASE noteshare_academy;

-- Stores user account information

-- =====================================================-- Step 1: Create the database (optional - uncomment if needed)-- USE noteshare_academy;

CREATE TABLE IF NOT EXISTS users (

    id INT AUTO_INCREMENT PRIMARY KEY,-- CREATE DATABASE IF NOT EXISTS iap_notesharing;

    email VARCHAR(255) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,-- USE iap_notesharing;-- =====================================================

    full_name VARCHAR(255) NOT NULL,

    phone VARCHAR(20),-- 1. USERS TABLE

    profile_picture VARCHAR(500),

    email_verified BOOLEAN DEFAULT FALSE,-- =====================================================-- =====================================================

    is_2fa_enabled BOOLEAN DEFAULT TRUE,

    is_active BOOLEAN DEFAULT TRUE,-- TABLE 1: USERSCREATE TABLE users (

    is_admin BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,-- Stores user account information    id INT AUTO_INCREMENT PRIMARY KEY,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- =====================================================    email VARCHAR(255) NOT NULL UNIQUE,

    INDEX idx_email (email),

    INDEX idx_active (is_active)CREATE TABLE IF NOT EXISTS users (    password VARCHAR(255) NOT NULL,

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    id INT AUTO_INCREMENT PRIMARY KEY,    full_name VARCHAR(255) NOT NULL,

-- =====================================================

-- TABLE 2: TWO_FACTOR_CODES    email VARCHAR(255) NOT NULL UNIQUE,    phone VARCHAR(20),

-- Stores OTP codes for 2FA authentication

-- =====================================================    password VARCHAR(255) NOT NULL,    profile_picture VARCHAR(500),

CREATE TABLE IF NOT EXISTS two_factor_codes (

    id INT AUTO_INCREMENT PRIMARY KEY,    full_name VARCHAR(255) NOT NULL,    email_verified BOOLEAN DEFAULT FALSE,

    user_id INT NOT NULL,

    code VARCHAR(10) NOT NULL,    phone VARCHAR(20),    verification_code VARCHAR(10),

    code_type ENUM('login', 'setup', 'password_reset') DEFAULT 'login',

    attempts_used INT DEFAULT 0,    profile_picture VARCHAR(500),    code_expiry TIMESTAMP NULL,

    max_attempts INT DEFAULT 3,

    expires_at TIMESTAMP NOT NULL,    email_verified BOOLEAN DEFAULT FALSE,    is_active BOOLEAN DEFAULT TRUE,

    used_at TIMESTAMP NULL,

    ip_address VARCHAR(45),    is_2fa_enabled BOOLEAN DEFAULT TRUE,    is_admin BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        is_active BOOLEAN DEFAULT TRUE,    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user_code (user_id, code),    is_admin BOOLEAN DEFAULT FALSE,    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_expires (expires_at),

    INDEX idx_used (used_at)    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,    INDEX idx_email (email),

-- =====================================================

-- TABLE 3: REMEMBER_TOKENS        INDEX idx_verification (verification_code),

-- Stores "Remember Me" tokens for persistent login

-- =====================================================    INDEX idx_email (email),    INDEX idx_active (is_active)

CREATE TABLE IF NOT EXISTS remember_tokens (

    id INT AUTO_INCREMENT PRIMARY KEY,    INDEX idx_active (is_active));

    user_id INT NOT NULL,

    token VARCHAR(255) NOT NULL UNIQUE,) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    expires_at TIMESTAMP NOT NULL,

    device_info VARCHAR(500),-- =====================================================

    ip_address VARCHAR(45),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,-- =====================================================-- 2. REMEMBER TOKENS TABLE (for "Remember Me" functionality)

    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- TABLE 2: TWO_FACTOR_CODES-- =====================================================

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_token (token),-- Stores OTP codes for 2FA authenticationCREATE TABLE remember_tokens (

    INDEX idx_user_id (user_id),

    INDEX idx_expires (expires_at)-- =====================================================    id INT AUTO_INCREMENT PRIMARY KEY,

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS two_factor_codes (    user_id INT NOT NULL,

-- =====================================================

-- TABLE 4: PASSWORD_RESETS    id INT AUTO_INCREMENT PRIMARY KEY,    token VARCHAR(255) NOT NULL UNIQUE,

-- Stores password reset tokens

-- =====================================================    user_id INT NOT NULL,    expires_at TIMESTAMP NOT NULL,

CREATE TABLE IF NOT EXISTS password_resets (

    id INT AUTO_INCREMENT PRIMARY KEY,    code VARCHAR(10) NOT NULL,    device_info VARCHAR(500),

    user_id INT NOT NULL,

    token VARCHAR(255) NOT NULL UNIQUE,    code_type ENUM('login', 'setup', 'password_reset') DEFAULT 'login',    ip_address VARCHAR(45),

    expires_at TIMESTAMP NOT NULL,

    used_at TIMESTAMP NULL,    attempts_used INT DEFAULT 0,    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        max_attempts INT DEFAULT 3,    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_token (token),    expires_at TIMESTAMP NOT NULL,    

    INDEX idx_user_id (user_id),

    INDEX idx_expires (expires_at)    used_at TIMESTAMP NULL,    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ip_address VARCHAR(45),    INDEX idx_token (token),

-- =====================================================

-- VERIFICATION QUERIES    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    INDEX idx_user_id (user_id),

-- Run these to verify all tables were created correctly

-- =====================================================        INDEX idx_expires (expires_at)



-- Show all tables    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,);

SHOW TABLES;

    INDEX idx_user_code (user_id, code),

-- Verify users table structure

DESCRIBE users;    INDEX idx_expires (expires_at),-- =====================================================



-- Verify two_factor_codes table structure    INDEX idx_used (used_at)-- 3. CATEGORIES TABLE (for organizing notes)

DESCRIBE two_factor_codes;

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;-- =====================================================

-- Verify remember_tokens table structure

DESCRIBE remember_tokens;CREATE TABLE categories (



-- Verify password_resets table structure-- =====================================================    id INT AUTO_INCREMENT PRIMARY KEY,

DESCRIBE password_resets;

-- TABLE 3: REMEMBER_TOKENS    name VARCHAR(255) NOT NULL,

-- Count records in each table

SELECT 'users' as table_name, COUNT(*) as record_count FROM users-- Stores "Remember Me" tokens for persistent login    description TEXT,

UNION ALL

SELECT 'two_factor_codes', COUNT(*) FROM two_factor_codes-- =====================================================    color VARCHAR(7) DEFAULT '#667eea', -- Hex color code

UNION ALL

SELECT 'remember_tokens', COUNT(*) FROM remember_tokensCREATE TABLE IF NOT EXISTS remember_tokens (    icon VARCHAR(50) DEFAULT 'bi-folder',

UNION ALL

SELECT 'password_resets', COUNT(*) FROM password_resets;    id INT AUTO_INCREMENT PRIMARY KEY,    parent_id INT NULL, -- For subcategories



-- =====================================================    user_id INT NOT NULL,    is_active BOOLEAN DEFAULT TRUE,

-- SUCCESS MESSAGE

-- =====================================================    token VARCHAR(255) NOT NULL UNIQUE,    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

SELECT '✅ Database setup complete! All tables created successfully.' as status;

    expires_at TIMESTAMP NOT NULL,    

    device_info VARCHAR(500),    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,

    ip_address VARCHAR(45),    INDEX idx_name (name),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    INDEX idx_parent (parent_id),

    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,    INDEX idx_active (is_active)

    );

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_token (token),-- =====================================================

    INDEX idx_user_id (user_id),-- 4. NOTES TABLE (main content)

    INDEX idx_expires (expires_at)-- =====================================================

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;CREATE TABLE notes (

    id INT AUTO_INCREMENT PRIMARY KEY,

-- =====================================================    user_id INT NOT NULL,

-- TABLE 4: PASSWORD_RESETS    category_id INT NULL,

-- Stores password reset tokens    title VARCHAR(255) NOT NULL,

-- =====================================================    content LONGTEXT,

CREATE TABLE IF NOT EXISTS password_resets (    summary TEXT, -- Short description/excerpt

    id INT AUTO_INCREMENT PRIMARY KEY,    tags VARCHAR(500), -- Comma-separated tags

    user_id INT NOT NULL,    file_path VARCHAR(500), -- If note has attached file

    token VARCHAR(255) NOT NULL UNIQUE,    file_type VARCHAR(50), -- pdf, docx, image, etc.

    expires_at TIMESTAMP NOT NULL,    file_size INT, -- in bytes

    used_at TIMESTAMP NULL,    is_public BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,    is_featured BOOLEAN DEFAULT FALSE,

        view_count INT DEFAULT 0,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,    like_count INT DEFAULT 0,

    INDEX idx_token (token),    share_count INT DEFAULT 0,

    INDEX idx_user_id (user_id),    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',

    INDEX idx_expires (expires_at)    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    

-- =====================================================    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

-- OPTIONAL: Sample Admin User    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,

-- Default password: Admin@123 (change this immediately!)    INDEX idx_user (user_id),

-- =====================================================    INDEX idx_category (category_id),

-- Uncomment the line below to create a default admin account    INDEX idx_title (title),

-- INSERT INTO users (email, password, full_name, email_verified, is_admin)     INDEX idx_status (status),

-- VALUES ('admin@iapnotes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', TRUE, TRUE);    INDEX idx_public (is_public),

    INDEX idx_created (created_at),

-- =====================================================    FULLTEXT idx_content (title, content, summary, tags)

-- VERIFICATION QUERIES);

-- Run these to verify all tables were created correctly

-- =====================================================-- =====================================================

-- 5. NOTE SHARES TABLE (who can access which notes)

-- Show all tables-- =====================================================

SHOW TABLES;CREATE TABLE note_shares (

    id INT AUTO_INCREMENT PRIMARY KEY,

-- Verify users table structure    note_id INT NOT NULL,

DESCRIBE users;    shared_with_user_id INT NULL, -- Specific user (NULL = public)

    shared_by_user_id INT NOT NULL,

-- Verify two_factor_codes table structure    permission ENUM('view', 'edit', 'admin') DEFAULT 'view',

DESCRIBE two_factor_codes;    expires_at TIMESTAMP NULL, -- Optional expiration

    is_active BOOLEAN DEFAULT TRUE,

-- Verify remember_tokens table structure    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

DESCRIBE remember_tokens;    

    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,

-- Verify password_resets table structure    FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE,

DESCRIBE password_resets;    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_note (note_id),

-- Count records in each table    INDEX idx_shared_with (shared_with_user_id),

SELECT 'users' as table_name, COUNT(*) as record_count FROM users    INDEX idx_shared_by (shared_by_user_id),

UNION ALL    INDEX idx_active (is_active),

SELECT 'two_factor_codes', COUNT(*) FROM two_factor_codes    UNIQUE KEY unique_share (note_id, shared_with_user_id)

UNION ALL);

SELECT 'remember_tokens', COUNT(*) FROM remember_tokens

UNION ALL-- =====================================================

SELECT 'password_resets', COUNT(*) FROM password_resets;-- 6. NOTE LIKES TABLE (user interactions)

-- =====================================================

-- =====================================================CREATE TABLE note_likes (

-- SUCCESS MESSAGE    id INT AUTO_INCREMENT PRIMARY KEY,

-- =====================================================    note_id INT NOT NULL,

SELECT '✅ Database setup complete! All tables created successfully.' as status;    user_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (note_id, user_id),
    INDEX idx_note (note_id),
    INDEX idx_user (user_id)
);

-- =====================================================
-- 7. NOTE COMMENTS TABLE (discussions)
-- =====================================================
CREATE TABLE note_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT NULL, -- For reply comments
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES note_comments(id) ON DELETE CASCADE,
    INDEX idx_note (note_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 8. USER FOLLOWS TABLE (follow other users)
-- =====================================================
CREATE TABLE user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
);

-- =====================================================
-- 9. FAVORITES TABLE (user's favorite notes)
-- =====================================================
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, note_id),
    INDEX idx_user (user_id),
    INDEX idx_note (note_id)
);

-- =====================================================
-- 10. ACTIVITY LOG TABLE (track user actions)
-- =====================================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL, -- 'created_note', 'liked_note', 'shared_note', etc.
    target_type VARCHAR(50), -- 'note', 'user', 'comment'
    target_id INT,
    details JSON, -- Additional data
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at)
);

-- =====================================================
-- INSERT SAMPLE DATA
-- =====================================================

-- Insert default categories
INSERT INTO categories (name, description, color, icon) VALUES
('Computer Science', 'Programming, algorithms, and software development', '#667eea', 'bi-laptop'),
('Mathematics', 'Calculus, algebra, statistics, and mathematical concepts', '#28a745', 'bi-calculator'),
('Physics', 'Mechanics, thermodynamics, and physics principles', '#dc3545', 'bi-lightning'),
('Chemistry', 'Organic, inorganic, and analytical chemistry', '#fd7e14', 'bi-flask'),
('Biology', 'Cell biology, genetics, and life sciences', '#20c997', 'bi-tree'),
('Business', 'Management, marketing, and business studies', '#6f42c1', 'bi-briefcase'),
('Literature', 'English literature, writing, and language arts', '#e83e8c', 'bi-book'),
('History', 'World history, civilizations, and historical events', '#795548', 'bi-clock-history'),
('Art & Design', 'Creative arts, design principles, and visual arts', '#ff5722', 'bi-palette'),
('General', 'Miscellaneous notes and study materials', '#6c757d', 'bi-journal-text');

-- Insert admin user (password: admin123)
INSERT INTO users (email, password, full_name, email_verified, is_admin) VALUES
('admin@noteshareacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', TRUE, TRUE);

-- Insert sample user (password: user123)
INSERT INTO users (email, password, full_name, email_verified) VALUES
('student@noteshareacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sample Student', TRUE);

-- Insert sample notes
INSERT INTO notes (user_id, category_id, title, content, summary, tags, is_public, status) VALUES
(2, 1, 'Introduction to Data Structures', 'Data structures are fundamental concepts in computer science that allow us to organize and store data efficiently...', 'Basic overview of arrays, linked lists, stacks, and queues', 'data-structures,algorithms,computer-science', TRUE, 'published'),
(2, 1, 'Object-Oriented Programming Principles', 'OOP is a programming paradigm based on the concept of objects, which contain data and code...', 'Understanding encapsulation, inheritance, and polymorphism', 'oop,programming,java,python', TRUE, 'published'),
(2, 2, 'Calculus I - Derivatives', 'A derivative represents the rate of change of a function with respect to a variable...', 'Introduction to derivatives and differentiation rules', 'calculus,derivatives,mathematics', TRUE, 'published');

-- =====================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- =====================================================

-- Update note like_count when likes are added/removed
DELIMITER $$
CREATE TRIGGER update_note_like_count_insert
AFTER INSERT ON note_likes
FOR EACH ROW
BEGIN
    UPDATE notes SET like_count = (
        SELECT COUNT(*) FROM note_likes WHERE note_id = NEW.note_id
    ) WHERE id = NEW.note_id;
END$$

CREATE TRIGGER update_note_like_count_delete
AFTER DELETE ON note_likes
FOR EACH ROW
BEGIN
    UPDATE notes SET like_count = (
        SELECT COUNT(*) FROM note_likes WHERE note_id = OLD.note_id
    ) WHERE id = OLD.note_id;
END$$
DELIMITER ;

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- Popular notes view
CREATE VIEW popular_notes AS
SELECT 
    n.*,
    u.full_name as author_name,
    u.profile_picture as author_picture,
    c.name as category_name,
    c.color as category_color
FROM notes n
LEFT JOIN users u ON n.user_id = u.id
LEFT JOIN categories c ON n.category_id = c.id
WHERE n.status = 'published' AND n.is_public = TRUE
ORDER BY (n.like_count + n.view_count + n.share_count) DESC;

-- User dashboard view
CREATE VIEW user_dashboard AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.email,
    COUNT(DISTINCT n.id) as total_notes,
    COUNT(DISTINCT nl.id) as total_likes_received,
    COUNT(DISTINCT f.id) as total_favorites,
    COUNT(DISTINCT uf.follower_id) as followers_count,
    COUNT(DISTINCT uf2.following_id) as following_count
FROM users u
LEFT JOIN notes n ON u.id = n.user_id
LEFT JOIN note_likes nl ON n.id = nl.note_id
LEFT JOIN favorites f ON u.id = f.user_id
LEFT JOIN user_follows uf ON u.id = uf.following_id
LEFT JOIN user_follows uf2 ON u.id = uf2.follower_id
GROUP BY u.id;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_notes_public_status ON notes(is_public, status);
CREATE INDEX idx_notes_featured ON notes(is_featured, created_at);
CREATE INDEX idx_activity_user_date ON activity_log(user_id, created_at);

-- =====================================================
-- SETUP COMPLETE!
-- =====================================================

SELECT 'Database setup completed successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = DATABASE();
SELECT 'Sample data inserted' as message;