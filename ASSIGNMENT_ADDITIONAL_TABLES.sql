-- =====================================================
-- ASSIGNMENT SPECIFIC REQUIREMENTS
-- Additional tables to meet all assignment criteria
-- =====================================================

USE noteshare_academy;

-- =====================================================
-- USER PROFILES TABLE (Extended user information)
-- =====================================================
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    bio TEXT,
    website VARCHAR(255),
    location VARCHAR(255),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    occupation VARCHAR(255),
    education_level ENUM('high_school', 'bachelors', 'masters', 'phd', 'other'),
    interests TEXT,
    social_media JSON,
    privacy_settings JSON,
    notification_preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_location (location),
    INDEX idx_occupation (occupation)
);

-- =====================================================
-- FORM SUBMISSIONS TABLE (Track all form submissions)
-- =====================================================
CREATE TABLE form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    form_type ENUM('contact', 'registration', 'feedback', 'support', 'survey') NOT NULL,
    form_data JSON NOT NULL,
    submission_ip VARCHAR(45),
    user_agent VARCHAR(500),
    status ENUM('pending', 'processed', 'replied', 'archived') DEFAULT 'pending',
    processed_by INT,
    processed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_form_type (form_type),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- =====================================================
-- VALIDATION RULES TABLE (Store validation rules)
-- =====================================================
CREATE TABLE validation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(100) NOT NULL,
    rule_type ENUM('required', 'email', 'phone', 'min_length', 'max_length', 'regex', 'numeric', 'date') NOT NULL,
    rule_value VARCHAR(255),
    error_message VARCHAR(255) NOT NULL,
    form_type VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_field_name (field_name),
    INDEX idx_form_type (form_type),
    INDEX idx_active (is_active)
);

-- =====================================================
-- INVENTORY TABLE (Product stock management)
-- =====================================================
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity_in_stock INT NOT NULL DEFAULT 0,
    quantity_reserved INT DEFAULT 0,
    quantity_available AS (quantity_in_stock - quantity_reserved) STORED,
    reorder_level INT DEFAULT 10,
    supplier_info JSON,
    last_restocked TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products_services(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_reorder (reorder_level)
);

-- =====================================================
-- AUDIT TRAIL TABLE (Track all database changes)
-- =====================================================
CREATE TABLE audit_trail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    old_values JSON,
    new_values JSON,
    changed_by INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_action (action),
    INDEX idx_changed_by (changed_by),
    INDEX idx_created (created_at)
);

-- =====================================================
-- INSERT SAMPLE DATA FOR NEW TABLES
-- =====================================================

-- Insert sample user profiles
INSERT INTO user_profiles (user_id, bio, location, occupation, education_level, interests) VALUES
(1, 'System administrator with 10+ years experience in web development and database management.', 'New York, USA', 'System Administrator', 'masters', 'Web Development, Database Design, Cybersecurity'),
(2, 'Software developer passionate about clean code and modern web technologies.', 'California, USA', 'Software Developer', 'bachelors', 'Programming, Machine Learning, Mobile Apps'),
(3, 'UI/UX designer focused on creating beautiful and functional user experiences.', 'Texas, USA', 'UI/UX Designer', 'bachelors', 'Design, User Experience, Digital Art'),
(4, 'Full-stack developer with expertise in PHP, JavaScript, and database systems.', 'Florida, USA', 'Full Stack Developer', 'masters', 'Web Development, API Design, Cloud Computing'),
(5, 'Marketing specialist helping businesses grow through digital marketing strategies.', 'Illinois, USA', 'Marketing Specialist', 'bachelors', 'Digital Marketing, Social Media, Analytics');

-- Insert validation rules
INSERT INTO validation_rules (field_name, rule_type, rule_value, error_message, form_type) VALUES
('email', 'required', '', 'Email address is required', 'registration'),
('email', 'email', '', 'Please enter a valid email address', 'registration'),
('password', 'required', '', 'Password is required', 'registration'),
('password', 'min_length', '8', 'Password must be at least 8 characters long', 'registration'),
('full_name', 'required', '', 'Full name is required', 'registration'),
('full_name', 'min_length', '2', 'Full name must be at least 2 characters long', 'registration'),
('phone', 'regex', '^[\+]?[1-9][\d]{0,15}$', 'Please enter a valid phone number', 'contact'),
('age', 'numeric', '', 'Age must be a number', 'survey'),
('age', 'min_length', '18', 'You must be at least 18 years old', 'survey');

-- Insert sample form submissions
INSERT INTO form_submissions (user_id, form_type, form_data, submission_ip, status) VALUES
(2, 'contact', '{"name": "John Doe", "email": "john.doe@example.com", "subject": "Website Inquiry", "message": "I am interested in your web development services."}', '192.168.1.100', 'processed'),
(3, 'feedback', '{"rating": 5, "comments": "Great platform! Very user-friendly interface.", "recommend": true}', '192.168.1.101', 'processed'),
(4, 'support', '{"issue_type": "technical", "priority": "medium", "description": "Having trouble with 2FA setup"}', '192.168.1.102', 'pending'),
(2, 'survey', '{"satisfaction": 4, "features_used": ["notes", "sharing", "2fa"], "improvement_suggestions": "Add mobile app"}', '192.168.1.100', 'pending');

-- Insert inventory data for products
INSERT INTO inventory (product_id, quantity_in_stock, reorder_level, supplier_info) VALUES
(1, 50, 10, '{"supplier_name": "Tech Wholesale Inc", "contact": "supplier@techwholesale.com", "phone": "+1-555-0123"}'),
(3, 30, 5, '{"supplier_name": "Mobile Devices Ltd", "contact": "orders@mobiledevices.com", "phone": "+1-555-0124"}'),
(5, 75, 15, '{"supplier_name": "Audio Equipment Co", "contact": "sales@audioequip.com", "phone": "+1-555-0125"}'),
(8, 40, 8, '{"supplier_name": "Tablet Solutions", "contact": "info@tabletsolutions.com", "phone": "+1-555-0126"}'),
(10, 60, 12, '{"supplier_name": "Wearable Tech Inc", "contact": "support@wearabletech.com", "phone": "+1-555-0127"}');

-- =====================================================
-- VIEWS FOR ASSIGNMENT REPORTING
-- =====================================================

-- All Users View (for displaying all users in a table)
CREATE VIEW all_users_display AS
SELECT 
    u.id,
    u.full_name,
    u.email,
    u.phone,
    up.location,
    up.occupation,
    u.is_2fa_enabled,
    u.email_verified,
    u.is_active,
    u.created_at as registration_date,
    COUNT(DISTINCT n.id) as notes_count,
    COUNT(DISTINCT o.id) as orders_count
FROM users u
LEFT JOIN user_profiles up ON u.id = up.user_id
LEFT JOIN notes n ON u.id = n.user_id
LEFT JOIN orders o ON u.id = o.user_id
GROUP BY u.id
ORDER BY u.created_at DESC;

-- All Products and Services View (for displaying goods and services)
CREATE VIEW all_products_services_display AS
SELECT 
    ps.id,
    ps.name,
    ps.description,
    ps.type,
    c.name as category,
    ps.price,
    ps.currency,
    i.quantity_available,
    ps.is_available,
    u.full_name as created_by,
    ps.created_at,
    COUNT(DISTINCT oi.id) as times_ordered
FROM products_services ps
LEFT JOIN categories c ON ps.category_id = c.id
LEFT JOIN users u ON ps.created_by = u.id
LEFT JOIN inventory i ON ps.id = i.product_id
LEFT JOIN order_items oi ON ps.id = oi.product_service_id
GROUP BY ps.id
ORDER BY ps.created_at DESC;

-- Form Submissions Summary
CREATE VIEW form_submissions_summary AS
SELECT 
    fs.id,
    u.full_name as submitted_by,
    fs.form_type,
    fs.status,
    fs.submission_ip,
    fs.created_at as submitted_at,
    p.full_name as processed_by_name,
    fs.processed_at
FROM form_submissions fs
LEFT JOIN users u ON fs.user_id = u.id
LEFT JOIN users p ON fs.processed_by = p.id
ORDER BY fs.created_at DESC;

-- =====================================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- =====================================================

-- Procedure to get user statistics
DELIMITER $$
CREATE PROCEDURE GetUserStatistics(IN user_id INT)
BEGIN
    SELECT 
        u.full_name,
        u.email,
        COUNT(DISTINCT n.id) as total_notes,
        COUNT(DISTINCT o.id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent,
        COUNT(DISTINCT fs.id) as form_submissions,
        u.created_at as member_since,
        DATEDIFF(NOW(), u.created_at) as days_since_registration
    FROM users u
    LEFT JOIN notes n ON u.id = n.user_id
    LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
    LEFT JOIN form_submissions fs ON u.id = fs.user_id
    WHERE u.id = user_id
    GROUP BY u.id;
END$$
DELIMITER ;

-- Procedure to update inventory
DELIMITER $$
CREATE PROCEDURE UpdateInventory(
    IN product_id INT,
    IN quantity_change INT,
    IN operation ENUM('add', 'subtract'),
    IN notes_text TEXT
)
BEGIN
    DECLARE current_stock INT DEFAULT 0;
    
    SELECT quantity_in_stock INTO current_stock 
    FROM inventory 
    WHERE product_id = product_id;
    
    IF operation = 'add' THEN
        UPDATE inventory 
        SET quantity_in_stock = quantity_in_stock + quantity_change,
            notes = notes_text,
            last_restocked = NOW()
        WHERE product_id = product_id;
    ELSE
        UPDATE inventory 
        SET quantity_in_stock = quantity_in_stock - quantity_change,
            notes = notes_text
        WHERE product_id = product_id;
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Assignment database setup completed!' as status;
SELECT 'All required tables for OOP PHP assignment created' as message;
SELECT 'Tables include: Users, Products/Services, Forms, Validation, Inventory, Audit Trail' as details;

-- Show final table count
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'noteshare_academy';