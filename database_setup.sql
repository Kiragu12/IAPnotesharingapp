-- =====================================================
-- NotesShare Academy Database Setup
-- Complete database schema for the notes sharing app
-- =====================================================

-- Create the main database (uncomment if needed)
-- CREATE DATABASE noteshare_academy;
-- USE noteshare_academy;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(500),
    email_verified BOOLEAN DEFAULT FALSE,
    verification_code VARCHAR(10),
    code_expiry TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_verification (verification_code),
    INDEX idx_active (is_active)
);

-- =====================================================
-- 2. REMEMBER TOKENS TABLE (for "Remember Me" functionality)
-- =====================================================
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    device_info VARCHAR(500),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);

-- =====================================================
-- 3. CATEGORIES TABLE (for organizing notes)
-- =====================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea', -- Hex color code
    icon VARCHAR(50) DEFAULT 'bi-folder',
    parent_id INT NULL, -- For subcategories
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
);

-- =====================================================
-- 4. NOTES TABLE (main content)
-- =====================================================
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    summary TEXT, -- Short description/excerpt
    tags VARCHAR(500), -- Comma-separated tags
    file_path VARCHAR(500), -- If note has attached file
    file_type VARCHAR(50), -- pdf, docx, image, etc.
    file_size INT, -- in bytes
    is_public BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    like_count INT DEFAULT 0,
    share_count INT DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_category (category_id),
    INDEX idx_title (title),
    INDEX idx_status (status),
    INDEX idx_public (is_public),
    INDEX idx_created (created_at),
    FULLTEXT idx_content (title, content, summary, tags)
);

-- =====================================================
-- 5. NOTE SHARES TABLE (who can access which notes)
-- =====================================================
CREATE TABLE note_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    shared_with_user_id INT NULL, -- Specific user (NULL = public)
    shared_by_user_id INT NOT NULL,
    permission ENUM('view', 'edit', 'admin') DEFAULT 'view',
    expires_at TIMESTAMP NULL, -- Optional expiration
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_note (note_id),
    INDEX idx_shared_with (shared_with_user_id),
    INDEX idx_shared_by (shared_by_user_id),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_share (note_id, shared_with_user_id)
);

-- =====================================================
-- 6. NOTE LIKES TABLE (user interactions)
-- =====================================================
CREATE TABLE note_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    note_id INT NOT NULL,
    user_id INT NOT NULL,
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