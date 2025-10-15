-- =====================================================
-- COMPLETE DATABASE SETUP FOR ASSIGNMENT
-- Run this entire script in MySQL Workbench
-- =====================================================

-- Create the database
DROP DATABASE IF EXISTS noteshare_academy;
CREATE DATABASE noteshare_academy;
USE noteshare_academy;

-- =====================================================
-- 1. USERS TABLE (for user registration and authentication)
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
    is_2fa_enabled BOOLEAN DEFAULT FALSE,
    preferred_2fa_method ENUM('email', 'sms') DEFAULT 'email',
    is_active BOOLEAN DEFAULT TRUE,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_verification (verification_code),
    INDEX idx_active (is_active),
    INDEX idx_2fa_enabled (is_2fa_enabled)
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
-- 3. PASSWORD RESETS TABLE (for password recovery)
-- =====================================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at),
    UNIQUE KEY unique_user_reset (user_id)
);

-- =====================================================
-- 4. TWO FACTOR CODES TABLE (for 2FA implementation)
-- =====================================================
CREATE TABLE two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(10) NOT NULL,
    code_type ENUM('login', 'setup', 'password_reset') DEFAULT 'login',
    attempts_used INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code),
    INDEX idx_expires (expires_at)
);

-- =====================================================
-- 5. TRUSTED DEVICES TABLE (skip 2FA for known devices)
-- =====================================================
CREATE TABLE trusted_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_fingerprint VARCHAR(255) NOT NULL,
    device_name VARCHAR(255),
    browser_info VARCHAR(500),
    ip_address VARCHAR(45),
    trust_expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_device (user_id, device_fingerprint),
    UNIQUE KEY unique_user_device (user_id, device_fingerprint)
);

-- =====================================================
-- 6. CATEGORIES TABLE (for organizing notes/content)
-- =====================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#667eea',
    icon VARCHAR(50) DEFAULT 'bi-folder',
    parent_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
);

-- =====================================================
-- 7. PRODUCTS/SERVICES TABLE (for goods and services)
-- =====================================================
CREATE TABLE products_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('product', 'service') NOT NULL,
    category_id INT,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_name (name),
    INDEX idx_type (type),
    INDEX idx_category (category_id),
    INDEX idx_available (is_available),
    INDEX idx_created_by (created_by)
);

-- =====================================================
-- 8. NOTES TABLE (main content)
-- =====================================================
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    summary TEXT,
    tags VARCHAR(500),
    file_path VARCHAR(500),
    file_type VARCHAR(50),
    file_size INT,
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
-- 9. ACTIVITY LOG TABLE (track user actions)
-- =====================================================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    details JSON,
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
-- 10. ORDERS TABLE (for product/service purchases)
-- =====================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    shipping_address TEXT,
    billing_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- =====================================================
-- 11. ORDER ITEMS TABLE (items in each order)
-- =====================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_service_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_service_id) REFERENCES products_services(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_product (product_service_id)
);

-- =====================================================
-- INSERT SAMPLE DATA
-- =====================================================

-- Insert sample categories
INSERT INTO categories (name, description, color, icon) VALUES
('Technology', 'Tech products and services', '#667eea', 'bi-laptop'),
('Education', 'Educational materials and courses', '#28a745', 'bi-book'),
('Health & Wellness', 'Health products and wellness services', '#dc3545', 'bi-heart'),
('Home & Garden', 'Home improvement and gardening', '#fd7e14', 'bi-house'),
('Business Services', 'Professional business services', '#6f42c1', 'bi-briefcase'),
('Entertainment', 'Entertainment products and services', '#e83e8c', 'bi-music-note'),
('Food & Beverage', 'Food products and catering services', '#20c997', 'bi-cup'),
('Fashion & Beauty', 'Clothing, accessories, and beauty products', '#ff5722', 'bi-bag');

-- Insert admin user (password: admin123)
INSERT INTO users (email, password, full_name, email_verified, is_admin, is_2fa_enabled) VALUES
('admin@noteshareacademy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', TRUE, TRUE, TRUE);

-- Insert sample users (password: user123)
INSERT INTO users (email, password, full_name, phone, email_verified) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '+1234567890', TRUE),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '+1234567891', TRUE),
('mike.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', '+1234567892', TRUE),
('sarah.wilson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Wilson', '+1234567893', TRUE);

-- Insert sample products
INSERT INTO products_services (name, description, type, category_id, price, stock_quantity, created_by) VALUES
('Laptop Computer', 'High-performance laptop for students and professionals', 'product', 1, 999.99, 50, 1),
('Web Development Course', 'Complete web development bootcamp', 'service', 2, 299.99, 0, 1),
('Smartphone', 'Latest Android smartphone with advanced features', 'product', 1, 699.99, 30, 1),
('Graphic Design Service', 'Professional logo and branding design', 'service', 5, 150.00, 0, 1),
('Wireless Headphones', 'Premium wireless headphones with noise cancellation', 'product', 1, 199.99, 75, 1),
('Digital Marketing Consultation', 'Expert digital marketing strategy consultation', 'service', 5, 89.99, 0, 1),
('Programming Tutorial Series', 'Comprehensive programming tutorials for beginners', 'service', 2, 49.99, 0, 1),
('Tablet Device', '10-inch tablet perfect for reading and productivity', 'product', 1, 399.99, 40, 1),
('Online Math Tutoring', 'One-on-one math tutoring sessions', 'service', 2, 25.00, 0, 1),
('Smart Watch', 'Fitness tracking smartwatch with health monitoring', 'product', 1, 249.99, 60, 1);

-- Insert sample notes
INSERT INTO notes (user_id, category_id, title, content, summary, tags, is_public, status) VALUES
(2, 1, 'Introduction to Object-Oriented Programming', 'Object-oriented programming (OOP) is a programming paradigm based on the concept of objects...', 'Basic concepts of OOP including classes, objects, inheritance, and polymorphism', 'oop,programming,php,java', TRUE, 'published'),
(2, 1, 'Database Normalization Guide', 'Database normalization is the process of organizing data in a database...', 'Complete guide to database normalization forms and best practices', 'database,mysql,normalization,sql', TRUE, 'published'),
(3, 2, 'Two-Factor Authentication Implementation', 'Implementing 2FA in web applications for enhanced security...', 'Step-by-step guide to implementing 2FA using PHP and MySQL', '2fa,security,php,authentication', TRUE, 'published'),
(3, 1, 'PHP PDO Best Practices', 'Using PHP Data Objects (PDO) for secure database connections...', 'Best practices for using PDO in PHP applications', 'php,pdo,database,security', TRUE, 'published'),
(4, 2, 'Bootstrap Form Validation', 'Client-side and server-side form validation using Bootstrap...', 'Complete form validation guide with Bootstrap and JavaScript', 'bootstrap,validation,forms,javascript', TRUE, 'published');

-- Insert sample orders
INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, shipping_address) VALUES
(2, 'ORD-2025-001', 999.99, 'completed', 'paid', '123 Main St, City, State 12345'),
(3, 'ORD-2025-002', 549.98, 'processing', 'paid', '456 Oak Ave, City, State 12346'),
(4, 'ORD-2025-003', 199.99, 'pending', 'pending', '789 Pine Rd, City, State 12347'),
(2, 'ORD-2025-004', 89.99, 'completed', 'paid', '123 Main St, City, State 12345');

-- Insert order items
INSERT INTO order_items (order_id, product_service_id, quantity, unit_price, total_price) VALUES
(1, 1, 1, 999.99, 999.99),
(2, 3, 1, 699.99, 699.99),
(2, 5, 1, 199.99, 199.99),
(3, 5, 1, 199.99, 199.99),
(4, 6, 1, 89.99, 89.99);

-- =====================================================
-- CREATE USEFUL VIEWS
-- =====================================================

-- View for user dashboard
CREATE VIEW user_dashboard AS
SELECT 
    u.id as user_id,
    u.full_name,
    u.email,
    u.phone,
    u.is_2fa_enabled,
    u.created_at as member_since,
    COUNT(DISTINCT n.id) as total_notes,
    COUNT(DISTINCT o.id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent
FROM users u
LEFT JOIN notes n ON u.id = n.user_id
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id;

-- View for products with category info
CREATE VIEW products_with_categories AS
SELECT 
    ps.id,
    ps.name,
    ps.description,
    ps.type,
    ps.price,
    ps.currency,
    ps.stock_quantity,
    ps.is_available,
    c.name as category_name,
    c.color as category_color,
    c.icon as category_icon,
    u.full_name as created_by_name,
    ps.created_at
FROM products_services ps
LEFT JOIN categories c ON ps.category_id = c.id
LEFT JOIN users u ON ps.created_by = u.id;

-- View for order details
CREATE VIEW order_details AS
SELECT 
    o.id as order_id,
    o.order_number,
    u.full_name as customer_name,
    u.email as customer_email,
    o.total_amount,
    o.currency,
    o.status as order_status,
    o.payment_status,
    o.created_at as order_date,
    COUNT(oi.id) as total_items
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- =====================================================
-- TRIGGERS FOR AUTOMATIC UPDATES
-- =====================================================

-- Update order total when items change
DELIMITER $$
CREATE TRIGGER update_order_total_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_amount = (
        SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id
    ) WHERE id = NEW.order_id;
END$$

CREATE TRIGGER update_order_total_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_amount = (
        SELECT SUM(total_price) FROM order_items WHERE order_id = NEW.order_id
    ) WHERE id = NEW.order_id;
END$$

CREATE TRIGGER update_order_total_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders SET total_amount = (
        SELECT COALESCE(SUM(total_price), 0) FROM order_items WHERE order_id = OLD.order_id
    ) WHERE id = OLD.order_id;
END$$
DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_products_price ON products_services(price);
CREATE INDEX idx_orders_date ON orders(created_at);
CREATE INDEX idx_notes_public_status ON notes(is_public, status);

-- =====================================================
-- SETUP COMPLETE!
-- =====================================================

SELECT 'Database setup completed successfully!' as status;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'noteshare_academy';
SELECT 'Sample data inserted - ready for testing!' as message;

-- Show all created tables
SHOW TABLES;